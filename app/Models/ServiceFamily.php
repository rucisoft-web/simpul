<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceFamily extends Model
{
    protected $guarded = [];

    public function services() { return $this->hasMany(Service::class); }
}
