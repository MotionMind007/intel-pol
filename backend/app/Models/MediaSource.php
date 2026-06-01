<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaSource extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'source_type',
        'platform',
        'credibility_score',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credibility_score' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
