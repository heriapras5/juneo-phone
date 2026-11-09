<?php

namespace Tests\Feature;

use App\Models\PhoneTrade;
use App\Models\PhoneTradeHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneTradeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type_hp' => 'iPhone 13 Pro Max',
            'brand' => 'Apple',
            'storage' => '8/256 GB',
            'warna' => 'Graphite',
            'kondisi' => 'bekas',
            'sumber' => 'langsung',
            'imei' => '356789012345678',
            'kelengkapan' => 'Unit, charger, dus',
            'tanggal_masuk' => now()->toDateString(),
            'harga_beli' => 5000000,
            'biaya_perbaikan' => 0,
            'penjual' => 'Budi',
            'status' => 'stok',
            'keterangan' => 'Mulus, minus lecet halus',
        ], $overrides);
    }

    public function test_page_loads(): void
    {
        $this->get('/phone-trades')
            ->assertOk()
            ->assertSee('Jual Beli HP')
            ->assertSee('Modal Stok')
            ->assertSee('Laporan Jual Beli HP');
    }

    public function test_can_create_stock_trade(): void
    {
        $this->post('/phone-trades', $this->payload())
            ->assertRedirect('/phone-trades')
            ->assertSessionHas('success');

        $trade = PhoneTrade::firstOrFail();

        $this->assertSame('stok', $trade->status);
        $this->assertSame('JB-0001', $trade->kode);
        $this->assertSame(5000000.0, (float) $trade->harga_beli);
        $this->assertNull($trade->harga_jual);
        $this->assertNull($trade->tanggal_keluar);
    }

    public function test_can_create_sold_trade_and_profit_is_calculated(): void
    {
        $this->post('/phone-trades', $this->payload([
            'status' => 'terjual',
            'tanggal_keluar' => now()->toDateString(),
            'harga_jual' => '7.000.000',
            'pembeli' => 'Siti',
        ]))->assertSessionHas('success');

        $trade = PhoneTrade::firstOrFail();

        $this->assertSame('terjual', $trade->status);
        $this->assertSame(7000000.0, (float) $trade->harga_jual);
        $this->assertSame(2000000.0, $trade->keuntungan);
    }

    public function test_sale_fields_are_required_when_status_is_terjual(): void
    {
        $this->post('/phone-trades', $this->payload([
            'status' => 'terjual',
        ]))->assertSessionHasErrors(['tanggal_keluar', 'harga_jual']);
    }

    public function test_can_mark_stock_as_sold(): void
    {
        $this->post('/phone-trades', $this->payload());
        $trade = PhoneTrade::firstOrFail();

        $this->put('/phone-trades/' . $trade->id, $this->payload([
            'status' => 'terjual',
            'tanggal_keluar' => now()->toDateString(),
            'harga_jual' => '6.500.000',
            'pembeli' => 'Andi',
        ]))->assertSessionHas('success');

        $trade->refresh();

        $this->assertSame('terjual', $trade->status);
        $this->assertSame('Andi', $trade->pembeli);
        $this->assertSame(1500000.0, $trade->keuntungan);
    }

    public function test_summary_separates_stock_and_sales(): void
    {
        PhoneTrade::create($this->payload([
            'status' => 'stok',
            'harga_beli' => 2000000,
        ]));

        PhoneTrade::create($this->payload([
            'type_hp' => 'Samsung S23',
            'status' => 'terjual',
            'harga_beli' => 3000000,
            'harga_jual' => 4000000,
            'tanggal_keluar' => now()->toDateString(),
            'pembeli' => 'Rina',
        ]));

        $this->get('/phone-trades')
            ->assertOk()
            ->assertViewHas('summary', function (array $summary) {
                return $summary['stock_count'] === 1
                    && $summary['stock_modal'] === 2000000.0
                    && $summary['sold_count'] === 1
                    && $summary['omzet'] === 4000000.0
                    && $summary['modal_terjual'] === 3000000.0
                    && $summary['laba'] === 1000000.0;
            });
    }

    public function test_can_delete_trade(): void
    {
        $this->post('/phone-trades', $this->payload());
        $trade = PhoneTrade::firstOrFail();

        $this->delete('/phone-trades/' . $trade->id)
            ->assertRedirect('/phone-trades')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('phone_trades', ['id' => $trade->id]);
    }

    public function test_profit_includes_repair_cost(): void
    {
        $this->post('/phone-trades', $this->payload([
            'status' => 'terjual',
            'biaya_perbaikan' => '500.000',
            'tanggal_keluar' => now()->toDateString(),
            'harga_jual' => '7.000.000',
            'pembeli' => 'Rina',
        ]))->assertSessionHas('success');

        $trade = PhoneTrade::firstOrFail();

        $this->assertSame(5500000.0, $trade->total_modal);
        $this->assertSame(1500000.0, $trade->keuntungan);
    }

    public function test_summary_includes_repair_cost_in_modal(): void
    {
        PhoneTrade::create($this->payload([
            'status' => 'stok',
            'harga_beli' => 2000000,
            'biaya_perbaikan' => 250000,
        ]));

        PhoneTrade::create($this->payload([
            'type_hp' => 'Samsung S23',
            'status' => 'terjual',
            'harga_beli' => 3000000,
            'biaya_perbaikan' => 500000,
            'harga_jual' => 4000000,
            'tanggal_keluar' => now()->toDateString(),
            'pembeli' => 'Rina',
        ]));

        $this->get('/phone-trades')
            ->assertOk()
            ->assertViewHas('summary', function (array $summary) {
                return $summary['stock_count'] === 1
                    && $summary['stock_modal'] === 2250000.0
                    && $summary['sold_count'] === 1
                    && $summary['omzet'] === 4000000.0
                    && $summary['modal_terjual'] === 3500000.0
                    && $summary['laba'] === 500000.0;
            });
    }

    public function test_sumber_service_is_saved_and_shown_on_detail(): void
    {
        $this->post('/phone-trades', $this->payload(['sumber' => 'service']))
            ->assertSessionHas('success');

        $trade = PhoneTrade::firstOrFail();

        $this->assertSame('service', $trade->sumber);

        $this->get('/phone-trades/' . $trade->id)
            ->assertOk()
            ->assertSee('Dari Service');
    }

    public function test_detail_page_shows_history_and_can_update_status(): void
    {
        $this->post('/phone-trades', $this->payload());
        $trade = PhoneTrade::firstOrFail();

        $this->get('/phone-trades/' . $trade->id)
            ->assertOk()
            ->assertSee('Riwayat Perubahan')
            ->assertSee('Data HP ditambahkan.');

        $this->put('/phone-trades/' . $trade->id . '/status', [
            'status' => 'terjual',
            'tanggal_keluar' => now()->toDateString(),
            'harga_jual' => '6.000.000',
            'pembeli' => 'Andi',
        ])->assertRedirect('/phone-trades/' . $trade->id);

        $trade->refresh();

        $this->assertSame('terjual', $trade->status);
        $this->assertDatabaseHas('phone_trade_histories', [
            'phone_trade_id' => $trade->id,
            'tipe' => 'status',
            'status_from' => 'stok',
            'status_to' => 'terjual',
        ]);
    }

    public function test_update_logs_monetary_changes(): void
    {
        $this->post('/phone-trades', $this->payload());
        $trade = PhoneTrade::firstOrFail();

        $this->put('/phone-trades/' . $trade->id, $this->payload([
            'harga_beli' => '4.500.000',
            'biaya_perbaikan' => '250.000',
        ]))->assertSessionHas('success');

        $history = PhoneTradeHistory::where('phone_trade_id', $trade->id)
            ->where('tipe', PhoneTrade::TIPE_UPDATE)
            ->latest('id')
            ->first();

        $this->assertNotNull($history);
        $this->assertStringContainsString('Harga beli', $history->note);
        $this->assertStringContainsString('Biaya perbaikan', $history->note);
    }

    public function test_exports_return_success(): void
    {
        PhoneTrade::create($this->payload([
            'status' => 'terjual',
            'harga_jual' => 6000000,
            'tanggal_keluar' => now()->toDateString(),
            'pembeli' => 'Andi',
        ]));

        $this->get('/phone-trades/export-excel')->assertOk();
        $this->get('/phone-trades/export-pdf')->assertOk();
    }
}
