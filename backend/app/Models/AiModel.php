<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    protected $fillable = [
        'provider_id',
        'modality',
        'model_name',
        'display_name',
        'capabilities_json',
        'context_window',
        'input_price_per_million_tokens',
        'output_price_per_million_tokens',
        'unit_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'capabilities_json' => 'array',
        ];
    }
}
