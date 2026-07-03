<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ServicesSheetImport(),
        ];
    }
}
