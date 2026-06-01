<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringKeyword extends Model
{
    protected $fillable = [
        'user_id',
        'keyword',
        'status',
    ];
}
