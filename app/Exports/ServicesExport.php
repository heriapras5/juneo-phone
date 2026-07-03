<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithStartRow
{
    private ?string $dateFrom;
    private ?string $dateTo;
    private ?int $customerId;
    private int $no = 0;

    public function __construct(?string $dateFrom = null, ?string $dateTo = null, ?int $customerId = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->customerId = $customerId;
    }

    public function query()
    {
        return Service::with('customer')
            ->where('status', '!=', 'batal')
            ->when($this->dateFrom, fn($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($this->dateTo, fn($q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->when($this->customerId, fn($q, $v) => $q->where('customer_id', $v))
            ->orderBy('tanggal', 'desc');
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
            'Modal',
            'Jasa',
            'Teknisi',
            'Keuntungan',
            'Estimasi Selesai',
            'Status',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        $this->no++;

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

        $teknisiLabel = match($row->teknisi) { 'edi' => 'Edi', 'nur' => 'Nur', default => 'Adit' };

        $statusLabels = ['menunggu' => 'Menunggu', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai', 'diambil' => 'Diambil', 'batal' => 'Batal'];

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
            $row->keuntungan,
            $row->estimasi_selesai ? $row->estimasi_selesai->format('d/m/Y') : '-',
            $statusLabels[$row->status] ?? $row->status,
            $row->keterangan ?? '-',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
