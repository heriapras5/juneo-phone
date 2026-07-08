<?php

namespace App\Imports\Restore;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RestoreMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Pelanggan' => new CustomersRestoreSheetImport(),
            'Service' => new ServicesRestoreSheetImport(),
            'Kebutuhan Pribadi' => new BiayaHarianRestoreSheetImport(),
        ];
    }
}
