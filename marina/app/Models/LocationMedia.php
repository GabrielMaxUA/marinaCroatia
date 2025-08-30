<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationMedia extends Model
{
    use HasFactory;

    protected $table = 'location_media';

    protected $fillable = [
        'location_id',
        'media_type',
        'media_url',
        'media_title',
        'display_order',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'uploaded_at' => 'datetime'
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Scope to get only images
     */
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    /**
     * Scope to get only videos
     */
    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    /**
     * Scope to get primary media
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    /**
     * Get the full URL for the media
     */
    public function getFullUrlAttribute()
    {
        return \Storage::url($this->media_url);
    }

    /**
     * Check if this is an image
     */
    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    /**
     * Check if this is a video
     */
    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }
}