<?php

namespace App\Exports;

use App\Helpers\ServiceHelper;
use App\Models\BiayaHarian;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanBulananSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
{
    protected $month;
    protected $label;
    protected $tahun;
    protected $namaToko;
    protected $customerId;
    protected $fromDate;   // Optional: Carbon date for custom range start
    protected $toDate;     // Optional: Carbon date for custom range end
    protected $no = 0;
    protected $cached = null;
    protected $cachedBiayaHarian = null;

    public function __construct(int $month, string $label, int $tahun, ?string $namaToko = null, ?int $customerId = null, ?string $fromDate = null, ?string $toDate = null)
    {
        $this->month = $month;
        $this->label = $label;
        $this->tahun = $tahun;
        $this->namaToko = $namaToko ?? 'Juneo Phone';
        $this->customerId = $customerId;
        $this->fromDate = $fromDate ? \Carbon\Carbon::parse($fromDate)->startOfMonth() : null;
        $this->toDate = $toDate ? \Carbon\Carbon::parse($toDate)->endOfMonth() : null;
    }

    public function collection()
    {
        if ($this->cached) return $this->cached;
        $this->cached = Service::with('customer')
            ->where('status', '!=', 'batal')
            ->when($this->fromDate && $this->toDate, function ($q) {
                $q->whereDate('tanggal', '>=', $this->fromDate)
                  ->whereDate('tanggal', '<=', $this->toDate)
                  ->whereMonth('tanggal', $this->month);
            }, function ($q) {
                $q->whereYear('tanggal', $this->tahun)
                  ->whereMonth('tanggal', $this->month);
            })
            ->when($this->customerId, fn($q, $v) => $q->where('customer_id', $v))
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();
        return $this->cached;
    }

    protected function getBiayaHarian(): float
    {
        if ($this->cachedBiayaHarian !== null) return $this->cachedBiayaHarian;
        $this->cachedBiayaHarian = BiayaHarian::query()
            ->when($this->fromDate && $this->toDate, function ($q) {
                $q->whereDate('tanggal', '>=', $this->fromDate)
                  ->whereDate('tanggal', '<=', $this->toDate)
                  ->whereMonth('tanggal', $this->month);
            }, function ($q) {
                $q->whereYear('tanggal', $this->tahun)
                  ->whereMonth('tanggal', $this->month);
            })
            ->sum('jumlah');
        return $this->cachedBiayaHarian;
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

    public function map($row): array
    {
        $this->no++;

        $teknisiLabel = match ($row->teknisi) {
            'edi' => 'Edi',
            'nur' => 'Nur',
            default => '',
        };

        $allTypesText = ServiceHelper::getAllTypesText($row);

        $kerusakanText = ServiceHelper::getAllKerusakanText($row);

        return [
            $this->no,
            $row->service_number,
            $row->tanggal->format('d/m/Y'),
            $row->customer->nama ?? '',
            $allTypesText,
            $kerusakanText,
            $row->pengeluaran,
            $row->jasa,
            $teknisiLabel,
            $row->keterangan ?? '',
            $row->keuntungan,
        ];
    }

    public function title(): string
    {
        return $this->label;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $rowCount = $sheet->getHighestRow();

                $sheet->insertNewRowBefore(1, 3);

                $colCount = 'K';
                $range = 'A1:' . $colCount . '1';

                $sheet->setCellValue('A1', 'LAPORAN SERVICE' . ($this->fromDate && $this->toDate ? ' (' . $this->fromDate->format('d/m/Y') . ' — ' . $this->toDate->format('d/m/Y') . ')' : ' TAHUN ' . $this->tahun));
                $sheet->mergeCells($range);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $subtitle = $this->label . ' ' . $this->tahun . ' — ' . $this->namaToko;
                if ($this->customerId) {
                    $customer = \App\Models\Customer::find($this->customerId);
                    if ($customer) {
                        $subtitle .= ' — Filter: ' . $customer->nama;
                    }
                }
                $sheet->setCellValue('A2', $subtitle);
                $sheet->mergeCells('A2:' . $colCount . '2');
                $sheet->getStyle('A2')->getFont()->setSize(12);
                $sheet->getStyle('A2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $headerRow = 4;
                $dataStartRow = 5;
                $lastDataRow = $rowCount + 3;

                $sheet->getStyle('A' . $headerRow . ':' . $colCount . $headerRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $headerRow . ':' . $colCount . $headerRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFB8860B');
                $sheet->getStyle('A' . $headerRow . ':' . $colCount . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A' . $headerRow . ':' . $colCount . $headerRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                foreach (['G', 'H', 'K'] as $c) {
                    $sheet->getStyle($c . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                $sheet->getStyle('I' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($lastDataRow >= $dataStartRow) {
                    $sheet->getStyle('A' . $dataStartRow . ':' . $colCount . $lastDataRow)
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                    $sheet->getStyle('G' . $dataStartRow . ':H' . $lastDataRow)
                        ->getNumberFormat()->setFormatCode('"Rp"#,##0');
                    $sheet->getStyle('K' . $dataStartRow . ':K' . $lastDataRow)
                        ->getNumberFormat()->setFormatCode('"Rp"#,##0');

                    $sheet->getStyle('G' . $dataStartRow . ':H' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('K' . $dataStartRow . ':K' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('I' . $dataStartRow . ':I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Summary section - two separate single-line rows (always shown)
                $services = $this->collection();
                $totalPengeluaran = $services->sum('pengeluaran');
                $totalJasa = $services->sum('jasa');
                $biayaHarian = $this->getBiayaHarian();
                $profit = $totalJasa - $totalPengeluaran - $biayaHarian;

                $summaryStart = ($lastDataRow >= $dataStartRow ? $lastDataRow : $headerRow) + 1;

                // Row 1: title
                $titleRow = $summaryStart;
                $sheet->setCellValue('A' . $titleRow, 'RINGKASAN ' . strtoupper($this->label));
                $sheet->mergeCells('A' . $titleRow . ':K' . $titleRow);
                $sheet->getStyle('A' . $titleRow)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A' . $titleRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A' . $titleRow . ':K' . $titleRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A' . $titleRow . ':K' . $titleRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFF8E1');

                // Row 2: values (single line)
                $valueRow = $summaryStart + 1;
                $summaryText = 'Pengeluaran: Rp ' . number_format($totalPengeluaran, 0, ',', '.') .
                               '  |  Omzet: Rp ' . number_format($totalJasa, 0, ',', '.') .
                               '  |  Kebutuhan: Rp ' . number_format($biayaHarian, 0, ',', '.') .
                               '  |  Profit: Rp ' . number_format($profit, 0, ',', '.');
                $sheet->setCellValue('A' . $valueRow, $summaryText);
                $sheet->mergeCells('A' . $valueRow . ':K' . $valueRow);
                $sheet->getStyle('A' . $valueRow)->getFont()->setSize(10);
                $sheet->getStyle('A' . $valueRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A' . $valueRow . ':K' . $valueRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A' . $valueRow . ':K' . $valueRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFF8E1');

                $footerRow = $summaryStart + 3;
                $sheet->setCellValue('A' . $footerRow, 'Dicetak: ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle('A' . $footerRow)->getFont()->setSize(8)->setItalic(true)->getColor()->setARGB('FF000000');

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(13);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(16);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(10);
                $sheet->getColumnDimension('J')->setWidth(22);
                $sheet->getColumnDimension('K')->setWidth(15);
            },
        ];
    }
}
