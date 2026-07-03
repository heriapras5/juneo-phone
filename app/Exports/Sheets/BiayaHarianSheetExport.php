<?php

namespace App\Exports\Sheets;

use App\Models\BiayaHarian;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BiayaHarianSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithColumnFormatting, WithStyles
{
    public function collection()
    {
        return BiayaHarian::orderBy('tanggal')->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kategori', 'Nama', 'Jumlah', 'Keterangan'];
    }

    public function map($item): array
    {
        return [
            $item->tanggal ? Carbon::parse($item->tanggal)->format('d/m/Y') : '',
            $item->kategori ?? '',
            $item->nama ?? '',
            $item->jumlah ?: 0,
            $item->keterangan ?? '',
        ];
    }

    public function title(): string
    {
        return 'Biaya Harian';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 20,
            'C' => 30,
            'D' => 15,
            'E' => 40,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '"Rp"#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
