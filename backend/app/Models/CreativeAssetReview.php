<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreativeAssetReview extends Model
{
    protected $fillable = ['asset_id', 'reviewer_id', 'status', 'notes'];
}
