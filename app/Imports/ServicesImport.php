<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ServicesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $customer = Customer::firstOrCreate(
            ['nama' => $row['pelanggan']],
            ['no_hp' => $row['no_hp'] ?? null]
        );

        return new Service([
            'tanggal' => $this->parseDate($row['tanggal']),
            'customer_id' => $customer->id,
            'type_hp' => $row['type_hp'],
            'kerusakan' => $row['kerusakan'],
            'pengeluaran' => $row['pengeluaran'] ?? 0,
            'jasa' => $row['jasa'] ?? 0,
            'teknisi' => $this->parseTeknisi($row['teknisi'] ?? ''),
            'keterangan' => $row['keterangan'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required',
            'pelanggan' => 'required|max:255',
            'type_hp' => 'required|max:255',
            'kerusakan' => 'required',
        ];
    }

    private function parseTeknisi($value)
    {
        $value = trim(strtolower($value));
        if (in_array($value, ['edi', 'nur'])) {
            return $value;
        }
        return null;
    }

    private function parseDate($value)
    {
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        return date('Y-m-d', strtotime($value));
    }
}
