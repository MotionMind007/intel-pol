<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSkill extends Model
{
    protected $fillable = [
        'agent_id',
        'skill_id',
        'enabled',
        'requires_approval',
        'daily_limit',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }
}
