<?php

namespace App\Http\Controllers;

use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\Service;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(DashboardService $service)
    {
        $period = request('period');
        $from   = request('from');
        $to     = request('to');

        // Auto-detect period jika tidak ada filter manual
        if (!$period && !$from && !$to) {
            $latestService = Service::where('status', '!=', 'batal')
                ->orderBy('tanggal', 'desc')
                ->value('tanggal');

            if ($latestService) {
                $diffDays = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($latestService)->startOfDay());
                $period = match (true) {
                    $diffDays <= 1  => 'daily',
                    $diffDays <= 7  => 'weekly',
                    default         => 'monthly',
                };
            } else {
                $period = 'monthly';
            }
        } else {
            $period = $period ?: ($from && $to ? 'custom' : 'monthly');
        }

        [$fromDate, $toDate] = $service->deriveDateRange($period, $from, $to);

        $totalCustomers = Customer::count();

        $chartData = $service->getChartData($period, $fromDate, $toDate);

        $recentServices = Service::with('customer')
            ->where('status', '!=', 'batal')
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();

        $statusCounts = Service::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $periodTotals = $service->getPeriodTotals($fromDate, $toDate);
        $prevPeriodTotals = $service->getPreviousPeriodTotals($fromDate, $toDate);

        $profitMargin = $periodTotals->revenue > 0 ? round(($periodTotals->net_profit / $periodTotals->revenue) * 100, 1) : 0;
        $avgProfitPerService = $periodTotals->services > 0 ? round($periodTotals->net_profit / $periodTotals->services) : 0;

        $changeRev   = $prevPeriodTotals->revenue > 0 ? round((($periodTotals->revenue - $prevPeriodTotals->revenue) / $prevPeriodTotals->revenue) * 100, 1) : ($periodTotals->revenue > 0 ? null : 0);
        $changeBiaya = $prevPeriodTotals->biaya_harian > 0 ? round((($periodTotals->biaya_harian - $prevPeriodTotals->biaya_harian) / $prevPeriodTotals->biaya_harian) * 100, 1) : ($periodTotals->biaya_harian > 0 ? null : 0);
        $changeNet   = $prevPeriodTotals->net_profit > 0 ? round((($periodTotals->net_profit - $prevPeriodTotals->net_profit) / $prevPeriodTotals->net_profit) * 100, 1) : ($periodTotals->net_profit > 0 ? null : 0);

        $prevMargin = $prevPeriodTotals->revenue > 0 ? round(($prevPeriodTotals->net_profit / $prevPeriodTotals->revenue) * 100, 1) : 0;
        $changeMargin = $prevMargin > 0 ? round((($profitMargin - $prevMargin) / $prevMargin) * 100, 1) : ($profitMargin > 0 ? null : 0);
        $prevAvgProfit = $prevPeriodTotals->services > 0 ? round($prevPeriodTotals->net_profit / $prevPeriodTotals->services) : 0;
        $changeAvgProfit = $prevAvgProfit > 0 ? round((($avgProfitPerService - $prevAvgProfit) / $prevAvgProfit) * 100, 1) : ($avgProfitPerService > 0 ? null : 0);

        $prevJago = $prevPeriodTotals->bank_jago ?? 0;
        $changeJago = $prevJago > 0 ? round((($periodTotals->bank_jago - $prevJago) / $prevJago) * 100, 1) : ($periodTotals->bank_jago > 0 ? null : 0);

        $biayaCount = BiayaHarian::whereDate('tanggal', now()->toDateString())->exists();

        $isPeriodComplete = $toDate->toDateString() < now()->toDateString();

        $hariKerja = $service->countWorkingDays($fromDate, $toDate);

        return view('dashboard.index', compact(
            'totalCustomers',
            'profitMargin',
            'avgProfitPerService',
            'chartData',
            'period',
            'fromDate',
            'toDate',
            'recentServices',
            'statusCounts',
            'periodTotals',
            'prevPeriodTotals',
            'biayaCount',
            'isPeriodComplete',
            'hariKerja',
            'changeRev',
            'changeBiaya',
            'changeNet',
            'changeMargin',
            'changeAvgProfit',
            'changeJago',
            'prevMargin',
            'prevAvgProfit',
            'prevJago',
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

        $statusCounts = Service::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return response()->json([
            'recentServices' => $services,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function data(DashboardService $service): JsonResponse
    {
        $period = request('period', 'monthly');
        $from   = request('from');
        $to     = request('to');

        [$fromDate, $toDate] = $service->deriveDateRange($period, $from, $to);

        $chartData = $service->getChartData($period, $fromDate, $toDate);

        $periodTotals = $service->getPeriodTotals($fromDate, $toDate);
        $prevTotals   = $service->getPreviousPeriodTotals($fromDate, $toDate);

        $profitMargin = $periodTotals->revenue > 0 ? round(($periodTotals->net_profit / $periodTotals->revenue) * 100, 1) : 0;
        $avgProfit    = $periodTotals->services > 0 ? round($periodTotals->net_profit / $periodTotals->services) : 0;
        $isComplete   = $toDate->toDateString() < now()->toDateString();
        $hariKerja    = $service->countWorkingDays($fromDate, $toDate);
        $biayaCount   = BiayaHarian::whereDate('tanggal', now()->toDateString())->exists();

        $changeRev   = $prevTotals->revenue > 0 ? round((($periodTotals->revenue - $prevTotals->revenue) / $prevTotals->revenue) * 100, 1) : ($periodTotals->revenue > 0 ? null : 0);
        $changeBiaya = $prevTotals->biaya_harian > 0 ? round((($periodTotals->biaya_harian - $prevTotals->biaya_harian) / $prevTotals->biaya_harian) * 100, 1) : ($periodTotals->biaya_harian > 0 ? null : 0);
        $changeNet   = $prevTotals->net_profit > 0 ? round((($periodTotals->net_profit - $prevTotals->net_profit) / $prevTotals->net_profit) * 100, 1) : ($periodTotals->net_profit > 0 ? null : 0);

        return response()->json([
            'fromDate'      => $fromDate->format('d M Y'),
            'toDate'        => $toDate->format('d M Y'),
            'fromDateInput' => $fromDate->format('Y-m-d'),
            'toDateInput'   => $toDate->format('Y-m-d'),
            'period'        => $period,
            'hasCustomRange' => $from && $to,
            'isComplete'    => $isComplete,
            'hariKerja'     => $hariKerja,
            'biayaCount'    => $biayaCount,

            'chartData'     => $chartData,
            'totalCustomers' => Customer::count(),

            'revenue'       => $periodTotals->revenue,
            'pengeluaran'   => $periodTotals->pengeluaran,
            'net_profit'    => $periodTotals->net_profit,
            'services'      => $periodTotals->services,
            'expenses'      => $periodTotals->expenses,
            'needs'         => $periodTotals->needs,
            'total_gaji'    => $periodTotals->total_gaji,
            'biaya_harian'  => $periodTotals->biaya_harian,
            'bank_jago'     => $periodTotals->bank_jago,
            'gaji_edi'      => $periodTotals->gaji_edi,
            'gaji_nur'      => $periodTotals->gaji_nur,

            'profitMargin'       => $profitMargin,
            'avgProfitPerService' => $avgProfit,

            'prevRevenue'   => $prevTotals->revenue,
            'prevBiaya'     => $prevTotals->biaya_harian,
            'prevNet'       => $prevTotals->net_profit,
            'prevServices'  => $prevTotals->services,

            'changeRev'   => $changeRev,
            'changeBiaya' => $changeBiaya,
            'changeNet'   => $changeNet,
        ]);
    }
}
