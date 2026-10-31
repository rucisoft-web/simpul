@extends('layouts.app')
@section('title', 'Peta Praktik ITIL 4')
@section('header')
    <h1 class="h1">ITIL 4 Service Value System</h1>
    <p class="sub">Bagaimana Simpul memetakan data model iTop ke Service Value Chain dan 34 praktik ITIL 4.</p>
@endsection

@section('content')
{{-- Service Value Chain --}}
<div class="card p-6 mb-4">
    <h2 class="font-bold mb-4">Service Value Chain</h2>
    <div class="grid md:grid-cols-[1fr_auto] gap-6 items-center">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach ([
                ['Plan', 'Strategi, portofolio, CIR', 'improvements.index', 'target'],
                ['Improve', 'Continual improvement, laporan', 'reports.index', 'trending'],
                ['Engage', 'Portal, service desk, kontak', 'tickets.index', 'users'],
                ['Design & Transition', 'Katalog layanan, change, rilis', 'changes.calendar', 'layers'],
                ['Obtain / Build', 'CMDB, aset, kontrak vendor', 'cis.index', 'database'],
                ['Deliver & Support', 'Insiden, permintaan, problem, event', 'events.index', 'activity'],
            ] as [$t, $d, $r, $ic])
                <a href="{{ route($r) }}" class="rounded-2xl border border-slate-200 p-4 hover:border-brand-400 hover:bg-brand-50/40 transition">
                    <x-icon :name="$ic" class="w-6 h-6 text-brand-600"/><p class="font-bold mt-2">{{ $t }}</p><p class="text-xs text-slate-500">{{ $d }}</p></a>
            @endforeach
        </div>
        <div class="rounded-2xl bg-[#0b1a44] text-white p-5 w-full md:w-64">
            <p class="text-xs text-sky-300">Opportunity / Demand →</p>
            <p class="text-2xl font-extrabold my-2">Value</p>
            <p class="text-xs text-slate-300">Produk & layanan yang dikonsumsi pengguna: {{ number_format(array_sum($counts)) }} tiket tercatat menunjukkan alur nilai dari permintaan hingga penyelesaian.</p>
        </div>
    </div>
    <div class="mt-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">7 Guiding Principles</p>
        <div class="flex flex-wrap gap-2">@foreach (\App\Support\Itil::GUIDING_PRINCIPLES as $g)<span class="chip bg-slate-100 text-slate-700 !text-xs !px-3 !py-1">{{ $g }}</span>@endforeach</div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    @foreach (\App\Support\Itil::PRACTICES as $group => $practices)
        <div class="card p-5">
            <h2 class="font-bold mb-1">{{ $group }}</h2>
            <p class="text-xs text-slate-500 mb-3">{{ collect($practices)->filter(fn ($p) => $p[1])->count() }} dari {{ count($practices) }} didukung modul</p>
            <ul class="space-y-1">
                @foreach ($practices as [$name, $route])
                    @php [$r, $type] = array_pad(explode(':', (string) $route), 2, null); @endphp
                    <li>@if ($route)
                        <a href="{{ route($r, $type ? ['type' => $type] : []) }}" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 text-sm"><span class="grid place-items-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-600"><x-icon name="check" class="w-3 h-3"/></span>{{ $name }}<x-icon name="chevron" class="w-3 h-3 ml-auto text-slate-300"/></a>
                    @else
                        <span class="flex items-center gap-2 px-2 py-1.5 text-sm text-slate-400"><span class="w-5 h-5 rounded-full border-2 border-dashed border-slate-200"></span>{{ $name }}</span>
                    @endif</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>

<div class="card p-5 mt-4 overflow-x-auto">
    <h2 class="font-bold mb-3">Pemetaan Data Model iTop → ITIL 4</h2>
    <table class="w-full text-sm">
        <thead><tr><th class="th">Modul iTop</th><th class="th">Kelas utama</th><th class="th">Praktik ITIL 4</th><th class="th">Layar</th></tr></thead>
        <tbody>
        @foreach ([
            ['itop-config-mgmt', 'Organization, Location, Person, Team, FunctionalCI, NetworkDevice, Server, PC, ApplicationSolution, BusinessProcess', 'Service Configuration Mgmt, IT Asset Mgmt', 'cis.index'],
            ['itop-datacenter-mgmt', 'Rack, Enclosure, PowerSource, PhysicalInterface', 'Infrastructure & Platform Mgmt', 'topology.index'],
            ['itop-virtualization-mgmt', 'Hypervisor, VirtualMachine', 'Infrastructure & Platform Mgmt', 'cis.index'],
            ['itop-service-mgmt', 'ServiceFamily, Service, ServiceSubcategory, SLA, SLT, CustomerContract, ProviderContract', 'Service Catalogue, Service Level, Supplier Mgmt', 'services.index'],
            ['itop-incident-mgmt-itil', 'Incident (TTO/TTR, eskalasi)', 'Incident Management', 'tickets.index'],
            ['itop-request-mgmt-itil', 'UserRequest (persetujuan)', 'Service Request Management', 'tickets.index'],
            ['itop-problem-mgmt', 'Problem', 'Problem Management', 'tickets.index'],
            ['itop-change-mgmt-itil', 'RoutineChange, NormalChange, EmergencyChange', 'Change Enablement', 'changes.calendar'],
            ['itop-knowledge-mgmt / faq', 'KnownError, FAQ, Document', 'Knowledge Management', 'knowledge.index'],
            ['(ekstensi Simpul)', 'Event, Improvement', 'Monitoring & Event Mgmt, Continual Improvement', 'events.index'],
        ] as [$m, $c, $p, $r])
            <tr><td class="td font-mono text-xs">{{ $m }}</td><td class="td text-xs">{{ $c }}</td><td class="td text-xs font-semibold">{{ $p }}</td><td class="td"><a href="{{ route($r) }}" class="text-brand-600 text-xs font-semibold">Buka →</a></td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
