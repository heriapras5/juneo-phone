<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageCategory extends Model
{
    protected $fillable = ['name', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function typeHpOptions()
    {
        return $this->belongsToMany(TypeHpOption::class);
    }
}
