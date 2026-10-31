@extends('layouts.app')
@section('title', $ci->name)
@section('header')
    <div class="flex items-center gap-1 text-xs text-slate-500 mb-2 flex-wrap">
        <a href="{{ route('cis.index', ['class' => $ci->class]) }}" class="hover:underline">{{ \App\Models\Ci::classLabel($ci->class) }}</a>
        @foreach ($path as $p)<x-icon name="chevron" class="w-3 h-3"/><a href="{{ route('cis.show', $p) }}" class="hover:underline">{{ $p->name }}</a>@endforeach
    </div>
    <div class="flex items-center gap-3">
        <span class="grid place-items-center w-12 h-12 rounded-2xl bg-brand-50 text-brand-600"><x-icon :name="$ci->icon" class="w-6 h-6"/></span>
        <div><h1 class="h1">{{ $ci->name }}</h1>
            <p class="sub">{{ $ci->organization->name }} · {{ $ci->location?->name }} @if ($ci->brand)· <b style="color: {{ $ci->brand->color }}">{{ $ci->brand->name }}</b> {{ $ci->deviceModel?->name }}@endif</p></div>
    </div>
@endsection
@section('actions')
    <a href="{{ route('tickets.create', ['type' => 'incident']) }}" class="btn btn-ghost"><x-icon name="alert" class="w-4 h-4"/> Laporkan Insiden</a>
    @if ($ci->network_role)<a href="{{ route('topology.index', ['org' => $ci->organization_id]) }}" class="btn btn-ghost"><x-icon name="network" class="w-4 h-4"/> Topologi</a>@endif
@endsection

@section('content')
<div class="grid lg:grid-cols-3 gap-4" x-data="{ tab: 'impact' }">
    <div class="space-y-4">
        <div class="card p-5 text-sm space-y-2.5">
            <h2 class="font-bold mb-1">Atribut</h2>
            @foreach (array_filter([
                'Status' => $ci->status, 'Kritikalitas bisnis' => $ci->business_criticity, 'Peran jaringan' => $ci->network_role,
                'IP manajemen' => $ci->management_ip, 'Serial number' => $ci->serial_number, 'Asset number' => $ci->asset_number,
                'OS / Versi' => trim(($ci->os_family ?? '').' '.($ci->os_version ?? '')), 'Firmware' => $ci->firmware, 'CPU' => $ci->cpu, 'RAM' => $ci->ram,
                'Rack' => $ci->rack?->name, 'Rack units' => $ci->rack_units ? $ci->rack_units.'U' : null, 'Tim pendukung' => $ci->team?->name,
                'Tanggal beli' => $ci->purchase_date?->translatedFormat('d M Y'), 'Garansi s.d.' => $ci->warranty_end?->translatedFormat('d M Y'),
                'Produksi sejak' => $ci->move2production?->translatedFormat('d M Y'),
            ]) as $k => $v)
                <div class="flex justify-between gap-3"><span class="text-slate-500">{{ $k }}</span><span class="font-medium text-right {{ in_array($k, ['IP manajemen', 'Serial number']) ? 'font-mono text-xs' : '' }}">{{ $v }}</span></div>
            @endforeach
            @if ($ci->warranty_end && $ci->warranty_end->isPast())<p class="chip bg-rose-100 text-rose-700">Garansi habis</p>@endif
            @if ($ci->description)<p class="text-slate-600 pt-2 border-t border-slate-100">{{ $ci->description }}</p>@endif
        </div>
        @if ($ci->services->count() || $ci->contracts->count())
        <div class="card p-5 text-sm">
            <h2 class="font-bold mb-2">Layanan & Kontrak</h2>
            @foreach ($ci->services as $s)<a href="{{ route('services.show', $s) }}" class="flex items-center gap-2 py-1 hover:text-brand-600"><x-icon name="grid" class="w-4 h-4 text-slate-400"/>{{ $s->name }}</a>@endforeach
            @foreach ($ci->contracts as $c)<p class="flex items-center gap-2 py-1"><x-icon name="file" class="w-4 h-4 text-slate-400"/>{{ $c->vendor }} — {{ $c->name }}</p>@endforeach
        </div>
        @endif
        @if ($ci->contacts->count())
        <div class="card p-5 text-sm">
            <h2 class="font-bold mb-2">Kontak</h2>
            @foreach ($ci->contacts as $p)<p>{{ $p->name }} <span class="text-slate-400">· {{ $p->pivot->role }} · {{ $p->function }}</span></p>@endforeach
        </div>
        @endif
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="card">
            <div class="flex gap-1 p-2 border-b border-slate-100 overflow-x-auto">
                @foreach (['impact' => 'Analisis Dampak', 'rel' => 'Relasi', 'if' => 'Interface ('.$ci->interfaces->count().')', 'tk' => 'Tiket ('.$tickets->count().')', 'ev' => 'Event'] as $k => $l)
                    <button @click="tab='{{ $k }}'" class="px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap" :class="tab === '{{ $k }}' ? 'bg-slate-900 text-white' : 'hover:bg-slate-50'">{{ $l }}</button>
                @endforeach
            </div>
            <div class="p-5">
                <div x-show="tab === 'impact'">
                    <p class="text-sm text-slate-500 mb-3">CI yang <b>terdampak</b> bila <b>{{ $ci->name }}</b> mengalami gangguan (downstream, rekursif melalui relasi dependensi dan topologi).</p>
                    @php
                        $render = function ($nodes, $lvl = 0) use (&$render) {
                            $html = '';
                            foreach ($nodes as $n) {
                                $c = $n['ci'];
                                $html .= '<li class="mt-1.5"><a href="'.route('cis.show', $c).'" class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-brand-400 bg-white text-sm">'
                                    .'<span class="text-[10px] font-semibold uppercase text-slate-400">'.e($c->class).'</span><span class="font-medium">'.e($c->name).'</span></a>';
                                if ($n['children']) $html .= '<ul class="ml-5 pl-3 border-l-2 border-dashed border-slate-200">'.$render($n['children'], $lvl + 1).'</ul>';
                                $html .= '</li>';
                            }
                            return $html;
                        };
                    @endphp
                    @if ($impact)
                        <div class="max-h-[480px] overflow-auto scrollbar-thin"><ul>{!! $render(array_slice($impact, 0, 40)) !!}</ul></div>
                    @else <p class="text-sm text-emerald-600">Tidak ada CI lain yang bergantung pada CI ini.</p> @endif
                </div>
                <div x-show="tab === 'rel'" x-cloak class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><p class="font-semibold mb-2">Bergantung pada (upstream)</p>
                        @if ($ci->parent)<a href="{{ route('cis.show', $ci->parent) }}" class="block py-1 hover:text-brand-600">↑ {{ $ci->parent->name }} <span class="text-slate-400">(uplink)</span></a>@endif
                        @foreach ($ci->dependsOn as $d)<a href="{{ route('cis.show', $d) }}" class="block py-1 hover:text-brand-600">↑ {{ $d->name }} <span class="text-slate-400">({{ $d->pivot->type }})</span></a>@endforeach
                    </div>
                    <div><p class="font-semibold mb-2">Digunakan oleh (downstream)</p>
                        @foreach ($ci->children as $d)<a href="{{ route('cis.show', $d) }}" class="block py-1 hover:text-brand-600">↓ {{ $d->name }} <span class="text-slate-400">({{ $d->brand?->name }})</span></a>@endforeach
                        @foreach ($ci->dependents as $d)<a href="{{ route('cis.show', $d) }}" class="block py-1 hover:text-brand-600">↓ {{ $d->name }} <span class="text-slate-400">({{ $d->pivot->type }})</span></a>@endforeach
                        @foreach ($ci->rackedDevices as $d)<a href="{{ route('cis.show', $d) }}" class="block py-1 hover:text-brand-600">▣ {{ $d->name }} <span class="text-slate-400">(di rack)</span></a>@endforeach
                    </div>
                </div>
                <div x-show="tab === 'if'" x-cloak>
                    <table class="w-full"><thead><tr><th class="th">Interface</th><th class="th">IP</th><th class="th">MAC</th><th class="th">Speed</th><th class="th">VLAN</th></tr></thead>
                        <tbody>@forelse ($ci->interfaces as $i)<tr><td class="td font-semibold">{{ $i->name }}</td><td class="td font-mono text-xs">{{ $i->ip_address }}</td><td class="td font-mono text-xs">{{ $i->mac_address }}</td><td class="td text-xs">{{ $i->speed }}</td><td class="td text-xs">{{ $i->vlan }}</td></tr>
                        @empty<tr><td class="td text-slate-400" colspan="5">Tidak ada interface tercatat.</td></tr>@endforelse</tbody></table>
                </div>
                <div x-show="tab === 'tk'" x-cloak>
                    @forelse ($tickets as $t)
                        <a href="{{ route('tickets.show', $t) }}" class="flex items-center gap-3 py-2 border-b border-slate-100 text-sm hover:text-brand-600">
                            <span class="font-mono text-xs font-bold text-brand-600 w-20">{{ $t->ref }}</span><span class="flex-1 line-clamp-1">{{ $t->title }}</span><x-status :status="$t->status"/><span class="text-xs text-slate-400 w-24 text-right">{{ $t->created_at->diffForHumans() }}</span></a>
                    @empty <p class="text-sm text-slate-400">Belum ada tiket.</p> @endforelse
                </div>
                <div x-show="tab === 'ev'" x-cloak>
                    @forelse ($ci->events as $e)
                        <p class="text-sm py-1.5 border-b border-slate-100"><span class="chip {{ ['critical' => 'bg-rose-100 text-rose-700', 'major' => 'bg-orange-100 text-orange-700', 'minor' => 'bg-amber-100 text-amber-700'][$e->severity] ?? 'bg-slate-100 text-slate-600' }}">{{ $e->severity }}</span> {{ $e->message }} <span class="text-xs text-slate-400">· {{ $e->source }} · {{ $e->occurred_at->diffForHumans() }}</span></p>
                    @empty <p class="text-sm text-slate-400">Tidak ada event.</p> @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
