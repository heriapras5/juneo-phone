<?php

namespace App\Imports\Restore;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CustomersRestoreSheetImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $nama = trim($row['nama'] ?? '');
        if (empty($nama)) {
            return null;
        }

        return Customer::firstOrCreate(
            ['nama' => $nama],
            [
                'no_hp' => $row['no_hp'] ?? null,
                'alamat' => $row['alamat'] ?? null,
            ]
        );
    }
}
