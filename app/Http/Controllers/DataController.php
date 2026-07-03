<?php

namespace App\Http\Controllers;

use App\Exports\BackupExport;
use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\GajiKaryawan;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataController extends Controller
{
    public function index()
    {
        $serviceCount = Service::count();
        $customerCount = Customer::count();
        $biayaCount = BiayaHarian::count();
        $gajiCount = GajiKaryawan::count();

        return view('data.index', compact('serviceCount', 'customerCount', 'biayaCount', 'gajiCount'));
    }

    public function backup()
    {
        $filename = 'backup-juneo-phone-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new BackupExport, $filename);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|string|in:RESET',
        ]);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }
        DB::table('service_histories')->truncate();
        DB::table('service_items')->truncate();
        Service::truncate();
        Customer::truncate();
        BiayaHarian::truncate();
        GajiKaryawan::truncate();
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        return redirect()->route('data.index')
            ->with('success', 'Semua data berhasil direset.');
    }
}
