@extends('layouts.app')
@section('title', 'Basis Pengetahuan')
@section('header')
    <h1 class="h1">Basis Pengetahuan</h1>
    <p class="sub">FAQ untuk self-help pengguna, <b>Known Error</b> hasil Problem Management, dan dokumen kebijakan/prosedur.</p>
@endsection

@section('content')
<form class="card p-5 mb-6 bg-gradient-to-br from-brand-600 to-sky-500 border-0 text-white">
    <p class="font-bold text-lg">Apa yang bisa kami bantu?</p>
    <div class="flex gap-2 mt-3"><input name="q" value="{{ $term }}" class="input !text-slate-800 flex-1" placeholder="Cari: password, eduroam, VPN, SAP, MyBluebird…"><button class="btn bg-slate-900 text-white">Cari</button></div>
</form>
<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2">
        <h2 class="font-bold mb-2">FAQ ({{ $faqs->count() }})</h2>
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach ($faqs as $f)
                <a href="{{ route('knowledge.faq', $f) }}" class="card p-4 hover:border-brand-300 block">
                    <p class="text-[11px] font-semibold text-brand-600">{{ $f->category->name }} @if ($f->organization)· {{ $f->organization->code }}@endif</p>
                    <p class="font-semibold mt-0.5">{{ $f->title }}</p>
                    <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $f->summary }}</p>
                    <p class="text-[11px] text-slate-400 mt-2">{{ number_format($f->views) }} kali dilihat</p>
                </a>
            @endforeach
        </div>
    </div>
    <div class="space-y-4">
        <div class="card p-4">
            <h2 class="font-bold mb-2">Known Error ({{ $knownErrors->count() }})</h2>
            @foreach ($knownErrors as $ke)
                <a href="{{ route('knowledge.ke', $ke) }}" class="block py-2 border-b border-slate-100 hover:text-brand-600">
                    <p class="text-sm font-semibold">{{ $ke->name }}</p>
                    <p class="text-xs text-slate-500">{{ $ke->domain }} · {{ $ke->organization->code }} · {{ $ke->error_code }} @if ($ke->solution)<span class="text-emerald-600">· solusi tersedia</span>@else<span class="text-amber-600">· workaround</span>@endif</p>
                </a>
            @endforeach
        </div>
        <div class="card p-4">
            <h2 class="font-bold mb-2">Dokumen</h2>
            @foreach ($documents as $d)
                <div class="flex items-start gap-2 py-1.5 text-sm"><x-icon name="file" class="w-4 h-4 text-slate-400 mt-0.5"/>
                    <div><p class="font-medium">{{ $d->url ? '' : '' }}@if ($d->url)<a href="{{ $d->url }}" class="text-brand-600 hover:underline">{{ $d->name }}</a>@else{{ $d->name }}@endif</p>
                    <p class="text-xs text-slate-500">{{ $d->organization->code }} · {{ \Illuminate\Support\Str::limit($d->content, 80) }}</p></div></div>
            @endforeach
        </div>
    </div>
</div>
@endsection
