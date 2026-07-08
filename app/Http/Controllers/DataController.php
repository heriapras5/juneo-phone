<?php

namespace App\Http\Controllers;

use App\Exports\BackupExport;
use App\Imports\Restore\RestoreMultiSheetImport;
use App\Models\BiayaHarian;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DataController extends Controller
{
    public function index()
    {
        $serviceCount = Service::count();
        $customerCount = Customer::count();
        $biayaCount = BiayaHarian::count();

        return view('data.index', compact('serviceCount', 'customerCount', 'biayaCount'));
    }

    public function backup()
    {
        $filename = 'backup-juneo-phone-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new BackupExport, $filename);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        try {
            Excel::import(new RestoreMultiSheetImport, $request->file('file'));

            return redirect()->route('data.index')
                ->with('success', 'Data berhasil direstorasi dari file backup.');
        } catch (\Exception $e) {
            return redirect()->route('data.index')
                ->with('error', 'Gagal merestorasi data: ' . $e->getMessage());
        }
    }
}
