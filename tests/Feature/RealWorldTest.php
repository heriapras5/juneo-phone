<?php

namespace Tests\Feature;

use App\Exports\BackupExport;
use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\DamageCategory;
use App\Models\Setting;
use App\Models\Service;
use App\Models\TypeHpOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class RealWorldTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        Setting::updateOrCreate(['key' => 'nama_toko'], ['value' => 'JUNEO PHONE']);
        Setting::updateOrCreate(['key' => 'alamat'], ['value' => 'Jl. Testing No. 1']);
        Setting::updateOrCreate(['key' => 'nota_telepon'], ['value' => '08123456789']);
        Setting::flushCache();
    }

    /** PHASE 1: Master Data */
    public function test_phase_1_settings_damage_categories_and_customers()
    {
        $this->actingAs($this->user);

        // 1. Setting: atur nama toko, alamat, telepon
        $this->put('/settings', [
            'nama_toko' => 'Bengkel HP Baru',
            'alamat' => 'Jl. Merdeka No. 10',
            'telepon' => '0211234567',
            'biaya_bank_jago' => '100000',
            'biaya_fee_edi' => '50000',
            'biaya_fee_nur' => '25000',
            'nota_telepon' => '08111111111',
            'nota_rekening' => 'BCA 123456 a.n. Bengkel HP',
        ])->assertSessionHas('success');

        $this->assertEquals('Bengkel HP Baru', Setting::getValue('nama_toko'));

        // 2. Tambah jenis kerusakan via form POST
        $this->post('/settings/damage-categories', [
            'name' => 'LCD Retak',
        ])->assertSessionHas('success');

        $this->post('/settings/damage-categories', [
            'name' => 'Batre Boros',
        ])->assertSessionHas('success');

        $this->assertCount(2, DamageCategory::all());

        // 3. Tambah type HP via AJAX
        $this->postJson('/settings/type-hp/quick', ['name' => 'iPhone 12'])
            ->assertJson(['success' => true]);
        $this->postJson('/settings/type-hp/quick', ['name' => 'Samsung S24'])
            ->assertJson(['success' => true]);
        $this->postJson('/settings/type-hp/quick', ['name' => 'Xiaomi Note 13'])
            ->assertJson(['success' => true]);

        $this->assertCount(3, TypeHpOption::all());

        // 4. Tambah pelanggan via form
        $this->post('/customers', [
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Anggrek No. 5',
        ])->assertSessionHas('success');

        $this->post('/customers', [
            'nama' => 'Siti Aminah',
            'no_hp' => '087654321098',
        ])->assertSessionHas('success');

        // Quick-add customer
        $this->postJson('/customers/quick', [
            'nama' => 'Ahmad Rizki',
            'no_hp' => '08999999999',
        ])->assertJson(['success' => true]);

        $this->assertCount(3, Customer::all());
    }

    /** PHASE 2: Service Operations */
    public function test_phase_2_service_crud_and_status_transitions()
    {
        $this->actingAs($this->user);

        // Setup: create customer + damage categories
        $customer = Customer::create(['nama' => 'Budi Santoso', 'no_hp' => '081111']);
        DamageCategory::create(['name' => 'LCD Retak', 'sort_order' => 1, 'is_active' => true]);
        DamageCategory::create(['name' => 'Batre Boros', 'sort_order' => 2, 'is_active' => true]);

        // 5. Buat Service #1: 1 item HP, estimasi H+3, teknisi Edi
        $service1Data = [
            'tanggal' => now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'estimasi_selesai' => now()->addDays(3)->format('Y-m-d'),
            'teknisi' => 'edi',
            'items' => [
                [
                    'type_hp' => 'iPhone 12',
                    'kerusakan' => json_encode(['LCD Retak']),
                    'jasa' => '500000',
                    'pengeluaran' => '200000',
                ],
            ],
        ];

        $this->post('/services', $service1Data)->assertSessionHas('success');

        $service1 = Service::where('customer_id', $customer->id)->first();
        $this->assertNotNull($service1);
        $this->assertEquals('edi', $service1->teknisi);
        $this->assertEquals(now()->addDays(3)->format('Y-m-d'), $service1->estimasi_selesai->format('Y-m-d'));
        $this->assertEquals(300000, (int) $service1->keuntungan);
        $this->assertCount(1, $service1->serviceItems);

        // 6. Buat Service #2: 2 item HP (multi-type), lalu ubah status ke dikerjakan
        $customer2 = Customer::create(['nama' => 'Siti Aminah', 'no_hp' => '082222']);

        $this->post('/services', [
            'tanggal' => now()->format('Y-m-d'),
            'customer_id' => $customer2->id,
            'items' => [
                [
                    'type_hp' => 'Samsung S24',
                    'kerusakan' => json_encode(['LCD Retak']),
                    'jasa' => '800000',
                    'pengeluaran' => '300000',
                ],
                [
                    'type_hp' => 'Xiaomi Note 13',
                    'kerusakan' => json_encode(['Batre Boros']),
                    'jasa' => '200000',
                    'pengeluaran' => '50000',
                ],
            ],
        ])->assertSessionHas('success');

        $service2 = Service::where('customer_id', $customer2->id)->first();
        $this->assertEquals('menunggu', $service2->status);
        // Update status via AJAX
        $this->putJson('/services/' . $service2->id . '/status', ['status' => 'dikerjakan'])
            ->assertJson(['success' => true]);
        $service2->refresh();
        $this->assertEquals('dikerjakan', $service2->status);
        $this->assertCount(2, $service2->serviceItems);
        $this->assertEquals(1000000, (int) $service2->jasa);
        $this->assertEquals(350000, (int) $service2->pengeluaran);
        $this->assertEquals(650000, (int) $service2->keuntungan);

        // 7. Buat Service #3: tanpa estimasi, teknisi Nur
        $customer3 = Customer::create(['nama' => 'Ahmad Rizki', 'no_hp' => '083333']);

        $this->post('/services', [
            'tanggal' => now()->format('Y-m-d'),
            'customer_id' => $customer3->id,
            'teknisi' => 'nur',
            'items' => [
                [
                    'type_hp' => 'iPhone 12',
                    'kerusakan' => json_encode(['LCD Retak', 'Batre Boros']),
                    'jasa' => '700000',
                    'pengeluaran' => '250000',
                ],
            ],
        ])->assertSessionHas('success');

        $service3 = Service::where('customer_id', $customer3->id)->first();
        $this->assertEquals('nur', $service3->teknisi);
        $this->assertNull($service3->estimasi_selesai);

        // 8. Edit Service #1: tambah item HP kedua, ubah estimasi
        $service1->refresh();
        $newEstimasi = now()->addDays(5)->format('Y-m-d');

        $this->put('/services/' . $service1->id, [
            'tanggal' => $service1->tanggal->format('Y-m-d'),
            'customer_id' => $customer->id,
            'estimasi_selesai' => $newEstimasi,
            'teknisi' => 'edi',
            'items' => [
                [
                    'id' => $service1->serviceItems->first()->id,
                    'type_hp' => 'iPhone 12',
                    'kerusakan' => json_encode(['LCD Retak']),
                    'jasa' => '500000',
                    'pengeluaran' => '200000',
                ],
                [
                    'type_hp' => 'Samsung S24',
                    'kerusakan' => json_encode(['Batre Boros']),
                    'jasa' => '300000',
                    'pengeluaran' => '100000',
                ],
            ],
        ])->assertSessionHas('success');

        $service1->refresh();
        $this->assertEquals($newEstimasi, $service1->estimasi_selesai->format('Y-m-d'));
        $this->assertCount(2, $service1->serviceItems);
        $this->assertEquals(800000, (int) $service1->jasa);
        $this->assertEquals(300000, (int) $service1->pengeluaran);
        $this->assertEquals(500000, (int) $service1->keuntungan);

        // 9. Queue view (check it loads)
        $this->get('/services/queue')->assertStatus(200);

        // 10. Status transitions
        // Service #1: menunggu -> dikerjakan -> selesai -> diambil
        $this->putJson('/services/' . $service1->id . '/status', ['status' => 'dikerjakan'])
            ->assertJson(['success' => true]);
        $service1->refresh();
        $this->assertEquals('dikerjakan', $service1->status);

        $this->putJson('/services/' . $service1->id . '/status', ['status' => 'selesai'])
            ->assertJson(['success' => true]);
        $service1->refresh();
        $this->assertEquals('selesai', $service1->status);

        // diambil with garansi
        $this->putJson('/services/' . $service1->id . '/status', [
            'status' => 'diambil',
            'garansi' => 'Garansi 1 bulan LCD',
        ])->assertJson(['success' => true]);
        $service1->refresh();
        $this->assertEquals('diambil', $service1->status);
        $this->assertEquals('Garansi 1 bulan LCD', $service1->garansi);

        // Service #2: dikerjakan -> batal (cek financial jadi 0)
        $service2->refresh();
        $this->putJson('/services/' . $service2->id . '/status', ['status' => 'batal'])
            ->assertJson(['success' => true]);
        $service2->refresh();
        $this->assertEquals('batal', $service2->status);
        $this->assertEquals(0, (int) $service2->jasa);
        $this->assertEquals(0, (int) $service2->pengeluaran);
        $this->assertEquals(0, (int) $service2->keuntungan);

        // Invalid transition: diambil -> menunggu (should fail)
        $this->putJson('/services/' . $service1->id . '/status', ['status' => 'menunggu'])
            ->assertStatus(422);

        // 11. Garansi modal — verify garansi appears on service detail
        $this->get('/services/' . $service1->id)->assertSee('Garansi 1 bulan LCD');

        // Show page
        $this->get('/services/' . $service1->id)->assertStatus(200);
        $this->get('/services/' . $service3->id)->assertStatus(200);

        // Print PDF
        $this->get('/services/' . $service1->id . '/print-pdf')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /** PHASE 3: Financial & Laporan */
    public function test_phase_3_financial_and_reports()
    {
        $this->actingAs($this->user);

        $customer = Customer::create(['nama' => 'Budi', 'no_hp' => '081111']);
        Setting::updateOrCreate(['key' => 'nama_toko'], ['value' => 'JUNEO PHONE']);
        Setting::flushCache();

        // Create some services across different dates
        $s1 = Service::create([
            'service_number' => 'SRV-' . now()->format('Ymd') . '-00001',
            'tanggal' => now()->startOfMonth()->addDays(5),
            'customer_id' => $customer->id,
            'type_hp' => 'iPhone 12',
            'kerusakan' => json_encode(['LCD Retak']),
            'jasa' => 500000,
            'pengeluaran' => 200000,
            'keuntungan' => 300000,
            'status' => 'selesai',
        ]);

        Service::create([
            'service_number' => 'SRV-' . now()->format('Ymd') . '-00002',
            'tanggal' => now()->startOfMonth()->addDays(15),
            'customer_id' => $customer->id,
            'type_hp' => 'Samsung S24',
            'kerusakan' => json_encode(['Batre Boros']),
            'jasa' => 800000,
            'pengeluaran' => 300000,
            'keuntungan' => 500000,
            'status' => 'diambil',
        ]);

        // Batal service — should NOT appear in laporan
        Service::create([
            'service_number' => 'SRV-' . now()->format('Ymd') . '-00003',
            'tanggal' => now()->startOfMonth()->addDays(10),
            'customer_id' => $customer->id,
            'type_hp' => 'Xiaomi',
            'kerusakan' => json_encode(['LCD Retak']),
            'jasa' => 1000000,
            'pengeluaran' => 500000,
            'keuntungan' => 500000,
            'status' => 'batal',
        ]);

        // 12. Biaya Harian
        $today = now()->format('Y-m-d');
        $this->post('/biaya-harian', [
            'tanggal' => $today,
            'items' => [
                ['kategori' => 'bank_jago', 'jumlah' => '50000', 'keterangan' => 'Biaya admin'],
                ['kategori' => 'fee_edi', 'jumlah' => '60000', 'keterangan' => 'Fee harian'],
            ],
        ])->assertSessionHas('success');

        $this->assertEquals(2, BiayaHarian::whereDate('tanggal', $today)->count());

        // 13. Verifikasi data service
        $this->assertEquals(2, Service::where('status', '!=', 'batal')->count());
        $this->assertEquals(1300000, (int) Service::where('status', '!=', 'batal')->sum('jasa'));
        $this->assertEquals(500000, (int) Service::where('status', '!=', 'batal')->sum('pengeluaran'));

        // Service batal tidak masuk hitungan
        $this->assertEquals(3, Service::count());

        // CSV export
        $tahun = now()->format('Y');
        $this->get('/reports/export-csv?tahun=' . $tahun)
            ->assertStatus(200);
    }

    /** PHASE 4: Backup & Restore */
    public function test_phase_4_backup_and_restore()
    {
        $this->actingAs($this->user);

        // Create test data
        $customer = Customer::create(['nama' => 'Test', 'no_hp' => '081111']);
        for ($i = 0; $i < 3; $i++) {
            Service::create([
                'service_number' => 'SRV-' . now()->format('Ymd') . '-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'tanggal' => now(),
                'customer_id' => $customer->id,
                'type_hp' => 'iPhone',
                'kerusakan' => json_encode(['LCD Retak']),
                'jasa' => 500000,
                'pengeluaran' => 200000,
                'keuntungan' => 300000,
                'status' => 'menunggu',
            ]);
        }
        BiayaHarian::create([
            'tanggal' => now()->format('Y-m-d'),
            'kategori' => 'bank_jago',
            'nama' => 'Test Biaya',
            'jumlah' => 50000,
        ]);

        // Data page loads
        $this->get('/data')->assertStatus(200);

        // Download backup
        $this->post('/data/backup')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Generate backup file for restore test
        $backupPath = 'test-backup-' . uniqid() . '.xlsx';
        Excel::store(new BackupExport, $backupPath);
        $fullPath = Storage::path($backupPath);

        // Delete existing data to verify restore works
        Service::query()->delete();
        Customer::query()->delete();
        BiayaHarian::query()->delete();
        $this->assertEquals(0, Service::count());
        $this->assertEquals(0, Customer::count());

        // Upload backup to restore
        $uploadedFile = new UploadedFile(
            $fullPath, 'backup.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true
        );
        $this->post('/data/restore', ['file' => $uploadedFile])
            ->assertSessionHas('success');

        // Verify data restored
        $this->assertEquals(3, Service::count());
        $this->assertEquals(1, Customer::count());

        // Clean up
        Storage::delete($backupPath);
    }

    /** PHASE 5: Edge Cases */
    public function test_phase_5_edge_cases()
    {
        $this->actingAs($this->user);

        $customer = Customer::create(['nama' => 'Test', 'no_hp' => '080000']);

        // 16. Input modal/jasa dengan format rupiah (titik sebagai ribuan)
        $this->post('/services', [
            'tanggal' => now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'items' => [
                [
                    'type_hp' => 'iPhone 12',
                    'kerusakan' => json_encode(['LCD Retak']),
                    'jasa' => '1.500.000',
                    'pengeluaran' => '500.000',
                ],
            ],
        ])->assertSessionHas('success');

        $service = Service::first();
        // Regex seharusnya mengubah "1.500.000" menjadi "1500000"
        $this->assertEquals(1500000, (int) $service->jasa);
        $this->assertEquals(500000, (int) $service->pengeluaran);

        // 17. Service batal — data pelanggan tetap ada
        $customer2 = Customer::create(['nama' => 'Test Batal', 'no_hp' => '089999']);
        $this->post('/services', [
            'tanggal' => now()->format('Y-m-d'),
            'customer_id' => $customer2->id,
            'items' => [
                [
                    'type_hp' => 'Xiaomi',
                    'kerusakan' => json_encode(['Batre Boros']),
                    'jasa' => '300000',
                    'pengeluaran' => '100000',
                ],
            ],
        ]);

        $batalService = Service::where('customer_id', $customer2->id)->first();
        $this->putJson('/services/' . $batalService->id . '/status', ['status' => 'batal'])
            ->assertJson(['success' => true]);

        // Pelanggan tetap ada
        $this->assertNotNull(Customer::find($customer2->id));

        // Service batal tidak masuk laporan
        $tahun = now()->format('Y');
        $reportResponse = $this->get('/reports?tahun=' . $tahun);
        $reportResponse->assertDontSee($batalService->service_number);

        // 18. Multi-kerusakan — cek tampilan
        $kerusakanInput = ['LCD Retak', 'Batre Boros', 'Touch ID Rusak'];
        $kerusakanJson = json_encode($kerusakanInput);
        // Verify the JSON is valid before sending
        $this->assertNotEmpty($kerusakanJson);
        $this->assertStringContainsString('LCD Retak', $kerusakanJson);
        $this->assertStringContainsString('Touch ID Rusak', $kerusakanJson);

        $this->post('/services', [
            'tanggal' => now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'items' => [
                [
                    'type_hp' => 'iPhone 12',
                    'kerusakan' => $kerusakanJson,
                    'jasa' => '1000000',
                    'pengeluaran' => '400000',
                ],
            ],
        ])->assertSessionHas('success');

        $multiService = Service::where('customer_id', $customer->id)
            ->orderBy('id', 'desc')->first();
        $this->assertNotNull($multiService, 'Multi-kerusakan service not found');
        $multiService->refresh();
        $this->assertCount(1, $multiService->serviceItems);
        $item = $multiService->serviceItems->first()->refresh();

        $raw = \Illuminate\Support\Facades\DB::table('service_items')
            ->where('id', $item->id)->value('kerusakan');

        $this->assertEquals('["LCD Retak","Batre Boros","Touch ID Rusak"]', $raw,
            'Raw DB kerusakan does not match. Got: ' . $raw);
        $kerusakan = $item->kerusakan;
        $this->assertIsArray($kerusakan, 'kerusakan is not array. Raw: ' . $raw . ' Cast: ' . json_encode($kerusakan));
        $this->assertCount(3, $kerusakan, 'Count mismatch. Raw: ' . $raw . ' Cast: ' . json_encode($kerusakan));
        $this->assertContains('LCD Retak', $kerusakan);
        $this->assertContains('Batre Boros', $kerusakan);
        $this->assertContains('Touch ID Rusak', $kerusakan);

        // Show page should display all kerusakan
        $this->get('/services/' . $multiService->id)->assertSee('LCD Retak')->assertSee('Touch ID Rusak');

        // 19. Nota PDF — teknisi default "Owner"
        $serviceWithoutTeknisi = Service::create([
            'service_number' => 'SRV-' . now()->format('Ymd') . '-00999',
            'tanggal' => now(),
            'customer_id' => $customer->id,
            'type_hp' => 'iPhone',
            'kerusakan' => json_encode(['LCD Retak']),
            'jasa' => 300000,
            'pengeluaran' => 100000,
            'keuntungan' => 200000,
            'status' => 'menunggu',
            'teknisi' => null,
        ]);
        $this->get('/services/' . $serviceWithoutTeknisi->id . '/print-pdf')
            ->assertStatus(200);
    }

    /** PHASE 5b: Delete damage category */
    public function test_phase_5b_delete_damage_category()
    {
        $this->actingAs($this->user);

        $cat = DamageCategory::create(['name' => 'Test Hapus', 'sort_order' => 1, 'is_active' => true]);
        $this->assertNotNull(DamageCategory::find($cat->id));

        $this->delete('/settings/damage-categories/' . $cat->id)
            ->assertSessionHas('success');

        $this->assertNull(DamageCategory::find($cat->id));
    }
}
