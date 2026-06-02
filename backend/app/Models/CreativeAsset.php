<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreativeAsset extends Model
{
    protected $fillable = [
        'project_id',
        'job_id',
        'asset_type',
        'title',
        'file_path',
        'thumbnail_path',
        'prompt_used',
        'negative_prompt_used',
        'provider_used',
        'model_used',
        'width',
        'height',
        'duration',
        'fps',
        'aspect_ratio',
        'resolution',
        'status',
        'approval_status',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function project()
    {
        return $this->belongsTo(CreativeProject::class, 'project_id');
    }
}
