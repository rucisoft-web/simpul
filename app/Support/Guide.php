<?php

namespace App\Support;

/**
 * Isi Panduan Penggunaan: konsep + cara input untuk setiap layar.
 * `image` merujuk ke public/img/panduan/{image}.png (dibuat oleh `php artisan panduan:screenshot`).
 * `diagram` merender ilustrasi konsep dari resources/views/guide/diagrams.
 */
class Guide
{
    /** Pemetaan nama route ke kunci panduan (untuk tombol Panduan kontekstual). */
    public const ROUTES = [
        'dashboard' => 'dashboard', 'tickets.index' => 'tickets', 'tickets.create' => 'ticket-create', 'tickets.show' => 'ticket-show',
        'changes.calendar' => 'calendar', 'cis.index' => 'cmdb', 'cis.show' => 'ci-show', 'topology.index' => 'topology',
        'locations.index' => 'locations', 'brands.index' => 'brands', 'services.index' => 'services', 'services.show' => 'service-show',
        'slas.index' => 'slas', 'contracts.index' => 'contracts', 'knowledge.index' => 'knowledge', 'knowledge.faq' => 'knowledge-faq',
        'knowledge.ke' => 'knowledge-ke', 'contacts.index' => 'contacts', 'organizations.index' => 'organizations', 'events.index' => 'events',
        'improvements.index' => 'improvements', 'itil.index' => 'itil', 'reports.index' => 'reports', 'login' => 'mulai', 'landing' => 'mulai',
    ];

    public static function keyForRoute(?string $route): string
    {
        return self::ROUTES[$route] ?? 'mulai';
    }

    public static function groups(): array
    {
        return [
            'Memulai' => ['mulai', 'navigasi', 'data'],
            'Operasional' => ['dashboard', 'tickets', 'ticket-create', 'ticket-show', 'events', 'calendar'],
            'Konfigurasi (CMDB)' => ['cmdb', 'ci-show', 'topology', 'locations', 'brands'],
            'Layanan' => ['services', 'service-show', 'slas', 'contracts'],
            'Pengetahuan & Orang' => ['knowledge', 'knowledge-faq', 'knowledge-ke', 'contacts', 'organizations'],
            'ITIL 4' => ['itil', 'improvements', 'reports'],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function all(): array
    {
        return [
            'mulai' => [
                'title' => 'Memulai: Masuk & Coba sebagai Tamu',
                'path' => '/login', 'image' => 'login', 'icon' => 'sparkles',
                'concept' => [
                    'Simpul adalah aplikasi IT Service Management (ITSM) dan Configuration Management Database (CMDB). Data modelnya mengikuti iTop (Organization, Location, Contact, FunctionalCI, Service, SLA, Ticket) dan alur kerjanya mengikuti praktik ITIL 4.',
                    'Aplikasi berisi data demo tiga organisasi: PT Blue Bird Tbk (transportasi), PT Medco Energi Internasional Tbk (energi), dan UIN Sunan Kalijaga Yogyakarta (pendidikan tinggi). Profil organisasi diambil dari sumber publik; perangkat, IP, orang, dan tiket adalah simulasi.',
                ],
                'steps' => [
                    'Di halaman utama, bagian <b>Demo Perusahaan</b>, klik kartu salah satu perusahaan: PT Blue Bird Tbk, PT Medco Energi, atau UIN Sunan Kalijaga.',
                    'Klik <b>Masuk sebagai Tamu</b> (tombol baru aktif setelah perusahaan dipilih). Anda langsung masuk tanpa registrasi, dan seluruh layar hanya menampilkan data perusahaan itu.',
                    'Untuk pindah perusahaan, klik nama perusahaan di kanan atas → <b>Ganti ke</b>. Data perusahaan lain tidak pernah tercampur.',
                    'Atau klik <b>Masuk</b>, isi email & password akun (admin/agen), lalu klik <b>Masuk</b>.',
                    'Setelah masuk Anda diarahkan ke Dashboard. Pita kuning di atas menandakan mode tamu dan menyebut perusahaan demo yang aktif.',
                    'Untuk keluar, klik avatar di kanan atas → <b>Keluar</b>.',
                ],
                'fields' => ['Perusahaan demo' => 'Pilihan wajib untuk akun Tamu; menentukan data yang tampil.', 'Email' => 'Alamat email akun terdaftar.', 'Password' => 'Kata sandi akun.', 'Ingat saya' => 'Sesi tetap aktif setelah browser ditutup.'],
                'tips' => ['Mode tamu tetap bisa membuat tiket dan menjalankan alur kerja, jadi seluruh fitur dapat dicoba pada perusahaan yang dipilih.'],
            ],
            'navigasi' => [
                'title' => 'Navigasi, Pemilih Organisasi & Tombol Panduan',
                'path' => '/dashboard', 'image' => 'dashboard', 'icon' => 'menu', 'diagram' => 'svs',
                'concept' => [
                    'Menu kiri dikelompokkan mengikuti alur nilai ITIL 4: Operasional (Deliver & Support), Konfigurasi (Obtain/Build), Layanan (Design & Transition), Pengetahuan & Orang (Engage), dan ITIL 4 (Plan & Improve).',
                    'Pemilih organisasi di kanan atas berfungsi sebagai filter global (multi-tenant). Memilih satu organisasi akan menyaring seluruh layar ke organisasi tersebut beserta entitas anaknya.',
                ],
                'steps' => [
                    'Klik item menu di sidebar untuk berpindah layar. Angka merah di Service Desk = jumlah tiket insiden & permintaan yang masih terbuka.',
                    'Klik pemilih organisasi (kanan atas). Admin/agen dapat memilih satu organisasi atau <b>Semua Organisasi</b>; akun Tamu terkunci pada perusahaan demo pilihannya dan berpindah lewat <b>Ganti ke</b>.',
                    'Gunakan kotak pencarian di header untuk mencari tiket berdasarkan nomor referensi atau judul.',
                    'Klik tombol <b>Panduan</b> di kanan atas kapan saja: panel panduan layar yang sedang dibuka akan muncul di sisi kanan.',
                    'Klik <b>+ Tiket Baru</b> untuk langsung membuat tiket dari layar mana pun.',
                ],
                'tips' => ['Di layar ponsel, buka sidebar dengan ikon ☰ di kiri atas.'],
                'fields' => ['Pemilih organisasi' => 'Filter global; tersimpan selama sesi sampai diganti.', 'Pencarian header' => 'Nomor referensi (mis. I-000123) atau sebagian judul tiket, lalu Enter.', 'Tombol Bantuan' => 'Membuka penjelasan konsep & cara input untuk layar yang sedang aktif.'],
            ],
            'data' => [
                'title' => 'Asal Data & Batasan Input',
                'path' => '/dashboard', 'image' => 'dashboard', 'icon' => 'database',
                'concept' => [
                    'Data induk (organisasi, lokasi, orang, tim, CI, layanan, SLA, kontrak, FAQ, Known Error) dimuat oleh <b>seeder</b> mengikuti data model iTop. Di aplikasi, layar-layar tersebut bersifat <b>telusur & analisis</b> (lihat, saring, dan klik ke detail).',
                    'Data yang diinput langsung oleh pengguna adalah data operasional: <b>tiket</b> (Insiden, Permintaan, Problem, Perubahan), <b>transisi status</b> tiket, dan <b>catatan aktivitas</b> (log publik/privat).',
                ],
                'steps' => [
                    'Untuk mencatat gangguan, permintaan, problem, atau perubahan: klik <b>+ Tiket Baru</b> di kanan atas.',
                    'Untuk memproses tiket: buka detail tiket → panel <b>Langkah berikutnya</b>.',
                    'Untuk menambah percakapan: kotak <b>Aktivitas & Komunikasi</b> di detail tiket.',
                    'Untuk mencari data induk: gunakan filter & kotak cari di tiap layar (CMDB, Kontak, Basis Pengetahuan).',
                    'Administrator dapat memuat ulang seluruh data demo dengan perintah <code>php artisan demo:reset</code>.',
                ],
                'fields' => ['Diinput pengguna' => 'Tiket, transisi status, catatan aktivitas.', 'Dimuat seeder' => 'Organisasi, lokasi, orang, tim, CI, layanan, SLA, kontrak, pengetahuan, event.'],
                'tips' => ['Tombol Bantuan di kanan atas selalu membuka panduan layar yang sedang Anda buka.'],
            ],
            'dashboard' => [
                'title' => 'Dashboard Layanan TI',
                'path' => '/dashboard', 'image' => 'dashboard', 'icon' => 'home',
                'concept' => [
                    'Dashboard adalah ringkasan kesehatan layanan: insiden & permintaan terbuka, kepatuhan SLA 30 hari, MTTR (Mean Time To Resolve), CSAT, jumlah CI, event monitoring aktif, perubahan terjadwal, dan tiket yang sudah melewati SLA.',
                    'Grafik menampilkan tren 12 bulan, distribusi prioritas, layanan dengan tiket terbanyak, komposisi CMDB per kelas CI, dan sebaran merek perangkat (multi-vendor).',
                ],
                'steps' => [
                    'Klik kartu KPI mana pun untuk membuka daftar detailnya (mis. <b>Insiden Terbuka</b> membuka Service Desk dengan filter insiden terbuka).',
                    'Lihat tabel <b>Antrean Prioritas</b>: diurutkan dari prioritas tertinggi dan tenggat SLA terdekat. Bilah warna menunjukkan sisa waktu SLA (hijau aman, kuning >70%, merah terlewati).',
                    'Klik nama organisasi pada <b>Volume per Organisasi</b> untuk memfokuskan dashboard ke organisasi tersebut.',
                    'Arahkan kursor ke grafik untuk melihat nilai detail.',
                ],
                'fields' => ['Kepatuhan SLA' => 'Persentase tiket selesai sebelum batas TTR.', 'MTTR' => 'Rata-rata waktu dari tiket dibuat hingga diselesaikan.', 'CSAT' => 'Rata-rata kepuasan pelapor (1–5) saat tiket ditutup.'],
            ],
            'tickets' => [
                'title' => 'Service Desk (Daftar Tiket)',
                'path' => '/tickets', 'image' => 'tickets', 'icon' => 'inbox', 'diagram' => 'lifecycle',
                'concept' => [
                    'Service Desk adalah titik kontak tunggal (Single Point of Contact). Satu antrean memuat empat tipe tiket ITIL: <b>Insiden</b> (gangguan layanan), <b>Permintaan Layanan</b> (hal standar yang diminta pengguna), <b>Problem</b> (akar penyebab insiden berulang), dan <b>Perubahan</b> (penambahan/modifikasi/penghapusan yang berdampak pada layanan).',
                    'Setiap tipe memiliki siklus status sendiri (lihat diagram). Tiket terbuka diurutkan di atas tiket yang sudah ditutup.',
                ],
                'steps' => [
                    'Pilih tab tipe tiket di atas (Semua / Insiden / Permintaan Layanan / Problem / Perubahan).',
                    'Saring dengan kotak <b>Cari</b>, status (Terbuka / Selesai / Lewat SLA), <b>Prioritas</b>, dan <b>Tim</b>, lalu klik <b>Filter</b>.',
                    'Klik nomor referensi atau judul untuk membuka detail tiket.',
                    'Klik <b>Buat Tiket</b> di kanan atas untuk membuat tiket baru dengan tipe yang sedang aktif.',
                ],
                'fields' => ['Ref' => 'Nomor unik: I- (insiden), R- (permintaan), P- (problem), C- (perubahan).', 'SLA' => '"Lewat" bila batas TTR terlampaui; "Tercapai" bila selesai tepat waktu; durasi = sisa waktu.', 'Tim / Agen' => 'Grup penugasan dan orang yang menangani.'],
            ],
            'ticket-create' => [
                'title' => 'Membuat Tiket Baru',
                'path' => '/tickets/create', 'image' => 'ticket-create', 'icon' => 'plus', 'diagram' => 'priority',
                'concept' => [
                    'Prioritas tidak dipilih manual, tetapi dihitung otomatis dari matriks <b>Dampak × Urgensi</b> ITIL. Dampak = seberapa luas pengaruhnya (departemen, satu layanan, individu); Urgensi = seberapa cepat harus ditangani.',
                    'Dari prioritas, sistem menetapkan batas SLA: <b>TTO</b> (Time To Own — tiket harus diambil agen) dan <b>TTR</b> (Time To Resolve — tiket harus selesai). Permintaan layanan mendapat batas 2× lebih panjang dari insiden.',
                ],
                'steps' => [
                    'Pilih <b>Tipe tiket</b>: Insiden, Permintaan Layanan, Problem, atau Perubahan.',
                    'Pilih <b>Organisasi</b> (atau entitas anak) dan <b>Pelapor / Pemohon</b>. Daftar layanan & CI mengikuti organisasi aktif di pemilih kanan atas.',
                    'Pilih <b>Layanan & subkategori</b> — ini menentukan tim yang otomatis ditugaskan.',
                    'Isi <b>Judul</b> yang singkat dan spesifik, lalu <b>Deskripsi</b> (gejala, sejak kapan, dampak, langkah yang sudah dicoba).',
                    'Pilih <b>CI terdampak</b> bila diketahui (aplikasi, perangkat jaringan, server, PC).',
                    'Atur <b>Dampak</b> dan <b>Urgensi</b> di panel kanan; kotak gelap menampilkan prioritas hasil hitung dan sel matriks ikut tersorot.',
                    'Untuk <b>Perubahan</b>: pilih jenis (Standard/Normal/Emergency) dan jadwal implementasi.',
                    'Klik <b>Simpan Tiket</b>. Anda diarahkan ke halaman detail tiket baru.',
                ],
                'fields' => [
                    'Tipe tiket' => 'Insiden / Permintaan / Problem / Perubahan.',
                    'Organisasi' => 'Pemilik tiket; entitas anak ditandai "—".',
                    'Pelapor' => 'Orang yang melapor atau meminta.',
                    'Layanan & subkategori' => 'Dari katalog layanan; menentukan tim & SLA.',
                    'Judul / Deskripsi' => 'Wajib / opsional (maks. 5.000 karakter).',
                    'CI terdampak' => 'Opsional, menghubungkan tiket ke CMDB.',
                    'Dampak (1–3)' => '1 Departemen/banyak layanan, 2 Satu layanan, 3 Individu.',
                    'Urgensi (1–4)' => '1 Kritis, 2 Tinggi, 3 Sedang, 4 Rendah.',
                    'Jenis perubahan' => 'Standard = pra-disetujui; Normal = lewat CAB; Emergency = lewat ECAB.',
                ],
                'tips' => ['Insiden P1 memiliki TTO 15 menit dan TTR 4 jam (SLA default).'],
            ],
            'ticket-show' => [
                'title' => 'Detail Tiket & Alur Kerja',
                'path' => '/tickets/{open}', 'image' => 'ticket-show', 'icon' => 'file', 'diagram' => 'lifecycle',
                'concept' => [
                    'Halaman detail menampilkan semua informasi tiket: deskripsi, CI terdampak, SLA, problem induk/insiden terkait, event monitoring, dan riwayat aktivitas.',
                    'Panel <b>Langkah berikutnya</b> hanya menampilkan transisi yang sah untuk status saat ini, sesuai siklus ITIL (mis. insiden: Baru → Ditugaskan → Selesai → Ditutup; perubahan: Baru → Tervalidasi → Menunggu Persetujuan → Disetujui → Terjadwal → Diimplementasi → Dipantau → Ditutup).',
                ],
                'steps' => [
                    'Klik tombol aksi di panel <b>Langkah berikutnya</b> (mis. <b>Tugaskan</b>). Form konfirmasi muncul.',
                    'Untuk <b>Tugaskan</b>: pilih agen dari anggota tim. Untuk <b>Selesaikan</b>: tulis solusi pada kolom catatan.',
                    'Klik <b>Konfirmasi</b>. Status berubah, waktu & pelaku tercatat di log, dan status SLA dihitung ulang.',
                    'Tulis balasan di kotak <b>Aktivitas & Komunikasi</b>: pilih <b>Log publik</b> (terlihat pelapor) atau <b>Log privat</b> (catatan internal tim), lalu klik <b>Kirim</b>.',
                    'Klik CI pada panel <b>CI Terdampak</b> untuk melihat analisis dampaknya, atau klik ref problem induk untuk melihat akar masalah.',
                ],
                'fields' => ['TTO' => 'Batas waktu tiket diambil/ditugaskan.', 'TTR' => 'Batas waktu tiket diselesaikan.', 'Log sistem' => 'Dicatat otomatis setiap perubahan status (abu-abu, miring).'],
                'tips' => ['Status "Selesai" belum berarti ditutup — pelapor/agen menutup setelah solusi dikonfirmasi; tiket bisa dibuka kembali.'],
            ],
            'events' => [
                'title' => 'Monitoring & Event Management',
                'path' => '/events', 'image' => 'events', 'icon' => 'activity',
                'concept' => [
                    'Event adalah perubahan keadaan yang terdeteksi alat monitoring (Zabbix, PRTG, SolarWinds, Wazuh, PI System, LibreNMS). Severity: info, warning, minor, major, critical.',
                    'Event kritis dikorelasikan menjadi insiden (asal tiket "monitoring") sehingga tim dapat bertindak sebelum pengguna melapor.',
                ],
                'steps' => [
                    'Klik kartu severity untuk menyaring event pada tingkat tersebut (angka = 24 jam terakhir).',
                    'Gunakan tab <b>Open / Acknowledged / Closed</b> untuk menyaring status penanganan.',
                    'Klik nama CI untuk membuka detail perangkat, atau ref insiden untuk melihat tiket hasil korelasi.',
                    'Grafik bertumpuk menunjukkan pola event 7 hari per severity.',
                ],
                'fields' => ['Severity' => 'info, warning, minor, major, critical (merah = kritis).', 'Pesan' => 'Teks alarm dari alat monitoring.', 'CI' => 'Perangkat/aplikasi sumber event; klik untuk detail CI.', 'Sumber' => 'Alat monitoring pengirim event (Zabbix, PRTG, Wazuh, dll.).', 'Status' => 'open (belum ditangani), acknowledged (sedang ditangani), closed.', 'Insiden' => 'Tiket insiden hasil korelasi event, bila ada.'],
            ],
            'calendar' => [
                'title' => 'Kalender Perubahan (Change Enablement)',
                'path' => '/changes/calendar', 'image' => 'calendar', 'icon' => 'calendar',
                'concept' => [
                    'ITIL 4 menyebut praktik ini <b>Change Enablement</b>: memaksimalkan perubahan yang berhasil dengan menilai risiko dan mengotorisasi perubahan.',
                    'Tiga jenis: <b>Standard</b> (risiko rendah, pra-disetujui — hijau), <b>Normal</b> (dinilai & disetujui CAB — ungu), <b>Emergency</b> (mendesak, disetujui ECAB — merah). Kalender membantu menghindari bentrok jadwal dan periode rawan.',
                ],
                'steps' => [
                    'Gunakan tombol ← / → untuk berpindah bulan. Hari ini diberi bingkai biru.',
                    'Klik entri pada kalender untuk membuka detail perubahan.',
                    'Klik <b>Ajukan Perubahan</b> untuk membuat Request for Change (RFC) baru.',
                    'Gulir ke bawah untuk daftar seluruh perubahan beserta risiko, downtime, dan status.',
                ],
                'fields' => ['Warna entri' => 'Hijau = Standard, ungu = Normal, merah = Emergency.', 'Risiko' => 'Tingkat risiko perubahan hasil penilaian.', 'Downtime' => 'Perkiraan waktu layanan tidak tersedia selama implementasi.', 'Jadwal' => 'Diisi pada kolom "Jadwal implementasi" saat membuat tiket Perubahan.'],
            ],
            'cmdb' => [
                'title' => 'Configuration Items (CMDB)',
                'path' => '/cmdb', 'image' => 'cmdb', 'icon' => 'database', 'diagram' => 'cimodel',
                'concept' => [
                    'Configuration Item (CI) adalah komponen yang perlu dikelola untuk menyediakan layanan. Kelas CI mengikuti hierarki iTop: Physical Device (perangkat jaringan, server, storage, rack, UPS, PC, printer, IP phone, CCTV), Virtualization (hypervisor, VM), Software Instance (DB server, web server, middleware), dan Application (web app, application solution, business process).',
                    'Setiap CI memiliki organisasi, lokasi, merek/model, status siklus hidup (implementation, production, stock, obsolete), kritikalitas bisnis, dan tim pendukung.',
                ],
                'steps' => [
                    'Pilih kelas CI pada panel kiri untuk menyaring daftar (angka = jumlah CI per kelas).',
                    'Cari berdasarkan <b>nama, serial number, atau IP</b>; saring <b>merek</b>, <b>status</b>, dan <b>lokasi</b>, lalu klik <b>Filter</b>.',
                    'Klik nama CI untuk membuka detail & analisis dampak.',
                    'Klik <b>Lihat Topologi</b> untuk melihat CI jaringan dalam bentuk pohon.',
                ],
                'fields' => ['Kritikalitas' => 'high / medium / low — dampak ke bisnis bila CI gagal.', 'Status' => 'production (aktif), implementation (dipasang), stock (cadangan), obsolete (pensiun).'],
            ],
            'ci-show' => [
                'title' => 'Detail CI & Analisis Dampak',
                'path' => '/cmdb/{core}', 'image' => 'ci-show', 'icon' => 'layers',
                'concept' => [
                    'Detail CI memuat atribut teknis (IP, serial, OS/firmware, CPU/RAM, rack, garansi), layanan & kontrak terkait, serta kontak.',
                    '<b>Analisis Dampak</b> menelusuri secara rekursif CI apa saja yang ikut terdampak bila CI ini gagal — melalui relasi dependensi (aplikasi → software → VM → host) dan topologi uplink (core → distribusi → akses → endpoint).',
                ],
                'steps' => [
                    'Baca breadcrumb di atas judul: jalur uplink dari WAN hingga perangkat ini.',
                    'Buka tab <b>Analisis Dampak</b> untuk melihat pohon CI downstream; klik node untuk menelusuri.',
                    'Tab <b>Relasi</b> memisahkan upstream (bergantung pada) dan downstream (digunakan oleh).',
                    'Tab <b>Interface</b> menampilkan port, IP, MAC, kecepatan, VLAN; tab <b>Tiket</b> & <b>Event</b> menampilkan riwayat operasional.',
                    'Klik <b>Laporkan Insiden</b> untuk membuat tiket yang berkaitan dengan CI ini.',
                ],
                'tips' => ['Label "Garansi habis" muncul otomatis bila tanggal garansi telah lewat — berguna untuk perencanaan peremajaan.'],
            ],
            'topology' => [
                'title' => 'Topologi Jaringan Bertingkat',
                'path' => '/topology', 'image' => 'topology', 'icon' => 'network', 'diagram' => 'topology',
                'concept' => [
                    'Topologi dibangun dari relasi <b>uplink</b> setiap perangkat (kolom parent pada CI). Hasilnya pohon bertingkat: Internet/ISP → WAN Edge Router → Firewall → Core Switch → Distribusi/ToR → Router site (pool, site lapangan, gedung) → Distribusi → Access Switch → Access Point & Endpoint.',
                    'Warna isi node = peran perangkat; warna garis tepi = merek (Cisco, Huawei, MikroTik, Fortinet, Juniper, Ruijie, dll.).',
                    'Mode <b>Lokasi</b> menampilkan pohon kantor pusat/kampus → data center, wilayah, gedung → pool/site beserta jumlah CI.',
                ],
                'steps' => [
                    'Pilih organisasi pada tombol di kiri atas (Blue Bird, Medco, UIN).',
                    'Klik satu kali pada node untuk membuka/menutup cabangnya dan menampilkan kartu ringkasan (peran, IP, lokasi, jumlah turunan).',
                    'Klik dua kali pada node untuk membuka halaman detail CI.',
                    'Scroll untuk zoom, drag untuk menggeser. Gunakan <b>Buka semua</b>, <b>Ringkas</b>, dan <b>Pas layar</b>.',
                    'Centang <b>Tampilkan endpoint</b> untuk menyertakan PC, printer, CCTV, IP phone, dan server.',
                    'Ganti ke mode <b>Lokasi</b> untuk melihat hierarki lokasi.',
                ],
                'fields' => ['Organisasi' => 'Tombol di kiri atas; topologi selalu per satu organisasi induk.', 'Mode Jaringan / Lokasi' => 'Pohon uplink perangkat atau pohon hierarki lokasi.', 'Tampilkan endpoint' => 'Centang untuk menambah PC, printer, CCTV, IP phone, dan server.', 'Warna isi / tepi node' => 'Isi = peran perangkat; tepi = merek.'],
            ],
            'locations' => [
                'title' => 'Lokasi',
                'path' => '/locations', 'image' => 'locations', 'icon' => 'map',
                'concept' => ['Lokasi bersifat hierarkis: kantor pusat/kampus memiliki data center, wilayah, dan gedung; wilayah memiliki pool atau site lapangan. Setiap CI dan orang ditempatkan pada satu lokasi.'],
                'steps' => ['Telusuri pohon lokasi per organisasi.', 'Klik angka <b>CI</b> untuk membuka CMDB yang tersaring ke lokasi tersebut.', 'Gunakan pemilih organisasi kanan atas untuk menampilkan satu organisasi saja.'],
                'fields' => ['Tipe' => 'hq, campus, datacenter, region, office, building, pool, site.'],
            ],
            'brands' => [
                'title' => 'Merek & Model',
                'path' => '/brands', 'image' => 'brands', 'icon' => 'tag',
                'concept' => ['Tipologi Brand → Model (iTop) memudahkan standarisasi perangkat, perencanaan suku cadang, dan negosiasi kontrak vendor di lingkungan multi-vendor.'],
                'steps' => ['Baca matriks merek × organisasi: angka = jumlah perangkat; warna makin pekat = makin banyak.', 'Klik nama merek untuk membuka CMDB tersaring ke merek tersebut.', 'Kartu di bawah merinci model per merek beserta kelas CI-nya.'],
                'fields' => ['Angka matriks' => 'Jumlah perangkat merek tersebut di organisasi itu.', 'Model' => 'Tipe perangkat per merek beserta kelas CI-nya.'],
            ],
            'services' => [
                'title' => 'Katalog Layanan',
                'path' => '/services', 'image' => 'services', 'icon' => 'grid',
                'concept' => [
                    'Struktur katalog iTop: <b>Service Family → Service → Service Subcategory</b>. Subkategori bertipe insiden (merah) atau permintaan layanan (biru) dan dipakai saat membuat tiket.',
                    'ITIL 4 menambahkan sudut pandang <b>value stream</b> — rangkaian langkah penciptaan nilai, mis. "Pemesanan Perjalanan", "Produksi Migas", "Siklus Akademik".',
                ],
                'steps' => ['Pilih tampilan <b>Per Service Family</b> atau <b>Per Value Stream</b>.', 'Klik kartu layanan untuk melihat subkategori, CI pendukung, kontrak & SLA, statistik 90 hari, dan tiket terbaru.', 'Klik <b>Ajukan Tiket</b> dari halaman layanan.'],
                'fields' => ['Service Family' => 'Kelompok layanan (Konektivitas, Aplikasi Bisnis, Keamanan, dst.).', 'Value stream' => 'Rangkaian penciptaan nilai yang didukung layanan.', 'Subkategori merah / biru' => 'Merah = untuk insiden, biru = untuk permintaan layanan.'],
            ],
            'service-show' => [
                'title' => 'Detail Layanan',
                'path' => '/services/{svc}', 'image' => 'service-show', 'icon' => 'grid',
                'concept' => [
                    'Halaman ini menyatukan semua yang menopang satu layanan: <b>subkategori</b> (jenis insiden/permintaan yang dapat diajukan), <b>CI pendukung</b> (aplikasi, server, perangkat jaringan), <b>kontrak & SLA</b> yang mengikat, statistik 90 hari, dan tiket terbaru.',
                    'Dengan melihat CI pendukung dan tiket sekaligus, pemilik layanan dapat menilai apakah gangguan berasal dari komponen tertentu.',
                ],
                'steps' => [
                    'Baca panel <b>Subkategori</b>: label merah untuk insiden, biru untuk permintaan layanan.',
                    'Klik CI pada panel <b>CI pendukung</b> untuk membuka detail & analisis dampaknya.',
                    'Periksa panel <b>Kontrak & SLA</b> untuk mengetahui target TTO/TTR yang berlaku.',
                    'Klik <b>Ajukan Tiket</b> untuk membuat tiket; di form, pilih layanan ini pada isian <b>Layanan & subkategori</b>.',
                    'Klik ref pada <b>Tiket terbaru</b> untuk membuka detail tiket.',
                ],
                'fields' => ['Subkategori' => 'Pilihan \"Layanan & subkategori\" saat membuat tiket; menentukan tim penanganan.', 'CI pendukung' => 'Komponen CMDB yang menjalankan layanan.', 'Kartu statistik' => 'Jumlah insiden & permintaan 90 hari terakhir, tiket lewat SLA, dan jumlah CI pendukung.'],
            ],
            'slas' => [
                'title' => 'SLA & SLT',
                'path' => '/slas', 'image' => 'slas', 'icon' => 'clock',
                'concept' => ['SLA (Service Level Agreement) berisi sekumpulan SLT (Service Level Target). Setiap SLT = prioritas × tipe permintaan × metrik (TTO/TTR) × nilai. Demo menyediakan SLA Gold (24×7 untuk layanan kritis) dan SLA Standar (jam kerja, 1,5× lebih longgar).'],
                'steps' => ['Bandingkan target TTO/TTR per prioritas untuk insiden dan permintaan.', 'SLA dihubungkan ke layanan melalui kontrak pelanggan/OLA (layar Kontrak).'],
                'fields' => ['Prioritas' => 'P1 Kritis s.d. P4 Rendah, hasil matriks Dampak × Urgensi.', 'TTO' => 'Time To Own: batas tiket diambil/ditugaskan.', 'TTR' => 'Time To Resolve: batas tiket diselesaikan.', 'Insiden / Permintaan' => 'Target dibedakan per tipe tiket; permintaan lebih longgar.'],
            ],
            'contracts' => [
                'title' => 'Kontrak (Pelanggan & Vendor)',
                'path' => '/contracts', 'image' => 'contracts', 'icon' => 'file',
                'concept' => ['<b>Customer contract / OLA</b> mengikat layanan + SLA kepada pelanggan internal (induk & entitas anak). <b>Provider contract</b> adalah kontrak vendor: konektivitas (Telkom, Telkomsat, Biznet), dukungan perangkat (Cisco, Fortinet, Juniper, Huawei), dan lisensi (Microsoft, SAP) — praktik Supplier Management.'],
                'steps' => ['Pantau kolom status: <b>Berakhir N hari</b> (kuning) muncul bila kontrak habis < 90 hari.', 'Lihat CI yang tercakup oleh setiap kontrak vendor.', 'Gulir ke bawah untuk daftar OLA per entitas.'],
                'fields' => ['Tipe' => 'Konektivitas, dukungan perangkat, atau lisensi (kontrak vendor).', 'Periode' => 'Tanggal mulai s.d. berakhir kontrak.', 'Nilai' => 'Nilai kontrak dalam Rupiah.', 'CI tercakup' => 'Perangkat/aplikasi yang didukung kontrak vendor.', 'Status' => 'Aktif, atau "Berakhir N hari" (kuning) bila kurang dari 90 hari.', 'Pelanggan / Penyedia / SLA' => 'Pada OLA: siapa menerima layanan, siapa menyediakan, dan SLA yang berlaku.'],
            ],
            'knowledge' => [
                'title' => 'Basis Pengetahuan',
                'path' => '/knowledge', 'image' => 'knowledge', 'icon' => 'book',
                'concept' => [
                    '<b>FAQ</b> untuk self-help pengguna (mengurangi tiket level-1). <b>Known Error</b> adalah problem yang akar masalah dan workaround-nya sudah diketahui — hasil Problem Management. <b>Dokumen</b> memuat kebijakan, prosedur, dan runbook.',
                ],
                'steps' => ['Ketik kata kunci di kotak pencarian (mis. "eduroam", "VPN", "SAP") lalu klik <b>Cari</b>.', 'Klik kartu FAQ untuk membaca langkah penyelesaian. Bila tidak membantu, klik <b>Tidak, buat tiket</b>.', 'Klik Known Error untuk melihat gejala, akar masalah, workaround, solusi, dan problem terkait.'],
                'fields' => ['Kotak pencarian' => 'Kata kunci dicocokkan ke judul, ringkasan, dan kata kunci artikel.', 'FAQ' => 'Panduan swalayan untuk pengguna.', 'Known Error' => 'Masalah dengan akar penyebab & workaround yang sudah diketahui.', 'Dokumen' => 'Kebijakan, prosedur, runbook.'],
            ],
            'knowledge-faq' => [
                'title' => 'Artikel FAQ',
                'path' => '/knowledge/faq/{faq}', 'image' => 'knowledge-faq', 'icon' => 'book',
                'concept' => ['FAQ adalah artikel swalayan (self-help) yang memandu pengguna menyelesaikan masalah umum tanpa membuat tiket — praktik <b>Knowledge Management</b> dan <i>shift-left</i> ke level 0.'],
                'steps' => [
                    'Baca <b>Ringkasan</b> untuk memastikan artikel sesuai masalah Anda.',
                    'Ikuti <b>Langkah penyelesaian</b> secara berurutan.',
                    'Bila masalah belum selesai, klik <b>Tidak, buat tiket</b> di bagian bawah artikel untuk membuat Insiden/Permintaan.',
                ],
                'fields' => ['Kategori' => 'Kelompok topik FAQ (Akun & Password, Jaringan & Wi-Fi, Keamanan, dst.).', 'Kata kunci' => 'Istilah yang dipakai pencarian Basis Pengetahuan.', 'Dilihat' => 'Jumlah kali artikel dibuka (indikator topik yang sering ditanyakan).'],
            ],
            'knowledge-ke' => [
                'title' => 'Known Error',
                'path' => '/knowledge/known-error/{ke}', 'image' => 'knowledge-ke', 'icon' => 'alert',
                'concept' => ['Known Error adalah problem yang <b>akar masalah</b> dan <b>workaround</b>-nya sudah diketahui. Agen memakai Known Error untuk memulihkan insiden serupa dengan cepat sambil menunggu solusi permanen.'],
                'steps' => [
                    'Cocokkan <b>Gejala</b> dan <b>Kode error</b> dengan insiden yang sedang ditangani.',
                    'Terapkan <b>Workaround</b> untuk memulihkan layanan, lalu catat di log tiket.',
                    'Klik <b>Buka problem P-…</b> untuk melihat analisa dan progres solusi permanen.',
                ],
                'fields' => ['Gejala' => 'Tanda yang dilihat pengguna/agen.', 'Kode error' => 'Pesan/kode teknis bila ada.', 'Akar masalah' => 'Penyebab hasil Problem Management.', 'Workaround' => 'Cara sementara memulihkan layanan.', 'Solusi permanen' => 'Perbaikan tuntas; \"Belum tersedia\" bila problem masih dianalisa.'],
            ],
            'contacts' => [
                'title' => 'Kontak & Tim',
                'path' => '/contacts', 'image' => 'contacts', 'icon' => 'users',
                'concept' => ['<b>Person</b> mencakup pelapor, agen, dan manajer. <b>Team</b> adalah grup penugasan tiket (Service Desk, Network, Data Center, Security, dst.). Satu orang dapat menjadi anggota beberapa tim dengan peran (Team Lead, Agent L1/L2/L3).'],
                'steps' => ['Lihat kartu tim: anggota (arahkan kursor ke avatar untuk nama & peran) dan jumlah tiket terbuka.', 'Klik <b>N tiket terbuka</b> untuk membuka antrean tim tersebut.', 'Cari orang berdasarkan nama atau jabatan pada tabel di bawah.'],
                'fields' => ['Cari' => 'Ketik nama atau jabatan, lalu Enter.', 'Jabatan' => 'Fungsi orang di organisasinya.', 'Tim' => 'Grup penugasan tempat orang itu menjadi anggota.', 'Kontak' => 'Email/telepon kerja.'],
            ],
            'organizations' => [
                'title' => 'Organisasi',
                'path' => '/organizations', 'image' => 'organizations', 'icon' => 'building',
                'concept' => ['Organisasi adalah pemilik data (multi-tenant). Organisasi induk dapat memiliki entitas anak (mis. PT Big Bird Pusaka di bawah PT Blue Bird Tbk; PT Medco Power Indonesia di bawah Medco).'],
                'steps' => ['Bandingkan jumlah lokasi, orang, tim, CI, layanan, dan tiket per organisasi.', 'Klik <b>Fokus ke organisasi ini</b> untuk mengaktifkan filter organisasi di seluruh aplikasi.'],
                'fields' => ['Entitas anak' => 'Organisasi di bawah induk; ikut tersaring saat induk dipilih.', 'Angka ringkasan' => 'Jumlah lokasi, orang, tim, CI, layanan, dan tiket milik organisasi.'],
            ],
            'itil' => [
                'title' => 'Peta Praktik ITIL 4',
                'path' => '/itil4', 'image' => 'itil', 'icon' => 'layers', 'diagram' => 'svs',
                'concept' => [
                    'ITIL 4 memandang manajemen layanan sebagai <b>Service Value System</b>: peluang/permintaan diubah menjadi nilai melalui <b>Service Value Chain</b> (Plan, Improve, Engage, Design & Transition, Obtain/Build, Deliver & Support), dipandu <b>7 guiding principles</b>, dan dijalankan oleh <b>34 praktik</b>.',
                    'Layar ini memetakan setiap aktivitas value chain dan praktik ITIL 4 ke modul aplikasi, serta modul data model iTop ke praktik ITIL 4.',
                ],
                'steps' => ['Klik kotak aktivitas value chain untuk membuka modul terkait.', 'Praktik bertanda ✓ hijau dapat diklik menuju layar pendukungnya; lingkaran putus-putus = belum didukung modul.', 'Gulir ke tabel pemetaan iTop → ITIL 4 untuk memahami asal data model.'],
                'fields' => ['Value chain' => 'Enam aktivitas: Plan, Improve, Engage, Design & Transition, Obtain/Build, Deliver & Support.', 'Praktik ✓' => 'Didukung modul aplikasi (dapat diklik).', 'Lingkaran putus-putus' => 'Praktik belum didukung modul.'],
            ],
            'improvements' => [
                'title' => 'Continual Improvement Register',
                'path' => '/improvements', 'image' => 'improvements', 'icon' => 'trending',
                'concept' => ['Continual Improvement adalah praktik yang menyelaraskan layanan dengan kebutuhan bisnis yang berubah. Setiap inisiatif dicatat di register (CIR) dengan praktik terkait, guiding principle, manfaat, pemilik, progres, dan target — dijalankan dengan model 7 langkah CI.'],
                'steps' => ['Baca papan kanban: Diusulkan → Disetujui → Berjalan → Selesai.', 'Bilah progres menunjukkan persentase penyelesaian; nama pemilik dan target bulan tertera di bawahnya.', 'Gulir ke bawah untuk model 7 langkah Continual Improvement.'],
                'fields' => ['Kolom kanban' => 'Status inisiatif: Diusulkan, Disetujui, Berjalan, Selesai.', 'Praktik / prinsip' => 'Praktik ITIL 4 dan guiding principle yang terkait.', 'Manfaat' => 'Hasil yang diharapkan dari inisiatif.', 'Progres' => 'Persentase penyelesaian.', 'Pemilik & target' => 'Penanggung jawab dan bulan target selesai.'],
            ],
            'reports' => [
                'title' => 'Laporan & SLA',
                'path' => '/reports', 'image' => 'reports', 'icon' => 'chart',
                'concept' => ['Praktik Measurement & Reporting: kepatuhan SLA (TTO & TTR) bulanan terhadap target 90%, MTTR bulanan, kanal masuk tiket, kode resolusi, heatmap beban (hari × jam), dan kinerja per tim.'],
                'steps' => ['Bandingkan kartu SLA, CSAT, dan volume per organisasi di bagian atas.', 'Gunakan heatmap untuk merencanakan jadwal shift agen (warna pekat = jam sibuk).', 'Tabel kinerja tim menampilkan tiket selesai, SLA TTR, MTTR, dan CSAT 90 hari.'],
                'fields' => ['Kepatuhan SLA' => 'Persentase tiket memenuhi TTO/TTR per bulan, dibanding target 90%.', 'MTTR' => 'Rata-rata jam penyelesaian per bulan.', 'Kanal masuk' => 'Asal tiket: portal, email, telepon, monitoring.', 'Kode resolusi' => 'Cara tiket diselesaikan (fixed, workaround, dll.).', 'Heatmap' => 'Jumlah tiket per hari × jam; makin pekat makin sibuk.'],
            ],
        ];
    }
}
