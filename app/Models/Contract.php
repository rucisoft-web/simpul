<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $guarded = [];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function provider() { return $this->belongsTo(Organization::class, 'provider_id'); }
    public function sla() { return $this->belongsTo(Sla::class); }
    public function services() { return $this->belongsToMany(Service::class); }
    public function cis() { return $this->belongsToMany(Ci::class); }
}
