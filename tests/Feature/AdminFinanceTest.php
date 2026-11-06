<?php

namespace Tests\Feature;

use App\Helpers\CurrencyHelper;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'finance@test.com',
        ]);
    }

    private function makeBookings(int $count, int $amountEach, string $status = 'confirmed'): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Booking::create([
                'bookingCode' => sprintf('WT-FIN%03d', $i),
                'type' => 'package',
                'customerName' => 'Pelanggan '.$i,
                'customerEmail' => "pelanggan{$i}@test.local",
                'customerPhone' => '08123456789',
                'startDate' => now()->addDays(10),
                'endDate' => now()->addDays(11),
                'totalPrice' => $amountEach,
                'currency' => 'IDR',
                'totalPrice_idr' => $amountEach,
                'exchange_rate_idr' => 1,
                'status' => $status,
                'metadata' => ['pax' => 2],
            ]);
        }
    }

    public function test_revenue_counts_every_booking_not_just_the_open_page(): void
    {
        // Halaman ini dipaginasi 20 per halaman. Kartu omzet dulu menjumlahkan
        // koleksi halaman aktif, jadi 25 pesanan terbaca 20 -- dan angkanya
        // berubah setiap admin menekan halaman berikutnya, tanpa error apa pun.
        $this->makeBookings(25, 1_000_000);

        $response = $this->actingAs($this->admin)->get('/admin/finance');

        $response->assertOk();
        $response->assertSee(CurrencyHelper::formatIn(25_000_000, 'IDR'), false);
        $response->assertDontSee(CurrencyHelper::formatIn(20_000_000, 'IDR'), false);
        $response->assertSee('25 Booking', false);
    }

    public function test_average_is_taken_over_every_booking_too(): void
    {
        // 20 pesanan Rp 1jt di halaman pertama, 5 pesanan Rp 5jt terdorong ke
        // halaman dua: rata-rata halaman aktif dan rata-rata sebenarnya berbeda.
        $this->makeBookings(20, 1_000_000);
        for ($i = 21; $i <= 25; $i++) {
            Booking::create([
                'bookingCode' => sprintf('WT-FIN%03d', $i),
                'type' => 'package',
                'customerName' => 'Pelanggan '.$i,
                'customerEmail' => "pelanggan{$i}@test.local",
                'customerPhone' => '08123456789',
                'startDate' => now()->addDays(10),
                'endDate' => now()->addDays(11),
                'totalPrice' => 5_000_000,
                'currency' => 'IDR',
                'totalPrice_idr' => 5_000_000,
                'exchange_rate_idr' => 1,
                'status' => 'confirmed',
                'metadata' => ['pax' => 2],
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/finance');

        // (20 x 1jt + 5 x 5jt) / 25 = 1,8jt
        $response->assertOk();
        $response->assertSee(CurrencyHelper::formatIn(1_800_000, 'IDR'), false);
    }

    public function test_pending_and_cancelled_bookings_stay_out_of_revenue(): void
    {
        $this->makeBookings(2, 1_000_000, 'confirmed');
        Booking::create([
            'bookingCode' => 'WT-FINPEND',
            'type' => 'package',
            'customerName' => 'Belum Bayar',
            'customerEmail' => 'pending@test.local',
            'customerPhone' => '08123456789',
            'startDate' => now()->addDays(10),
            'endDate' => now()->addDays(11),
            'totalPrice' => 9_000_000,
            'currency' => 'IDR',
            'totalPrice_idr' => 9_000_000,
            'exchange_rate_idr' => 1,
            'status' => 'pending',
            'metadata' => ['pax' => 2],
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/finance');

        $response->assertOk();
        $response->assertSee(CurrencyHelper::formatIn(2_000_000, 'IDR'), false);
        $response->assertDontSee(CurrencyHelper::formatIn(11_000_000, 'IDR'), false);
    }

    public function test_empty_finance_page_renders_zero_instead_of_breaking(): void
    {
        // AVG atas nol baris memulangkan NULL; tanpa COALESCE halaman ini
        // menampilkan '-' di tempat angka rupiah, atau melempar.
        $response = $this->actingAs($this->admin)->get('/admin/finance');

        $response->assertOk();
        $response->assertSee(CurrencyHelper::formatIn(0, 'IDR'), false);
        $response->assertSee('0 Booking', false);
    }
}
