<?php

namespace Database\Seeders\data;

/**
 * Profil organisasi demo.
 * Nama perusahaan, entitas anak, wilayah operasi, aplikasi & inisiatif TI diambil dari
 * Laporan Tahunan 2025 PT Blue Bird Tbk dan PT Medco Energi Internasional Tbk,
 * serta situs uin-suka.ac.id. Detail teknis (perangkat, IP, tiket) adalah SIMULASI.
 */
class OrgProfiles
{
    public static function all(): array
    {
        return [self::bluebird(), self::medco(), self::uin()];
    }

    public static function bluebird(): array
    {
        return [
            'org' => ['code' => 'BBRD', 'name' => 'PT Blue Bird Tbk', 'industry' => 'Transportasi & Mobilitas', 'color' => '#1d4ed8',
                'website' => 'https://www.bluebirdgroup.com',
                'description' => 'Perusahaan mobilitas darat (taksi, sewa mobil, shuttle, bus) yang beroperasi di 18 wilayah Indonesia. Kanal digital utama: aplikasi MyBluebird dan Driver App.'],
            'children' => [
                ['code' => 'BBP', 'name' => 'PT Big Bird Pusaka', 'industry' => 'Sewa Mobil & Bus'],
                ['code' => 'PPT', 'name' => 'PT Pusaka Prima Transport', 'industry' => 'Penyewaan Bus'],
                ['code' => 'TAN', 'name' => 'PT Trans Antar Nusabird', 'industry' => 'Layanan Shuttle'],
                ['code' => 'PBT', 'name' => 'PT Praja Bali Transportasi', 'industry' => 'Taksi (Bali)'],
            ],
            'domain' => 'bluebird.demo',
            'ip' => 10,
            'monitor' => ['Zabbix', 'PRTG', 'Wazuh'],
            'hq' => ['name' => 'Kantor Pusat Cengkareng', 'address' => 'Jl. Bojong Indah Raya No. 6A, Rawa Buaya, Cengkareng', 'city' => 'Jakarta Barat', 'lat' => -6.1630, 'lng' => 106.7330],
            'dc' => ['name' => 'Data Center Utama Cengkareng', 'city' => 'Jakarta Barat'],
            'drc' => ['name' => 'Disaster Recovery Center Cikarang', 'city' => 'Bekasi'],
            'regions' => [
                ['name' => 'Kantor Operasional Mampang', 'city' => 'Jakarta Selatan', 'address' => 'Gedung Bluebird, Jl. Mampang Prapatan Raya No. 60', 'size' => 'large', 'type' => 'office', 'sites' => []],
                ['name' => 'Regional Jadetabek', 'city' => 'Jakarta', 'size' => 'medium', 'sites' => [
                    ['Pool Mampang', 'Jakarta Selatan', 'medium'], ['Pool Perigi', 'Tangerang Selatan', 'small'], ['Pool Halim', 'Jakarta Timur', 'small'],
                    ['Pool Ciputat', 'Tangerang Selatan', 'small'], ['Pool Garuda (Bandara Soetta)', 'Tangerang', 'small']]],
                ['name' => 'Regional Jawa Barat & Banten', 'city' => 'Bandung', 'size' => 'small', 'sites' => [['Pool Bandung', 'Bandung', 'small'], ['Pool Cilegon', 'Cilegon', 'small']]],
                ['name' => 'Regional Jawa Tengah & DIY', 'city' => 'Semarang', 'size' => 'small', 'sites' => [['Pool Semarang', 'Semarang', 'small'], ['Pool Yogyakarta', 'Yogyakarta', 'small'], ['Pool Solo', 'Surakarta', 'small']]],
                ['name' => 'Regional Jawa Timur', 'city' => 'Surabaya', 'size' => 'small', 'sites' => [['Pool Surabaya', 'Surabaya', 'medium']]],
                ['name' => 'Regional Bali & Nusa Tenggara', 'city' => 'Badung', 'size' => 'small', 'sites' => [
                    ['Pool Jimbaran (Bali)', 'Badung', 'medium', 'Jl. By Pass Nusa Dua No. 4, Jimbaran'], ['Pool Lombok', 'Mataram', 'small', 'Jl. Koperasi No. 102, Ampenan']]],
                ['name' => 'Regional Sumatera', 'city' => 'Medan', 'size' => 'small', 'sites' => [
                    ['Pool Medan', 'Medan', 'small'], ['Pool Palembang', 'Palembang', 'small'], ['Pool Pekanbaru', 'Pekanbaru', 'small'], ['Pool Batam', 'Batam', 'small'], ['Pool Padang', 'Padang', 'small']]],
                ['name' => 'Regional Indonesia Timur', 'city' => 'Makassar', 'size' => 'small', 'sites' => [
                    ['Pool Makassar', 'Makassar', 'small'], ['Pool Balikpapan', 'Balikpapan', 'small'], ['Pool Manado', 'Manado', 'small']]],
            ],
            'brands' => [
                'wan' => ['Cisco', 'ASR 1001-X'], 'fw' => ['Fortinet', 'FortiGate 600F'], 'core' => ['Cisco', 'Catalyst 9500-48Y4C'],
                'tor' => ['Cisco', 'Nexus 93180YC-FX'], 'router' => ['Cisco', 'ISR 1100-4G'], 'site_router' => ['MikroTik', 'RB5009UG+S+IN'],
                'dist' => ['Cisco', 'Catalyst 9300-48P'], 'access' => ['Aruba', 'CX 6100 24G PoE'], 'ap' => ['Ubiquiti', 'UniFi U6 Pro'],
                'server' => [['Dell', 'PowerEdge R760'], ['HPE', 'ProLiant DL380 Gen11']], 'storage' => ['Dell', 'PowerStore 1200T'],
                'ups' => ['APC', 'Smart-UPS SRT 10kVA'], 'pc' => [['Lenovo', 'ThinkCentre M70q Gen 4'], ['HP', 'EliteBook 840 G10'], ['Asus', 'ExpertBook B5']],
                'printer' => ['Epson', 'EcoTank L15160'], 'cctv' => ['Hikvision', 'DS-2CD2143G2-I'], 'phone' => ['Yealink', 'SIP-T54W'],
                'hypervisor' => 'VMware ESXi 8.0 U3',
            ],
            'teams' => [
                'sd' => ['Service Desk Bluebird', 'Service Desk 24x7 untuk karyawan, pool, dan pengemudi'],
                'net' => ['Network & Infrastructure', 'WAN, SD-WAN pool, LAN, Wi-Fi'],
                'app' => ['Digital Product – MyBluebird', 'Aplikasi MyBluebird, Driver App, Goldenbird'],
                'erp' => ['SAP & Finance Systems', 'SAP ERP, otomasi pencatatan keuangan'],
                'euc' => ['End User Computing', 'Laptop, PC, printer, perangkat pool'],
                'sec' => ['Security Operations', 'Keamanan siber dan perlindungan data pribadi'],
                'dc' => ['Data Center & Cloud', 'Server, virtualisasi, backup, DRC'],
            ],
            'apps' => [
                ['MyBluebird Mobile App', 'mybluebird', 'app', 'high', [['bb-mbb-api01', 'Ubuntu 24.04', 'web', 'NGINX 1.26'], ['bb-mbb-api02', 'Ubuntu 24.04', 'web', 'NGINX 1.26'], ['bb-mbb-db01', 'Ubuntu 24.04', 'db', 'PostgreSQL 16']]],
                ['Driver App & Dispatch Engine', 'dispatch', 'app', 'high', [['bb-dsp-app01', 'Ubuntu 24.04', 'mw', 'Apache Kafka 3.7'], ['bb-dsp-db01', 'Ubuntu 24.04', 'db', 'MongoDB 7.0']]],
                ['AI Fleet Placement (Prediksi Permintaan)', 'dispatch', 'app', 'medium', [['bb-ai-ml01', 'Ubuntu 22.04', 'mw', 'Python ML Serving']]],
                ['SAP ERP Finance', 'erp', 'erp', 'high', [['bb-sap-app01', 'SUSE Linux 15', 'mw', 'SAP NetWeaver 7.5'], ['bb-sap-db01', 'SUSE Linux 15', 'db', 'SAP HANA 2.0']]],
                ['Payment Gateway (OVO & Non-Tunai)', 'payment', 'app', 'high', [['bb-pay-gw01', 'Rocky Linux 9', 'web', 'Kong Gateway 3.6']]],
                ['VoIP Pelanggan–Pengemudi', 'voip', 'app', 'medium', [['bb-voip-sbc01', 'Debian 12', 'mw', 'Asterisk 20 SBC']]],
                ['Dashboard Operasional Lintas Fungsi', 'dashboard', 'app', 'medium', [['bb-bi-app01', 'Windows Server 2022', 'web', 'IIS 10'], ['bb-dwh-db01', 'Windows Server 2022', 'db', 'SQL Server 2022']]],
                ['BirdKembang Learning Platform', 'lms', 'app', 'low', [['bb-lms-web01', 'Ubuntu 24.04', 'web', 'Apache 2.4']]],
                ['Microsoft 365 & Email', 'email', 'euc', 'medium', []],
            ],
            'processes' => [['Pemesanan Taksi End-to-End', ['MyBluebird Mobile App', 'Driver App & Dispatch Engine', 'Payment Gateway (OVO & Non-Tunai)', 'VoIP Pelanggan–Pengemudi']],
                ['Rekonsiliasi Pendapatan & Keuangan', ['SAP ERP Finance', 'Payment Gateway (OVO & Non-Tunai)', 'Dashboard Operasional Lintas Fungsi']]],
            'services' => [
                'mybluebird' => ['Reservasi Digital MyBluebird', 'Aplikasi Bisnis', 'Pemesanan Perjalanan', 'app', [['Aplikasi tidak bisa login', 'incident'], ['Order gagal / timeout', 'incident'], ['Promo & voucher bermasalah', 'incident'], ['Permintaan fitur baru', 'service_request']]],
                'dispatch' => ['Driver App & Dispatch', 'Aplikasi Bisnis', 'Pemesanan Perjalanan', 'app', [['Driver App crash', 'incident'], ['Order tidak masuk ke pengemudi', 'incident'], ['Registrasi perangkat pengemudi', 'service_request']]],
                'erp' => ['ERP Keuangan (SAP)', 'Aplikasi Bisnis', 'Record to Report', 'erp', [['Transaksi SAP error', 'incident'], ['Pembuatan user SAP', 'service_request'], ['Penambahan role / otorisasi', 'service_request']]],
                'payment' => ['Pembayaran Non-Tunai', 'Aplikasi Bisnis', 'Pemesanan Perjalanan', 'app', [['Pembayaran e-wallet gagal', 'incident'], ['Selisih rekonsiliasi', 'incident']]],
                'voip' => ['Komunikasi VoIP Pelanggan–Pengemudi', 'Konektivitas & Komunikasi', 'Pemesanan Perjalanan', 'app', [['Panggilan VoIP putus', 'incident']]],
                'dashboard' => ['Dashboard Operasional', 'Data & Analitik', 'Pengambilan Keputusan', 'app', [['Data dashboard tidak update', 'incident'], ['Permintaan laporan baru', 'service_request']]],
                'wan' => ['Konektivitas Pool (SD-WAN)', 'Konektivitas & Komunikasi', 'Operasional Pool', 'net', [['Pool tidak ada koneksi', 'incident'], ['Koneksi lambat', 'incident'], ['Penambahan titik Wi-Fi', 'service_request']]],
                'euc' => ['End User Computing', 'Layanan Pengguna', 'Produktivitas Karyawan', 'euc', [['Laptop/PC rusak', 'incident'], ['Printer bermasalah', 'incident'], ['Permintaan laptop baru', 'service_request'], ['Instalasi software', 'service_request']]],
                'email' => ['Email & Kolaborasi (M365)', 'Layanan Pengguna', 'Produktivitas Karyawan', 'euc', [['Tidak bisa akses email', 'incident'], ['Reset password / MFA', 'service_request'], ['Pembuatan akun baru', 'service_request']]],
                'lms' => ['Learning Platform BirdKembang', 'Layanan Pengguna', 'Pengembangan SDM', 'app', [['Materi tidak bisa diakses', 'incident']]],
                'security' => ['Keamanan Informasi & PDP', 'Keamanan Informasi', 'Tata Kelola', 'sec', [['Email phishing dilaporkan', 'incident'], ['Permintaan akses VPN', 'service_request']]],
            ],
            'vendors' => [
                ['Telkom Indonesia', 'Astinet & MPLS 1 Gbps DC + SD-WAN pool', 'Konektivitas', 2_850_000_000],
                ['Cisco Systems (via mitra)', 'Cisco SmartNet 8x5xNBD – core & WAN', 'Dukungan Perangkat', 640_000_000],
                ['Fortinet (via mitra)', 'FortiCare Premium + UTP Bundle', 'Dukungan Perangkat', 420_000_000],
                ['Microsoft', 'Microsoft 365 E3 Enterprise Agreement', 'Lisensi', 3_200_000_000],
                ['SAP Indonesia', 'SAP Enterprise Support', 'Lisensi & Dukungan', 2_100_000_000],
            ],
            'incidents' => [
                ['MyBluebird gagal memuat daftar armada di area {city}', 'mybluebird', 1, 2], ['Pelanggan tidak menerima OTP saat login MyBluebird', 'mybluebird', 2, 2],
                ['Voucher promo tidak terpotong saat checkout', 'mybluebird', 2, 3], ['Driver App force close setelah update versi terbaru', 'dispatch', 2, 2],
                ['Order tidak diteruskan ke pengemudi di {site}', 'dispatch', 2, 1], ['Pembayaran OVO gagal, saldo terpotong', 'payment', 2, 2],
                ['Selisih rekonsiliasi transaksi non-tunai harian', 'payment', 2, 3], ['Panggilan VoIP pelanggan–pengemudi terputus', 'voip', 2, 3],
                ['Posting jurnal SAP error FI-GL', 'erp', 2, 3], ['SAP lambat saat closing bulanan', 'erp', 1, 2],
                ['Dashboard operasional tidak update sejak pagi', 'dashboard', 2, 3], ['Koneksi internet {site} terputus', 'wan', 2, 2],
                ['Koneksi lambat di {site}, latency tinggi', 'wan', 3, 3], ['Access point area parkir {site} mati', 'wan', 3, 3],
                ['Laptop tidak bisa booting', 'euc', 3, 3], ['Printer kasir {site} paper jam terus', 'euc', 3, 4],
                ['Outlook tidak sinkron', 'email', 3, 3], ['Materi BirdKembang tidak bisa diputar', 'lms', 3, 4],
                ['Laporan email phishing mengatasnamakan HRD', 'security', 2, 2], ['CPU tinggi pada server {ci}', 'dashboard', 2, 2],
            ],
            'requests' => [
                ['Permintaan laptop baru untuk karyawan baru', 'euc'], ['Instalasi Microsoft Visio', 'euc'], ['Reset password & MFA akun M365', 'email'],
                ['Pembuatan akun email karyawan baru', 'email'], ['Pembuatan user SAP untuk staf finance', 'erp'], ['Penambahan role SAP approval PO', 'erp'],
                ['Registrasi perangkat pengemudi baru', 'dispatch'], ['Permintaan akses VPN untuk WFH', 'security'], ['Penambahan titik Wi-Fi di ruang tunggu {site}', 'wan'],
                ['Permintaan laporan utilisasi armada mingguan', 'dashboard'], ['Permintaan fitur favorit alamat di MyBluebird', 'mybluebird'],
            ],
            'changes' => [
                ['Upgrade firmware FortiGate ke 7.4.x', 'normal', 'security'], ['Rilis MyBluebird v{v} (fitur personalisasi)', 'normal', 'mybluebird'],
                ['Rilis Driver App Goldenbird v{v}', 'normal', 'dispatch'], ['Penambahan opsi pembayaran non-tunai baru', 'normal', 'payment'],
                ['Patch bulanan Windows Server', 'standard', 'euc'], ['Penggantian access switch {site}', 'standard', 'wan'],
                ['Migrasi link SD-WAN {site} ke provider cadangan', 'normal', 'wan'], ['Hotfix dispatch engine karena order duplikat', 'emergency', 'dispatch'],
                ['SAP kernel update & support package', 'normal', 'erp'], ['Penambahan kapasitas storage PowerStore', 'standard', 'dashboard'],
                ['Emergency patch kerentanan kritis OpenSSL', 'emergency', 'security'],
            ],
            'problems' => [
                ['Driver App crash berulang pada Android 14', 'dispatch', 'Memory leak pada library peta versi lama', 'Clear cache aplikasi & restart'],
                ['Pembayaran e-wallet gagal saat jam sibuk', 'payment', 'Rate limit API gateway terlalu rendah', 'Retry otomatis setelah 30 detik'],
                ['Pool kehilangan koneksi saat hujan deras', 'wan', 'Konektor outdoor radio backup tidak kedap air', 'Failover manual ke LTE'],
                ['SAP lambat saat closing bulanan', 'erp', 'Index tabel BSEG terfragmentasi & batch job bertabrakan', 'Jadwalkan ulang batch job ke malam hari'],
            ],
            'improvements' => [
                ['Self-service reset password M365', 'Service Request Management', 'Optimize and automate', 'Kurangi 25% tiket reset password'],
                ['Monitoring proaktif link SD-WAN seluruh pool', 'Monitoring & Event Management', 'Collaborate and promote visibility', 'Deteksi gangguan sebelum pengemudi terdampak'],
                ['Prediksi penempatan armada berbasis AI', 'Service Design', 'Focus on value', 'Utilisasi armada naik'],
                ['Katalog layanan digital terpadu', 'Service Catalogue Management', 'Keep it simple and practical', 'Satu pintu permintaan layanan'],
            ],
        ];
    }

    public static function medco(): array
    {
        return [
            'org' => ['code' => 'MEDC', 'name' => 'PT Medco Energi Internasional Tbk', 'industry' => 'Energi (Migas, Ketenagalistrikan)', 'color' => '#047857',
                'website' => 'https://www.medcoenergi.com',
                'description' => 'Perusahaan energi dengan operasi hulu migas di Indonesia, Tailan (Thailand), dan Oman serta bisnis ketenagalistrikan. Strategi Digital IT 5 tahun (2022–2026): SAP S/4HANA, ITSM/ITAM terpusat, AI/ML, keamanan siber.'],
            'children' => [
                ['code' => 'MEPI', 'name' => 'Medco E&P Indonesia', 'industry' => 'Hulu Minyak & Gas'],
                ['code' => 'MPI', 'name' => 'PT Medco Power Indonesia', 'industry' => 'Ketenagalistrikan'],
                ['code' => 'ELB', 'name' => 'PT Energi Listrik Batam', 'industry' => 'Pembangkit Listrik'],
            ],
            'domain' => 'medco.demo',
            'ip' => 20,
            'monitor' => ['SolarWinds', 'Splunk', 'PI System'],
            'hq' => ['name' => 'Gedung The Energy (Kantor Pusat)', 'address' => 'Gedung The Energy, SCBD Lot 11A, Jl. Jend. Sudirman', 'city' => 'Jakarta Selatan', 'lat' => -6.2253, 'lng' => 106.8088],
            'dc' => ['name' => 'Data Center Jakarta (Primary)', 'city' => 'Jakarta'],
            'drc' => ['name' => 'Disaster Recovery Center Batam', 'city' => 'Batam'],
            'regions' => [
                ['name' => 'Sumatera Selatan Asset', 'city' => 'Palembang', 'size' => 'medium', 'type' => 'region', 'sites' => [
                    ['Blok Corridor – Lapangan Suban', 'Musi Banyuasin', 'medium'], ['Blok Corridor – Grissik CPP', 'Musi Banyuasin', 'medium'],
                    ['Blok Rimau', 'Musi Banyuasin', 'small'], ['Blok Lematang', 'Lahat', 'small']]],
                ['name' => 'Natuna Asset', 'city' => 'Natuna', 'size' => 'small', 'type' => 'region', 'sites' => [['South Natuna Sea Block B – FPSO', 'Laut Natuna', 'small'], ['Natuna Shore Base', 'Ranai', 'small']]],
                ['name' => 'Sulawesi Asset', 'city' => 'Banggai', 'size' => 'small', 'type' => 'region', 'sites' => [['Blok Senoro – CPP', 'Banggai', 'small']]],
                ['name' => 'International Asset', 'city' => 'Bangkok', 'size' => 'small', 'type' => 'region', 'country' => 'Thailand', 'sites' => [['Blok Bualuang (Tailan)', 'Teluk Thailand', 'small'], ['Oman Field Office', 'Muscat', 'small']]],
                ['name' => 'Power Generation', 'city' => 'Jakarta', 'size' => 'small', 'type' => 'region', 'sites' => [
                    ['PLTP Ijen', 'Bondowoso', 'small'], ['PLTS Bali Timur', 'Karangasem', 'small'], ['Pembangkit Batam (ELB)', 'Batam', 'small'], ['PLTP Sarulla', 'Tapanuli Utara', 'small']]],
            ],
            'brands' => [
                'wan' => ['Juniper', 'MX204'], 'fw' => ['Palo Alto Networks', 'PA-5450'], 'core' => ['Juniper', 'QFX5120-48Y'],
                'tor' => ['Arista', '7050SX3-48YC8'], 'router' => ['Cisco', 'Catalyst 8300-1N1S'], 'site_router' => ['Cisco', 'IR1101 Industrial'],
                'site_fw' => ['Fortinet', 'FortiGate Rugged 60F'], 'vsat' => ['Hughes', 'HT2010 VSAT'],
                'dist' => ['Cisco', 'Catalyst 9300-24UX'], 'access' => ['Siemens', 'RUGGEDCOM RSG2488'], 'ap' => ['Cisco Meraki', 'MR46'],
                'server' => [['Dell', 'PowerEdge R660'], ['Lenovo', 'ThinkSystem SR650 V3'], ['Nutanix', 'NX-8170-G9']], 'storage' => ['NetApp', 'AFF A400'],
                'ups' => ['Eaton', '93PM 50kW'], 'pc' => [['Dell', 'Latitude 7450'], ['Lenovo', 'ThinkPad T14 Gen 5'], ['Microsoft', 'Surface Laptop 6']],
                'printer' => ['HP', 'LaserJet Enterprise M611'], 'cctv' => ['Axis', 'P1468-XLE Explosion-Protected'], 'phone' => ['Cisco', 'IP Phone 8851'],
                'industrial' => ['Moxa', 'EDS-G516E'],
                'hypervisor' => 'Nutanix AHV 6.8',
            ],
            'teams' => [
                'sd' => ['DIT Service Desk', 'Service desk terpusat (ITSM & ITAM)'],
                'net' => ['Network & OT Connectivity', 'WAN, VSAT, microwave, jaringan lapangan & OT'],
                'erp' => ['SAP S/4HANA CoE', 'Migrasi & operasional SAP S/4HANA'],
                'dc' => ['Data Center & Hybrid Cloud', 'Server, HCI Nutanix, backup, DRC'],
                'sec' => ['Cyber Security Operations', 'SOC, manajemen kerentanan, latihan krisis bersama BSSN/CSA'],
                'app' => ['Data & AI Platform', 'Platform AI/ML, GenAI internal, mSeek, dasbor'],
                'euc' => ['End User Services', 'Perangkat pengguna & Microsoft 365'],
            ],
            'apps' => [
                ['SAP S/4HANA', 'erp', 'erp', 'high', [['md-s4-app01', 'SUSE Linux 15 SP5', 'mw', 'SAP S/4HANA 2023'], ['md-s4-app02', 'SUSE Linux 15 SP5', 'mw', 'SAP S/4HANA 2023'], ['md-s4-db01', 'SUSE Linux 15 SP5', 'db', 'SAP HANA 2.0 SPS07']]],
                ['Platform ITSM & ITAM Terpusat', 'itsm', 'sd', 'medium', [['md-itsm-web01', 'Rocky Linux 9', 'web', 'NGINX 1.26'], ['md-itsm-db01', 'Rocky Linux 9', 'db', 'MySQL 8.4']]],
                ['mSeek AI Agent', 'ai', 'app', 'medium', [['md-ai-gpu01', 'Ubuntu 22.04', 'mw', 'vLLM Inference'], ['md-ai-vec01', 'Ubuntu 22.04', 'db', 'PostgreSQL 16 + pgvector']]],
                ['Platform AI/ML & Generative AI', 'ai', 'app', 'medium', [['md-ml-k8s01', 'Ubuntu 22.04', 'mw', 'Kubernetes 1.30']]],
                ['Dasbor SDM', 'hr', 'app', 'low', [['md-bi-app01', 'Windows Server 2022', 'web', 'Power BI Report Server']]],
                ['OT Data Historian (Produksi)', 'ot', 'net', 'high', [['md-pi-srv01', 'Windows Server 2022', 'mw', 'AVEVA PI Server 2023'], ['md-pi-sql01', 'Windows Server 2022', 'db', 'SQL Server 2022']]],
                ['Supplier Data Management', 'scm', 'erp', 'medium', [['md-sdm-app01', 'Rocky Linux 9', 'web', 'Apache Tomcat 10']]],
                ['Microsoft 365 & Teams', 'email', 'euc', 'medium', []],
            ],
            'processes' => [['Pemantauan Produksi Migas', ['OT Data Historian (Produksi)', 'Platform AI/ML & Generative AI']],
                ['Procure-to-Pay', ['SAP S/4HANA', 'Supplier Data Management']], ['Pengambilan Keputusan SDM', ['Dasbor SDM', 'mSeek AI Agent']]],
            'services' => [
                'erp' => ['SAP S/4HANA', 'Aplikasi Bisnis', 'Record to Report', 'erp', [['Transaksi SAP error', 'incident'], ['Pembuatan user SAP', 'service_request'], ['Permintaan perubahan master data', 'service_request']]],
                'itsm' => ['Layanan ITSM & ITAM', 'Layanan Pengguna', 'Operasional TI', 'sd', [['Portal layanan tidak bisa diakses', 'incident'], ['Registrasi aset TI', 'service_request']]],
                'ai' => ['mSeek & Platform AI', 'Data & Analitik', 'Pengambilan Keputusan', 'app', [['Jawaban mSeek tidak relevan / error', 'incident'], ['Permintaan akses GenAI', 'service_request'], ['Permintaan model AI/ML baru', 'service_request']]],
                'hr' => ['Dasbor SDM', 'Data & Analitik', 'Pengambilan Keputusan', 'app', [['Data dasbor tidak sesuai', 'incident']]],
                'ot' => ['OT Data Historian', 'Operasional Lapangan', 'Produksi Migas', 'net', [['Tag historian tidak update', 'incident'], ['Penambahan tag baru', 'service_request']]],
                'field' => ['Konektivitas Lapangan (VSAT/Microwave)', 'Konektivitas & Komunikasi', 'Produksi Migas', 'net', [['Link VSAT down', 'incident'], ['Latensi tinggi', 'incident'], ['Penambahan bandwidth', 'service_request']]],
                'scm' => ['Supplier Data Management', 'Aplikasi Bisnis', 'Procure-to-Pay', 'erp', [['Vendor tidak bisa login portal', 'incident'], ['Registrasi vendor baru', 'service_request']]],
                'email' => ['Microsoft 365 & Kolaborasi', 'Layanan Pengguna', 'Produktivitas Karyawan', 'euc', [['Teams tidak bisa join meeting', 'incident'], ['Reset password / MFA', 'service_request']]],
                'euc' => ['End User Computing', 'Layanan Pengguna', 'Produktivitas Karyawan', 'euc', [['Laptop rusak', 'incident'], ['Permintaan laptop', 'service_request']]],
                'security' => ['Keamanan Siber (SOC)', 'Keamanan Informasi', 'Tata Kelola', 'sec', [['Simulasi phishing – laporan klik', 'incident'], ['Insiden keamanan terdeteksi', 'incident'], ['Permintaan akses privileged', 'service_request']]],
            ],
            'vendors' => [
                ['Telkomsat', 'VSAT & backhaul satelit lapangan offshore', 'Konektivitas', 4_600_000_000],
                ['Juniper Networks (via mitra)', 'Juniper Care Core Support', 'Dukungan Perangkat', 780_000_000],
                ['Palo Alto Networks (via mitra)', 'Premium Support + Threat Prevention', 'Dukungan Perangkat', 1_150_000_000],
                ['SAP Indonesia', 'RISE with SAP S/4HANA', 'Lisensi & Dukungan', 9_800_000_000],
                ['Nutanix (via mitra)', 'Nutanix Production Support', 'Dukungan Perangkat', 690_000_000],
                ['Microsoft', 'Microsoft 365 E5 + Azure OpenAI', 'Lisensi', 6_400_000_000],
            ],
            'incidents' => [
                ['Link VSAT {site} down, data produksi tidak terkirim', 'field', 1, 1], ['Latensi tinggi microwave link {site}', 'field', 2, 3],
                ['Tag PI historian {site} berhenti update', 'ot', 2, 2], ['Transaksi goods receipt SAP S/4HANA error', 'erp', 2, 3],
                ['SAP S/4HANA lambat saat MRP run', 'erp', 1, 2], ['mSeek memberikan jawaban kosong untuk topik SDM', 'ai', 3, 3],
                ['Pipeline training model AI/ML gagal', 'ai', 3, 3], ['Dasbor SDM menampilkan data headcount ganda', 'hr', 3, 3],
                ['Vendor tidak bisa login portal supplier', 'scm', 3, 3], ['Teams tidak bisa join meeting dari {site}', 'email', 3, 3],
                ['Laptop karyawan terkena layar biru', 'euc', 3, 3], ['Karyawan mengklik tautan simulasi phishing triwulan', 'security', 3, 4],
                ['Deteksi brute force pada akun VPN', 'security', 2, 1], ['Switch RUGGEDCOM {site} reboot berulang', 'field', 2, 2],
                ['Disk hampir penuh pada {ci}', 'itsm', 2, 2], ['Portal ITSM tidak bisa diakses dari Oman', 'itsm', 2, 3],
            ],
            'requests' => [
                ['Pembuatan user SAP S/4HANA untuk staf procurement', 'erp'], ['Perubahan master data material', 'erp'], ['Registrasi aset laptop baru di ITAM', 'itsm'],
                ['Permintaan akses Generative AI internal', 'ai'], ['Permintaan model prediksi decline produksi', 'ai'], ['Penambahan tag historian sumur baru', 'ot'],
                ['Penambahan bandwidth VSAT {site}', 'field'], ['Registrasi vendor baru', 'scm'], ['Reset MFA Microsoft 365', 'email'],
                ['Permintaan laptop untuk engineer lapangan', 'euc'], ['Permintaan akses privileged server', 'security'],
            ],
            'changes' => [
                ['Migrasi modul FI/CO ke SAP S/4HANA (wave {v})', 'normal', 'erp'], ['Upgrade PAN-OS firewall DC', 'normal', 'security'],
                ['Penambahan node Nutanix cluster DC', 'normal', 'itsm'], ['Rilis mSeek fitur topik Keuangan', 'normal', 'ai'],
                ['Patch bulanan server Windows', 'standard', 'euc'], ['Penggantian modem VSAT {site}', 'standard', 'field'],
                ['Emergency patch kerentanan kritis VPN gateway', 'emergency', 'security'], ['Penambahan tag OT historian {site}', 'standard', 'ot'],
                ['Uji failover DRC Batam', 'normal', 'itsm'], ['Rotasi sertifikat SSL portal supplier', 'standard', 'scm'],
            ],
            'problems' => [
                ['VSAT offshore putus saat cuaca buruk', 'field', 'Rain fade Ku-band tanpa adaptive coding', 'Failover ke link microwave'],
                ['Historian kehilangan data saat link putus', 'ot', 'Buffer interface PI tidak dikonfigurasi', 'Aktifkan store-and-forward buffer'],
                ['SAP lambat saat MRP run', 'erp', 'Parallel processing belum dioptimalkan', 'Batasi MRP per plant'],
                ['Akun VPN menjadi target brute force', 'security', 'MFA belum wajib untuk akun lama', 'Blokir geo-IP & paksa MFA'],
            ],
            'improvements' => [
                ['Pusat kendali & dasbor monitoring terpusat', 'Monitoring & Event Management', 'Collaborate and promote visibility', 'Deteksi masalah lebih cepat, gangguan berkurang'],
                ['Demand & Portfolio Management berbasis value case', 'Portfolio Management', 'Focus on value', 'Prioritas inisiatif terukur'],
                ['Citizen Developer & Data Science Academy', 'Workforce & Talent Management', 'Progress iteratively with feedback', 'Kapabilitas digital karyawan meningkat'],
                ['Simulasi phishing triwulanan', 'Information Security Management', 'Start where you are', 'Klik phishing turun'],
                ['Tata kelola data lintas unit bisnis', 'Knowledge Management', 'Think and work holistically', 'Kualitas data & kontrol akses konsisten'],
            ],
        ];
    }

    public static function uin(): array
    {
        $faculties = ['Fakultas Adab dan Ilmu Budaya', 'Fakultas Dakwah dan Komunikasi', 'Fakultas Syariah dan Hukum', 'Fakultas Tarbiyah dan Keguruan',
            'Fakultas Ushuluddin dan Pemikiran Islam', 'Fakultas Sains dan Teknologi', 'Fakultas Ilmu Sosial dan Humaniora',
            'Fakultas Ekonomi dan Bisnis Islam', 'Fakultas Kedokteran dan Ilmu Kesehatan', 'Pascasarjana'];

        return [
            'org' => ['code' => 'UINSK', 'name' => 'UIN Sunan Kalijaga Yogyakarta', 'industry' => 'Pendidikan Tinggi', 'color' => '#15803d',
                'website' => 'https://uin-suka.ac.id',
                'description' => 'Perguruan tinggi negeri Islam di Yogyakarta. Layanan TI dikelola PTIPD (Pusat Teknologi Informasi dan Pangkalan Data): SUKAnet, SUKAmail, SUKAdemia, SUKApustaka, Eduroam, serta helpdesk SIGAP.'],
            'children' => [],
            'domain' => 'uin-suka.demo',
            'ip' => 30,
            'monitor' => ['Zabbix', 'LibreNMS', 'Wazuh'],
            'hq' => ['name' => 'Kampus UIN Sunan Kalijaga', 'address' => 'Jl. Laksda Adisucipto, Papringan, Caturtunggal, Depok', 'city' => 'Sleman', 'lat' => -7.7840, 'lng' => 110.3947, 'type' => 'campus'],
            'dc' => ['name' => 'Data Center & NOC PTIPD', 'city' => 'Sleman'],
            'drc' => ['name' => 'Disaster Recovery Center (DRC) PTIPD', 'city' => 'Sleman'],
            'regions' => array_merge(
                [['name' => 'Gedung Rektorat', 'city' => 'Sleman', 'size' => 'medium', 'type' => 'building', 'sites' => []],
                    ['name' => 'Perpustakaan (SUKApustaka)', 'city' => 'Sleman', 'size' => 'medium', 'type' => 'building', 'sites' => []],
                    ['name' => 'Laboratorium Komputer Terpadu', 'city' => 'Sleman', 'size' => 'large', 'type' => 'building', 'lab' => true, 'sites' => []],
                    ['name' => 'Gedung Multipurpose & SUKA Sport Centre', 'city' => 'Sleman', 'size' => 'small', 'type' => 'building', 'sites' => []]],
                array_map(fn ($f) => ['name' => $f, 'city' => 'Sleman', 'size' => 'medium', 'type' => 'building', 'sites' => []], $faculties)
            ),
            'brands' => [
                'wan' => ['MikroTik', 'CCR2216-1G-12XS-2XQ'], 'fw' => ['Sangfor', 'NGAF M5600'], 'core' => ['Huawei', 'CloudEngine S12700E-8'],
                'tor' => ['Huawei', 'CloudEngine 6881-48S6CQ'], 'router' => ['MikroTik', 'CCR2004-16G-2S+'], 'site_router' => null,
                'dist' => ['Huawei', 'S6730-H24X6C'], 'access' => ['Ruijie', 'RG-S2910-24GT4XS-E'], 'ap' => [['Ruijie', 'RG-AP820-L(V3)'], ['Aruba', 'AP-515'], ['Ruckus', 'R650']],
                'server' => [['Fujitsu', 'PRIMERGY RX2540 M7'], ['Lenovo', 'ThinkSystem SR630 V3'], ['Supermicro', 'SYS-620P-TRT']], 'storage' => ['Synology', 'SA3610'],
                'ups' => ['APC', 'Symmetra PX 40kW'], 'pc' => [['Acer', 'Veriton M4690G'], ['Lenovo', 'ThinkCentre neo 50s'], ['Axioo', 'Hype 5 X6']],
                'printer' => ['Canon', 'imageRUNNER 2625i'], 'cctv' => ['Dahua', 'IPC-HFW2441S'], 'phone' => ['Grandstream', 'GXP1625'],
                'hypervisor' => 'Proxmox VE 8.2',
            ],
            'teams' => [
                'sd' => ['Helpdesk PTIPD (SIGAP)', 'Penerimaan gangguan, aduan, dan permohonan via SIGAP'],
                'net' => ['Jaringan & SUKAnet', 'Backbone fiber kampus, Wi-Fi, Eduroam'],
                'app' => ['Sistem Informasi Akademik', 'SUKAdemia, SUKAstudia, SUKAdministravia, SUKAexecutiva'],
                'dc' => ['Data Center & DRC', 'Server, virtualisasi Proxmox, backup, DRC'],
                'euc' => ['Laboratorium & Perangkat Pengguna', 'Lab komputer, PC dosen & tendik'],
                'sec' => ['Keamanan Informasi (CSIRT)', 'Keamanan siber kampus'],
                'erp' => ['SUKAmail & Kolaborasi', 'Email dan identitas akun kampus'],
            ],
            'apps' => [
                ['SUKAdemia (Sistem Akademik)', 'sukademia', 'app', 'high', [['uin-akd-web01', 'Ubuntu 24.04', 'web', 'NGINX 1.26 + PHP 8.3'], ['uin-akd-web02', 'Ubuntu 24.04', 'web', 'NGINX 1.26 + PHP 8.3'], ['uin-akd-db01', 'Ubuntu 24.04', 'db', 'MariaDB 11.4']]],
                ['SUKAstudia (Pembelajaran Daring)', 'sukastudia', 'app', 'high', [['uin-lms-web01', 'Ubuntu 24.04', 'web', 'Apache 2.4 + Moodle 4.4'], ['uin-lms-db01', 'Ubuntu 24.04', 'db', 'PostgreSQL 16']]],
                ['SUKAmail', 'sukamail', 'erp', 'medium', [['uin-mail-gw01', 'Ubuntu 22.04', 'mw', 'Postfix Mail Gateway']]],
                ['SUKApustaka (Perpustakaan Digital)', 'sukapustaka', 'app', 'medium', [['uin-lib-web01', 'Ubuntu 22.04', 'web', 'Apache 2.4'], ['uin-lib-db01', 'Ubuntu 22.04', 'db', 'MySQL 8.0']]],
                ['SUKAmedia', 'sukamedia', 'app', 'low', [['uin-media-web01', 'Ubuntu 22.04', 'web', 'NGINX 1.24']]],
                ['SUKAdministravia', 'sukadmin', 'app', 'medium', [['uin-adm-web01', 'Ubuntu 24.04', 'web', 'NGINX 1.26 + PHP 8.3']]],
                ['SUKAexecutiva (Dashboard Pimpinan)', 'sukaexec', 'app', 'low', [['uin-exec-bi01', 'Ubuntu 24.04', 'web', 'Metabase 0.50']]],
                ['SIGAP Helpdesk', 'sigap', 'sd', 'medium', [['uin-sigap-web01', 'Ubuntu 24.04', 'web', 'NGINX 1.26 + PHP 8.3']]],
                ['Identity & SSO (LDAP/RADIUS)', 'eduroam', 'net', 'high', [['uin-idm-ldap01', 'Rocky Linux 9', 'mw', 'OpenLDAP + FreeRADIUS']]],
            ],
            'processes' => [['Perkuliahan Daring', ['SUKAstudia (Pembelajaran Daring)', 'SUKAdemia (Sistem Akademik)', 'Identity & SSO (LDAP/RADIUS)']],
                ['Registrasi & KRS Mahasiswa', ['SUKAdemia (Sistem Akademik)', 'Identity & SSO (LDAP/RADIUS)']],
                ['Layanan Perpustakaan', ['SUKApustaka (Perpustakaan Digital)', 'Identity & SSO (LDAP/RADIUS)']]],
            'services' => [
                'sukanet' => ['SUKAnet (Internet & Wi-Fi Kampus)', 'Konektivitas & Komunikasi', 'Kehidupan Kampus', 'net', [['Wi-Fi tidak bisa terhubung', 'incident'], ['Internet lambat', 'incident'], ['Permintaan titik akses baru', 'service_request']]],
                'eduroam' => ['Eduroam UINSK', 'Konektivitas & Komunikasi', 'Kehidupan Kampus', 'net', [['Gagal autentikasi Eduroam', 'incident'], ['Aktivasi akun Eduroam', 'service_request']]],
                'sukamail' => ['SUKAmail', 'Layanan Pengguna', 'Kehidupan Kampus', 'erp', [['Tidak bisa login SUKAmail', 'incident'], ['Reset password akun', 'service_request'], ['Pembuatan email unit', 'service_request']]],
                'sukademia' => ['SUKAdemia (Akademik)', 'Aplikasi Akademik', 'Siklus Akademik', 'app', [['Gagal input KRS', 'incident'], ['Nilai tidak muncul', 'incident'], ['Perubahan data mahasiswa', 'service_request']]],
                'sukastudia' => ['SUKAstudia (Pembelajaran)', 'Aplikasi Akademik', 'Siklus Akademik', 'app', [['Tidak bisa upload tugas', 'incident'], ['Pembuatan kelas daring', 'service_request']]],
                'sukapustaka' => ['SUKApustaka', 'Aplikasi Akademik', 'Riset & Pustaka', 'app', [['Repositori tidak bisa diakses', 'incident'], ['Akses e-journal', 'service_request']]],
                'sukamedia' => ['SUKAmedia', 'Aplikasi Akademik', 'Kehidupan Kampus', 'app', [['Streaming video bermasalah', 'incident']]],
                'sukadmin' => ['SUKAdministravia', 'Aplikasi Administrasi', 'Administrasi Kampus', 'app', [['Surat elektronik tidak terkirim', 'incident'], ['Penambahan hak akses unit', 'service_request']]],
                'sukaexec' => ['SUKAexecutiva', 'Data & Analitik', 'Administrasi Kampus', 'app', [['Data dashboard tidak sesuai', 'incident']]],
                'sigap' => ['SIGAP – Gangguan, Aduan & Permohonan', 'Layanan Pengguna', 'Kehidupan Kampus', 'sd', [['Aduan layanan', 'incident'], ['Permohonan layanan TI', 'service_request']]],
                'lab' => ['Laboratorium Komputer', 'Layanan Pengguna', 'Siklus Akademik', 'euc', [['PC lab rusak', 'incident'], ['Instalasi software praktikum', 'service_request'], ['Peminjaman lab untuk ujian', 'service_request']]],
                'dc' => ['Hosting & Data Center', 'Data Center & Cloud', 'Riset & Pustaka', 'dc', [['Website unit down', 'incident'], ['Permintaan VPS / hosting website', 'service_request']]],
            ],
            'vendors' => [
                ['Telkom Indonesia', 'Astinet 5 Gbps + Metro-E', 'Konektivitas', 1_950_000_000],
                ['Biznet', 'Dedicated Internet 2 Gbps (redundan)', 'Konektivitas', 780_000_000],
                ['Huawei (via mitra)', 'Huawei Co-Care Core Switch', 'Dukungan Perangkat', 310_000_000],
                ['Sangfor (via mitra)', 'Sangfor NGAF Subscription', 'Dukungan Perangkat', 240_000_000],
                ['Microsoft', 'Microsoft 365 A3 Campus Agreement', 'Lisensi', 1_200_000_000],
            ],
            'incidents' => [
                ['Wi-Fi SUKAnet di {site} tidak bisa terhubung', 'sukanet', 2, 2], ['Internet lambat di {site} saat jam kuliah', 'sukanet', 2, 3],
                ['Gagal autentikasi Eduroam untuk mahasiswa baru', 'eduroam', 2, 2], ['Mahasiswa tidak bisa input KRS (error 500)', 'sukademia', 1, 1],
                ['Nilai semester tidak muncul di SUKAdemia', 'sukademia', 2, 2], ['Tidak bisa upload tugas di SUKAstudia', 'sukastudia', 2, 2],
                ['Video kuliah SUKAmedia buffering', 'sukamedia', 3, 3], ['Repositori SUKApustaka tidak bisa diakses', 'sukapustaka', 2, 3],
                ['Dosen tidak bisa login SUKAmail', 'sukamail', 3, 3], ['Surat elektronik SUKAdministravia tidak terkirim', 'sukadmin', 3, 3],
                ['PC lab {site} tidak bisa booting', 'lab', 3, 3], ['Proyektor & PC kelas {site} tidak terhubung jaringan', 'lab', 3, 2],
                ['Website fakultas down', 'dc', 2, 2], ['Aduan SIGAP: akun mahasiswa terkunci', 'sigap', 3, 3],
                ['Dashboard SUKAexecutiva data mahasiswa aktif tidak sesuai', 'sukaexec', 3, 3], ['Memory tinggi pada {ci}', 'dc', 2, 2],
            ],
            'requests' => [
                ['Reset password akun SUKAmail', 'sukamail'], ['Pembuatan email unit kerja', 'sukamail'], ['Aktivasi akun Eduroam dosen tamu', 'eduroam'],
                ['Perubahan data mahasiswa (nama/NIK)', 'sukademia'], ['Pembuatan kelas daring semester baru', 'sukastudia'], ['Akses e-journal untuk penelitian', 'sukapustaka'],
                ['Instalasi software praktikum (SPSS, MATLAB)', 'lab'], ['Peminjaman lab komputer untuk ujian CBT', 'lab'], ['Permintaan VPS untuk website jurnal', 'dc'],
                ['Penambahan titik akses Wi-Fi di {site}', 'sukanet'], ['Penambahan hak akses SUKAdministravia', 'sukadmin'], ['Permohonan layanan TI via SIGAP', 'sigap'],
            ],
            'changes' => [
                ['Upgrade SUKAdemia modul KRS semester {v}', 'normal', 'sukademia'], ['Upgrade Moodle SUKAstudia ke versi terbaru', 'normal', 'sukastudia'],
                ['Penambahan AP Wi-Fi di {site}', 'standard', 'sukanet'], ['Penggantian access switch {site}', 'standard', 'sukanet'],
                ['Upgrade firmware core switch Huawei', 'normal', 'sukanet'], ['Penambahan kapasitas bandwidth internet kampus', 'normal', 'sukanet'],
                ['Reimaging PC lab komputer awal semester', 'standard', 'lab'], ['Emergency patch kerentanan kritis web server', 'emergency', 'dc'],
                ['Migrasi VM ke cluster Proxmox baru', 'normal', 'dc'], ['Uji failover DRC PTIPD', 'normal', 'dc'],
            ],
            'problems' => [
                ['SUKAdemia error 500 saat puncak KRS', 'sukademia', 'Pool koneksi database habis saat ribuan sesi bersamaan', 'Antrian KRS bertahap per angkatan'],
                ['Eduroam gagal untuk akun baru', 'eduroam', 'Sinkronisasi LDAP ke RADIUS terlambat 24 jam', 'Sinkronisasi manual oleh helpdesk'],
                ['Wi-Fi lambat di gedung kuliah padat', 'sukanet', 'Channel overlap & AP terlalu padat di 2.4 GHz', 'Arahkan klien ke 5 GHz (band steering)'],
                ['Website fakultas sering down', 'dc', 'CMS usang terkena serangan bot', 'Aktifkan WAF dan rate limit'],
            ],
            'improvements' => [
                ['Integrasi SIGAP dengan katalog layanan PTIPD', 'Service Desk', 'Collaborate and promote visibility', 'Satu pintu aduan & permohonan'],
                ['Antrian KRS bertahap per angkatan', 'Capacity & Performance Management', 'Keep it simple and practical', 'Nol downtime saat KRS'],
                ['Monitoring Wi-Fi per gedung (heatmap)', 'Monitoring & Event Management', 'Start where you are', 'Keluhan Wi-Fi turun'],
                ['Basis pengetahuan self-help untuk mahasiswa', 'Knowledge Management', 'Optimize and automate', 'Tiket level-1 berkurang'],
            ],
        ];
    }
}
