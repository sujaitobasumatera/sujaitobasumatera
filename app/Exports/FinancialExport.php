<?php

namespace App\Exports;

use App\Helpers\CurrencyHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinancialExport implements FromCollection, WithHeadings, WithMapping
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tgl Pesan',
            'ID Transaksi',
            'Item',
            'Pelanggan',
            'Mata Uang',
            'Total (asli)',
            'Total (IDR)',
            'Status',
        ];
    }

    public function map($booking): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $booking->createdAt->format('d/m/Y H:i'),
            $booking->bookingCode,
            $booking->package?->name ?? 'Custom',
            $booking->customer?->name ?? 'Demo User',
            // Both figures: the amount the customer agreed to, and the frozen
            // IDR value that bookkeeping reconciles against. A single column
            // would be ambiguous now that bookings carry their own currency.
            $booking->currency,
            CurrencyHelper::formatRecord($booking->totalPrice, $booking->currency),
            CurrencyHelper::formatRecord($booking->totalPrice_idr, 'IDR'),
            ucfirst($booking->status),
        ];
    }
}
