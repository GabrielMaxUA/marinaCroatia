<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model
{
    protected $table = 'bank_info';
    
    protected $fillable = [
        'owner_id',
        'bank_name',
        'swift',
        'bank_address',
        'account_number',
        'iban',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the owner that owns this bank info
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}