<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicResponse extends Model
{
    protected $fillable = [
        'request_id',
        'source_id',
        'platform',
        'author_or_account',
        'content_text',
        'url',
        'published_at',
        'engagement_json',
        'sentiment',
        'sentiment_score',
        'response_type',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'engagement_json' => 'array',
        ];
    }
}
