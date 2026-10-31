@props(['label', 'value', 'icon' => 'activity', 'tone' => 'brand', 'hint' => null, 'href' => null])
@php
    $tones = ['brand' => 'bg-brand-50 text-brand-600', 'rose' => 'bg-rose-50 text-rose-600', 'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600', 'violet' => 'bg-violet-50 text-violet-600', 'sky' => 'bg-sky-50 text-sky-600'];
@endphp
<{{ $href ? 'a href='.$href : 'div' }} class="card p-4 flex items-start gap-3 {{ $href ? 'hover:border-brand-300 hover:shadow transition' : '' }}">
    <span class="grid place-items-center w-11 h-11 rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}"><x-icon :name="$icon"/></span>
    <div class="min-w-0">
        <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
        <p class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $value }}</p>
        @if ($hint)<p class="text-[11px] text-slate-400 mt-0.5">{{ $hint }}</p>@endif
    </div>
</{{ $href ? 'a' : 'div' }}>
