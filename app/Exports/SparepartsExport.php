<?php

namespace App\Exports;

use App\Models\Sparepart;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SparepartsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private array $filters;
    private int $no = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Sparepart::filter($this->filters)
            ->orderBy('kategori')
            ->orderBy('nama_barang');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kategori Brand',
            'Kategori',
            'Nama Barang',
            'Type',
            'Stok',
            'Harga Beli',
            'Toko',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        $this->no++;
        return [
            $this->no,
            $row->brand ?? '',
            $row->kategori,
            $row->nama_barang,
            $row->type_hp ?? '',
            $row->stok,
            $row->harga_beli ? (float) $row->harga_beli : '',
            $row->toko ?? '',
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
        $sheet->getStyle('A1:I1')->applyFromArray($header);

        $lastRow = $sheet->getHighestRow();
        $bodyRange = 'A2:I' . $lastRow;
        $sheet->getStyle($bodyRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'vertical' => Alignment::VERTICAL_CENTER,
        ]);

        $sheet->getStyle('A2:A' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:F' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G' . $lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G2:G' . $lastRow)
            ->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
