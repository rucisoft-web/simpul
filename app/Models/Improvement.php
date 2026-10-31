<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Improvement extends Model
{
    protected $guarded = [];
    protected $casts = ['target_date' => 'date'];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function owner() { return $this->belongsTo(Person::class, 'owner_id'); }
}
