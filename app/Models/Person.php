<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'persons';
    protected $guarded = [];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function manager() { return $this->belongsTo(Person::class, 'manager_id'); }
    public function teams() { return $this->belongsToMany(Team::class)->withPivot('role'); }
    public function cis() { return $this->belongsToMany(Ci::class)->withPivot('role'); }
    public function callerTickets() { return $this->hasMany(Ticket::class, 'caller_id'); }
    public function agentTickets() { return $this->hasMany(Ticket::class, 'agent_id'); }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(mb_substr($this->first_name, 0, 1).mb_substr($this->last_name ?? '', 0, 1));
    }
}
