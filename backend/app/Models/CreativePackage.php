<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreativePackage extends Model
{
    protected $fillable = [
        'project_id',
        'creative_brief',
        'big_idea',
        'content_angles_json',
        'hook_options_json',
        'caption_options_json',
        'cta_options_json',
        'visual_style',
        'script_json',
        'storyboard_json',
        'image_prompts_json',
        'video_prompts_json',
        'asset_specs_json',
        'safety_notes_json',
        'raw_json',
    ];

    protected function casts(): array
    {
        return [
            'content_angles_json' => 'array',
            'hook_options_json' => 'array',
            'caption_options_json' => 'array',
            'cta_options_json' => 'array',
            'script_json' => 'array',
            'storyboard_json' => 'array',
            'image_prompts_json' => 'array',
            'video_prompts_json' => 'array',
            'asset_specs_json' => 'array',
            'safety_notes_json' => 'array',
            'raw_json' => 'array',
        ];
    }
}
