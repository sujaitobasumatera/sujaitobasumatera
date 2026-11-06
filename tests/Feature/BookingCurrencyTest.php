<?php

namespace Tests\Feature;

use App\Helpers\CurrencyHelper;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The selling price list is kept in MYR, but a booking is a record: it carries
 * the currency it was agreed in and a frozen IDR figure for reporting. These
 * tests pin the property that makes that worth doing — an admin editing the
 * exchange rate must not change any already-issued amount.
 */
class BookingCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setRate(float $myrToIdr): void
    {
        Setting::updateOrCreate(
            ['key' => 'general'],
            ['value' => ['finance' => ['exchange_rate_manual_myr' => $myrToIdr]]]
        );
    }

    protected function makePackage(): Package
    {
        return Package::create([
            'slug' => 'currency-package',
            'name' => 'Currency Package',
            'shortDescription' => 'Short desc',
            'description' => 'Full description',
            'images' => [],
            'includes' => [],
            'excludes' => [],
            'pricingDetails' => [],
            'itinerary' => [],
            'translations' => [],
            'price' => 350.00,
            'duration' => '2 Hari',
            'status' => 'active',
        ]);
    }

    public function test_format_in_does_not_convert(): void
    {
        $this->setRate(4400);

        // 350 is 350, whatever currency it is labelled with. Only the
        // presentation changes.
        $this->assertSame('RM 350', CurrencyHelper::formatIn(350, 'MYR'));
        $this->assertSame('Rp 350', CurrencyHelper::formatIn(350, 'IDR'));
        $this->assertSame('S$ 350', CurrencyHelper::formatIn(350, 'SGD'));
    }

    public function test_format_price_converts_the_selling_price_per_locale(): void
    {
        $this->setRate(4400);

        $this->assertSame('RM 350', CurrencyHelper::formatPrice(350, 'my'));
        $this->assertSame('Rp 1.540.000', CurrencyHelper::formatPrice(350, 'id'));
    }

    public function test_price_tags_round_but_documents_keep_the_sen(): void
    {
        // A child fare is half the adult fare, so an odd price produces sen on
        // the first booking that includes a child. The shop window may round
        // that away; an invoice may not, or the customer transfers a figure
        // that does not match what was recorded.
        $this->assertSame('RM 325', CurrencyHelper::formatIn(324.50, 'MYR'));
        $this->assertSame('RM 324.50', CurrencyHelper::formatRecord(324.50, 'MYR'));

        // Rupiah has no subunit in use here, so both forms agree.
        $this->assertSame('Rp 6.000.000', CurrencyHelper::formatIn(6000000, 'IDR'));
        $this->assertSame('Rp 6.000.000', CurrencyHelper::formatRecord(6000000, 'IDR'));
    }

    public function test_unknown_currency_does_not_pass_through_silently(): void
    {
        // A ringgit amount must never be rendered under an unrecognised label
        // as though nothing were wrong.
        $this->assertSame('RM 350', CurrencyHelper::formatIn(350, 'EUR'));
    }

    public function test_booking_amounts_do_not_move_when_the_rate_changes(): void
    {
        $this->setRate(4400);
        $package = $this->makePackage();

        $booking = Booking::create([
            'bookingCode' => 'WT-FROZEN',
            'type' => 'package',
            'packageId' => $package->id,
            'customerName' => 'Siti Test',
            'customerEmail' => 'siti@test.local',
            'customerPhone' => '08123456789',
            'startDate' => now()->addDay(),
            'endDate' => now()->addDays(2),
            'totalPrice' => 700.00,
            'currency' => 'MYR',
            'exchange_rate_idr' => CurrencyHelper::getRate('IDR'),
            'totalPrice_idr' => CurrencyHelper::toIdr(700.00),
            'status' => 'confirmed',
            'metadata' => ['pax' => 2],
        ]);

        $this->assertEquals(4400, $booking->exchange_rate_idr);
        $this->assertEquals(3080000, $booking->totalPrice_idr);

        // The admin revises the rate well after the booking was taken.
        $this->setRate(5000);
        $booking->refresh();

        // Neither the agreed amount nor the reporting figure may follow it.
        $this->assertEquals(700.00, $booking->totalPrice);
        $this->assertEquals(3080000, $booking->totalPrice_idr);
        $this->assertEquals(4400, $booking->exchange_rate_idr);
    }

    public function test_tracking_page_shows_the_booking_currency_not_the_visitor_locale(): void
    {
        $this->setRate(4400);
        $package = $this->makePackage();

        Booking::create([
            'bookingCode' => 'WT-CUR1',
            'type' => 'package',
            'packageId' => $package->id,
            'customerName' => 'Budi Test',
            'customerEmail' => 'budi@test.local',
            'customerPhone' => '08123456789',
            'startDate' => now()->addDay(),
            'endDate' => now()->addDays(2),
            'totalPrice' => 700.00,
            'currency' => 'MYR',
            'exchange_rate_idr' => 4400,
            'totalPrice_idr' => 3080000,
            'status' => 'pending',
            'metadata' => ['pax' => 2],
        ]);

        // Visitor is browsing in Indonesian; the booking was made in ringgit.
        $response = $this->withSession(['locale' => 'id'])
            ->get(route('booking.track', 'wt-cur1'));

        $response->assertOk();
        $response->assertSee('RM 700.00');
        // Rupiah appears only as the frozen reference figure.
        $response->assertSee('Rp 3.080.000');
    }

    public function test_legacy_rupiah_booking_still_renders_as_rupiah(): void
    {
        $this->setRate(4400);
        $package = $this->makePackage();

        // A booking taken before the ringgit price list. The migration labels
        // these IDR at rate 1 without touching the amount.
        Booking::create([
            'bookingCode' => 'WT-OLD1',
            'type' => 'package',
            'packageId' => $package->id,
            'customerName' => 'Lama Test',
            'customerEmail' => 'lama@test.local',
            'customerPhone' => '08123456789',
            'startDate' => now()->addDay(),
            'endDate' => now()->addDays(2),
            'totalPrice' => 6000000,
            'currency' => 'IDR',
            'exchange_rate_idr' => 1,
            'totalPrice_idr' => 6000000,
            'status' => 'pending',
            'metadata' => ['pax' => 2],
        ]);

        $response = $this->get(route('booking.track', 'wt-old1'));

        $response->assertOk();
        $response->assertSee('Rp 6.000.000');
        $response->assertDontSee('RM 6.000.000');
    }

    public function test_admin_editing_price_updates_frozen_idr_using_the_booking_rate(): void
    {
        $this->setRate(4400);
        $package = $this->makePackage();

        $booking = Booking::create([
            'bookingCode' => 'WT-EDIT1',
            'type' => 'package',
            'packageId' => $package->id,
            'customerName' => 'Edit Test',
            'customerPhone' => '08123456789',
            'startDate' => now()->addDay(),
            'endDate' => now()->addDays(2),
            'totalPrice' => 700.00,
            'currency' => 'MYR',
            'exchange_rate_idr' => 4400,
            'totalPrice_idr' => 3080000,
            'status' => 'confirmed',
            'metadata' => ['pax' => 2],
        ]);

        // The current rate moves after the booking; the correction must ignore it.
        $this->setRate(5000);

        app(\App\Services\BookingService::class)->update($booking, [
            'status' => 'confirmed',
            'totalPrice' => 1000.00,
        ]);
        $booking->refresh();

        // IDR follows the corrected price at the FROZEN rate (1000 * 4400),
        // never the new 5000 rate (which would give 5,000,000).
        $this->assertEquals(1000.00, $booking->totalPrice);
        $this->assertEquals(4400000, $booking->totalPrice_idr);
        $this->assertEquals(4400, $booking->exchange_rate_idr);
    }
}
