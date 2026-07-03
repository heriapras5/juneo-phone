<?php

namespace App\Exports;

use App\Exports\Sheets\ServicesSheetExport;
use App\Exports\Sheets\CustomersSheetExport;
use App\Exports\Sheets\BiayaHarianSheetExport;
use App\Exports\Sheets\GajiKaryawanSheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BackupExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ServicesSheetExport,
            new CustomersSheetExport,
            new BiayaHarianSheetExport,
            new GajiKaryawanSheetExport,
        ];
    }
}
