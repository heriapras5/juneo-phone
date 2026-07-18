<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiayaHarian extends Model
{
    protected $table = 'biaya_harian';

    protected $fillable = [
        'tanggal',
        'kategori',
        'nama',
        'jumlah',
        'keterangan',
    ];

    public static function kategoriList(): array
    {
        return ['bank_jago', 'fee_edi', 'fee_nur', 'fee_tambahan', 'lainnya'];
    }

    public static function kategoriLabels(): array
    {
        return [
            'bank_jago' => 'Bank Jago',
            'fee_edi' => 'Fee Edi',
            'fee_nur' => 'Fee Nur',
            'fee_tambahan' => 'Fee Tambahan',
            'lainnya' => 'Lainnya',
        ];
    }

    public static function defaultValues(): array
    {
        return [
            'bank_jago' => (int) Setting::getValue('biaya_bank_jago', 110000),
            'fee_edi' => (int) Setting::getValue('biaya_fee_edi', 60000),
            'fee_nur' => (int) Setting::getValue('biaya_fee_nur', 30000),
            'fee_tambahan' => 0,
            'lainnya' => (int) Setting::getValue('biaya_lainnya', 0),
        ];
    }

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'jumlah' => 'decimal:2',
        ];
    }
}
