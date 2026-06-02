<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignDataSource extends Model
{
    protected $fillable = [
        'request_id',
        'source_type',
        'source_name',
        'url',
        'title',
        'published_at',
        'content_text',
        'raw_json',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'raw_json' => 'array',
        ];
    }
}
