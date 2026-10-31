@extends('layouts.app')
@section('title', 'Configuration Items')
@section('header')
    <h1 class="h1">Configuration Management Database</h1>
    <p class="sub">Seluruh Configuration Item (CI) mengikuti data model iTop: Physical Device, Virtualization, Software Instance, Application & Business Process.</p>
@endsection
@section('actions')
    <a href="{{ route('topology.index') }}" class="btn btn-ghost"><x-icon name="network" class="w-4 h-4"/> Lihat Topologi</a>
@endsection

@section('content')
<div class="grid lg:grid-cols-[260px_1fr] gap-4">
    <div class="card p-3 h-max">
        <a href="{{ route('cis.index') }}" class="flex justify-between px-3 py-2 rounded-lg text-sm font-semibold {{ !request('class') ? 'bg-brand-50 text-brand-700' : 'hover:bg-slate-50' }}">Semua CI <span>{{ $classCounts->sum() }}</span></a>
        @foreach (\App\Models\Ci::CLASSES as $group => $classes)
            <p class="px-3 mt-3 mb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">{{ $group }}</p>
            @foreach ($classes as $class => $label)
                @if ($classCounts[$class] ?? 0)
                <a href="{{ route('cis.index', ['class' => $class]) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm {{ request('class') === $class ? 'bg-brand-50 text-brand-700 font-semibold' : 'hover:bg-slate-50' }}">
                    <x-icon :name="\App\Models\Ci::ICONS[$class] ?? 'box'" class="w-4 h-4 text-slate-400"/><span class="flex-1">{{ $label }}</span><span class="text-xs text-slate-400">{{ $classCounts[$class] }}</span></a>
                @endif
            @endforeach
        @endforeach
    </div>

    <div>
        <form class="card p-3 mb-4 flex flex-wrap gap-2 items-center">
            <input type="hidden" name="class" value="{{ request('class') }}">
            <input name="q" value="{{ request('q') }}" class="input !w-60" placeholder="Nama, serial, atau IP…">
            <select name="brand_id" class="input !w-44"><option value="">Semua merek</option>@foreach ($brands as $b)<option value="{{ $b->id }}" @selected(request('brand_id') == $b->id)>{{ $b->name }}</option>@endforeach</select>
            <select name="status" class="input !w-40"><option value="">Semua status</option>@foreach (['production', 'implementation', 'stock', 'obsolete'] as $s)<option @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select>
            <select name="location_id" class="input !w-56"><option value="">Semua lokasi</option>@foreach ($locations as $l)<option value="{{ $l->id }}" @selected(request('location_id') == $l->id)>{{ $l->name }}</option>@endforeach</select>
            <button class="btn btn-ghost"><x-icon name="search" class="w-4 h-4"/> Filter</button>
            <span class="ml-auto text-sm text-slate-500">{{ number_format($cis->total()) }} CI</span>
        </form>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto"><table class="w-full">
                <thead><tr><th class="th">Nama</th><th class="th">Kelas</th><th class="th">Merek / Model</th><th class="th">IP Mgmt</th><th class="th">Lokasi</th><th class="th">Kritikalitas</th><th class="th">Status</th></tr></thead>
                <tbody>
                @foreach ($cis as $ci)
                    <tr class="hover:bg-slate-50">
                        <td class="td"><a href="{{ route('cis.show', $ci) }}" class="flex items-center gap-2.5 font-semibold hover:text-brand-600">
                            <span class="grid place-items-center w-8 h-8 rounded-lg bg-slate-100 text-slate-500 shrink-0"><x-icon :name="$ci->icon" class="w-4 h-4"/></span><span class="truncate max-w-[260px]">{{ $ci->name }}</span></a></td>
                        <td class="td text-xs">{{ \App\Models\Ci::classLabel($ci->class) }}@if ($ci->network_role)<br><span class="text-slate-400">{{ $ci->network_role }}</span>@endif</td>
                        <td class="td text-xs">@if ($ci->brand)<span class="inline-flex items-center gap-1.5 font-semibold"><span class="w-2 h-2 rounded-full" style="background: {{ $ci->brand->color }}"></span>{{ $ci->brand->name }}</span><br><span class="text-slate-400">{{ $ci->deviceModel?->name }}</span>@else — @endif</td>
                        <td class="td font-mono text-xs">{{ $ci->management_ip ?? '—' }}</td>
                        <td class="td text-xs">{{ $ci->location?->name }}<div><x-org-dot :org="$ci->organization"/></div></td>
                        <td class="td"><span class="chip {{ ['high' => 'bg-rose-100 text-rose-700', 'medium' => 'bg-amber-100 text-amber-700', 'low' => 'bg-slate-100 text-slate-600'][$ci->business_criticity] }}">{{ $ci->business_criticity }}</span></td>
                        <td class="td"><span class="chip {{ $ci->status === 'production' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $ci->status }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="p-4 border-t border-slate-100">{{ $cis->links() }}</div>
        </div>
    </div>
</div>
@endsection
