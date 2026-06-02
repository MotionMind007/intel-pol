<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignIssue extends Model
{
    protected $fillable = [
        'request_id',
        'issue',
        'priority',
        'reason',
        'risk',
        'recommended_narrative',
        'raw_json',
    ];

    protected function casts(): array
    {
        return ['raw_json' => 'array'];
    }
}
