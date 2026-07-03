<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ServicesSheetImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public static int $skippedBatal = 0;

    public function model(array $row)
    {
        $required = ['tanggal', 'pelanggan'];
        foreach ($required as $field) {
            if (empty($row[$field])) {
                return null;
            }
        }

        $status = !empty($row['status']) ? strtolower(trim($row['status'])) : 'menunggu';
        $allowedStatus = ['menunggu', 'dikerjakan', 'selesai', 'diambil', 'batal'];
        if (!in_array($status, $allowedStatus)) {
            $status = 'menunggu';
        }

        if ($status === 'batal') {
            static::$skippedBatal++;
            return null;
        }

        $customer = Customer::firstOrCreate(
            ['nama' => $row['pelanggan']],
            ['no_hp' => $row['no_hp'] ?? null]
        );

        $kerusakan = $row['kerusakan'] ?? '';
        $kerusakanArr = json_decode($kerusakan, true);
        if (!is_array($kerusakanArr)) {
            $kerusakanArr = $kerusakan ? [$kerusakan] : [];
        }
        $kerusakanJson = json_encode($kerusakanArr);

        $parsedDate = $this->parseDate($row['tanggal']);
        if (!$parsedDate) {
            return null;
        }

        $data = [
            'tanggal' => $parsedDate,
            'customer_id' => $customer->id,
            'type_hp' => $row['type_hp'] ?? '',
            'kerusakan' => $kerusakanJson,
            'pengeluaran' => $row['pengeluaran'] ?? 0,
            'jasa' => $row['jasa'] ?? 0,
            'teknisi' => $this->parseTeknisi($row['teknisi'] ?? ''),
            'keterangan' => $row['keterangan'] ?? null,
            'status' => $status,
        ];

        if (!empty($row['estimasi_selesai'])) {
            $data['estimasi_selesai'] = $this->parseDate($row['estimasi_selesai']);
        }
        if (!empty($row['tanggal_selesai'])) {
            $data['tanggal_selesai'] = $this->parseDate($row['tanggal_selesai']);
        }

        $service = Service::create($data);

        if (!empty($row['type_hp'])) {
            $service->serviceItems()->create([
                'type_hp' => $row['type_hp'],
                'kerusakan' => $kerusakanArr,
                'jasa' => (float) ($row['jasa'] ?? 0),
                'pengeluaran' => (float) ($row['pengeluaran'] ?? 0),
            ]);
        }

        return $service;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required',
            'pelanggan' => 'required|max:255',
        ];
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
