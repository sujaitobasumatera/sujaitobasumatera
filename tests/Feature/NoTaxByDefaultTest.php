<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoTaxByDefaultTest extends TestCase
{
    use RefreshDatabase;

    private ?Package $package = null;

    private function makePackage(): Package
    {
        return $this->package ??= Package::create([
            'slug' => 'paket-tanpa-pajak',
            'name' => 'Paket Tanpa Pajak',
            'shortDescription' => 'Ringkas',
            'description' => 'Lengkap',
            'images' => [], 'includes' => [], 'excludes' => [],
            'pricingDetails' => [], 'itinerary' => [], 'translations' => [],
            'price' => 500, 'duration' => '2 Hari', 'status' => 'active',
        ]);
    }

    private function book(): Booking
    {
        return app(BookingService::class)->create([
            'packageId' => $this->makePackage()->id,
            'type' => 'package',
            'customerName' => 'Tamu Pajak',
            'customerPhone' => '081234567890',
            'startDate' => now()->addDays(30)->format('Y-m-d'),
            'endDate' => now()->addDays(32)->format('Y-m-d'),
            'status' => 'pending',
            'metadata' => ['pax' => 2, 'paxChildren' => 0],
        ]);
    }

    public function test_no_tax_is_charged_when_nobody_configured_one(): void
    {
        // Angka 11 dulu tertanam sebagai nilai bawaan di kode, jadi setiap
        // pesanan sejak awal dipungut PPN tanpa seorang pun pernah memilihnya.
        // Memungut PPN tanpa berstatus PKP adalah masalah hukum, bukan sekadar
        // salah angka.
        $booking = $this->book();
        $pb = $booking->metadata['price_breakdown'];

        $this->assertEquals(0, $pb['tax']);
        $this->assertEquals(0, $pb['tax_percentage']);
        // 2 dewasa x RM 500, tanpa tambahan apa pun.
        $this->assertEquals(1000.00, $pb['subtotal_base']);
        $this->assertEquals(1000.00, $booking->totalPrice);
    }

    public function test_the_zero_tax_row_is_not_printed_on_the_invoice(): void
    {
        // "Pajak & Layanan RM 0,00" pada dokumen keuangan bukan informasi,
        // ia pertanyaan.
        $booking = $this->book();

        $this->get(route('invoice.download', $booking->bookingCode))
            ->assertOk()
            ->assertDontSee('Cukai & Perkhidmatan', false)
            ->assertDontSee('Pajak & Layanan', false);
    }

    public function test_tax_still_applies_when_the_owner_switches_it_on(): void
    {
        // Kalau nanti sudah PKP, angkanya diisi di Pengaturan dan langsung
        // berlaku — nol hanya BAWAAN, bukan penghapusan fiturnya.
        Setting::updateOrCreate(['key' => 'general'], ['value' => ['finance' => ['tax_percentage' => 11]]]);

        $booking = $this->book();
        $pb = $booking->metadata['price_breakdown'];

        $this->assertEquals(110.00, $pb['tax']);
        $this->assertEquals(1110.00, $booking->totalPrice);
    }

    public function test_old_bookings_keep_the_tax_that_was_frozen_on_them(): void
    {
        // Invoice adalah catatan. Pesanan lama yang memang dipungut 11% harus
        // tetap terbaca 11% hari ini, apa pun pengaturan sekarang.
        $booking = Booking::create([
            'bookingCode' => 'WT-LAMA01',
            'type' => 'package',
            'packageId' => $this->makePackage()->id,
            'customerName' => 'Tamu Lama',
            'customerPhone' => '081234567890',
            'startDate' => now()->addDays(10),
            'endDate' => now()->addDays(12),
            'totalPrice' => 1110.00,
            'currency' => 'IDR',
            'totalPrice_idr' => 1110.00,
            'exchange_rate_idr' => 1,
            'status' => 'confirmed',
            'metadata' => ['pax' => 2, 'price_breakdown' => [
                'pax_dewasa' => 2, 'price_dewasa_total' => 1000.00,
                'pax_anak' => 0, 'price_anak_total' => 0,
                'additional_services' => [], 'subtotal_base' => 1000.00,
                'surcharges' => [], 'tax_percentage' => 11, 'tax' => 110.00,
                'total' => 1110.00,
            ]],
        ]);

        $this->get(route('invoice.download', $booking->bookingCode))
            ->assertOk()
            ->assertSee('(11%)', false);
    }
}
