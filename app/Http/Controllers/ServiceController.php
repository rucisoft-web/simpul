<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Service;
use App\Models\ServiceFamily;
use App\Models\Sla;
use App\Models\Ticket;
use App\Support\Scope;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Scope::apply(Service::query(), 'organization_id', false)->with(['organization', 'family', 'subcategories'])
            ->withCount(['tickets as open_tickets' => fn ($q) => $q->open()])->withCount('cis')->orderBy('name')->get();
        $families = ServiceFamily::orderBy('name')->get();
        $valueStreams = $services->groupBy('value_stream');

        return view('services.index', compact('services', 'families', 'valueStreams'));
    }

    public function show(Service $service)
    {
        $service->load(['organization', 'family', 'subcategories', 'cis.brand', 'contracts.sla.slts', 'contracts.organization']);
        $stats = Ticket::where('service_id', $service->id)->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('type, COUNT(*) n, SUM(sla_ttr_passed) breached')->groupBy('type')->get()->keyBy('type');
        $recent = Ticket::where('service_id', $service->id)->with('organization')->latest()->limit(10)->get();

        return view('services.show', compact('service', 'stats', 'recent'));
    }

    public function slas()
    {
        $slas = Scope::apply(Sla::query(), 'organization_id', false)->with(['organization', 'slts', 'contracts.organization'])->get();

        return view('services.slas', compact('slas'));
    }

    public function contracts()
    {
        $q = fn () => Scope::apply(Contract::query())->with(['organization', 'provider', 'sla', 'services', 'cis']);
        $customer = $q()->where('kind', 'customer')->orderBy('name')->get();
        $provider = $q()->where('kind', 'provider')->orderBy('end_date')->get();

        return view('services.contracts', compact('customer', 'provider'));
    }
}
