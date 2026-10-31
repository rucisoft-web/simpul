<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $guarded = [];

    public function parent() { return $this->belongsTo(Organization::class, 'parent_id'); }
    public function children() { return $this->hasMany(Organization::class, 'parent_id'); }
    public function locations() { return $this->hasMany(Location::class); }
    public function persons() { return $this->hasMany(Person::class); }
    public function teams() { return $this->hasMany(Team::class); }
    public function cis() { return $this->hasMany(Ci::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function services() { return $this->hasMany(Service::class); }
    public function contracts() { return $this->hasMany(Contract::class); }

    /** Id organisasi ini beserta seluruh anak perusahaannya. */
    public function familyIds(): array
    {
        return array_merge([$this->id], $this->children()->pluck('id')->all());
    }
}
