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
            ->whereNotIn('kategori', ['fee_edi', 'fee_nur'])
            ->get();

        $gajiRows = BiayaHarian::whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)
            ->whereIn('kategori', ['fee_edi', 'fee_nur'])
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
                $grouped[$label] = ['revenue' => 0, 'expenses' => 0, 'needs' => 0, 'profit' => 0, 'count' => 0, 'biaya' => 0, 'gaji' => 0];
            }
            $current->addDay();
        }

        foreach ($services as $s) {
            $label = $groupKey($s->tanggal);
            $grouped[$label]['revenue'] += (float) $s->jasa;
            $grouped[$label]['expenses'] += (float) $s->pengeluaran;
            $grouped[$label]['needs'] += (float) $s->kebutuhan_pribadi;
            $grouped[$label]['profit'] += (float) $s->keuntungan;
            $grouped[$label]['count']++;
        }

        foreach ($biayaRows as $b) {
            $label = $groupKey(Carbon::parse($b->tanggal));
            $grouped[$label]['biaya'] += (float) $b->jumlah;
        }

        foreach ($gajiRows as $g) {
            $label = $groupKey(Carbon::parse($g->tanggal));
            $grouped[$label]['gaji'] += (float) $g->jumlah;
        }

        $grouped = collect($grouped);
        $pengeluaran = $grouped->map(fn($g) => $g['expenses'] + $g['needs'] + $g['biaya'] + $g['gaji']);
        $netProfit = $grouped->map(fn($g) => $g['revenue'] - ($g['expenses'] + $g['needs'] + $g['biaya'] + $g['gaji']));

        $rawLabels = $grouped->keys();
        $labels = $rawLabels->map(function ($label) use ($period) {
            // Untuk 'yearly', label sudah berupa tahun (mis. "2026") — tidak perlu parse
            if ($period === 'yearly') {
                return $label;
            }
            $parsed = Carbon::parse($label);
            return match ($period) {
                'daily' => $parsed->format('d M'),
                'weekly' => $parsed->startOfWeek()->format('d M') . '-' . $parsed->endOfWeek()->format('d M'),
                default => $parsed->format('M Y'),
            };
        });

        return [
            'labels' => $labels,
            'revenue' => $grouped->pluck('revenue')->map(fn($v) => (float) $v),
            'pengeluaran' => $pengeluaran,
            'net_profit' => $netProfit,
            'counts' => $grouped->pluck('count'),
        ];
    }

    public function getPeriodTotals(Carbon $fromDate, Carbon $toDate): object
    {
        $serviceQuery = Service::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)->where('status', '!=', 'batal');

        $biayaHarian = BiayaHarian::whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)
            ->whereNotIn('kategori', ['fee_edi', 'fee_nur'])
            ->sum('jumlah');

        $gajiEdi = (int) BiayaHarian::whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)->where('kategori', 'fee_edi')->sum('jumlah');
        $gajiNur = (int) BiayaHarian::whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)->where('kategori', 'fee_nur')->sum('jumlah');

        $bankJago = BiayaHarian::whereDate('tanggal', '>=', $fromDate)->whereDate('tanggal', '<=', $toDate)->where('kategori', 'bank_jago')->sum('jumlah');

        $revenue = $serviceQuery->sum('jasa');
        $sparepart = $serviceQuery->sum('pengeluaran');
        $profit = $serviceQuery->sum('keuntungan');
        $needs = $serviceQuery->sum('kebutuhan_pribadi');
        $totalGaji = $gajiEdi + $gajiNur;
        $pengeluaran = $sparepart + $needs + $totalGaji + $biayaHarian;
        $netProfit = $revenue - $pengeluaran;

        return (object) [
            'services' => $serviceQuery->count(),
            'revenue' => $revenue,
            'expenses' => $sparepart,
            'needs' => $needs,
            'profit' => $profit,
            'pengeluaran' => $pengeluaran,
            'biaya_harian' => $biayaHarian,
            'gaji_edi' => $gajiEdi,
            'gaji_nur' => $gajiNur,
            'total_gaji' => $totalGaji,
            'bank_jago' => $bankJago,
            'net_profit' => $netProfit,
        ];
    }

    public function getPreviousPeriodTotals(Carbon $fromDate, Carbon $toDate): object
    {
        $duration = $fromDate->diffInDays($toDate) + 1;

        $prevEnd = $fromDate->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($duration - 1)->startOfDay();

        $serviceQuery = Service::whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)->where('status', '!=', 'batal');

        $revenue = $serviceQuery->sum('jasa');
        $sparepart = $serviceQuery->sum('pengeluaran');
        $profit = $serviceQuery->sum('keuntungan');
        $needs = $serviceQuery->sum('kebutuhan_pribadi');

        $biayaHarian = BiayaHarian::whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)
            ->whereNotIn('kategori', ['fee_edi', 'fee_nur'])->sum('jumlah');

        $biayaBankJago = BiayaHarian::whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)->where('kategori', 'bank_jago')->sum('jumlah');

        $gaji = (int) BiayaHarian::whereDate('tanggal', '>=', $prevStart)
            ->whereDate('tanggal', '<=', $prevEnd)
            ->whereIn('kategori', ['fee_edi', 'fee_nur'])->sum('jumlah');

        $pengeluaran = $sparepart + $needs + $gaji + $biayaHarian;
        $netProfit = $revenue - $pengeluaran;

        return (object) [
            'services' => $serviceQuery->count(),
            'revenue' => $revenue,
            'expenses' => $sparepart,
            'needs' => $needs,
            'profit' => $profit,
            'pengeluaran' => $pengeluaran,
            'biaya_harian' => $biayaHarian,
            'gaji' => $gaji,
            'bank_jago' => $biayaBankJago,
            'net_profit' => $netProfit,
        ];
    }

    public function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if ($current->dayOfWeek !== Carbon::SUNDAY) {
                $count++;
            }
            $current->addDay();
        }
        return $count;
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
