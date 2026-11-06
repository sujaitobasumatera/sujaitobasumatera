<?php

namespace Tests\Feature;

use App\Helpers\CurrencyHelper;
use App\Models\Booking;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Rincian yang bentuknya sama dengan yang ditulis
     * BookingService::calculateTotalPriceAndCost.
     */
    private function makeBooking(string $code, string $status = 'pending'): Booking
    {
        $package = Package::create([
            'slug' => 'invoice-package-'.strtolower($code),
            'name' => 'Invoice Package',
            'shortDescription' => 'Short desc',
            'description' => 'Full description',
            'images' => [],
            'includes' => [],
            'excludes' => [],
            'pricingDetails' => [],
            'itinerary' => [],
            'translations' => [],
            'price' => 750000,
            'duration' => '2 Hari',
            'status' => 'active',
        ]);

        return Booking::create([
            'bookingCode' => $code,
            'type' => 'package',
            'packageId' => $package->id,
            'customerName' => 'Budi Test',
            'customerEmail' => 'budi@test.local',
            'customerPhone' => '08123456789',
            'startDate' => now()->addDays(30),
            'endDate' => now()->addDays(31),
            'totalPrice' => 1998000,
            'currency' => 'IDR',
            'totalPrice_idr' => 1998000,
            'exchange_rate_idr' => 1,
            'status' => $status,
            'metadata' => [
                'pax' => 2,
                'price_breakdown' => [
                    'pax_dewasa' => 2,
                    'price_dewasa_total' => 1500000,
                    'pax_anak' => 1,
                    'price_anak_total' => 300000,
                    'additional_services' => [],
                    'subtotal_base' => 1800000,
                    'surcharges' => [['name' => 'Musim Ramai', 'amount' => 0]],
                    'total_surcharge' => 0,
                    'subtotal_with_surcharge' => 1800000,
                    'tax_percentage' => 11,
                    'tax' => 198000,
                    'total' => 1998000,
                ],
            ],
        ]);
    }

    public function test_invoice_subtotal_plus_tax_equals_total(): void
    {
        // Baris Subtotal pernah membaca kunci `subtotal` yang tidak pernah ditulis
        // BookingService, lalu jatuh ke totalPrice yang SUDAH termasuk pajak —
        // sehingga Subtotal + Pajak tidak pernah sama dengan Total. Tes ini
        // membaca ketiga angka dari halaman terbit, bukan dari metadata.
        $this->makeBooking('WT-INV001');

        $response = $this->get(route('invoice.download', 'WT-INV001'));

        $response->assertOk();
        $response->assertSee(CurrencyHelper::formatRecord(1800000, 'IDR'), false);
        $response->assertSee(CurrencyHelper::formatRecord(198000, 'IDR'), false);
        $response->assertSee(CurrencyHelper::formatRecord(1998000, 'IDR'), false);
        $response->assertSee('(11%)', false);
        // Angka pajak tidak boleh sama dengan total: itu gejala pajak dipungut dua kali.
        $this->assertNotSame(
            CurrencyHelper::formatRecord(1998000, 'IDR'),
            CurrencyHelper::formatRecord(1800000, 'IDR')
        );
    }

    public function test_invoice_renders_instead_of_returning_an_error_string(): void
    {
        // streamInvoice() menangkap Throwable dan memulangkan teks error dengan
        // status 200, jadi assertOk() saja tidak membuktikan apa pun.
        $this->makeBooking('WT-INV002');

        $response = $this->get(route('invoice.download', 'WT-INV002'));

        $response->assertOk();
        $response->assertDontSee('Gagal membuka invoice');
        $response->assertSee('WT-INV002');
        $response->assertSee('Invoice Package', false);
    }

    public function test_invoice_is_translated_for_malaysian_visitors(): void
    {
        // Seluruh 430 baris halaman ini dulu ditulis langsung dalam bahasa
        // Indonesia: tamu yang memilih bahasa lain tetap menerima invoice
        // berbahasa Indonesia.
        $this->makeBooking('WT-INV003');

        $response = $this->withSession(['locale' => 'my'])->get(route('invoice.download', 'WT-INV003'));

        $response->assertOk();
        $response->assertSee('Butiran Invois');
        $response->assertSee('Jumlah Perlu Dibayar');
        $response->assertSee('Kuantiti');
        $response->assertDontSee('Rincian Invoice');
    }

    public function test_invoice_is_translated_for_english_visitors(): void
    {
        $this->makeBooking('WT-INV004');

        $response = $this->withSession(['locale' => 'en'])->get(route('invoice.download', 'WT-INV004'));

        $response->assertOk();
        $response->assertSee('Invoice Details');
        $response->assertSee('Amount Due');
        $response->assertSee('Billed To');
        $response->assertDontSee('Diterbitkan Untuk');
    }

    public function test_unknown_invoice_code_does_not_leak_other_bookings(): void
    {
        $this->makeBooking('WT-INV005');

        $response = $this->get(route('invoice.download', 'WT-NOPE'));

        $response->assertDontSee('WT-INV005');
        $response->assertDontSee('Budi Test');
    }
}
