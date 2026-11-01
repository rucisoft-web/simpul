<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · Simpul ITSM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.5"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'] },
            colors: { brand: { 50: '#eef6ff', 100: '#d9eaff', 500: '#2f6bff', 600: '#1f54e6', 700: '#1a43b8', 900: '#0b1a44' } } } } };
    </script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.1/cdn.min.js"></script>
    <style type="text/tailwindcss">
        [x-cloak] { display: none !important; }
        .card { @apply bg-white rounded-2xl border border-slate-200/80 shadow-sm; }
        .btn { @apply inline-flex items-center gap-1.5 h-9 px-3.5 rounded-xl text-sm font-semibold transition; }
        .btn-primary { @apply bg-brand-600 hover:bg-brand-700 text-white; }
        .btn-ghost { @apply border border-slate-200 bg-white hover:bg-slate-50 text-slate-700; }
        .input { @apply w-full h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500; }
        .th { @apply px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 bg-slate-50; }
        .td { @apply px-4 py-3 text-sm border-t border-slate-100; }
        .chip { @apply inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap; }
        .h1 { @apply text-2xl font-extrabold tracking-tight text-slate-900; }
        .sub { @apply text-sm text-slate-500 mt-1; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; } .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased" @keydown.escape.window="guide=false"
      @keydown.window="if ($event.key === '?' && !['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName)) openGuide()"
      x-data="{ nav: false, guide: false, guideHtml: '', async openGuide() { this.guide = true; if (!this.guideHtml) { this.guideHtml = await (await fetch('{{ route('guide.panel', \App\Support\Guide::keyForRoute(request()->route()?->getName())) }}')).text(); } } }">
@php
    $u = auth()->user();
    $scopeOrg = \App\Support\Scope::org();
    $roots = \App\Models\Organization::whereNull('parent_id')->orderBy('name')->get();
    $openCount = \App\Support\Scope::apply(\App\Models\Ticket::query())->open()->whereIn('type', ['incident', 'request'])->count();
    $menu = [
        'Operasional' => [
            ['dashboard', 'Dashboard', 'home', route('dashboard')],
            ['tickets.index', 'Service Desk', 'inbox', route('tickets.index'), $openCount],
            ['events.index', 'Monitoring & Event', 'activity', route('events.index')],
            ['changes.calendar', 'Kalender Perubahan', 'calendar', route('changes.calendar')],
        ],
        'Konfigurasi (CMDB)' => [
            ['cis.index', 'Configuration Items', 'database', route('cis.index')],
            ['topology.index', 'Topologi Jaringan', 'network', route('topology.index')],
            ['locations.index', 'Lokasi', 'map', route('locations.index')],
            ['brands.index', 'Merek & Model', 'tag', route('brands.index')],
        ],
        'Layanan' => [
            ['services.index', 'Katalog Layanan', 'grid', route('services.index')],
            ['slas.index', 'SLA & SLT', 'clock', route('slas.index')],
            ['contracts.index', 'Kontrak', 'file', route('contracts.index')],
        ],
        'Pengetahuan & Orang' => [
            ['knowledge.index', 'Basis Pengetahuan', 'book', route('knowledge.index')],
            ['contacts.index', 'Kontak & Tim', 'users', route('contacts.index')],
            ['organizations.index', 'Organisasi', 'building', route('organizations.index')],
        ],
        'ITIL 4' => [
            ['itil.index', 'Peta Praktik ITIL 4', 'layers', route('itil.index')],
            ['improvements.index', 'Continual Improvement', 'trending', route('improvements.index')],
            ['reports.index', 'Laporan & SLA', 'chart', route('reports.index')],
        ],
    ];
@endphp

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-[#0b1a44] text-slate-300 flex flex-col transition-transform lg:translate-x-0"
           :class="nav ? 'translate-x-0' : '-translate-x-full'">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-5 h-16 border-b border-white/10">
            <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-brand-600 text-white font-extrabold">S</span>
            <span><span class="block text-white font-bold leading-4">Simpul</span><span class="text-[11px] text-sky-300/80">ITSM · CMDB · ITIL 4</span></span>
        </a>
        <nav class="flex-1 overflow-y-auto scrollbar-thin px-3 py-4 space-y-5">
            @foreach ($menu as $group => $items)
                <div>
                    <p class="px-3 mb-1.5 text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ $group }}</p>
                    @foreach ($items as $item)
                        @php $active = request()->routeIs($item[0]) || request()->routeIs(\Illuminate\Support\Str::before($item[0], '.').'.*'); @endphp
                        <a href="{{ $item[3] }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $active ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                            <x-icon :name="$item[2]" class="w-[18px] h-[18px] {{ $active ? 'text-sky-300' : '' }}"/>
                            <span class="flex-1">{{ $item[1] }}</span>
                            @if (!empty($item[4]))<span class="text-[10px] font-bold bg-rose-500 text-white rounded-full px-1.5 py-0.5">{{ $item[4] }}</span>@endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        <div class="p-3 border-t border-white/10 text-xs text-slate-400">
            Data demo simulasi · Laravel {{ app()->version() }}
        </div>
    </aside>
    <div class="fixed inset-0 z-30 bg-black/40 lg:hidden" x-show="nav" x-cloak @click="nav=false"></div>

    {{-- Main --}}
    <div class="flex-1 lg:pl-64 min-w-0">
        <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center gap-3 px-4 lg:px-6">
            <button class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100" @click="nav=!nav"><x-icon name="menu"/></button>
            <form action="{{ route('tickets.index') }}" class="hidden md:flex items-center gap-2 bg-slate-100 rounded-xl px-3 h-10 w-80">
                <x-icon name="search" class="w-4 h-4 text-slate-400"/>
                <input name="q" placeholder="Cari tiket (ref / judul)…" class="bg-transparent outline-none text-sm flex-1">
            </form>
            <div class="flex-1"></div>
            {{-- Pemilih organisasi --}}
            <div class="relative" x-data="{ o: false }" @click.outside="o=false">
                <button @click="o=!o" class="flex items-center gap-2 h-10 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-sm">
                    <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $scopeOrg->color ?? '#64748b' }}"></span>
                    <span class="font-medium max-w-[180px] truncate">{{ $scopeOrg->name ?? 'Semua Organisasi' }}</span>
                    <x-icon name="down" class="w-4 h-4 text-slate-400"/>
                </button>
                <div x-show="o" x-cloak class="absolute right-0 mt-2 w-72 card p-1.5 z-50">
                    @if ($u->isGuest())
                        {{-- Tamu: data satu perusahaan saja; pindah perusahaan = masuk ulang sebagai tamu perusahaan lain --}}
                        <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Data demo aktif</p>
                        <p class="flex items-center gap-2 px-3 py-2 text-sm font-semibold"><span class="w-2.5 h-2.5 rounded-full" style="background: {{ $scopeOrg?->color }}"></span>{{ $scopeOrg?->name }}</p>
                        <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400 border-t border-slate-100">Ganti ke</p>
                        @foreach ($roots->where('id', '!=', $scopeOrg?->id) as $r)
                            <a href="{{ route('guest.link', ['org' => $r->code]) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-50 text-sm">
                                <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $r->color }}"></span>{{ $r->name }}</a>
                        @endforeach
                    @else
                        <a href="{{ route('scope', ['org' => 'all']) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-50 text-sm"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>Semua Organisasi</a>
                        @foreach ($roots as $r)
                            <a href="{{ route('scope', ['org' => $r->id]) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-50 text-sm {{ $scopeOrg?->id === $r->id ? 'bg-slate-50 font-semibold' : '' }}">
                                <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $r->color }}"></span>{{ $r->name }}</a>
                        @endforeach
                    @endif
                </div>
            </div>
            <a href="{{ route('tickets.create') }}" class="hidden sm:inline-flex items-center gap-1.5 h-10 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">
                <x-icon name="plus" class="w-4 h-4"/> Tiket Baru</a>
            {{-- Tombol Bantuan (kontekstual sesuai layar aktif): konsep & cara input data --}}
            <button @click="openGuide()" class="inline-flex items-center gap-1.5 h-10 px-3 sm:px-4 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-semibold" title="Bantuan layar ini: konsep & cara input data (tekan ?)">
                <x-icon name="help" class="w-4 h-4"/><span class="hidden sm:inline">Bantuan</span></button>
            <div class="relative" x-data="{ o: false }" @click.outside="o=false">
                <button @click="o=!o" class="flex items-center gap-2">
                    <span class="grid place-items-center w-9 h-9 rounded-full bg-gradient-to-br from-amber-300 to-rose-400 text-white text-sm font-bold">{{ mb_substr($u->name, 0, 1) }}</span>
                </button>
                <div x-show="o" x-cloak class="absolute right-0 mt-2 w-56 card p-2 z-50 text-sm">
                    <p class="px-2 font-semibold">{{ $u->name }}</p>
                    <p class="px-2 text-xs text-slate-500 mb-2">{{ ['admin' => 'Administrator', 'agent' => 'Agen Service Desk', 'guest' => 'Tamu (mode demo)'][$u->role] ?? $u->role }}</p>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="w-full flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-slate-50 text-rose-600"><x-icon name="logout" class="w-4 h-4"/> Keluar</button>
                    </form>
                </div>
            </div>
        </header>

        @if ($u->isGuest())
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200 text-amber-900 text-sm px-4 lg:px-6 py-2 flex items-center gap-2">
                <x-icon name="eye" class="w-4 h-4 shrink-0"/> <span>Mode tamu · data demo <b>{{ $scopeOrg?->name }}</b> (simulasi). Silakan coba membuat tiket dan menjalankan alur kerja.</span>
                <a href="{{ route('landing') }}#enterprise" class="ml-auto font-semibold underline hidden sm:inline whitespace-nowrap">Jadwalkan demo Enterprise</a>
            </div>
        @endif

        <main class="p-4 lg:p-6 max-w-[1600px] mx-auto">
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center gap-2"><x-icon name="check" class="w-4 h-4"/>{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif
            @hasSection('header')
                <div class="flex flex-wrap items-end gap-3 mb-6">
                    <div class="flex-1 min-w-0">@yield('header')</div>
                    @yield('actions')
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
{{-- Panel panduan --}}
<div x-show="guide" x-cloak class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-slate-900/40" @click="guide=false" x-show="guide" x-transition.opacity></div>
    <aside class="absolute right-0 top-0 h-full w-full max-w-xl bg-white shadow-2xl flex flex-col" x-show="guide"
           x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
        @php $gk = \App\Support\Guide::keyForRoute(request()->route()?->getName()); $gi = \App\Support\Guide::get($gk); @endphp
        <div class="h-16 px-5 flex items-center gap-3 border-b border-slate-200 shrink-0">
            <span class="grid place-items-center w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600"><x-icon name="book" class="w-5 h-5"/></span>
            <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold uppercase tracking-widest text-indigo-600">Bantuan layar ini</p><p class="font-bold truncate">{{ $gi['title'] }}</p></div>
            <a href="{{ route('guide.index') }}#{{ $gk }}" class="text-xs font-semibold text-indigo-600 hover:underline whitespace-nowrap">Semua topik</a>
            <button @click="guide=false" class="p-2 rounded-lg hover:bg-slate-100"><x-icon name="x" class="w-5 h-5"/></button>
        </div>
        <div class="flex-1 overflow-y-auto scrollbar-thin p-5">
            <div x-show="!guideHtml" class="text-sm text-slate-400">Memuat panduan…</div>
            <div x-html="guideHtml"></div>
        </div>
    </aside>
</div>
@stack('scripts')
</body>
</html>
