<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServicesTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Tanggal',
            'Pelanggan',
            'Type HP',
            'Kerusakan',
            'Pengeluaran',
            'Jasa',
            'Teknisi',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        return [
            [
                now()->format('Y-m-d'),
                'Contoh Nama Pelanggan',
                'iPhone 12',
                'Ganti baterai',
                '50000',
                '150000',
                'Adit',
                '',
            ],
        ];
    }
}
