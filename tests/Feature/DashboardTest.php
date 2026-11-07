<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
    }

    public function test_dashboard_loads_and_shows_kpi(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_accepts_custom_date_range(): void
    {
        $response = $this->get('/dashboard?from=2026-01-01&to=2026-12-31');

        $response->assertStatus(200);
    }

    public function test_dashboard_data_includes_pickup_summary(): void
    {
        $customer = Customer::create(['nama' => 'Pelanggan Test']);
        $tanggal = now()->toDateString();

        $makeService = function (string $status, float $jasa, float $pengeluaran) use ($customer, $tanggal) {
            return Service::create([
                'customer_id' => $customer->id,
                'type_hp' => 'iPhone 12',
                'kerusakan' => 'LCD',
                'tanggal' => $tanggal,
                'status' => $status,
                'jasa' => $jasa,
                'pengeluaran' => $pengeluaran,
            ]);
        };

        $makeService('menunggu', 100000, 40000);
        $makeService('diambil', 200000, 50000);
        $makeService('selesai', 150000, 50000);
        $makeService('batal', 300000, 100000);

        $response = $this->getJson('/dashboard/data?period=daily');

        $response->assertOk();

        $summary = $response->json('pickupSummary');

        $this->assertEquals(1, $summary['taken_count']);
        $this->assertEquals(150000, $summary['taken_profit']);
        $this->assertEquals(2, $summary['pending_count']);
        $this->assertEquals(160000, $summary['pending_profit']);
    }
}
