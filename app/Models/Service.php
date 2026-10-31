<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function family() { return $this->belongsTo(ServiceFamily::class, 'service_family_id'); }
    public function subcategories() { return $this->hasMany(ServiceSubcategory::class); }
    public function cis() { return $this->belongsToMany(Ci::class); }
    public function contracts() { return $this->belongsToMany(Contract::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
}
