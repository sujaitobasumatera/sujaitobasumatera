<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use App\Models\Setting;

/**
 * Currency conversion & formatting.
 *
 * There are TWO kinds of money in this system and they must not be confused:
 *
 * 1. SELLING PRICES — stored in MYR (`packages.price`, `childPrice`,
 *    `dronePrice`, and the `price` keys inside `pricingDetails`). These are a
 *    forward-looking price list, managed in ringgit because the primary market
 *    is Malaysia/Singapore. They are converted at display time via formatPrice().
 *
 * 2. RECORDS — an amount that was already agreed, earned, or paid. Bookings
 *    carry their own `currency` + frozen `totalPrice_idr`; internal costs
 *    (`cost_price`, `total_cost`) stay in IDR because they are paid to local
 *    vendors in rupiah. These must NEVER be re-converted at read time: an
 *    invoice or a tax report that moves when an admin edits the exchange rate
 *    is not a record. Render them with formatIn($amount, $currency).
 *
 * Rates are stored in settings as IDR-per-unit, matching the labels already
 * used in the admin settings form:
 *   finance.exchange_rate_manual_myr => how many IDR make 1 MYR (e.g. 4400)
 *   finance.exchange_rate_manual_sgd => how many IDR make 1 SGD (e.g. 11500)
 */
class CurrencyHelper
{
    /** Currency the selling price list is denominated in. */
    public const PRICE_BASE = 'MYR';

    /** Currency all financial reporting is denominated in. */
    public const REPORTING = 'IDR';

    public const DEFAULT_MYR_IDR = 4400;

    public const DEFAULT_SGD_IDR = 11500;

    /**
     * How each currency is written. Keyed by ISO code so that records can be
     * rendered in the currency they were agreed in, independent of who is
     * looking at them.
     *
     * 'decimals' is the SHOP WINDOW form — a price tag reads better whole
     * ("RM 78", not "RM 77.92"). 'recordDecimals' is what a document that
     * states an amount owed must use; hiding sen there would print a total
     * the customer cannot reconcile against what they transfer. Child pricing
     * defaults to half the adult fare, so an odd price produces sen the very
     * first time someone books with a child.
     */
    public const CURRENCIES = [
        'MYR' => ['symbol' => 'RM ', 'decimals' => 0, 'recordDecimals' => 2, 'decPoint' => '.', 'thousandsSep' => ','],
        'IDR' => ['symbol' => 'Rp ', 'decimals' => 0, 'recordDecimals' => 0, 'decPoint' => ',', 'thousandsSep' => '.'],
        'SGD' => ['symbol' => 'S$ ', 'decimals' => 0, 'recordDecimals' => 2, 'decPoint' => '.', 'thousandsSep' => ','],
    ];

    /** Which currency each site locale shops in. */
    public const LOCALE_CURRENCY = [
        'my' => 'MYR',
        'id' => 'IDR',
        'en' => 'SGD',
    ];

    /**
     * Currency code for a locale, defaulting to the price base.
     *
     * @param  string|null  $locale
     * @return string
     */
    public static function currencyFor($locale = null)
    {
        if (is_null($locale)) {
            $locale = session('locale', 'my');
        }

        return self::LOCALE_CURRENCY[$locale] ?? self::PRICE_BASE;
    }

    /**
     * Presentation config for a currency code.
     *
     * @param  string  $currency
     * @return array
     */
    public static function config($currency)
    {
        $currency = strtoupper((string) $currency);

        if (! isset(self::CURRENCIES[$currency])) {
            Log::warning("CurrencyHelper: unknown currency '{$currency}', presenting as ".self::PRICE_BASE);

            return self::CURRENCIES[self::PRICE_BASE];
        }

        return self::CURRENCIES[$currency];
    }

    /**
     * Manual IDR-per-unit rates from settings.
     *
     * Di-cache dua lapis:
     * 1. Static property ($cached) — nol query DB setelah panggilan pertama
     *    dalam request yang sama, berapa pun harga yang ditampilkan.
     * 2. Cache Laravel ('currency_manual_rates', 300 detik) — menghindari query
     *    DB pada request berikutnya. SettingObserver membuang key ini saat
     *    admin menyimpan pengaturan nilai tukar.
     *
     * @return array{myr: float, sgd: float}
     */
    protected static array $cachedRates = [];

    protected static function manualRates()
    {
        if (! empty(static::$cachedRates)) {
            return static::$cachedRates;
        }

        static::$cachedRates = \Illuminate\Support\Facades\Cache::remember(
            'currency_manual_rates',
            300,
            function () {
                $settings = Setting::where('key', 'general')->value('value') ?? [];
                if (is_string($settings)) {
                    $settings = json_decode($settings, true) ?: [];
                }
                $finance = $settings['finance'] ?? [];

                $myr = (float) ($finance['exchange_rate_manual_myr'] ?? self::DEFAULT_MYR_IDR);
                $sgd = (float) ($finance['exchange_rate_manual_sgd'] ?? self::DEFAULT_SGD_IDR);

                return [
                    'myr' => $myr > 0 ? $myr : self::DEFAULT_MYR_IDR,
                    'sgd' => $sgd > 0 ? $sgd : self::DEFAULT_SGD_IDR,
                ];
            }
        );

        return static::$cachedRates;
    }

    /**
     * Multiplier converting an amount in MYR (the price base) to $targetCurrency.
     *
     * Only meaningful for selling prices. Do not use it to re-derive a stored
     * record — see the class docblock.
     *
     * @param  string  $targetCurrency
     * @return float
     */
    public static function getRate($targetCurrency)
    {
        $targetCurrency = strtoupper($targetCurrency);

        if ($targetCurrency === self::PRICE_BASE) {
            return 1.0;
        }

        $rates = self::manualRates();

        switch ($targetCurrency) {
            case 'IDR':
                // 1 MYR buys this many IDR.
                return $rates['myr'];
            case 'SGD':
                // Cross rate via IDR: (IDR per MYR) / (IDR per SGD).
                return $rates['sgd'] > 0 ? $rates['myr'] / $rates['sgd'] : 0.0;
        }

        // An unknown currency must not pass through as 1:1 — that would render
        // a ringgit amount under a foreign symbol without anything looking wrong.
        Log::warning("CurrencyHelper: unsupported currency '{$targetCurrency}', falling back to ".self::PRICE_BASE);

        return 1.0;
    }

    /**
     * IDR value of an amount in the price base, at the current rate.
     *
     * Call this once when a booking is created, then store the result. Never
     * call it to display an existing booking.
     *
     * @param  float|null  $amountInMyr
     * @return float
     */
    public static function toIdr($amountInMyr)
    {
        return round((float) $amountInMyr * self::getRate('IDR'), 2);
    }

    /**
     * Format an amount that is already denominated in $currency, without
     * converting it. This is the function for records: invoices, tracking,
     * financial reports, CSV exports, admin tables, internal costs.
     *
     * @param  float|null  $amount
     * @param  string  $currency
     * @param  bool  $withSymbol
     * @return string
     */
    public static function formatIn($amount, $currency, $withSymbol = true)
    {
        return self::render($amount, $currency, 'decimals', $withSymbol);
    }

    /**
     * Format an amount that a document states as owed, earned, or paid —
     * invoices, the tracking page, financial exports.
     *
     * Identical to formatIn() except it keeps the sen. A price tag may round;
     * a figure someone is about to transfer may not.
     *
     * @param  float|null  $amount
     * @param  string  $currency
     * @param  bool  $withSymbol
     * @return string
     */
    public static function formatRecord($amount, $currency, $withSymbol = true)
    {
        return self::render($amount, $currency, 'recordDecimals', $withSymbol);
    }

    /**
     * @param  float|null  $amount
     * @param  string  $currency
     * @param  string  $precisionKey  'decimals' | 'recordDecimals'
     * @param  bool  $withSymbol
     * @return string
     */
    protected static function render($amount, $currency, $precisionKey, $withSymbol)
    {
        if (is_null($amount) || $amount === '') {
            return '-';
        }

        $config = self::config($currency);

        $formatted = number_format(
            (float) $amount,
            $config[$precisionKey] ?? $config['decimals'],
            $config['decPoint'],
            $config['thousandsSep']
        );

        return $withSymbol ? $config['symbol'].$formatted : $formatted;
    }

    /**
     * Convert a selling price held in MYR and format it for the active locale.
     *
     * @param  float|null  $priceInMyr
     * @param  string|null  $locale
     * @return string
     */
    public static function formatPrice($priceInMyr, $locale = null)
    {
        if (is_null($priceInMyr) || $priceInMyr === '') {
            return '-';
        }

        $currency = self::currencyFor($locale);

        return self::formatIn((float) $priceInMyr * self::getRate($currency), $currency);
    }
}
