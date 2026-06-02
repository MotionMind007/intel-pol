<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignRegion extends Model
{
    protected $fillable = [
        'request_id',
        'region',
        'status',
        'strategy',
        'actions_json',
        'risk',
        'raw_json',
    ];

    protected function casts(): array
    {
        return [
            'actions_json' => 'array',
            'raw_json' => 'array',
        ];
    }
}
