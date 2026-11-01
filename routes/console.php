<?php

use App\Models\Ci;
use App\Models\Ticket;
use App\Support\Guide;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

Artisan::command('demo:reset', function () {
    $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    $this->info('Data demo telah dibuat ulang.');
})->purpose('Hapus seluruh data dan buat ulang data demo (Bluebird, Medco, UIN Suka)');

// Situs demo publik: data demo dibuat ulang setiap hari (SIMPUL_DEMO_RESET_DAILY=true + cron schedule:run).
if (config('simpul.demo_reset_daily')) {
    Schedule::command('demo:reset')->dailyAt(config('simpul.demo_reset_at'))->timezone(config('app.timezone'));
}

/*
 * Membuat akun Administrator atau mengganti kata sandinya bila email sudah ada.
 *   php artisan simpul:create-admin --name="Administrator" --email=admin@domain.com --password='SandiKuat2026'
 */
Artisan::command('simpul:create-admin {--name=} {--email=} {--password=}', function () {
    $name = $this->option('name') ?: $this->ask('Nama', 'Administrator');
    $email = $this->option('email') ?: $this->ask('Email');
    $password = $this->option('password') ?: $this->secret('Kata sandi (minimal 8 karakter)');

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Email tidak valid.');

        return 1;
    }
    if (strlen((string) $password) < 8) {
        $this->error('Kata sandi minimal 8 karakter.');

        return 1;
    }

    $user = User::where('email', $email)->first();
    if ($user) {
        $user->update(['name' => $name, 'password' => $password, 'role' => 'admin']);
        $this->info("Akun {$email} dijadikan Administrator dan kata sandinya diganti.");
    } else {
        User::create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => 'admin']);
        $this->info("Administrator {$email} dibuat.");
    }

    return 0;
})->purpose('Buat akun Administrator atau ganti kata sandinya');

/*
 * Mengambil tangkapan layar setiap topik panduan ke public/img/panduan/*.png memakai Edge/Chrome headless.
 * Jalankan saat server aktif:  php artisan serve  lalu  php artisan panduan:screenshot
 */
Artisan::command('panduan:screenshot {--url=http://127.0.0.1:8000} {--browser=} {--only=}', function () {
    $browser = $this->option('browser') ?: collect([
        env('BROWSER_PATH'),
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        '/usr/bin/google-chrome', '/usr/bin/chromium', '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    ])->filter()->first(fn ($p) => is_file($p));
    if (! $browser) {
        $this->error('Browser Chrome/Edge tidak ditemukan. Gunakan --browser="path".');

        return 1;
    }

    $openTicket = Ticket::where('type', 'incident')->where('status', 'assigned')->whereHas('logs')->latest()->value('ref') ?? Ticket::value('ref');
    $core = Ci::where('network_role', 'core')->value('id');
    $ids = ['{open}' => $openTicket, '{core}' => $core, '{svc}' => \App\Models\Service::whereHas('cis')->value('id'),
        '{faq}' => \App\Models\Faq::value('id'), '{ke}' => \App\Models\KnownError::whereNotNull('problem_id')->value('id') ?? \App\Models\KnownError::value('id')];
    $targets = collect(Guide::all())->mapWithKeys(fn ($g) => [$g['image'] => strtr($g['path'], $ids)])->all();
    $targets['topology'] .= '?org='.\App\Models\Organization::where('code', 'BBRD')->value('id');
    if ($only = $this->option('only')) {
        $targets = array_intersect_key($targets, array_flip(explode(',', $only)));
    }

    $dir = public_path('img/panduan');
    @mkdir($dir, 0775, true);
    $base = rtrim($this->option('url'), '/');
    foreach ($targets as $name => $path) {
        // Halaman login diambil tanpa sesi; layar lain lewat tautan masuk-sebagai-tamu.
        $url = $name === 'login' ? $base.$path : $base.'/coba-sebagai-tamu?next='.urlencode($path);
        $profile = storage_path('framework/shot-'.Str::random(8));
        $file = $dir.DIRECTORY_SEPARATOR.$name.'.png';
        $cmd = sprintf('"%s" --headless=new --disable-gpu --hide-scrollbars --no-first-run --force-device-scale-factor=1 --window-size=1440,900 --virtual-time-budget=8000 --user-data-dir="%s" --screenshot="%s" "%s"',
            $browser, $profile, $file, $url);
        exec($cmd.' 2>&1', $out, $code);
        \Illuminate\Support\Facades\File::deleteDirectory($profile);
        $this->line(sprintf('%-16s %s  %s', $name, is_file($file) ? '✓' : '✗', $path));
    }
    $this->info('Selesai: '.count($targets).' gambar di public/img/panduan');
})->purpose('Ambil tangkapan layar untuk Panduan Penggunaan');
