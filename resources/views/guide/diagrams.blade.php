@php use App\Support\Itil; @endphp
@switch($name)
    @case('lifecycle')
        {{-- Siklus status per tipe tiket, dihasilkan dari aturan transisi aplikasi --}}
        <div class="space-y-3">
            @foreach ([
                'incident' => ['new', 'assigned', 'pending', 'resolved', 'closed'],
                'request' => ['new', 'waiting_approval', 'approved', 'assigned', 'resolved', 'closed'],
                'problem' => ['new', 'assigned', 'known_error', 'resolved', 'closed'],
                'change' => ['new', 'validated', 'waiting_approval', 'approved', 'planned', 'implemented', 'monitored', 'closed'],
            ] as $type => $flow)
                <div class="rounded-xl border border-slate-200 p-3">
                    <p class="text-xs font-bold text-slate-700 mb-2">{{ Itil::TYPES[$type]['label'] }} <span class="font-normal text-slate-400">· {{ Itil::TYPES[$type]['practice'] }}</span></p>
                    <div class="flex flex-wrap items-center gap-1">
                        @foreach ($flow as $i => $st)
                            <span class="chip {{ Itil::statusColor($st) }}">{{ Itil::statusLabel($st) }}</span>@if (!$loop->last)<span class="text-slate-300 text-xs">→</span>@endif
                        @endforeach
                    </div>
                </div>
            @endforeach
            <p class="text-[11px] text-slate-400">Cabang lain: Insiden → Eskalasi; Permintaan/Perubahan → Ditolak / Tidak Disetujui; Selesai → Buka Kembali.</p>
        </div>
        @break

    @case('priority')
        <div class="rounded-xl border border-slate-200 p-3 overflow-x-auto">
            <table class="text-xs text-center w-full">
                <tr><td class="p-1 text-left text-slate-500">Dampak ↓ / Urgensi →</td>@foreach (Itil::URGENCIES as $u => $l)<td class="p-1 font-semibold text-slate-600">{{ $u }}<br><span class="font-normal">{{ $l }}</span></td>@endforeach</tr>
                @foreach (Itil::IMPACTS as $i => $l)
                    <tr><td class="p-1 text-left font-semibold text-slate-600">{{ $i }} — {{ $l }}</td>
                        @foreach (Itil::URGENCIES as $u => $x)@php $p = Itil::priority($i, $u); @endphp<td class="p-1"><div class="rounded-md py-1.5 font-bold {{ Itil::PRIORITIES[$p]['class'] }}">P{{ $p }}</div></td>@endforeach</tr>
                @endforeach
            </table>
            <div class="grid grid-cols-4 gap-1 mt-3 text-[11px] text-center">
                @foreach (Itil::DEFAULT_SLT as $p => $s)
                    <div class="rounded-md bg-slate-50 p-1.5"><b>P{{ $p }}</b><br>TTO {{ $s['tto'] < 1 ? ($s['tto'] * 60).' mnt' : $s['tto'].' jam' }}<br>TTR {{ $s['ttr'] }} jam</div>
                @endforeach
            </div>
        </div>
        @break

    @case('topology')
        <div class="rounded-xl border border-slate-200 p-4 bg-[radial-gradient(circle,#e2e8f0_1px,transparent_1px)] [background-size:14px_14px]">
            @foreach ([
                ['Internet / ISP', '#0b1a44', 'Telkom, Biznet, Telkomsat'], ['WAN Edge Router', '#2563eb', 'Cisco ASR · Juniper MX · MikroTik CCR'],
                ['Firewall (HA)', '#dc2626', 'FortiGate · Palo Alto · Sangfor'], ['Core Switch', '#7c3aed', 'Catalyst 9500 · QFX · CloudEngine'],
                ['Distribusi / ToR / Router site', '#0891b2', 'Nexus · Arista · S6730 · ISR/IR1101'], ['Access Switch', '#059669', 'Aruba CX · RUGGEDCOM · Ruijie'],
                ['AP & Endpoint', '#f59e0b', 'UniFi · Meraki · Ruckus · PC · CCTV · IP phone'],
            ] as $i => [$l, $c, $b])
                <div class="flex items-center gap-3" style="margin-left: {{ $i * 14 }}px">
                    <span class="w-3.5 h-3.5 rounded-full ring-2 ring-white shrink-0" style="background: {{ $c }}"></span>
                    <div class="bg-white/90 rounded-lg px-2 py-1 my-0.5"><p class="text-xs font-bold text-slate-800">{{ $l }}</p><p class="text-[10px] text-slate-500">{{ $b }}</p></div>
                </div>
            @endforeach
        </div>
        @break

    @case('cimodel')
        <div class="grid grid-cols-2 gap-2">
            @foreach (\App\Models\Ci::CLASSES as $group => $classes)
                <div class="rounded-xl border border-slate-200 p-3">
                    <p class="text-xs font-bold text-indigo-600 mb-1">FunctionalCI › {{ $group }}</p>
                    @foreach ($classes as $class => $label)<p class="text-[11px] text-slate-600 flex items-center gap-1.5"><x-icon :name="\App\Models\Ci::ICONS[$class] ?? 'box'" class="w-3 h-3 text-slate-400"/>{{ $label }}</p>@endforeach
                </div>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-400 mt-2">Relasi: Business Process → Application Solution → Software Instance → Virtual Machine → Server (Hypervisor) → Storage/UPS.</p>
        @break

    @case('svs')
        <div class="rounded-xl border border-slate-200 p-3">
            <div class="grid grid-cols-3 gap-1.5 text-center text-[11px]">
                @foreach (['Plan', 'Improve', 'Engage', 'Design & Transition', 'Obtain / Build', 'Deliver & Support'] as $a)
                    <div class="rounded-lg bg-indigo-50 text-indigo-800 font-semibold py-2 px-1">{{ $a }}</div>
                @endforeach
            </div>
            <div class="flex items-center justify-between mt-2 text-[11px]"><span class="text-slate-500">Peluang / Permintaan</span><span class="flex-1 mx-2 border-t-2 border-dashed border-indigo-300"></span><span class="font-bold text-indigo-700">Nilai</span></div>
            <p class="text-[11px] text-slate-500 mt-2">Dipandu 7 guiding principles · didukung 34 praktik · ditingkatkan terus-menerus.</p>
        </div>
        @break
@endswitch
