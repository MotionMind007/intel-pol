<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'provider_type',
        'base_url',
        'api_key_encrypted',
        'status',
        'rate_limit_per_minute',
        'cost_limit_per_day',
        'timeout_seconds',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        // Removed api_key_encrypted from hidden so it can be accessed internally
        // Security is maintained via masked_api_key appended attribute for API responses
    ];

    protected $appends = [
        'masked_api_key',
    ];

    protected function casts(): array
    {
        return [
            'api_key_encrypted' => 'encrypted',
            'cost_limit_per_day' => 'decimal:4',
            'timeout_seconds' => 'integer',
            'rate_limit_per_minute' => 'integer',
        ];
    }

    public function getMaskedApiKeyAttribute(): ?string
    {
        $key = $this->api_key_encrypted;

        if (! $key) {
            return null;
        }

        return strlen($key) <= 8
            ? str_repeat('*', strlen($key))
            : substr($key, 0, 6).'...'.substr($key, -4);
    }
}
