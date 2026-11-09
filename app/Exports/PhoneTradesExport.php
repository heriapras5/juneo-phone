<?php

namespace App\Exports;

use App\Models\PhoneTrade;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PhoneTradesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $sheet->getHighestRow();
                $lastColumn = 'R';
                $summaryTitleRow = $lastDataRow + 2;
                $summaryValueRow = $summaryTitleRow + 1;
                $footerRow = $summaryValueRow + 2;

                $sheet->mergeCells("A{$summaryTitleRow}:{$lastColumn}{$summaryTitleRow}");
                $sheet->setCellValue("A{$summaryTitleRow}", 'RINGKASAN JUAL BELI HP (' . $this->periodLabel() . ')');
                $sheet->getStyle("A{$summaryTitleRow}")->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle("A{$summaryTitleRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$summaryTitleRow}:{$lastColumn}{$summaryTitleRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8E1']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $summaryText = 'Stok Tersedia: ' . $this->summary['stock_count'] . ' unit'
                    . '  |  Modal Stok: Rp ' . number_format((float) $this->summary['stock_modal'], 0, ',', '.')
                    . '  |  Terjual: ' . $this->summary['sold_count'] . ' unit'
                    . '  |  Omzet: Rp ' . number_format((float) $this->summary['omzet'], 0, ',', '.')
                    . '  |  Modal Terjual: Rp ' . number_format((float) $this->summary['modal_terjual'], 0, ',', '.')
                    . '  |  Profit: Rp ' . number_format((float) $this->summary['laba'], 0, ',', '.');

                $sheet->mergeCells("A{$summaryValueRow}:{$lastColumn}{$summaryValueRow}");
                $sheet->setCellValue("A{$summaryValueRow}", $summaryText);
                $sheet->getStyle("A{$summaryValueRow}")->getFont()->setSize(10);
                $sheet->getStyle("A{$summaryValueRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$summaryValueRow}:{$lastColumn}{$summaryValueRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8E1']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->setCellValue("A{$footerRow}", 'Dicetak: ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle("A{$footerRow}")->getFont()->setSize(8)->setItalic(true);
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
        $sheet->getStyle('A1:R1')->applyFromArray($header);

        $lastRow = 1 + $this->query()->count();

        if ($lastRow < 2) {
            return [];
        }

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
