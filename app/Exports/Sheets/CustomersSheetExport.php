<?php

namespace App\Exports\Sheets;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersSheetExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    public function collection()
    {
        return Customer::orderBy('nama')->get(['nama', 'no_hp', 'alamat']);
    }

    public function headings(): array
    {
        return ['Nama', 'No. HP', 'Alamat'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 40,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
