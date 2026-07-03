<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('currentpassword'),
        ]);
        $this->actingAs($this->user);
    }

    public function test_profile_page_loads(): void
    {
        $response = $this->get('/profile');

        $response->assertStatus(200);
    }

    public function test_can_update_profile_name(): void
    {
        $response = $this->put('/profile', [
            'name' => 'Nama Baru',
            'email' => $this->user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_can_update_password(): void
    {
        $response = $this->put('/profile', [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => 'currentpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));
    }

    public function test_cannot_update_password_with_wrong_current_password(): void
    {
        $response = $this->put('/profile', [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }
}
