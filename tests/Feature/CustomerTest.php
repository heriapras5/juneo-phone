<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
    }

    public function test_can_view_customers_list(): void
    {
        Customer::create(['nama' => 'Budi', 'no_hp' => '081111']);
        Customer::create(['nama' => 'Ani', 'no_hp' => '082222']);

        $response = $this->get('/customers');

        $response->assertStatus(200);
        $response->assertSee('Budi');
        $response->assertSee('Ani');
    }

    public function test_can_create_customer(): void
    {
        $response = $this->post('/customers', [
            'nama' => 'Customer Baru',
            'no_hp' => '08123456789',
            'alamat' => 'Jl. Merdeka No.1',
        ]);

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'nama' => 'Customer Baru',
            'no_hp' => '08123456789',
        ]);
    }

    public function test_cannot_create_customer_without_name(): void
    {
        $response = $this->post('/customers', [
            'nama' => '',
            'no_hp' => '081234',
        ]);

        $response->assertSessionHasErrors('nama');
    }

    public function test_can_quick_store_customer(): void
    {
        $response = $this->post('/customers/quick', [
            'nama' => 'Quick Customer',
            'no_hp' => '089999',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('customers', ['nama' => 'Quick Customer']);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::create(['nama' => 'Lama', 'no_hp' => '08111']);

        $response = $this->put("/customers/{$customer->id}", [
            'nama' => 'Baru',
            'no_hp' => '08222',
            'alamat' => 'Alamat baru',
        ]);

        $response->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', ['nama' => 'Baru', 'no_hp' => '08222']);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::create(['nama' => 'Hapus', 'no_hp' => '08111']);

        $response = $this->delete("/customers/{$customer->id}");

        $response->assertRedirect('/customers');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
