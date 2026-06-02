<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignStrategyRequest extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'campaign_object_type',
        'campaign_object_name',
        'campaign_goal',
        'region',
        'status',
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

    public function report()
    {
        return $this->hasOne(CampaignStrategyReport::class, 'request_id');
    }

    public function sources()
    {
        return $this->hasMany(CampaignDataSource::class, 'request_id');
    }

    public function segments()
    {
        return $this->hasMany(CampaignSegment::class, 'request_id');
    }

    public function issues()
    {
        return $this->hasMany(CampaignIssue::class, 'request_id');
    }

    public function regions()
    {
        return $this->hasMany(CampaignRegion::class, 'request_id');
    }

    public function recommendations()
    {
        return $this->hasMany(CampaignRecommendation::class, 'request_id');
    }
}
