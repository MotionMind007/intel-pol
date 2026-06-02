<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignRecommendation extends Model
{
    protected $fillable = [
        'request_id',
        'recommendation_type',
        'title',
        'description',
        'target',
        'channel',
        'raw_json',
    ];

    protected function casts(): array
    {
        return ['raw_json' => 'array'];
    }
}
