<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'role_description',
        'system_prompt',
        'provider_id',
        'model_id',
        'temperature',
        'max_tokens',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'max_tokens' => 'integer',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    public function model()
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'agent_skills')
            ->withPivot(['enabled', 'requires_approval', 'daily_limit'])
            ->withTimestamps();
    }
}
