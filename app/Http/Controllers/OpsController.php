<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Improvement;
use App\Models\Organization;
use App\Models\Team;
use App\Models\Ticket;
use App\Support\Scope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpsController extends Controller
{
    public function events(Request $request)
    {
        $q = Scope::apply(Event::query(), 'organization_id', false)->with(['ci.brand', 'ticket', 'organization']);
        if ($s = $request->query('severity')) {
            $q->where('severity', $s);
        }
        if ($st = $request->query('status')) {
            $q->where('status', $st);
        }
        $events = $q->latest('occurred_at')->paginate(30)->withQueryString();
        $sev = Scope::apply(Event::query(), 'organization_id', false)->where('occurred_at', '>=', now()->subDay())->selectRaw('severity, COUNT(*) n')->groupBy('severity')->pluck('n', 'severity');
        $hourly = Scope::apply(Event::query(), 'organization_id', false)->where('occurred_at', '>=', now()->subDays(7))
            ->selectRaw("DATE_FORMAT(occurred_at, '%Y-%m-%d') d, severity, COUNT(*) n")->groupBy('d', 'severity')->get();

        return view('ops.events', compact('events', 'sev', 'hourly'));
    }

    public function improvements()
    {
        $items = Scope::apply(Improvement::query(), 'organization_id', false)->with(['organization', 'owner'])->orderBy('status')->get();

        return view('ops.improvements', compact('items'));
    }

    public function itil()
    {
        $counts = [
            'incident' => Scope::apply(Ticket::query())->where('type', 'incident')->count(),
            'request' => Scope::apply(Ticket::query())->where('type', 'request')->count(),
            'problem' => Scope::apply(Ticket::query())->where('type', 'problem')->count(),
            'change' => Scope::apply(Ticket::query())->where('type', 'change')->count(),
        ];

        return view('ops.itil', compact('counts'));
    }

    public function reports()
    {
        $base = fn () => Scope::apply(Ticket::query())->whereIn('type', ['incident', 'request'])->whereNotNull('resolved_at');
        $months = collect(range(11, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $sla = $base()->where('created_at', '>=', $months->first())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) n, SUM(sla_ttr_passed = 0) ok, SUM(sla_tto_passed = 0) tto_ok, AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) mttr")
            ->groupBy('ym')->get()->keyBy('ym');
        $slaChart = [
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M y')),
            'ttr' => $months->map(fn ($m) => ($r = $sla[$m->format('Y-m')] ?? null) ? round($r->ok / max(1, $r->n) * 100, 1) : null),
            'tto' => $months->map(fn ($m) => ($r = $sla[$m->format('Y-m')] ?? null) ? round($r->tto_ok / max(1, $r->n) * 100, 1) : null),
            'mttr' => $months->map(fn ($m) => ($r = $sla[$m->format('Y-m')] ?? null) ? round($r->mttr / 60, 1) : null),
        ];
        $teams = Scope::apply(Team::query())->with('organization')->get()->map(function ($t) {
            $r = Ticket::where('team_id', $t->id)->whereNotNull('resolved_at')->where('created_at', '>=', now()->subDays(90))
                ->selectRaw('COUNT(*) n, SUM(sla_ttr_passed = 0) ok, AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) mttr, AVG(satisfaction) csat')->first();
            $t->stat = $r;

            return $t;
        })->filter(fn ($t) => $t->stat->n > 0)->sortByDesc(fn ($t) => $t->stat->n);
        $orgs = Organization::whereNull('parent_id')->get()->map(function ($o) {
            $ids = $o->familyIds();
            $o->stat = Ticket::whereIn('organization_id', $ids)->whereIn('type', ['incident', 'request'])->whereNotNull('resolved_at')->where('created_at', '>=', now()->subDays(90))
                ->selectRaw('COUNT(*) n, SUM(sla_ttr_passed = 0) ok, AVG(satisfaction) csat')->first();
            $o->fcr = Ticket::whereIn('organization_id', $ids)->where('type', 'incident')->where('created_at', '>=', now()->subDays(90))->whereNotNull('parent_id')->count();

            return $o;
        });
        $resolution = $base()->where('created_at', '>=', now()->subDays(90))->selectRaw('resolution_code, COUNT(*) n')->groupBy('resolution_code')->pluck('n', 'resolution_code');
        $origin = Scope::apply(Ticket::query())->where('created_at', '>=', now()->subDays(90))->selectRaw('origin, COUNT(*) n')->groupBy('origin')->pluck('n', 'origin');
        $heat = Scope::apply(Ticket::query())->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('WEEKDAY(created_at) wd, HOUR(created_at) h, COUNT(*) n')->groupBy('wd', 'h')->get();

        return view('ops.reports', compact('slaChart', 'teams', 'orgs', 'resolution', 'origin', 'heat'));
    }
}
