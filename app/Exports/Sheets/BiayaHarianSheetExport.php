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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BiayaHarianSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithColumnFormatting, WithStyles, WithEvents
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
            'A' => 5,
            'B' => 14,
            'C' => 20,
            'D' => 28,
            'E' => 18,
            'F' => 30,
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $rowCount = $sheet->getHighestRow();
                
                // Header styling
                $sheet->getStyle('A1:F1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFB8860B');
                $sheet->getStyle('A1:F1')->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                $sheet->getStyle('A1:F1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                if ($rowCount > 1) {
                    $sheet->getStyle('A2:F' . $rowCount)
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
            },
        ];
    }
}
