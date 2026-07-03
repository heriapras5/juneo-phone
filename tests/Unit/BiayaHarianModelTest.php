<?php

namespace Tests\Unit;

use App\Models\BiayaHarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiayaHarianModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_biaya_harian(): void
    {
        $biaya = BiayaHarian::create([
            'tanggal' => now(),
            'kategori' => 'bank_jago',
            'nama' => 'Bank Jago',
            'jumlah' => 110000,
            'keterangan' => 'Setoran harian',
        ]);

        $this->assertNotNull($biaya->id);
        $this->assertEquals('bank_jago', $biaya->kategori);
    }

    public function test_unique_tanggal_kategori_constraint(): void
    {
        BiayaHarian::create([
            'tanggal' => '2026-06-01',
            'kategori' => 'bank_jago',
            'jumlah' => 110000,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        BiayaHarian::create([
            'tanggal' => '2026-06-01',
            'kategori' => 'bank_jago',
            'jumlah' => 110000,
        ]);
    }

    public function test_multiple_categories_same_date(): void
    {
        BiayaHarian::create(['tanggal' => '2026-06-01', 'kategori' => 'bank_jago', 'jumlah' => 110000]);
        BiayaHarian::create(['tanggal' => '2026-06-01', 'kategori' => 'fee_edi', 'jumlah' => 60000]);
        BiayaHarian::create(['tanggal' => '2026-06-01', 'kategori' => 'fee_nur', 'jumlah' => 30000]);
        BiayaHarian::create(['tanggal' => '2026-06-01', 'kategori' => 'lainnya', 'jumlah' => 50000]);

        $this->assertEquals(4, BiayaHarian::whereDate('tanggal', '2026-06-01')->count());
        $this->assertEquals(250000, (int) BiayaHarian::whereDate('tanggal', '2026-06-01')->sum('jumlah'));
    }
}
