<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
        $this->customer = Customer::create(['nama' => 'Test', 'no_hp' => '081234']);
    }

    public function test_can_create_service_with_items(): void
    {
        $response = $this->post('/services', [
            'tanggal' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'type_hp' => 'Samsung A52',
                    'kerusakan' => 'Ganti LCD',
                    'jasa' => '200000',
                    'pengeluaran' => '80000',
                    'keterangan' => 'Test',
                ],
            ],
        ]);

        $response->assertRedirect('/services');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'type_hp' => 'Samsung A52',
            'jasa' => 200000,
            'pengeluaran' => 80000,
            'keuntungan' => 120000,
        ]);

        $this->assertDatabaseHas('service_items', [
            'type_hp' => 'Samsung A52',
            'jasa' => 200000,
        ]);
    }

    public function test_cannot_create_service_without_items(): void
    {
        $response = $this->post('/services', [
            'tanggal' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_cannot_create_service_without_customer(): void
    {
        $response = $this->post('/services', [
            'tanggal' => now()->toDateString(),
            'customer_id' => 999,
            'items' => [['type_hp' => 'X', 'kerusakan' => 'Y']],
        ]);

        $response->assertSessionHasErrors('customer_id');
    }

    public function test_can_update_service(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Oppo',
            'kerusakan' => 'Baterei',
            'jasa' => 100000,
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->put("/services/{$service->id}", [
            'tanggal' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'type_hp' => 'Oppo Updated',
                    'kerusakan' => 'Baterei Updated',
                    'jasa' => 150000,
                    'pengeluaran' => 30000,
                ],
            ],
        ]);

        $response->assertRedirect('/services');
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'type_hp' => 'Oppo Updated',
            'jasa' => 150000,
            'keuntungan' => 120000,
        ]);
    }

    public function test_can_delete_service(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Xiaomi',
            'kerusakan' => 'Touchscreen',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->delete("/services/{$service->id}");

        $response->assertRedirect('/services');
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_can_update_status_via_ajax(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Vivo',
            'kerusakan' => 'Mic',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->put("/services/{$service->id}/status", [
            'status' => 'dikerjakan',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('dikerjakan', $service->fresh()->status);
    }

    public function test_cannot_update_status_to_invalid_transition(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Realme',
            'kerusakan' => 'Software',
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
        ]);

        $response = $this->put("/services/{$service->id}/status", [
            'status' => 'batal',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_can_quick_update_teknisi(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Test',
            'kerusakan' => 'Test',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->put("/services/{$service->id}/quick-update", [
            'teknisi' => 'Edi',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'teknisi' => 'Edi']);
        $this->assertEquals('Edi', $service->fresh()->teknisi);
    }

    public function test_can_update_garansi(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Test',
            'kerusakan' => 'Test',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->put("/services/{$service->id}/garansi", [
            'garansi' => '3 bulan',
        ]);

        $response->assertRedirect();
        $this->assertEquals('3 bulan', $service->fresh()->garansi);
    }

    public function test_show_service_page(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Asus',
            'kerusakan' => 'Motherboard',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->get("/services/{$service->id}");

        $response->assertStatus(200);
        $response->assertSee('Asus');
    }

    public function test_can_print_pdf(): void
    {
        $service = Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Nokia',
            'kerusakan' => 'Antenna',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->get("/services/{$service->id}/print-pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
