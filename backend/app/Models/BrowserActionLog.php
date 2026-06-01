<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrowserActionLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'agent_id',
        'run_id',
        'action_type',
        'target_url',
        'domain',
        'input_json',
        'output_json',
        'screenshot_path',
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
