<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDate extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'booking_id',
    //     'suite_id',
    //     'booking_date'
    // ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function suite(): BelongsTo
    {
        return $this->belongsTo(Suite::class);
    }
}