<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneTradeHistory extends Model
{
    protected $fillable = [
        'phone_trade_id',
        'tipe',
        'status_from',
        'status_to',
        'note',
        'user_id',
    ];

    public function phoneTrade()
    {
        return $this->belongsTo(PhoneTrade::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function tipeLabels(): array
    {
        return [
            PhoneTrade::TIPE_DIBUAT => 'Dibuat',
            PhoneTrade::TIPE_STATUS => 'Status',
            PhoneTrade::TIPE_UPDATE => 'Perubahan',
        ];
    }

    public static function tipeClasses(): array
    {
        return [
            PhoneTrade::TIPE_DIBUAT => 'bg-gray-100 text-gray-700',
            PhoneTrade::TIPE_STATUS => 'bg-blue-100 text-blue-700',
            PhoneTrade::TIPE_UPDATE => 'bg-amber-100 text-amber-700',
        ];
    }

    public function getTipeLabelAttribute(): string
    {
        return static::tipeLabels()[$this->tipe] ?? $this->tipe;
    }
}
