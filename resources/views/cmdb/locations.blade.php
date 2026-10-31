@extends('layouts.app')
@section('title', 'Lokasi')
@section('header')
    <h1 class="h1">Lokasi</h1>
    <p class="sub">Hierarki lokasi: kantor pusat/kampus → data center, wilayah, gedung → pool/site. Setiap CI dan orang terhubung ke lokasi.</p>
@endsection

@section('content')
@php
    $icons = ['hq' => '#0b1a44', 'campus' => '#0b1a44', 'datacenter' => '#7c3aed', 'region' => '#2563eb', 'office' => '#0891b2', 'building' => '#0891b2', 'pool' => '#059669', 'site' => '#059669'];
    $render = function ($parentId, $orgLocs, $lvl = 0) use (&$render, $icons) {
        $out = '';
        foreach ($orgLocs->where('parent_id', $parentId)->sortBy('name') as $l) {
            $kids = $render($l->id, $orgLocs, $lvl + 1);
            $out .= '<li><div class="flex items-center gap-3 py-2 px-3 rounded-xl hover:bg-slate-50">'
                .'<span class="grid place-items-center w-8 h-8 rounded-lg text-white text-[10px] font-bold uppercase" style="background:'.($icons[$l->type] ?? '#64748b').'">'.e(mb_substr($l->type, 0, 2)).'</span>'
                .'<div class="flex-1 min-w-0"><p class="font-semibold text-sm">'.e($l->name).' <span class="chip bg-slate-100 text-slate-500 ml-1">'.e($l->type).'</span></p>'
                .'<p class="text-xs text-slate-500">'.e(trim(($l->address ? $l->address.', ' : '').$l->city.', '.$l->country, ', ')).'</p></div>'
                .'<a href="'.route('cis.index', ['location_id' => $l->id]).'" class="text-xs font-semibold text-brand-600 whitespace-nowrap">'.$l->cis_count.' CI</a>'
                .'<span class="text-xs text-slate-400 w-16 text-right">'.$l->persons_count.' orang</span></div>';
            if ($kids) $out .= '<ul class="ml-7 border-l-2 border-slate-100 pl-2">'.$kids.'</ul>';
            $out .= '</li>';
        }
        return $out;
    };
@endphp
<div class="grid xl:grid-cols-{{ min(3, $orgs->count()) }} gap-4">
    @foreach ($orgs as $org)
        <div class="card p-4">
            <div class="flex items-center gap-2 mb-2"><span class="w-3 h-3 rounded-full" style="background: {{ $org->color }}"></span><h2 class="font-bold">{{ $org->name }}</h2>
                <span class="ml-auto text-xs text-slate-400">{{ $locations->where('organization_id', $org->id)->count() }} lokasi</span></div>
            <ul>{!! $render(null, $locations->where('organization_id', $org->id)) !!}</ul>
        </div>
    @endforeach
</div>
@endsection
