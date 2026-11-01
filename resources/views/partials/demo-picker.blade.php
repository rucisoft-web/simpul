{{--
    Pemilih perusahaan demo. Setiap perusahaan punya tombol "Masuk sebagai Tamu" sendiri, jadi
    satu klik langsung masuk ke data perusahaan itu (tidak bergantung JavaScript, dan tetap tampil
    walau pengunjung sedang login). Setelah masuk, seluruh layar hanya menampilkan data perusahaan itu.
    Param: $orgs (organisasi induk), $variant = 'cards' | 'chips', $dark = bool, $scen = [code => [label, deskripsi]]
--}}
@php
    $variant = $variant ?? 'cards';
    $dark = $dark ?? false;
    $scen = $scen ?? [];
@endphp
@if ($orgs->isEmpty())
    <p class="rounded-xl px-4 py-3 text-sm {{ $dark ? 'bg-white/10 text-white' : 'bg-amber-50 border border-amber-200 text-amber-900' }}">
        Data demo belum dimuat. Administrator: jalankan <code>php artisan migrate:fresh --seed --force</code> lalu <code>php artisan optimize</code>.
    </p>
@elseif ($variant === 'cards')
    <div class="grid lg:grid-cols-3 gap-5">
        @foreach ($orgs as $o)
            <form method="POST" action="{{ route('guest.login') }}"
                  class="flex flex-col rounded-2xl p-6 border-2 transition {{ $dark ? 'bg-white/5 border-white/10 hover:bg-white/10 hover:border-sky-400' : 'bg-white border-slate-200 hover:shadow-lg hover:border-indigo-400' }}">
                @csrf
                <input type="hidden" name="org" value="{{ $o->code }}">
                <span class="self-start text-xs font-bold px-2 py-1 rounded-full text-white" style="background: {{ $o->color }}">{{ $scen[$o->code][0] ?? $o->industry }}</span>
                <p class="text-xl font-extrabold mt-4">{{ $o->name }}</p>
                <p class="text-sm mt-2 {{ $dark ? 'text-slate-300' : 'text-slate-600' }}">{{ $scen[$o->code][1] ?? $o->description }}</p>
                <div class="grid grid-cols-3 gap-2 mt-5 text-center">
                    @foreach ([[$o->cis_count ?? null, 'CI'], [$o->locations_count ?? null, 'lokasi'], [$o->services_count ?? null, 'layanan']] as [$v, $l])
                        @if ($v !== null)
                            <div class="rounded-xl py-2 {{ $dark ? 'bg-white/5' : 'bg-slate-50' }}"><p class="font-extrabold">{{ number_format($v) }}</p><p class="text-[11px] {{ $dark ? 'text-slate-400' : 'text-slate-500' }}">{{ $l }}</p></div>
                        @endif
                    @endforeach
                </div>
                <button class="mt-6 h-12 w-full rounded-xl grad text-white font-bold shadow-lg shadow-indigo-500/30 hover:opacity-90">Masuk sebagai Tamu →</button>
            </form>
        @endforeach
    </div>
    <p class="mt-5 text-center text-xs {{ $dark ? 'text-slate-400' : 'text-slate-500' }}">Tanpa registrasi. Setelah masuk, hanya data perusahaan yang Anda pilih yang tampil.</p>
@else
    <p class="text-xs font-semibold {{ $dark ? 'text-white/80' : 'text-slate-500' }} mb-2">Masuk sebagai Tamu ke data demo:</p>
    <div class="grid gap-2 {{ ($center ?? false) ? 'sm:grid-cols-3' : '' }}">
        @foreach ($orgs as $o)
            <form method="POST" action="{{ route('guest.login') }}">
                @csrf
                <input type="hidden" name="org" value="{{ $o->code }}">
                <button class="w-full flex items-center gap-2 min-h-12 px-4 py-2 rounded-xl border-2 text-sm font-semibold text-left transition {{ $dark ? 'border-white/40 bg-white/10 hover:bg-white hover:text-slate-900' : 'border-slate-200 hover:border-indigo-500 hover:bg-indigo-50' }}">
                    <span class="w-2.5 h-2.5 shrink-0 rounded-full" style="background: {{ $o->color }}"></span>
                    <span class="flex-1">{{ $o->name }}</span><span aria-hidden="true">→</span>
                </button>
            </form>
        @endforeach
    </div>
@endif
