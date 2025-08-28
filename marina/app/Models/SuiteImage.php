<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuiteImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'suite_id',
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

    public function suite(): BelongsTo
    {
        return $this->belongsTo(Suite::class);
    }
}