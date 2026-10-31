@extends('layouts.app')
@section('title', 'Tiket Baru')
@section('header')
    <h1 class="h1">Buat Tiket Baru</h1>
    <p class="sub">Prioritas dihitung otomatis dari matriks <b>Dampak × Urgensi</b> (ITIL). SLA TTO/TTR ditetapkan dari prioritas.</p>
@endsection

@section('content')
@php
    $matrix = [];
    foreach ([1, 2, 3] as $i) foreach ([1, 2, 3, 4] as $u) $matrix[$i][$u] = \App\Support\Itil::priority($i, $u);
@endphp
<form method="POST" action="{{ route('tickets.store') }}" class="grid lg:grid-cols-3 gap-4"
      x-data="{ type: '{{ $type }}', impact: 3, urgency: 3, m: @js($matrix), labels: @js(collect(\App\Support\Itil::PRIORITIES)->map->label) }">
    @csrf
    <div class="card p-5 lg:col-span-2 space-y-4">
        <div>
            <label class="text-sm font-semibold">Tipe tiket</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1.5">
                @foreach (\App\Support\Itil::TYPES as $k => $t)
                    <label class="cursor-pointer"><input type="radio" name="type" value="{{ $k }}" x-model="type" class="peer sr-only">
                        <span class="block text-center rounded-xl border border-slate-200 py-2.5 text-sm font-semibold peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700">{{ $t['label'] }}</span></label>
                @endforeach
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold">Organisasi</label>
                <select name="organization_id" class="input mt-1.5">
                    @foreach ($orgs as $o)
                        <option value="{{ $o->id }}" @selected($o->id == $orgId)>{{ $o->name }}</option>
                        @foreach ($o->children as $c)<option value="{{ $c->id }}">— {{ $c->name }}</option>@endforeach
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Ganti organisasi aktif lewat pemilih di kanan atas untuk memuat layanan & CI-nya.</p>
            </div>
            <div>
                <label class="text-sm font-semibold">Pelapor / Pemohon</label>
                <select name="caller_id" class="input mt-1.5">
                    @foreach ($callers as $c)<option value="{{ $c->id }}">{{ $c->name }} — {{ $c->function }}</option>@endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="text-sm font-semibold">Layanan & subkategori</label>
            <select name="service_subcategory_id" class="input mt-1.5">
                <option value="">— pilih —</option>
                @foreach ($services as $s)
                    <optgroup label="{{ $s->name }}">
                        @foreach ($s->subcategories as $sub)<option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->request_type === 'incident' ? 'insiden' : 'permintaan' }})</option>@endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-semibold">Judul</label>
            <input name="title" required maxlength="200" class="input mt-1.5" placeholder="Contoh: Wi-Fi di gedung rektorat tidak bisa terhubung">
        </div>
        <div>
            <label class="text-sm font-semibold">Deskripsi</label>
            <textarea name="description" rows="5" class="input !h-auto py-2 mt-1.5" placeholder="Jelaskan gejala, sejak kapan, dampak, dan langkah yang sudah dicoba."></textarea>
        </div>
        <div>
            <label class="text-sm font-semibold">CI terdampak</label>
            <select name="ci_id" class="input mt-1.5">
                <option value="">— tidak ada / tidak tahu —</option>
                @foreach ($cis->groupBy('class') as $class => $items)
                    <optgroup label="{{ \App\Models\Ci::classLabel($class) }}">@foreach ($items as $ci)<option value="{{ $ci->id }}">{{ $ci->name }}</option>@endforeach</optgroup>
                @endforeach
            </select>
        </div>
        <div class="grid sm:grid-cols-2 gap-4" x-show="type === 'change'" x-cloak>
            <div><label class="text-sm font-semibold">Jenis perubahan</label>
                <select name="change_type" class="input mt-1.5"><option value="standard">Standard (pra-disetujui)</option><option value="normal" selected>Normal (CAB)</option><option value="emergency">Emergency (ECAB)</option></select></div>
            <div><label class="text-sm font-semibold">Jadwal implementasi</label><input type="datetime-local" name="planned_start" class="input mt-1.5"></div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <h2 class="font-bold mb-3">Prioritas</h2>
            <label class="text-sm font-semibold">Dampak</label>
            <select name="impact" x-model.number="impact" class="input mt-1 mb-3">
                @foreach (\App\Support\Itil::IMPACTS as $k => $v)<option value="{{ $k }}">{{ $k }} — {{ $v }}</option>@endforeach
            </select>
            <label class="text-sm font-semibold">Urgensi</label>
            <select name="urgency" x-model.number="urgency" class="input mt-1">
                @foreach (\App\Support\Itil::URGENCIES as $k => $v)<option value="{{ $k }}">{{ $k }} — {{ $v }}</option>@endforeach
            </select>
            <div class="mt-4 rounded-xl bg-slate-900 text-white p-4 text-center">
                <p class="text-xs text-slate-400">Prioritas terhitung</p>
                <p class="text-2xl font-extrabold" x-text="labels[m[impact][urgency]]"></p>
            </div>
            <table class="w-full mt-4 text-[11px] text-center">
                <tr><td></td>@foreach (\App\Support\Itil::URGENCIES as $u => $l)<td class="font-semibold text-slate-500 pb-1">U{{ $u }}</td>@endforeach</tr>
                @foreach ($matrix as $i => $row)
                    <tr><td class="font-semibold text-slate-500 pr-1">D{{ $i }}</td>
                        @foreach ($row as $u => $p)<td class="p-0.5"><div class="rounded py-1 {{ \App\Support\Itil::PRIORITIES[$p]['class'] }}" :class="impact == {{ $i }} && urgency == {{ $u }} ? 'ring-2 ring-offset-1 ring-slate-900' : ''">P{{ $p }}</div></td>@endforeach</tr>
                @endforeach
            </table>
        </div>
        <button class="btn btn-primary w-full justify-center h-11"><x-icon name="check" class="w-4 h-4"/> Simpan Tiket</button>
    </div>
</form>
@endsection
