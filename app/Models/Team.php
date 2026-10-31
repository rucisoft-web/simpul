<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $guarded = [];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function members() { return $this->belongsToMany(Person::class)->withPivot('role'); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function cis() { return $this->hasMany(Ci::class); }
}
