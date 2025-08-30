<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContent extends Model
{
    use HasFactory;

    protected $table = 'site_content';
    
    // Disable created_at since the table only has updated_at
    const CREATED_AT = null;
    
    protected $fillable = [
        'content_key',
        'content_value',
        'updated_by'
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function get($key, $default = '')
    {
        $content = static::where('content_key', $key)->first();
        return $content ? $content->content_value : $default;
    }

    public static function set($key, $value, $userId = null)
    {
        return static::updateOrCreate(
            ['content_key' => $key],
            [
                'content_value' => $value,
                'updated_by' => $userId
            ]
        );
    }
}