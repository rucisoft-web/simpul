# Simpul ITSM

Aplikasi IT Service Management & CMDB berbasis **Laravel 13 + MySQL**, dengan data model mengikuti
[iTop](https://www.itophub.io/wiki/page?id=latest:datamodel:start) dan alur kerja mengikuti **ITIL 4**.

## Fitur

| Area | Layar |
|---|---|
| Landing page (AIDA) | `/` — Attention (hero), Interest (tantangan & fitur), Desire (skenario & paket), Action (CTA). Tombol **Coba sebagai Tamu** |
| Bantuan & panduan | Tombol **Bantuan** (ikon ?) di kanan atas setiap layar — juga tombol `?` di keyboard — membuka konsep, cara input, dan keterangan isian/kolom untuk layar yang sedang dibuka. Semua topik ada di `/panduan` (tangkapan layar asli, ilustrasi konsep) |
| Operasional | Dashboard, Service Desk (Insiden, Permintaan, Problem, Perubahan), Monitoring & Event, Kalender Perubahan |
| CMDB | Configuration Items (18 kelas iTop), detail CI + analisis dampak rekursif, **topologi jaringan bertingkat** (D3), lokasi, merek & model |
| Layanan | Katalog layanan (family / value stream), SLA & SLT, kontrak OLA & vendor |
| Pengetahuan | FAQ, Known Error, dokumen; kontak, tim, organisasi |
| ITIL 4 | Peta Service Value Chain & 34 praktik, Continual Improvement Register, laporan SLA/MTTR/CSAT/heatmap |

## Data demo

- **PT Blue Bird Tbk** & **PT Medco Energi Internasional Tbk** — profil, entitas anak, wilayah operasi, aplikasi & inisiatif TI dari Laporan Tahunan 2025.
- **UIN Sunan Kalijaga Yogyakarta** — fakultas, unit, layanan PTIPD (SUKAnet, SUKAmail, SUKAdemia, Eduroam, SIGAP) dari uin-suka.ac.id.
- Perangkat multi-merek (Cisco, Juniper, Fortinet, Palo Alto, Huawei, Ruijie, MikroTik, Aruba, Siemens RUGGEDCOM, Dell, HPE, Lenovo, Fujitsu, NetApp, Nutanix, dll.), IP, orang, dan ±2.100 tiket 12 bulan adalah **simulasi**.

Akun demo (dibuat seeder):

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@simpul.test` (atau `SIMPUL_ADMIN_EMAIL`) | `password` (atau `SIMPUL_DEMO_PASSWORD`) |
| Agen | `agent.bbrd@simpul.test`, `agent.medc@simpul.test`, `agent.uinsk@simpul.test` | sama dengan admin |
| Tamu | tombol **Coba sebagai Tamu** (tanpa password) | — |

## Instalasi

```bash
composer install
cp .env.example .env   # set DB_CONNECTION=mysql, DB_DATABASE=itsm_hub
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Perintah tambahan:

```bash
php artisan demo:reset            # hapus & buat ulang data demo (tanggal relatif ke hari ini)
php artisan simpul:create-admin   # buat akun Administrator / ganti kata sandinya
php artisan panduan:screenshot    # ambil ulang gambar panduan (server harus berjalan; butuh Edge/Chrome)
```

Pengaturan khusus (`.env`):

| Variabel | Fungsi |
|---|---|
| `SIMPUL_ADMIN_EMAIL` | Email Administrator demo (default `admin@simpul.test`) |
| `SIMPUL_DEMO_PASSWORD` | Kata sandi Administrator & Agen demo. **Ganti di server publik.** |
| `SIMPUL_DEMO_RESET_DAILY` | `true` = data demo dibuat ulang tiap hari pukul `SIMPUL_DEMO_RESET_AT` (butuh cron `schedule:run`) |
| `APP_TIMEZONE` | Default `Asia/Jakarta` |

Instalasi di hosting cPanel (dengan atau tanpa Terminal): lihat panduan `PANDUAN-CPANEL.md` di paket `simpul-cpanel.zip`.

Frontend memakai Tailwind CSS, Alpine.js, Chart.js, dan D3 via CDN — tidak perlu `npm build`.

## Struktur penting

- `database/migrations/2026_10_31_000100_create_itsm_tables.php` — skema (iTop + ITIL 4)
- `database/seeders/data/OrgProfiles.php` — profil 3 organisasi; `DemoSeeder.php` — generator topologi, CI, tiket
- `app/Support/Itil.php` — tipe tiket, siklus status, matriks prioritas, praktik ITIL 4
- `app/Support/Guide.php` — isi panduan penggunaan
