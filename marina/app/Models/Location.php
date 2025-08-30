<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'created_by'
    ];

    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(LocationMedia::class)->ordered();
    }

    public function images(): HasMany
    {
        return $this->hasMany(LocationMedia::class)->images()->ordered();
    }

    public function videos(): HasMany
    {
        return $this->hasMany(LocationMedia::class)->videos()->ordered();
    }

    public function primaryImage()
    {
        return $this->hasOne(LocationMedia::class)->images()->primary();
    }

    /**
     * Get the primary image URL or null
     */
    public function getPrimaryImageUrlAttribute()
    {
        $primaryImage = $this->primaryImage;
        return $primaryImage ? $primaryImage->full_url : null;
    }

    /**
     * Get total media count
     */
    public function getMediaCountAttribute()
    {
        return $this->media()->count();
    }

    /**
     * Get image count
     */
    public function getImageCountAttribute()
    {
        return $this->images()->count();
    }

    /**
     * Get video count
     */
    public function getVideoCountAttribute()
    {
        return $this->videos()->count();
    }
}