<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'suite_id',
        'created_by',
        'booking_source',
        'guest_name',
        'guest_phone',
        'guest_quantity',
        'check_in',
        'check_out',
        'total_nights',
        'parking_needed',
        'pets_allowed',
        'has_small_kids',
        'deposit_paid',
        'deposit_amount',
        'notes',
        'is_owner_booking',
        'cancelled_at',
        'cancelled_by'
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'parking_needed' => 'boolean',
            'pets_allowed' => 'boolean',
            'has_small_kids' => 'boolean',
            'deposit_paid' => 'boolean',
            'is_owner_booking' => 'boolean',
            'cancelled_at' => 'datetime',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function suite(): BelongsTo
    {
        return $this->belongsTo(Suite::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function bookingDates(): HasMany
    {
        return $this->hasMany(BookingDate::class);
    }

    public function isActive(): bool
    {
        return $this->cancelled_at === null;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }
}