<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Person;
use App\Models\Team;
use App\Support\Scope;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $teams = Scope::apply(Team::query())->with(['organization', 'members'])->withCount(['tickets as open_tickets' => fn ($q) => $q->open()])->get();
        $persons = Scope::apply(Person::query())->with(['organization', 'location', 'teams'])
            ->when($request->query('q'), fn ($q, $t) => $q->where(fn ($w) => $w->where('first_name', 'like', "%$t%")->orWhere('last_name', 'like', "%$t%")->orWhere('function', 'like', "%$t%")))
            ->orderBy('first_name')->paginate(24)->withQueryString();

        return view('contacts.index', compact('teams', 'persons'));
    }

    public function organizations()
    {
        $orgs = Organization::whereNull('parent_id')->when(Scope::rootId(), fn ($q) => $q->whereKey(Scope::rootId()))->with(['children' => fn ($q) => $q->withCount(['persons', 'tickets'])])
            ->withCount(['locations', 'persons', 'teams', 'cis', 'services', 'tickets'])->get();

        return view('contacts.organizations', compact('orgs'));
    }
}
