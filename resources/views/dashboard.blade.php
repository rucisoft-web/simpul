@extends('layouts.app')
@section('title', 'Dashboard')
@section('header')
    <h1 class="h1">Dashboard Layanan TI</h1>
    <p class="sub">Ringkasan real-time Service Desk, SLA, CMDB, dan perubahan — {{ now()->translatedFormat('l, d F Y H:i') }}</p>
@endsection
@section('actions')
    <a href="{{ route('reports.index') }}" class="btn btn-ghost"><x-icon name="chart" class="w-4 h-4"/> Laporan</a>
@endsection

@section('content')
@php
    $slaPct = $slaTotal ? round($slaOk / $slaTotal * 100, 1) : 100;
    $mttrH = $mttr ? round($mttr / 60, 1) : 0;
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat label="Insiden Terbuka" :value="$openIncidents" icon="alert" tone="rose" :hint="$p1.' prioritas P1/P2'" :href="route('tickets.index', ['type' => 'incident', 'state' => 'open'])"/>
    <x-stat label="Permintaan Layanan Terbuka" :value="$openRequests" icon="inbox" tone="sky" hint="menunggu proses / persetujuan" :href="route('tickets.index', ['type' => 'request', 'state' => 'open'])"/>
    <x-stat label="Kepatuhan SLA (30 hari)" :value="$slaPct.'%'" icon="target" :tone="$slaPct >= 90 ? 'emerald' : 'amber'" :hint="$slaOk.' dari '.$slaTotal.' tiket tepat waktu'" :href="route('reports.index')"/>
    <x-stat label="MTTR Insiden" :value="$mttrH.' jam'" icon="clock" tone="violet" :hint="'CSAT '.number_format($csat ?? 0, 2).' / 5'"/>
</div>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
    <x-stat label="Configuration Items" :value="number_format($ciCount)" icon="database" tone="brand" hint="di CMDB" :href="route('cis.index')"/>
    <x-stat label="Event Monitoring Aktif" :value="$openEvents" icon="activity" tone="amber" hint="open / acknowledged" :href="route('events.index')"/>
    <x-stat label="Perubahan 2 Minggu" :value="$changesWeek" icon="calendar" tone="violet" hint="terjadwal" :href="route('changes.calendar')"/>
    <x-stat label="Tiket Melewati SLA" :value="$breaches" icon="zap" tone="rose" hint="masih terbuka" :href="route('tickets.index', ['state' => 'breach'])"/>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-3"><h2 class="font-bold">Tren Tiket 12 Bulan</h2><span class="text-xs text-slate-400">Insiden vs Permintaan Layanan</span></div>
        <div class="h-64"><canvas id="trend"></canvas></div>
    </div>
    <div class="card p-5">
        <h2 class="font-bold mb-3">Insiden per Prioritas (90 hari)</h2>
        <div class="h-64"><canvas id="prio"></canvas></div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card lg:col-span-2 overflow-hidden">
        <div class="flex items-center justify-between px-5 pt-4 pb-3"><h2 class="font-bold">Antrean Prioritas</h2>
            <a href="{{ route('tickets.index', ['state' => 'open']) }}" class="text-sm text-brand-600 font-semibold">Lihat semua →</a></div>
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr><th class="th">Ref</th><th class="th">Judul</th><th class="th">Prioritas</th><th class="th">Status</th><th class="th">SLA TTR</th><th class="th">Agen</th></tr></thead>
            <tbody>
            @foreach ($queue as $tk)
                <tr class="hover:bg-slate-50">
                    <td class="td whitespace-nowrap"><a class="font-mono text-xs font-semibold text-brand-600" href="{{ route('tickets.show', $tk) }}">{{ $tk->ref }}</a><div><x-org-dot :org="$tk->organization"/></div></td>
                    <td class="td"><a href="{{ route('tickets.show', $tk) }}" class="hover:text-brand-600 line-clamp-1">{{ $tk->title }}</a><p class="text-xs text-slate-400">{{ $tk->team?->name }}</p></td>
                    <td class="td"><x-priority :p="$tk->priority"/></td>
                    <td class="td"><x-status :status="$tk->status"/></td>
                    <td class="td whitespace-nowrap text-xs">
                        @if ($tk->ttr_deadline)
                            <span class="{{ $tk->sla_ttr_passed ? 'text-rose-600 font-semibold' : 'text-slate-600' }}">{{ $tk->sla_ttr_passed ? 'Lewat ' : '' }}{{ $tk->ttr_deadline->diffForHumans() }}</span>
                            <div class="w-24 h-1.5 bg-slate-100 rounded-full mt-1"><div class="h-1.5 rounded-full {{ $tk->sla_progress >= 100 ? 'bg-rose-500' : ($tk->sla_progress > 70 ? 'bg-amber-400' : 'bg-emerald-500') }}" style="width: {{ $tk->sla_progress }}%"></div></div>
                        @endif
                    </td>
                    <td class="td text-xs">{{ $tk->agent?->name ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    <div class="card p-5">
        <h2 class="font-bold mb-3">Volume per Organisasi (90 hari)</h2>
        <div class="space-y-3">
            @php $maxOrg = max(1, $byOrg->max()); @endphp
            @foreach ($roots as $id => $org)
                <a href="{{ route('scope', ['org' => $id]) }}" class="block group">
                    <div class="flex justify-between text-sm mb-1"><span class="font-medium group-hover:text-brand-600">{{ $org->name }}</span><span class="font-bold">{{ $byOrg[$id] ?? 0 }}</span></div>
                    <div class="h-2.5 bg-slate-100 rounded-full"><div class="h-2.5 rounded-full" style="width: {{ round(($byOrg[$id] ?? 0) / $maxOrg * 100) }}%; background: {{ $org->color }}"></div></div>
                </a>
            @endforeach
        </div>
        <h2 class="font-bold mt-6 mb-3">Perubahan Mendatang</h2>
        <ul class="space-y-2.5">
            @forelse ($upcomingChanges as $c)
                <li class="flex gap-3">
                    <div class="text-center w-11 shrink-0 rounded-lg bg-violet-50 text-violet-700 py-1"><p class="text-[10px] uppercase">{{ $c->planned_start->translatedFormat('M') }}</p><p class="font-extrabold leading-4">{{ $c->planned_start->format('d') }}</p></div>
                    <div class="min-w-0"><a href="{{ route('tickets.show', $c) }}" class="text-sm font-medium hover:text-brand-600 line-clamp-1">{{ $c->title }}</a>
                        <p class="text-xs text-slate-500">{{ ucfirst($c->change_type) }} · <x-status :status="$c->status"/></p></div>
                </li>
            @empty <li class="text-sm text-slate-400">Tidak ada.</li> @endforelse
        </ul>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card p-5">
        <h2 class="font-bold mb-3">Top Layanan (90 hari)</h2>
        <div class="h-72"><canvas id="svc"></canvas></div>
    </div>
    <div class="card p-5">
        <h2 class="font-bold mb-3">Komposisi CMDB per Kelas</h2>
        <div class="h-72"><canvas id="cls"></canvas></div>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3"><h2 class="font-bold">Multi-Merek Perangkat</h2><a href="{{ route('brands.index') }}" class="text-xs text-brand-600 font-semibold">Detail →</a></div>
        <div class="space-y-2">
            @php $maxB = max(1, $topBrands->max('n')); @endphp
            @foreach ($topBrands as $b)
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-24 truncate font-medium">{{ $b->name }}</span>
                    <div class="flex-1 h-2 bg-slate-100 rounded-full"><div class="h-2 rounded-full" style="width: {{ round($b->n / $maxB * 100) }}%; background: {{ $b->color }}"></div></div>
                    <span class="w-8 text-right font-semibold">{{ $b->n }}</span>
                </div>
            @endforeach
        </div>
        <h2 class="font-bold mt-6 mb-2">Event Kritis Terbaru</h2>
        <ul class="space-y-2 text-sm">
            @foreach ($events as $e)
                <li class="flex items-start gap-2"><span class="mt-1.5 w-2 h-2 rounded-full {{ $e->severity === 'critical' ? 'bg-rose-500' : 'bg-orange-400' }}"></span>
                    <div class="min-w-0"><p class="line-clamp-1">{{ $e->message }}</p><p class="text-xs text-slate-400">{{ $e->ci?->name }} · {{ $e->occurred_at->diffForHumans() }}</p></div></li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
Chart.defaults.color = '#64748b';
const grid = { color: '#f1f5f9' };
new Chart(document.getElementById('trend'), {
    type: 'line',
    data: { labels: @json($trend['labels']), datasets: [
        { label: 'Insiden', data: @json($trend['incident']), borderColor: '#e11d48', backgroundColor: 'rgba(225,29,72,.08)', fill: true, tension: .35 },
        { label: 'Permintaan Layanan', data: @json($trend['request']), borderColor: '#0284c7', backgroundColor: 'rgba(2,132,199,.08)', fill: true, tension: .35 },
    ]},
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { grid }, y: { grid, beginAtZero: true } } }
});
new Chart(document.getElementById('prio'), {
    type: 'doughnut',
    data: { labels: ['P1 Kritis', 'P2 Tinggi', 'P3 Sedang', 'P4 Rendah'],
        datasets: [{ data: [1,2,3,4].map(p => (@json($byPriority))[p] || 0), backgroundColor: ['#e11d48', '#f97316', '#fbbf24', '#cbd5e1'], borderWidth: 0 }] },
    options: { maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('svc'), {
    type: 'bar',
    data: { labels: @json($byService->keys()), datasets: [{ data: @json($byService->values()), backgroundColor: '#2f6bff', borderRadius: 6 }] },
    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid }, y: { grid: { display: false }, ticks: { callback: function(v) { const l = this.getLabelForValue(v); return l.length > 24 ? l.slice(0, 24) + '…' : l; } } } } }
});
new Chart(document.getElementById('cls'), {
    type: 'polarArea',
    data: { labels: @json($ciByClass->keys()), datasets: [{ data: @json($ciByClass->values()),
        backgroundColor: ['#2f6bff','#0ea5e9','#14b8a6','#22c55e','#eab308','#f97316','#ef4444','#a855f7','#ec4899','#64748b','#06b6d4','#84cc16','#f59e0b','#8b5cf6','#10b981'].map(c => c + 'cc') }] },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }, scales: { r: { ticks: { display: false } } } }
});
</script>
@endpush
