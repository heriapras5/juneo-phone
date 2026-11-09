<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PhoneTrade extends Model
{
    const STATUS_STOK = 'stok';
    const STATUS_TERJUAL = 'terjual';

    const KONDISI_BARU = 'baru';
    const KONDISI_BEKAS = 'bekas';
    const KONDISI_MINUS = 'minus';

    const SUMBER_LANGSUNG = 'langsung';
    const SUMBER_SERVICE = 'service';

    const TIPE_DIBUAT = 'dibuat';
    const TIPE_STATUS = 'status';
    const TIPE_UPDATE = 'update';

    protected $fillable = [
        'type_hp',
        'brand',
        'storage',
        'warna',
        'kondisi',
        'sumber',
        'imei',
        'kelengkapan',
        'tanggal_masuk',
        'harga_beli',
        'biaya_perbaikan',
        'penjual',
        'tanggal_keluar',
        'harga_jual',
        'pembeli',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date:Y-m-d',
            'tanggal_keluar' => 'date:Y-m-d',
            'harga_beli' => 'decimal:2',
            'biaya_perbaikan' => 'decimal:2',
            'harga_jual' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (PhoneTrade $trade) {
            if (!$trade->kode) {
                $trade->forceFill([
                    'kode' => 'JB-' . str_pad((string) $trade->id, 4, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }

            $trade->histories()->create([
                'tipe' => self::TIPE_DIBUAT,
                'status_from' => null,
                'status_to' => $trade->status,
                'note' => 'Data HP ditambahkan.',
                'user_id' => auth()->id(),
            ]);
        });

        static::saving(function (PhoneTrade $trade) {
            if ($trade->exists
                && $trade->isDirty('status')
                && !static::isValidTransition((string) $trade->getOriginal('status'), $trade->status)) {
                throw ValidationException::withMessages([
                    'status' => 'Status Terjual tidak dapat dikembalikan ke Tersedia.',
                ]);
            }

            if ($trade->status === self::STATUS_STOK) {
                $trade->tanggal_keluar = null;
                $trade->pembeli = null;
            }
        });

        static::updated(function (PhoneTrade $trade) {
            $changes = $trade->monetaryChanges();
            $statusChanged = $trade->wasChanged('status');

            if ($statusChanged) {
                $note = 'Status diubah menjadi ' . $trade->status_label . '.';

                if (!empty($changes)) {
                    $note .= ' ' . implode('; ', $changes) . '.';
                }

                $trade->histories()->create([
                    'tipe' => self::TIPE_STATUS,
                    'status_from' => $trade->getOriginal('status'),
                    'status_to' => $trade->status,
                    'note' => $note,
                    'user_id' => auth()->id(),
                ]);

                return;
            }

            if (!empty($changes)) {
                $trade->histories()->create([
                    'tipe' => self::TIPE_UPDATE,
                    'status_from' => null,
                    'status_to' => null,
                    'note' => implode('; ', $changes) . '.',
                    'user_id' => auth()->id(),
                ]);
            }
        });
    }

    /**
     * Perubahan nominal yang perlu dicatat ke riwayat.
     */
    public function monetaryChanges(): array
    {
        $fields = [
            'harga_beli' => 'Harga beli',
            'biaya_perbaikan' => 'Biaya service',
            'harga_jual' => 'Harga jual',
        ];

        $skipJual = $this->wasChanged('status') && $this->status === self::STATUS_STOK;
        $changes = [];

        foreach ($fields as $field => $label) {
            if ($skipJual && $field === 'harga_jual') {
                continue;
            }

            if (!$this->wasChanged($field)) {
                continue;
            }

            $rawFrom = $this->getOriginal($field);
            $rawTo = $this->{$field};

            $from = ($rawFrom === null || $rawFrom === '') ? null : (float) $rawFrom;
            $to = ($rawTo === null || $rawTo === '') ? null : (float) $rawTo;

            if (($from ?? 0.0) === ($to ?? 0.0)) {
                continue;
            }

            if (($from === null || $from == 0.0) && $to !== null && $to > 0) {
                $changes[] = $label . ' ditetapkan: Rp ' . number_format($to, 0, ',', '.');
            } elseif ($to === null || $to == 0.0) {
                $changes[] = $label . ' dikosongkan';
            } else {
                $changes[] = $label . ' diubah menjadi: Rp ' . number_format($to, 0, ',', '.');
            }
        }

        return $changes;
    }

    public function histories()
    {
        return $this->hasMany(PhoneTradeHistory::class)->oldest();
    }

    public function getTotalModalAttribute(): float
    {
        return (float) $this->harga_beli + (float) $this->biaya_perbaikan;
    }

    public function getKeuntunganAttribute(): float
    {
        if ($this->status !== self::STATUS_TERJUAL || $this->harga_jual === null) {
            return 0.0;
        }

        return (float) $this->harga_jual - $this->total_modal;
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['sumber'] ?? null, fn($q, $v) => $q->where('sumber', $v))
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('tanggal_masuk', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('tanggal_masuk', '<=', $v))
            ->when($filters['search'] ?? null, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('kode', 'like', "%{$v}%")
                        ->orWhere('type_hp', 'like', "%{$v}%")
                        ->orWhere('brand', 'like', "%{$v}%")
                        ->orWhere('penjual', 'like', "%{$v}%")
                        ->orWhere('pembeli', 'like', "%{$v}%")
                        ->orWhere('keterangan', 'like', "%{$v}%");
                });
            });
    }

    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_STOK => [self::STATUS_TERJUAL],
            self::STATUS_TERJUAL => [],
        ];
    }

    public static function isValidTransition(string $from, string $to): bool
    {
        return in_array($to, static::allowedTransitions()[$from] ?? [], true);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_STOK => 'Tersedia',
            self::STATUS_TERJUAL => 'Terjual',
        ];
    }

    public static function statusClasses(): array
    {
        return [
            self::STATUS_STOK => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            self::STATUS_TERJUAL => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        ];
    }

    public static function kondisiLabels(): array
    {
        return [
            self::KONDISI_BARU => 'Baru',
            self::KONDISI_BEKAS => 'Bekas',
            self::KONDISI_MINUS => 'Minus / Perlu Perbaikan',
        ];
    }

    public static function sumberLabels(): array
    {
        return [
            self::SUMBER_LANGSUNG => 'Pembelian Langsung',
            self::SUMBER_SERVICE => 'Dari Service',
        ];
    }

    public static function sumberClasses(): array
    {
        return [
            self::SUMBER_LANGSUNG => 'bg-gray-100 text-gray-600 border-gray-200',
            self::SUMBER_SERVICE => 'bg-blue-100 text-blue-700 border-blue-200',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusClassAttribute(): string
    {
        return static::statusClasses()[$this->status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
    }

    public function getSumberLabelAttribute(): string
    {
        return static::sumberLabels()[$this->sumber] ?? ($this->sumber ?: 'Pembelian Langsung');
    }

    public function getSumberClassAttribute(): string
    {
        return static::sumberClasses()[$this->sumber] ?? 'bg-gray-100 text-gray-600 border-gray-200';
    }
}
