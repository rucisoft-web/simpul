@extends('layouts.app')
@section('title', 'Organisasi')
@section('header')
    <h1 class="h1">Organisasi</h1>
    <p class="sub">Organisasi adalah pemilik data (multi-tenant). Entitas anak mewarisi layanan induk melalui kontrak OLA.</p>
@endsection

@section('content')
<div class="grid lg:grid-cols-3 gap-4">
    @foreach ($orgs as $o)
        <div class="card overflow-hidden">
            <div class="p-5 text-white" style="background: linear-gradient(135deg, {{ $o->color }}, #0b1a44)">
                <p class="text-xs opacity-80">{{ $o->industry }}</p><p class="text-xl font-extrabold">{{ $o->name }}</p>
                <a href="{{ $o->website }}" target="_blank" rel="noopener" class="text-xs underline opacity-80">{{ $o->website }}</a>
            </div>
            <div class="p-5">
                <p class="text-sm text-slate-600">{{ $o->description }}</p>
                <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                    @foreach (['Lokasi' => $o->locations_count, 'Orang' => $o->persons_count, 'Tim' => $o->teams_count, 'CI' => $o->cis_count, 'Layanan' => $o->services_count, 'Tiket' => $o->tickets_count] as $k => $v)
                        <div class="rounded-xl bg-slate-50 py-2"><p class="text-lg font-extrabold">{{ number_format($v) }}</p><p class="text-[11px] text-slate-500">{{ $k }}</p></div>
                    @endforeach
                </div>
                @if ($o->children->count())
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mt-4 mb-1">Entitas anak</p>
                    @foreach ($o->children as $c)<p class="flex justify-between text-sm py-1 border-b border-slate-100">{{ $c->name }}<span class="text-xs text-slate-400">{{ $c->industry }} · {{ $c->tickets_count }} tiket</span></p>@endforeach
                @endif
                <a href="{{ route('scope', ['org' => $o->id]) }}" class="btn btn-ghost w-full justify-center mt-4">Fokus ke organisasi ini</a>
            </div>
        </div>
    @endforeach
</div>
<p class="text-xs text-slate-400 mt-4">Profil organisasi bersumber dari Laporan Tahunan 2025 PT Blue Bird Tbk & PT Medco Energi Internasional Tbk serta situs uin-suka.ac.id. Perangkat, IP, nama orang, dan tiket adalah data simulasi.</p>
@endsection
