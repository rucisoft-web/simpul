<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $guarded = [];

    public function models() { return $this->hasMany(DeviceModel::class); }
    public function cis() { return $this->hasMany(Ci::class); }
}
