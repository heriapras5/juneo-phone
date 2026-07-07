<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanExport implements WithMultipleSheets
{
    protected $tahun;
    protected $bulanData;
    protected $namaToko;
    protected $customerId;
    protected $fromDate;
    protected $toDate;

    public function __construct($bulanData = null, ?string $namaToko = null, ?int $customerId = null, ?string $fromDate = null, ?string $toDate = null, ?int $tahun = null)
    {
        $this->tahun = $tahun ?? (int) now()->format('Y');
        $this->namaToko = $namaToko ?? 'Juneo Phone';
        $this->customerId = $customerId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        if ($bulanData !== null) {
            $this->bulanData = $bulanData;
        } else {
            $this->bulanData = $this->getAvailableMonths();
        }
    }

    protected function getAvailableMonths(): array
    {
        $available = Service::whereYear('tanggal', $this->tahun)
            ->where('status', '!=', 'batal')
            ->when($this->customerId, fn($q, $v) => $q->where('customer_id', $v))
            ->selectRaw('MONTH(tanggal) as m')
            ->groupBy('m')
            ->orderBy('m')
            ->pluck('m')
            ->toArray();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $bulanData = [];
        foreach ($available as $m) {
            $bulanData[] = [
                'month' => $m,
                'year' => $this->tahun,
                'label' => $namaBulan[$m] ?? $m,
            ];
        }

        return $bulanData;
    }

    public function sheets(): array
    {
        $sheets = [];
        $bulanMap = [];

        foreach ($this->bulanData as $item) {
            $month = $item['month'];
            $year = $item['year'];
            $label = $item['label'];

            $sheets[] = new LaporanBulananSheet($month, $label, $year, $this->namaToko, $this->customerId, $this->fromDate, $this->toDate);
            $bulanMap[$month] = $label;
        }

        $sheets[] = new LaporanRekapSheet($bulanMap ?: [1 => 'Januari'], $this->tahun, $this->namaToko, $this->customerId, $this->fromDate, $this->toDate);

        return $sheets;
    }
}
