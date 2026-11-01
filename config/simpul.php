<?php

/*
 * Pengaturan khusus Simpul ITSM (diisi lewat .env).
 */
return [
    // Email akun Administrator yang dibuat seeder demo.
    'admin_email' => env('SIMPUL_ADMIN_EMAIL', 'admin@simpul.test'),

    // Kata sandi akun Administrator & Agen demo. Di server publik WAJIB diganti dari "password".
    'demo_password' => env('SIMPUL_DEMO_PASSWORD', 'password'),

    // Situs demo: hapus & buat ulang data demo setiap hari (tiket buatan tamu dibersihkan,
    // tanggal tiket kembali relatif ke hari ini). Butuh cron "schedule:run" setiap menit.
    'demo_reset_daily' => (bool) env('SIMPUL_DEMO_RESET_DAILY', false),
    'demo_reset_at' => env('SIMPUL_DEMO_RESET_AT', '02:00'),
];
