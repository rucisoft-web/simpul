<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Ci;
use App\Models\Contract;
use App\Models\DeviceModel;
use App\Models\Document;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Improvement;
use App\Models\KnownError;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Person;
use App\Models\Service;
use App\Models\ServiceFamily;
use App\Models\Sla;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Itil;
use Carbon\Carbon;
use Database\Seeders\data\OrgProfiles;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data demo Simpul ITSM: PT Blue Bird Tbk, PT Medco Energi Internasional Tbk, UIN Sunan Kalijaga.
 * Seluruh perangkat, IP, orang, dan tiket adalah data simulasi.
 */
class DemoSeeder extends Seeder
{
    private $faker;
    private Carbon $now;
    private array $brandCache = [];
    private array $modelCache = [];
    private array $families = [];
    private array $refCounter = ['incident' => 0, 'request' => 0, 'problem' => 0, 'change' => 0];

    private const BRAND_META = [
        'Cisco' => ['USA', '#049fd9'], 'Cisco Meraki' => ['USA', '#67b346'], 'Juniper' => ['USA', '#84b135'], 'Arista' => ['USA', '#1d3f72'],
        'Fortinet' => ['USA', '#da291c'], 'Palo Alto Networks' => ['USA', '#fa582d'], 'Aruba' => ['USA', '#ff8300'], 'Ubiquiti' => ['USA', '#0559c9'],
        'MikroTik' => ['Latvia', '#293239'], 'Huawei' => ['China', '#cf0a2c'], 'Ruijie' => ['China', '#e60012'], 'Ruckus' => ['USA', '#ec7600'],
        'Sangfor' => ['China', '#0a65c4'], 'Siemens' => ['Germany', '#009999'], 'Moxa' => ['Taiwan', '#008c95'], 'Hughes' => ['USA', '#0033a0'],
        'Dell' => ['USA', '#007db8'], 'HPE' => ['USA', '#01a982'], 'HP' => ['USA', '#0096d6'], 'Lenovo' => ['China', '#e2231a'],
        'Fujitsu' => ['Japan', '#e60012'], 'Supermicro' => ['USA', '#1f4e9c'], 'Nutanix' => ['USA', '#024da1'], 'NetApp' => ['USA', '#0067c5'],
        'Synology' => ['Taiwan', '#2b2b2b'], 'APC' => ['France', '#3dcd58'], 'Eaton' => ['Ireland', '#005eb8'], 'Asus' => ['Taiwan', '#00539b'],
        'Acer' => ['Taiwan', '#83b81a'], 'Axioo' => ['Indonesia', '#e31e24'], 'Microsoft' => ['USA', '#737373'], 'Epson' => ['Japan', '#003399'],
        'Canon' => ['Japan', '#cc0000'], 'Hikvision' => ['China', '#c8102e'], 'Dahua' => ['China', '#e2001a'], 'Axis' => ['Sweden', '#ffcc33'],
        'Yealink' => ['China', '#3a7ce6'], 'Grandstream' => ['USA', '#00a3e0'],
    ];

    public function run(): void
    {
        mt_srand(2026);
        $this->faker = FakerFactory::create('id_ID');
        $this->faker->seed(2026);
        $this->now = Carbon::now()->startOfHour();

        foreach (['Konektivitas & Komunikasi' => 'wifi', 'Aplikasi Bisnis' => 'grid', 'Aplikasi Akademik' => 'book', 'Aplikasi Administrasi' => 'file',
            'Layanan Pengguna' => 'user', 'Data & Analitik' => 'chart', 'Keamanan Informasi' => 'shield', 'Data Center & Cloud' => 'server',
            'Operasional Lapangan' => 'bolt'] as $name => $icon) {
            $this->families[$name] = ServiceFamily::create(['name' => $name, 'icon' => $icon])->id;
        }

        foreach (OrgProfiles::all() as $profile) {
            $this->seedOrganization($profile);
        }

        $this->seedFaq();

        User::create(['name' => 'Administrator', 'email' => config('simpul.admin_email'), 'password' => config('simpul.demo_password'), 'role' => 'admin']);
        User::create(['name' => 'Tamu Demo', 'email' => 'tamu@simpul.test', 'password' => Str::random(32), 'role' => 'guest']);
    }

    // ------------------------------------------------------------------ helpers

    private function model(array $bm, string $type): array
    {
        [$brandName, $modelName] = $bm;
        if (! isset($this->brandCache[$brandName])) {
            $meta = self::BRAND_META[$brandName] ?? [null, '#64748b'];
            $this->brandCache[$brandName] = Brand::create(['name' => $brandName, 'country' => $meta[0], 'color' => $meta[1]])->id;
        }
        $key = $brandName.'|'.$modelName;
        if (! isset($this->modelCache[$key])) {
            $this->modelCache[$key] = DeviceModel::create(['brand_id' => $this->brandCache[$brandName], 'name' => $modelName, 'type' => $type])->id;
        }

        return ['brand_id' => $this->brandCache[$brandName], 'model_id' => $this->modelCache[$key]];
    }

    private function pick(array $a)
    {
        return $a[array_rand($a)];
    }

    /** Pilih brand/model; spesifikasi bisa berupa satu pasangan atau daftar pasangan. */
    private function bm($spec): array
    {
        return is_array($spec[0]) ? $this->pick($spec) : $spec;
    }

    private function serial(string $brand): string
    {
        return strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $brand), 0, 3)).strtoupper(Str::random(3)).mt_rand(100000, 999999);
    }

    private function mac(): string
    {
        return implode(':', array_map(fn () => sprintf('%02x', mt_rand(0, 255)), range(1, 6)));
    }

    private function ci(array $ctx, array $attrs, ?array $bm = null): Ci
    {
        $data = array_merge([
            'organization_id' => $ctx['org']->id,
            'status' => 'production',
            'business_criticity' => 'medium',
            'purchase_date' => $this->now->copy()->subDays(mt_rand(120, 1800))->toDateString(),
        ], $attrs);
        if ($bm) {
            $data = array_merge($data, $this->model($bm, $attrs['class']));
            $data['serial_number'] ??= $this->serial($bm[0]);
            $data['asset_number'] ??= $ctx['org']->code.'-'.str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $data['warranty_end'] ??= Carbon::parse($data['purchase_date'])->addYears(mt_rand(3, 5))->toDateString();
        }

        return Ci::create($data);
    }

    private function iface(Ci $ci, array $names, ?string $ipPrefix = null): void
    {
        $rows = [];
        foreach ($names as $i => $n) {
            $rows[] = ['ci_id' => $ci->id, 'name' => $n, 'ip_address' => $i === 0 ? $ci->management_ip : ($ipPrefix ? $ipPrefix.'.'.mt_rand(2, 250) : null),
                'mac_address' => $this->mac(), 'speed' => $this->pick(['1 Gbps', '10 Gbps', '25 Gbps']), 'vlan' => (string) $this->pick([1, 10, 20, 30, 99, 100])];
        }
        DB::table('physical_interfaces')->insert($rows);
    }

    private function relate(Ci $source, Ci $target, string $type = 'depends_on'): void
    {
        DB::table('ci_relations')->insert(['source_id' => $source->id, 'target_id' => $target->id, 'type' => $type]);
    }

    private function person(array $ctx, array $attrs = []): Person
    {
        $first = $this->faker->firstName;
        $last = $this->faker->lastName;

        return Person::create(array_merge([
            'organization_id' => $ctx['org']->id,
            'first_name' => $first,
            'last_name' => $last,
            'email' => Str::lower(Str::slug($first.'.'.$last, '.')).mt_rand(1, 99).'@'.$ctx['p']['domain'],
            'phone' => '+62 8'.mt_rand(11, 59).' '.mt_rand(1000, 9999).' '.mt_rand(1000, 9999),
            'employee_number' => $ctx['org']->code.mt_rand(10000, 99999),
            'status' => 'active',
        ], $attrs));
    }

    // ------------------------------------------------------------------ organization

    private function seedOrganization(array $p): void
    {
        $org = Organization::create($p['org']);
        $ctx = ['org' => $org, 'p' => $p, 'b' => $p['brands'], 'ipc' => 0, 'sites' => [], 'devices' => []];
        $orgIds = [$org->id];
        foreach ($p['children'] as $child) {
            $orgIds[] = Organization::create(array_merge($child, ['parent_id' => $org->id, 'color' => $p['org']['color']]))->id;
        }

        // ----- Lokasi (pohon bertingkat)
        $hq = Location::create(['organization_id' => $org->id, 'name' => $p['hq']['name'], 'type' => $p['hq']['type'] ?? 'hq',
            'address' => $p['hq']['address'], 'city' => $p['hq']['city'], 'lat' => $p['hq']['lat'], 'lng' => $p['hq']['lng']]);
        $dc = Location::create(['organization_id' => $org->id, 'parent_id' => $hq->id, 'name' => $p['dc']['name'], 'type' => 'datacenter', 'city' => $p['dc']['city']]);
        $drc = Location::create(['organization_id' => $org->id, 'parent_id' => $hq->id, 'name' => $p['drc']['name'], 'type' => 'datacenter', 'city' => $p['drc']['city']]);
        $ctx['hq'] = $hq;
        $ctx['dc'] = $dc;

        // ----- Tim & orang
        $teams = [];
        foreach ($p['teams'] as $key => [$name, $function]) {
            $teams[$key] = Team::create(['organization_id' => $org->id, 'name' => $name, 'function' => $function, 'email' => Str::slug($name).'@'.$p['domain']]);
        }
        $ctx['teams'] = $teams;
        $cio = $this->person($ctx, ['location_id' => $hq->id, 'function' => $org->code === 'UINSK' ? 'Kepala PTIPD' : 'Chief Information Officer']);
        $agents = [];
        foreach ($teams as $key => $team) {
            $lead = $this->person($ctx, ['location_id' => $hq->id, 'function' => 'Team Lead '.$team->name, 'manager_id' => $cio->id]);
            $team->members()->attach($lead->id, ['role' => 'Team Lead']);
            $agents[$key] = [$lead];
            $n = $key === 'sd' ? 5 : mt_rand(2, 4);
            for ($i = 0; $i < $n; $i++) {
                $a = $this->person($ctx, ['location_id' => $this->pick([$hq->id, $dc->id]), 'function' => $this->pick(['Engineer', 'Senior Engineer', 'Analyst', 'Specialist']).' '.$team->name, 'manager_id' => $lead->id]);
                $team->members()->attach($a->id, ['role' => $this->pick(['Agent L1', 'Agent L2', 'Agent L3'])]);
                $agents[$key][] = $a;
            }
        }
        $ctx['agents'] = $agents;

        // ----- Topologi jaringan data center
        $this->buildDataCenter($ctx, $dc, $drc);

        // ----- Wilayah, pool, site, gedung
        $callers = [];
        foreach ($p['regions'] as $region) {
            $rloc = Location::create(['organization_id' => $org->id, 'parent_id' => $hq->id, 'name' => $region['name'],
                'type' => $region['type'] ?? 'region', 'address' => $region['address'] ?? null, 'city' => $region['city'], 'country' => $region['country'] ?? 'Indonesia']);
            $regionRouter = $this->buildSite($ctx, $rloc, $region['size'], $ctx['core2'], $region['lab'] ?? false);
            for ($i = 0; $i < 3; $i++) {
                $callers[] = $this->person($ctx, ['location_id' => $rloc->id, 'organization_id' => $this->pick($orgIds), 'function' => $this->pick(['Staf Operasional', 'Supervisor', 'Manajer', 'Staf Administrasi', 'Staf Keuangan'])]);
            }
            foreach ($region['sites'] as $s) {
                $sloc = Location::create(['organization_id' => $org->id, 'parent_id' => $rloc->id, 'name' => $s[0], 'type' => str_starts_with($s[0], 'Pool') ? 'pool' : 'site',
                    'city' => $s[1], 'address' => $s[3] ?? null, 'country' => $region['country'] ?? 'Indonesia']);
                $this->buildSite($ctx, $sloc, $s[2], $regionRouter);
                $callers[] = $this->person($ctx, ['location_id' => $sloc->id, 'organization_id' => $this->pick($orgIds), 'function' => $this->pick(['Kepala Pool', 'Field Supervisor', 'Staf Operasional', 'Teknisi'])]);
            }
        }
        $hqFunctions = $org->code === 'UINSK'
            ? ['Dosen', 'Mahasiswa', 'Tenaga Kependidikan', 'Staf Akademik', 'Pustakawan', 'Staf Keuangan']
            : ['Staf Keuangan', 'Staf SDM', 'Manajer Pemasaran', 'Analis Bisnis', 'Sekretaris', 'Staf Pengadaan'];
        for ($i = 0; $i < 25; $i++) {
            $callers[] = $this->person($ctx, ['location_id' => $hq->id, 'organization_id' => $this->pick($orgIds), 'function' => $this->pick($hqFunctions)]);
        }
        $ctx['callers'] = $callers;

        // pemilik perangkat pengguna
        $rows = [];
        foreach (Ci::where('organization_id', $org->id)->where('class', 'PC')->pluck('id') as $pcId) {
            $rows[] = ['ci_id' => $pcId, 'person_id' => $this->pick($callers)->id, 'role' => 'Pengguna'];
        }
        DB::table('ci_person')->insert($rows);

        // ----- Layanan, aplikasi, SLA, kontrak
        $this->buildServices($ctx);

        // ----- Tiket & rekaman operasional
        $this->buildTickets($ctx);
        $this->buildEvents($ctx);
        $this->buildImprovements($ctx, $cio);
        $this->buildDocuments($ctx);

        User::create(['name' => $agents['sd'][1]->name, 'email' => 'agent.'.Str::lower($org->code).'@simpul.test', 'password' => config('simpul.demo_password'), 'role' => 'agent', 'person_id' => $agents['sd'][1]->id]);
    }

    private function nextIp(array &$ctx): string
    {
        $ctx['ipc']++;

        return '10.'.$ctx['p']['ip'].'.'.intdiv($ctx['ipc'], 250).'.'.($ctx['ipc'] % 250 + 2);
    }

    private function buildDataCenter(array &$ctx, Location $dc, Location $drc): void
    {
        $b = $ctx['b'];
        $code = Str::lower($ctx['org']->code);
        $net = $ctx['teams']['net']->id;
        $dcTeam = $ctx['teams']['dc']->id;

        $racks = [];
        foreach (['A01', 'A02', 'A03', 'B01'] as $r) {
            $racks[] = $this->ci($ctx, ['class' => 'Rack', 'name' => "RACK-$r ".$dc->name, 'location_id' => $dc->id, 'rack_units' => 42, 'team_id' => $dcTeam], ['APC', 'NetShelter SX 42U']);
        }
        $ups = $this->ci($ctx, ['class' => 'PowerSource', 'name' => "UPS-01 $code-dc", 'location_id' => $dc->id, 'business_criticity' => 'high', 'team_id' => $dcTeam], $b['ups']);
        $this->ci($ctx, ['class' => 'PowerSource', 'name' => "UPS-02 $code-dc", 'location_id' => $dc->id, 'business_criticity' => 'high', 'team_id' => $dcTeam], $b['ups']);

        $isp = $ctx['p']['vendors'][0][0];
        $wan = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-wan-rtr01", 'network_role' => 'wan', 'location_id' => $dc->id, 'rack_id' => $racks[0]->id,
            'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'firmware' => $this->pick(['17.9.4a', '23.4R2', '7.15.3']), 'rack_units' => 1, 'team_id' => $net,
            'description' => "Router edge internet & MPLS (uplink $isp)"], $b['wan']);
        $this->iface($wan, ['Mgmt0', 'Gi0/0/0 (ISP-1)', 'Gi0/0/1 (ISP-2)', 'Te0/1/0 (FW)'], '203.0.113');
        $ctx['devices'][] = $wan;

        $fw = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-fw-cluster01", 'network_role' => 'firewall', 'parent_id' => $wan->id, 'location_id' => $dc->id,
            'rack_id' => $racks[0]->id, 'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'rack_units' => 2, 'team_id' => $ctx['teams']['sec']->id,
            'description' => 'Firewall NGFW HA active-passive'], $b['fw']);
        $this->iface($fw, ['mgmt', 'port1 (WAN)', 'port2 (DMZ)', 'port3 (CORE)', 'ha1'], '10.'.$ctx['p']['ip'].'.250');
        $ctx['devices'][] = $fw;

        $cores = [];
        foreach ([1, 2] as $i) {
            $cores[$i] = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-core-sw0$i", 'network_role' => 'core', 'parent_id' => $fw->id, 'location_id' => $dc->id,
                'rack_id' => $racks[1]->id, 'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'rack_units' => 2, 'team_id' => $net], $b['core']);
            $this->iface($cores[$i], ['Mgmt', 'Hu1/0/1 (FW)', 'Hu1/0/2 (Peer-link)', 'Te1/0/1 (ToR-01)', 'Te1/0/2 (ToR-02)', 'Te1/0/24 (WAN-Site)']);
            $ctx['devices'][] = $cores[$i];
        }
        $ctx['core2'] = $cores[2];

        // Server fisik + virtualisasi
        $tors = [];
        foreach ([1, 2] as $i) {
            $tors[$i] = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-dc-tor0$i", 'network_role' => 'distribution', 'parent_id' => $cores[1]->id,
                'location_id' => $dc->id, 'rack_id' => $racks[$i + 1]->id, 'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'rack_units' => 1, 'team_id' => $net], $b['tor']);
            $this->iface($tors[$i], ['Mgmt', 'Eth1/49 (Core-01)', 'Eth1/50 (Core-02)']);
            $ctx['devices'][] = $tors[$i];
        }
        $hosts = [];
        for ($i = 1; $i <= 6; $i++) {
            $tor = $tors[$i <= 3 ? 1 : 2];
            $h = $this->ci($ctx, ['class' => 'Server', 'name' => sprintf('%s-hv%02d', $code, $i), 'parent_id' => $tor->id, 'location_id' => $dc->id,
                'rack_id' => $tor->rack_id, 'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'os_family' => 'Hypervisor', 'os_version' => $b['hypervisor'],
                'cpu' => $this->pick(['2x Intel Xeon Gold 6430', '2x AMD EPYC 9354', '2x Intel Xeon Silver 4416+']), 'ram' => $this->pick(['512 GB', '768 GB', '1 TB']),
                'rack_units' => 2, 'team_id' => $dcTeam, 'network_role' => 'endpoint'], $this->bm($b['server']));
            $this->iface($h, ['BMC (iDRAC/iLO/XCC)', 'nic0', 'nic1'], '10.'.$ctx['p']['ip'].'.240');
            $this->relate($h, $ups, 'powered_by');
            $hosts[] = $h;
        }
        $ctx['hosts'] = $hosts;
        $storage = $this->ci($ctx, ['class' => 'StorageSystem', 'name' => "$code-san01", 'parent_id' => $tors[2]->id, 'location_id' => $dc->id, 'rack_id' => $racks[3]->id,
            'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'rack_units' => 4, 'team_id' => $dcTeam, 'network_role' => 'endpoint',
            'description' => 'All-flash storage untuk datastore virtualisasi'], $b['storage']);
        foreach ($hosts as $h) {
            $this->relate($h, $storage, 'uses_storage');
        }

        // DRC: router + server replika
        $drcRtr = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-drc-rtr01", 'network_role' => 'wan', 'parent_id' => $cores[2]->id, 'location_id' => $drc->id,
            'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'team_id' => $net, 'description' => 'Link replikasi ke DRC'], $b['router']);
        $ctx['devices'][] = $drcRtr;
        for ($i = 1; $i <= 2; $i++) {
            $this->ci($ctx, ['class' => 'Server', 'name' => sprintf('%s-drc-hv%02d', $code, $i), 'parent_id' => $drcRtr->id, 'location_id' => $drc->id, 'os_family' => 'Hypervisor',
                'os_version' => $b['hypervisor'], 'business_criticity' => 'medium', 'management_ip' => $this->nextIp($ctx), 'ram' => '512 GB', 'team_id' => $dcTeam, 'network_role' => 'endpoint'], $this->bm($b['server']));
        }
        $this->ci($ctx, ['class' => 'StorageSystem', 'name' => "$code-drc-backup01", 'parent_id' => $drcRtr->id, 'location_id' => $drc->id, 'business_criticity' => 'medium',
            'management_ip' => $this->nextIp($ctx), 'team_id' => $dcTeam, 'network_role' => 'endpoint', 'description' => 'Target backup & replikasi'], $b['storage']);
    }

    /** Membangun site: router -> (firewall) -> distribusi -> akses -> AP & endpoint. Mengembalikan router site. */
    private function buildSite(array &$ctx, Location $loc, string $size, Ci $uplink, bool $lab = false): Ci
    {
        $b = $ctx['b'];
        $code = Str::lower($ctx['org']->code);
        $slug = rtrim(Str::limit(Str::slug(preg_replace('/^(Pool|Regional|Fakultas|Gedung|Blok)\s+/i', '', $loc->name)), 14, ''), '-');
        $net = $ctx['teams']['net']->id;
        $euc = $ctx['teams']['euc']->id;
        $isField = $code === 'medc' && $loc->type === 'site';

        $routerBm = (($loc->type === 'pool' || $isField) && $b['site_router']) ? $b['site_router'] : $b['router'];
        $router = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-$slug-rtr01", 'network_role' => 'wan', 'parent_id' => $uplink->id, 'location_id' => $loc->id,
            'business_criticity' => $size === 'small' ? 'medium' : 'high', 'management_ip' => $this->nextIp($ctx), 'team_id' => $net,
            'description' => $isField ? 'Router industri + backhaul VSAT/microwave' : ($loc->type === 'building' ? 'Router/L3 gedung (fiber backbone kampus)' : 'Router SD-WAN site')], $routerBm);
        $this->iface($router, ['Mgmt', 'WAN1', 'WAN2/LTE', 'LAN1']);
        $ctx['devices'][] = $router;
        $ctx['sites'][] = $loc;
        $top = $router;

        if ($isField) {
            $ctx['devices'][] = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-$slug-vsat01", 'network_role' => 'wan', 'parent_id' => $router->id, 'location_id' => $loc->id,
                'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'team_id' => $net, 'description' => 'Terminal VSAT Ku-band'], $b['vsat']);
            $top = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-$slug-fw01", 'network_role' => 'firewall', 'parent_id' => $router->id, 'location_id' => $loc->id,
                'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'team_id' => $ctx['teams']['sec']->id, 'description' => 'Firewall segmentasi IT/OT (IEC 62443)'], $b['site_fw']);
            $ctx['devices'][] = $top;
        }

        $accessCount = ['small' => 1, 'medium' => 2, 'large' => 3][$size];
        $parentForAccess = $top;
        if ($size !== 'small') {
            $parentForAccess = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => "$code-$slug-dist01", 'network_role' => 'distribution', 'parent_id' => $top->id,
                'location_id' => $loc->id, 'business_criticity' => 'high', 'management_ip' => $this->nextIp($ctx), 'team_id' => $net], $b['dist']);
            $this->iface($parentForAccess, ['Vlanif99', 'XGE0/0/1 (Uplink)', 'XGE0/0/2', 'XGE0/0/3']);
            $ctx['devices'][] = $parentForAccess;
        }

        for ($a = 1; $a <= $accessCount; $a++) {
            $accBm = $b['access'];
            if ($code === 'medc') {
                $accBm = $isField ? ($a === 1 ? $b['access'] : $b['industrial']) : ['Cisco', 'Catalyst 9200L-48P'];
            }
            $acc = $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => sprintf('%s-%s-acc%02d', $code, $slug, $a), 'network_role' => 'access', 'parent_id' => $parentForAccess->id,
                'location_id' => $loc->id, 'business_criticity' => 'medium', 'management_ip' => $this->nextIp($ctx), 'team_id' => $net], $accBm);
            $ctx['devices'][] = $acc;

            $apCount = $lab ? 2 : mt_rand(1, 2);
            for ($i = 1; $i <= $apCount; $i++) {
                $this->ci($ctx, ['class' => 'NetworkDevice', 'name' => sprintf('%s-%s-ap%02d%02d', $code, $slug, $a, $i), 'network_role' => 'wireless', 'parent_id' => $acc->id,
                    'location_id' => $loc->id, 'business_criticity' => 'low', 'management_ip' => $this->nextIp($ctx), 'team_id' => $net], $this->bm($b['ap']));
            }

            $endpoints = $lab ? 8 : mt_rand(2, 3);
            for ($i = 1; $i <= $endpoints; $i++) {
                $this->ci($ctx, ['class' => 'PC', 'name' => sprintf('%s-%s-pc%02d%02d', $code, $slug, $a, $i), 'network_role' => 'endpoint', 'parent_id' => $acc->id,
                    'location_id' => $loc->id, 'business_criticity' => 'low', 'os_family' => 'Windows', 'os_version' => $this->pick(['Windows 11 Pro 23H2', 'Windows 11 Pro 24H2', 'Windows 10 Pro 22H2']),
                    'cpu' => $this->pick(['Intel Core i5-1335U', 'Intel Core i7-1365U', 'AMD Ryzen 5 7530U', 'Intel Core i5-13400']), 'ram' => $this->pick(['8 GB', '16 GB', '32 GB']),
                    'team_id' => $euc, 'status' => mt_rand(1, 20) === 1 ? 'stock' : 'production'], $this->bm($b['pc']));
            }
            if ($a === 1) {
                $this->ci($ctx, ['class' => 'Printer', 'name' => sprintf('%s-%s-prn01', $code, $slug), 'network_role' => 'endpoint', 'parent_id' => $acc->id,
                    'location_id' => $loc->id, 'business_criticity' => 'low', 'management_ip' => $this->nextIp($ctx), 'team_id' => $euc], $b['printer']);
                $this->ci($ctx, ['class' => 'Peripheral', 'name' => sprintf('%s-%s-cctv01', $code, $slug), 'network_role' => 'endpoint', 'parent_id' => $acc->id,
                    'location_id' => $loc->id, 'business_criticity' => 'low', 'management_ip' => $this->nextIp($ctx), 'team_id' => $net, 'description' => 'Kamera CCTV IP'], $b['cctv']);
                if ($size !== 'small') {
                    $this->ci($ctx, ['class' => 'Phone', 'name' => sprintf('%s-%s-tel01', $code, $slug), 'network_role' => 'endpoint', 'parent_id' => $acc->id,
                        'location_id' => $loc->id, 'business_criticity' => 'low', 'team_id' => $euc], $b['phone']);
                }
            }
        }

        return $router;
    }

    private function buildServices(array &$ctx): void
    {
        $p = $ctx['p'];
        $org = $ctx['org'];

        // SLA & SLT
        $slaGold = Sla::create(['organization_id' => $org->id, 'name' => 'SLA Gold – Layanan Kritis', 'description' => 'Untuk layanan berkritikalitas tinggi, 24x7']);
        $slaStd = Sla::create(['organization_id' => $org->id, 'name' => 'SLA Standar – Jam Kerja', 'description' => 'Untuk layanan umum, jam kerja 08.00–17.00']);
        $slts = [];
        foreach ([[$slaGold, 1.0], [$slaStd, 1.5]] as [$sla, $factor]) {
            foreach (Itil::DEFAULT_SLT as $prio => $t) {
                foreach (['incident', 'service_request'] as $rt) {
                    $mult = $rt === 'service_request' ? 2 : 1;
                    $label = $rt === 'incident' ? 'Insiden' : 'Permintaan';
                    $slts[] = ['sla_id' => $sla->id, 'name' => "P$prio $label TTO", 'priority' => $prio, 'request_type' => $rt, 'metric' => 'tto',
                        'value' => (int) max(15, round($t['tto'] * 60 * $factor * $mult)), 'unit' => 'minutes'];
                    $slts[] = ['sla_id' => $sla->id, 'name' => "P$prio $label TTR", 'priority' => $prio, 'request_type' => $rt, 'metric' => 'ttr',
                        'value' => (int) round($t['ttr'] * $factor * $mult), 'unit' => 'hours'];
                }
            }
        }
        DB::table('slts')->insert($slts);

        $services = [];
        foreach ($p['services'] as $key => [$name, $family, $valueStream, $teamKey, $subs]) {
            $svc = Service::create(['organization_id' => $org->id, 'service_family_id' => $this->families[$family] ?? null, 'name' => $name,
                'value_stream' => $valueStream, 'status' => 'production', 'description' => "Layanan $name yang dikelola tim ".$ctx['teams'][$teamKey]->name.'.']);
            foreach ($subs as [$subName, $rt]) {
                $svc->subcategories()->create(['name' => $subName, 'request_type' => $rt]);
            }
            $svc->load('subcategories');
            $services[$key] = ['model' => $svc, 'team' => $teamKey];
        }
        $ctx['services'] = $services;

        // Kontrak pelanggan internal (OLA) per entitas
        $gold = [];
        $std = [];
        foreach ($services as $s) {
            if (in_array($s['team'], ['euc', 'sd'], true)) {
                $std[] = $s['model']->id;
            } else {
                $gold[] = $s['model']->id;
            }
        }
        $customers = Organization::where('id', $org->id)->orWhere('parent_id', $org->id)->get();
        foreach ($customers as $customer) {
            foreach ([[$slaGold, $gold, 'Layanan Kritis'], [$slaStd, $std, 'Layanan Pengguna']] as [$sla, $svcIds, $label]) {
                if (! $svcIds) {
                    continue;
                }
                $c = Contract::create(['kind' => 'customer', 'organization_id' => $customer->id, 'provider_id' => $org->id, 'sla_id' => $sla->id,
                    'name' => "OLA $label – {$customer->code} ".$this->now->year, 'contract_type' => 'Internal OLA', 'start_date' => $this->now->copy()->startOfYear(),
                    'end_date' => $this->now->copy()->endOfYear(), 'status' => 'production', 'billing_frequency' => 'Tahunan']);
                $c->services()->attach($svcIds);
            }
        }

        // Aplikasi (ApplicationSolution) -> software -> VM -> host fisik
        $apps = [];
        foreach ($p['apps'] as [$appName, $svcKey, $teamKey, $crit, $vms]) {
            $app = $this->ci($ctx, ['class' => 'ApplicationSolution', 'name' => $appName, 'business_criticity' => $crit, 'team_id' => $ctx['teams'][$teamKey]->id,
                'location_id' => $ctx['dc']->id, 'move2production' => $this->now->copy()->subDays(mt_rand(200, 2000))->toDateString(),
                'description' => 'Solusi aplikasi untuk layanan '.$services[$svcKey]['model']->name]);
            $apps[$appName] = $app;
            DB::table('ci_service')->insert(['ci_id' => $app->id, 'service_id' => $services[$svcKey]['model']->id]);
            foreach ($vms as [$vmName, $os, $kind, $software]) {
                $vm = $this->ci($ctx, ['class' => 'VirtualMachine', 'name' => $vmName, 'location_id' => $ctx['dc']->id, 'business_criticity' => $crit,
                    'os_family' => str_contains($os, 'Windows') ? 'Windows' : 'Linux', 'os_version' => $os, 'cpu' => mt_rand(2, 16).' vCPU', 'ram' => $this->pick([8, 16, 32, 64, 128]).' GB',
                    'management_ip' => $this->nextIp($ctx), 'team_id' => $ctx['teams']['dc']->id]);
                $this->relate($vm, $this->pick($ctx['hosts']), 'runs_on');
                $class = ['db' => 'DBServer', 'web' => 'WebServer', 'mw' => 'Middleware'][$kind];
                $sw = $this->ci($ctx, ['class' => $class, 'name' => "$software @ $vmName", 'business_criticity' => $crit, 'location_id' => $ctx['dc']->id,
                    'os_version' => $software, 'team_id' => $ctx['teams'][$teamKey]->id]);
                $this->relate($sw, $vm, 'runs_on');
                $this->relate($app, $sw, 'depends_on');
            }
        }
        foreach ($p['processes'] as [$bpName, $appNames]) {
            $bp = $this->ci($ctx, ['class' => 'BusinessProcess', 'name' => $bpName, 'business_criticity' => 'high', 'description' => 'Proses bisnis yang ditopang solusi aplikasi.']);
            foreach ($appNames as $an) {
                if (isset($apps[$an])) {
                    $this->relate($bp, $apps[$an], 'depends_on');
                }
            }
        }
        // Layanan jaringan bergantung pada perangkat inti
        foreach ($services as $s) {
            if ($s['team'] === 'net') {
                foreach (array_slice($ctx['devices'], 0, 4) as $d) {
                    DB::table('ci_service')->insert(['ci_id' => $d->id, 'service_id' => $s['model']->id]);
                }
            }
        }

        // Kontrak vendor (provider contract) + CI terkait
        foreach ($p['vendors'] as $i => [$vendor, $name, $type, $cost]) {
            $start = $this->now->copy()->subMonths(mt_rand(2, 20))->startOfMonth();
            $c = Contract::create(['kind' => 'provider', 'organization_id' => $org->id, 'vendor' => $vendor, 'name' => $name, 'contract_type' => $type,
                'start_date' => $start, 'end_date' => $start->copy()->addYears(mt_rand(1, 3))->subDay(), 'cost' => $cost, 'billing_frequency' => $this->pick(['Bulanan', 'Tahunan']),
                'status' => 'production']);
            foreach (array_slice($ctx['devices'], $i * 2, 3) as $d) {
                DB::table('ci_contract')->insert(['contract_id' => $c->id, 'ci_id' => $d->id]);
            }
        }
    }

    // ------------------------------------------------------------------ tickets

    private function ref(string $type): string
    {
        $this->refCounter[$type]++;

        return Itil::TYPES[$type]['prefix'].'-'.str_pad((string) $this->refCounter[$type], 6, '0', STR_PAD_LEFT);
    }

    private function businessTime(Carbon $day): Carbon
    {
        $h = mt_rand(1, 10) <= 8 ? mt_rand(7, 18) : mt_rand(0, 23);

        return $day->copy()->setTime($h, mt_rand(0, 59));
    }

    private function fill(string $tpl, array $ctx): string
    {
        $site = $this->pick($ctx['sites']);

        return strtr($tpl, ['{site}' => $site->name, '{city}' => $site->city ?? 'Jakarta', '{ci}' => $this->pick($ctx['devices'])->name,
            '{v}' => mt_rand(4, 9).'.'.mt_rand(0, 12)]);
    }

    private function buildTickets(array &$ctx): void
    {
        $p = $ctx['p'];
        $org = $ctx['org'];
        $orgIds = Organization::where('id', $org->id)->orWhere('parent_id', $org->id)->pluck('id')->all();
        $logs = [];
        $ciLinks = [];
        $volume = ['BBRD' => 1.7, 'MEDC' => 1.4, 'UINSK' => 2.0][$org->code] ?? 1.5;
        $ctx['svcCis'] = [];
        foreach ($ctx['services'] as $key => $s) {
            $ctx['svcCis'][$key] = DB::table('ci_service')->where('service_id', $s['model']->id)->pluck('ci_id')->all();
        }

        $incidentsByService = [];
        for ($d = 365; $d >= 0; $d--) {
            $day = $this->now->copy()->subDays($d)->startOfDay();
            $base = $volume * ($day->isWeekend() ? 0.45 : 1.0) * (1 + (365 - $d) / 900); // tren naik (adopsi portal)
            if ($org->code === 'UINSK' && in_array($day->month, [2, 8, 9])) {
                $base *= 1.6; // puncak KRS & awal semester
            }
            $n = (int) floor($base + mt_rand(0, 100) / 100);
            for ($i = 0; $i < $n; $i++) {
                $created = $this->businessTime($day);
                if ($created->gt($this->now)) {
                    $created = $this->now->copy()->subMinutes(mt_rand(5, 300));
                }
                if (mt_rand(1, 100) <= 58) {
                    [$tpl, $svcKey, $impact, $urgency] = $this->pick($p['incidents']);
                    $impact = max(1, min(3, $impact + (mt_rand(1, 10) > 7 ? 1 : 0)));
                    $t = $this->makeTicket($ctx, 'incident', $this->fill($tpl, $ctx), $svcKey, $impact, $urgency, $created, $orgIds, $logs, $ciLinks);
                    $incidentsByService[$svcKey][] = $t->id;
                } else {
                    [$tpl, $svcKey] = $this->pick($p['requests']);
                    $this->makeTicket($ctx, 'request', $this->fill($tpl, $ctx), $svcKey, 3, $this->pick([2, 3, 3, 4]), $created, $orgIds, $logs, $ciLinks);
                }
            }
        }

        // Problems (dengan insiden terkait & known error)
        foreach ($p['problems'] as $i => [$title, $svcKey, $rootCause, $workaround]) {
            $created = $this->now->copy()->subDays(mt_rand(20, 300))->setTime(10, 0);
            $status = $i === 0 ? 'assigned' : ($i === 1 ? 'known_error' : 'closed');
            $prob = $this->makeTicket($ctx, 'problem', $title, $svcKey, 2, 2, $created, $orgIds, $logs, $ciLinks, $status);
            $prob->update(['root_cause' => $rootCause, 'workaround' => $workaround, 'tto_deadline' => null, 'ttr_deadline' => null, 'sla_tto_passed' => false, 'sla_ttr_passed' => false]);
            $related = array_slice($incidentsByService[$svcKey] ?? [], -mt_rand(3, 7));
            Ticket::whereIn('id', $related)->update(['parent_id' => $prob->id]);
            if ($status !== 'assigned') {
                KnownError::create(['organization_id' => $org->id, 'problem_id' => $prob->id, 'name' => 'KE: '.$title,
                    'domain' => $ctx['services'][$svcKey]['team'] === 'net' ? 'Network' : 'Application',
                    'symptom' => $title, 'error_code' => strtoupper(Str::random(2)).'-'.mt_rand(100, 999), 'root_cause' => $rootCause, 'workaround' => $workaround,
                    'solution' => $status === 'closed' ? 'Perbaikan permanen telah diterapkan melalui change terkait.' : null,
                    'vendor' => $this->pick(array_keys($this->brandCache))]);
            }
        }

        // Changes
        foreach (range(1, 34) as $i) {
            [$tpl, $ctype, $svcKey] = $this->pick($p['changes']);
            $offset = mt_rand(-330, 40);
            $planned = $this->now->copy()->startOfDay()->addDays($offset)->setTime($this->pick([20, 21, 22, 23, 1]), 0);
            $created = $planned->copy()->subDays($ctype === 'emergency' ? 0 : mt_rand(3, 14))->subHours(mt_rand(1, 8));
            if ($created->gt($this->now)) {
                $created = $this->now->copy()->subHours(mt_rand(2, 48));
            }
            if ($offset > 0) {
                $status = $ctype === 'standard' ? $this->pick(['new', 'planned']) : $this->pick(['new', 'validated', 'waiting_approval', 'approved', 'planned']);
            } else {
                $status = mt_rand(1, 12) === 1 ? $this->pick(['rejected', 'not_approved']) : ($offset > -5 ? $this->pick(['implemented', 'monitored']) : 'closed');
            }
            $t = $this->makeTicket($ctx, 'change', $this->fill($tpl, $ctx), $svcKey, $ctype === 'emergency' ? 1 : 2, $ctype === 'emergency' ? 1 : 3, $created, $orgIds, $logs, $ciLinks, $status);
            $t->update(['change_type' => $ctype, 'planned_start' => $planned, 'planned_end' => $planned->copy()->addHours(mt_rand(1, 4)),
                'risk' => $ctype === 'standard' ? 'low' : $this->pick(['low', 'medium', 'high']), 'outage' => mt_rand(1, 3) === 1,
                'closed_at' => $status === 'closed' ? $planned->copy()->addDays(mt_rand(1, 4)) : null,
                'fallback_plan' => 'Rollback ke konfigurasi/rilis sebelumnya dari backup; verifikasi layanan oleh tim terkait.']);
        }

        foreach (array_chunk($logs, 500) as $chunk) {
            DB::table('ticket_logs')->insert($chunk);
        }
        foreach (array_chunk($ciLinks, 500) as $chunk) {
            DB::table('ci_ticket')->insert($chunk);
        }
    }

    private function makeTicket(array $ctx, string $type, string $title, string $svcKey, int $impact, int $urgency, Carbon $created, array $orgIds, array &$logs, array &$ciLinks, ?string $forceStatus = null): Ticket
    {
        $svc = $ctx['services'][$svcKey]['model'];
        $teamKey = $ctx['services'][$svcKey]['team'];
        $team = $ctx['teams'][$teamKey];
        $agent = $this->pick($ctx['agents'][$teamKey]);
        $caller = $this->pick($ctx['callers']);
        $rt = $type === 'request' ? 'service_request' : 'incident';
        $sub = $svc->subcategories->firstWhere('request_type', $rt) ?? $svc->subcategories->first();
        $priority = Itil::priority($impact, $urgency);
        $slt = Itil::DEFAULT_SLT[$priority];
        $mult = $type === 'request' ? 2 : 1;
        $ttoH = $slt['tto'] * $mult;
        $ttrH = $slt['ttr'] * $mult;
        $ageH = $created->diffInHours($this->now);

        // Status ditentukan dari umur tiket: tiket lama sudah ditutup, tiket baru masih berjalan.
        if ($forceStatus) {
            $status = $forceStatus;
        } elseif ($ageH > $ttrH * 3 + 120 && mt_rand(1, 150) > 1) {
            $status = mt_rand(1, 30) === 1 ? 'resolved' : 'closed';
            if ($type === 'request' && mt_rand(1, 40) === 1) {
                $status = 'rejected';
            }
        } else {
            $status = $type === 'incident'
                ? $this->pick(['new', 'assigned', 'assigned', 'pending', 'resolved', 'escalated_ttr'])
                : $this->pick(['new', 'waiting_approval', 'approved', 'assigned', 'pending', 'resolved']);
        }

        $breach = mt_rand(1, 100) <= ($priority <= 2 ? 14 : 8);
        $assignedAt = in_array($status, ['new', 'waiting_approval', 'rejected']) ? null : $created->copy()->addMinutes((int) max(3, $ttoH * 60 * (mt_rand(10, $breach ? 160 : 90) / 100)));
        if ($assignedAt && $assignedAt->gt($this->now)) {
            $assignedAt = $this->now->copy();
        }
        $resolvedAt = null;
        if (in_array($status, ['resolved', 'closed']) && $type !== 'change') {
            $resolvedAt = $created->copy()->addMinutes((int) max(10, $ttrH * 60 * ($breach ? mt_rand(105, 220) : mt_rand(8, 95)) / 100));
            if ($resolvedAt->gt($this->now)) {
                $resolvedAt = $this->now->copy()->subMinutes(mt_rand(1, 30));
            }
        }
        $closedAt = $status === 'closed' ? ($resolvedAt ?? $created)->copy()->addHours(mt_rand(4, 72)) : null;
        if ($closedAt && $closedAt->gt($this->now)) {
            $closedAt = $this->now->copy();
        }
        $isSla = in_array($type, ['incident', 'request'], true);
        $ttrDeadline = $created->copy()->addMinutes((int) ($ttrH * 60));
        $tto = $created->copy()->addMinutes((int) ($ttoH * 60));
        $ttoPassed = $isSla && (($assignedAt && $assignedAt->gt($tto)) || (! $assignedAt && $status !== 'rejected' && $this->now->gt($tto)));
        $ttrPassed = $isSla && (($resolvedAt && $resolvedAt->gt($ttrDeadline)) || (! $resolvedAt && ! in_array($status, ['rejected', 'closed']) && $this->now->gt($ttrDeadline)));

        $t = Ticket::create([
            'ref' => $this->ref($type), 'type' => $type, 'organization_id' => $this->pick($orgIds), 'caller_id' => $caller->id,
            'team_id' => $team->id, 'agent_id' => $assignedAt ? $agent->id : null, 'service_id' => $svc->id, 'service_subcategory_id' => $sub?->id,
            'title' => $title, 'description' => $this->description($type, $title, $caller),
            'status' => $status, 'impact' => $impact, 'urgency' => $urgency, 'priority' => $priority,
            'origin' => $this->pick(['portal', 'portal', 'portal', 'email', 'phone', 'monitoring', 'chat']),
            'tto_deadline' => $isSla ? $tto : null, 'ttr_deadline' => $isSla ? $ttrDeadline : null,
            'assigned_at' => $assignedAt, 'resolved_at' => $resolvedAt, 'closed_at' => $closedAt,
            'sla_tto_passed' => $ttoPassed, 'sla_ttr_passed' => $ttrPassed,
            'resolution_code' => $resolvedAt ? $this->pick(['fixed', 'workaround', 'config_change', 'hardware_replaced', 'user_training', 'no_fault_found']) : null,
            'solution' => $resolvedAt ? $this->pick(['Konfigurasi diperbaiki dan layanan diverifikasi normal.', 'Restart service & clear cache, pengguna konfirmasi normal.',
                'Perangkat diganti unit cadangan.', 'Hak akses diberikan sesuai persetujuan atasan.', 'Diberikan panduan penggunaan kepada pengguna.', 'Patch diterapkan, monitoring 24 jam normal.']) : null,
            'satisfaction' => $status === 'closed' && mt_rand(1, 3) > 1 ? $this->pick([3, 4, 4, 5, 5, 5]) : null,
            'created_at' => $created, 'updated_at' => $closedAt ?? $resolvedAt ?? $assignedAt ?? $created,
        ]);

        // CI terdampak: aplikasi layanan (computed) + kadang perangkat jaringan (manual)
        $svcCis = $ctx['svcCis'][$svcKey] ?? [];
        $ciLinks[] = ['ticket_id' => $t->id, 'ci_id' => $svcCis ? $this->pick($svcCis) : $this->pick($ctx['devices'])->id, 'impact_code' => 'computed'];
        if (mt_rand(1, 3) === 1) {
            $ciLinks[] = ['ticket_id' => $t->id, 'ci_id' => $this->pick($ctx['devices'])->id, 'impact_code' => 'manual'];
        }

        // Log aktivitas
        $logs[] = ['ticket_id' => $t->id, 'author' => 'Sistem', 'kind' => 'system', 'message' => 'Tiket dibuat melalui '.$t->origin.' oleh '.$caller->name.'.', 'created_at' => $created];
        if ($type === 'request' && $assignedAt) {
            $logs[] = ['ticket_id' => $t->id, 'author' => 'Atasan Pemohon', 'kind' => 'system', 'message' => 'Permintaan disetujui oleh atasan.', 'created_at' => $created->copy()->addMinutes(20)];
        }
        if ($assignedAt) {
            $logs[] = ['ticket_id' => $t->id, 'author' => 'Sistem', 'kind' => 'system', 'message' => "Ditugaskan ke tim {$team->name} / {$agent->name}.", 'created_at' => $assignedAt];
            $logs[] = ['ticket_id' => $t->id, 'author' => $agent->name, 'kind' => 'public', 'message' => $this->pick([
                'Halo, tiket sudah kami terima dan sedang dianalisa.', 'Mohon informasikan sejak kapan kendala ini terjadi?', 'Kami sedang melakukan pengecekan remote ke perangkat terkait.',
                'Sudah kami eskalasi ke vendor, menunggu respon.', 'Bisa dikirimkan screenshot error yang muncul?']), 'created_at' => $assignedAt->copy()->addMinutes(mt_rand(5, 90))];
            if (mt_rand(1, 2) === 1) {
                $logs[] = ['ticket_id' => $t->id, 'author' => $agent->name, 'kind' => 'private', 'message' => $this->pick([
                    'Cek log menunjukkan timeout ke database.', 'Kemungkinan terkait change minggu lalu, cek CAB record.', 'Interface uplink sempat flapping 3x.',
                    'User sudah pernah lapor hal serupa, cek known error.']), 'created_at' => $assignedAt->copy()->addMinutes(mt_rand(30, 180))];
            }
        }
        if ($resolvedAt) {
            $logs[] = ['ticket_id' => $t->id, 'author' => $agent->name, 'kind' => 'public', 'message' => 'Solusi: '.$t->solution, 'created_at' => $resolvedAt];
        }
        if ($closedAt) {
            $logs[] = ['ticket_id' => $t->id, 'author' => 'Sistem', 'kind' => 'system', 'message' => 'Tiket ditutup'.($t->satisfaction ? " – kepuasan pengguna {$t->satisfaction}/5." : '.'), 'created_at' => $closedAt];
        }

        return $t;
    }

    private function description(string $type, string $title, Person $caller): string
    {
        return match ($type) {
            'incident' => "Pelapor: {$caller->name} ({$caller->function}).\n\nKendala: {$title}.\nDampak: pengguna tidak dapat bekerja normal. Sudah dicoba restart/refresh namun belum berhasil.",
            'request' => "Pemohon: {$caller->name} ({$caller->function}).\n\nMohon diproses: {$title}. Justifikasi: kebutuhan operasional unit kerja.",
            'problem' => "Problem dibuka dari analisis tren insiden berulang: {$title}. Dilakukan Root Cause Analysis (5 Whys / Ishikawa).",
            default => "Rencana perubahan: {$title}.\nTujuan: peningkatan stabilitas/keamanan/fitur. Rencana uji: smoke test layanan terdampak setelah implementasi.",
        };
    }

    private function buildEvents(array $ctx): void
    {
        $rows = [];
        $msgs = [
            'critical' => ['Interface {if} down', 'Device unreachable (ICMP loss 100%)', 'BGP neighbor down', 'HA failover terjadi'],
            'major' => ['CPU utilization > 90% selama 15 menit', 'Memory utilization > 92%', 'Disk /var > 95%', 'Packet loss 12% pada uplink'],
            'minor' => ['Latency > 150 ms', 'Suhu perangkat 68°C', 'Power supply redundancy lost', 'Sertifikat SSL kedaluwarsa dalam 14 hari'],
            'warning' => ['Utilisasi bandwidth > 80%', 'Login gagal berulang (5x)', 'NTP out of sync', 'Backup job selesai dengan peringatan'],
            'info' => ['Konfigurasi tersimpan', 'Interface kembali up', 'Firmware check: update tersedia', 'Backup job sukses'],
        ];
        $orgIds = Organization::where('id', $ctx['org']->id)->orWhere('parent_id', $ctx['org']->id)->pluck('id');
        $monIncidents = Ticket::whereIn('organization_id', $orgIds)->where('type', 'incident')->where('origin', 'monitoring')
            ->where('created_at', '>=', $this->now->copy()->subDays(30))->with('cis')->get();
        foreach ($monIncidents as $inc) {
            $rows[] = ['organization_id' => $ctx['org']->id, 'ci_id' => $inc->cis->first()?->id, 'ticket_id' => $inc->id, 'source' => $this->pick($ctx['p']['monitor']),
                'severity' => $inc->priority <= 2 ? 'critical' : 'major', 'message' => 'Alert → '.$inc->title, 'status' => $inc->is_open ? 'acknowledged' : 'closed',
                'occurred_at' => $inc->created_at->copy()->subMinutes(2)];
        }
        $candidates = Ci::where('organization_id', $ctx['org']->id)->whereIn('class', ['NetworkDevice', 'Server', 'StorageSystem', 'VirtualMachine', 'PowerSource'])->pluck('id')->all();
        for ($i = 0; $i < 220; $i++) {
            $sev = $this->pick(['info', 'info', 'warning', 'warning', 'warning', 'minor', 'minor', 'major', 'critical']);
            $at = $this->now->copy()->subMinutes(mt_rand(1, 60 * 24 * 21));
            $rows[] = ['organization_id' => $ctx['org']->id, 'ci_id' => $this->pick($candidates), 'ticket_id' => null, 'source' => $this->pick($ctx['p']['monitor']), 'severity' => $sev,
                'message' => str_replace('{if}', $this->pick(['Gi1/0/24', 'Te1/1/1', 'port3', 'WAN1', 'XGE0/0/2']), $this->pick($msgs[$sev])),
                'status' => $at->gt($this->now->copy()->subHours(8)) ? $this->pick(['open', 'open', 'acknowledged']) : 'closed', 'occurred_at' => $at];
        }
        DB::table('events')->insert($rows);
    }

    private function buildImprovements(array $ctx, Person $owner): void
    {
        foreach ($ctx['p']['improvements'] as $i => [$title, $practice, $principle, $benefit]) {
            $status = ['done', 'in_progress', 'in_progress', 'approved', 'proposed'][$i % 5];
            Improvement::create(['organization_id' => $ctx['org']->id, 'owner_id' => $this->pick(array_merge([$owner], $ctx['agents']['sd']))->id,
                'title' => $title, 'practice' => $practice, 'guiding_principle' => $principle, 'benefit' => $benefit, 'status' => $status,
                'progress' => ['done' => 100, 'in_progress' => mt_rand(25, 80), 'approved' => 10, 'proposed' => 0][$status],
                'description' => "Inisiatif perbaikan berkelanjutan: $title. Dijalankan dengan model Continual Improvement ITIL 4: visi → kondisi saat ini → target → rencana → aksi → evaluasi → menjaga momentum.",
                'target_date' => $this->now->copy()->addDays(mt_rand(-60, 180))]);
        }
    }

    private function buildDocuments(array $ctx): void
    {
        $org = $ctx['org'];
        foreach ([
            ['Kebijakan Manajemen Layanan TI', 'note', 'Kebijakan ini menetapkan penerapan praktik ITIL 4 untuk seluruh layanan TI '.$org->name.'.'],
            ['Prosedur Change Enablement & CAB', 'note', 'Standard change disetujui otomatis; normal change melalui CAB mingguan; emergency change melalui ECAB.'],
            ['Diagram Topologi Jaringan', 'web', null],
            ['Runbook Failover DRC', 'note', '1) Deklarasikan insiden mayor 2) Aktifkan replikasi 3) Alihkan DNS 4) Verifikasi layanan kritis 5) Komunikasikan ke pemangku kepentingan.'],
        ] as [$name, $kind, $content]) {
            Document::create(['organization_id' => $org->id, 'name' => $name, 'kind' => $kind, 'content' => $content,
                'url' => $kind === 'web' ? '/topology?org='.$org->id : null, 'status' => 'published']);
        }
    }

    private function seedFaq(): void
    {
        $cats = [];
        foreach (['Akun & Password', 'Jaringan & Wi-Fi', 'Aplikasi', 'Perangkat', 'Keamanan'] as $c) {
            $cats[$c] = FaqCategory::create(['name' => $c])->id;
        }
        $orgs = Organization::whereNull('parent_id')->pluck('id', 'code');
        $faqs = [
            ['Akun & Password', null, 'Bagaimana cara reset password akun kantor?', 'password, reset, akun', 'Gunakan portal self-service atau ajukan permintaan layanan.', 'Buka portal layanan > Permintaan Layanan > Reset Password. Verifikasi MFA, password baru dikirim ke email/SMS terdaftar.'],
            ['Akun & Password', null, 'Cara mengaktifkan MFA', 'mfa, otp, authenticator', 'Pasang aplikasi authenticator dan pindai QR saat login.', 'Install aplikasi authenticator, login ke portal akun, pilih Tambah metode > Aplikasi, pindai QR.'],
            ['Jaringan & Wi-Fi', $orgs['UINSK'] ?? null, 'Cara terhubung ke Eduroam UINSK', 'eduroam, wifi, kampus', 'Gunakan akun kampus lengkap dengan domain sebagai username.', 'Pilih SSID eduroam, metode PEAP/MSCHAPv2, username: akun@domain kampus, password akun SUKAmail.'],
            ['Jaringan & Wi-Fi', $orgs['UINSK'] ?? null, 'Wi-Fi SUKAnet terhubung tapi tidak bisa internet', 'sukanet, captive portal', 'Login ulang captive portal dan lupakan jaringan.', 'Forget network, sambungkan ulang, buka browser untuk captive portal. Bila tetap gagal laporkan via SIGAP dengan lokasi gedung.'],
            ['Aplikasi', $orgs['BBRD'] ?? null, 'Order MyBluebird gagal / timeout', 'mybluebird, order', 'Periksa versi aplikasi dan koneksi data.', 'Perbarui aplikasi ke versi terbaru, bersihkan cache, pastikan lokasi aktif. Bila berulang, lampirkan ID order saat melapor.'],
            ['Aplikasi', $orgs['MEDC'] ?? null, 'Cara menggunakan mSeek', 'mseek, ai, sdm', 'mSeek adalah agen AI untuk pencarian informasi perusahaan.', 'Buka mSeek dari portal intranet, ajukan pertanyaan dalam bahasa alami. Tahap awal fokus pada topik SDM.'],
            ['Aplikasi', $orgs['UINSK'] ?? null, 'Gagal input KRS di SUKAdemia', 'krs, sukademia', 'Pastikan pembayaran UKT terverifikasi dan jadwal KRS aktif.', 'Cek status UKT, gunakan browser terbaru, coba di luar jam puncak. Bila error 500 berulang, laporkan via SIGAP.'],
            ['Perangkat', null, 'Laptop lambat setelah update', 'laptop, lambat, update', 'Restart dan biarkan update selesai.', 'Restart 2x, pastikan update Windows tuntas, jalankan Disk Cleanup. Bila masih lambat ajukan tiket EUC.'],
            ['Perangkat', null, 'Printer tidak terdeteksi', 'printer, driver', 'Pastikan terhubung ke jaringan yang sama dan driver terpasang.', 'Cek kabel/Wi-Fi printer, tambahkan via IP printer, instal driver dari portal software.'],
            ['Keamanan', null, 'Menerima email mencurigakan (phishing)', 'phishing, email, keamanan', 'Jangan klik tautan; gunakan tombol Report Phishing.', 'Gunakan tombol Report Phishing di Outlook atau teruskan ke tim keamanan, lalu hapus email. Jika terlanjur klik, segera reset password.'],
            ['Keamanan', null, 'Cara meminta akses VPN', 'vpn, remote', 'Ajukan permintaan layanan dengan persetujuan atasan.', 'Portal layanan > Keamanan Informasi > Permintaan akses VPN. Akses aktif setelah disetujui atasan dan tim keamanan.'],
        ];
        foreach ($faqs as [$cat, $orgId, $title, $kw, $summary, $desc]) {
            Faq::create(['faq_category_id' => $cats[$cat], 'organization_id' => $orgId, 'title' => $title, 'keywords' => $kw, 'summary' => $summary, 'description' => $desc, 'views' => mt_rand(40, 2400)]);
        }
    }
}
