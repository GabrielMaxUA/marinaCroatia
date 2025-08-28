<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suite extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'name',
        'capacity_people',
        'bedrooms',
        'bathrooms',
        'floor_number',
        'description',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingDates(): HasMany
    {
        return $this->hasMany(BookingDate::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(SuiteImage::class);
    }

    public function amenities(): HasMany
    {
        return $this->hasMany(SuiteAmenity::class);
    }

    public function primaryImage()
    {
        return $this->images()->where('is_primary', true)->first();
    }

    public function isAvailable($date)
    {
        return !$this->bookingDates()->where('booking_date', $date)->exists();
    }
}