<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static $cache = [];

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
