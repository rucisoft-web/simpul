@extends('layouts.app')
@section('title', 'Kalender Perubahan')
@section('header')
    <h1 class="h1">Kalender Perubahan (Change Enablement)</h1>
    <p class="sub">Jadwal Standard, Normal, dan Emergency change — membantu CAB menghindari bentrok dan periode rawan.</p>
@endsection
@section('actions')
    <a href="{{ route('tickets.create', ['type' => 'change']) }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4"/> Ajukan Perubahan</a>
@endsection

@section('content')
@php $colors = ['standard' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'normal' => 'bg-violet-100 text-violet-800 border-violet-300', 'emergency' => 'bg-rose-100 text-rose-800 border-rose-300']; @endphp
<div class="grid grid-cols-3 gap-4 mb-4">
    @foreach (['standard' => 'Standard change', 'normal' => 'Normal change', 'emergency' => 'Emergency change'] as $k => $l)
        <div class="card p-4 flex items-center gap-3"><span class="w-3 h-3 rounded-full {{ explode(' ', $colors[$k])[0] }} border {{ explode(' ', $colors[$k])[2] }}"></span><span class="text-sm">{{ $l }}</span><span class="ml-auto font-extrabold text-lg">{{ $stats[$k] ?? 0 }}</span></div>
    @endforeach
</div>
<div class="card p-4">
    <div class="flex items-center justify-between mb-3">
        <a href="{{ route('changes.calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-ghost">← {{ $month->copy()->subMonth()->translatedFormat('M') }}</a>
        <h2 class="font-bold text-lg">{{ $month->translatedFormat('F Y') }}</h2>
        <a href="{{ route('changes.calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-ghost">{{ $month->copy()->addMonth()->translatedFormat('M') }} →</a>
    </div>
    <div class="grid grid-cols-7 text-center text-xs font-semibold text-slate-500 mb-1">@foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)<div class="py-1">{{ $d }}</div>@endforeach</div>
    <div class="grid grid-cols-7 gap-px bg-slate-200 rounded-xl overflow-hidden">
        @for ($d = $start->copy(); $d <= $end; $d->addDay())
            <div class="bg-white min-h-[96px] p-1.5 {{ $d->month !== $month->month ? 'bg-slate-50 text-slate-300' : '' }} {{ $d->isToday() ? 'ring-2 ring-inset ring-brand-500' : '' }}">
                <p class="text-xs font-semibold">{{ $d->day }}</p>
                @foreach ($changes[$d->toDateString()] ?? [] as $c)
                    <a href="{{ route('tickets.show', $c) }}" title="{{ $c->title }}" class="block mt-1 text-[10px] leading-tight px-1.5 py-1 rounded border-l-2 truncate {{ $colors[$c->change_type] ?? '' }}">
                        {{ $c->planned_start->format('H:i') }} {{ $c->title }}</a>
                @endforeach
            </div>
        @endfor
    </div>
</div>

<div class="card mt-4 overflow-hidden">
    <h2 class="font-bold px-5 pt-4 pb-2">Daftar Perubahan</h2>
    <div class="overflow-x-auto"><table class="w-full">
        <thead><tr><th class="th">Ref</th><th class="th">Judul</th><th class="th">Jenis</th><th class="th">Risiko</th><th class="th">Jadwal</th><th class="th">Status</th></tr></thead>
        <tbody>@foreach ($list as $c)
            <tr class="hover:bg-slate-50"><td class="td"><a href="{{ route('tickets.show', $c) }}" class="font-mono text-xs font-bold text-brand-600">{{ $c->ref }}</a><div><x-org-dot :org="$c->organization"/></div></td>
                <td class="td">{{ $c->title }}</td><td class="td"><span class="chip border {{ $colors[$c->change_type] ?? '' }}">{{ ucfirst($c->change_type) }}</span></td>
                <td class="td text-sm">{{ ucfirst($c->risk) }}{{ $c->outage ? ' · downtime' : '' }}</td>
                <td class="td text-xs whitespace-nowrap">{{ $c->planned_start?->translatedFormat('d M Y H:i') }}</td><td class="td"><x-status :status="$c->status"/></td></tr>
        @endforeach</tbody>
    </table></div>
    <div class="p-4">{{ $list->links() }}</div>
</div>
@endsection
