<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWithoutEmailTest extends TestCase
{
    use RefreshDatabase;

    private ?Package $package = null;

    /** Satu paket dipakai ulang: setiap pemanggilan baru akan bentrok slug. */
    private function makePackage(): Package
    {
        return $this->package ??= Package::create([
            'slug' => 'paket-tanpa-email',
            'name' => 'Paket Tanpa Email',
            'shortDescription' => 'Ringkas',
            'description' => 'Lengkap',
            'images' => [], 'includes' => [], 'excludes' => [],
            'pricingDetails' => [], 'itinerary' => [], 'translations' => [],
            'price' => 500, 'duration' => '2 Hari', 'status' => 'active',
        ]);
    }

    private function book(array $override = []): Booking
    {
        return app(BookingService::class)->create(array_merge([
            'packageId' => $this->makePackage()->id,
            'type' => 'package',
            'customerName' => 'Tamu Satu',
            'customerPhone' => '081234567890',
            'startDate' => now()->addDays(30)->format('Y-m-d'),
            'endDate' => now()->addDays(32)->format('Y-m-d'),
            'status' => 'pending',
            'metadata' => ['pax' => 2, 'paxChildren' => 0],
        ], $override));
    }

    public function test_a_booking_can_be_made_without_an_email(): void
    {
        $booking = $this->book();

        $this->assertNull($booking->customerEmail);
        $this->assertNotNull($booking->bookingCode);
        $this->assertDatabaseHas('customers', ['name' => 'Tamu Satu']);
    }

    public function test_two_guests_without_email_stay_two_customers(): void
    {
        // Inti dari pemindahan kunci. Selama email jadi pengenal, dua tamu
        // tanpa email akan bertabrakan jadi satu baris pelanggan -- namanya
        // bertukar-tukar setiap ada pesanan baru, dan riwayat pesanan satu
        // orang tercampur ke orang lain.
        $this->book(['customerName' => 'Tamu Satu', 'customerPhone' => '081234567890']);
        $this->book(['customerName' => 'Tamu Dua', 'customerPhone' => '081999888777']);

        $this->assertSame(2, Customer::count());
        $this->assertDatabaseHas('customers', ['name' => 'Tamu Satu']);
        $this->assertDatabaseHas('customers', ['name' => 'Tamu Dua']);
    }

    public function test_the_same_number_written_differently_is_one_customer(): void
    {
        // 0812..., +62 812..., dan 62812... adalah orang yang sama.
        $this->book(['customerName' => 'Budi', 'customerPhone' => '081234567890']);
        $this->book(['customerName' => 'Budi', 'customerPhone' => '+62 812-3456-7890']);
        $this->book(['customerName' => 'Budi', 'customerPhone' => '6281234567890']);

        $this->assertSame(1, Customer::count());
        $this->assertSame(3, Customer::first()->total_bookings);
    }

    public function test_an_email_given_later_is_kept_and_never_wiped(): void
    {
        // Tamu memesan tanpa email, lalu memesan lagi dan mengisinya --
        // dan berikutnya mengosongkannya lagi. Alamat yang sudah tercatat
        // tidak boleh terhapus oleh pesanan yang kosong.
        $this->book(['customerPhone' => '081234567890']);
        $this->book(['customerPhone' => '081234567890', 'customerEmail' => 'budi@test.local']);
        $this->book(['customerPhone' => '081234567890']);

        $this->assertSame(1, Customer::count());
        $this->assertSame('budi@test.local', Customer::first()->email);
    }

    public function test_the_public_form_no_longer_asks_for_email_or_a_checkbox(): void
    {
        $package = $this->makePackage();

        $html = $this->get(route('tour.package.detail', $package->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('name="customerEmail"', $html);
        $this->assertStringNotContainsString('name="terms"', $html);
        // Persetujuannya tidak ikut hilang, hanya pindah ke atas tombol kirim.
        $this->assertStringContainsString(route('terms'), $html);
        $this->assertStringContainsString(route('privacy'), $html);
    }

    public function test_the_submit_button_is_not_stuck_disabled(): void
    {
        // Tombol kirim dulu terkunci sampai centang S&K dicentang. Setelah
        // centangnya dihapus, kondisi itu akan bernilai false selamanya dan
        // tombolnya tidak pernah bisa ditekan.
        $package = $this->makePackage();

        $html = $this->get(route('tour.package.detail', $package->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('termsAccepted', $html);
    }

    public function test_posting_the_form_without_email_is_accepted(): void
    {
        $package = $this->makePackage();

        $response = $this->post(route('tour.booking.submit'), [
            'packageId' => $package->id,
            'customerName' => 'Tamu Form',
            'customerPhone' => '081234567890',
            'startDate' => now()->addDays(30)->format('Y-m-d'),
            'pax' => 2,
            'paxChildren' => 0,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', ['customerName' => 'Tamu Form']);
    }
}
