@php
$prevMargin = $prevPeriodTotals->revenue > 0 ? round(($prevPeriodTotals->net_profit / $prevPeriodTotals->revenue) * 100, 1) : 0;
$changeMargin = $prevMargin > 0 ? round((($profitMargin - $prevMargin) / $prevMargin) * 100, 1) : ($profitMargin > 0 ? null : 0);
$prevAvgProfit = $prevPeriodTotals->services > 0 ? round($prevPeriodTotals->net_profit / $prevPeriodTotals->services) : 0;
$changeAvgProfit = $prevAvgProfit > 0 ? round((($avgProfitPerService - $prevAvgProfit) / $prevAvgProfit) * 100, 1) : ($avgProfitPerService > 0 ? null : 0);
@endphp

{{-- Margin Keuntungan --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-50 to-amber-100 flex items-center justify-center flex-shrink-0 shadow-md">
        <i class="fas fa-percent text-yellow-600 text-lg"></i>
    </div>
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Margin Keuntungan</p>
        <p class="text-[10px] text-gray-400 italic leading-tight">Persentase keuntungan bersih dari pendapatan — semakin tinggi semakin bagus</p>
        <p class="text-xl font-bold mt-0.5" style="color:#1F2937;">{{ number_format($profitMargin, 1) }}%</p>
        @if($changeMargin !== null)
        <div class="flex items-center gap-1 mt-0.5">
            <i class="fas fa-{{ $changeMargin >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeMargin >= 0 ? 'emerald' : 'red' }}-500 text-[10px]"></i>
            <span class="text-[10px] font-medium text-{{ $changeMargin >= 0 ? 'emerald' : 'red' }}-600">{{ $changeMargin >= 0 ? '+' : '' }}{{ number_format($changeMargin, 1) }}%</span>
            <span class="text-[10px] text-gray-400">vs periode lalu</span>
        </div>
        @else
        <div class="flex items-center gap-1 mt-0.5">
            <span class="text-[10px] font-medium text-gray-400">— vs periode lalu</span>
        </div>
        @endif
    </div>
</div>

{{-- Rata-rata Keuntungan/Service --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-50 to-purple-100 flex items-center justify-center flex-shrink-0 shadow-md">
        <i class="fas fa-calculator text-violet-600 text-lg"></i>
    </div>
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Rata-rata Keuntungan/Service</p>
        <p class="text-[10px] text-gray-400 italic leading-tight">Rata-rata keuntungan bersih dari setiap service</p>
        <p class="text-xl font-bold mt-0.5" style="color:#1F2937;">Rp {{ number_format($avgProfitPerService, 0, ',', '.') }}</p>
        @if($changeAvgProfit !== null)
        <div class="flex items-center gap-1 mt-0.5">
            <i class="fas fa-{{ $changeAvgProfit >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeAvgProfit >= 0 ? 'emerald' : 'red' }}-500 text-[10px]"></i>
            <span class="text-[10px] font-medium text-{{ $changeAvgProfit >= 0 ? 'emerald' : 'red' }}-600">{{ $changeAvgProfit >= 0 ? '+' : '' }}{{ number_format($changeAvgProfit, 1) }}%</span>
            <span class="text-[10px] text-gray-400">vs periode lalu</span>
        </div>
        @else
        <div class="flex items-center gap-1 mt-0.5">
            <span class="text-[10px] font-medium text-gray-400">— vs periode lalu</span>
        </div>
        @endif
    </div>
</div>

{{-- Biaya Harian --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 flex items-center justify-center flex-shrink-0 shadow-md">
        <i class="fas fa-receipt text-orange-600 text-lg"></i>
    </div>
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Biaya Harian</p>
        <p class="text-[10px] text-gray-400 italic leading-tight">Biaya Operasional Harian (Bank Jago + Lainnya) — di luar gaji karyawan</p>
        <p class="text-xl font-bold mt-0.5 text-orange-600">Rp {{ number_format($periodTotals->biaya_harian, 0, ',', '.') }}</p>
        <div class="flex items-center gap-2 mt-1 text-[10px] text-gray-400">
            <span>Bank Jago: <span class="font-medium text-gray-600">Rp {{ number_format($periodTotals->bank_jago, 0, ',', '.') }}</span></span>
            <span class="text-gray-300">|</span>
            <span>Lainnya: <span class="font-medium text-gray-600">Rp {{ number_format($periodTotals->biaya_harian - $periodTotals->bank_jago, 0, ',', '.') }}</span></span>
        </div>
        @if($changeBiaya !== null)
        <div class="flex items-center gap-1 mt-0.5">
            <i class="fas fa-{{ $changeBiaya >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeBiaya >= 0 ? 'red' : 'emerald' }}-500 text-[10px]"></i>
            <span class="text-[10px] font-medium text-{{ $changeBiaya >= 0 ? 'red' : 'emerald' }}-600">{{ $changeBiaya >= 0 ? '+' : '' }}{{ number_format($changeBiaya, 1) }}%</span>
            <span class="text-[10px] text-gray-400">vs periode lalu</span>
        </div>
        @else
        <div class="flex items-center gap-1 mt-0.5">
            <span class="text-[10px] font-medium text-gray-400">— vs periode lalu</span>
        </div>
        @endif
    </div>
</div>

{{-- Total Pelanggan --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-50 to-blue-100 flex items-center justify-center flex-shrink-0 shadow-md">
        <i class="fas fa-users text-blue-600 text-lg"></i>
    </div>
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Pelanggan</p>
        <p class="text-[10px] text-gray-400 italic leading-tight">Jumlah seluruh pelanggan yang pernah tercatat</p>
        <p class="text-xl font-bold mt-0.5" style="color:#1F2937;">{{ number_format($totalCustomers) }}</p>
        <p class="text-xs text-gray-400">Seluruh masa</p>
    </div>
</div>
