<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->owner = User::factory()->create(['role' => 'superadmin', 'status' => 'active']);
    }

    public function test_admin_can_access_customers(): void
    {
        $this->actingAs($this->admin);
        $this->get('/customers')->assertStatus(200);
    }

    public function test_admin_can_access_services(): void
    {
        $this->actingAs($this->admin);
        $this->get('/services')->assertStatus(200);
    }

    public function test_admin_can_access_biaya_harian(): void
    {
        $this->actingAs($this->admin);
        $this->get('/biaya-harian')->assertStatus(200);
    }

    public function test_admin_can_access_data(): void
    {
        $this->actingAs($this->admin);
        $this->get('/data')->assertStatus(200);
    }

    public function test_admin_can_access_settings(): void
    {
        $this->actingAs($this->admin);
        $this->get('/settings')->assertStatus(200);
    }

    public function test_owner_can_access_admin_routes(): void
    {
        $this->actingAs($this->owner);
        $this->get('/customers')->assertStatus(200);
        $this->get('/services')->assertStatus(200);
        $this->get('/biaya-harian')->assertStatus(200);
        $this->get('/data')->assertStatus(200);
        $this->get('/settings')->assertStatus(200);
    }

    public function test_admin_cannot_access_superadmin_only_routes(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin')->assertStatus(403);
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->get('/customers')->assertRedirect('/login');
        $this->get('/services')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/biaya-harian')->assertRedirect('/login');
    }
}
