<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Helper extends Model
{
    protected $fillable = ['nama', 'warna'];

    public static function options(): array
    {
        return self::orderBy('id')->pluck('nama', 'id')->toArray();
    }

    public static function badgeClass(string $nama): string
    {
        $helper = self::where('nama', $nama)->first();
        return $helper?->warna ?? 'bg-gray-100 text-gray-600 border-gray-200';
    }
}
