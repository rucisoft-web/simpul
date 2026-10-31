@extends('layouts.app')
@section('title', 'Continual Improvement')
@section('header')
    <h1 class="h1">Continual Improvement Register</h1>
    <p class="sub">Daftar inisiatif perbaikan berkelanjutan (CIR) yang dipetakan ke praktik dan <i>guiding principle</i> ITIL 4.</p>
@endsection

@section('content')
@php $cols = ['proposed' => ['Diusulkan', 'bg-slate-100'], 'approved' => ['Disetujui', 'bg-sky-50'], 'in_progress' => ['Berjalan', 'bg-amber-50'], 'done' => ['Selesai', 'bg-emerald-50']]; @endphp
<div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
    @foreach ($cols as $status => [$label, $bg])
        <div class="rounded-2xl {{ $bg }} p-3">
            <p class="font-bold text-sm mb-2 px-1">{{ $label }} <span class="text-slate-400 font-normal">{{ $items->where('status', $status)->count() }}</span></p>
            <div class="space-y-3">
                @foreach ($items->where('status', $status) as $it)
                    <div class="card p-4">
                        <div class="flex items-center gap-2 mb-1"><x-org-dot :org="$it->organization"/><span class="chip bg-violet-50 text-violet-700">{{ $it->practice }}</span></div>
                        <p class="font-semibold leading-5">{{ $it->title }}</p>
                        <p class="text-xs text-slate-500 mt-1">🎯 {{ $it->benefit }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">Prinsip: {{ $it->guiding_principle }}</p>
                        <div class="h-1.5 bg-slate-100 rounded-full mt-3"><div class="h-1.5 rounded-full bg-brand-500" style="width: {{ $it->progress }}%"></div></div>
                        <div class="flex justify-between text-[11px] text-slate-500 mt-1"><span>{{ $it->owner?->name }}</span><span>{{ $it->progress }}% · target {{ $it->target_date?->translatedFormat('M Y') }}</span></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
<div class="card p-5 mt-6">
    <h2 class="font-bold mb-3">Model Continual Improvement ITIL 4</h2>
    <div class="grid sm:grid-cols-7 gap-2 text-center text-xs">
        @foreach (['Apa visinya?', 'Di mana kita sekarang?', 'Ke mana kita ingin?', 'Bagaimana mencapainya?', 'Ambil tindakan', 'Sudahkah tercapai?', 'Jaga momentum'] as $i => $s)
            <div class="rounded-xl bg-gradient-to-b from-brand-50 to-white border border-brand-100 p-3"><p class="text-lg font-extrabold text-brand-600">{{ $i + 1 }}</p><p class="font-medium">{{ $s }}</p></div>
        @endforeach
    </div>
</div>
@endsection
