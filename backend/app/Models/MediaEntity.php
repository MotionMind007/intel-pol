<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaEntity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_item_id',
        'entity_name',
        'entity_type',
        'confidence_score',
        'created_at',
    ];
}
