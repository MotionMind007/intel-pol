<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    protected $fillable = [
        'keyword_id',
        'run_id',
        'source_id',
        'source_type',
        'platform',
        'title',
        'content_text',
        'snippet',
        'url',
        'author',
        'account_name',
        'published_at',
        'captured_at',
        'engagement_json',
        'content_hash',
        'screenshot_path',
        'raw_json',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'captured_at' => 'datetime',
            'engagement_json' => 'array',
            'raw_json' => 'array',
        ];
    }

    public function analysis()
    {
        return $this->hasOne(MediaItemAnalysis::class, 'media_item_id');
    }

    public function source()
    {
        return $this->belongsTo(MediaSource::class, 'source_id');
    }
}
