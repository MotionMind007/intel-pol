<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaMonitoringInsight extends Model
{
    protected $fillable = [
        'run_id',
        'executive_summary',
        'dominant_issues_json',
        'positive_issues_json',
        'negative_issues_json',
        'top_actors_json',
        'top_sources_json',
        'trend_json',
        'google_trends_json',
        'risk_assessment',
        'strategic_recommendation',
        'raw_json',
    ];

    protected function casts(): array
    {
        return [
            'dominant_issues_json' => 'array',
            'positive_issues_json' => 'array',
            'negative_issues_json' => 'array',
            'top_actors_json' => 'array',
            'top_sources_json' => 'array',
            'trend_json' => 'array',
            'google_trends_json' => 'array',
            'strategic_recommendation' => 'array',
            'raw_json' => 'array',
        ];
    }
}
