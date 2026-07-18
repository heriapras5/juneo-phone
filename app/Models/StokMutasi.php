<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';

    protected $fillable = [
        'sparepart_id',
        'tipe',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'keterangan',
        'user_id',
    ];

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
