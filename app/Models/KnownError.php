<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnownError extends Model
{
    protected $guarded = [];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function problem() { return $this->belongsTo(Ticket::class, 'problem_id'); }
}
