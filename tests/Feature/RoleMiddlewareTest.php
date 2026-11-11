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

    public function test_admin_can_access_user_management(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin')->assertStatus(200);
    }

    public function test_admin_sees_users_without_management_actions(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($this->admin);

        $this->get('/admin')
            ->assertStatus(200)
            ->assertSee($this->owner->name)
            ->assertSee($otherAdmin->name)
            ->assertSee('Anda hanya dapat mengelola akun Anda sendiri.')
            ->assertSee('data-edit-user="' . $this->admin->id . '"', false)
            ->assertDontSee('data-edit-user="' . $otherAdmin->id . '"', false)
            ->assertDontSee('data-edit-user="' . $this->owner->id . '"', false)
            ->assertDontSee(route('admin.destroy', $this->owner), false)
            ->assertDontSee(route('admin.destroy', $otherAdmin), false)
            ->assertDontSee(route('admin.destroy', $this->admin), false);
    }

    public function test_admin_can_update_own_account(): void
    {
        $this->actingAs($this->admin);

        $this->put('/admin/' . $this->admin->id, [
            'name' => 'Admin Diperbarui',
            'email' => 'admin-diperbarui@example.com',
            'role' => 'admin',
            'status' => 'active',
        ])->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Admin Diperbarui',
            'email' => 'admin-diperbarui@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_escalate_own_role_or_status(): void
    {
        $this->actingAs($this->admin);

        $this->put('/admin/' . $this->admin->id, [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'superadmin',
            'status' => 'pending',
        ])->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_create_users(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin', [
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ])->assertStatus(403);
    }

    public function test_admin_cannot_manage_other_admins(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($this->admin);

        $this->put('/admin/' . $otherAdmin->id, [
            'name' => 'Diubah Admin Lain',
            'email' => 'diubah@example.com',
            'role' => 'admin',
            'status' => 'active',
        ])->assertStatus(403);

        $this->delete('/admin/' . $otherAdmin->id)->assertStatus(403);
    }

    public function test_admin_cannot_manage_superadmin_account(): void
    {
        $this->actingAs($this->admin);

        $this->put('/admin/' . $this->owner->id, [
            'name' => 'Diubah Admin',
            'email' => 'diubah@example.com',
            'role' => 'admin',
            'status' => 'active',
        ])->assertStatus(403);

        $this->delete('/admin/' . $this->owner->id)->assertStatus(403);
    }

    public function test_superadmin_can_manage_users(): void
    {
        $this->actingAs($this->owner);

        $this->post('/admin', [
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ])->assertRedirect(route('admin.index'));

        $newAdmin = User::where('email', 'admin-baru@example.com')->firstOrFail();

        $this->put('/admin/' . $newAdmin->id, [
            'name' => 'Admin Diperbarui',
            'email' => 'admin-baru@example.com',
            'role' => 'admin',
            'status' => 'active',
        ])->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'id' => $newAdmin->id,
            'name' => 'Admin Diperbarui',
        ]);

        $this->delete('/admin/' . $newAdmin->id)->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('users', ['id' => $newAdmin->id]);
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->get('/customers')->assertRedirect('/login');
        $this->get('/services')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/biaya-harian')->assertRedirect('/login');
    }
}
