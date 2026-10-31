<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panduan Penggunaan · Simpul ITSM</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.5"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif'] } } } };</script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.1/cdn.min.js"></script>
    <style type="text/tailwindcss">.chip { @apply inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap; }</style>
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800" x-data="{ toc: false, active: '' }">
<header class="sticky top-0 z-40 h-16 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 h-full flex items-center gap-3">
        <button class="lg:hidden p-2 -ml-2" @click="toc=!toc"><x-icon name="menu"/></button>
        <a href="{{ route('landing') }}" class="flex items-center gap-2"><span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-indigo-600 text-white font-extrabold">S</span>
            <span class="font-extrabold">Simpul <span class="font-medium text-slate-400">· Panduan Penggunaan</span></span></a>
        <div class="flex-1"></div>
        {{-- Tombol aksi di kanan atas --}}
        <button onclick="window.print()" class="hidden sm:inline-flex items-center gap-1.5 h-10 px-4 rounded-xl border border-slate-200 bg-white text-sm font-semibold hover:bg-slate-50"><x-icon name="printer" class="w-4 h-4"/> Cetak / PDF</button>
        @auth
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Kembali ke aplikasi →</a>
        @else
            <form method="POST" action="{{ route('guest.login') }}">@csrf<button class="h-10 px-4 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Coba sebagai Tamu →</button></form>
        @endauth
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 py-8 grid lg:grid-cols-[260px_1fr] gap-8">
    <aside class="lg:block print:hidden" :class="toc ? 'fixed inset-0 z-30 bg-white p-6 pt-20 overflow-y-auto' : 'hidden'">
        <nav class="lg:sticky lg:top-24 space-y-5 text-sm">
            @foreach ($groups as $g => $keys)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ $g }}</p>
                    @foreach ($keys as $k)
                        <a href="#{{ $k }}" @click="toc=false" class="block px-3 py-1.5 rounded-lg hover:bg-white" :class="active === '{{ $k }}' ? 'bg-white font-semibold text-indigo-600 shadow-sm' : 'text-slate-600'">{{ $items[$k]['title'] }}</a>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </aside>

    <main class="space-y-8 min-w-0">
        <div class="rounded-2xl bg-gradient-to-br from-[#0b1a44] to-indigo-700 text-white p-8">
            <p class="text-sm text-sky-300 font-semibold">Panduan Penggunaan</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold mt-1">Konsep & cara input setiap layar</h1>
            <p class="text-slate-200 mt-3 max-w-3xl">Panduan ini menjelaskan konsep ITIL 4 dan data model iTop di balik setiap layar Simpul, langkah-langkah pengisian, serta arti setiap kolom. Setiap bagian dilengkapi tangkapan layar dari data demo dan ilustrasi konsep.</p>
            <div class="grid sm:grid-cols-3 gap-3 mt-6 text-sm">
                <div class="rounded-xl bg-white/10 p-3"><b>{{ count($items) }}</b> topik panduan</div>
                <div class="rounded-xl bg-white/10 p-3">Tombol <b>Panduan</b> di kanan atas aplikasi membuka bagian yang sesuai layar aktif</div>
                <div class="rounded-xl bg-white/10 p-3">Klik <b>Buka layar ini</b> untuk mencoba langsung</div>
            </div>
        </div>

        @foreach ($groups as $g => $keys)
            <div>
                <h2 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-3">{{ $g }}</h2>
                <div class="space-y-6">
                    @foreach ($keys as $k)
                        @include('guide.section', ['key' => $k, 'item' => $items[$k]])
                    @endforeach
                </div>
            </div>
        @endforeach

        <p class="text-xs text-slate-400 text-center pb-8">Profil organisasi bersumber dari Laporan Tahunan 2025 PT Blue Bird Tbk & PT Medco Energi Internasional Tbk serta uin-suka.ac.id. Seluruh perangkat, IP, nama orang, dan tiket adalah data simulasi.</p>
    </main>
</div>
<script>
// Sorot topik di daftar isi sesuai bagian yang sedang terlihat.
const spy = new IntersectionObserver(entries => entries.forEach(e => {
    if (e.isIntersecting) document.body._x_dataStack && (document.body._x_dataStack[0].active = e.target.id);
}), { rootMargin: '-30% 0px -60% 0px' });
document.querySelectorAll('main section[id]').forEach(s => spy.observe(s));
</script>
</body>
</html>
