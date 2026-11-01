<?php

namespace Tests\Feature;

use App\Models\Ci;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestDemoTest extends TestCase
{
    use RefreshDatabase;

    private Organization $a;

    private Organization $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->a = Organization::create(['code' => 'AAA', 'name' => 'PT Alfa', 'color' => '#111111']);
        $this->b = Organization::create(['code' => 'BBB', 'name' => 'PT Beta', 'color' => '#222222']);
    }

    private function ticket(Organization $org, string $ref): Ticket
    {
        return Ticket::create(['ref' => $ref, 'type' => 'incident', 'organization_id' => $org->id, 'title' => "Tiket $ref",
            'impact' => 2, 'urgency' => 2, 'priority' => 2, 'status' => 'new', 'origin' => 'portal']);
    }

    public function test_landing_has_no_package_tiers_and_offers_company_picker(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('Starter')->assertDontSee('Professional')
            ->assertSee('Simpul Enterprise')->assertSee('PT Alfa')->assertSee('PT Beta')
            ->assertSee('name="org"', false);
    }

    public function test_guest_must_choose_a_company(): void
    {
        $this->post(route('guest.login'))->assertRedirect(route('landing').'#demo');
        $this->assertGuest();
    }

    public function test_guest_only_sees_chosen_company(): void
    {
        $this->ticket($this->a, 'I-000001');
        $this->ticket($this->b, 'I-000002');

        $this->post(route('guest.login'), ['org' => 'AAA'])->assertRedirect(route('dashboard'));
        $this->assertSame($this->a->id, session('org_id'));

        // (Daftar tiket memakai fungsi MySQL FIELD(); tes memakai SQLite, jadi diuji lewat detail tiket.)
        $this->get('/tickets/I-000001')->assertOk();
        $this->get('/tickets/I-000002')->assertNotFound();
        $this->post('/tickets/I-000002/comment', ['message' => 'x', 'kind' => 'public'])->assertNotFound();
        $this->post(route('tickets.store'), ['type' => 'incident', 'organization_id' => $this->b->id, 'title' => 'x', 'impact' => 2, 'urgency' => 2])->assertNotFound();
        $this->get(route('topology.data', ['org' => $this->b->id]))->assertNotFound();

        // "Semua Organisasi" tidak tersedia untuk tamu
        $this->get(route('scope', ['org' => 'all']));
        $this->assertSame($this->a->id, session('org_id'));
    }

    public function test_guest_can_switch_company(): void
    {
        $this->ticket($this->b, 'I-000002');
        $this->post(route('guest.login'), ['org' => 'AAA']);
        $this->get(route('guest.link', ['org' => 'BBB']))->assertRedirect(route('dashboard'));
        $this->assertSame($this->b->id, session('org_id'));
        $this->get('/tickets/I-000002')->assertOk();
    }

    public function test_guest_without_company_in_session_is_sent_to_picker(): void
    {
        $guest = User::create(['name' => 'Tamu', 'email' => 'tamu@simpul.test', 'password' => 'x', 'role' => 'guest']);
        $this->actingAs($guest)->get(route('dashboard'))->assertRedirect(route('landing').'#demo');
    }

    public function test_admin_can_still_see_all_organizations(): void
    {
        $this->ticket($this->a, 'I-000001');
        $this->ticket($this->b, 'I-000002');
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@contoh.id', 'password' => 'SandiKuat2026', 'role' => 'admin']);
        $this->actingAs($admin)->get('/tickets/I-000001')->assertOk();
        $this->get('/tickets/I-000002')->assertOk();
    }
}
