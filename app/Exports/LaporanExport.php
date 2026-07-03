<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanExport implements WithMultipleSheets
{
    protected $tahun;
    protected $bulan;
    protected $namaToko;
    protected $customerId;

    public function __construct(?int $tahun = null, ?array $bulan = null, ?string $namaToko = null, ?int $customerId = null)
    {
        $this->tahun = $tahun ?? (int) now()->format('Y');
        $this->namaToko = $namaToko ?? 'Juneo Phone';
        $this->customerId = $customerId;

        if ($bulan !== null) {
            $this->bulan = $bulan;
        } else {
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

            $this->bulan = [];
            foreach ($available as $m) {
                $this->bulan[$m] = $namaBulan[$m] ?? $m;
            }

            if (empty($this->bulan)) {
                $this->bulan = [];
            }
        }
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->bulan as $month => $label) {
            $sheets[] = new LaporanBulananSheet($month, $label, $this->tahun, $this->namaToko, $this->customerId);
        }

        $sheets[] = new LaporanRekapSheet($this->bulan ?: [1 => 'Januari'], $this->tahun, $this->namaToko, $this->customerId);

        return $sheets;
    }
}
