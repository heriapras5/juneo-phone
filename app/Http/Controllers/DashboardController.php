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
            'periodTotals',
            'prevPeriodTotals',
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

        // Tentukan granularitas chart yang sesuai berdasarkan durasi rentang
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
            'gaji_edi'         => $periodTotals->gaji_edi,
            'gaji_nur'         => $periodTotals->gaji_nur,
            'lainnya'          => $periodTotals->lainnya,

            'changeOmzet'       => $changeOmzet,
            'changePengeluaran' => $changePengeluaran,
            'changeKebutuhan'   => $changeKebutuhan,
            'changeProfit'      => $changeProfit,
        ]);
    }
}
