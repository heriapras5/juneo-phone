<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GajiKaryawan extends Model
{
    protected $table = 'gaji_karyawan';

    protected $fillable = [
        'tanggal',
        'karyawan',
        'jumlah',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'decimal:2',
        ];
    }
}
