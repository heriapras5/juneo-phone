<?php

namespace App\Exports;

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
                  ->whereDate('tanggal', '<=', $this->toDate);
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
                  ->whereDate('tanggal', '<=', $this->toDate);
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
            'Type',
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
            default => 'Adit',
        };

        $allTypes = collect([$row->type_hp])
            ->concat($row->serviceItems->pluck('type_hp'))
            ->unique()->values();
        $allTypesText = '• ' . $allTypes->implode(' | • ');

        $allKerusakan = collect();
        $parentKr = is_string($row->kerusakan) ? (json_decode($row->kerusakan, true) ?? [$row->kerusakan]) : (array) ($row->kerusakan ?? []);
        foreach ($parentKr as $kr) { $allKerusakan->push($kr); }
        foreach ($row->serviceItems as $item) {
            $itemKr = is_array($item->kerusakan) ? $item->kerusakan : (json_decode($item->kerusakan, true) ?? []);
            foreach ($itemKr as $kr) { $allKerusakan->push($kr); }
        }
        $allKerusakan = $allKerusakan->unique()->values();
        $kerusakanText = '• ' . $allKerusakan->implode(' | • ');

        return [
            $this->no,
            $row->service_number,
            $row->tanggal->format('d/m/Y'),
            $row->customer->nama ?? '-',
            $allTypesText,
            $kerusakanText,
            $row->pengeluaran,
            $row->jasa,
            $teknisiLabel,
            $row->keterangan ?? '-',
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

                $sheet->insertNewRowBefore(1, 4);

                $sheet->setCellValue('A1', 'LAPORAN SERVICE TAHUN ' . $this->tahun);
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->setCellValue('A2', $this->label . ' ' . $this->tahun . ' — ' . $this->namaToko);
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->getFont()->setSize(12);
                $sheet->getStyle('A2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->setCellValue('A3', '');
                $sheet->setCellValue('A4', 'RINGKASAN ' . strtoupper($this->label) . ' ' . $this->tahun);
                $sheet->mergeCells('A4:K4');
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);

                $services = $this->collection();
                $totalPengeluaran = $services->sum('pengeluaran');
                $totalJasa = $services->sum('jasa');
                $biayaHarian = $this->getBiayaHarian();

                $summary = [
                    ['Omzet', $totalJasa],
                    ['Pengeluaran', $totalPengeluaran],
                    ['Kebutuhan Pribadi', $biayaHarian],
                ];

                $startSummaryRow = 5;
                foreach ($summary as $i => $s) {
                    $r = $startSummaryRow + $i;
                    $sheet->setCellValue('A' . $r, $s[0]);
                    $sheet->setCellValue('B' . $r, $s[1]);
                    $sheet->getStyle('A' . $r)->getFont()->setBold(true);
                    $sheet->getStyle('B' . $r)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                }

                $labaBersihRow = $startSummaryRow + count($summary);
                $sheet->setCellValue('A' . $labaBersihRow, 'Keuntungan Bersih');
                $sheet->setCellValue('B' . $labaBersihRow, $totalJasa - $totalPengeluaran - $biayaHarian);
                $sheet->getStyle('A' . $labaBersihRow)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('B' . $labaBersihRow)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('B' . $labaBersihRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');

                $lastSummaryRow = $labaBersihRow;
                $sheet->getStyle('A' . $startSummaryRow . ':B' . $lastSummaryRow)
                    ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

                $headerRow = $lastSummaryRow + 2;
                $dataStartRow = $headerRow + 1;
                $lastDataRow = $rowCount + 4;

                $headings = $this->headings();
                foreach ($headings as $colIndex => $heading) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $cell = $colLetter . $headerRow;
                    $sheet->setCellValue($cell, $heading);
                }

                $sheet->getStyle('A' . $headerRow . ':K' . $headerRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $headerRow . ':K' . $headerRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFB8860B');
                $sheet->getStyle('A' . $headerRow . ':K' . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');

                if ($lastDataRow >= $dataStartRow) {
                    $sheet->getStyle('A' . $dataStartRow . ':K' . $lastDataRow)
                        ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                    $sheet->getStyle('A' . $dataStartRow . ':K' . $lastDataRow)
                        ->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle('A' . $dataStartRow . ':K' . $lastDataRow)
                        ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

                    $sheet->getStyle('G' . $dataStartRow . ':K' . $lastDataRow)
                        ->getNumberFormat()->setFormatCode('"Rp"#,##0');
                }
            },
        ];
    }
}
