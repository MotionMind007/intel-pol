<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreativeProject extends Model
{
    protected $fillable = [
        'user_id',
        'campaign_strategy_report_id',
        'title',
        'campaign_object_type',
        'campaign_object_name',
        'objective',
        'platform',
        'tone',
        'status',
    ];

    public function package()
    {
        return $this->hasOne(CreativePackage::class, 'project_id');
    }

    public function assets()
    {
        return $this->hasMany(CreativeAsset::class, 'project_id');
    }

    public function jobs()
    {
        return $this->hasMany(CreativeGenerationJob::class, 'project_id');
    }
}
