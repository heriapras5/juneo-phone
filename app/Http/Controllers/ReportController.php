<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Exports\ServicesExport;
use App\Imports\MultiSheetImport;
use App\Imports\ServicesSheetImport;
use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $tahun = request('tahun', now()->format('Y'));
        $from  = request('from');
        $to    = request('to');
        $customerId = request('customer_id');

        $query = Service::with(['customer', 'serviceItems'])
            ->where('status', '!=', 'batal')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereDate('tanggal', '>=', $from)
                  ->whereDate('tanggal', '<=', $to);
            }, function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun);
            })
            ->when($customerId, fn($q, $v) => $q->where('customer_id', $v))
            ->orderBy('tanggal', 'desc');

        $services = $query->paginate(10)->withQueryString();

        $customers = Customer::orderBy('nama')->limit(200)->get(['id', 'nama', 'no_hp']);

        $tahunTersedia = Service::where('status', '!=', 'batal')
            ->select('tanggal')
            ->distinct()
            ->pluck('tanggal')
            ->map(fn($d) => (int) \Carbon\Carbon::parse($d)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        return view('reports.index', compact(
            'services', 'customers', 'tahun', 'tahunTersedia', 'from', 'to'
        ));
    }

    public function exportExcel()
    {
        $from       = request('from');
        $to         = request('to');
        $tahun      = $from && $to ? null : (int) request('tahun', now()->format('Y'));
        $customerId = request('customer_id') ? (int) request('customer_id') : null;
        $namaToko   = Setting::getValue('nama_toko', 'Juneo Phone');
        $slug       = str_replace(' ', '-', $namaToko);
        $dateLabel  = $tahun ?? $from . '-' . $to;

        return Excel::download(
            new LaporanExport($tahun, null, $namaToko, $customerId),
            'Laporan-Service-' . $dateLabel . '-' . $slug . '.xlsx'
        );
    }

    public function exportCsv()
    {
        $from       = request('from');
        $to         = request('to');
        $tahun      = (int) request('tahun', now()->format('Y'));
        $customerId = request('customer_id') ? (int) request('customer_id') : null;
        $namaToko   = Setting::getValue('nama_toko', 'Juneo Phone');
        $slug       = str_replace(' ', '-', $namaToko);

        $dateFrom = $from ?? $tahun . '-01-01';
        $dateTo   = $to   ?? $tahun . '-12-31';
        $dateLabel = $from && $to ? $from . '-' . $to : (string) $tahun;

        return Excel::download(
            new ServicesExport($dateFrom, $dateTo, $customerId),
            'Laporan-Service-' . $dateLabel . '-' . $slug . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf()
    {
        $from       = request('from');
        $to         = request('to');
        $tahun      = (int) request('tahun', now()->format('Y'));
        $customerId = request('customer_id') ? (int) request('customer_id') : null;
        $namaToko   = Setting::getValue('nama_toko', 'Juneo Phone');
        $slug       = str_replace(' ', '-', $namaToko);

        $query = Service::with(['customer', 'serviceItems'])
            ->where('status', '!=', 'batal')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereDate('tanggal', '>=', $from)
                  ->whereDate('tanggal', '<=', $to);
            }, function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun);
            })
            ->when($customerId, fn($q, $v) => $q->where('customer_id', $v))
            ->orderBy('tanggal')
            ->orderBy('created_at');

        $services = $query->cursor();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($from && $to) {
            $bulanList = [];
            foreach ($services as $service) {
                $key = $service->tanggal->format('Y-m');
                $bulanList[$key][] = $service;
            }
            $rekapBulan = [];
            foreach ($bulanList as $ym => $data) {
                [$year, $month] = explode('-', $ym);
                $biayaHarian = BiayaHarian::whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->sum('jumlah');

                $rekapBulan[$ym] = [
                    'totalPengeluaran' => collect($data)->sum('pengeluaran'),
                    'totalJasa' => collect($data)->sum('jasa'),
                    'totalKeuntungan' => collect($data)->sum('keuntungan'),
                    'biayaHarian' => $biayaHarian,
                ];
            }

            $grandPengeluaran = array_sum(array_column($rekapBulan, 'totalPengeluaran'));
            $grandJasa = array_sum(array_column($rekapBulan, 'totalJasa'));
            $grandBiayaHarian = array_sum(array_column($rekapBulan, 'biayaHarian'));
        } else {
            $bulanList = [];
            foreach ($services as $service) {
                $month = (int) $service->tanggal->format('m');
                $bulanList[$month][] = $service;
            }

            $rekapBulan = [];
            foreach ($bulanList as $month => $data) {
                $biayaHarian = BiayaHarian::whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $month)
                    ->sum('jumlah');

                $rekapBulan[$month] = [
                    'totalPengeluaran' => collect($data)->sum('pengeluaran'),
                    'totalJasa' => collect($data)->sum('jasa'),
                    'totalKeuntungan' => collect($data)->sum('keuntungan'),
                    'biayaHarian' => $biayaHarian,
                ];
            }

            $grandPengeluaran = array_sum(array_column($rekapBulan, 'totalPengeluaran'));
            $grandJasa = array_sum(array_column($rekapBulan, 'totalJasa'));
            $grandBiayaHarian = array_sum(array_column($rekapBulan, 'biayaHarian'));
        }

        $customerName = null;
        if ($customerId) {
            $customer = Customer::find($customerId);
            $customerName = $customer?->nama;
        }

        $dateLabel = $from && $to ? $from . '-' . $to : 'Tahun-' . $tahun;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.pdf-services-yearly', compact(
            'bulanList', 'rekapBulan', 'namaBulan',
            'grandPengeluaran', 'grandJasa',
            'grandBiayaHarian',
            'tahun', 'namaToko', 'customerName'
        ));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Laporan-Service-' . $dateLabel . '-' . $slug . '.pdf');
    }

    public function importForm()
    {
        return view('reports.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        ServicesSheetImport::$skippedBatal = 0;

        try {
            Excel::import(new MultiSheetImport, $request->file('file'));

            $message = 'Data berhasil diimport.';
            if (ServicesSheetImport::$skippedBatal > 0) {
                $message .= ' (' . ServicesSheetImport::$skippedBatal . ' baris status "batal" dilewati)';
            }

            return redirect()->route('reports.import')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
