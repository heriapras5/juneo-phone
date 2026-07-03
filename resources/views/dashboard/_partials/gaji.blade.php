@php
$prevJago = $prevPeriodTotals->bank_jago ?? 0;
$changeJago = $prevJago > 0 ? round((($periodTotals->bank_jago - $prevJago) / $prevJago) * 100, 1) : ($periodTotals->bank_jago > 0 ? null : 0);
@endphp

{{-- Gaji Edi --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center flex-shrink-0 shadow-md">
        <i class="fas fa-user-tie text-blue-600 text-lg"></i>
    </div>
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Gaji Edi</p>
        <p class="text-[10px] text-gray-400 italic leading-tight">Rp 60.000/hari × {{ $hariKerja }} hari kerja</p>
        <p class="text-xl font-bold mt-0.5" style="color:#1F2937;">Rp {{ number_format($periodTotals->gaji_edi, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $hariKerja }} hari kerja</p>
    </div>
</div>

{{-- Gaji Nur --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-50 to-rose-100 flex items-center justify-center flex-shrink-0 shadow-md">
        <i class="fas fa-user-tie text-rose-600 text-lg"></i>
    </div>
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Gaji Nur</p>
        <p class="text-[10px] text-gray-400 italic leading-tight">Rp 30.000/hari × {{ $hariKerja }} hari kerja</p>
        <p class="text-xl font-bold mt-0.5" style="color:#1F2937;">Rp {{ number_format($periodTotals->gaji_nur, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $hariKerja }} hari kerja</p>
    </div>
</div>

{{-- Tabungan Bank Jago --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-sky-100 flex items-center justify-center flex-shrink-0 shadow-md">
        <i class="fas fa-piggy-bank text-blue-600 text-lg"></i>
    </div>
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Tabungan Bank Jago</p>
        <p class="text-xl font-bold mt-0.5 text-blue-600">Rp {{ number_format($periodTotals->bank_jago, 0, ',', '.') }}</p>
        @if($changeJago !== null)
        <div class="flex items-center gap-1 mt-0.5">
            <i class="fas fa-{{ $changeJago >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $changeJago >= 0 ? 'emerald' : 'red' }}-500 text-[10px]"></i>
            <span class="text-[10px] font-medium text-{{ $changeJago >= 0 ? 'emerald' : 'red' }}-600">{{ $changeJago >= 0 ? '+' : '' }}{{ number_format($changeJago, 1) }}%</span>
            <span class="text-[10px] text-gray-400">vs periode lalu</span>
        </div>
        @else
        <div class="flex items-center gap-1 mt-0.5">
            <span class="text-[10px] font-medium text-gray-400">— vs periode lalu</span>
        </div>
        @endif
    </div>
</div>
