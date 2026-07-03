<?php

namespace Tests\Feature;

use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
    }

    public function test_data_page_loads(): void
    {
        $response = $this->get('/data');

        $response->assertStatus(200);
    }

    public function test_can_download_backup(): void
    {
        $response = $this->post('/data/backup');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_cannot_reset_without_correct_confirmation(): void
    {
        $response = $this->post('/data/reset', [
            'confirmation' => 'WRONG',
        ]);

        $response->assertSessionHasErrors('confirmation');
    }

    public function test_can_reset_data_with_correct_confirmation(): void
    {
        $customer = Customer::create(['nama' => 'Test', 'no_hp' => '08111']);
        Service::create([
            'customer_id' => $customer->id,
            'type_hp' => 'Test',
            'kerusakan' => 'Test',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ]);
        BiayaHarian::create([
            'tanggal' => now()->toDateString(),
            'kategori' => 'bank_jago',
            'jumlah' => 110000,
        ]);

        $response = $this->post('/data/reset', [
            'confirmation' => 'RESET',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(0, Service::count());
        $this->assertEquals(0, Customer::count());
        $this->assertEquals(0, BiayaHarian::count());
    }
}
