<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static $cache = [];

    protected static function booted()
    {
        static::saved(function ($setting) {
            if ($setting->key === 'logo' && $setting->wasChanged('value')) {
                $original = $setting->getOriginal('value');
                if ($original && Storage::disk('public')->exists($original)) {
                    Storage::disk('public')->delete($original);
                }
            }
        });
    }

    public static function getValue($key, $default = null)
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }
        $setting = static::where('key', $key)->first();
        static::$cache[$key] = $setting ? $setting->value : $default;
        return static::$cache[$key];
    }

    public static function flushCache(): void
    {
        static::$cache = [];
    }
}
