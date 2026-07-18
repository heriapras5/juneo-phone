<?php

namespace App\Console\Commands;

use App\Models\Helper;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTeknisiAdit extends Command
{
    protected $signature = 'teknisi:backfill-adit
                            {--dry-run : Preview changes without writing to database}';

    protected $description = 'Tambah helper Adit + backfill teknisi ke semua data service yang kosong';

    private string $teknisi = 'adit';
    private string $teknisiNama = 'Adit';
    private string $teknisiWarna = 'bg-blue-100 text-blue-700 border-blue-200';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $prefix = $dryRun ? '[DRY-RUN] ' : '';

        $this->line("{$prefix}Mulai backfill teknisi \"{$this->teknisiNama}\"...");
        $this->newLine();

        // ── 1. Helper ──
        $this->line("{$prefix}1. Cek helper \"{$this->teknisiNama}\"...");
        $helper = Helper::where('nama', $this->teknisiNama)->first();

        if ($helper) {
            $this->info("   Sudah ada (ID: {$helper->id}).");
        } else {
            if ($dryRun) {
                $this->warn("   Akan dibuat: nama={$this->teknisiNama}, role=teknisi, warna={$this->teknisiWarna}");
            } else {
                $helper = Helper::create([
                    'nama' => $this->teknisiNama,
                    'role' => 'teknisi',
                    'warna' => $this->teknisiWarna,
                ]);
                $this->info("   Berhasil dibuat (ID: {$helper->id}).");
            }
        }

        // ── 2. Services ──
        $this->newLine();
        $this->line("{$prefix}2. Backfill services.teknisi...");
        $serviceQuery = Service::where(function ($q) {
            $q->whereNull('teknisi')->orWhere('teknisi', '');
        });
        $serviceCount = $serviceQuery->count();
        $this->line("   Data kosong: {$serviceCount}");

        if ($serviceCount > 0) {
            if ($dryRun) {
                $this->warn("   Akan update {$serviceCount} service(s).");
            } else {
                $affected = $serviceQuery->update(['teknisi' => $this->teknisi]);
                $this->info("   Terupdate: {$affected} service(s).");
            }
        } else {
            $this->info("   Tidak ada yang perlu diupdate.");
        }

        // ── 3. Service Items ──
        $this->newLine();
        $this->line("{$prefix}3. Backfill service_items.teknisi...");
        $itemQuery = ServiceItem::where(function ($q) {
            $q->whereNull('teknisi')->orWhere('teknisi', '');
        });
        $itemCount = $itemQuery->count();
        $this->line("   Data kosong: {$itemCount}");

        if ($itemCount > 0) {
            if ($dryRun) {
                $this->warn("   Akan update {$itemCount} service_item(s).");
            } else {
                $affected = $itemQuery->update(['teknisi' => $this->teknisi]);
                $this->info("   Terupdate: {$affected} service_item(s).");
            }
        } else {
            $this->info("   Tidak ada yang perlu diupdate.");
        }

        // ── 4. Kerusakan JSON ──
        $this->newLine();
        $this->line("{$prefix}4. Backfill kerusakan JSON di service_items...");
        $allItems = ServiceItem::all();
        $jsonUpdated = 0;

        foreach ($allItems as $item) {
            $kerusakan = $item->kerusakan;
            if (!is_array($kerusakan) || empty($kerusakan)) {
                continue;
            }

            $changed = false;
            foreach ($kerusakan as &$kr) {
                if (is_array($kr)) {
                    $tek = $kr['teknisi'] ?? '';
                    if (empty($tek)) {
                        $kr['teknisi'] = $this->teknisi;
                        $changed = true;
                    }
                }
            }
            unset($kr);

            if ($changed) {
                $jsonUpdated++;
                if (!$dryRun) {
                    $item->update(['kerusakan' => $kerusakan]);
                }
            }
        }

        if ($jsonUpdated > 0) {
            if ($dryRun) {
                $this->warn("   Akan update JSON pada {$jsonUpdated} service_item(s).");
            } else {
                $this->info("   JSON terupdate: {$jsonUpdated} service_item(s).");
            }
        } else {
            $this->info("   Semua JSON sudah terisi.");
        }

        // ── Summary ──
        $this->newLine();
        if ($dryRun) {
            $this->warn('=== DRY-RUN SELESAI — Tidak ada perubahan nyata ===');
        } else {
            $this->info('=== BACKFILL SELESAI ===');
        }

        $countAll = Service::count();
        $countFilled = Service::whereNotNull('teknisi')->where('teknisi', '!=', '')->count();
        $this->line("Total service: {$countAll} | Terisi teknisi: {$countFilled}");

        return Command::SUCCESS;
    }
}
