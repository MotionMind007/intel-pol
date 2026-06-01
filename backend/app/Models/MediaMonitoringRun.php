<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaMonitoringRun extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'keyword_id',
        'status',
        'total_items',
        'news_count',
        'social_count',
        'google_search_count',
        'google_trends_count',
        'positive_count',
        'neutral_count',
        'negative_count',
        'risk_level',
        'error_message',
        'queued_at',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function keyword()
    {
        return $this->belongsTo(MonitoringKeyword::class, 'keyword_id');
    }

    public function insight()
    {
        return $this->hasOne(MediaMonitoringInsight::class, 'run_id');
    }

    public function items()
    {
        return $this->hasMany(MediaItem::class, 'run_id');
    }
}
