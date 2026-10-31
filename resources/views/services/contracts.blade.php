@extends('layouts.app')
@section('title', 'Kontrak')
@section('header')
    <h1 class="h1">Kontrak</h1>
    <p class="sub"><b>Customer contract / OLA</b> mengikat layanan + SLA ke pelanggan internal. <b>Provider contract</b> adalah kontrak vendor (dukungan, lisensi, konektivitas) — praktik Supplier Management.</p>
@endsection

@section('content')
<h2 class="font-bold mb-2">Kontrak Vendor (Provider Contract)</h2>
<div class="card overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full">
    <thead><tr><th class="th">Kontrak</th><th class="th">Vendor</th><th class="th">Tipe</th><th class="th">Periode</th><th class="th text-right">Nilai</th><th class="th">CI tercakup</th><th class="th">Status</th></tr></thead>
    <tbody>@foreach ($provider as $c)
        @php $days = now()->diffInDays($c->end_date, false); @endphp
        <tr class="hover:bg-slate-50"><td class="td font-semibold">{{ $c->name }}<div><x-org-dot :org="$c->organization"/></div></td><td class="td text-sm">{{ $c->vendor }}</td><td class="td text-xs">{{ $c->contract_type }}</td>
            <td class="td text-xs whitespace-nowrap">{{ $c->start_date->translatedFormat('d M Y') }} – {{ $c->end_date->translatedFormat('d M Y') }}</td>
            <td class="td text-right text-sm font-semibold whitespace-nowrap">Rp {{ number_format($c->cost / 1e6, 0, ',', '.') }} jt<br><span class="text-xs font-normal text-slate-400">{{ $c->billing_frequency }}</span></td>
            <td class="td text-xs">{{ $c->cis->pluck('name')->implode(', ') }}</td>
            <td class="td">@if ($days < 0)<span class="chip bg-slate-200 text-slate-600">Kedaluwarsa</span>@elseif ($days < 90)<span class="chip bg-amber-100 text-amber-700">Berakhir {{ (int) $days }} hari</span>@else<span class="chip bg-emerald-100 text-emerald-700">Aktif</span>@endif</td></tr>
    @endforeach</tbody>
</table></div></div>

<h2 class="font-bold mb-2">Kontrak Pelanggan / OLA</h2>
<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="w-full">
    <thead><tr><th class="th">Kontrak</th><th class="th">Pelanggan</th><th class="th">Penyedia</th><th class="th">SLA</th><th class="th">Layanan</th><th class="th">Periode</th></tr></thead>
    <tbody>@foreach ($customer as $c)
        <tr class="hover:bg-slate-50"><td class="td font-semibold">{{ $c->name }}</td><td class="td text-sm">{{ $c->organization->name }}</td><td class="td text-sm">{{ $c->provider?->name }}</td>
            <td class="td text-xs">{{ $c->sla?->name }}</td><td class="td text-xs max-w-sm">{{ $c->services->pluck('name')->implode(', ') }}</td>
            <td class="td text-xs whitespace-nowrap">{{ $c->start_date->format('d/m/Y') }} – {{ $c->end_date->format('d/m/Y') }}</td></tr>
    @endforeach</tbody>
</table></div></div>
@endsection
