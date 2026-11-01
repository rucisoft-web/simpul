<?php

namespace App\Http\Controllers;

use App\Models\Ci;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Ticket;
use App\Support\Itil;
use App\Support\Scope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $t = fn () => Scope::apply(Ticket::query());
        $since = now()->subDays(30);

        $openIncidents = $t()->where('type', 'incident')->open()->count();
        $p1 = $t()->where('type', 'incident')->open()->where('priority', '<=', 2)->count();
        $openRequests = $t()->where('type', 'request')->open()->count();
        $slaBase = $t()->whereIn('type', ['incident', 'request'])->where('created_at', '>=', $since)->whereNotNull('resolved_at');
        $slaTotal = (clone $slaBase)->count();
        $slaOk = (clone $slaBase)->where('sla_ttr_passed', false)->count();
        $mttr = (clone $slaBase)->where('type', 'incident')->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, resolved_at)'));
        $csat = $t()->whereNotNull('satisfaction')->where('created_at', '>=', $since)->avg('satisfaction');
        $changesWeek = $t()->where('type', 'change')->whereBetween('planned_start', [now()->startOfWeek(), now()->endOfWeek()->addWeek()])->count();
        $ciCount = Scope::apply(Ci::query(), 'organization_id', false)->count();
        $openEvents = Scope::apply(Event::query(), 'organization_id', false)->where('status', '!=', 'closed')->count();

        // Tren 12 bulan
        $months = collect(range(11, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $trendRaw = $t()->where('created_at', '>=', $months->first())->whereIn('type', ['incident', 'request'])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, type, COUNT(*) n")->groupBy('ym', 'type')->get();
        $trend = [
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M y'))->all(),
            'incident' => $months->map(fn ($m) => (int) ($trendRaw->where('ym', $m->format('Y-m'))->where('type', 'incident')->first()->n ?? 0))->all(),
            'request' => $months->map(fn ($m) => (int) ($trendRaw->where('ym', $m->format('Y-m'))->where('type', 'request')->first()->n ?? 0))->all(),
        ];

        $byPriority = $t()->where('type', 'incident')->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('priority, COUNT(*) n')->groupBy('priority')->pluck('n', 'priority');
        $byService = $t()->where('tickets.created_at', '>=', now()->subDays(90))->join('services', 'services.id', '=', 'tickets.service_id')
            ->selectRaw('services.name, COUNT(*) n')->groupBy('services.name')->orderByDesc('n')->limit(8)->pluck('n', 'name');
        $byOrg = Scope::apply(Ticket::query())->where('tickets.created_at', '>=', now()->subDays(90))
            ->join('organizations as o', 'o.id', '=', 'tickets.organization_id')
            ->selectRaw('COALESCE(o.parent_id, o.id) root, COUNT(*) n')->groupBy('root')->pluck('n', 'root');
        $roots = Scope::roots()->keyBy('id');
        $ciByClass = Scope::apply(Ci::query(), 'organization_id', false)->selectRaw('class, COUNT(*) n')->groupBy('class')->orderByDesc('n')->pluck('n', 'class');
        $topBrands = Scope::apply(Ci::query(), 'organization_id', false)->join('brands', 'brands.id', '=', 'cis.brand_id')
            ->selectRaw('brands.name, brands.color, COUNT(*) n')->groupBy('brands.name', 'brands.color')->orderByDesc('n')->limit(10)->get();

        $queue = $t()->open()->whereIn('type', ['incident', 'request'])->with(['organization', 'agent', 'team'])
            ->orderBy('priority')->orderBy('ttr_deadline')->limit(8)->get();
        $breaches = $t()->open()->where('sla_ttr_passed', true)->count();
        $upcomingChanges = $t()->where('type', 'change')->where('planned_start', '>=', now())->with('organization')->orderBy('planned_start')->limit(5)->get();
        $events = Scope::apply(Event::query(), 'organization_id', false)->with('ci')->whereIn('severity', ['critical', 'major'])->latest('occurred_at')->limit(6)->get();

        return view('dashboard', compact('openIncidents', 'p1', 'openRequests', 'slaTotal', 'slaOk', 'mttr', 'csat', 'changesWeek', 'ciCount', 'openEvents',
            'trend', 'byPriority', 'byService', 'byOrg', 'roots', 'ciByClass', 'topBrands', 'queue', 'breaches', 'upcomingChanges', 'events'));
    }

    public function scope(Request $request)
    {
        $org = $request->query('org');
        if (Scope::locked()) {
            // Tamu berpindah perusahaan lewat tautan "Ganti ke" (masuk ulang sebagai tamu perusahaan lain).
            $code = Organization::whereNull('parent_id')->whereKey((int) $org)->value('code');

            return $code ? redirect()->route('guest.link', ['org' => $code]) : back();
        }
        if ($org === 'all' || ! $org) {
            session()->forget('org_id');
        } else {
            session(['org_id' => (int) $org]);
        }

        return back();
    }
}
