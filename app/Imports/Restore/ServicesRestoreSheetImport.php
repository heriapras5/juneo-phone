<?php

namespace App\Imports\Restore;

use App\Models\Customer;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ServicesRestoreSheetImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $serviceNumber = trim($row['no_service'] ?? '');
        if (empty($serviceNumber)) {
            return null;
        }

        if (Service::where('service_number', $serviceNumber)->exists()) {
            return null;
        }

        $tanggal = $this->parseDate($row['tanggal'] ?? '');
        if (!$tanggal) {
            return null;
        }

        $nama = trim($row['pelanggan'] ?? '');
        if (empty($nama)) {
            return null;
        }
        $customer = Customer::firstOrCreate(
            ['nama' => $nama],
            ['no_hp' => $row['no_hp'] ?? null]
        );

        $data = [
            'service_number' => $serviceNumber,
            'tanggal' => $tanggal,
            'customer_id' => $customer->id,
            'type_hp' => $this->parseTypeHp($row['type_hp'] ?? ''),
            'kerusakan' => $this->parseKerusakan($row['kerusakan'] ?? ''),
            'pengeluaran' => $row['pengeluaran'] ?? 0,
            'jasa' => $row['jasa'] ?? 0,
            'keuntungan' => $row['keuntungan'] ?? 0,
            'kebutuhan_pribadi' => $row['kebutuhan_pribadi'] ?? 0,
            'teknisi' => $row['teknisi'] ?? 'adit',
            'status' => $this->parseStatus($row['status'] ?? ''),
            'keterangan' => $row['keterangan'] ?? null,
        ];

        if (!empty($row['estimasi_selesai'])) {
            $data['estimasi_selesai'] = $this->parseDate($row['estimasi_selesai']);
        }
        if (!empty($row['tanggal_selesai'])) {
            $data['tanggal_selesai'] = $this->parseDate($row['tanggal_selesai']);
        }

        return Service::create($data);
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
            $parts = explode('/', $value);
            return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }

    private function parseTypeHp($value)
    {
        if (empty($value)) {
            return '';
        }
        $value = preg_replace('/^•\s*/', '', $value);
        $types = explode(' | ', $value);
        return trim($types[0] ?? '');
    }

    private function parseKerusakan($value)
    {
        if (empty($value)) {
            return '[]';
        }
        $value = preg_replace('/^•\s*/', '', $value);
        $items = explode(' | ', $value);
        $items = array_map('trim', $items);
        return json_encode($items);
    }

    private function parseStatus($value)
    {
        $value = strtolower(trim($value));
        $allowed = ['menunggu', 'dikerjakan', 'selesai', 'diambil', 'batal'];
        if (in_array($value, $allowed)) {
            return $value;
        }
        return 'menunggu';
    }
}
