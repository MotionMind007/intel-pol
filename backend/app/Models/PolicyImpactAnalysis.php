<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyImpactAnalysis extends Model
{
    protected $fillable = [
        'request_id',
        'positive_impact_json',
        'negative_impact_json',
        'implementation_risk_json',
        'political_risk_json',
        'reputation_risk_json',
        'scenario_json',
        'policy_score_json',
        'recommendation_json',
    ];

    protected function casts(): array
    {
        return [
            'positive_impact_json' => 'array',
            'negative_impact_json' => 'array',
            'implementation_risk_json' => 'array',
            'political_risk_json' => 'array',
            'reputation_risk_json' => 'array',
            'scenario_json' => 'array',
            'policy_score_json' => 'array',
            'recommendation_json' => 'array',
        ];
    }
}
