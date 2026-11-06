<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Re-denominates the SELLING price list from IDR to MYR.
 *
 * Scope — selling prices only:
 *   packages.price, packages.childPrice, packages.dronePrice
 *   packages.pricingDetails -> tiers[].price, tiers[].child_price
 *   packages.pricingDetails -> additional_services[].price
 *
 * Deliberately NOT converted:
 *   packages.cost_price / bookings.total_cost — internal cost, paid to local
 *     vendors in rupiah; converting it would make reported margin drift with
 *     the exchange rate.
 *   bookings.* — records, handled by 2026_07_21_000001_freeze_booking_currency.
 *
 * THIS REWRITES DATA AND CANNOT BE UNDONE WITHOUT A BACKUP.
 * Take a database dump before running it. `down()` divides back by the same
 * rate, but rounding to 2 decimals loses precision, so it is a fallback, not
 * a substitute for a dump.
 *
 * The rate is NOT defaulted on purpose. Set it explicitly on the server:
 *   PRICE_MIGRATION_MYR_IDR=4400
 * A wrong rate silently mis-prices the entire catalogue, so an unset value
 * aborts the migration instead of guessing.
 */
return new class extends Migration
{
    /** Settings marker recording that the catalogue is already in MYR. */
    protected const MARKER = 'price_base_currency';

    protected function rate(): float
    {
        $rate = (float) env('PRICE_MIGRATION_MYR_IDR', 0);

        if ($rate <= 0) {
            throw new RuntimeException(
                'PRICE_MIGRATION_MYR_IDR is not set. Add it to .env on the server '
                .'(e.g. PRICE_MIGRATION_MYR_IDR=4400) so the catalogue is converted '
                .'at a rate you chose, then re-run the migration.'
            );
        }

        return $rate;
    }

    protected function alreadyConverted(): bool
    {
        $settings = DB::table('settings')->where('key', 'general')->value('value');
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?: [];
        }

        return ($settings['finance'][self::MARKER] ?? null) === 'MYR';
    }

    /**
     * Record the new base currency, and pin the display rate to the rate the
     * catalogue was actually converted at.
     *
     * These two must agree. The migration divides IDR prices by rate X; the
     * site multiplies the resulting MYR back by whatever `exchange_rate_manual_myr`
     * says. If those differ, every rupiah price silently shifts by the ratio
     * between them — a rehearsal on real data showed a 2.1% increase from a
     * settings value of 4492 against a migration rate of 4400. Writing the rate
     * here makes the round trip exact by construction.
     *
     * The admin can still change the rate afterwards; that is a deliberate
     * price change, which is different from an accidental one.
     */
    protected function markConverted(string $currency, ?float $rate = null): void
    {
        $row = DB::table('settings')->where('key', 'general')->first();
        if (! $row) {
            return;
        }

        $value = is_string($row->value) ? (json_decode($row->value, true) ?: []) : (array) $row->value;
        $value['finance'][self::MARKER] = $currency;

        if ($rate && $currency === 'MYR') {
            $value['finance']['exchange_rate_manual_myr'] = (string) $rate;
        }

        DB::table('settings')->where('key', 'general')->update(['value' => json_encode($value)]);
    }

    /**
     * Walk the money keys inside a pricingDetails payload.
     */
    protected function convertPricingDetails($details, callable $convert)
    {
        if (! is_array($details)) {
            return $details;
        }

        foreach ($details['tiers'] ?? [] as $i => $tier) {
            foreach (['price', 'child_price'] as $key) {
                if (isset($tier[$key]) && is_numeric($tier[$key])) {
                    $details['tiers'][$i][$key] = $convert($tier[$key]);
                }
            }
        }

        foreach ($details['additional_services'] ?? [] as $i => $service) {
            if (isset($service['price']) && is_numeric($service['price'])) {
                $details['additional_services'][$i]['price'] = $convert($service['price']);
            }
        }

        return $details;
    }

    protected function rescale(callable $convert, string $marker, ?float $rate = null): void
    {
        DB::transaction(function () use ($convert) {
            DB::table('packages')->orderBy('id')->chunkById(100, function ($packages) use ($convert) {
                foreach ($packages as $package) {
                    $update = [];

                    foreach (['price', 'childPrice', 'dronePrice'] as $column) {
                        if (! is_null($package->$column)) {
                            $update[$column] = $convert($package->$column);
                        }
                    }

                    $details = is_string($package->pricingDetails)
                        ? json_decode($package->pricingDetails, true)
                        : $package->pricingDetails;

                    if (is_array($details)) {
                        $update['pricingDetails'] = json_encode(
                            $this->convertPricingDetails($details, $convert)
                        );
                    }

                    if ($update) {
                        DB::table('packages')->where('id', $package->id)->update($update);
                    }
                }
            });
        });

        $this->markConverted($marker, $rate);
    }

    /**
     * A database with no packages has no rupiah price list to re-denominate —
     * fresh installs and the test suite land here. Record the currency and
     * skip, rather than demanding a rate that would convert nothing.
     */
    protected function nothingToConvert(): bool
    {
        return DB::table('packages')->count() === 0;
    }

    public function up(): void
    {
        if ($this->alreadyConverted()) {
            return;
        }

        if ($this->nothingToConvert()) {
            $this->markConverted('MYR');

            return;
        }

        $rate = $this->rate();

        $this->rescale(fn ($amount) => round((float) $amount / $rate, 2), 'MYR', $rate);
    }

    public function down(): void
    {
        if (! $this->alreadyConverted()) {
            return;
        }

        if ($this->nothingToConvert()) {
            $this->markConverted('IDR');

            return;
        }

        $rate = $this->rate();

        $this->rescale(fn ($amount) => round((float) $amount * $rate, 2), 'IDR');
    }
};
