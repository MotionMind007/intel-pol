<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaItemAnalysis extends Model
{
    protected $fillable = [
        'media_item_id',
        'summary',
        'sentiment',
        'sentiment_score',
        'issue_category',
        'risk_level',
        'risk_reason',
        'framing',
        'recommendation',
    ];
}
