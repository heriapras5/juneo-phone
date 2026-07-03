<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    protected $fillable = [
        'service_id',
        'type_hp',
        'kerusakan',
        'pengeluaran',
        'jasa',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'pengeluaran' => 'decimal:2',
            'jasa' => 'decimal:2',
            'kerusakan' => 'array',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}