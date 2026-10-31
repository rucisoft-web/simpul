<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    protected $table = 'models';
    protected $guarded = [];

    public function brand() { return $this->belongsTo(Brand::class); }
    public function cis() { return $this->hasMany(Ci::class, 'model_id'); }
}
