<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ServicesSheetImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $tanggal = $this->parseDate($row['tanggal'] ?? '');
        $pelanggan = trim($row['pelanggan'] ?? '');
        if (empty($tanggal) || empty($pelanggan)) {
            return null;
        }

        $customer = Customer::firstOrCreate(
            ['nama' => $pelanggan]
        );

        $typeHp = $row['type_hp'] ?? '';
        $kerusakan = $row['kerusakan'] ?? '';
        $kerusakanArr = $kerusakan ? [$kerusakan] : [];

        $service = Service::create([
            'tanggal' => $tanggal,
            'customer_id' => $customer->id,
            'type_hp' => $typeHp,
            'kerusakan' => json_encode($kerusakanArr),
            'pengeluaran' => $row['pengeluaran'] ?? 0,
            'jasa' => $row['jasa'] ?? 0,
            'teknisi' => $this->parseTeknisi($row['teknisi'] ?? ''),
            'keterangan' => $row['keterangan'] ?? null,
        ]);

        if (!empty($typeHp)) {
            $service->serviceItems()->create([
                'type_hp' => $typeHp,
                'kerusakan' => $kerusakanArr,
                'jasa' => (float) ($row['jasa'] ?? 0),
                'pengeluaran' => (float) ($row['pengeluaran'] ?? 0),
            ]);
        }

        return $service;
    }

    private function parseTeknisi($value)
    {
        $value = trim(strtolower($value));
        if (in_array($value, ['edi', 'nur'])) {
            return $value;
        }
        return 'adit';
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            logger()->warning("ServicesSheetImport: gagal parse tanggal '$value'");
            return null;
        }
        return date('Y-m-d', $timestamp);
    }
}
