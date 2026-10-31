<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded = [];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function parent() { return $this->belongsTo(Location::class, 'parent_id'); }
    public function children() { return $this->hasMany(Location::class, 'parent_id'); }
    public function cis() { return $this->hasMany(Ci::class); }
    public function persons() { return $this->hasMany(Person::class); }
}
