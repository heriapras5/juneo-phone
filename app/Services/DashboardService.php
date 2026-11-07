<?php

namespace App\Services;

use App\Models\BiayaHarian;
use App\Models\Service;
use Carbon\Carbon;

class DashboardService
{
    public function getChartData(string $period, Carbon $fromDate, Carbon $toDate): array
    {
        $services = Service::where('status', '!=', 'batal')
            ->whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)
            ->get();

        $biayaRows = BiayaHarian::whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)
            ->get();

        $groupKey = match ($period) {
            'daily' => fn($d) => $d->format('Y-m-d'),
            'weekly' => fn($d) => $d->format('o-\WW'),
            'yearly' => fn($d) => $d->format('Y'),
            default => fn($d) => $d->format('Y-m'),
        };

        $grouped = [];
        $current = $fromDate->copy()->startOfDay();

        while ($current->lte($toDate)) {
            $label = $groupKey($current);
            if (!isset($grouped[$label])) {
                $grouped[$label] = ['omzet' => 0, 'pengeluaran' => 0, 'keuntungan' => 0, 'kebutuhan_pribadi' => 0, 'count' => 0];
            }
            $current->addDay();
        }

        foreach ($services as $s) {
            $label = $groupKey($s->tanggal);
            $grouped[$label]['omzet'] += (float) $s->jasa;
            $grouped[$label]['pengeluaran'] += (float) $s->pengeluaran;
            $grouped[$label]['keuntungan'] += (float) $s->keuntungan;
            $grouped[$label]['count']++;
        }

        foreach ($biayaRows as $b) {
            $label = $groupKey(Carbon::parse($b->tanggal));
            $grouped[$label]['kebutuhan_pribadi'] += (float) $b->jumlah;
        }

        $grouped = collect($grouped);
        $profit = $grouped->map(fn($g) => $g['keuntungan'] - $g['kebutuhan_pribadi'])->values();

        $rawLabels = $grouped->keys();
        $labels = $rawLabels->map(function ($label) use ($period) {
            if ($period === 'yearly') {
                return $label;
            }
            $parsed = match ($period) {
                'weekly' => Carbon::parse($label . '-1')->startOfWeek(),
                default => Carbon::parse($label),
            };
            return match ($period) {
                'daily' => $parsed->format('d M'),
                'weekly' => $parsed->format('d M') . '-' . $parsed->copy()->endOfWeek()->format('d M'),
                default => $parsed->format('M Y'),
            };
        });

        return [
            'labels' => $labels,
            'omzet' => $grouped->pluck('omzet')->map(fn($v) => (float) $v),
            'pengeluaran' => $grouped->pluck('pengeluaran')->map(fn($v) => (float) $v),
            'profit' => $profit,
            'counts' => $grouped->pluck('count'),
        ];
    }

    public function getPeriodTotals(Carbon $fromDate, Carbon $toDate): object
    {
        $serviceQuery = Service::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)->where('status', '!=', 'batal');

        $biayaTotal = BiayaHarian::whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)
            ->sum('jumlah');

        $bankJago = BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)->where('kategori', 'bank_jago')->sum('jumlah');
        $lainnya = (int) BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)->where('kategori', 'lainnya')->sum('jumlah');

        $fees = [];

        $feeEdi = (int) BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)->where('kategori', 'fee_edi')->sum('jumlah');
        $feeEdiLabel = BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)
            ->where('kategori', 'fee_edi')->whereNotNull('nama')->latest()->value('nama') ?? 'Fee Edi';
        $feeEdiKet = BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)
            ->where('kategori', 'fee_edi')->whereNotNull('keterangan')->latest('tanggal')->value('keterangan');
        $fees[] = (object) [
            'nama' => 'Edi', 'kategori' => 'fee_edi', 'total' => $feeEdi,
            'label' => $feeEdiLabel, 'keterangan' => $feeEdiKet,
            'warna' => 'bg-blue-100 text-blue-700 border-blue-200',
        ];

        $feeNur = (int) BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)->where('kategori', 'fee_nur')->sum('jumlah');
        $feeNurLabel = BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)
            ->where('kategori', 'fee_nur')->whereNotNull('nama')->latest()->value('nama') ?? 'Fee Nur';
        $feeNurKet = BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)
            ->where('kategori', 'fee_nur')->whereNotNull('keterangan')->latest('tanggal')->value('keterangan');
        $fees[] = (object) [
            'nama' => 'Nur', 'kategori' => 'fee_nur', 'total' => $feeNur,
            'label' => $feeNurLabel, 'keterangan' => $feeNurKet,
            'warna' => 'bg-purple-100 text-purple-700 border-purple-200',
        ];

        $tambahanRows = BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)
            ->where('kategori', 'fee_tambahan')->get();
        foreach ($tambahanRows as $row) {
            $fees[] = (object) [
                'nama' => $row->nama,
                'kategori' => 'fee_tambahan',
                'total' => (int) $row->jumlah,
                'label' => $row->nama,
                'keterangan' => $row->keterangan,
                'warna' => 'bg-amber-100 text-amber-700 border-amber-200',
            ];
        }

        $omzet = $serviceQuery->sum('jasa');
        $pengeluaran = $serviceQuery->sum('pengeluaran');
        $keuntungan = $omzet - $pengeluaran;
        $kebutuhanPribadi = $biayaTotal;
        $profit = $keuntungan - $kebutuhanPribadi;

        return (object) [
            'services' => $serviceQuery->count(),
            'omzet' => $omzet,
            'pengeluaran' => $pengeluaran,
            'keuntungan' => $keuntungan,
            'kebutuhan_pribadi' => $kebutuhanPribadi,
            'profit' => $profit,
            'bank_jago' => $bankJago,
            'lainnya' => $lainnya,
            'fees' => $fees,
        ];
    }

    public function getPickupSummary(Carbon $fromDate, Carbon $toDate): object
    {
        $baseQuery = Service::where('status', '!=', 'batal')
            ->whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate);

        $takenQuery = (clone $baseQuery)->where('status', 'diambil');
        $pendingQuery = (clone $baseQuery)->whereIn('status', ['menunggu', 'dikerjakan', 'selesai']);

        return (object) [
            'taken_count' => $takenQuery->count(),
            'taken_profit' => (float) $takenQuery->sum('keuntungan'),
            'pending_count' => $pendingQuery->count(),
            'pending_profit' => (float) $pendingQuery->sum('keuntungan'),
        ];
    }

    public function getPreviousPeriodTotals(Carbon $fromDate, Carbon $toDate): object
    {
        $duration = $fromDate->diffInDays($toDate) + 1;

        $prevEnd = $fromDate->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($duration - 1)->startOfDay();

        $serviceQuery = Service::whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)->where('status', '!=', 'batal');

        $omzet = $serviceQuery->sum('jasa');
        $pengeluaran = $serviceQuery->sum('pengeluaran');
        $keuntungan = $omzet - $pengeluaran;

        $kebutuhanPribadi = BiayaHarian::whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)->sum('jumlah');

        $bankJago = BiayaHarian::whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)->where('kategori', 'bank_jago')->sum('jumlah');
        $lainnya = (int) BiayaHarian::whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)->where('kategori', 'lainnya')->sum('jumlah');

        $profit = $keuntungan - $kebutuhanPribadi;

        return (object) [
            'services' => $serviceQuery->count(),
            'omzet' => $omzet,
            'pengeluaran' => $pengeluaran,
            'keuntungan' => $keuntungan,
            'kebutuhan_pribadi' => $kebutuhanPribadi,
            'profit' => $profit,
            'bank_jago' => $bankJago,
            'lainnya' => $lainnya,
        ];
    }

    public function deriveDateRange(?string $period, ?string $from, ?string $to): array
    {
        if ($from && $to) {
            return [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ];
        }

        $now = now();
        $fromDate = match ($period) {
            'daily' => $now->copy()->startOfDay(),
            'weekly' => $now->copy()->startOfWeek(),
            'yearly' => $now->copy()->startOfYear(),
            default => $now->copy()->startOfMonth(),
        };
        $toDate = match ($period) {
            'daily' => $now->copy()->endOfDay(),
            'weekly' => $now->copy()->endOfWeek(),
            'yearly' => $now->copy()->endOfYear(),
            default => $now->copy()->endOfMonth(),
        };

        return [$fromDate, $toDate];
    }
}
