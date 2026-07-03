<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceModelTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = Customer::create(['nama' => 'Test', 'no_hp' => '081234']);
    }

    private function createService(array $overrides = []): Service
    {
        return Service::create(array_merge([
            'customer_id' => $this->customer->id,
            'type_hp' => 'iPhone 12',
            'kerusakan' => 'LCD Rusak',
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
        ], $overrides));
    }

    public function test_service_number_generated_on_create(): void
    {
        $service = $this->createService();

        $this->assertNotNull($service->service_number);
        $this->assertStringStartsWith('SRV-' . now()->format('Ymd') . '-', $service->service_number);
    }

    public function test_service_number_increments_daily(): void
    {
        $s1 = $this->createService();
        $s2 = $this->createService();

        $prefix = 'SRV-' . now()->format('Ymd') . '-';
        $this->assertEquals($prefix . '00001', $s1->service_number);
        $this->assertEquals($prefix . '00002', $s2->service_number);
    }

    public function test_profit_calculated_automatically_on_create(): void
    {
        $service = $this->createService([
            'jasa' => 200000,
            'pengeluaran' => 75000,
        ]);

        $this->assertEquals(125000, (int) $service->keuntungan);
    }

    public function test_profit_recalculated_on_update(): void
    {
        $service = $this->createService(['jasa' => 150000, 'pengeluaran' => 30000]);

        $service->update(['jasa' => 200000, 'pengeluaran' => 50000]);

        $this->assertEquals(150000, (int) $service->fresh()->keuntungan);
    }

    public function test_default_status_is_menunggu(): void
    {
        $service = $this->createService();

        $this->assertEquals('menunggu', $service->status);
    }

    public function test_allowed_transitions_are_correct(): void
    {
        $transitions = Service::allowedTransitions();

        $this->assertEquals(['dikerjakan', 'batal'], $transitions['menunggu']);
        $this->assertEquals(['selesai', 'batal'], $transitions['dikerjakan']);
        $this->assertEquals(['diambil'], $transitions['selesai']);
        $this->assertEquals([], $transitions['diambil']);
        $this->assertEquals([], $transitions['batal']);
    }

    public function test_status_transition_creates_history(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($user);

        $service = $this->createService();
        $service->update(['status' => 'dikerjakan']);

        $this->assertDatabaseHas('service_histories', [
            'service_id' => $service->id,
            'status_from' => 'menunggu',
            'status_to' => 'dikerjakan',
            'user_id' => $user->id,
        ]);
    }

    public function test_completion_sets_tanggal_selesai(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($user);

        $service = $this->createService(['status' => 'dikerjakan']);
        $service->update(['status' => 'selesai']);

        $this->assertNotNull($service->fresh()->tanggal_selesai);
        $this->assertEquals(now()->toDateString(), $service->fresh()->tanggal_selesai->toDateString());
    }

    public function test_cancel_resets_monetary_values(): void
    {
        $service = $this->createService([
            'jasa' => 500000,
            'pengeluaran' => 200000,
            'kebutuhan_pribadi' => 50000,
        ]);

        $service->update(['status' => 'batal']);

        $fresh = $service->fresh();
        $this->assertEquals(0, (int) $fresh->pengeluaran);
        $this->assertEquals(0, (int) $fresh->jasa);
        $this->assertEquals(0, (int) $fresh->keuntungan);
        $this->assertEquals(0, (int) $fresh->kebutuhan_pribadi);
    }

    public function test_status_labels_returns_all(): void
    {
        $labels = Service::statusLabels();
        $this->assertEquals('Menunggu', $labels['menunggu']);
        $this->assertEquals('Dikerjakan', $labels['dikerjakan']);
        $this->assertEquals('Selesai', $labels['selesai']);
        $this->assertEquals('Diambil', $labels['diambil']);
        $this->assertEquals('Batal', $labels['batal']);
    }

    public function test_get_display_priority_returns_rendah_when_recent(): void
    {
        $service = $this->createService(['tanggal' => now()->toDateString()]);
        $this->assertEquals('rendah', $service->getDisplayPriority());
    }

    public function test_get_display_priority_returns_urgent_when_overdue(): void
    {
        $service = $this->createService(['tanggal' => now()->subDays(6)->toDateString()]);
        $this->assertEquals('urgent', $service->getDisplayPriority());
    }

    public function test_get_display_priority_uses_estimasi(): void
    {
        $service = $this->createService(['estimasi_selesai' => now()->addDays(5)->toDateString()]);
        $this->assertEquals('rendah', $service->getDisplayPriority());
    }

    public function test_service_has_customer_relationship(): void
    {
        $service = $this->createService();
        $this->assertInstanceOf(Customer::class, $service->customer);
        $this->assertEquals($this->customer->id, $service->customer->id);
    }
}
