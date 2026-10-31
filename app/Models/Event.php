<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['occurred_at' => 'datetime'];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function ci() { return $this->belongsTo(Ci::class); }
    public function ticket() { return $this->belongsTo(Ticket::class); }
}
