<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Converts the price copies held in CMS content from IDR to MYR.
 *
 * The homepage slider stores its own `price` on each slide, inside the
 * `cms_tour` settings row, rather than reading it from the package it links
 * to. 2026_07_21_000002 converted the `packages` table and missed these,
 * so after that migration the homepage rendered rupiah figures under a
 * ringgit symbol — "RM 950,000.00" for a package listed at RM 211.49.
 *
 * Uses the rate the catalogue was actually converted at, read from
 * `finance.exchange_rate_manual_myr`, which 000002 pins. That keeps this
 * migration correct even if it runs later or the admin has since edited
 * the slides — and means it needs no environment variable of its own.
 *
 * Idempotent via `finance.cms_price_base_currency`.
 *
 * NOTE: the real defect is that a slide duplicates a price that already
 * exists on the package. This migration only re-denominates the copies; it
 * does not remove the duplication.
 */
return new class extends Migration
{
    protected const MARKER = 'cms_price_base_currency';

    /**
     * @return array{0: array, 1: array}  [seluruh settings general, blok finance]
     */
    protected function general(): array
    {
        $row = DB::table('settings')->where('key', 'general')->first();
        $value = $row ? (is_string($row->value) ? (json_decode($row->value, true) ?: []) : (array) $row->value) : [];

        return [$value, $value['finance'] ?? []];
    }

    protected function rate(): float
    {
        [, $finance] = $this->general();

        $rate = (float) ($finance['exchange_rate_manual_myr'] ?? 0);

        if ($rate <= 0) {
            throw new RuntimeException(
                'finance.exchange_rate_manual_myr is not set, so CMS slide prices '
                .'cannot be converted at the same rate as the catalogue. Run '
                .'2026_07_21_000002 first.'
            );
        }

        return $rate;
    }

    protected function alreadyConverted(): bool
    {
        [, $finance] = $this->general();

        return ($finance[self::MARKER] ?? null) === 'MYR';
    }

    protected function mark(string $currency): void
    {
        [$value] = $this->general();
        $value['finance'][self::MARKER] = $currency;

        DB::table('settings')->where('key', 'general')->update(['value' => json_encode($value)]);
    }

    /**
     * Re-denominate every slide price in the cms_tour row.
     */
    protected function rescale(callable $convert): void
    {
        $row = DB::table('settings')->where('key', 'cms_tour')->first();
        if (! $row) {
            return;
        }

        $cms = is_string($row->value) ? (json_decode($row->value, true) ?: []) : (array) $row->value;

        $changed = false;
        foreach ($cms['homepage_slides'] ?? [] as $i => $slide) {
            if (! isset($slide['price'])) {
                continue;
            }

            // Slides store the price as a string; keep it a string so the CMS
            // form round-trips it unchanged.
            $raw = preg_replace('/[^0-9.]/', '', (string) $slide['price']);
            if ($raw === '' || ! is_numeric($raw)) {
                continue;
            }

            $cms['homepage_slides'][$i]['price'] = (string) $convert((float) $raw);
            $changed = true;
        }

        if ($changed) {
            DB::table('settings')->where('key', 'cms_tour')->update(['value' => json_encode($cms)]);
        }
    }

    /**
     * No slider content means no price copies to re-denominate — fresh
     * installs and the test suite land here. Skip rather than demanding a
     * rate that would convert nothing.
     */
    protected function nothingToConvert(): bool
    {
        $row = DB::table('settings')->where('key', 'cms_tour')->first();
        if (! $row) {
            return true;
        }

        $cms = is_string($row->value) ? (json_decode($row->value, true) ?: []) : (array) $row->value;

        foreach ($cms['homepage_slides'] ?? [] as $slide) {
            $raw = preg_replace('/[^0-9.]/', '', (string) ($slide['price'] ?? ''));
            if ($raw !== '' && is_numeric($raw)) {
                return false;
            }
        }

        return true;
    }

    public function up(): void
    {
        if ($this->alreadyConverted()) {
            return;
        }

        if ($this->nothingToConvert()) {
            $this->mark('MYR');

            return;
        }

        $rate = $this->rate();

        $this->rescale(fn ($amount) => round($amount / $rate, 2));
        $this->mark('MYR');
    }

    public function down(): void
    {
        if (! $this->alreadyConverted()) {
            return;
        }

        if ($this->nothingToConvert()) {
            $this->mark('IDR');

            return;
        }

        $rate = $this->rate();

        $this->rescale(fn ($amount) => round($amount * $rate, 2));
        $this->mark('IDR');
    }
};
