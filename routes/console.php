<?php

use App\Models\Ci;
use App\Models\Ticket;
use App\Support\Guide;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('demo:reset', function () {
    $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    $this->info('Data demo telah dibuat ulang.');
})->purpose('Hapus seluruh data dan buat ulang data demo (Bluebird, Medco, UIN Suka)');

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
    $targets = collect(Guide::all())->mapWithKeys(fn ($g) => [$g['image'] => strtr($g['path'], ['{open}' => $openTicket, '{core}' => $core])])->all();
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
