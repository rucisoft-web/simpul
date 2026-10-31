@extends('layouts.app')
@section('title', $ticket->ref)
@section('header')
    <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
        <a href="{{ route('tickets.index', ['type' => $ticket->type]) }}" class="hover:underline">{{ $ticket->type_label }}</a><x-icon name="chevron" class="w-3 h-3"/>
        <span class="font-mono">{{ $ticket->ref }}</span>
    </div>
    <h1 class="h1">{{ $ticket->title }}</h1>
    <div class="flex flex-wrap items-center gap-2 mt-2">
        <x-status :status="$ticket->status"/> <x-priority :p="$ticket->priority"/>
        @if ($ticket->change_type)<span class="chip bg-violet-100 text-violet-700">{{ ucfirst($ticket->change_type) }} change</span>@endif
        <x-org-dot :org="$ticket->organization"/> <span class="text-xs text-slate-400">· {{ $ticket->organization->name }}</span>
    </div>
@endsection

@section('content')
<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        {{-- Alur status --}}
        @if ($transitions)
        <div class="card p-4" x-data="{ action: null, label: '' }">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Langkah berikutnya (siklus {{ \App\Support\Itil::TYPES[$ticket->type]['practice'] }})</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($transitions as $act => $to)
                    <button type="button" @click="action='{{ $act }}'; label='{{ \App\Support\Itil::ACTION_LABELS[$act] }}'"
                            class="btn {{ in_array($act, ['resolve', 'approve', 'close', 'implement']) ? 'btn-primary' : 'btn-ghost' }}">
                        {{ \App\Support\Itil::ACTION_LABELS[$act] }} <span class="text-[10px] opacity-70">→ {{ \App\Support\Itil::statusLabel($to) }}</span></button>
                @endforeach
            </div>
            <form x-show="action" x-cloak method="POST" action="{{ route('tickets.transition', $ticket) }}" class="mt-3 grid sm:grid-cols-3 gap-2 items-start">
                @csrf <input type="hidden" name="action" :value="action">
                <template x-if="action === 'assign'">
                    <select name="agent_id" class="input">
                        @foreach ($teamMembers as $m)<option value="{{ $m->id }}" @selected($ticket->agent_id === $m->id)>{{ $m->name }} ({{ $m->pivot->role }})</option>@endforeach
                    </select>
                </template>
                <input name="note" class="input sm:col-span-2" :placeholder="action === 'resolve' ? 'Solusi / resolusi (wajib diisi untuk Selesaikan)' : 'Catatan (opsional)'">
                <button class="btn btn-primary justify-center" x-text="'Konfirmasi: ' + label"></button>
            </form>
        </div>
        @endif

        <div class="card p-5">
            <h2 class="font-bold mb-2">Deskripsi</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $ticket->description }}</p>
            @if ($ticket->type === 'change')
                <div class="grid sm:grid-cols-2 gap-4 mt-4 text-sm">
                    <div><p class="text-xs text-slate-500">Jadwal</p><p class="font-semibold">{{ $ticket->planned_start?->translatedFormat('d M Y H:i') }} – {{ $ticket->planned_end?->format('H:i') }}</p></div>
                    <div><p class="text-xs text-slate-500">Risiko / Outage</p><p class="font-semibold">{{ ucfirst($ticket->risk ?? '-') }} · {{ $ticket->outage ? 'Ada downtime' : 'Tanpa downtime' }}</p></div>
                    <div class="sm:col-span-2"><p class="text-xs text-slate-500">Rencana fallback</p><p>{{ $ticket->fallback_plan }}</p></div>
                </div>
            @endif
            @if ($ticket->root_cause || $ticket->workaround)
                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    <div class="rounded-xl bg-amber-50 p-3"><p class="text-xs font-semibold text-amber-700">Akar masalah</p><p class="text-sm">{{ $ticket->root_cause }}</p></div>
                    <div class="rounded-xl bg-sky-50 p-3"><p class="text-xs font-semibold text-sky-700">Workaround</p><p class="text-sm">{{ $ticket->workaround }}</p></div>
                </div>
            @endif
            @if ($ticket->solution)
                <div class="mt-4 rounded-xl bg-emerald-50 p-3"><p class="text-xs font-semibold text-emerald-700">Solusi ({{ $ticket->resolution_code }})</p><p class="text-sm">{{ $ticket->solution }}</p></div>
            @endif
        </div>

        @if ($ticket->children->count())
        <div class="card p-5">
            <h2 class="font-bold mb-2">Insiden Terkait ({{ $ticket->children->count() }})</h2>
            <ul class="divide-y divide-slate-100">
                @foreach ($ticket->children as $c)
                    <li class="py-2 flex items-center gap-3 text-sm"><a class="font-mono text-xs text-brand-600 font-semibold" href="{{ route('tickets.show', $c) }}">{{ $c->ref }}</a><span class="flex-1 line-clamp-1">{{ $c->title }}</span><x-status :status="$c->status"/></li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Log --}}
        <div class="card p-5" x-data="{ kind: 'public' }">
            <h2 class="font-bold mb-3">Aktivitas & Komunikasi</h2>
            <form method="POST" action="{{ route('tickets.comment', $ticket) }}" class="mb-5">
                @csrf
                <textarea name="message" rows="3" required class="input !h-auto py-2" placeholder="Tulis balasan untuk pelapor atau catatan internal…"></textarea>
                <div class="flex items-center gap-3 mt-2">
                    <label class="text-sm flex items-center gap-1.5"><input type="radio" name="kind" value="public" x-model="kind"> Log publik (terlihat pelapor)</label>
                    <label class="text-sm flex items-center gap-1.5"><input type="radio" name="kind" value="private" x-model="kind"> Log privat (internal)</label>
                    <button class="btn btn-primary ml-auto">Kirim</button>
                </div>
            </form>
            <ol class="relative border-l-2 border-slate-100 ml-2 space-y-4">
                @foreach ($ticket->logs as $log)
                    <li class="ml-4">
                        <span class="absolute -left-[7px] mt-1.5 w-3 h-3 rounded-full border-2 border-white {{ ['system' => 'bg-slate-300', 'public' => 'bg-brand-500', 'private' => 'bg-amber-400'][$log->kind] }}"></span>
                        <p class="text-xs text-slate-500"><span class="font-semibold text-slate-700">{{ $log->author }}</span> · {{ $log->created_at?->translatedFormat('d M Y H:i') }}
                            @if ($log->kind === 'private')<span class="chip bg-amber-100 text-amber-700 ml-1">privat</span>@endif</p>
                        <p class="text-sm mt-0.5 {{ $log->kind === 'system' ? 'text-slate-500 italic' : '' }}">{{ $log->message }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5 text-sm space-y-3">
            <h2 class="font-bold">Detail</h2>
            @php
                $rows = [
                    'Pelapor' => $ticket->caller ? $ticket->caller->name.' — '.$ticket->caller->function : '-',
                    'Lokasi pelapor' => $ticket->caller?->location?->name ?? '-',
                    'Layanan' => $ticket->service?->name ?? '-',
                    'Subkategori' => $ticket->subcategory?->name ?? '-',
                    'Tim' => $ticket->team?->name ?? '-',
                    'Agen' => $ticket->agent?->name ?? 'Belum ditugaskan',
                    'Dampak' => \App\Support\Itil::IMPACTS[$ticket->impact] ?? $ticket->impact,
                    'Urgensi' => \App\Support\Itil::URGENCIES[$ticket->urgency] ?? $ticket->urgency,
                    'Asal' => ucfirst($ticket->origin),
                    'Dibuat' => $ticket->created_at->translatedFormat('d M Y H:i'),
                ];
            @endphp
            @foreach ($rows as $k => $v)
                <div class="flex justify-between gap-3"><span class="text-slate-500">{{ $k }}</span><span class="font-medium text-right">{{ $v }}</span></div>
            @endforeach
            @if ($ticket->parent)
                <div class="flex justify-between gap-3"><span class="text-slate-500">Problem induk</span><a href="{{ route('tickets.show', $ticket->parent) }}" class="font-mono text-xs font-semibold text-brand-600">{{ $ticket->parent->ref }}</a></div>
            @endif
        </div>

        @if ($ticket->ttr_deadline)
        <div class="card p-5 text-sm">
            <h2 class="font-bold mb-3">SLA</h2>
            @foreach ([['TTO (waktu respons)', $ticket->tto_deadline, $ticket->assigned_at, $ticket->sla_tto_passed], ['TTR (waktu penyelesaian)', $ticket->ttr_deadline, $ticket->resolved_at, $ticket->sla_ttr_passed]] as [$lbl, $dl, $done, $passed])
                <div class="mb-3">
                    <div class="flex justify-between"><span class="text-slate-500">{{ $lbl }}</span>
                        <span class="chip {{ $passed ? 'bg-rose-100 text-rose-700' : ($done ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700') }}">{{ $passed ? 'Terlewati' : ($done ? 'Tercapai' : 'Berjalan') }}</span></div>
                    <p class="text-xs text-slate-500 mt-1">Batas: {{ $dl->translatedFormat('d M Y H:i') }}@if ($done) · aktual {{ $done->translatedFormat('d M H:i') }}@endif</p>
                </div>
            @endforeach
            <div class="h-2 bg-slate-100 rounded-full"><div class="h-2 rounded-full {{ $ticket->sla_progress >= 100 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $ticket->sla_progress }}%"></div></div>
        </div>
        @endif

        <div class="card p-5">
            <h2 class="font-bold mb-3">CI Terdampak</h2>
            <ul class="space-y-2">
                @forelse ($ticket->cis as $ci)
                    <li><a href="{{ route('cis.show', $ci) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50">
                        <span class="grid place-items-center w-9 h-9 rounded-lg bg-slate-100 text-slate-600"><x-icon :name="$ci->icon" class="w-4 h-4"/></span>
                        <span class="min-w-0"><span class="block text-sm font-semibold truncate">{{ $ci->name }}</span><span class="text-xs text-slate-500">{{ \App\Models\Ci::classLabel($ci->class) }} {{ $ci->brand ? '· '.$ci->brand->name : '' }}</span></span></a></li>
                @empty <li class="text-sm text-slate-400">Tidak ada.</li> @endforelse
            </ul>
        </div>

        @if ($ticket->events->count())
        <div class="card p-5">
            <h2 class="font-bold mb-2">Event Monitoring</h2>
            @foreach ($ticket->events as $e)<p class="text-sm"><span class="chip bg-rose-100 text-rose-700">{{ $e->severity }}</span> {{ $e->source }} · {{ $e->occurred_at->format('d M H:i') }}</p>@endforeach
        </div>
        @endif
        @foreach ($ticket->knownErrors as $ke)
            <a href="{{ route('knowledge.ke', $ke) }}" class="card p-4 block hover:border-brand-300"><p class="text-xs text-orange-600 font-semibold">Known Error</p><p class="text-sm font-semibold">{{ $ke->name }}</p></a>
        @endforeach
    </div>
</div>
@endsection
