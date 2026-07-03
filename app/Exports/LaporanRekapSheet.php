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

class LaporanRekapSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $namaToko;
    protected $customerId;
    protected $data;

    public function __construct(array $bulan, int $tahun, ?string $namaToko = null, ?int $customerId = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->namaToko = $namaToko ?? 'Juneo Phone';
        $this->customerId = $customerId;
        $this->loadData();
    }

    protected function loadData()
    {
        $allKeys = array_keys($this->bulan);
        $labelTotal = 'TOTAL ' . count($allKeys) . ' BULAN';

        foreach ($this->bulan as $month => $label) {
            $services = Service::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $month)
                ->where('status', '!=', 'batal')
                ->when($this->customerId, fn($q, $v) => $q->where('customer_id', $v));

            $biayaHarian = BiayaHarian::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            $totalJasa = (clone $services)->sum('jasa');
            $totalPengeluaran = (clone $services)->sum('pengeluaran');

            $this->data[] = [
                $label,
                (clone $services)->count(),
                $totalJasa,
                $totalPengeluaran,
                $biayaHarian,
                $totalJasa - $totalPengeluaran - $biayaHarian,
            ];
        }

        $totalCount = array_sum(array_column($this->data, 1));
        $totalJasa = array_sum(array_column($this->data, 2));
        $totalPengeluaran = array_sum(array_column($this->data, 3));
        $totalBiayaHarian = array_sum(array_column($this->data, 4));

        $this->data[] = ['', '', '', '', '', ''];
        $this->data[] = [
            $labelTotal,
            $totalCount,
            $totalJasa,
            $totalPengeluaran,
            $totalBiayaHarian,
            $totalJasa - $totalPengeluaran - $totalBiayaHarian,
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
            'Pendapatan',
            'Modal',
            'Biaya Harian',
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

                $sheet->setCellValue('A1', 'REKAP LAPORAN SERVICE TAHUN ' . $this->tahun . ' — ' . $this->namaToko);
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $headerRow = 2;
                $lastRow = $rowCount + 1;

                $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF2563EB');
                $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $totalRowIndex = $lastRow;
                $separatorRow = $totalRowIndex - 1;

                $sheet->getStyle('A' . $totalRowIndex . ':F' . $totalRowIndex)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A' . $totalRowIndex . ':F' . $totalRowIndex)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFEF3C7');

                $sheet->getStyle('A' . $totalRowIndex . ':F' . $totalRowIndex)
                    ->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

                if ($separatorRow > $headerRow) {
                    $sheet->getStyle('A' . $separatorRow . ':F' . $separatorRow)
                        ->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOTTED);
                }

                foreach (range(1, $lastRow) as $r) {
                    foreach (range('B', 'F') as $col) {
                        if ($r !== $headerRow) {
                            $sheet->getStyle($col . $r)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                        }
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(14);
                $sheet->getColumnDimension('C')->setWidth(16);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('F')->setWidth(18);

                $sheet->getStyle('A' . $headerRow . ':F' . $lastRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
