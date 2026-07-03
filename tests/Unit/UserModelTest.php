<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_admin_role_constant(): void
    {
        $this->assertEquals('admin', User::ROLE_ADMIN);
    }

    public function test_user_has_owner_role_constant(): void
    {
        $this->assertEquals('owner', User::ROLE_OWNER);
    }

    public function test_is_active_returns_true_for_active(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertTrue($user->isActive());
    }

    public function test_is_active_returns_false_for_pending(): void
    {
        $user = User::factory()->create(['status' => 'pending']);
        $this->assertFalse($user->isActive());
    }

    public function test_is_active_returns_false_for_rejected(): void
    {
        $user = User::factory()->create(['status' => 'rejected']);
        $this->assertFalse($user->isActive());
    }

    public function test_scope_active_returns_only_active_users(): void
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'pending']);
        User::factory()->create(['status' => 'rejected']);

        $this->assertEquals(1, User::active()->count());
    }

    public function test_scope_pending_returns_only_pending_users(): void
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'pending']);
        User::factory()->create(['status' => 'pending']);

        $this->assertEquals(2, User::pending()->count());
    }

    public function test_scope_rejected_returns_only_rejected_users(): void
    {
        User::factory()->create(['status' => 'rejected']);
        User::factory()->create(['status' => 'active']);

        $this->assertEquals(1, User::rejected()->count());
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->assertNotEquals('password', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $user->password));
    }
}
