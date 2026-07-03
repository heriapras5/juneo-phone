<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    const STATUS_MENUNGGU = 'menunggu';
    const STATUS_DIKERJAKAN = 'dikerjakan';
    const STATUS_SELESAI = 'selesai';
    const STATUS_DIAMBIL = 'diambil';
    const STATUS_BATAL = 'batal';

    const PRIORITAS_RENDAH = 'rendah';
    const PRIORITAS_NORMAL = 'normal';
    const PRIORITAS_TINGGI = 'tinggi';
    const PRIORITAS_URGENT = 'urgent';

    protected $fillable = [
        'service_number',
        'tanggal',
        'tanggal_dikerjakan',
        'customer_id',
        'type_hp',
        'kerusakan',
        'garansi',
        'pengeluaran',
        'jasa',
        'keuntungan',
        'keterangan',
        'status',
        'tanggal_selesai',
        'estimasi_selesai',
        'teknisi',
        'kebutuhan_pribadi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanggal_dikerjakan' => 'date',
            'tanggal_selesai' => 'date',
            'estimasi_selesai' => 'date',
            'pengeluaran' => 'decimal:2',
            'jasa' => 'decimal:2',
            'keuntungan' => 'decimal:2',
        ];
    }

    public function scopeQueue($query)
    {
        $today = now()->startOfDay()->toDateString();
        $fiveDaysAgo = now()->subDays(5)->toDateString();
        $oneDayAhead = now()->addDay()->toDateString();
        $threeDaysAgo = now()->subDays(3)->toDateString();
        $threeDaysAhead = now()->addDays(3)->toDateString();
        $oneDayAgo = now()->subDay()->toDateString();

        return $query->whereIn('status', ['menunggu', 'dikerjakan'])
            ->orderByRaw("
                CASE
                    WHEN estimasi_selesai IS NOT NULL AND estimasi_selesai < ? THEN 1
                    WHEN tanggal <= ? THEN 2
                    WHEN estimasi_selesai IS NOT NULL AND estimasi_selesai <= ? THEN 3
                    WHEN tanggal <= ? THEN 4
                    WHEN estimasi_selesai IS NOT NULL AND estimasi_selesai <= ? THEN 5
                    WHEN tanggal <= ? THEN 6
                    ELSE 7
                END
            ", [$today, $fiveDaysAgo, $oneDayAhead, $threeDaysAgo, $threeDaysAhead, $oneDayAgo])
            ->orderBy('tanggal')
            ->orderBy('created_at');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function histories()
    {
        return $this->hasMany(ServiceHistory::class)->oldest();
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }

    public static function statusLabels(): array
    {
        return [
            'menunggu' => 'Menunggu',
            'dikerjakan' => 'Dikerjakan',
            'selesai' => 'Selesai',
            'diambil' => 'Diambil',
            'batal' => 'Batal',
        ];
    }

    public static function statusClasses(): array
    {
        return [
            'menunggu' => 'bg-yellow-100 text-yellow-700',
            'dikerjakan' => 'bg-blue-100 text-blue-700',
            'selesai' => 'bg-emerald-100 text-emerald-700',
            'diambil' => 'bg-gray-100 text-gray-600',
            'batal' => 'bg-red-100 text-red-700',
        ];
    }

    public static function prioritasLabels(): array
    {
        return [
            'urgent' => 'Urgent',
            'tinggi' => 'Tinggi',
            'normal' => 'Normal',
            'rendah' => 'Rendah',
        ];
    }

    public static function prioritasClasses(): array
    {
        return [
            'urgent' => 'bg-red-100 text-red-700',
            'tinggi' => 'bg-orange-100 text-orange-700',
            'normal' => 'bg-blue-100 text-blue-700',
            'rendah' => 'bg-gray-100 text-gray-600',
        ];
    }

    public static function allowedTransitions(): array
    {
        return [
            'menunggu' => ['dikerjakan', 'batal'],
            'dikerjakan' => ['selesai', 'batal'],
            'selesai' => ['diambil'],
            'diambil' => [],
            'batal' => [],
        ];
    }

    public static function isValidTransition(string $from, string $to): bool
    {
        return in_array($to, static::allowedTransitions()[$from] ?? []);
    }

    public function getStatusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusClass(): string
    {
        return static::statusClasses()[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    public function getDisplayPriority(): string
    {
        if ($this->estimasi_selesai) {
            $hariTersisa = now()->startOfDay()->diffInDays($this->estimasi_selesai, false);
            if ($hariTersisa < 0) return 'urgent';
            if ($hariTersisa <= 1) return 'tinggi';
            if ($hariTersisa <= 3) return 'normal';
            return 'rendah';
        }

        if (!$this->tanggal) return 'normal';

        $hariKe = $this->tanggal->diffInDays(now()->startOfDay()) + 1;
        if ($hariKe > 5) return 'urgent';
        if ($hariKe > 3) return 'tinggi';
        if ($hariKe > 1) return 'normal';
        return 'rendah';
    }

    public function getDisplayPriorityLabel(): string
    {
        return static::prioritasLabels()[$this->getDisplayPriority()] ?? $this->getDisplayPriority();
    }

    public function getDisplayPriorityClass(): string
    {
        return static::prioritasClasses()[$this->getDisplayPriority()] ?? 'bg-gray-100 text-gray-600';
    }

    public function getPrioritasLabel(): string
    {
        return $this->getDisplayPriorityLabel();
    }

    public function getPrioritasClass(): string
    {
        return $this->getDisplayPriorityClass();
    }

    public function getDeadlineStatus(): string
    {
        if (in_array($this->status, ['selesai', 'diambil', 'batal'])) return 'done';

        if (!$this->estimasi_selesai) return 'none';

        $sisa = now()->startOfDay()->diffInDays($this->estimasi_selesai, false);

        if ($sisa > 3) return 'on_track';
        if ($sisa > 0) return 'urgent';
        return 'overdue';
    }

    public static function deadlineLabels(): array
    {
        return [
            'done' => '-',
            'none' => '-',
            'on_track' => 'Tepat',
            'urgent' => 'Mendesak',
            'overdue' => 'Terlambat',
        ];
    }

    public static function deadlineClasses(): array
    {
        return [
            'done' => 'text-gray-300 border-gray-100',
            'none' => 'text-gray-300 border-gray-100',
            'on_track' => 'text-emerald-600 border-emerald-300',
            'urgent' => 'text-yellow-600 border-yellow-300',
            'overdue' => 'text-red-600 border-red-300',
        ];
    }

    public function getDeadlineLabel(): string
    {
        return static::deadlineLabels()[$this->getDeadlineStatus()] ?? '-';
    }

    public function getDeadlineClass(): string
    {
        return static::deadlineClasses()[$this->getDeadlineStatus()] ?? 'bg-gray-100 text-gray-400';
    }

    protected static function booted()
    {
        static::creating(function ($service) {
            $service->keuntungan = (float) $service->jasa - (float) $service->pengeluaran;
            $service->service_number = static::generateServiceNumber();

            if ($service->status === static::STATUS_BATAL) {
                $service->pengeluaran = 0;
                $service->jasa = 0;
                $service->keuntungan = 0;
                $service->kebutuhan_pribadi = 0;
            }
        });

        static::updating(function ($service) {
            $service->keuntungan = (float) $service->jasa - (float) $service->pengeluaran;

            if ($service->isDirty('status')) {
                $originalStatus = $service->getOriginal('status');
                $newStatus = $service->status;

                if (!static::isValidTransition($originalStatus, $newStatus)) {
                    throw new \InvalidArgumentException(
                        "Transisi status tidak valid: " .
                        (static::statusLabels()[$originalStatus] ?? $originalStatus) . " → " .
                        (static::statusLabels()[$newStatus] ?? $newStatus)
                    );
                }

                if ($newStatus === static::STATUS_DIKERJAKAN && !$service->getOriginal('tanggal_dikerjakan')) {
                    $service->tanggal_dikerjakan = now();
                }
                if (in_array($newStatus, [static::STATUS_SELESAI, static::STATUS_DIAMBIL]) && !$service->getOriginal('tanggal_selesai')) {
                    $service->tanggal_selesai = now();
                }
                if ($newStatus === static::STATUS_BATAL) {
                    $service->pengeluaran = 0;
                    $service->jasa = 0;
                    $service->keuntungan = 0;
                    $service->kebutuhan_pribadi = 0;
                }
            }
        });

        static::updated(function ($service) {
            if ($service->wasChanged('status')) {
                $service->histories()->create([
                    'status_from' => $service->getOriginal('status'),
                    'status_to' => $service->status,
                    'user_id' => auth()->id(),
                ]);
            }
        });
    }

    protected static function generateServiceNumber()
    {
        $prefix = 'SRV-' . now()->format('Ymd') . '-';

        return DB::transaction(function () use ($prefix) {
            $last = static::where('service_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('service_number', 'desc')
                ->value('service_number');

            if ($last) {
                $lastNum = (int) substr($last, -5);
                $newNum = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $newNum = '00001';
            }

            return $prefix . $newNum;
        });
    }
}
