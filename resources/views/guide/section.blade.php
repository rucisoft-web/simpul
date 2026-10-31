@php
    $compact = $compact ?? false;
    $openUrl = str_contains($item['path'], '{') ? null : (auth()->check() ? url($item['path']) : route('guest.link', ['next' => $item['path']]));
    $img = 'img/panduan/'.$item['image'].'.png';
@endphp
<section id="{{ $key }}" class="scroll-mt-24 {{ $compact ? '' : 'bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8' }}">
    @unless ($compact)
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600"><x-icon :name="$item['icon'] ?? 'book'"/></span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 flex-1">{{ $item['title'] }}</h2>
            @if ($openUrl)<a href="{{ $openUrl }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:underline">Buka layar ini →</a>@endif
        </div>
    @endunless

    @if (file_exists(public_path($img)))
        <figure class="{{ $compact ? 'mb-4' : 'mb-6' }}">
            <a href="{{ asset($img) }}" target="_blank" class="block rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition">
                <img src="{{ asset($img) }}" alt="Tangkapan layar: {{ $item['title'] }}" loading="lazy" class="w-full">
            </a>
            <figcaption class="text-xs text-slate-400 mt-1.5">Gambar: tampilan layar {{ $item['title'] }} (klik untuk memperbesar).</figcaption>
        </figure>
    @endif

    <div class="{{ $compact ? 'space-y-5' : 'grid lg:grid-cols-2 gap-8' }}">
        <div class="space-y-5">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-2">Konsep</h3>
                @foreach ($item['concept'] as $p)<p class="text-sm text-slate-700 leading-relaxed mb-2">{!! $p !!}</p>@endforeach
            </div>
            @isset($item['diagram'])
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-2">Ilustrasi konsep</h3>
                    @include('guide.diagrams', ['name' => $item['diagram']])
                </div>
            @endisset
        </div>
        <div class="space-y-5">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-2">Cara menggunakan / input</h3>
                <ol class="space-y-2">
                    @foreach ($item['steps'] as $i => $s)
                        <li class="flex gap-3 text-sm text-slate-700"><span class="grid place-items-center w-6 h-6 shrink-0 rounded-full bg-indigo-600 text-white text-xs font-bold">{{ $i + 1 }}</span><span class="pt-0.5 leading-relaxed">{!! $s !!}</span></li>
                    @endforeach
                </ol>
            </div>
            @if (!empty($item['fields']))
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-2">Keterangan isian & kolom</h3>
                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <table class="w-full text-sm">
                            @foreach ($item['fields'] as $f => $d)
                                <tr class="border-t border-slate-100 first:border-0"><td class="px-3 py-2 font-semibold text-slate-800 bg-slate-50 w-2/5 align-top">{{ $f }}</td><td class="px-3 py-2 text-slate-600">{{ $d }}</td></tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endif
            @if (!empty($item['tips']))
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-3">
                    @foreach ($item['tips'] as $t)<p class="text-sm text-amber-900">💡 {{ $t }}</p>@endforeach
                </div>
            @endif
        </div>
    </div>
    @if ($compact)
        <div class="mt-6 pt-4 border-t border-slate-100 flex gap-2">
            <a href="{{ route('guide.index') }}#{{ $key }}" class="flex-1 text-center h-10 leading-10 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Buka panduan lengkap</a>
        </div>
    @endif
</section>
