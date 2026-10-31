@extends('layouts.app')
@section('title', 'Monitoring & Event')
@section('header')
    <h1 class="h1">Monitoring & Event Management</h1>
    <p class="sub">Alert dari Zabbix, PRTG, SolarWinds, Wazuh, PI System, dll. Event kritis dikorelasikan otomatis menjadi insiden (origin: monitoring).</p>
@endsection

@section('content')
@php $sevColors = ['critical' => 'bg-rose-600 text-white', 'major' => 'bg-orange-500 text-white', 'minor' => 'bg-amber-300 text-amber-900', 'warning' => 'bg-yellow-100 text-yellow-800', 'info' => 'bg-slate-100 text-slate-600']; @endphp
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
    @foreach ($sevColors as $s => $c)
        <a href="{{ route('events.index', ['severity' => $s]) }}" class="card p-3 hover:border-brand-300 {{ request('severity') === $s ? 'ring-2 ring-brand-500' : '' }}">
            <span class="chip {{ $c }}">{{ $s }}</span><p class="text-2xl font-extrabold mt-1">{{ $sev[$s] ?? 0 }}</p><p class="text-[11px] text-slate-400">24 jam terakhir</p></a>
    @endforeach
</div>
<div class="card p-4 mb-4"><div class="h-48"><canvas id="ev"></canvas></div></div>
<div class="card overflow-hidden">
    <div class="p-3 flex gap-2 border-b border-slate-100">
        @foreach (['' => 'Semua', 'open' => 'Open', 'acknowledged' => 'Acknowledged', 'closed' => 'Closed'] as $k => $l)
            <a href="{{ route('events.index', array_filter(['status' => $k, 'severity' => request('severity')])) }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ request('status', '') === $k ? 'bg-slate-900 text-white' : 'hover:bg-slate-50' }}">{{ $l }}</a>
        @endforeach
    </div>
    <div class="overflow-x-auto"><table class="w-full">
        <thead><tr><th class="th">Waktu</th><th class="th">Severity</th><th class="th">Pesan</th><th class="th">CI</th><th class="th">Sumber</th><th class="th">Status</th><th class="th">Insiden</th></tr></thead>
        <tbody>@foreach ($events as $e)
            <tr class="hover:bg-slate-50"><td class="td text-xs whitespace-nowrap">{{ $e->occurred_at->translatedFormat('d M H:i') }}</td><td class="td"><span class="chip {{ $sevColors[$e->severity] }}">{{ $e->severity }}</span></td>
                <td class="td text-sm">{{ $e->message }}</td>
                <td class="td text-xs">@if ($e->ci)<a href="{{ route('cis.show', $e->ci) }}" class="font-semibold hover:text-brand-600">{{ $e->ci->name }}</a><br><span class="text-slate-400">{{ $e->ci->brand?->name }}</span>@endif</td>
                <td class="td text-xs">{{ $e->source }}</td><td class="td text-xs">{{ $e->status }}</td>
                <td class="td">@if ($e->ticket)<a href="{{ route('tickets.show', $e->ticket) }}" class="font-mono text-xs font-bold text-brand-600">{{ $e->ticket->ref }}</a>@endif</td></tr>
        @endforeach</tbody>
    </table></div>
    <div class="p-4">{{ $events->links() }}</div>
</div>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const raw = @json($hourly); const days = [...new Set(raw.map(r => r.d))].sort();
const sevs = { critical: '#e11d48', major: '#f97316', minor: '#fbbf24', warning: '#fde68a', info: '#cbd5e1' };
new Chart(document.getElementById('ev'), { type: 'bar', data: { labels: days, datasets: Object.entries(sevs).map(([s, c]) => ({ label: s, backgroundColor: c, data: days.map(d => (raw.find(r => r.d === d && r.severity === s) || {}).n || 0) })) },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true } } } });
</script>
@endpush
