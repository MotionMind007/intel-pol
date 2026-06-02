<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignStrategyReport extends Model
{
    protected $fillable = [
        'request_id',
        'executive_summary',
        'positioning_statement',
        'main_narrative',
        'final_strategy_json',
        'sources_json',
        'strategic_score',
        'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'final_strategy_json' => 'array',
            'sources_json' => 'array',
            'strategic_score' => 'integer',
        ];
    }
}
