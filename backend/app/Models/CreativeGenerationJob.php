<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreativeGenerationJob extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'asset_type',
        'provider_id',
        'model_id',
        'prompt',
        'negative_prompt',
        'aspect_ratio',
        'resolution',
        'duration',
        'fps',
        'quality',
        'style',
        'camera_style',
        'output_count',
        'reference_asset_id',
        'status',
        'provider_job_id',
        'cost_estimate',
        'cost_final',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'output_count' => 'integer',
            'cost_estimate' => 'decimal:4',
            'cost_final' => 'decimal:4',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function assets()
    {
        return $this->hasMany(CreativeAsset::class, 'job_id');
    }
}
