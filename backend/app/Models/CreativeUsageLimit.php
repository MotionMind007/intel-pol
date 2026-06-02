<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreativeUsageLimit extends Model
{
    protected $fillable = [
        'role',
        'max_images_per_day',
        'max_videos_per_day',
        'max_video_duration',
        'max_cost_per_day',
        'requires_approval_above_cost',
    ];

    protected function casts(): array
    {
        return [
            'max_images_per_day' => 'integer',
            'max_videos_per_day' => 'integer',
            'max_video_duration' => 'integer',
            'max_cost_per_day' => 'decimal:4',
            'requires_approval_above_cost' => 'decimal:4',
        ];
    }
}
