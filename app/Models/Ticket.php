<?php

namespace App\Models;

use App\Support\Itil;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];
    protected $casts = [
        'planned_start' => 'datetime', 'planned_end' => 'datetime',
        'tto_deadline' => 'datetime', 'ttr_deadline' => 'datetime',
        'assigned_at' => 'datetime', 'resolved_at' => 'datetime', 'closed_at' => 'datetime',
        'outage' => 'boolean', 'sla_tto_passed' => 'boolean', 'sla_ttr_passed' => 'boolean',
    ];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function caller() { return $this->belongsTo(Person::class, 'caller_id'); }
    public function agent() { return $this->belongsTo(Person::class, 'agent_id'); }
    public function team() { return $this->belongsTo(Team::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function subcategory() { return $this->belongsTo(ServiceSubcategory::class, 'service_subcategory_id'); }
    public function parent() { return $this->belongsTo(Ticket::class, 'parent_id'); }
    public function children() { return $this->hasMany(Ticket::class, 'parent_id'); }
    public function cis() { return $this->belongsToMany(Ci::class)->withPivot('impact_code'); }
    public function logs() { return $this->hasMany(TicketLog::class)->orderByDesc('created_at'); }
    public function events() { return $this->hasMany(Event::class); }
    public function knownErrors() { return $this->hasMany(KnownError::class, 'problem_id'); }

    public function scopeOpen($q)
    {
        return $q->whereNotIn('status', Itil::CLOSED_STATUSES);
    }

    public function getIsOpenAttribute(): bool
    {
        return ! in_array($this->status, Itil::CLOSED_STATUSES, true);
    }

    public function getTypeLabelAttribute(): string
    {
        return Itil::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return Itil::statusLabel($this->status);
    }

    /** Persentase sisa waktu TTR (untuk progress bar SLA). */
    public function getSlaProgressAttribute(): ?int
    {
        if (! $this->ttr_deadline) {
            return null;
        }
        $end = $this->resolved_at ?? now();
        $total = max(1, $this->created_at->diffInMinutes($this->ttr_deadline));
        $used = $this->created_at->diffInMinutes($end);

        return (int) min(100, round($used / $total * 100));
    }
}
