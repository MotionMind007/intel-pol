<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyStakeholder extends Model
{
    protected $fillable = [
        'request_id',
        'name',
        'type',
        'position',
        'influence_level',
        'notes',
    ];
}
