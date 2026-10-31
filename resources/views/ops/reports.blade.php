@extends('layouts.app')
@section('title', 'Laporan & SLA')
@section('header')
    <h1 class="h1">Laporan Kinerja Layanan</h1>
    <p class="sub">Measurement & Reporting: kepatuhan SLA, MTTR, kinerja tim, kanal masuk, dan pola beban kerja.</p>
@endsection

@section('content')
<div class="grid lg:grid-cols-3 gap-4 mb-4">
    @foreach ($orgs as $o)
        @php $pct = $o->stat->n ? round($o->stat->ok / $o->stat->n * 100, 1) : 0; @endphp
        <div class="card p-5">
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background: {{ $o->color }}"></span><p class="font-bold">{{ $o->name }}</p></div>
            <div class="flex items-end gap-4 mt-3">
                <div><p class="text-3xl font-extrabold {{ $pct >= 90 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $pct }}%</p><p class="text-xs text-slate-500">SLA TTR 90 hari</p></div>
                <div><p class="text-xl font-bold">{{ number_format($o->stat->csat ?? 0, 2) }}</p><p class="text-xs text-slate-500">CSAT / 5</p></div>
                <div><p class="text-xl font-bold">{{ number_format($o->stat->n) }}</p><p class="text-xs text-slate-500">tiket selesai</p></div>
            </div>
        </div>
    @endforeach
</div>
<div class="grid lg:grid-cols-2 gap-4 mb-4">
    <div class="card p-5"><h2 class="font-bold mb-2">Kepatuhan SLA bulanan (%)</h2><div class="h-64"><canvas id="sla"></canvas></div></div>
    <div class="card p-5"><h2 class="font-bold mb-2">MTTR bulanan (jam)</h2><div class="h-64"><canvas id="mttr"></canvas></div></div>
</div>
<div class="grid lg:grid-cols-3 gap-4 mb-4">
    <div class="card p-5"><h2 class="font-bold mb-2">Kanal masuk tiket</h2><div class="h-56"><canvas id="origin"></canvas></div></div>
    <div class="card p-5"><h2 class="font-bold mb-2">Kode resolusi</h2><div class="h-56"><canvas id="res"></canvas></div></div>
    <div class="card p-5">
        <h2 class="font-bold mb-2">Heatmap beban (hari × jam)</h2>
        @php $max = max(1, $heat->max('n')); $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']; @endphp
        <div class="space-y-0.5">
            @foreach ($days as $wd => $dn)
                <div class="flex items-center gap-0.5"><span class="w-7 text-[10px] text-slate-500">{{ $dn }}</span>
                    @for ($h = 0; $h < 24; $h++)
                        @php $n = optional($heat->first(fn ($r) => $r->wd == $wd && $r->h == $h))->n ?? 0; @endphp
                        <span title="{{ $dn }} {{ $h }}:00 — {{ $n }} tiket" class="flex-1 h-4 rounded-sm" style="background: rgba(47,107,255,{{ round(0.06 + $n / $max * 0.94, 2) }})"></span>
                    @endfor
                </div>
            @endforeach
            <div class="flex justify-between text-[10px] text-slate-400 pl-7"><span>00</span><span>06</span><span>12</span><span>18</span><span>23</span></div>
        </div>
    </div>
</div>
<div class="card overflow-hidden">
    <h2 class="font-bold px-5 pt-4 pb-2">Kinerja tim (90 hari)</h2>
    <div class="overflow-x-auto"><table class="w-full">
        <thead><tr><th class="th">Tim</th><th class="th">Organisasi</th><th class="th text-right">Tiket selesai</th><th class="th">SLA TTR</th><th class="th text-right">MTTR</th><th class="th text-right">CSAT</th></tr></thead>
        <tbody>@foreach ($teams as $t)
            @php $pct = round($t->stat->ok / max(1, $t->stat->n) * 100, 1); @endphp
            <tr><td class="td font-semibold">{{ $t->name }}</td><td class="td"><x-org-dot :org="$t->organization"/></td><td class="td text-right">{{ $t->stat->n }}</td>
                <td class="td"><div class="flex items-center gap-2"><div class="w-28 h-2 bg-slate-100 rounded-full"><div class="h-2 rounded-full {{ $pct >= 90 ? 'bg-emerald-500' : 'bg-amber-400' }}" style="width: {{ $pct }}%"></div></div><span class="text-xs font-semibold">{{ $pct }}%</span></div></td>
                <td class="td text-right text-sm">{{ round($t->stat->mttr / 60, 1) }} jam</td><td class="td text-right text-sm">{{ number_format($t->stat->csat ?? 0, 2) }}</td></tr>
        @endforeach</tbody>
    </table></div>
</div>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
const L = @json($slaChart['labels']);
new Chart(document.getElementById('sla'), { type: 'line', data: { labels: L, datasets: [
    { label: 'TTR tercapai', data: @json($slaChart['ttr']), borderColor: '#059669', tension: .3 },
    { label: 'TTO tercapai', data: @json($slaChart['tto']), borderColor: '#2f6bff', tension: .3 },
    { label: 'Target 90%', data: L.map(() => 90), borderColor: '#e11d48', borderDash: [6, 4], pointRadius: 0 } ] },
    options: { maintainAspectRatio: false, scales: { y: { min: 60, max: 100 } }, plugins: { legend: { position: 'bottom' } } } });
new Chart(document.getElementById('mttr'), { type: 'bar', data: { labels: L, datasets: [{ label: 'MTTR (jam)', data: @json($slaChart['mttr']), backgroundColor: '#7c3aed', borderRadius: 6 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } } } });
const pal = ['#2f6bff', '#0ea5e9', '#14b8a6', '#f59e0b', '#ef4444', '#a855f7', '#64748b'];
new Chart(document.getElementById('origin'), { type: 'doughnut', data: { labels: @json($origin->keys()), datasets: [{ data: @json($origin->values()), backgroundColor: pal, borderWidth: 0 }] }, options: { maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'right' } } } });
new Chart(document.getElementById('res'), { type: 'pie', data: { labels: @json($resolution->keys()), datasets: [{ data: @json($resolution->values()), backgroundColor: pal, borderWidth: 0 }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } } });
</script>
@endpush
