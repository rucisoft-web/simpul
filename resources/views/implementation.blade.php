<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panduan Implementasi — Simpul ITSM Enterprise</title>
    <meta name="description" content="Tahapan, tim, persiapan data, konfigurasi ITIL 4, pelatihan, go-live, dan operasional implementasi Simpul ITSM Enterprise.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.5?plugins=typography"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif'] } } } };</script>
    <style>
        .grad{background:linear-gradient(120deg,#38bdf8,#6366f1 50%,#a855f7)}
        .doc h2{scroll-margin-top:5.5rem}
        .doc table{display:block;overflow-x:auto;font-size:.875rem}
        .doc th{background:#f1f5f9}
        .doc code{overflow-wrap:anywhere}
        .doc th,.doc td{padding:.5rem .75rem!important;border:1px solid #e2e8f0;vertical-align:top}
        .doc ul:has(input[type=checkbox]){list-style:none;padding-left:0}
        .doc li:has(> input[type=checkbox]){display:flex;gap:.6rem;align-items:flex-start}
        .doc input[type=checkbox]{margin-top:.45rem;width:1rem;height:1rem;flex-shrink:0}
    </style>
</head>
<body class="font-sans text-slate-800 antialiased bg-white">

<header class="fixed top-0 inset-x-0 z-50 bg-[#070f2b]/90 backdrop-blur border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-6 text-white">
        <a href="{{ route('landing') }}" class="flex items-center gap-2"><span class="grid place-items-center w-9 h-9 rounded-xl grad font-extrabold">S</span><span class="font-extrabold">Simpul <span class="hidden sm:inline text-xs font-semibold text-sky-300 align-middle">Enterprise</span></span></a>
        <nav class="hidden md:flex gap-6 text-sm text-slate-300">
            <a href="{{ route('landing') }}#fitur" class="hover:text-white">Fitur</a>
            <a href="{{ route('landing') }}#demo" class="hover:text-white">Demo Perusahaan</a>
            <a href="{{ route('landing') }}#enterprise" class="hover:text-white">Enterprise</a>
            <a href="{{ route('implementation') }}" class="text-white font-semibold">Implementasi</a>
        </nav>
        <div class="flex-1"></div>
        <a href="{{ route('guide.index') }}" class="hidden sm:inline text-sm text-slate-300 hover:text-white">Panduan</a>
        <a href="{{ route('landing') }}#demo" class="text-sm font-semibold px-4 h-10 inline-flex items-center rounded-xl bg-white text-slate-900 hover:bg-sky-100">Coba Demo</a>
    </div>
</header>

<section class="bg-[#070f2b] text-white pt-28 pb-12">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-sm font-semibold text-sky-300">Panduan Implementasi</p>
        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">Dari kick-off sampai go-live dalam 10–14 minggu</h1>
        <p class="mt-3 text-slate-300 max-w-3xl">Langkah lengkap menerapkan Simpul ITSM Enterprise di organisasi Anda: tahapan, tim, persiapan teknis, penyiapan data CMDB, konfigurasi proses ITIL 4, pelatihan, go-live, dan operasional.</p>
    </div>
</section>

<main class="max-w-7xl mx-auto px-4 py-10 lg:grid lg:grid-cols-[240px_1fr] lg:gap-12">
    <aside class="mb-8 lg:mb-0">
        <div class="lg:sticky lg:top-24 rounded-2xl border border-slate-200 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Daftar isi</p>
            <ol class="space-y-1.5 text-sm">
                @foreach ($toc as $i => $t)
                    <li><a href="#{{ $t['id'] }}" class="flex gap-2 text-slate-600 hover:text-indigo-600"><span class="text-slate-400 w-4 shrink-0">{{ $i + 1 }}</span>{{ $t['title'] }}</a></li>
                @endforeach
            </ol>
        </div>
    </aside>
    <article class="doc prose prose-slate max-w-none prose-headings:font-extrabold prose-h2:mt-12 prose-h2:pt-6 prose-h2:border-t prose-h2:border-slate-200 first:prose-h2:mt-0 prose-code:before:content-none prose-code:after:content-none prose-code:bg-slate-100 prose-code:px-1 prose-code:rounded prose-a:text-indigo-600 min-w-0">
        {!! $html !!}
    </article>
</main>

<section class="pb-20">
    <div class="max-w-5xl mx-auto px-4">
        <div class="rounded-3xl grad p-10 text-white text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold">Siap memulai implementasi?</h2>
            <p class="mt-2 text-white/90">Jelajahi data demo dulu, lalu jadwalkan sesi asesmen dengan tim kami.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('landing') }}#demo" class="h-11 px-6 inline-flex items-center rounded-xl bg-white text-slate-900 font-semibold">Coba demo sebagai tamu</a>
                @if ($wa = config('simpul.whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}?text={{ rawurlencode('Halo, saya ingin mendiskusikan implementasi Simpul ITSM Enterprise.') }}" target="_blank" rel="noopener" class="h-11 px-6 inline-flex items-center rounded-xl border border-white/40 font-semibold hover:bg-white/10">Diskusikan implementasi</a>
                @endif
            </div>
        </div>
    </div>
</section>

<footer class="border-t border-slate-200 py-8 text-center text-sm text-slate-500">
    © {{ date('Y') }} Simpul ITSM Enterprise · Data model terinspirasi iTop, praktik mengikuti ITIL 4.
</footer>
</body>
</html>
