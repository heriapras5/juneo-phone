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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithColumnFormatting, WithStyles
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
            'No. HP',
            'Type HP',
            'Kerusakan',
            'Estimasi Selesai',
            'Tanggal Selesai',
            'Pengeluaran',
            'Jasa',
            'Keuntungan',
            'Kebutuhan Pribadi',
            'Teknisi',
            'Status',
            'Keterangan',
        ];
    }

    public function map($service): array
    {
        $this->rowNumber++;

        $allTypes = collect([$service->type_hp])
            ->concat($service->serviceItems->pluck('type_hp'))
            ->unique()->values();
        $typesText = '• ' . $allTypes->implode(' | • ');

        $allKerusakan = collect();
        $parentKr = is_string($service->kerusakan) ? (json_decode($service->kerusakan, true) ?? [$service->kerusakan]) : (array) ($service->kerusakan ?? []);
        foreach ($parentKr as $kr) { $allKerusakan->push($kr); }
        foreach ($service->serviceItems as $item) {
            $itemKr = is_array($item->kerusakan) ? $item->kerusakan : (json_decode($item->kerusakan, true) ?? []);
            foreach ($itemKr as $kr) { $allKerusakan->push($kr); }
        }
        $allKerusakan = $allKerusakan->unique()->values();
        $kerusakanText = '• ' . $allKerusakan->implode(' | • ');

        return [
            $this->rowNumber,
            $service->service_number ?? '',
            $service->tanggal ? Carbon::parse($service->tanggal)->format('d/m/Y') : '',
            $service->customer?->nama ?? '',
            $service->customer?->no_hp ?? '',
            $typesText,
            $kerusakanText,
            $service->estimasi_selesai ? Carbon::parse($service->estimasi_selesai)->format('d/m/Y') : '',
            $service->tanggal_selesai ? Carbon::parse($service->tanggal_selesai)->format('d/m/Y') : '',
            $service->pengeluaran ?: 0,
            $service->jasa ?: 0,
            $service->keuntungan ?: 0,
            $service->kebutuhan_pribadi ?: 0,
            $service->teknisi ?: 'Adit',
            $service->status ?? '',
            $service->keterangan ?? '',
        ];
    }

    public function title(): string
    {
        return 'Services';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 14,
            'D' => 25,
            'E' => 18,
            'F' => 20,
            'G' => 30,
            'H' => 16,
            'I' => 16,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 18,
            'N' => 10,
            'O' => 14,
            'P' => 30,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => '"Rp"#,##0',
            'K' => '"Rp"#,##0',
            'L' => '"Rp"#,##0',
            'M' => '"Rp"#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'J:J' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'K:K' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'L:L' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'M:M' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }
}
