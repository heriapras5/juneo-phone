<?php

namespace Tests\Feature;

use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
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
        $response->assertSee('openStatusDetailBtn', false);
        $response->assertDontSee('statusProfitHintBtn', false);
        $response->assertDontSee('Klik grafik untuk lihat profit');
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
        $this->assertEquals(310000, $summary['total_profit']);
        $this->assertEquals(310000, $response->json('profit'));

        $this->assertEquals(1, $response->json('statusBreakdown.batal.count'));
        $this->assertEquals(0, $response->json('statusBreakdown.batal.profit'));
        $this->assertEquals(150000, $response->json('statusBreakdown.diambil.profit'));
        $this->assertEquals(60000, $response->json('statusBreakdown.menunggu.profit'));
        $this->assertEquals(100000, $response->json('statusBreakdown.selesai.profit'));
        $this->assertEquals($response->json('profit'), $summary['taken_profit'] + $summary['pending_profit']);
    }

    public function test_pickup_summary_splits_period_profit_after_kebutuhan_pribadi(): void
    {
        $customer = Customer::create(['nama' => 'Pelanggan Test']);
        $tanggal = now()->toDateString();

        foreach ([200000, 200000] as $jasa) {
            Service::create([
                'customer_id' => $customer->id,
                'type_hp' => 'iPhone 12',
                'kerusakan' => 'LCD',
                'tanggal' => $tanggal,
                'status' => 'selesai',
                'jasa' => $jasa,
                'pengeluaran' => 0,
            ]);
        }

        BiayaHarian::create([
            'tanggal' => $tanggal,
            'kategori' => 'bank_jago',
            'jumlah' => 138500,
        ]);

        $response = $this->getJson('/dashboard/data?period=daily');
        $response->assertOk();

        $summary = $response->json('pickupSummary');

        $this->assertEquals(2, $summary['pending_count']);
        $this->assertEquals(261500, $summary['pending_profit']);
        $this->assertEquals(0, $summary['taken_profit']);
        $this->assertEquals(261500, $summary['total_profit']);
        $this->assertEquals($response->json('profit'), $summary['total_profit']);
        $this->assertEquals(261500, $response->json('statusBreakdown.selesai.profit'));
    }

    public function test_pickup_summary_splits_profit_by_gross_profit_contribution(): void
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

        $makeService('diambil', 700000, 100000);
        $makeService('selesai', 400000, 0);

        BiayaHarian::create([
            'tanggal' => $tanggal,
            'kategori' => 'bank_jago',
            'jumlah' => 100000,
        ]);

        $response = $this->getJson('/dashboard/data?period=daily');
        $response->assertOk();

        $summary = $response->json('pickupSummary');

        $this->assertEquals(1, $summary['taken_count']);
        $this->assertEquals(540000, $summary['taken_profit']);
        $this->assertEquals(1, $summary['pending_count']);
        $this->assertEquals(360000, $summary['pending_profit']);
        $this->assertEquals(900000, $summary['total_profit']);
        $this->assertEquals($response->json('profit'), $summary['taken_profit'] + $summary['pending_profit']);
        $this->assertEquals(540000, $response->json('statusBreakdown.diambil.profit'));
        $this->assertEquals(360000, $response->json('statusBreakdown.selesai.profit'));
    }

    public function test_pickup_summary_ignores_kebutuhan_pribadi_without_services(): void
    {
        BiayaHarian::create([
            'tanggal' => now()->toDateString(),
            'kategori' => 'bank_jago',
            'jumlah' => 138500,
        ]);

        $response = $this->getJson('/dashboard/data?period=daily');
        $response->assertOk();

        $summary = $response->json('pickupSummary');

        $this->assertEquals(0, $summary['taken_count']);
        $this->assertEquals(0, $summary['pending_count']);
        $this->assertEquals(0, $summary['taken_profit']);
        $this->assertEquals(0, $summary['pending_profit']);
        $this->assertEquals(0, $summary['total_profit']);
        $this->assertEquals(0, $response->json('statusBreakdown.selesai.profit'));
        $this->assertEquals(0, $response->json('statusBreakdown.diambil.profit'));
        $this->assertEquals(-138500, $response->json('profit'));
    }

    public function test_rounding_remainder_is_not_allocated_to_batal_status(): void
    {
        $customer = Customer::create(['nama' => 'Pelanggan Rounding']);
        $tanggal = now()->toDateString();

        foreach (['menunggu', 'diambil', 'selesai'] as $status) {
            Service::create([
                'customer_id' => $customer->id,
                'type_hp' => 'iPhone 12',
                'kerusakan' => 'LCD',
                'tanggal' => $tanggal,
                'status' => $status,
                'jasa' => 100,
                'pengeluaran' => 0,
            ]);
        }

        Service::create([
            'customer_id' => $customer->id,
            'type_hp' => 'iPhone 12',
            'kerusakan' => 'LCD',
            'tanggal' => $tanggal,
            'status' => 'batal',
            'jasa' => 300,
            'pengeluaran' => 0,
        ]);

        BiayaHarian::create([
            'tanggal' => $tanggal,
            'kategori' => 'lainnya',
            'jumlah' => 200,
        ]);

        $response = $this->getJson('/dashboard/data?period=daily');
        $response->assertOk();

        $summary = $response->json('pickupSummary');

        $this->assertEquals(100, $response->json('profit'));
        $this->assertEquals(0, $response->json('statusBreakdown.batal.profit'));
        $this->assertEquals(100, $summary['taken_profit'] + $summary['pending_profit']);
        $this->assertEquals(100, $summary['total_profit']);
    }

    public function test_summary_period_label_element_is_rendered(): void
    {
        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('pickupSummaryPeriodLabel', false);
    }

    public function test_fee_note_does_not_leak_from_previous_period(): void
    {
        BiayaHarian::create([
            'tanggal' => '2026-11-01',
            'kategori' => 'fee_edi',
            'nama' => 'Edi',
            'jumlah' => 60000,
            'keterangan' => 'Libur',
        ]);

        BiayaHarian::create([
            'tanggal' => '2026-11-08',
            'kategori' => 'fee_edi',
            'nama' => 'Edi',
            'jumlah' => 60000,
            'keterangan' => null,
        ]);

        $service = app(DashboardService::class);
        $totals = $service->getPeriodTotals(
            Carbon::parse('2026-10-26')->startOfDay(),
            Carbon::parse('2026-11-08')->endOfDay()
        );

        $fee = collect($totals->fees)->firstWhere('kategori', 'fee_edi');

        $this->assertNotNull($fee);
        $this->assertNull($fee->keterangan, 'Catatan lama tidak boleh terbawa saat catatan terbaru kosong.');
    }

    public function test_latest_fee_note_wins_when_present(): void
    {
        BiayaHarian::create([
            'tanggal' => '2026-11-01',
            'kategori' => 'fee_edi',
            'nama' => 'Edi',
            'jumlah' => 60000,
            'keterangan' => 'Libur',
        ]);

        BiayaHarian::create([
            'tanggal' => '2026-11-08',
            'kategori' => 'fee_edi',
            'nama' => 'Edi',
            'jumlah' => 60000,
            'keterangan' => 'Masuk',
        ]);

        $service = app(DashboardService::class);
        $totals = $service->getPeriodTotals(
            Carbon::parse('2026-10-26')->startOfDay(),
            Carbon::parse('2026-11-08')->endOfDay()
        );

        $fee = collect($totals->fees)->firstWhere('kategori', 'fee_edi');

        $this->assertNotNull($fee);
        $this->assertSame('Masuk', $fee->keterangan);
    }

    public function test_fee_components_without_data_or_zero_are_hidden(): void
    {
        BiayaHarian::create([
            'tanggal' => now()->toDateString(),
            'kategori' => 'bank_jago',
            'jumlah' => 138500,
        ]);

        BiayaHarian::create([
            'tanggal' => now()->toDateString(),
            'kategori' => 'fee_tambahan',
            'nama' => 'Kosong',
            'jumlah' => 0,
        ]);

        $response = $this->getJson('/dashboard/data?period=daily');
        $response->assertOk();

        $fees = $response->json('fees');
        $this->assertSame([], $fees, 'Fee tanpa data atau bernilai 0 tidak boleh ikut dikirim.');

        $html = $this->get('/dashboard')->getContent();
        $this->assertStringContainsString('Bank Jago', $html);
        $this->assertStringNotContainsString('Fee Edi', $html);
        $this->assertStringNotContainsString('Fee Nur', $html);
    }
}
