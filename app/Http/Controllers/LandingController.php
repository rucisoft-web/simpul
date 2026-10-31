<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Ci;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Ticket;

class LandingController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'cis' => Ci::count(),
            'tickets' => Ticket::count(),
            'brands' => Brand::has('cis')->count(),
            'locations' => Location::count(),
            'sla' => round(Ticket::whereNotNull('resolved_at')->where('sla_ttr_passed', false)->count() / max(1, Ticket::whereNotNull('resolved_at')->count()) * 100, 1),
        ];
        $orgs = Organization::whereNull('parent_id')->withCount(['cis', 'locations', 'services'])->get();
        $brands = Brand::has('cis')->orderBy('name')->get();

        return view('landing', compact('stats', 'orgs', 'brands'));
    }
}
