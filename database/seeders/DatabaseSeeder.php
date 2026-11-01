<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder demo hanya untuk database kosong; menjalankannya dua kali menimbulkan duplikat.
        if (Organization::exists()) {
            $this->command?->warn('Data demo sudah ada, seeding dilewati. Untuk membuat ulang: php artisan demo:reset');

            return;
        }

        // Satu transaksi: bila gagal di tengah jalan, tidak ada data setengah jadi yang tertinggal.
        DB::transaction(fn () => $this->call(DemoSeeder::class));
    }
}
