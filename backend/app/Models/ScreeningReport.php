<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningReport extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'subject_name',
        'status',
        'final_score',
        'result_json',
        'result_markdown',
        'sources_json',
        'error_message',
        'queued_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'result_json' => 'array',
            'sources_json' => 'array',
            'final_score' => 'integer',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
