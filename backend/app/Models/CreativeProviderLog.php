<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreativeProviderLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'provider_id',
        'model_id',
        'job_id',
        'request_payload_json',
        'response_payload_json',
        'status',
        'error_message',
        'cost_estimate',
        'cost_final',
        'latency_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload_json' => 'array',
            'response_payload_json' => 'array',
            'cost_estimate' => 'decimal:4',
            'cost_final' => 'decimal:4',
            'latency_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
