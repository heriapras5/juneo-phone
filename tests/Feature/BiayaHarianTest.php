<?php

namespace Tests\Feature;

use App\Models\BiayaHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiayaHarianTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
    }

    public function test_can_view_biaya_harian_page(): void
    {
        $response = $this->get('/biaya-harian');

        $response->assertStatus(200);
        $response->assertSee('Biaya Harian');
    }

    public function test_can_update_biaya_harian(): void
    {
        $response = $this->post('/biaya-harian', [
            'tanggal' => now()->toDateString(),
            'items' => [
                ['kategori' => 'bank_jago', 'jumlah' => '110000', 'keterangan' => 'Setor'],
                ['kategori' => 'fee_edi', 'jumlah' => '60000', 'keterangan' => 'Fee Edi'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('biaya_harian', [
            'kategori' => 'bank_jago',
            'jumlah' => 110000,
        ]);
        $this->assertDatabaseHas('biaya_harian', [
            'kategori' => 'fee_edi',
            'jumlah' => 60000,
        ]);
    }

    public function test_can_update_existing_biaya_harian(): void
    {
        BiayaHarian::create([
            'tanggal' => now()->toDateString(),
            'kategori' => 'bank_jago',
            'jumlah' => 100000,
        ]);

        $response = $this->post('/biaya-harian', [
            'tanggal' => now()->toDateString(),
            'items' => [
                ['kategori' => 'bank_jago', 'jumlah' => '150000', 'keterangan' => 'Updated'],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('biaya_harian', [
            'kategori' => 'bank_jago',
            'jumlah' => 150000,
        ]);
        $this->assertEquals(1, BiayaHarian::where('kategori', 'bank_jago')->count());
    }

    public function test_can_delete_biaya_harian_by_date(): void
    {
        BiayaHarian::create([
            'tanggal' => now()->toDateString(),
            'kategori' => 'bank_jago',
            'jumlah' => 110000,
        ]);
        BiayaHarian::create([
            'tanggal' => now()->toDateString(),
            'kategori' => 'fee_edi',
            'jumlah' => 60000,
        ]);

        $response = $this->delete('/biaya-harian/' . now()->toDateString());

        $response->assertRedirect();
        $this->assertEquals(0, BiayaHarian::count());
    }
}
