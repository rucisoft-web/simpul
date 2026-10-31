@extends('layouts.app')
@section('title', 'Katalog Layanan')
@section('header')
    <h1 class="h1">Katalog Layanan</h1>
    <p class="sub">Service Family → Service → Subkategori (insiden / permintaan). Dikelompokkan per <b>value stream</b> ITIL 4.</p>
@endsection

@section('content')
<div x-data="{ view: 'family' }">
    <div class="flex gap-2 mb-4">
        <button @click="view='family'" class="btn" :class="view==='family' ? 'bg-slate-900 text-white' : 'btn-ghost'">Per Service Family</button>
        <button @click="view='vs'" class="btn" :class="view==='vs' ? 'bg-slate-900 text-white' : 'btn-ghost'">Per Value Stream</button>
    </div>
    @foreach (['family' => $services->groupBy(fn ($s) => $s->family?->name ?? 'Lainnya'), 'vs' => $valueStreams] as $mode => $groups)
        <div x-show="view==='{{ $mode }}'" @if ($mode === 'vs') x-cloak @endif class="space-y-6">
            @foreach ($groups as $group => $items)
                <div>
                    <h2 class="font-bold text-slate-700 mb-2 flex items-center gap-2">{{ $group ?: 'Lainnya' }} <span class="text-xs font-normal text-slate-400">{{ $items->count() }} layanan</span></h2>
                    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($items as $s)
                            <a href="{{ route('services.show', $s) }}" class="card p-4 hover:border-brand-300 hover:shadow transition block">
                                <div class="flex items-start gap-3">
                                    <span class="grid place-items-center w-10 h-10 rounded-xl text-white shrink-0" style="background: {{ $s->organization->color }}"><x-icon :name="$s->family->icon ?? 'grid'" class="w-5 h-5"/></span>
                                    <div class="min-w-0 flex-1"><p class="font-bold leading-5">{{ $s->name }}</p><p class="text-xs text-slate-500">{{ $s->organization->name }}</p></div>
                                    @if ($s->open_tickets)<span class="chip bg-rose-50 text-rose-600">{{ $s->open_tickets }} terbuka</span>@endif
                                </div>
                                <div class="flex flex-wrap gap-1 mt-3">
                                    @foreach ($s->subcategories as $sub)<span class="chip {{ $sub->request_type === 'incident' ? 'bg-rose-50 text-rose-700' : 'bg-sky-50 text-sky-700' }}">{{ $sub->name }}</span>@endforeach
                                </div>
                                <p class="text-[11px] text-slate-400 mt-3">{{ $s->cis_count }} CI pendukung · value stream: {{ $s->value_stream }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection
