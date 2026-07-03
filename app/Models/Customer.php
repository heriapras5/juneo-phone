<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function getTotalServiceAttribute()
    {
        return $this->services()->count();
    }
}
