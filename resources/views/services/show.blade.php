@extends('layouts.app')
@section('title', $service->name)
@section('header')
    <p class="text-sm text-slate-500"><a href="{{ route('services.index') }}" class="hover:underline">Katalog Layanan</a> · {{ $service->family?->name }}</p>
    <h1 class="h1">{{ $service->name }}</h1>
    <p class="sub">{{ $service->description }} Provider: {{ $service->organization->name }}. Value stream: {{ $service->value_stream }}.</p>
@endsection
@section('actions')
    <a href="{{ route('tickets.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4"/> Ajukan Tiket</a>
@endsection

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <x-stat label="Insiden (90 hari)" :value="$stats['incident']->n ?? 0" icon="alert" tone="rose"/>
    <x-stat label="Permintaan (90 hari)" :value="$stats['request']->n ?? 0" icon="inbox" tone="sky"/>
    <x-stat label="Lewat SLA" :value="($stats['incident']->breached ?? 0) + ($stats['request']->breached ?? 0)" icon="clock" tone="amber"/>
    <x-stat label="CI pendukung" :value="$service->cis->count()" icon="database"/>
</div>
<div class="grid lg:grid-cols-3 gap-4">
    <div class="card p-5">
        <h2 class="font-bold mb-2">Subkategori</h2>
        @foreach ($service->subcategories as $sub)<p class="flex justify-between text-sm py-1.5 border-b border-slate-100">{{ $sub->name }}<span class="chip {{ $sub->request_type === 'incident' ? 'bg-rose-50 text-rose-700' : 'bg-sky-50 text-sky-700' }}">{{ $sub->request_type === 'incident' ? 'insiden' : 'permintaan' }}</span></p>@endforeach
        <h2 class="font-bold mt-5 mb-2">CI pendukung</h2>
        @foreach ($service->cis as $ci)<a href="{{ route('cis.show', $ci) }}" class="flex items-center gap-2 text-sm py-1 hover:text-brand-600"><x-icon :name="$ci->icon" class="w-4 h-4 text-slate-400"/>{{ $ci->name }}</a>@endforeach
    </div>
    <div class="card p-5">
        <h2 class="font-bold mb-2">Kontrak & SLA</h2>
        @foreach ($service->contracts as $c)
            <div class="py-2 border-b border-slate-100 text-sm"><p class="font-semibold">{{ $c->name }}</p><p class="text-xs text-slate-500">Pelanggan: {{ $c->organization->name }} · {{ $c->sla?->name }}</p></div>
        @endforeach
    </div>
    <div class="card p-5">
        <h2 class="font-bold mb-2">Tiket terbaru</h2>
        @foreach ($recent as $t)
            <a href="{{ route('tickets.show', $t) }}" class="block py-1.5 border-b border-slate-100 text-sm hover:text-brand-600"><span class="font-mono text-xs font-bold text-brand-600">{{ $t->ref }}</span> {{ \Illuminate\Support\Str::limit($t->title, 48) }} <x-status :status="$t->status"/></a>
        @endforeach
    </div>
</div>
@endsection
