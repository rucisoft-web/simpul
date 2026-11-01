# Panduan Instalasi Simpul ITSM Enterprise di cPanel

Simpul ITSM (IT Service Management & CMDB berbasis data model iTop + ITIL 4) adalah aplikasi Laravel 13 + MySQL. Panduan ini untuk hosting cPanel biasa (shared hosting atau VPS dengan cPanel), dengan dua jalur: **dengan Terminal/SSH** dan **tanpa Terminal** (File Manager + Cron Jobs).

Contoh di panduan memakai akun cPanel `userku`, folder program `/home/userku/simpul`, dan alamat `https://simpul.domainanda.com`. Sesuaikan dengan milik Anda.

> Simpul adalah aplikasi **demo/peragaan** edisi Enterprise: seluruh isi (organisasi, CI, layanan, SLA, kontrak, ±2.100 tiket) dimuat oleh data demo tiga perusahaan, yaitu PT Blue Bird Tbk, PT Medco Energi Internasional Tbk, dan UIN Sunan Kalijaga. Pengunjung memilih satu perusahaan di halaman depan, lalu masuk dengan tombol **Masuk sebagai Tamu**. Data perusahaan lain tidak ikut tampil.

## 1. Prasyarat hosting

| Komponen | Ketentuan | Tempat di cPanel |
|---|---|---|
| PHP | **8.3 atau lebih baru** | *MultiPHP Manager* atau *Select PHP Version* (CloudLinux) |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `dom`, `ctype`, `fileinfo`, `iconv`, `filter`, `session` | *Select PHP Version › Extensions* |
| Memori PHP | `memory_limit` minimal **128M** (pemuatan data demo memakai ±50 MB) | *MultiPHP INI Editor* |
| Database | MySQL 8 atau MariaDB 10.6+ | *MySQL Databases* |
| Cron | Wajib bila memakai reset demo harian (bagian 7) | *Cron Jobs* |
| SSL | Disarankan | *SSL/TLS Status* › Run AutoSSL |

Tidak perlu Node.js/`npm`: tampilan memakai Tailwind CSS, Alpine.js, Chart.js, dan D3 dari CDN. Tidak perlu `php artisan storage:link`.

## 2. Buat database

1. **MySQL Databases** › *Create New Database*: `simpul` (menjadi `userku_simpul`).
2. *Add New User*: misalnya `simpul` (menjadi `userku_simpul`) dengan kata sandi kuat.
3. *Add User To Database* › centang **ALL PRIVILEGES** › Make Changes.

## 3. Taruh program di server

Program diletakkan **di luar `public_html`** (misalnya `/home/userku/simpul`), supaya `.env` dan kode tidak bisa dibuka dari browser.

### Jalur A: dengan Terminal

```bash
cd ~
git clone https://github.com/rucisoft-web/simpul.git
cd simpul
composer install --no-dev --optimize-autoloader
```

Repo privat meminta username GitHub dan *personal access token*. Bila `composer` tidak ada: `curl -sS https://getcomposer.org/installer | php` lalu `php composer.phar install --no-dev --optimize-autoloader`. Bila `php -v` bukan 8.3, pakai path lengkap, misalnya `/opt/cpanel/ea-php83/root/usr/bin/php` atau `/opt/alt/php83/usr/bin/php`.

### Jalur B: tanpa Terminal

1. Pakai paket **`simpul-cpanel.zip`** (sudah berisi folder `vendor` produksi, tidak perlu Composer).
2. *File Manager* › `/home/userku` › **Upload** zip › klik kanan › **Extract** ke `/home/userku`. Hasilnya folder `/home/userku/simpul`.
3. Hapus file zip setelah diekstrak.

## 4. Arahkan domain ke folder `public`

**Pilihan 1 (disarankan):** *Domains* › Create A New Domain (misalnya `simpul.domainanda.com`), matikan *Share document root*, isi **Document Root** `simpul/public`. Aktifkan SSL.

**Pilihan 2 (domain utama terkunci di `public_html`):** salin seluruh isi `simpul/public` ke `public_html` (termasuk `.htaccess` dan folder `img`; aktifkan *Show Hidden Files*), lalu edit `public_html/index.php`, ganti tiga baris `__DIR__.'/../` menjadi:

```php
if (file_exists($maintenance = __DIR__.'/../simpul/storage/framework/maintenance.php')) {
require __DIR__.'/../simpul/vendor/autoload.php';
$app = require_once __DIR__.'/../simpul/bootstrap/app.php';
```

> Jangan mengekstrak seluruh program ke `public_html`. Bila document root terlanjur diarahkan ke folder `simpul` (bukan `simpul/public`), file `.htaccess` di folder `simpul` memblokir file sensitif dan meneruskan permintaan ke `public/`.

## 5. Isi file `.env`

Di folder `simpul`, salin `.env.example` menjadi `.env` (File Manager: klik kanan › *Copy*, lalu *Rename*), lalu ubah:

```dotenv
APP_NAME="Simpul ITSM"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://simpul.domainanda.com
APP_TIMEZONE=Asia/Jakarta
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=userku_simpul
DB_USERNAME=userku_simpul
DB_PASSWORD="kata-sandi-database"

SESSION_SECURE_COOKIE=true

# akun Administrator & Agen demo: GANTI kata sandinya di server publik
SIMPUL_ADMIN_EMAIL=admin@domainanda.com
SIMPUL_DEMO_PASSWORD="SandiKuat-2026"
# situs demo publik: data demo dibuat ulang setiap hari (lihat bagian 7)
SIMPUL_DEMO_RESET_DAILY=true
SIMPUL_DEMO_RESET_AT=02:00
# tombol "Jadwalkan demo Enterprise" di halaman depan (kosong = disembunyikan)
SIMPUL_WHATSAPP=6281234567890
```

Biarkan `APP_KEY=` kosong; diisi oleh `key:generate` di langkah 6. Di cPanel `DB_HOST` hampir selalu `localhost`. Kata sandi yang memuat `#` atau spasi diberi tanda kutip.

## 6. Kunci aplikasi, tabel, dan data demo

### Dengan Terminal

```bash
cd ~/simpul
php artisan key:generate --force
php artisan migrate --seed --force
php artisan optimize
```

`migrate --seed` membuat tabel lalu memuat data demo (±10–60 detik). Akun yang dibuat:

| Peran | Email | Kata sandi |
|---|---|---|
| Administrator | nilai `SIMPUL_ADMIN_EMAIL` (default `admin@simpul.test`) | nilai `SIMPUL_DEMO_PASSWORD` |
| Agen | `agent.bbrd@simpul.test`, `agent.medc@simpul.test`, `agent.uinsk@simpul.test` | nilai `SIMPUL_DEMO_PASSWORD` |
| Tamu | pilih perusahaan di halaman depan/login › **Masuk sebagai Tamu** (tanpa kata sandi) | — |

Administrator tambahan atau ganti kata sandi: `php artisan simpul:create-admin` (menanyakan nama, email, kata sandi; atau pakai `--name= --email= --password=`). Bila email sudah ada, akun itu dijadikan admin dan kata sandinya diganti.

### Tanpa Terminal (Cron Jobs sekali jalan)

Tambahkan perintah berikut satu per satu dengan jadwal *Once Per Minute*, tunggu 1–2 menit, lalu **hapus** cron tersebut sebelum menambah yang berikutnya:

1. `cd /home/userku/simpul && php artisan key:generate --force > storage/logs/setup-1.txt 2>&1`
2. `cd /home/userku/simpul && php artisan migrate --seed --force > storage/logs/setup-2.txt 2>&1`
3. `cd /home/userku/simpul && php artisan optimize > storage/logs/setup-3.txt 2>&1`

Periksa hasilnya di `simpul/storage/logs/setup-*.txt` (langkah 2 berakhir dengan `DemoSeeder ... DONE`). Bila cron memakai PHP lama, ganti `php` dengan path PHP 8.3, misalnya `/opt/cpanel/ea-php83/root/usr/bin/php`.

## 7. Cron permanen (untuk reset demo harian)

Bila `SIMPUL_DEMO_RESET_DAILY=true`, tambahkan cron *Once Per Minute*:

```
cd /home/userku/simpul && php artisan schedule:run >> /dev/null 2>&1
```

Setiap hari pada jam `SIMPUL_DEMO_RESET_AT` (WIB), seluruh data dihapus dan dibuat ulang: tiket buatan pengunjung/tamu dibersihkan, dan tanggal tiket kembali relatif ke hari ini sehingga dasbor selalu terlihat "hidup". Akun admin & agen dibuat ulang dengan `SIMPUL_ADMIN_EMAIL` dan `SIMPUL_DEMO_PASSWORD`; akun yang dibuat dengan `simpul:create-admin` **ikut terhapus**. Pengguna yang sedang login akan keluar saat reset.

Cek jadwal: `php artisan schedule:list`. Reset manual kapan saja: `php artisan demo:reset`.

## 8. Mulai memakai

1. Buka `https://simpul.domainanda.com`. Halaman depan disusun dengan alur AIDA: *Attention* (hero), *Interest* (tantangan & fitur), *Desire* (pilih perusahaan demo & paket Simpul Enterprise), lalu *Action* (masuk sebagai tamu atau jadwalkan demo lewat WhatsApp `SIMPUL_WHATSAPP`).
2. Pengunjung mengklik kartu perusahaan dulu, baru tombol **Masuk sebagai Tamu** aktif. Akun Tamu terkunci pada perusahaan itu: tidak ada pilihan "Semua Organisasi", dan tiket, CI, atau layanan perusahaan lain tidak bisa dibuka meskipun alamatnya diketik langsung. Pindah perusahaan lewat nama perusahaan di kanan atas › **Ganti ke**.
3. Administrator/Agen masuk di `https://simpul.domainanda.com/login` dan tetap bisa melihat **Semua Organisasi**.
4. Di setiap layar ada tombol **Bantuan** (ikon ?) di kanan atas, atau tekan tombol `?` di keyboard: panel berisi **konsep**, **cara menggunakan/input**, dan **keterangan isian & kolom** untuk layar yang sedang dibuka. Semua topik juga ada di `https://simpul.domainanda.com/panduan` (bisa dicetak).
5. Data yang diinput pengguna: **tiket** (Insiden, Permintaan Layanan, Problem, Perubahan) lewat **+ Tiket Baru**, **transisi status** lewat panel *Langkah berikutnya*, dan **catatan aktivitas** di detail tiket. Data induk (CI, topologi, layanan, SLA, kontrak, pengetahuan) dimuat oleh data demo.

> **Data demo.** Profil organisasi diambil dari sumber publik (Laporan Tahunan 2025, situs uin-suka.ac.id). Perangkat, IP, orang, dan tiket adalah **simulasi**, bukan data resmi organisasi tersebut.

## 9. Memperbarui program

```bash
cd ~/simpul
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan up
```

Tanpa Terminal: upload zip versi baru, ekstrak menimpa folder `simpul` (zip tidak berisi `.env`), lalu jalankan `cd /home/userku/simpul && php artisan migrate --force && php artisan optimize` lewat cron sekali jalan. Bila memakai Pilihan 2 (bagian 4), salin ulang isi `simpul/public` ke `public_html` (kecuali `index.php` yang sudah diedit).

### Instal ulang (data demo bersih)

```bash
cd ~/simpul
php artisan demo:reset
php artisan optimize
```

`demo:reset` menjalankan `migrate:fresh --seed`: **semua tabel dihapus** lalu dibuat ulang dengan data demo baru. Tanpa Terminal: jalankan `cd /home/userku/simpul && php artisan demo:reset > storage/logs/reset.txt 2>&1` sebagai cron sekali jalan.

## 10. Masalah umum

| Gejala | Solusi |
|---|---|
| Error 500 tanpa keterangan | Lihat `simpul/storage/logs/laravel.log`. Sementara boleh `APP_DEBUG=true`, lalu kembalikan ke `false`. |
| `Your PHP version … does not satisfy` | Ubah PHP domain ke 8.3, dan pastikan cron/Terminal juga memakai PHP 8.3. |
| `Class "Faker\Factory" not found` saat seeding | Pakai paket zip terbaru (versi lama belum menyertakan Faker di `vendor` produksi), atau jalankan `composer install --no-dev` dari repo terbaru. |
| `Allowed memory size … exhausted` saat seeding | Naikkan `memory_limit` ke 256M di *MultiPHP INI Editor*. |
| `SQLSTATE[HY000] [1045] Access denied` | Periksa awalan `userku_` pada nama database/user dan ALL PRIVILEGES. |
| `Duplicate entry 'BBRD' for key 'organizations_code_unique'` | Data demo dari percobaan sebelumnya sudah sebagian masuk. Jalankan `php artisan migrate:fresh --seed --force` (atau `php artisan demo:reset`), lalu `php artisan optimize`. |
| `SQLSTATE[42S02] … Table 'sessions' doesn't exist` | Migrasi belum dijalankan: `php artisan migrate --seed --force`. |
| `No application encryption key` | Jalankan `php artisan key:generate --force`. |
| Halaman polos tanpa gaya | Browser/jaringan memblokir CDN (`cdn.tailwindcss.com`, `cdnjs.cloudflare.com`). Buka dari jaringan lain. |
| Gambar panduan tidak muncul | Folder `public/img/panduan` belum ikut tersalin (Pilihan 2). |
| Semua halaman selain beranda 404 | `.htaccess` dari `public` belum ikut tersalin. |
| `Permission denied` di `storage` | Izin folder `storage` dan `bootstrap/cache` 755. |
| `419 Page Expired` saat login | `APP_URL` harus sama persis dengan alamat yang dibuka. Tanpa SSL, hapus `SESSION_SECURE_COOKIE=true`. |
| Jam tiket selisih 7 jam | Isi `APP_TIMEZONE=Asia/Jakarta` lalu `php artisan optimize`. |
| Perubahan `.env` tidak berlaku | `php artisan optimize:clear` lalu `php artisan optimize`. |
| Data demo tidak ter-reset tiap hari | Pastikan cron `schedule:run` aktif dan `SIMPUL_DEMO_RESET_DAILY=true`, lalu `php artisan optimize`. |
