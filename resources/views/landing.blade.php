<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simpul ITSM Enterprise — Kelola Layanan TI Berbasis ITIL 4 & CMDB</title>
    <meta name="description" content="Aplikasi ITSM & CMDB berbasis data model iTop dan ITIL 4: service desk, topologi jaringan bertingkat, SLA, change, knowledge base.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.5"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif'] } } } };</script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.1/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}.grad{background:linear-gradient(120deg,#38bdf8,#6366f1 50%,#a855f7)}.text-grad{background:linear-gradient(120deg,#38bdf8,#818cf8,#c084fc);-webkit-background-clip:text;background-clip:text;color:transparent}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}.float{animation:float 6s ease-in-out infinite}</style>
</head>
<body class="font-sans text-slate-800 antialiased bg-white">
@php
    $wa = config('simpul.whatsapp');
    $waUrl = $wa ? 'https://wa.me/'.preg_replace('/\D/', '', $wa).'?text='.rawurlencode('Halo, saya ingin menjadwalkan demo Simpul ITSM Enterprise.') : null;
    $scen = [
        'BBRD' => ['Transportasi', '18 wilayah operasi & puluhan pool terhubung SD-WAN. MyBluebird, Driver App, pembayaran non-tunai, SAP — semua dipetakan ke layanan dan SLA.'],
        'MEDC' => ['Energi', 'Site migas di Sumatera Selatan, Natuna, Senoro, Tailan & Oman dengan VSAT dan jaringan OT. Migrasi SAP S/4HANA, mSeek AI, SOC.'],
        'UINSK' => ['Pendidikan Tinggi', '10 fakultas/pascasarjana, lab komputer, data center & DRC PTIPD. SUKAnet, Eduroam, SUKAdemia — lonjakan tiket saat KRS terlihat jelas.'],
    ];
@endphp

{{-- NAV --}}
<header class="fixed top-0 inset-x-0 z-50 bg-[#070f2b]/80 backdrop-blur border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-6 text-white">
        <a href="#" class="flex items-center gap-2"><span class="grid place-items-center w-9 h-9 rounded-xl grad font-extrabold">S</span><span class="font-extrabold">Simpul <span class="hidden sm:inline text-xs font-semibold text-sky-300 align-middle">Enterprise</span></span></a>
        <nav class="hidden md:flex gap-6 text-sm text-slate-300">
            <a href="#masalah" class="hover:text-white">Tantangan</a><a href="#fitur" class="hover:text-white">Fitur</a><a href="#demo" class="hover:text-white">Demo Perusahaan</a><a href="#enterprise" class="hover:text-white">Enterprise</a>
        </nav>
        <div class="flex-1"></div>
        <a href="{{ route('guide.index') }}" class="hidden sm:inline-flex items-center gap-1.5 text-sm text-slate-300 hover:text-white"><x-icon name="help" class="w-4 h-4"/> Panduan</a>
        @auth
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold px-4 h-10 inline-flex items-center rounded-xl bg-white text-slate-900">Buka Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="hidden sm:inline text-sm text-slate-300 hover:text-white">Masuk</a>
            <a href="#demo" class="text-sm font-semibold px-4 h-10 inline-flex items-center rounded-xl bg-white text-slate-900 hover:bg-sky-100">Coba Demo</a>
        @endauth
    </div>
</header>

@if (session('status'))
    <div class="fixed top-20 inset-x-0 z-40 flex justify-center px-4"><div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-2 shadow">{{ session('status') }}</div></div>
@endif

{{-- A: ATTENTION --}}
<section class="relative overflow-hidden bg-[#070f2b] text-white pt-32 pb-24">
    <div class="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full bg-indigo-600/30 blur-3xl"></div>
    <div class="absolute top-20 -right-40 w-[500px] h-[500px] rounded-full bg-sky-500/20 blur-3xl"></div>
    <div class="relative max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs text-sky-200">● ITSM & CMDB Enterprise · ITIL 4 · data model iTop</span>
            <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.05] tracking-tight">Satu layar untuk <span class="text-grad">seluruh layanan TI</span> grup usaha Anda.</h1>
            <p class="mt-5 text-lg text-slate-300 max-w-xl">Gangguan jaringan di pool, link VSAT di anjungan lepas pantai, atau Wi-Fi kampus saat KRS — Simpul menghubungkan tiket, perangkat, layanan, dan SLA sehingga tim Anda tahu <b class="text-white">apa yang terdampak, siapa yang menangani, dan kapan harus selesai.</b></p>
            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="h-12 px-6 inline-flex items-center rounded-xl grad font-bold shadow-lg shadow-indigo-500/30">Buka Dashboard →</a>
                @else
                    <a href="#demo" class="h-12 px-6 inline-flex items-center rounded-xl grad font-bold shadow-lg shadow-indigo-500/30 hover:opacity-90">Pilih perusahaan demo →</a>
                    <a href="#enterprise" class="h-12 px-6 inline-flex items-center rounded-xl border border-white/20 hover:bg-white/10 font-semibold">Lihat paket Enterprise</a>
                @endauth
            </div>
            <div class="mt-10 grid grid-cols-4 gap-4 max-w-lg">
                @foreach ([[number_format($stats['cis']), 'CI di CMDB'], [number_format($stats['tickets']), 'tiket demo'], [$stats['brands'], 'merek perangkat'], [$stats['sla'].'%', 'SLA tercapai']] as [$v, $l])
                    <div><p class="text-2xl font-extrabold">{{ $v }}</p><p class="text-xs text-slate-400">{{ $l }}</p></div>
                @endforeach
            </div>
        </div>
        <div class="relative float">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-2 shadow-2xl shadow-indigo-900/50">
                @if (file_exists(public_path('img/panduan/dashboard.png')))
                    <img src="{{ asset('img/panduan/dashboard.png') }}" alt="Tangkapan layar dashboard Simpul" class="rounded-xl w-full">
                @else
                    <div class="rounded-xl bg-slate-100 aspect-[16/10]"></div>
                @endif
            </div>
            <div class="absolute -bottom-6 -left-6 hidden sm:block rounded-2xl bg-white text-slate-800 p-4 shadow-xl w-60">
                <p class="text-xs text-slate-500">Insiden P1 terdeteksi monitoring</p><p class="font-bold text-sm">Link VSAT site lapangan down</p>
                <p class="text-xs mt-1"><span class="text-rose-600 font-semibold">12 CI terdampak</span> · TTR 4 jam</p>
            </div>
        </div>
    </div>
</section>

{{-- I: INTEREST — masalah --}}
<section id="masalah" class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-sm font-bold text-indigo-600 uppercase tracking-widest">Tantangan yang familiar</p>
        <h2 class="text-3xl sm:text-4xl font-extrabold mt-2 max-w-3xl">Tim TI sering bekerja buta: tiket di satu tempat, data perangkat di spreadsheet, topologi di kepala satu orang.</h2>
        <div class="grid md:grid-cols-3 gap-6 mt-12">
            @foreach ([
                ['🔍', 'Tidak tahu dampak', 'Router pool mati — layanan apa saja yang ikut down? Tanpa CMDB, analisis dampak hanya tebakan.'],
                ['⏱️', 'SLA dilanggar diam-diam', 'Tiket P1 tenggelam di antara permintaan reset password. Tidak ada yang tahu sampai pengguna marah.'],
                ['🧩', 'Multi-vendor, multi-lokasi', 'Cisco, Huawei, MikroTik, Fortinet, Ruijie tersebar di puluhan site. Garansi dan kontrak vendor terlupa.'],
            ] as [$e, $t, $d])
                <div class="rounded-2xl bg-white p-6 border border-slate-200"><p class="text-3xl">{{ $e }}</p><p class="font-bold text-lg mt-3">{{ $t }}</p><p class="text-slate-600 mt-2">{{ $d }}</p></div>
            @endforeach
        </div>
    </div>
</section>

{{-- I: INTEREST — fitur --}}
<section id="fitur" class="py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto">
            <p class="text-sm font-bold text-indigo-600 uppercase tracking-widest">Solusinya</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold mt-2">Data model iTop yang teruji + praktik ITIL 4 yang modern</h2>
            <p class="text-slate-600 mt-4">Setiap tiket terhubung ke layanan, CI, tim, dan SLA. Setiap perangkat terhubung ke lokasi, merek, kontrak, dan uplink-nya.</p>
        </div>
        @php
            $features = [
                ['Service Desk ITIL', 'Incident, Service Request, Problem, Change dengan siklus status, matriks prioritas dampak × urgensi, dan SLA TTO/TTR otomatis.', 'tickets', 'from-rose-500 to-orange-400'],
                ['Topologi Bertingkat', 'Pohon interaktif Internet → Firewall → Core → Distribusi → Akses → AP & endpoint, juga per lokasi.', 'topology', 'from-sky-500 to-indigo-500'],
                ['CMDB & Analisis Dampak', '18 kelas CI (iTop): perangkat jaringan, server, VM, aplikasi, proses bisnis — dengan relasi dependensi rekursif.', 'cmdb', 'from-emerald-500 to-teal-400'],
                ['Katalog Layanan & SLA', 'Service family, value stream, subkategori, SLA/SLT, kontrak OLA dan vendor.', 'services', 'from-violet-500 to-fuchsia-500'],
                ['Monitoring & Event', 'Alert dari Zabbix/PRTG/SolarWinds dikorelasikan menjadi insiden, terhubung ke CI.', 'events', 'from-amber-500 to-yellow-400'],
                ['Continual Improvement', 'Register perbaikan berkelanjutan dipetakan ke 34 praktik & 7 guiding principles ITIL 4.', 'itil', 'from-slate-700 to-slate-500'],
            ];
        @endphp
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-14">
            @foreach ($features as [$t, $d, $img, $g])
                <div class="group rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition">
                    <div class="aspect-[16/9] bg-gradient-to-br {{ $g }} p-3 overflow-hidden">
                        @if (file_exists(public_path("img/panduan/$img.png")))
                            <img src="{{ asset("img/panduan/$img.png") }}" alt="Tangkapan layar {{ $t }}" loading="lazy" class="rounded-lg shadow-lg w-full object-cover object-top h-full group-hover:scale-[1.03] transition">
                        @endif
                    </div>
                    <div class="p-6"><p class="font-bold text-lg">{{ $t }}</p><p class="text-slate-600 mt-2 text-sm">{{ $d }}</p></div>
                </div>
            @endforeach
        </div>
        <div class="mt-16 text-center">
            <p class="text-sm text-slate-500 mb-4">Multi-merek perangkat yang sudah ada di data demo</p>
            <div class="flex flex-wrap justify-center gap-2">
                @foreach ($brands as $b)<span class="px-3 py-1.5 rounded-full border border-slate-200 text-sm font-semibold" style="color: {{ $b->color }}">{{ $b->name }}</span>@endforeach
            </div>
        </div>
    </div>
</section>

{{-- D: DESIRE — pilih perusahaan demo --}}
<section id="demo" class="py-24 bg-[#070f2b] text-white scroll-mt-16">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-sm font-bold text-sky-300 uppercase tracking-widest">Bayangkan di organisasi Anda</p>
        <h2 class="text-3xl sm:text-4xl font-extrabold mt-2 max-w-3xl">Pilih satu perusahaan, lalu jelajahi layanan TI-nya dari dalam.</h2>
        <p class="text-slate-400 mt-3 max-w-3xl text-sm">Setiap perusahaan punya data demo sendiri: CMDB, topologi, layanan, SLA, kontrak, dan tiket 12 bulan. Setelah masuk, seluruh layar hanya menampilkan data perusahaan yang Anda pilih; pindah perusahaan lewat tautan <b class="text-slate-200">Ganti ke</b> di kanan atas. Profil disusun dari Laporan Tahunan 2025 dan situs resmi institusi; perangkat, IP, orang, dan tiket adalah simulasi dan tidak berafiliasi dengan organisasi terkait.</p>
        <div class="mt-12">
            @auth
                <div class="grid lg:grid-cols-3 gap-5">
                    @foreach ($orgs as $o)
                        <div class="rounded-2xl bg-white/5 border border-white/10 p-6">
                            <span class="text-xs font-bold px-2 py-1 rounded-full" style="background: {{ $o->color }}">{{ $scen[$o->code][0] ?? $o->industry }}</span>
                            <p class="text-xl font-extrabold mt-4">{{ $o->name }}</p>
                            <p class="text-slate-300 text-sm mt-2">{{ $scen[$o->code][1] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                @include('partials.demo-picker', ['orgs' => $orgs, 'variant' => 'cards', 'dark' => true, 'scen' => $scen])
            @endauth
        </div>
        <div class="grid sm:grid-cols-3 gap-6 mt-16">
            @foreach ([['Lebih cepat', 'menemukan akar masalah dengan analisis dampak & korelasi event'], ['Lebih terukur', 'SLA, MTTR, CSAT, dan beban tim dalam satu laporan'], ['Lebih siap audit', 'jejak perubahan, persetujuan CAB, dan register perbaikan']] as [$t, $d])
                <div class="border-l-2 border-sky-400 pl-4"><p class="text-2xl font-extrabold text-grad">{{ $t }}</p><p class="text-slate-300 text-sm mt-1">{{ $d }}</p></div>
            @endforeach
        </div>
    </div>
</section>

{{-- D: DESIRE — Simpul Enterprise (satu paket lengkap) --}}
<section id="enterprise" class="py-24 bg-slate-50 scroll-mt-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto">
            <p class="text-sm font-bold text-indigo-600 uppercase tracking-widest">Simpul Enterprise</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold mt-2">Satu paket lengkap untuk grup usaha, BUMN, dan perguruan tinggi</h2>
            <p class="text-slate-600 mt-4">Tanpa memilih-milih modul. Semua yang Anda lihat di demo ikut dipasang, disesuaikan dengan struktur organisasi, layanan, dan perangkat Anda.</p>
        </div>
        <div class="mt-12 rounded-3xl bg-[#070f2b] text-white p-8 sm:p-10 ring-4 ring-indigo-400/30 shadow-2xl shadow-indigo-900/20">
            <div class="grid lg:grid-cols-[1fr_320px] gap-10">
                <div class="grid sm:grid-cols-2 gap-x-8 gap-y-6">
                    @foreach ([
                        ['Service Desk ITIL 4', ['Incident, Service Request, Problem, Change', 'Matriks prioritas & SLA TTO/TTR otomatis', 'CAB/ECAB & kalender perubahan']],
                        ['CMDB & Topologi', ['18 kelas CI data model iTop', 'Analisis dampak rekursif', 'Topologi jaringan & lokasi bertingkat']],
                        ['Layanan & Kontrak', ['Katalog layanan & value stream', 'SLA/SLT, OLA, kontrak vendor', 'Peringatan kontrak & garansi habis']],
                        ['Multi-entitas', ['Induk & anak usaha dalam satu sistem', 'Data terpisah per organisasi', 'Laporan konsolidasi untuk pimpinan']],
                        ['Monitoring & Perbaikan', ['Korelasi event monitoring → insiden', 'Basis pengetahuan & Known Error', 'Continual Improvement Register']],
                        ['Implementasi & Pendampingan', ['On-premise atau cloud privat', 'Migrasi data dari spreadsheet/iTop', 'Pelatihan & pendampingan ITIL 4']],
                    ] as [$t, $items])
                        <div>
                            <p class="font-bold text-sky-300">{{ $t }}</p>
                            <ul class="mt-2 space-y-1.5 text-sm text-slate-200">@foreach ($items as $i)<li class="flex gap-2"><span class="text-emerald-400">✓</span>{{ $i }}</li>@endforeach</ul>
                        </div>
                    @endforeach
                </div>
                <div class="rounded-2xl bg-white/5 border border-white/10 p-6 flex flex-col">
                    <span class="self-start text-xs font-bold px-2 py-1 rounded-full grad">Enterprise</span>
                    <p class="text-2xl font-extrabold mt-3">Harga sesuai skala</p>
                    <p class="text-sm text-slate-300 mt-2">Dihitung dari jumlah entitas, lokasi, dan agen. Termasuk instalasi, migrasi data awal, dan pelatihan tim.</p>
                    <div class="mt-auto pt-6 space-y-3">
                        @if ($waUrl)
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="w-full h-11 inline-flex items-center justify-center rounded-xl grad font-bold">Jadwalkan demo Enterprise</a>
                        @endif
                        <a href="#demo" class="w-full h-11 inline-flex items-center justify-center rounded-xl border border-white/30 font-semibold hover:bg-white/10">Coba demo sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- A: ACTION --}}
<section class="py-24">
    <div class="max-w-5xl mx-auto px-4">
        <div class="rounded-3xl grad p-10 sm:p-14 text-white text-center shadow-2xl shadow-indigo-500/30">
            <h2 class="text-3xl sm:text-4xl font-extrabold">Lihat sendiri dalam 30 detik.</h2>
            <p class="mt-3 text-white/90 max-w-2xl mx-auto">Tanpa registrasi. Pilih perusahaan, masuk sebagai tamu, jelajahi tiket, CMDB, dan topologi jaringannya, lalu coba buat tiket Anda sendiri.</p>
            <div class="mt-8 max-w-3xl mx-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="h-12 px-7 inline-flex items-center rounded-xl bg-white text-slate-900 font-bold">Buka Dashboard →</a>
                @else
                    @include('partials.demo-picker', ['orgs' => $orgs, 'variant' => 'chips', 'dark' => true, 'center' => true, 'buttonClass' => 'h-12 px-7 rounded-xl bg-white text-slate-900 font-bold hover:bg-slate-100'])
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        @if ($waUrl)<a href="{{ $waUrl }}" target="_blank" rel="noopener" class="h-11 px-6 inline-flex items-center rounded-xl border border-white/40 font-semibold hover:bg-white/10">Jadwalkan demo Enterprise</a>@endif
                        <a href="{{ route('login') }}" class="h-11 px-6 inline-flex items-center rounded-xl border border-white/40 font-semibold hover:bg-white/10">Masuk dengan akun</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</section>

<footer class="border-t border-slate-200 py-8 text-center text-sm text-slate-500">
    © {{ date('Y') }} Simpul ITSM Enterprise · Dibangun dengan Laravel {{ app()->version() }} & MySQL · Data model terinspirasi iTop, praktik mengikuti ITIL 4.
</footer>
</body>
</html>
