<?php

namespace App\Exports;

use App\Models\BiayaHarian;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class LaporanRekapSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $namaToko;
    protected $customerId;
    protected $fromDate;
    protected $toDate;
    protected $data;
    protected $profitColumnIndex = 0;

    public function __construct(array $bulan, int $tahun, ?string $namaToko = null, ?int $customerId = null, ?string $fromDate = null, ?string $toDate = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->namaToko = $namaToko ?? 'Juneo Phone';
        $this->customerId = $customerId;
        $this->fromDate = $fromDate ? \Carbon\Carbon::parse($fromDate) : null;
        $this->toDate = $toDate ? \Carbon\Carbon::parse($toDate) : null;
        $this->loadData();
    }

    protected function loadData()
    {
        $allKeys = array_keys($this->bulan);
        $labelTotal = 'TOTAL ' . count($allKeys) . ' BULAN';

        foreach ($this->bulan as $month => $label) {
            $services = Service::where('status', '!=', 'batal')
                ->when($this->fromDate && $this->toDate, function ($q) use ($month) {
                    $q->whereDate('tanggal', '>=', $this->fromDate)
                      ->whereDate('tanggal', '<=', $this->toDate)
                      ->whereMonth('tanggal', $month);
                }, function ($q) use ($month) {
                    $q->whereYear('tanggal', $this->tahun)
                      ->whereMonth('tanggal', $month);
                })
                ->when($this->customerId, fn($q, $v) => $q->where('customer_id', $v));

            $biayaHarian = BiayaHarian::query()
                ->when($this->fromDate && $this->toDate, function ($q) use ($month) {
                    $q->whereDate('tanggal', '>=', $this->fromDate)
                      ->whereDate('tanggal', '<=', $this->toDate)
                      ->whereMonth('tanggal', $month);
                }, function ($q) use ($month) {
                    $q->whereYear('tanggal', $this->tahun)
                      ->whereMonth('tanggal', $month);
                })
                ->sum('jumlah');

            $totalJasa = (clone $services)->sum('jasa');
            $totalPengeluaran = (clone $services)->sum('pengeluaran');

            $profit = $totalJasa - $totalPengeluaran - $biayaHarian;

            $this->data[] = [
                $label,
                (clone $services)->count(),
                $totalJasa,
                $totalPengeluaran,
                $biayaHarian,
                $profit,
            ];
        }

        $totalCount = array_sum(array_column($this->data, 1));
        $totalJasa = array_sum(array_column($this->data, 2));
        $totalPengeluaran = array_sum(array_column($this->data, 3));
        $totalBiayaHarian = array_sum(array_column($this->data, 4));
        $totalProfit = $totalJasa - $totalPengeluaran - $totalBiayaHarian;

        $this->data[] = ['', '', '', '', '', ''];
        $this->data[] = [
            $labelTotal,
            $totalCount,
            $totalJasa,
            $totalPengeluaran,
            $totalBiayaHarian,
            $totalProfit,
        ];
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Total Service',
            'Omzet',
            'Pengeluaran',
            'Kebutuhan Pribadi',
            'Keuntungan Bersih',
        ];
    }

    public function title(): string
    {
        $count = count($this->bulan);
        return $count > 0 ? 'Rekap ' . $count . ' Bulan' : 'Rekap Tahunan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $rowCount = $sheet->getHighestRow();

                $sheet->insertNewRowBefore(1);
                $sheet->insertNewRowBefore(1);

                $title = $this->fromDate && $this->toDate
                    ? 'REKAP LAPORAN SERVICE (' . $this->fromDate->format('d/m/Y') . ' — ' . $this->toDate->format('d/m/Y') . ')'
                    : 'REKAP LAPORAN SERVICE TAHUN ' . $this->tahun;
                $title .= ' — ' . $this->namaToko;
                if ($this->customerId) {
                    $customer = \App\Models\Customer::find($this->customerId);
                    if ($customer) {
                        $title .= ' — Filter: ' . $customer->nama;
                    }
                }
                $sheet->setCellValue('A1', $title);
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $headerRow = 3;
                $lastRow = $rowCount + 1;

                $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFB8860B');
                $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $totalRowIndex = $lastRow;
                $separatorRow = $totalRowIndex - 1;

                // Styling total row
                $sheet->getStyle('A' . $totalRowIndex . ':F' . $totalRowIndex)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A' . $totalRowIndex . ':F' . $totalRowIndex)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFEF3C7');
                $sheet->getStyle('A' . $totalRowIndex . ':F' . $totalRowIndex)
                    ->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
                $sheet->getStyle('A' . $totalRowIndex . ':F' . $totalRowIndex)
                    ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

                if ($separatorRow > $headerRow) {
                    $sheet->getStyle('A' . $separatorRow . ':F' . $separatorRow)
                        ->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOTTED);
                }

                // Format Rp untuk kolom C, D, E, F
                foreach (range(4, $lastRow) as $r) {
                    foreach (['C', 'D', 'E', 'F'] as $col) {
                        if ($r !== $headerRow) {
                            $sheet->getStyle($col . $r)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                            $sheet->getStyle($col . $r)->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        }
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(22);
                $sheet->getColumnDimension('B')->setWidth(16);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(22);

                // Border all data cells
                $sheet->getStyle('A' . $headerRow . ':F' . $lastRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Footer
                $footerRow = $totalRowIndex + 2;
                $sheet->setCellValue('A' . $footerRow, 'Dicetak: ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle('A' . $footerRow)->getFont()->setSize(8)->setItalic(true)->getColor()->setARGB('FF000000');
            },
        ];
    }
}
