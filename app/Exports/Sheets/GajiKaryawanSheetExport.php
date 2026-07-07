<?php

namespace App\Exports\Sheets;

use App\Models\GajiKaryawan;
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

class GajiKaryawanSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithColumnFormatting, WithStyles
{
    private int $rowNumber = 0;

    public function collection()
    {
        return GajiKaryawan::orderBy('tanggal')->get();
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Karyawan', 'Jumlah'];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $item->tanggal ? Carbon::parse($item->tanggal)->format('d/m/Y') : '',
            $item->nama ?? $item->karyawan ?? '',
            $item->jumlah ?: 0,
        ];
    }

    public function title(): string
    {
        return 'Gaji Karyawan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 25,
            'D' => 15,
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
            'D:D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }
}
