<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
        $this->customer = Customer::create(['nama' => 'Test', 'no_hp' => '08111']);
    }

    public function test_report_page_loads(): void
    {
        Service::create([
            'customer_id' => $this->customer->id,
            'type_hp' => 'Test',
            'kerusakan' => 'Test',
            'tanggal' => now()->toDateString(),
            'status' => 'selesai',
        ]);

        $response = $this->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('Test');
    }

    public function test_import_form_loads(): void
    {
        $response = $this->get('/reports/import');
        $response->assertStatus(200);
    }
}
