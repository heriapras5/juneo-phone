<?php

namespace App\Imports\Restore;

use App\Models\BiayaHarian;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class BiayaHarianRestoreSheetImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $tanggal = $this->parseDate($row['tanggal'] ?? '');
        if (!$tanggal) {
            return null;
        }

        $kategori = trim($row['kategori'] ?? '');
        if (empty($kategori)) {
            return null;
        }

        if (BiayaHarian::where('tanggal', $tanggal)->where('kategori', $kategori)->exists()) {
            return null;
        }

        return BiayaHarian::create([
            'tanggal' => $tanggal,
            'kategori' => $kategori,
            'nama' => $row['nama'] ?? null,
            'jumlah' => $row['jumlah'] ?? 0,
            'keterangan' => $row['keterangan'] ?? null,
        ]);
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
}
