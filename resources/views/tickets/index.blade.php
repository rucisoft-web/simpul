@extends('layouts.app')
@section('title', 'Service Desk')
@section('header')
    <h1 class="h1">Service Desk</h1>
    <p class="sub">Incident · Service Request · Problem · Change — satu antrean terpadu sesuai praktik ITIL 4.</p>
@endsection
@section('actions')
    <a href="{{ route('tickets.create', ['type' => $type ?: 'incident']) }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4"/> Buat Tiket</a>
@endsection

@section('content')
@php
    $tabs = ['' => ['Semua', 'grid']] + collect(\App\Support\Itil::TYPES)->map(fn ($t) => [$t['label'], null])->all();
@endphp
<div class="flex flex-wrap gap-2 mb-4">
    @foreach ($tabs as $key => [$label])
        <a href="{{ route('tickets.index', array_filter(['type' => $key, 'state' => request('state')])) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold border {{ ($type ?? '') === $key ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 hover:bg-slate-50' }}">
            {{ $label }}
            @if ($key && ($counts[$key] ?? 0))<span class="ml-1 text-[11px] {{ ($type ?? '') === $key ? 'text-slate-300' : 'text-slate-400' }}">{{ $counts[$key] }} terbuka</span>@endif
        </a>
    @endforeach
</div>

<form class="card p-3 mb-4 flex flex-wrap gap-2 items-center">
    <input type="hidden" name="type" value="{{ $type }}">
    <input name="q" value="{{ request('q') }}" placeholder="Cari ref atau judul…" class="input !w-64">
    <select name="state" class="input !w-40">
        <option value="">Semua status</option>
        <option value="open" @selected(request('state') === 'open')>Terbuka</option>
        <option value="closed" @selected(request('state') === 'closed')>Selesai / Ditutup</option>
        <option value="breach" @selected(request('state') === 'breach')>Lewat SLA</option>
    </select>
    <select name="priority" class="input !w-36">
        <option value="">Prioritas</option>
        @foreach (\App\Support\Itil::PRIORITIES as $k => $p)<option value="{{ $k }}" @selected(request('priority') == $k)>{{ $p['label'] }}</option>@endforeach
    </select>
    <select name="team" class="input !w-56">
        <option value="">Semua tim</option>
        @foreach ($teams as $tm)<option value="{{ $tm->id }}" @selected(request('team') == $tm->id)>{{ $tm->name }}</option>@endforeach
    </select>
    <button class="btn btn-ghost"><x-icon name="search" class="w-4 h-4"/> Filter</button>
    @if (request()->hasAny(['q', 'state', 'priority', 'team']))<a href="{{ route('tickets.index', ['type' => $type]) }}" class="text-sm text-slate-500 hover:underline">Reset</a>@endif
    <span class="ml-auto text-sm text-slate-500">{{ number_format($tickets->total()) }} tiket</span>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full">
        <thead><tr><th class="th">Ref</th><th class="th">Judul</th><th class="th">Tipe</th><th class="th">Prioritas</th><th class="th">Status</th><th class="th">Pelapor</th><th class="th">Tim / Agen</th><th class="th">Dibuat</th><th class="th">SLA</th></tr></thead>
        <tbody>
        @forelse ($tickets as $t)
            <tr class="hover:bg-slate-50">
                <td class="td whitespace-nowrap"><a href="{{ route('tickets.show', $t) }}" class="font-mono text-xs font-bold text-brand-600">{{ $t->ref }}</a><div><x-org-dot :org="$t->organization"/></div></td>
                <td class="td max-w-md"><a href="{{ route('tickets.show', $t) }}" class="font-medium hover:text-brand-600 line-clamp-1">{{ $t->title }}</a><p class="text-xs text-slate-400 line-clamp-1">{{ $t->service?->name }}</p></td>
                <td class="td text-xs whitespace-nowrap">{{ $t->type_label }}@if ($t->change_type)<br><span class="text-slate-400">{{ ucfirst($t->change_type) }}</span>@endif</td>
                <td class="td"><x-priority :p="$t->priority"/></td>
                <td class="td"><x-status :status="$t->status"/></td>
                <td class="td text-xs">{{ $t->caller?->name }}</td>
                <td class="td text-xs">{{ $t->team?->name }}<br><span class="text-slate-400">{{ $t->agent?->name ?? 'belum ditugaskan' }}</span></td>
                <td class="td text-xs whitespace-nowrap" title="{{ $t->created_at }}">{{ $t->created_at->diffForHumans() }}</td>
                <td class="td">
                    @if ($t->ttr_deadline)
                        @if ($t->sla_ttr_passed)<span class="chip bg-rose-100 text-rose-700">Lewat</span>
                        @elseif ($t->is_open)<span class="chip bg-sky-50 text-sky-700">{{ $t->ttr_deadline->diffForHumans(null, true) }}</span>
                        @else<span class="chip bg-emerald-50 text-emerald-700">Tercapai</span>@endif
                    @else <span class="text-slate-300">—</span> @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="td text-center text-slate-400 py-10">Tidak ada tiket.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $tickets->links() }}</div>
</div>
@endsection
