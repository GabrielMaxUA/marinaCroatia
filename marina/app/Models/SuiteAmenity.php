<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuiteAmenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'suite_id',
        'amenity_name'
    ];

    public function suite(): BelongsTo
    {
        return $this->belongsTo(Suite::class);
    }
}