@extends('layouts.app')
@section('title', 'Merek & Model')
@section('header')
    <h1 class="h1">Merek & Model Perangkat</h1>
    <p class="sub">Tipologi Brand → Model (iTop). Lingkungan multi-vendor: {{ $brands->where('cis_count', '>', 0)->count() }} merek, {{ $brands->sum(fn ($b) => $b->models->count()) }} model.</p>
@endsection

@section('content')
<div class="card overflow-hidden mb-4">
    <div class="overflow-x-auto"><table class="w-full">
        <thead><tr><th class="th">Merek</th><th class="th">Negara</th>@foreach ($orgCodes as $c)<th class="th text-center">{{ $c }}</th>@endforeach<th class="th text-right">Total CI</th></tr></thead>
        <tbody>
        @foreach ($brands->where('cis_count', '>', 0) as $b)
            <tr class="hover:bg-slate-50">
                <td class="td"><a href="{{ route('cis.index', ['brand_id' => $b->id]) }}" class="flex items-center gap-2 font-semibold hover:text-brand-600"><span class="w-3 h-3 rounded" style="background: {{ $b->color }}"></span>{{ $b->name }}</a></td>
                <td class="td text-xs text-slate-500">{{ $b->country }}</td>
                @foreach ($orgCodes as $c)
                    @php $n = optional(($matrix[$b->id] ?? collect())->firstWhere('code', $c))->n; @endphp
                    <td class="td text-center">@if ($n)<span class="inline-block min-w-[2rem] rounded-md px-1.5 py-0.5 text-xs font-bold text-white" style="background: {{ $b->color }}; opacity: {{ min(1, .35 + $n / 60) }}">{{ $n }}</span>@else<span class="text-slate-200">·</span>@endif</td>
                @endforeach
                <td class="td text-right font-bold">{{ $b->cis_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
</div>
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
    @foreach ($brands->where('cis_count', '>', 0) as $b)
        <div class="card p-4">
            <div class="flex items-center gap-2 mb-2"><span class="grid place-items-center w-9 h-9 rounded-xl text-white font-extrabold" style="background: {{ $b->color }}">{{ mb_substr($b->name, 0, 1) }}</span>
                <div><p class="font-bold leading-4">{{ $b->name }}</p><p class="text-xs text-slate-400">{{ $b->cis_count }} perangkat</p></div></div>
            <ul class="text-sm space-y-1">
                @foreach ($b->models->sortByDesc('cis_count') as $m)
                    <li class="flex justify-between gap-2"><span class="truncate">{{ $m->name }} <span class="text-[10px] text-slate-400">{{ $m->type }}</span></span><span class="text-slate-500">{{ $m->cis_count }}</span></li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
@endsection
