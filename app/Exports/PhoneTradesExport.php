<?php

namespace App\Exports;

use App\Models\PhoneTrade;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PhoneTradesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private array $filters;
    private int $no = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return PhoneTrade::filter($this->filters)
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Tipe HP',
            'Brand',
            'Storage',
            'Warna',
            'Kondisi',
            'Sumber',
            'Tanggal Masuk',
            'Harga Beli',
            'Biaya Perbaikan',
            'Total Modal',
            'Status',
            'Tanggal Keluar',
            'Harga Jual',
            'Pembeli',
            'Profit',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->kode ?? '',
            $row->type_hp,
            $row->brand ?? '',
            $row->storage ?? '',
            $row->warna ?? '',
            PhoneTrade::kondisiLabels()[$row->kondisi] ?? $row->kondisi,
            $row->sumber_label,
            $row->tanggal_masuk?->format('d/m/Y') ?? '',
            (float) $row->harga_beli,
            (float) $row->biaya_perbaikan,
            $row->total_modal,
            $row->status_label,
            $row->tanggal_keluar?->format('d/m/Y') ?? '',
            $row->harga_jual !== null ? (float) $row->harga_jual : '',
            $row->pembeli ?? '',
            $row->status === PhoneTrade::STATUS_TERJUAL ? $row->keuntungan : '',
            $row->keterangan ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $header = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3CD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:R1')->applyFromArray($header);

        $lastRow = $sheet->getHighestRow();
        $bodyRange = 'A2:R' . $lastRow;

        $sheet->getStyle($bodyRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'vertical' => Alignment::VERTICAL_CENTER,
        ]);

        $sheet->getStyle('A2:B' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:I' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M2:N' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (['J', 'K', 'L', 'O', 'Q'] as $column) {
            $sheet->getStyle($column . '2:' . $column . $lastRow)
                ->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($column . '2:' . $column . $lastRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }
}
