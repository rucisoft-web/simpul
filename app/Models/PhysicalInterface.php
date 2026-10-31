<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalInterface extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function ci() { return $this->belongsTo(Ci::class); }
}
