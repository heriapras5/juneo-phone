<?php

namespace App\Exports\Sheets;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithStyles
{
    private int $rowNumber = 0;

    public function collection()
    {
        return Customer::orderBy('nama')->get(['nama', 'no_hp', 'alamat']);
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'No. HP', 'Alamat'];
    }

    public function title(): string
    {
        return 'Pelanggan';
    }

    public function map($customer): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $customer->nama,
            $customer->no_hp ?? '',
            $customer->alamat ?? '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 30,
            'C' => 20,
            'D' => 40,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
