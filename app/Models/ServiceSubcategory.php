<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubcategory extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function service() { return $this->belongsTo(Service::class); }
}
