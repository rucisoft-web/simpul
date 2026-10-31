<?php

namespace App\Http\Controllers;

use App\Models\Ci;
use App\Models\Organization;
use App\Models\Person;
use App\Models\Service;
use App\Models\Team;
use App\Models\Ticket;
use App\Support\Itil;
use App\Support\Scope;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $q = Scope::apply(Ticket::query())->with(['organization', 'caller', 'agent', 'team', 'service']);
        if ($type) {
            $q->where('type', $type);
        }
        match ($request->query('state')) {
            'open' => $q->open(),
            'closed' => $q->whereIn('status', Itil::CLOSED_STATUSES),
            'breach' => $q->open()->where('sla_ttr_passed', true),
            default => null,
        };
        if ($p = $request->query('priority')) {
            $q->where('priority', $p);
        }
        if ($s = $request->query('status')) {
            $q->where('status', $s);
        }
        if ($team = $request->query('team')) {
            $q->where('team_id', $team);
        }
        if ($term = $request->query('q')) {
            $q->where(fn ($w) => $w->where('ref', 'like', "%$term%")->orWhere('title', 'like', "%$term%"));
        }
        $tickets = $q->orderByRaw("FIELD(status, 'closed', 'rejected', 'not_approved') ASC")->latest()->paginate(25)->withQueryString();

        $counts = Scope::apply(Ticket::query())->open()->selectRaw('type, COUNT(*) n')->groupBy('type')->pluck('n', 'type');
        $teams = Scope::apply(Team::query())->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'type', 'counts', 'teams'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['organization', 'caller.location', 'agent', 'team.members', 'service.family', 'subcategory', 'cis.brand', 'cis.location', 'logs',
            'parent', 'children.organization', 'events.ci', 'knownErrors']);
        $transitions = Itil::transitions($ticket->type, $ticket->status);
        $teamMembers = $ticket->team?->members ?? collect();

        return view('tickets.show', compact('ticket', 'transitions', 'teamMembers'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'incident');
        $orgs = Organization::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $orgId = Scope::rootId() ?? $orgs->first()->id;
        $services = Service::with('subcategories')->where('organization_id', $orgId)->orderBy('name')->get();
        $callers = Person::whereIn('organization_id', Organization::find($orgId)->familyIds())->orderBy('first_name')->limit(300)->get();
        $cis = Ci::where('organization_id', $orgId)->whereIn('class', ['ApplicationSolution', 'NetworkDevice', 'Server', 'VirtualMachine', 'PC', 'Printer'])
            ->orderBy('class')->orderBy('name')->get(['id', 'name', 'class']);

        return view('tickets.create', compact('type', 'orgs', 'orgId', 'services', 'callers', 'cis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:incident,request,problem,change',
            'organization_id' => 'required|exists:organizations,id',
            'caller_id' => 'nullable|exists:persons,id',
            'service_subcategory_id' => 'nullable|exists:service_subcategories,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'impact' => 'required|integer|between:1,3',
            'urgency' => 'required|integer|between:1,4',
            'ci_id' => 'nullable|exists:cis,id',
            'change_type' => 'nullable|in:standard,normal,emergency',
            'planned_start' => 'nullable|date',
        ]);
        $sub = $data['service_subcategory_id'] ? \App\Models\ServiceSubcategory::with('service')->find($data['service_subcategory_id']) : null;
        $priority = Itil::priority($data['impact'], $data['urgency']);
        $slt = Itil::DEFAULT_SLT[$priority];
        $mult = $data['type'] === 'request' ? 2 : 1;
        $now = now();
        $teamId = $sub ? Ticket::where('service_id', $sub->service_id)->whereNotNull('team_id')->value('team_id') : null;
        $teamId ??= Team::where('organization_id', $data['organization_id'])->value('id');

        $prefix = Itil::TYPES[$data['type']]['prefix'];
        $last = Ticket::where('type', $data['type'])->max('id');
        $ref = $prefix.'-'.str_pad((string) (Ticket::where('type', $data['type'])->count() + 1), 6, '0', STR_PAD_LEFT);
        while (Ticket::where('ref', $ref)->exists()) {
            $ref = $prefix.'-'.str_pad((string) (intval(substr($ref, 2)) + 1), 6, '0', STR_PAD_LEFT);
        }

        $isSla = in_array($data['type'], ['incident', 'request'], true);
        $ticket = Ticket::create([
            'ref' => $ref, 'type' => $data['type'], 'organization_id' => $data['organization_id'], 'caller_id' => $data['caller_id'] ?? null,
            'service_id' => $sub?->service_id, 'service_subcategory_id' => $sub?->id, 'team_id' => $teamId,
            'title' => $data['title'], 'description' => $data['description'] ?? null, 'impact' => $data['impact'], 'urgency' => $data['urgency'],
            'priority' => $priority, 'status' => 'new', 'origin' => 'portal',
            'tto_deadline' => $isSla ? $now->copy()->addMinutes((int) ($slt['tto'] * 60 * $mult)) : null,
            'ttr_deadline' => $isSla ? $now->copy()->addMinutes((int) ($slt['ttr'] * 60 * $mult)) : null,
            'change_type' => $data['type'] === 'change' ? ($data['change_type'] ?? 'normal') : null,
            'planned_start' => $data['planned_start'] ?? null,
            'planned_end' => isset($data['planned_start']) ? Carbon::parse($data['planned_start'])->addHours(2) : null,
        ]);
        if (! empty($data['ci_id'])) {
            $ticket->cis()->attach($data['ci_id'], ['impact_code' => 'manual']);
        }
        $ticket->logs()->create(['author' => $request->user()->name, 'kind' => 'system', 'message' => 'Tiket dibuat melalui portal. Prioritas dihitung otomatis dari matriks dampak × urgensi: '.Itil::PRIORITIES[$priority]['label'].'.', 'created_at' => $now]);

        return redirect()->route('tickets.show', $ticket)->with('status', "Tiket {$ticket->ref} berhasil dibuat.");
    }

    public function transition(Request $request, Ticket $ticket)
    {
        $action = $request->input('action');
        $allowed = Itil::transitions($ticket->type, $ticket->status);
        abort_unless(isset($allowed[$action]), 422, 'Transisi tidak valid untuk status saat ini.');

        $to = $allowed[$action];
        $now = now();
        $updates = ['status' => $to];
        if ($action === 'assign' && $request->filled('agent_id')) {
            $updates['agent_id'] = $request->input('agent_id');
        }
        if ($to === 'assigned' && ! $ticket->assigned_at) {
            $updates['assigned_at'] = $now;
            $updates['agent_id'] ??= $ticket->agent_id ?? $ticket->team?->members()->value('persons.id');
            $updates['sla_tto_passed'] = $ticket->tto_deadline && $now->gt($ticket->tto_deadline);
        }
        if ($to === 'resolved') {
            $updates['resolved_at'] = $now;
            $updates['solution'] = $request->input('note') ?: 'Diselesaikan oleh agen.';
            $updates['resolution_code'] = 'fixed';
            $updates['sla_ttr_passed'] = $ticket->ttr_deadline && $now->gt($ticket->ttr_deadline);
        }
        if ($to === 'closed') {
            $updates['closed_at'] = $now;
        }
        if ($action === 'reopen') {
            $updates['resolved_at'] = null;
        }
        $from = $ticket->status;
        $ticket->update($updates);
        $msg = 'Status: '.Itil::statusLabel($from).' → '.Itil::statusLabel($to).' ('.Itil::ACTION_LABELS[$action].').';
        if ($request->filled('note')) {
            $msg .= ' Catatan: '.$request->input('note');
        }
        $ticket->logs()->create(['author' => $request->user()->name, 'kind' => 'system', 'message' => $msg, 'created_at' => $now]);

        return back()->with('status', $msg);
    }

    public function comment(Request $request, Ticket $ticket)
    {
        $data = $request->validate(['message' => 'required|string|max:3000', 'kind' => 'required|in:public,private']);
        $ticket->logs()->create(['author' => $request->user()->name, 'kind' => $data['kind'], 'message' => $data['message'], 'created_at' => now()]);
        $ticket->touch();

        return back()->with('status', 'Catatan ditambahkan.');
    }

    public function calendar(Request $request)
    {
        $month = $request->query('month') ? Carbon::parse($request->query('month').'-01') : now()->startOfMonth();
        $start = $month->copy()->startOfMonth()->startOfWeek();
        $end = $month->copy()->endOfMonth()->endOfWeek();
        $changes = Scope::apply(Ticket::query())->where('type', 'change')->whereBetween('planned_start', [$start, $end])
            ->with('organization')->orderBy('planned_start')->get()->groupBy(fn ($c) => $c->planned_start->toDateString());
        $list = Scope::apply(Ticket::query())->where('type', 'change')->with(['organization', 'team'])->latest('planned_start')->paginate(15);
        $stats = Scope::apply(Ticket::query())->where('type', 'change')->selectRaw('change_type, COUNT(*) n')->groupBy('change_type')->pluck('n', 'change_type');

        return view('tickets.calendar', compact('month', 'start', 'end', 'changes', 'list', 'stats'));
    }
}
