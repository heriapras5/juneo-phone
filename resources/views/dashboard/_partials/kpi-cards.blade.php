@php
$profitPositive = $periodTotals->profit >= 0;
@endphp

{{-- Pengeluaran (Modal Sparepart) --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group flex flex-col h-full transition-all duration-200 ease-in-out hover:-translate-y-1 hover:shadow-lg">
    <div class="p-4 sm:p-5 flex-1 flex flex-col">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Pengeluaran {{ $periodLabels[$period] }}</p>
                <p class="text-[10px] text-gray-400 italic mt-1 hidden sm:block">Modal sparepart — total biaya beli komponen service</p>
                <p class="text-xl sm:text-2xl font-bold mt-1 text-red-500">Rp {{ number_format($periodTotals->pengeluaran, 0, ',', '.') }}</p>
                @if($changePengeluaran !== null)
                <div class="flex items-center gap-1 mt-1">
                    <i class="fas fa-{{ $changePengeluaran >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changePengeluaran >= 0 ? 'red' : 'emerald' }}-500 text-xs"></i>
                    <span class="text-xs font-medium text-{{ $changePengeluaran >= 0 ? 'red' : 'emerald' }}-600">{{ $changePengeluaran >= 0 ? '+' : '' }}{{ number_format($changePengeluaran, 1) }}%</span>
                    <span class="text-xs text-gray-400">vs periode lalu</span>
                </div>
                @else
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium text-gray-400">— vs periode lalu</span>
                </div>
                @endif
            </div>
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-red-50 to-rose-100 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-cart-shopping text-red-600 text-sm sm:text-lg"></i>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">{{ $periodTotals->services }} service tercatat</p>
        <div class="mt-auto pt-3 hidden sm:block">
            <svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">
                <path d="M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round"/>
                <path d="M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10 L200,30 L0,30 Z" fill="url(#sparkAmber)" opacity="0.15"/>
            </svg>
        </div>
    </div>
    <div class="h-1 bg-red-500"></div>
</div>

{{-- Omzet --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group flex flex-col h-full transition-all duration-200 ease-in-out hover:-translate-y-1 hover:shadow-lg">
    <div class="p-4 sm:p-5 flex-1 flex flex-col">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Omzet {{ $periodLabels[$period] }}</p>
                <p class="text-[10px] text-gray-400 italic mt-1 hidden sm:block">Total tagihan jasa service ke pelanggan — belum dipotong biaya apapun</p>
                <p class="text-xl sm:text-2xl font-bold mt-1 text-gray-900">Rp {{ number_format($periodTotals->omzet, 0, ',', '.') }}</p>
                @if($changeOmzet !== null)
                <div class="flex items-center gap-1 mt-1">
                    <i class="fas fa-{{ $changeOmzet >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeOmzet >= 0 ? 'emerald' : 'red' }}-500 text-xs"></i>
                    <span class="text-xs font-medium text-{{ $changeOmzet >= 0 ? 'emerald' : 'red' }}-600">{{ $changeOmzet >= 0 ? '+' : '' }}{{ number_format($changeOmzet, 1) }}%</span>
                    <span class="text-xs text-gray-400">vs periode lalu</span>
                </div>
                @else
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium text-gray-400">— vs periode lalu</span>
                </div>
                @endif
            </div>
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-money-bill-wave text-emerald-600 text-sm sm:text-lg"></i>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">{{ $periodTotals->services }} service tercatat</p>
        <div class="mt-auto pt-3 hidden sm:block">
            <svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">
                <path d="M0,25 Q25,15 45,20 T85,10 T125,14 T165,6 T200,12" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round"/>
                <path d="M0,25 Q25,15 45,20 T85,10 T125,14 T165,6 T200,12 L200,30 L0,30 Z" fill="url(#sparkEmerald)" opacity="0.15"/>
            </svg>
        </div>
    </div>
    <div class="h-1 bg-emerald-500"></div>
</div>

{{-- Kebutuhan Pribadi --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group flex flex-col h-full transition-all duration-200 ease-in-out hover:-translate-y-1 hover:shadow-lg">
    <div class="p-4 sm:p-5 flex-1 flex flex-col">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Kebutuhan Pribadi {{ $periodLabels[$period] }}</p>
                <p class="text-[10px] text-gray-400 italic mt-1 hidden sm:block">Bank Jago + Gaji Edi + Gaji Nur + Lainnya — total biaya kebutuhan harian</p>
                <p class="text-xl sm:text-2xl font-bold mt-1 text-orange-600">Rp {{ number_format($periodTotals->kebutuhan_pribadi, 0, ',', '.') }}</p>
                <div class="hidden sm:flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs">
                    <span class="text-gray-400">Bank Jago:</span>
                    <span class="font-medium text-blue-600">Rp {{ number_format($periodTotals->bank_jago, 0, ',', '.') }}</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-400">Gaji Edi:</span>
                    <span class="font-medium text-purple-600">Rp {{ number_format($periodTotals->gaji_edi, 0, ',', '.') }}</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-400">Gaji Nur:</span>
                    <span class="font-medium text-purple-600">Rp {{ number_format($periodTotals->gaji_nur, 0, ',', '.') }}</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-400">Lainnya:</span>
                    <span class="font-medium text-amber-600">Rp {{ number_format($periodTotals->lainnya ?? 0, 0, ',', '.') }}</span>
                </div>
                @if($changeKebutuhan !== null)
                <div class="flex items-center gap-1 mt-1">
                    <i class="fas fa-{{ $changeKebutuhan >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeKebutuhan >= 0 ? 'red' : 'emerald' }}-500 text-xs"></i>
                    <span class="text-xs font-medium text-{{ $changeKebutuhan >= 0 ? 'red' : 'emerald' }}-600">{{ $changeKebutuhan >= 0 ? '+' : '' }}{{ number_format($changeKebutuhan, 1) }}%</span>
                    <span class="text-xs text-gray-400">vs periode lalu</span>
                </div>
                @else
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium text-gray-400">— vs periode lalu</span>
                </div>
                @endif
            </div>
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-wallet text-orange-600 text-sm sm:text-lg"></i>
            </div>
        </div>
        <div class="mt-auto pt-3 hidden sm:block">
            <svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">
                <path d="M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round"/>
                <path d="M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10 L200,30 L0,30 Z" fill="url(#sparkAmber)" opacity="0.15"/>
            </svg>
        </div>
    </div>
    <div class="h-1 bg-orange-500"></div>
</div>

{{-- Profit --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group flex flex-col h-full transition-all duration-200 ease-in-out hover:-translate-y-1 hover:shadow-lg">
    <div class="p-4 sm:p-5 flex-1 flex flex-col">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Profit {{ $periodLabels[$period] }}</p>
                <p class="text-[10px] text-gray-400 italic mt-1 hidden sm:block">Sisa keuntungan setelah dikurangi seluruh Kebutuhan Pribadi</p>
                <p class="text-xl sm:text-2xl font-bold mt-1 {{ $profitPositive ? 'text-emerald-600' : 'text-red-600' }}">
                    Rp {{ number_format($periodTotals->profit, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1 hidden sm:block">Omzet - Pengeluaran - Kebutuhan Pribadi</p>
                @if($changeProfit !== null)
                <div class="flex items-center gap-1 mt-1">
                    <i class="fas fa-{{ $changeProfit >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeProfit >= 0 ? 'emerald' : 'red' }}-500 text-xs"></i>
                    <span class="text-xs font-medium text-{{ $changeProfit >= 0 ? 'emerald' : 'red' }}-600">{{ $changeProfit >= 0 ? '+' : '' }}{{ number_format($changeProfit, 1) }}%</span>
                    <span class="text-xs text-gray-400">vs periode lalu</span>
                </div>
                @else
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium text-gray-400">— vs periode lalu</span>
                </div>
                @endif
            </div>
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-coins text-emerald-600 text-sm sm:text-lg"></i>
            </div>
        </div>
        <div class="mt-auto pt-3 hidden sm:block">
            <svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">
                <path d="M0,22 Q30,18 50,20 T100,12 T140,16 T180,5 T200,8" fill="none" stroke="{{ $profitPositive ? '#10B981' : '#EF4444' }}" stroke-width="2" stroke-linecap="round"/>
                <path d="M0,22 Q30,18 50,20 T100,12 T140,16 T180,5 T200,8 L200,30 L0,30 Z" fill="{{ $profitPositive ? 'url(#sparkEmerald)' : 'url(#sparkAmber)' }}" opacity="0.15"/>
            </svg>
        </div>
    </div>
    <div class="h-1 {{ $profitPositive ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
</div>
