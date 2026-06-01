<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'prompt_content',
        'category',
        'risk_level',
        'technology_type',
        'endpoint',
        'status',
    ];
}
