<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyReport extends Model
{
    protected $fillable = [
        'request_id',
        'executive_summary',
        'result_json',
        'result_markdown',
        'sources_json',
        'final_score',
        'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'result_json' => 'array',
            'sources_json' => 'array',
            'final_score' => 'integer',
        ];
    }
}
