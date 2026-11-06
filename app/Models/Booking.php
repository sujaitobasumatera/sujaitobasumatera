<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * @mixin \Eloquent
 */

class Booking extends Model
{
    use HasFactory;
    use Notifiable, SoftDeletes;

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->customerEmail;
    }

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'userId', 'customerId', 'type', 'packageId', 'startDate', 'endDate',
        'totalPrice', 'currency', 'exchange_rate_idr', 'totalPrice_idr',
        'total_cost', 'customerName', 'customerEmail', 'customerPhone',
        'notes', 'metadata', 'status', 'bookingCode',
    ];

    protected $casts = [
        'startDate' => 'datetime',
        'endDate' => 'datetime',
        // decimal, matching the columns — 'double' reintroduced float error on
        // values the schema deliberately stores exactly.
        'totalPrice' => 'decimal:2',
        'totalPrice_idr' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'exchange_rate_idr' => 'decimal:4',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'packageId');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
