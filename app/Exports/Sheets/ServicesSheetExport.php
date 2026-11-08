<?php

namespace App\Exports\Sheets;

use App\Models\Service;
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

class ServicesSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithColumnFormatting, WithStyles, WithEvents
{
    private int $rowNumber = 0;

    public function collection()
    {
        return Service::with('customer', 'serviceItems')
            ->where('status', '!=', 'batal')
            ->orderBy('tanggal')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Service',
            'Tanggal',
            'Pelanggan',
            'Type HP',
            'Kerusakan',
            'Pengeluaran',
            'Jasa',
            'Teknisi',
            'Keterangan',
            'Keuntungan',
        ];
    }

    public function map($service): array
    {
        $this->rowNumber++;

        $allTypes = collect([$service->type_hp])
            ->concat($service->serviceItems->pluck('type_hp'))
            ->unique()->values();
        $typesText = '- ' . $allTypes->implode(' | - ');

        $allKerusakan = collect();
        $parentKr = is_string($service->kerusakan) ? (json_decode($service->kerusakan, true) ?? [$service->kerusakan]) : (array) ($service->kerusakan ?? []);
        foreach ($parentKr as $kr) { $allKerusakan->push(is_array($kr) ? ($kr['nama'] ?? '') : $kr); }
        foreach ($service->serviceItems as $item) {
            $itemKr = is_array($item->kerusakan) ? $item->kerusakan : (json_decode($item->kerusakan, true) ?? []);
            foreach ($itemKr as $kr) { $allKerusakan->push(is_array($kr) ? ($kr['nama'] ?? '') : $kr); }
        }
        $allKerusakan = $allKerusakan->unique()->values();
        $kerusakanText = '- ' . $allKerusakan->implode(' | - ');

        return [
            $this->rowNumber,
            $service->service_number ?? '',
            $service->tanggal ? Carbon::parse($service->tanggal)->format('d/m/Y') : '',
            $service->customer?->nama ?? '',
            $typesText,
            $kerusakanText,
            $service->pengeluaran ?: 0,
            $service->jasa ?: 0,
            $service->serviceItems->pluck('teknisi')->filter()->unique()->map(fn($t) => $t === 'edi' ? 'Edi' : ($t === 'nur' ? 'Nur' : 'Adit'))->values()->join(', ') ?: 'Adit',
            $service->keterangan ?? '',
            $service->keuntungan ?: 0,
        ];
    }

    public function title(): string
    {
        return 'Service';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 22,
            'C' => 13,
            'D' => 25,
            'E' => 20,
            'F' => 16,
            'G' => 15,
            'H' => 15,
            'I' => 10,
            'J' => 22,
            'K' => 15,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '"Rp"#,##0',
            'H' => '"Rp"#,##0',
            'K' => '"Rp"#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'G:G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'H:H' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'K:K' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'I:I' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $rowCount = $sheet->getHighestRow();
                
                // Header styling
                $sheet->getStyle('A1:K1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFB8860B');
                $sheet->getStyle('A1:K1')->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                $sheet->getStyle('A1:K1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                if ($rowCount > 1) {
                    $sheet->getStyle('A2:K' . $rowCount)
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
            },
        ];
    }
}
