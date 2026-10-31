@extends('layouts.app')
@section('title', $title)
@section('header')
    <p class="text-sm text-slate-500"><a href="{{ route('knowledge.index') }}" class="hover:underline">Basis Pengetahuan</a> · {{ $kind }}</p>
    <h1 class="h1">{{ $title }}</h1>
    @if ($meta)<p class="sub">{{ $meta }}</p>@endif
@endsection

@section('content')
<div class="card p-6 max-w-3xl space-y-5">
    @foreach ($sections as $h => $body)
        @if ($body)<div><h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $h }}</h2><p class="mt-1 text-slate-800 whitespace-pre-line">{{ $body }}</p></div>@endif
    @endforeach
    @isset($problem)
        <a href="{{ route('tickets.show', $problem) }}" class="btn btn-ghost">Buka problem {{ $problem->ref }}</a>
    @endisset
    <div class="pt-4 border-t border-slate-100 flex items-center gap-2 text-sm text-slate-500">Apakah artikel ini membantu? <button class="btn btn-ghost">👍 Ya</button><a href="{{ route('tickets.create') }}" class="btn btn-ghost">Tidak, buat tiket</a></div>
</div>
@endsection
