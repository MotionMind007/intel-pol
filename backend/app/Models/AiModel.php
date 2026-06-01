<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    protected $fillable = [
        'provider_id',
        'model_name',
        'display_name',
        'context_window',
        'input_price_per_million_tokens',
        'output_price_per_million_tokens',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
