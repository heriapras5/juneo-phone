<?php

namespace App\Exports;

use App\Models\PhoneTrade;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PhoneTradesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    private array $filters;
    private array $summary;
    private int $no = 0;

    public function __construct(array $filters = [], array $summary = [])
    {
        $this->filters = $filters;
        $this->summary = array_merge([
            'stock_count' => 0,
            'stock_modal' => 0,
            'sold_count' => 0,
            'omzet' => 0,
            'modal_terjual' => 0,
            'laba' => 0,
        ], $summary);
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'LAPORAN JUAL BELI HP');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel());
                $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $labels = [
                    'A3' => 'Stok Tersedia',
                    'B3' => 'Modal Stok',
                    'C3' => 'Terjual',
                    'D3' => 'Omzet',
                    'E3' => 'Modal Terjual',
                    'F3' => 'Profit',
                ];

                foreach ($labels as $cell => $label) {
                    $sheet->setCellValue($cell, $label);
                }

                $values = [
                    'A4' => $this->summary['stock_count'] . ' unit',
                    'B4' => $this->summary['stock_modal'],
                    'C4' => $this->summary['sold_count'] . ' unit',
                    'D4' => $this->summary['omzet'],
                    'E4' => $this->summary['modal_terjual'],
                    'F4' => $this->summary['laba'],
                ];

                foreach ($values as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                $sheet->getStyle('A3:F3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->getStyle('A4:F4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8E1']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                foreach (['B4', 'D4', 'E4', 'F4'] as $cell) {
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"Rp" #,##0');
                }

                $sheet->mergeCells('A5:F5');
                $sheet->setCellValue('A5', 'Profit = Harga Jual - (Harga Beli + Biaya Perbaikan)');
                $sheet->getStyle('A5')->getFont()->setItalic(true)->setSize(9);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(24);
            },
        ];
    }

    private function periodLabel(): string
    {
        $from = $this->filters['from'] ?? null;
        $to = $this->filters['to'] ?? null;

        if (!$from && !$to) {
            return 'Seluruh periode';
        }

        $fromLabel = $from ? \Carbon\Carbon::parse($from)->format('d/m/Y') : 'awal';
        $toLabel = $to ? \Carbon\Carbon::parse($to)->format('d/m/Y') : 'sekarang';

        return $fromLabel . ' s.d. ' . $toLabel;
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
        $sheet->getStyle('A8:R8')->applyFromArray($header);

        $lastRow = $sheet->getHighestRow();

        if ($lastRow < 9) {
            return [];
        }

        $bodyRange = 'A9:R' . $lastRow;

        $sheet->getStyle($bodyRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'vertical' => Alignment::VERTICAL_CENTER,
        ]);

        $sheet->getStyle('A9:B' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I9:I' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M9:N' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (['J', 'K', 'L', 'O', 'Q'] as $column) {
            $sheet->getStyle($column . '9:' . $column . $lastRow)
                ->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($column . '9:' . $column . $lastRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }
}
