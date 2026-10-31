<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Simpul ITSM</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.5"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"'] } } } };</script>
</head>
<body class="font-sans min-h-screen grid lg:grid-cols-2 bg-white">
    <div class="hidden lg:flex flex-col justify-between bg-[#070f2b] text-white p-12 relative overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-indigo-600/40 blur-3xl"></div>
        <a href="{{ route('landing') }}" class="relative flex items-center gap-2"><span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-indigo-600 font-extrabold">S</span><span class="font-extrabold">Simpul</span></a>
        <div class="relative"><p class="text-4xl font-extrabold leading-tight">Tiket, perangkat, layanan, dan SLA — terhubung.</p><p class="text-slate-300 mt-4">Data model iTop + praktik ITIL 4.</p></div>
        <p class="relative text-xs text-slate-500">Data demo simulasi</p>
    </div>
    <div class="flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <h1 class="text-2xl font-extrabold">Masuk</h1>
            <p class="text-sm text-slate-500 mt-1">Gunakan akun Anda, atau langsung coba sebagai tamu.</p>
            <form method="POST" action="{{ route('guest.login') }}" class="mt-6">@csrf
                <button class="w-full h-12 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/20">Coba sebagai Tamu →</button>
            </form>
            <div class="flex items-center gap-3 my-6 text-xs text-slate-400"><span class="flex-1 h-px bg-slate-200"></span>atau dengan akun<span class="flex-1 h-px bg-slate-200"></span></div>
            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-3">@csrf
                @error('email')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                <input name="email" type="email" value="{{ old('email') }}" required placeholder="Email" class="w-full h-11 rounded-xl border border-slate-200 px-3 text-sm outline-none focus:ring-2 focus:ring-indigo-500/30">
                <input name="password" type="password" required placeholder="Password" class="w-full h-11 rounded-xl border border-slate-200 px-3 text-sm outline-none focus:ring-2 focus:ring-indigo-500/30">
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember"> Ingat saya</label>
                <button class="w-full h-11 rounded-xl bg-slate-900 text-white font-semibold">Masuk</button>
            </form>
            <p class="text-xs text-slate-400 mt-6">Akun demo admin & agen tercantum di README proyek.</p>
        </div>
    </div>
</body>
</html>
