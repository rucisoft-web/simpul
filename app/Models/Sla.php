<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    protected $guarded = [];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function slts() { return $this->hasMany(Slt::class); }
    public function contracts() { return $this->hasMany(Contract::class); }
}
