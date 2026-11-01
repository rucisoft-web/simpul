<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_route_mapped_to_help_has_a_guide_topic(): void
    {
        foreach (Guide::ROUTES as $route => $key) {
            $this->assertNotNull(Guide::get($key), "Topik bantuan '{$key}' untuk route '{$route}' tidak ada.");
        }
        foreach (Guide::groups() as $keys) {
            foreach ($keys as $key) {
                $this->assertNotNull(Guide::get($key), "Topik '{$key}' di daftar isi tidak ada.");
            }
        }
    }

    public function test_help_panels_render(): void
    {
        foreach (array_keys(Guide::all()) as $key) {
            $this->get(route('guide.panel', $key))->assertOk();
        }
        $this->get(route('guide.index'))->assertOk();
        $this->get(route('login'))->assertOk()->assertSee('Bantuan');
    }

    public function test_create_admin_command_creates_and_updates_admin(): void
    {
        $this->artisan('simpul:create-admin', ['--name' => 'Admin', '--email' => 'admin@contoh.id', '--password' => 'SandiKuat2026'])->assertSuccessful();
        $user = User::where('email', 'admin@contoh.id')->firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertTrue(Hash::check('SandiKuat2026', $user->password));

        $this->artisan('simpul:create-admin', ['--name' => 'Admin', '--email' => 'admin@contoh.id', '--password' => 'SandiBaru2026'])->assertSuccessful();
        $this->assertTrue(Hash::check('SandiBaru2026', $user->fresh()->password));

        $this->artisan('simpul:create-admin', ['--name' => 'X', '--email' => 'x@contoh.id', '--password' => 'pendek'])->assertFailed();
    }

    public function test_screens_without_demo_data_do_not_crash(): void
    {
        $admin = User::create(['name' => 'A', 'email' => 'a@contoh.id', 'password' => 'SandiKuat2026', 'role' => 'admin']);
        $this->actingAs($admin);
        $this->get(route('tickets.create'))->assertRedirect(route('dashboard'));
        $this->get(route('topology.index'))->assertRedirect(route('dashboard'));
    }
}
