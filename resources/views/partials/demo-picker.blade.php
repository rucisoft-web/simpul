{{--
    Pemilih perusahaan demo + tombol "Masuk sebagai Tamu".
    Pengunjung memilih satu perusahaan dulu; tombol baru aktif setelah dipilih. Setelah masuk,
    seluruh layar hanya menampilkan data perusahaan itu (tidak tercampur).
    Param: $orgs (organisasi induk), $variant = 'cards' | 'chips', $dark = bool, $scen = [code => [label, deskripsi]]
--}}
@php
    $variant = $variant ?? 'cards';
    $dark = $dark ?? false;
    $scen = $scen ?? [];
@endphp
<form method="POST" action="{{ route('guest.login') }}" x-data="{ org: '' }">
    @csrf
    <input type="hidden" name="org" :value="org">
    @if ($variant === 'cards')
        <div class="grid lg:grid-cols-3 gap-5">
            @foreach ($orgs as $o)
                <button type="button" @click="org = '{{ $o->code }}'"
                        class="text-left rounded-2xl p-6 border-2 transition {{ $dark ? 'bg-white/5 hover:bg-white/10' : 'bg-white hover:shadow-lg' }}"
                        :class="org === '{{ $o->code }}' ? 'border-sky-400 ring-4 ring-sky-400/30' : '{{ $dark ? 'border-white/10' : 'border-slate-200' }}'">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold px-2 py-1 rounded-full text-white" style="background: {{ $o->color }}">{{ $scen[$o->code][0] ?? $o->industry }}</span>
                        <span class="grid place-items-center w-6 h-6 rounded-full border-2" :class="org === '{{ $o->code }}' ? 'bg-sky-400 border-sky-400 text-white' : '{{ $dark ? 'border-white/30' : 'border-slate-300' }}'">
                            <svg x-show="org === '{{ $o->code }}'" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                    </div>
                    <p class="text-xl font-extrabold mt-4">{{ $o->name }}</p>
                    <p class="text-sm mt-2 {{ $dark ? 'text-slate-300' : 'text-slate-600' }}">{{ $scen[$o->code][1] ?? $o->description }}</p>
                    <div class="grid grid-cols-3 gap-2 mt-5 text-center">
                        @foreach ([[$o->cis_count, 'CI'], [$o->locations_count, 'lokasi'], [$o->services_count, 'layanan']] as [$v, $l])
                            <div class="rounded-xl py-2 {{ $dark ? 'bg-white/5' : 'bg-slate-50' }}"><p class="font-extrabold">{{ number_format($v) }}</p><p class="text-[11px] {{ $dark ? 'text-slate-400' : 'text-slate-500' }}">{{ $l }}</p></div>
                        @endforeach
                    </div>
                </button>
            @endforeach
        </div>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <button :disabled="!org" class="h-12 px-8 rounded-xl font-bold shadow-lg transition disabled:opacity-40 disabled:cursor-not-allowed grad text-white shadow-indigo-500/30">
                <span x-show="!org">Pilih perusahaan di atas dulu</span>
                <span x-show="org" x-cloak>Masuk sebagai Tamu →</span>
            </button>
            <p class="text-xs {{ $dark ? 'text-slate-400' : 'text-slate-500' }}">Tanpa registrasi. Data perusahaan lain tidak ikut tampil.</p>
        </div>
    @else
        <p class="text-xs font-semibold {{ $dark ? 'text-white/80' : 'text-slate-500' }} mb-2">1. Pilih perusahaan demo</p>
        <div class="flex flex-wrap gap-2 {{ $center ?? false ? 'justify-center' : '' }}">
            @foreach ($orgs as $o)
                <button type="button" @click="org = '{{ $o->code }}'"
                        class="inline-flex items-center gap-2 h-10 px-3 rounded-xl border-2 text-sm font-semibold transition"
                        :class="org === '{{ $o->code }}' ? '{{ $dark ? 'bg-white text-slate-900 border-white' : 'bg-indigo-50 text-indigo-700 border-indigo-500' }}' : '{{ $dark ? 'border-white/30 hover:bg-white/10' : 'border-slate-200 hover:bg-slate-50' }}'">
                    <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $o->color }}"></span>{{ $o->name }}
                </button>
            @endforeach
        </div>
        <p class="text-xs font-semibold {{ $dark ? 'text-white/80' : 'text-slate-500' }} mt-4 mb-2">2. Masuk</p>
        <button :disabled="!org" class="{{ $buttonClass ?? 'w-full h-12 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white font-bold' }} disabled:opacity-40 disabled:cursor-not-allowed">
            <span x-show="!org">Pilih perusahaan dulu</span><span x-show="org" x-cloak>Masuk sebagai Tamu →</span>
        </button>
    @endif
</form>
