@php
$rev = $periodTotals->revenue;
$prevRev = $prevPeriodTotals->revenue ?? 0;
$changeRev = $prevRev > 0 ? round((($rev - $prevRev) / $prevRev) * 100, 1) : ($rev > 0 ? null : 0);

$biayaTotal = $periodTotals->pengeluaran;
$prevBiayaTotal = $prevPeriodTotals->pengeluaran ?? 0;
$changeBiayaTotal = $prevBiayaTotal > 0 ? round((($biayaTotal - $prevBiayaTotal) / $prevBiayaTotal) * 100, 1) : ($biayaTotal > 0 ? null : 0);

$net = $periodTotals->net_profit;
$prevNet = $prevPeriodTotals->net_profit ?? 0;
$changeNet = $prevNet > 0 ? round((($net - $prevNet) / $prevNet) * 100, 1) : ($net > 0 ? null : 0);
@endphp

{{-- Pendapatan --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group">
    <div class="p-5">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Pendapatan {{ $periodLabels[$period] }}</p>
                <p class="text-[10px] text-gray-400">{{ $fromDate->format('d M') }} — {{ $toDate->format('d M Y') }}</p>
                <p class="text-[10px] text-gray-400 italic mt-0.5">Total uang masuk dari jasa service — belum dipotong biaya apapun</p>
                <p class="text-2xl font-bold mt-1" style="color:#1F2937;">Rp {{ number_format($periodTotals->revenue, 0, ',', '.') }}</p>
                @if($changeRev !== null)
                <div class="flex items-center gap-1 mt-1.5">
                    <i class="fas fa-{{ $changeRev >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeRev >= 0 ? 'emerald' : 'red' }}-500 text-xs"></i>
                    <span class="text-xs font-medium text-{{ $changeRev >= 0 ? 'emerald' : 'red' }}-600">{{ $changeRev >= 0 ? '+' : '' }}{{ number_format($changeRev, 1) }}%</span>
                    <span class="text-xs text-gray-400">vs periode lalu</span>
                </div>
                @else
                <div class="flex items-center gap-1 mt-1.5">
                    <span class="text-xs font-medium text-gray-400">— vs periode lalu</span>
                </div>
                @endif
            </div>
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-money-bill-wave text-emerald-600 text-lg"></i>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ $periodTotals->services }} service tercatat</p>
        <div class="mt-1">
            <svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">
                <path d="M0,25 Q25,15 45,20 T85,10 T125,14 T165,6 T200,12" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round"/>
                <path d="M0,25 Q25,15 45,20 T85,10 T125,14 T165,6 T200,12 L200,30 L0,30 Z" fill="url(#sparkEmerald)" opacity="0.15"/>
            </svg>
        </div>
    </div>
    <div class="h-1"></div>
</div>

{{-- Pengeluaran --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group">
    <div class="p-5">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Pengeluaran {{ $periodLabels[$period] }}</p>
                <p class="text-[10px] text-gray-400">{{ $fromDate->format('d M') }} — {{ $toDate->format('d M Y') }}</p>
                <p class="text-[10px] text-gray-400 italic mt-0.5">Sparepart + Kebutuhan Pribadi + Gaji + Biaya Operasional — seluruh biaya operasional</p>
                <p class="text-2xl font-bold mt-1 text-red-500">Rp {{ number_format($periodTotals->pengeluaran, 0, ',', '.') }}</p>
                <div class="flex items-center gap-2 mt-1.5 text-xs">
                    <span class="text-gray-400">Modal:</span>
                    <span class="font-medium text-red-500">Rp {{ number_format($periodTotals->expenses, 0, ',', '.') }}</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-400">Gaji:</span>
                    <span class="font-medium text-purple-600">Rp {{ number_format($periodTotals->total_gaji, 0, ',', '.') }}</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-400">Operasional:</span>
                    <span class="font-medium text-orange-600">Rp {{ number_format($periodTotals->biaya_harian, 0, ',', '.') }}</span>
                </div>
                @if($changeBiayaTotal !== null)
                <div class="flex items-center gap-1 mt-1.5">
                    <i class="fas fa-{{ $changeBiayaTotal >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeBiayaTotal >= 0 ? 'red' : 'emerald' }}-500 text-xs"></i>
                    <span class="text-xs font-medium text-{{ $changeBiayaTotal >= 0 ? 'red' : 'emerald' }}-600">{{ $changeBiayaTotal >= 0 ? '+' : '' }}{{ number_format($changeBiayaTotal, 1) }}%</span>
                    <span class="text-xs text-gray-400">vs periode lalu</span>
                </div>
                @else
                <div class="flex items-center gap-1 mt-1.5">
                    <span class="text-xs font-medium text-gray-400">— vs periode lalu</span>
                </div>
                @endif
            </div>
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-red-50 to-rose-100 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-cart-shopping text-red-600 text-lg"></i>
            </div>
        </div>
        <div class="mt-1">
            <svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">
                <path d="M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round"/>
                <path d="M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10 L200,30 L0,30 Z" fill="url(#sparkAmber)" opacity="0.15"/>
            </svg>
        </div>
    </div>
    <div class="h-1"></div>
</div>

{{-- Keuntungan Bersih --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group">
    <div class="p-5">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Keuntungan Bersih {{ $periodLabels[$period] }}</p>
                <p class="text-[10px] text-gray-400">{{ $fromDate->format('d M') }} — {{ $toDate->format('d M Y') }}</p>
                <p class="text-[10px] text-gray-400 italic mt-0.5">Sisa uang setelah Pendapatan dikurangi seluruh Pengeluaran</p>
                <p class="text-2xl font-bold mt-1 {{ $periodTotals->net_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    Rp {{ number_format($periodTotals->net_profit, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1.5">Pendapatan - Pengeluaran</p>
                @if($changeNet !== null)
                <div class="flex items-center gap-1 mt-1">
                    <i class="fas fa-{{ $changeNet >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeNet >= 0 ? 'emerald' : 'red' }}-500 text-xs"></i>
                    <span class="text-xs font-medium text-{{ $changeNet >= 0 ? 'emerald' : 'red' }}-600">{{ $changeNet >= 0 ? '+' : '' }}{{ number_format($changeNet, 1) }}%</span>
                    <span class="text-xs text-gray-400">vs periode lalu</span>
                </div>
                @else
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium text-gray-400">— vs periode lalu</span>
                </div>
                @endif
            </div>
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-coins text-emerald-600 text-lg"></i>
            </div>
        </div>
        <div class="mt-2">
            <svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">
                <path d="M0,22 Q30,18 50,20 T100,12 T140,16 T180,5 T200,8" fill="none" stroke="{{ $periodTotals->net_profit >= 0 ? '#10B981' : '#EF4444' }}" stroke-width="2" stroke-linecap="round"/>
                <path d="M0,22 Q30,18 50,20 T100,12 T140,16 T180,5 T200,8 L200,30 L0,30 Z" fill="{{ $periodTotals->net_profit >= 0 ? 'url(#sparkEmerald)' : 'url(#sparkAmber)' }}" opacity="0.15"/>
            </svg>
        </div>
    </div>
    <div class="h-1"></div>
</div>
