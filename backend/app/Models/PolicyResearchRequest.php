<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyResearchRequest extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'policy_topic',
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
        return $this->hasOne(PolicyReport::class, 'request_id');
    }

    public function sources()
    {
        return $this->hasMany(PolicySource::class, 'request_id');
    }

    public function publicResponses()
    {
        return $this->hasMany(PublicResponse::class, 'request_id');
    }

    public function stakeholders()
    {
        return $this->hasMany(PolicyStakeholder::class, 'request_id');
    }

    public function impactAnalysis()
    {
        return $this->hasOne(PolicyImpactAnalysis::class, 'request_id');
    }
}
