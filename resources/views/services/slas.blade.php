@extends('layouts.app')
@section('title', 'SLA & SLT')
@section('header')
    <h1 class="h1">Service Level Agreement</h1>
    <p class="sub">SLA terdiri dari beberapa SLT (Service Level Target): metrik <b>TTO</b> (Time To Own/respons) dan <b>TTR</b> (Time To Resolve) per prioritas dan tipe permintaan.</p>
@endsection

@section('content')
<div class="grid lg:grid-cols-2 gap-4">
    @foreach ($slas as $sla)
        <div class="card overflow-hidden">
            <div class="p-4 flex items-center gap-2 border-b border-slate-100"><span class="w-3 h-3 rounded-full" style="background: {{ $sla->organization->color }}"></span>
                <div class="flex-1"><p class="font-bold">{{ $sla->name }}</p><p class="text-xs text-slate-500">{{ $sla->organization->name }} · {{ $sla->description }}</p></div>
                <span class="text-xs text-slate-400">{{ $sla->contracts->count() }} kontrak</span></div>
            <table class="w-full">
                <thead><tr><th class="th">Prioritas</th><th class="th">Insiden TTO</th><th class="th">Insiden TTR</th><th class="th">Permintaan TTO</th><th class="th">Permintaan TTR</th></tr></thead>
                <tbody>
                @foreach ([1, 2, 3, 4] as $p)
                    @php $f = fn ($rt, $m) => optional($sla->slts->first(fn ($s) => $s->priority == $p && $s->request_type == $rt && $s->metric == $m)); @endphp
                    <tr><td class="td"><x-priority :p="$p"/></td>
                        @foreach ([['incident', 'tto'], ['incident', 'ttr'], ['service_request', 'tto'], ['service_request', 'ttr']] as [$rt, $m])
                            @php $s = $f($rt, $m); @endphp
                            <td class="td text-sm font-semibold">{{ $s->value }} <span class="text-xs font-normal text-slate-400">{{ $s->unit === 'minutes' ? 'menit' : 'jam' }}</span></td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endsection
