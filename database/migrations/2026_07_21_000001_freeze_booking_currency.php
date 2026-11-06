<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Freezes the currency of every booking at the moment it is made.
 *
 * A booking is a record, not a price tag. Before this migration the only
 * money on it was `totalPrice`, whose meaning depended on whatever exchange
 * rate happened to be configured when someone opened the invoice — so an
 * admin editing the rate silently rewrote the amount on already-issued
 * documents, and monthly revenue totals moved after the fact.
 *
 * Three columns fix that permanently:
 *   currency          the currency the customer actually agreed in
 *   exchange_rate_idr IDR per 1 unit of that currency, at booking time
 *   totalPrice_idr    the IDR figure, frozen — the basis for all reporting
 *
 * EXISTING ROWS ARE NOT CONVERTED. Every booking made before this deploy was
 * priced in rupiah, so they are simply labelled IDR at rate 1. No historical
 * amount changes by a single digit.
 *
 * Guarded so it is a safe no-op if partially applied.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'currency')) {
                $table->string('currency', 3)->default('IDR')->after('totalPrice');
            }
            if (! Schema::hasColumn('bookings', 'exchange_rate_idr')) {
                $table->decimal('exchange_rate_idr', 15, 4)->default(1)->after('currency');
            }
            if (! Schema::hasColumn('bookings', 'totalPrice_idr')) {
                $table->decimal('totalPrice_idr', 15, 2)->nullable()->after('exchange_rate_idr');
            }
        });

        // Backfill only rows that have not been stamped yet, so re-running
        // cannot multiply an already-frozen figure.
        DB::table('bookings')
            ->whereNull('totalPrice_idr')
            ->update([
                'currency' => 'IDR',
                'exchange_rate_idr' => 1,
                'totalPrice_idr' => DB::raw('totalPrice'),
            ]);

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('currency');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'currency')) {
                $table->dropIndex(['currency']);
            }
            foreach (['totalPrice_idr', 'exchange_rate_idr', 'currency'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
