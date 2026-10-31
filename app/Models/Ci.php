<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FunctionalCI (iTop). Semua subclass disimpan dalam satu tabel dengan kolom `class`.
 */
class Ci extends Model
{
    protected $table = 'cis';
    protected $guarded = [];
    protected $casts = [
        'extra' => 'array',
        'purchase_date' => 'date',
        'warranty_end' => 'date',
        'move2production' => 'date',
    ];

    /** Hierarki kelas CI mengikuti data model iTop. */
    public const CLASSES = [
        'PhysicalDevice' => [
            'NetworkDevice' => 'Perangkat Jaringan',
            'Server' => 'Server',
            'StorageSystem' => 'Storage System',
            'Rack' => 'Rack',
            'Enclosure' => 'Enclosure',
            'PowerSource' => 'Power Source / UPS',
            'PC' => 'PC / Laptop',
            'Printer' => 'Printer',
            'Phone' => 'IP Phone',
            'Peripheral' => 'Periferal / CCTV',
        ],
        'Virtualization' => [
            'Hypervisor' => 'Hypervisor',
            'VirtualMachine' => 'Virtual Machine',
        ],
        'SoftwareInstance' => [
            'DBServer' => 'Database Server',
            'WebServer' => 'Web Server',
            'Middleware' => 'Middleware',
        ],
        'Application' => [
            'WebApplication' => 'Web Application',
            'ApplicationSolution' => 'Application Solution',
            'BusinessProcess' => 'Business Process',
        ],
    ];

    public const ICONS = [
        'NetworkDevice' => 'router', 'Server' => 'server', 'StorageSystem' => 'database', 'Rack' => 'rack',
        'Enclosure' => 'rack', 'PowerSource' => 'bolt', 'PC' => 'monitor', 'Printer' => 'printer', 'Phone' => 'phone',
        'Peripheral' => 'camera', 'Hypervisor' => 'layers', 'VirtualMachine' => 'box', 'DBServer' => 'database',
        'WebServer' => 'globe', 'Middleware' => 'cog', 'WebApplication' => 'globe', 'ApplicationSolution' => 'grid',
        'BusinessProcess' => 'flow',
    ];

    public static function classLabel(string $class): string
    {
        foreach (self::CLASSES as $group) {
            if (isset($group[$class])) {
                return $group[$class];
            }
        }

        return $class;
    }

    public function organization() { return $this->belongsTo(Organization::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function deviceModel() { return $this->belongsTo(DeviceModel::class, 'model_id'); }
    public function team() { return $this->belongsTo(Team::class); }
    public function parent() { return $this->belongsTo(Ci::class, 'parent_id'); }
    public function children() { return $this->hasMany(Ci::class, 'parent_id'); }
    public function rack() { return $this->belongsTo(Ci::class, 'rack_id'); }
    public function rackedDevices() { return $this->hasMany(Ci::class, 'rack_id'); }
    public function interfaces() { return $this->hasMany(PhysicalInterface::class); }
    public function contacts() { return $this->belongsToMany(Person::class)->withPivot('role'); }
    public function services() { return $this->belongsToMany(Service::class); }
    public function contracts() { return $this->belongsToMany(Contract::class); }
    public function tickets() { return $this->belongsToMany(Ticket::class)->withPivot('impact_code'); }
    public function events() { return $this->hasMany(Event::class); }

    /** CI yang menjadi dependensi CI ini (upstream: CI ini berjalan di atas / bergantung pada). */
    public function dependsOn()
    {
        return $this->belongsToMany(Ci::class, 'ci_relations', 'source_id', 'target_id')->withPivot('type');
    }

    /** CI yang bergantung pada CI ini (downstream: terdampak bila CI ini down). */
    public function dependents()
    {
        return $this->belongsToMany(Ci::class, 'ci_relations', 'target_id', 'source_id')->withPivot('type');
    }

    /**
     * Analisis dampak: kumpulkan seluruh CI yang terdampak (rekursif) bila CI ini gagal,
     * baik melalui relasi dependensi maupun anak topologi jaringan.
     */
    public function impactTree(int $depth = 0, array &$seen = []): array
    {
        $seen[$this->id] = true;
        $nodes = [];
        if ($depth > 5) {
            return $nodes;
        }
        $children = $this->dependents()->get()->merge($this->children()->get());
        foreach ($children as $child) {
            if (isset($seen[$child->id])) {
                continue;
            }
            $nodes[] = ['ci' => $child, 'children' => $child->impactTree($depth + 1, $seen)];
        }

        return $nodes;
    }

    public function getIconAttribute(): string
    {
        return self::ICONS[$this->class] ?? 'box';
    }
}
