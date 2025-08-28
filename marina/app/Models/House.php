<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'owner_id',
        'name',
        'street_address',
        'house_number',
        'distance_to_sea',
        'parking_available',
        'parking_description',
        'description',
        'owner_phone',
        'owner_email',
        'bank_account_number',
        'bank_name',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'parking_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function suites(): HasMany
    {
        return $this->hasMany(Suite::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(HouseImage::class);
    }

    public function primaryImage()
    {
        return $this->images()->where('is_primary', true)->first();
    }
}