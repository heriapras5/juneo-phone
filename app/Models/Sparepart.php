<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $fillable = [
        'nama_barang',
        'kategori',
        'type_hp',
        'stok',
        'harga_beli',
        'toko',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
        ];
    }

    public function mutasi()
    {
        return $this->hasMany(StokMutasi::class)->latest();
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['kategori'] ?? null, fn($q, $v) => $q->where('kategori', $v))
            ->when($filters['toko'] ?? null, fn($q, $v) => $q->where('toko', $v))
            ->when($filters['search'] ?? null, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('nama_barang', 'like', "%{$v}%")
                      ->orWhere('type_hp', 'like', "%{$v}%")
                      ->orWhere('keterangan', 'like', "%{$v}%");
                });
            });
    }

    public static function kategoriLabels(): array
    {
        return [
            'lcd' => 'LCD',
            'baterai' => 'Baterai',
            'pcb' => 'PCB / Papan Cas',
            'fleksibot' => 'Fleksibot',
            'flex_on_off' => 'Flex On/Off',
            'flex_volume' => 'Flex Volume',
            'keypad' => 'Keypad',
            'lainnya' => 'Lainnya',
        ];
    }

    public static function tokoOptions(): array
    {
        return Toko::pluck('nama')->toArray();
    }
}
