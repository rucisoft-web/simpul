@props(['org'])
@if ($org)
<span class="inline-flex items-center gap-1.5 text-xs text-slate-600 whitespace-nowrap"><span class="w-2 h-2 rounded-full" style="background: {{ $org->color }}"></span>{{ $org->code }}</span>
@endif
