<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Eloquent
 */

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'phone_key', 'address', 'notes',
        'total_bookings', 'total_spent', 'last_booking_at',
    ];

    /**
     * Kunci pengenal pelanggan: sembilan digit terakhir nomor telepon.
     *
     * Sembilan digit terakhir stabil untuk bentuk 08xx, +628xx, maupun 628xx,
     * jadi tamu yang menulis nomornya berbeda-beda di dua pesanan tetap
     * dikenali sebagai satu orang. Nomor yang terlalu pendek untuk dipercaya
     * menghasilkan null — lebih baik tidak punya kunci daripada punya kunci
     * yang menempelkan dua orang asing jadi satu.
     */
    public static function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $phone);

        return strlen($digits) >= 9 ? substr($digits, -9) : null;
    }

    protected $casts = [
        'last_booking_at' => 'datetime',
        'total_spent' => 'decimal:2',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customerId');
    }
}
