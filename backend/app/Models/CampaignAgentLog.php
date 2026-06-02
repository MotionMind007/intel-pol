<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignAgentLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'agent_id',
        'request_id',
        'skill_id',
        'action',
        'input_json',
        'output_json',
        'status',
        'error_message',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'input_json' => 'array',
            'output_json' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
