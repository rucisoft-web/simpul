## Ringkasan

Implementasi Simpul Enterprise di satu organisasi berjalan dalam 7 fase selama sekitar 10–14 minggu, dari kick-off sampai hypercare. Pekerjaan terberatnya ada di penyiapan data (CMDB, layanan, SLA) dan penyelarasan proses ITIL 4, bukan di instalasi server.

Simpul adalah aplikasi IT Service Management dan CMDB berbasis Laravel 13 + MySQL. Data modelnya mengikuti iTop (Organization, Location, Person, Team, FunctionalCI, Service, SLA, Contract, Ticket) dan alur kerjanya mengikuti ITIL 4: Insiden, Permintaan Layanan, Problem, dan Perubahan.

Panduan ini untuk tim Rucisoft yang memimpin implementasi dan untuk tim TI pelanggan (CIO/Kepala TI, Service Desk, tim infrastruktur). Langkah teknis instalasi ada di PANDUAN-CPANEL.md di paket `simpul-cpanel.zip`; dokumen ini tidak mengulanginya.

## Tahapan implementasi

Tujuh fase berurutan; setiap fase ditutup dengan keluaran yang disetujui sponsor sebelum fase berikutnya dimulai. Durasi adalah perkiraan untuk satu organisasi induk dengan 2–5 entitas anak dan 10–30 lokasi.

| Fase | Perkiraan durasi | Kegiatan utama | Keluaran (gerbang) |
| --- | --- | --- | --- |
| 0. Persiapan & kick-off | 1 minggu | Tetapkan sponsor, tim inti, ruang lingkup (entitas, lokasi, layanan), jadwal | Piagam proyek & jadwal disetujui |
| 1. Asesmen proses & data | 1–2 minggu | Wawancara Service Desk & tim teknis; inventaris sumber data (spreadsheet aset, monitoring, kontrak vendor, daftar aplikasi) | Laporan asesmen + daftar sumber data |
| 2. Desain layanan & proses | 2 minggu | Katalog layanan, matriks prioritas, target SLA/SLT, tim penugasan, alur Perubahan (CAB) | Dokumen desain disetujui pemilik proses |
| 3. Instalasi & penyiapan data | 2–3 minggu | Pasang server; isi templat data; muat ke Simpul; verifikasi topologi & relasi CI | Simpul berjalan dengan data pelanggan |
| 4. Pelatihan & uji coba (UAT) | 1–2 minggu | Pelatihan per peran; skenario uji end-to-end; perbaikan data | Berita acara UAT |
| 5. Go-live | 1 minggu | Cut-over dari kanal lama (email/WA/spreadsheet) ke Simpul; komunikasi ke pengguna | Service Desk resmi memakai Simpul |
| 6. Hypercare & serah terima | 2–4 minggu | Pendampingan harian, tinjauan SLA mingguan, register perbaikan | Berita acara serah terima operasional |

## Tim & peran

Satu orang boleh memegang beberapa peran, tetapi pemilik proses dan administrator data harus ditunjuk dengan nama sebelum Fase 2.

| Peran | Dari | Tanggung jawab | Akun di Simpul |
| --- | --- | --- | --- |
| Sponsor | Pelanggan (CIO / Kepala TI / Kepala PTIPD) | Menyetujui ruang lingkup, target SLA, dan gerbang tiap fase | Admin (baca laporan) |
| Manajer proyek | Rucisoft | Jadwal, risiko, koordinasi, laporan mingguan | Admin |
| Pemilik proses | Pelanggan (Kepala Service Desk, Ketua CAB) | Menetapkan aturan Insiden, Permintaan, Problem, Perubahan | Admin |
| Administrator data | Pelanggan (tim infrastruktur / aset) | Mengisi dan memverifikasi templat data CMDB, lokasi, kontrak | Admin |
| Konsultan ITIL 4 | Rucisoft | Desain layanan & SLA, pelatihan, pendampingan hypercare | Admin |
| Teknisi implementasi | Rucisoft | Instalasi server, pemuatan data, cadangan & pembaruan | Admin |
| Agen Service Desk & resolver | Pelanggan (per tim) | Menangani tiket sesuai tim penugasan | Agen |

## Persiapan teknis & instalasi

Simpul cukup dipasang di satu server web dengan PHP 8.3 dan MySQL; tidak perlu Node.js, Redis, atau layanan antrean terpisah.

| Komponen | Ketentuan |
| --- | --- |
| Server | Hosting cPanel, VPS, atau server on-premise pelanggan (Linux + Apache/Nginx) |
| PHP | 8.3 atau lebih baru; ekstensi `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `dom`, `ctype`, `fileinfo`; `memory_limit` minimal 128M |
| Database | MySQL 8 atau MariaDB 10.6+, satu database khusus Simpul |
| Domain & SSL | Subdomain khusus, mis. `itsm.perusahaan.co.id`, dengan SSL aktif |
| Akses keluar | Browser pengguna perlu menjangkau `cdn.tailwindcss.com` dan `cdnjs.cloudflare.com` (tampilan dimuat dari CDN) |
| Cron | `php artisan schedule:run` setiap menit |
| Cadangan | Dump database harian + salinan `.env` |

Langkah instalasi (dengan atau tanpa Terminal) ada di PANDUAN-CPANEL.md. Untuk instalasi pelanggan, perbedaannya dengan situs demo:

1. Isi `SIMPUL_ADMIN_EMAIL` dan `SIMPUL_DEMO_PASSWORD` dengan nilai pelanggan, lalu ganti kata sandi setiap admin lewat `php artisan simpul:create-admin`.
2. Biarkan `SIMPUL_DEMO_RESET_DAILY=false`. Reset harian menghapus seluruh data dan hanya cocok untuk situs demo.
3. Jangan jalankan `demo:reset` atau `migrate:fresh` di server pelanggan setelah data dimuat.

## Penyiapan data

Data induk dikumpulkan pelanggan dalam templat spreadsheet, lalu dimuat oleh teknisi Rucisoft; versi saat ini belum punya layar input atau impor untuk data induk (lihat Batasan). Muat dalam urutan tabel di bawah, karena setiap baris merujuk baris di atasnya.

| Urutan | Data | Kolom utama di Simpul | Sumber umum di pelanggan |
| --- | --- | --- | --- |
| 1 | Organisasi & entitas anak | kode, nama, industri, induk, warna | Struktur grup / SK organisasi |
| 2 | Lokasi (bertingkat) | nama, tipe (hq, campus, datacenter, region, office, building, pool, site), induk, alamat, kota, koordinat | Daftar kantor, gedung, site |
| 3 | Orang | nama depan/belakang, email, telepon, jabatan, NIP, lokasi, atasan | Data SDM / direktori email |
| 4 | Tim penugasan & anggota | nama tim, email tim, fungsi; anggota + peran (Team Lead, Agent L1/L2/L3) | Struktur unit TI |
| 5 | Merek & model | merek, model, tipe perangkat | Inventaris aset |
| 6 | Configuration Item | kelas CI (18 kelas iTop), nama, status, kritikalitas, merek/model, lokasi, tim pendukung, serial, nomor aset, IP manajemen, OS/firmware, garansi | Spreadsheet aset, NMS, hypervisor |
| 7 | Uplink & relasi CI | perangkat induk (uplink) untuk topologi; relasi dependensi aplikasi → software → VM → host | Diagram jaringan, dokumentasi aplikasi |
| 8 | Katalog layanan | service family, layanan, value stream, subkategori (insiden/permintaan), CI pendukung | Daftar aplikasi & layanan TI |
| 9 | SLA & SLT | nama SLA, target TTO/TTR per prioritas dan tipe | Perjanjian layanan internal |
| 10 | Kontrak | kontrak pelanggan/OLA dan vendor: penyedia, periode, nilai, SLA, layanan & CI tercakup | Dokumen pengadaan |
| 11 | Basis pengetahuan | kategori FAQ, FAQ, Known Error, dokumen/prosedur | Wiki, SOP, catatan tim |

Cara memuat pada versi ini:

1. Pelanggan mengisi templat, satu sheet per baris tabel di atas, memakai kode dan nama yang konsisten antar sheet.
2. Teknisi Rucisoft menyusun seeder khusus pelanggan dengan pola `database/seeders/data/OrgProfiles.php` dan `DemoSeeder.php`, tetapi membaca templat alih-alih membuat data simulasi.
3. Muat ke database kosong di server uji, periksa layar Organisasi, Lokasi, CMDB, Topologi, dan Katalog Layanan bersama administrator data, lalu ulangi sampai benar.
4. Muat sekali ke server produksi sebelum go-live. Tiket lama dari sistem sebelumnya cukup diringkas sebagai referensi, tidak perlu dimigrasi.

Kriteria data siap: setiap CI punya lokasi dan tim; setiap perangkat jaringan punya uplink sampai ke WAN edge; setiap layanan punya minimal satu subkategori dan satu CI pendukung; tidak ada email orang yang ganda.

## Konfigurasi proses ITIL 4

Fase 2 menyepakati aturan kerja di atas siklus status yang sudah tertanam di Simpul; yang disepakati adalah siapa melakukan apa dan dalam berapa lama, bukan statusnya.

| Proses | Siklus status di Simpul | Yang perlu disepakati pelanggan |
| --- | --- | --- |
| Insiden | Baru → Ditugaskan → (Tertunda / Eskalasi) → Selesai → Ditutup; bisa dibuka kembali | Kanal masuk resmi, siapa menetapkan Dampak & Urgensi, kapan eskalasi, siapa menutup |
| Permintaan Layanan | Baru → (Menunggu Persetujuan → Disetujui/Ditolak) → Ditugaskan → Selesai → Ditutup | Subkategori yang butuh persetujuan atasan, siapa penyetuju |
| Problem | Baru → Ditugaskan → Known Error → Selesai → Ditutup | Kriteria membuka Problem (mis. 3 insiden serupa dalam 30 hari), pemilik Known Error |
| Perubahan | Baru → Tervalidasi → Menunggu Persetujuan → Disetujui → Terjadwal → Diimplementasi → Dipantau (PIR) → Ditutup | Daftar Standard Change (pra-disetujui), anggota CAB/ECAB, jadwal rapat CAB, periode bebas perubahan |

Prioritas dihitung otomatis dari matriks Dampak (1 Departemen/banyak layanan, 2 Satu layanan, 3 Individu) × Urgensi (1 Kritis s.d. 4 Rendah). Target bawaan untuk tiket baru:

| Prioritas | TTO (diambil agen) | TTR (selesai) |
| --- | --- | --- |
| P1 Kritis | 15 menit | 4 jam |
| P2 Tinggi | 30 menit | 8 jam |
| P3 Sedang | 2 jam | 24 jam |
| P4 Rendah | 4 jam | 72 jam |

Permintaan Layanan mendapat batas dua kali lebih panjang. Bila pelanggan menyepakati angka lain, teknisi mengubah `DEFAULT_SLT` di `app/Support/Itil.php`; target per SLA di layar SLA & SLT saat ini bersifat informasi dan belum dipakai saat menghitung tenggat tiket.

Tim penugasan otomatis mengikuti subkategori layanan yang dipilih pelapor. Pastikan setiap subkategori jatuh ke tim yang benar sebelum UAT.

## Pelatihan, uji coba, go-live & hypercare

### Pelatihan per peran

| Peran | Durasi | Materi | Bukti lulus |
| --- | --- | --- | --- |
| Administrator | 1 hari | Instalasi & pembaruan (PANDUAN-CPANEL), `simpul:create-admin`, cadangan database, pemuatan data, pengguna & peran | Bisa memulihkan cadangan ke server uji |
| Agen service desk | 1 hari | Membuat tiket, matriks Dampak × Urgensi, transisi status, komentar, menautkan CI & layanan, FAQ dan Known Error | Menyelesaikan 5 skenario UAT tanpa bantuan |
| Pemilik proses (Problem, Change, SLA) | ½ hari | Problem → Known Error, alur CAB, kalender perubahan, laporan & dasbor SLA | Memimpin satu rapat CAB simulasi |
| Manajemen | 2 jam | Dasbor, laporan, membaca kepatuhan SLA | Menyepakati KPI di bagian berikut |

Gunakan tombol **Bantuan** (pojok kanan atas, atau tekan `?`) di setiap layar sebagai bahan ajar; isinya menjelaskan konsep dan cara input di layar itu. Untuk latihan, pakai **Masuk sebagai Tamu** dengan data demo, bukan server produksi.

### Uji penerimaan pengguna (UAT)

Jalankan di server uji yang sudah berisi data pelanggan (bukan data demo).

| No | Skenario | Hasil yang diharapkan |
| --- | --- | --- |
| 1 | Insiden end-to-end: catat, tugaskan, tunda, selesaikan, tutup | Prioritas sesuai matriks, tenggat TTO/TTR terisi, riwayat transisi tercatat |
| 2 | Insiden dibuka kembali setelah Selesai | Status kembali Ditugaskan, komentar alasan tersimpan |
| 3 | Permintaan layanan dengan persetujuan: ajukan, setujui, kerjakan, tutup | Tenggat dua kali lipat dari insiden setara; jalur Ditolak juga diuji |
| 4 | Problem dari insiden berulang ditandai Known Error | Known Error muncul di Basis Pengetahuan dengan workaround |
| 5 | Perubahan melalui CAB: validasi, ajukan, setujui, jadwalkan, implementasi, PIR, tutup | Tampil di kalender perubahan; jalur Tidak Disetujui juga diuji |
| 6 | Topologi: buka CI kritis, lihat dampak hulu-hilir | Relasi CI sesuai data CMDB pelanggan |
| 7 | Laporan & dasbor bulan berjalan | Angka cocok dengan hitungan manual dari daftar tiket |
| 8 | Hak akses: agen tidak bisa membuka menu admin | Ditolak dengan pesan yang jelas |

Kriteria lulus: semua skenario lulus, tidak ada cacat kategori tinggi yang terbuka, dan pemilik proses menandatangani berita acara UAT.

### Daftar periksa cut-over (H-1 sampai H)

- [ ] Cadangan penuh database server uji diambil dan diuji pulih
- [ ] Data master final dimuat ke produksi sesuai urutan di bagian Penyiapan data
- [ ] Akun admin pelanggan dibuat dengan `simpul:create-admin`; akun demo dan tamu dinonaktifkan atau kata sandinya diganti
- [ ] `SIMPUL_DEMO_RESET_DAILY=false` di `.env` produksi, lalu `php artisan optimize`
- [ ] Kanal lama (email, WhatsApp, spreadsheet) diumumkan berpindah ke Simpul pada jam yang disepakati
- [ ] Tiket terbuka di sistem lama diselesaikan di sana sampai habis, atau dipindahkan manual
- [ ] Tim hypercare dan jadwal piket diumumkan

### Hypercare (2–4 minggu setelah go-live)

- Rapat harian 15 menit di minggu pertama: tiket tersangkut, salah kategori, salah prioritas.
- Tinjauan SLA mingguan dari layar Laporan; catat penyebab setiap pelanggaran.
- Perbaikan konfigurasi dan data dikerjakan dalam siklus mingguan.
- Hypercare selesai bila dua minggu berturut-turut kepatuhan SLA mencapai target dan tidak ada tiket yang lebih dari 2 hari tanpa pembaruan.

## Operasional & perbaikan berkelanjutan

### KPI yang dipantau

| KPI | Sumber di Simpul | Target awal | Frekuensi |
| --- | --- | --- | --- |
| Kepatuhan SLA (TTO & TTR) | Laporan, Dasbor | ≥ 90% | Mingguan |
| Rata-rata waktu penyelesaian (MTTR) per prioritas | Laporan | P1 ≤ 4 jam, P2 ≤ 8 jam | Mingguan |
| Backlog tiket terbuka & umur tiket | Daftar tiket, Dasbor | Tidak ada tiket > 30 hari tanpa pembaruan | Mingguan |
| Insiden berulang yang belum punya Problem | Daftar tiket (Problem) | Turun tiap bulan | Bulanan |
| Tingkat keberhasilan perubahan | Kalender perubahan, tiket Change | ≥ 95% tanpa insiden ikutan | Bulanan |
| Kepuasan pengguna (CSAT) | Survei di luar aplikasi | ≥ 4 dari 5 | Bulanan |

Target di atas adalah titik awal; tetapkan ulang setelah tiga bulan data nyata.

### Rutinitas operasional

- **Harian:** cadangan database otomatis (fitur Backup cPanel atau `mysqldump` lewat cron), simpan minimal 14 hari di luar server.
- **Mingguan:** tinjauan SLA dan backlog bersama koordinator service desk.
- **Bulanan:** rapat perbaikan layanan, tinjau KPI, pilih 1–3 inisiatif perbaikan.
- **Saat ada versi baru:** ikuti bagian pembaruan di PANDUAN-CPANEL (cadangan dulu, unggah berkas, `php artisan migrate --force`, `php artisan optimize`). Jangan pernah menjalankan `migrate:fresh` atau `demo:reset` di server pelanggan karena seluruh data akan terhapus.

### Register perbaikan berkelanjutan

Catat setiap inisiatif di layar **Perbaikan Berkelanjutan** (Continual Improvement) dengan pola ITIL: visi, kondisi sekarang (angka KPI), target, langkah, lalu ukur lagi. Contoh: insiden printer berulang di cabang → buat Problem, tetapkan Known Error dengan workaround, tambahkan FAQ, ukur penurunan insiden bulan berikutnya.

## Batasan versi saat ini & daftar periksa

### Batasan yang perlu disampaikan ke pelanggan

| Batasan | Dampak | Cara menangani |
| --- | --- | --- |
| Data master (organisasi, lokasi, kontak, CI, layanan, kontrak) belum punya layar tambah/ubah atau fitur impor | Pemuatan data dilakukan tim implementasi lewat seeder atau skrip | Siapkan data di templat spreadsheet, tim implementasi memuatnya; perubahan rutin diajukan sebagai permintaan layanan ke admin |
| Layar SLA/SLT hanya informasi; tenggat tiket memakai target default per prioritas (P1 0,25/4 jam, P2 0,5/8, P3 2/24, P4 4/72; permintaan ×2) | Kontrak dengan target berbeda belum memengaruhi tenggat | Sepakati target default sebagai SLA internal, atau minta penyesuaian kode sebelum go-live |
| Peran hanya Administrator, Agen, dan Tamu | Belum ada peran pemohon (end user) atau approver terpisah | Pengguna akhir melapor lewat service desk; persetujuan dicatat oleh agen yang ditunjuk |
| Belum ada notifikasi email atau WhatsApp otomatis | Agen harus memantau daftar tiket | Tetapkan kebiasaan memantau Dasbor dan daftar tiket per shift |
| Mode demo (tamu, reset harian) aktif di paket instalasi | Bisa membingungkan atau menghapus data bila salah konfigurasi | Matikan reset harian dan nonaktifkan akun tamu di produksi |

### Daftar periksa implementasi

- [ ] Sponsor, manajer proyek, dan pemilik proses ditetapkan
- [ ] Server memenuhi syarat (PHP 8.3, MySQL/MariaDB, HTTPS) dan Simpul terpasang dari paket cPanel
- [ ] Akun admin pelanggan dibuat; kata sandi bawaan diganti
- [ ] Data master terkumpul dan lolos validasi (kode unik, relasi lengkap)
- [ ] Data dimuat sesuai urutan; jumlah rekaman dicocokkan dengan sumber
- [ ] Aturan proses (kanal, prioritas, eskalasi, CAB) disepakati dan ditandatangani
- [ ] Pelatihan semua peran selesai
- [ ] UAT lulus dan berita acara ditandatangani
- [ ] Reset harian dimatikan, akun tamu dinonaktifkan, cadangan harian aktif
- [ ] Cut-over dijalankan dan kanal lama ditutup
- [ ] Hypercare selesai dan KPI bulan pertama dilaporkan
