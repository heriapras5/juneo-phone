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

    public function getStatusBreakdown(Carbon $fromDate, Carbon $toDate, ?object $periodTotals = null): array
    {
        $rows = Service::whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)
            ->selectRaw('status, COUNT(*) as total, SUM(jasa) as omzet, SUM(pengeluaran) as pengeluaran')
            ->groupBy('status')
            ->get();

        $breakdown = [];
        $metrics = [];

        foreach (array_keys(Service::statusLabels()) as $status) {
            $breakdown[$status] = ['count' => 0, 'profit' => 0];
            $metrics[$status] = ['count' => 0, 'omzet' => 0.0, 'pengeluaran' => 0.0, 'gross' => 0.0];
        }

        foreach ($rows as $row) {
            $status = $row->status;

            if (!isset($breakdown[$status])) {
                $breakdown[$status] = ['count' => 0, 'profit' => 0];
                $metrics[$status] = ['count' => 0, 'omzet' => 0.0, 'pengeluaran' => 0.0, 'gross' => 0.0];
            }

            $count = (int) $row->total;
            $breakdown[$status]['count'] = $count;

            if ($status === Service::STATUS_BATAL) {
                continue;
            }

            $omzet = (float) $row->omzet;
            $pengeluaran = (float) $row->pengeluaran;
            $metrics[$status] = [
                'count' => $count,
                'omzet' => $omzet,
                'pengeluaran' => $pengeluaran,
                'gross' => $omzet - $pengeluaran,
            ];
        }

        if (!$periodTotals) {
            $periodTotals = $this->getPeriodTotals($fromDate, $toDate);
        }

        $totalProfit = (int) round((float) $periodTotals->profit);
        $totalGross = 0.0;
        $totalOmzet = 0.0;
        $totalCount = 0;

        foreach ($metrics as $metric) {
            $totalGross += $metric['gross'];
            $totalOmzet += $metric['omzet'];
            $totalCount += $metric['count'];
        }

        $weightKey = $totalGross > 0 ? 'gross' : ($totalOmzet > 0 ? 'omzet' : ($totalCount > 0 ? 'count' : null));

        if ($weightKey !== null) {
            $allocatable = [];
            $weightTotal = 0.0;

            foreach ($metrics as $status => $metric) {
                if ($metric['count'] <= 0) {
                    continue;
                }

                $weight = (float) $metric[$weightKey];
                $allocatable[$status] = $weight;
                $weightTotal += $weight;
            }

            if ($weightTotal != 0.0) {
                $remaining = $totalProfit;
                $lastStatus = array_key_last($allocatable);

                foreach ($allocatable as $status => $weight) {
                    if ($status === $lastStatus) {
                        $profit = $remaining;
                    } else {
                        $profit = (int) round($totalProfit * ($weight / $weightTotal));
                    }

                    $breakdown[$status]['profit'] = $profit;
                    $remaining -= $profit;
                }
            }
        }

        return $breakdown;
    }

    public function formatPeriodSummaryLabel(Carbon $fromDate, Carbon $toDate, string $period, bool $hasCustomRange): string
    {
        if ($hasCustomRange) {
            if ($fromDate->isSameDay($toDate)) {
                return '(' . $fromDate->format('d M Y') . ')';
            }

            return '(' . $fromDate->format('d M Y') . ' s.d. ' . $toDate->format('d M Y') . ')';
        }

        $weeklyStart = $fromDate->format($fromDate->month === $toDate->month ? 'd' : 'd M');

        return match ($period) {
            'daily' => 'Harian (' . $fromDate->format('d M Y') . ')',
            'weekly' => 'Mingguan (' . $weeklyStart . ' s.d. ' . $toDate->format('d M Y') . ')',
            'monthly' => 'Bulanan (' . $fromDate->format('F Y') . ')',
            'yearly' => 'Tahunan (' . $fromDate->format('Y') . ')',
            default => '(' . $fromDate->format('d M Y') . ' s.d. ' . $toDate->format('d M Y') . ')',
        };
    }

    public function getPickupSummary(Carbon $fromDate, Carbon $toDate, ?object $periodTotals = null, ?array $statusBreakdown = null): object
    {
        if (!$periodTotals) {
            $periodTotals = $this->getPeriodTotals($fromDate, $toDate);
        }

        if ($statusBreakdown === null) {
            $statusBreakdown = $this->getStatusBreakdown($fromDate, $toDate, $periodTotals);
        }

        $takenCount = (int) ($statusBreakdown[Service::STATUS_DIAMBIL]['count'] ?? 0);
        $takenProfit = (int) ($statusBreakdown[Service::STATUS_DIAMBIL]['profit'] ?? 0);
        $pendingCount = 0;
        $pendingProfit = 0;

        foreach ([Service::STATUS_MENUNGGU, Service::STATUS_DIKERJAKAN, Service::STATUS_SELESAI] as $status) {
            $pendingCount += (int) ($statusBreakdown[$status]['count'] ?? 0);
            $pendingProfit += (int) ($statusBreakdown[$status]['profit'] ?? 0);
        }

        $totalProfit = (int) round((float) $periodTotals->profit);
        $pendingProfit += $totalProfit - ($takenProfit + $pendingProfit);

        return (object) [
            'taken_count' => $takenCount,
            'taken_profit' => $takenProfit,
            'pending_count' => $pendingCount,
            'pending_profit' => $pendingProfit,
            'total_profit' => $totalProfit,
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
