@extends('layouts.app')
@section('title', 'Kontak & Tim')
@section('header')
    <h1 class="h1">Kontak & Tim</h1>
    <p class="sub">Person (pelapor, agen, manajer) dan Team (grup penugasan). Satu orang dapat menjadi anggota beberapa tim dengan peran berbeda.</p>
@endsection

@section('content')
<h2 class="font-bold mb-2">Tim Pendukung ({{ $teams->count() }})</h2>
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @foreach ($teams as $t)
        <div class="card p-4">
            <div class="flex items-start gap-2"><div class="flex-1"><p class="font-bold leading-5">{{ $t->name }}</p><p class="text-xs text-slate-500">{{ $t->function }}</p></div><x-org-dot :org="$t->organization"/></div>
            <div class="flex -space-x-2 mt-3">
                @foreach ($t->members->take(6) as $m)<span title="{{ $m->name }} ({{ $m->pivot->role }})" class="grid place-items-center w-8 h-8 rounded-full border-2 border-white text-[11px] font-bold text-white" style="background: hsl({{ crc32($m->name) % 360 }} 60% 50%)">{{ $m->initials }}</span>@endforeach
            </div>
            <div class="flex justify-between text-xs mt-3"><span class="text-slate-500">{{ $t->members->count() }} anggota</span>
                <a href="{{ route('tickets.index', ['team' => $t->id, 'state' => 'open']) }}" class="font-semibold text-brand-600">{{ $t->open_tickets }} tiket terbuka</a></div>
        </div>
    @endforeach
</div>

<div class="flex items-center mb-2"><h2 class="font-bold flex-1">Orang ({{ number_format($persons->total()) }})</h2>
    <form><input name="q" value="{{ request('q') }}" placeholder="Cari nama / jabatan…" class="input !w-64"></form></div>
<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="w-full">
    <thead><tr><th class="th">Nama</th><th class="th">Jabatan</th><th class="th">Organisasi</th><th class="th">Lokasi</th><th class="th">Kontak</th><th class="th">Tim</th></tr></thead>
    <tbody>@foreach ($persons as $p)
        <tr class="hover:bg-slate-50"><td class="td"><div class="flex items-center gap-2"><span class="grid place-items-center w-8 h-8 rounded-full text-[11px] font-bold text-white" style="background: hsl({{ crc32($p->name) % 360 }} 60% 50%)">{{ $p->initials }}</span><span class="font-semibold">{{ $p->name }}</span></div></td>
            <td class="td text-sm">{{ $p->function }}</td><td class="td text-xs">{{ $p->organization->name }}</td><td class="td text-xs">{{ $p->location?->name }}</td>
            <td class="td text-xs">{{ $p->email }}<br><span class="text-slate-400">{{ $p->phone }}</span></td><td class="td text-xs">{{ $p->teams->pluck('name')->implode(', ') }}</td></tr>
    @endforeach</tbody>
</table></div><div class="p-4">{{ $persons->links() }}</div></div>
@endsection
