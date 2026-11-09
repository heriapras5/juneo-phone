<?php

namespace App\Http\Controllers;

use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\Service;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(DashboardService $service)
    {
        $period = request('period');
        $from   = request('from');
        $to     = request('to');

        if (!$period && !$from && !$to) {
            $period = 'daily';
        } else {
            $period = $period ?: ($from && $to ? 'custom' : 'daily');
        }

        [$fromDate, $toDate] = $service->deriveDateRange($period, $from, $to);

        $totalCustomers = Customer::count();

        $chartData = $service->getChartData($period, $fromDate, $toDate);

        $recentServices = Service::with('customer')
            ->where('status', '!=', 'batal')
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();

        $periodTotals = $service->getPeriodTotals($fromDate, $toDate);
        $prevPeriodTotals = $service->getPreviousPeriodTotals($fromDate, $toDate);
        $statusBreakdown = $service->getStatusBreakdown($fromDate, $toDate, $periodTotals);
        $statusCounts = collect($statusBreakdown)->map(fn ($item) => $item['count'])->toArray();
        $pickupSummary = $service->getPickupSummary($fromDate, $toDate, $statusBreakdown);
        $summaryPeriodLabel = $service->formatPeriodSummaryLabel($fromDate, $toDate, $period, (bool) ($from && $to));

        $changeOmzet   = $prevPeriodTotals->omzet > 0 ? round((($periodTotals->omzet - $prevPeriodTotals->omzet) / $prevPeriodTotals->omzet) * 100, 1) : ($periodTotals->omzet > 0 ? null : 0);
        $changePengeluaran = $prevPeriodTotals->pengeluaran > 0 ? round((($periodTotals->pengeluaran - $prevPeriodTotals->pengeluaran) / $prevPeriodTotals->pengeluaran) * 100, 1) : ($periodTotals->pengeluaran > 0 ? null : 0);
        $changeKebutuhan = $prevPeriodTotals->kebutuhan_pribadi > 0 ? round((($periodTotals->kebutuhan_pribadi - $prevPeriodTotals->kebutuhan_pribadi) / $prevPeriodTotals->kebutuhan_pribadi) * 100, 1) : ($periodTotals->kebutuhan_pribadi > 0 ? null : 0);
        $changeProfit   = $prevPeriodTotals->profit > 0 ? round((($periodTotals->profit - $prevPeriodTotals->profit) / $prevPeriodTotals->profit) * 100, 1) : ($periodTotals->profit > 0 ? null : 0);

        $biayaCount = BiayaHarian::whereDate('tanggal', now()->toDateString())->exists();

        $isPeriodComplete = $toDate->toDateString() < now()->toDateString();

        return view('dashboard.index', compact(
            'totalCustomers',
            'chartData',
            'period',
            'fromDate',
            'toDate',
            'recentServices',
            'statusCounts',
            'statusBreakdown',
            'periodTotals',
            'prevPeriodTotals',
            'pickupSummary',
            'summaryPeriodLabel',
            'biayaCount',
            'isPeriodComplete',
            'changeOmzet',
            'changePengeluaran',
            'changeKebutuhan',
            'changeProfit',
        ));
    }

    public function recentServices(): JsonResponse
    {
        $services = Service::with('customer')
            ->where('status', '!=', 'batal')
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'customer_name' => $s->customer?->nama ?? '-',
                'type_hp' => $s->type_hp,
                'kerusakan' => is_string($s->kerusakan) ? json_decode($s->kerusakan, true) : $s->kerusakan,
                'status' => $s->status,
                'tanggal' => $s->tanggal->format('d/m'),
            ]);

        return response()->json([
            'recentServices' => $services,
        ]);
    }

    public function data(DashboardService $service): JsonResponse
    {
        $period = request('period', 'daily');
        $from   = request('from');
        $to     = request('to');

        [$fromDate, $toDate] = $service->deriveDateRange($period, $from, $to);

        $chartPeriod = $period;
        if ($from && $to) {
            $days = $fromDate->diffInDays($toDate) + 1;
            $chartPeriod = match (true) {
                $days <= 31  => 'daily',
                $days <= 90  => 'weekly',
                default      => 'monthly',
            };
        }
        $chartData = $service->getChartData($chartPeriod, $fromDate, $toDate);

        $periodTotals = $service->getPeriodTotals($fromDate, $toDate);
        $prevTotals   = $service->getPreviousPeriodTotals($fromDate, $toDate);
        $statusBreakdown = $service->getStatusBreakdown($fromDate, $toDate, $periodTotals);
        $statusCounts = collect($statusBreakdown)->map(fn ($item) => $item['count'])->toArray();
        $pickupSummary = $service->getPickupSummary($fromDate, $toDate, $statusBreakdown);
        $summaryPeriodLabel = $service->formatPeriodSummaryLabel($fromDate, $toDate, $period, (bool) ($from && $to));

        $isComplete   = $toDate->toDateString() < now()->toDateString();
        $biayaCount   = BiayaHarian::whereDate('tanggal', now()->toDateString())->exists();

        $changeOmzet   = $prevTotals->omzet > 0 ? round((($periodTotals->omzet - $prevTotals->omzet) / $prevTotals->omzet) * 100, 1) : ($periodTotals->omzet > 0 ? null : 0);
        $changePengeluaran = $prevTotals->pengeluaran > 0 ? round((($periodTotals->pengeluaran - $prevTotals->pengeluaran) / $prevTotals->pengeluaran) * 100, 1) : ($periodTotals->pengeluaran > 0 ? null : 0);
        $changeKebutuhan = $prevTotals->kebutuhan_pribadi > 0 ? round((($periodTotals->kebutuhan_pribadi - $prevTotals->kebutuhan_pribadi) / $prevTotals->kebutuhan_pribadi) * 100, 1) : ($periodTotals->kebutuhan_pribadi > 0 ? null : 0);
        $changeProfit   = $prevTotals->profit > 0 ? round((($periodTotals->profit - $prevTotals->profit) / $prevTotals->profit) * 100, 1) : ($periodTotals->profit > 0 ? null : 0);

        return response()->json([
            'fromDate'      => $fromDate->format('d M Y'),
            'toDate'        => $toDate->format('d M Y'),
            'fromDateInput' => $fromDate->format('Y-m-d'),
            'toDateInput'   => $toDate->format('Y-m-d'),
            'period'        => $period,
            'hasCustomRange' => $from && $to,
            'isComplete'    => $isComplete,
            'biayaCount'    => $biayaCount,

            'chartData'     => $chartData,
            'totalCustomers' => Customer::count(),

            'omzet'            => $periodTotals->omzet,
            'pengeluaran'      => $periodTotals->pengeluaran,
            'keuntungan'       => $periodTotals->keuntungan,
            'kebutuhan_pribadi' => $periodTotals->kebutuhan_pribadi,
            'profit'           => $periodTotals->profit,
            'services'         => $periodTotals->services,
            'bank_jago'        => $periodTotals->bank_jago,
            'lainnya'          => $periodTotals->lainnya,
            'fees'             => $periodTotals->fees,
            'pickupSummary'    => $pickupSummary,
            'statusBreakdown'  => $statusBreakdown,
            'statusCounts'     => $statusCounts,
            'summaryPeriodLabel' => $summaryPeriodLabel,

            'changeOmzet'       => $changeOmzet,
            'changePengeluaran' => $changePengeluaran,
            'changeKebutuhan'   => $changeKebutuhan,
            'changeProfit'      => $changeProfit,
        ]);
    }
}
