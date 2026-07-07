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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BiayaHarianSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithColumnFormatting, WithStyles
{
    private int $rowNumber = 0;

    public function collection()
    {
        return BiayaHarian::orderBy('tanggal')->get();
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Kategori', 'Nama', 'Jumlah', 'Keterangan'];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $item->tanggal ? Carbon::parse($item->tanggal)->format('d/m/Y') : '',
            $item->kategori ?? '',
            $item->nama ?? '',
            $item->jumlah ?: 0,
            $item->keterangan ?? '',
        ];
    }

    public function title(): string
    {
        return 'Kebutuhan Pribadi';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 20,
            'D' => 30,
            'E' => 15,
            'F' => 40,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '"Rp"#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'E:E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }
}
