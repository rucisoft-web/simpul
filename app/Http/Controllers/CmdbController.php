<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Ci;
use App\Models\Location;
use App\Models\Organization;
use App\Support\Scope;
use Illuminate\Http\Request;

class CmdbController extends Controller
{
    public function index(Request $request)
    {
        $q = Scope::apply(Ci::query(), 'organization_id', false)->with(['organization', 'location', 'brand', 'deviceModel']);
        foreach (['class', 'status', 'brand_id', 'location_id', 'network_role', 'business_criticity'] as $f) {
            if ($v = $request->query($f)) {
                $q->where($f, $v);
            }
        }
        if ($term = $request->query('q')) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$term%")->orWhere('serial_number', 'like', "%$term%")->orWhere('management_ip', 'like', "%$term%"));
        }
        $cis = $q->orderBy('class')->orderBy('name')->paginate(30)->withQueryString();
        $classCounts = Scope::apply(Ci::query(), 'organization_id', false)->selectRaw('class, COUNT(*) n')->groupBy('class')->pluck('n', 'class');
        $brands = Brand::orderBy('name')->get();
        $locations = Scope::apply(Location::query(), 'organization_id', false)->orderBy('name')->get();

        return view('cmdb.index', compact('cis', 'classCounts', 'brands', 'locations'));
    }

    public function show(Ci $ci)
    {
        $ci->load(['organization', 'location.parent', 'brand', 'deviceModel', 'team', 'parent', 'children.brand', 'rack', 'rackedDevices', 'interfaces',
            'contacts', 'services', 'contracts', 'dependsOn', 'dependents', 'events' => fn ($q) => $q->latest('occurred_at')->limit(10)]);
        $tickets = $ci->tickets()->with('organization')->latest()->limit(15)->get();
        $impact = $ci->impactTree();
        $path = [];
        for ($p = $ci->parent; $p; $p = $p->parent) {
            array_unshift($path, $p);
        }

        return view('cmdb.show', compact('ci', 'tickets', 'impact', 'path'));
    }

    public function topology(Request $request)
    {
        $orgs = Organization::whereNull('parent_id')->orderBy('name')->get();
        $org = $orgs->firstWhere('id', (int) $request->query('org', Scope::rootId())) ?? $orgs->firstWhere('code', 'BBRD') ?? $orgs->first();
        if (! $org) {
            return redirect()->route('dashboard')->with('status', 'Belum ada organisasi. Muat data demo dulu: php artisan demo:reset');
        }
        $roleCounts = Ci::where('organization_id', $org->id)->whereNotNull('network_role')->selectRaw('network_role, COUNT(*) n')->groupBy('network_role')->pluck('n', 'network_role');

        return view('cmdb.topology', compact('orgs', 'org', 'roleCounts'));
    }

    /** JSON pohon topologi: jaringan fisik (parent/uplink) atau hierarki lokasi. */
    public function topologyData(Request $request)
    {
        $org = Organization::findOrFail($request->query('org'));
        $endpoints = $request->boolean('endpoints');

        if ($request->query('mode') === 'location') {
            $locs = Location::where('organization_id', $org->id)->withCount('cis')->get();
            $build = function ($parentId) use (&$build, $locs) {
                return $locs->where('parent_id', $parentId)->values()->map(fn ($l) => [
                    'name' => $l->name, 'role' => $l->type, 'sub' => ($l->city ?? '').' · '.$l->cis_count.' CI', 'url' => route('cis.index', ['location_id' => $l->id]),
                    'children' => $build($l->id),
                ])->all();
            };

            return response()->json(['name' => $org->name, 'role' => 'org', 'sub' => $org->industry, 'children' => $build(null)]);
        }

        $q = Ci::where('organization_id', $org->id)->whereNotNull('network_role')->with(['brand', 'deviceModel', 'location']);
        if (! $endpoints) {
            $q->where('network_role', '!=', 'endpoint');
        }
        $cis = $q->get();
        $byParent = $cis->groupBy(fn ($c) => $c->parent_id ?? 0);
        $build = function ($parentId) use (&$build, $byParent) {
            return ($byParent[$parentId] ?? collect())->sortBy('name')->values()->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->name, 'role' => $c->network_role, 'class' => $c->class,
                'brand' => $c->brand?->name, 'color' => $c->brand?->color, 'model' => $c->deviceModel?->name, 'ip' => $c->management_ip,
                'sub' => trim(($c->brand?->name ?? '').' '.($c->deviceModel?->name ?? '')), 'loc' => $c->location?->name, 'status' => $c->status,
                'url' => route('cis.show', $c->id), 'children' => $build($c->id),
            ])->all();
        };

        return response()->json(['name' => $org->name, 'role' => 'org', 'sub' => 'Internet / ISP', 'children' => $build(0)]);
    }

    public function locations()
    {
        $orgs = Scope::rootId() ? Organization::where('id', Scope::rootId())->get() : Organization::whereNull('parent_id')->orderBy('name')->get();
        $locations = Location::whereIn('organization_id', $orgs->pluck('id'))->withCount(['cis', 'persons'])->get();

        return view('cmdb.locations', compact('orgs', 'locations'));
    }

    public function brands()
    {
        $orgIds = Scope::rootId() ? [Scope::rootId()] : null;
        $brands = Brand::with(['models' => fn ($q) => $q->withCount(['cis' => fn ($c) => $orgIds ? $c->whereIn('organization_id', $orgIds) : $c])])
            ->withCount(['cis' => fn ($c) => $orgIds ? $c->whereIn('organization_id', $orgIds) : $c])->orderByDesc('cis_count')->get();
        $matrix = Ci::query()->when($orgIds, fn ($q) => $q->whereIn('organization_id', $orgIds))->whereNotNull('brand_id')
            ->join('organizations', 'organizations.id', '=', 'cis.organization_id')
            ->selectRaw('brand_id, organizations.code, COUNT(*) n')->groupBy('brand_id', 'organizations.code')->get()->groupBy('brand_id');
        $orgCodes = Organization::whereNull('parent_id')->when($orgIds, fn ($q) => $q->whereIn('id', $orgIds))->pluck('code');

        return view('cmdb.brands', compact('brands', 'matrix', 'orgCodes'));
    }
}
