<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'image_url',
        'image_title',
        'display_order',
        'is_primary'
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}