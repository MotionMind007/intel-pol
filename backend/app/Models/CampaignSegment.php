<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSegment extends Model
{
    protected $fillable = [
        'request_id',
        'segment',
        'priority',
        'needs',
        'main_issue',
        'message',
        'channel',
        'raw_json',
    ];

    protected function casts(): array
    {
        return ['raw_json' => 'array'];
    }
}
