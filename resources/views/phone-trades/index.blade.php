@extends('layouts.app')

@section('title', 'Jual Beli HP')
@section('page-title', 'Jual Beli HP')

@section('content')
@php
    $statusLabels = \App\Models\PhoneTrade::statusLabels();
    $kondisiLabels = \App\Models\PhoneTrade::kondisiLabels();
    $sumberLabels = \App\Models\PhoneTrade::sumberLabels();
    $exportFilters = array_filter(request()->only(['search', 'status', 'sumber', 'from', 'to']), fn($v) => $v !== null && $v !== '');
    $exportQuery = array_merge($exportFilters, ['_ts' => now()->timestamp]);
@endphp

@if($errors->any())
<script>
window._pageData = {
    hasErrors: true,
    editing_id: {{ Js::from(old('editing_id')) }},
    old: {
        type_hp: {{ Js::from(old('type_hp')) }},
        brand: {{ Js::from(old('brand')) }},
        storage: {{ Js::from(old('storage')) }},
        warna: {{ Js::from(old('warna')) }},
        kondisi: {{ Js::from(old('kondisi')) }},
        sumber: {{ Js::from(old('sumber')) }},
        kelengkapan: {{ Js::from(old('kelengkapan')) }},
        tanggal_masuk: {{ Js::from(old('tanggal_masuk')) }},
        harga_beli: {{ Js::from(old('harga_beli')) }},
        biaya_perbaikan: {{ Js::from(old('biaya_perbaikan')) }},
        penjual: {{ Js::from(old('penjual')) }},
        status: {{ Js::from(old('status')) }},
        original_status: {{ Js::from(old('original_status')) }},
        tanggal_keluar: {{ Js::from(old('tanggal_keluar')) }},
        harga_jual: {{ Js::from(old('harga_jual')) }},
        pembeli: {{ Js::from(old('pembeli')) }},
        keterangan: {{ Js::from(old('keterangan')) }}
    }
};
</script>
@endif

@if(!empty($editTrade))
<script>
window._pageData = Object.assign(window._pageData || {}, { editTrade: {{ Js::from($editTrade) }} });
</script>
@endif

<div x-data="phoneTradePage">
    <div class="space-y-4">

        {{-- Filter --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" class="flex flex-wrap items-end gap-3 w-full sm:w-auto">
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Kode, HP, penjual, pembeli..."
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none w-56 max-w-full">
                    </div>
                    <div class="w-[calc(50%-0.5rem)] sm:w-auto">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select name="status"
                            class="w-full sm:w-auto px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            <option value="">Semua Status</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-[calc(50%-0.5rem)] sm:w-auto">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Sumber</label>
                        <select name="sumber"
                            class="w-full sm:w-auto px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            <option value="">Semua Sumber</option>
                            @foreach($sumberLabels as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['sumber'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-[calc(50%-0.5rem)] sm:w-auto">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Dari Tanggal</label>
                        <div class="relative w-full sm:w-44" x-data="dateField('{{ $filters['from'] ?? '' }}')">
                            <input type="date" name="from" x-ref="input" x-model="value"
                                @focus="focused = true" @blur="focused = false" @click="openPicker()"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                aria-label="Dari Tanggal" title="Dari Tanggal" autocomplete="off">
                            <div class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-left pointer-events-none"
                                :class="[value ? 'text-gray-900' : 'text-gray-400', focused ? 'border-yellow-500 ring-2 ring-yellow-500/30' : '']">
                                <span x-text="display">dd/mm/yyyy</span>
                            </div>
                            <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        </div>
                    </div>
                    <div class="w-[calc(50%-0.5rem)] sm:w-auto">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Sampai Tanggal</label>
                        <div class="relative w-full sm:w-44" x-data="dateField('{{ $filters['to'] ?? '' }}')">
                            <input type="date" name="to" x-ref="input" x-model="value"
                                @focus="focused = true" @blur="focused = false" @click="openPicker()"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                aria-label="Sampai Tanggal" title="Sampai Tanggal" autocomplete="off">
                            <div class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-left pointer-events-none"
                                :class="[value ? 'text-gray-900' : 'text-gray-400', focused ? 'border-yellow-500 ring-2 ring-yellow-500/30' : '']">
                                <span x-text="display">dd/mm/yyyy</span>
                            </div>
                            <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        </div>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 text-white rounded-lg text-sm font-medium hover:from-yellow-400 hover:to-amber-400 shadow-sm">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'sumber', 'from', 'to']))
                        <a href="{{ route('phone-trades.index') }}"
                            class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    @endif
                </form>

                <button type="button" @click="openCreateModal()"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm ml-auto flex-shrink-0">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
        </div>

        {{-- Daftar HP --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="rounded-xl" x-data="scrollSync">
                <div class="overflow-x-auto" x-ref="table" @scroll="syncFromTable">
                    <table class="w-full min-w-[1360px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-10">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Kode</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">HP</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Kondisi</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Sumber</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Tgl Masuk</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Modal</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Tgl Keluar</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Harga Jual</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Profit</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-28 sticky right-0 z-10 bg-gray-50">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($trades as $trade)
                            @php
                                $tradePayload = [
                                    'id' => $trade->id,
                                    'type_hp' => $trade->type_hp,
                                    'brand' => $trade->brand,
                                    'storage' => $trade->storage,
                                    'warna' => $trade->warna,
                                    'kondisi' => $trade->kondisi,
                                    'sumber' => $trade->sumber,
                                    'kelengkapan' => $trade->kelengkapan,
                                    'tanggal_masuk' => $trade->tanggal_masuk?->format('Y-m-d'),
                                    'harga_beli' => (float) $trade->harga_beli,
                                    'biaya_perbaikan' => (float) $trade->biaya_perbaikan,
                                    'penjual' => $trade->penjual,
                                    'status' => $trade->status,
                                    'tanggal_keluar' => $trade->tanggal_keluar?->format('Y-m-d'),
                                    'harga_jual' => $trade->harga_jual !== null ? (float) $trade->harga_jual : null,
                                    'pembeli' => $trade->pembeli,
                                    'keterangan' => $trade->keterangan,
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50/50" data-row="{{ $trade->id }}">
                                <td class="px-4 py-3 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration + ($trades->currentPage() - 1) * $trades->perPage() }}</td>
                                <td class="px-4 py-3 text-sm font-semibold whitespace-nowrap">
                                    <a href="{{ route('phone-trades.show', $trade) }}" class="text-yellow-600 hover:text-yellow-700 hover:underline">
                                        {{ $trade->kode ?? '-' }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('phone-trades.show', $trade) }}" class="block group">
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-yellow-600">{{ $trade->type_hp }}</div>
                                        <div class="text-[13px] text-gray-600 font-medium truncate max-w-[180px]"
                                            title="{{ collect([$trade->brand, $trade->storage, $trade->warna])->filter()->implode(' · ') ?: '-' }}">
                                            {{ collect([$trade->brand, $trade->storage, $trade->warna])->filter()->implode(' · ') ?: '-' }}
                                        </div>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{
                                        $trade->kondisi === 'baru' ? 'bg-blue-100 text-blue-700' :
                                        ($trade->kondisi === 'minus' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')
                                    }}">
                                        {{ $kondisiLabels[$trade->kondisi] ?? $trade->kondisi }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $trade->sumber_class }}">
                                        {{ $trade->sumber_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $trade->tanggal_masuk?->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 tabular-nums"
                                        title="Total modal: Rp {{ number_format($trade->total_modal, 0, ',', '.') }}">
                                        Rp {{ number_format($trade->harga_beli, 0, ',', '.') }}
                                    </div>
                                    @if((float) $trade->biaya_perbaikan > 0)
                                        <div class="text-[11px] text-amber-700 font-medium tabular-nums" title="Biaya service">
                                            + Rp {{ number_format($trade->biaya_perbaikan, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($trade->status === \App\Models\PhoneTrade::STATUS_TERJUAL)
                                        <div class="text-sm text-gray-900">{{ $trade->tanggal_keluar?->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-sm text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($trade->status === \App\Models\PhoneTrade::STATUS_TERJUAL)
                                        <div class="text-sm font-medium text-gray-900 tabular-nums"
                                            title="@if($trade->pembeli)Nama Pembeli: {{ $trade->pembeli }}@endif">
                                            Rp {{ number_format($trade->harga_jual, 0, ',', '.') }}
                                        </div>
                                    @elseif($trade->harga_jual !== null)
                                        <div class="text-sm font-medium text-gray-500 tabular-nums" title="Harga jual rencana">
                                            Rp {{ number_format($trade->harga_jual, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex flex-col items-center">
                                        <button type="button" data-status-badge
                                            @if(!empty(\App\Models\PhoneTrade::allowedTransitions()[$trade->status] ?? []))
                                            @click="toggleStatus({{ $trade->id }}, $event)"
                                            @endif
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $trade->status_class }} {{ !empty(\App\Models\PhoneTrade::allowedTransitions()[$trade->status] ?? []) ? 'cursor-pointer hover:opacity-80' : '' }}">
                                            {{ $trade->status_label }}
                                            @if(!empty(\App\Models\PhoneTrade::allowedTransitions()[$trade->status] ?? []))
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            @endif
                                        </button>
                                        <div x-show="rowState[{{ $trade->id }}]?.openStatus"
                                            data-status-dropdown
                                            x-transition:enter="transition-opacity duration-75"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition-opacity duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            :style="rowState[{{ $trade->id }}]?.statusStyle"
                                            class="fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-lg py-1 min-w-[140px] text-left">
                                            @php
                                                $allowedNext = \App\Models\PhoneTrade::allowedTransitions()[$trade->status] ?? [];
                                                $hasOptions = !empty($allowedNext);
                                            @endphp
                                            @foreach($allowedNext as $key)
                                                <button type="button"
                                                    @click.stop="closeRowDropdowns({{ $trade->id }}); openEditModal({{ Js::from($tradePayload) }}, '{{ $key }}')"
                                                    class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100 text-gray-700">
                                                    {{ \App\Models\PhoneTrade::statusLabels()[$key] ?? $key }}
                                                </button>
                                            @endforeach
                                            @unless($hasOptions)
                                                <div class="px-3 py-2 text-xs text-gray-400 italic">Tidak ada aksi tersedia</div>
                                            @endunless
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap tabular-nums">
                                    @if($trade->status === \App\Models\PhoneTrade::STATUS_TERJUAL)
                                        <span class="text-sm font-semibold {{ $trade->keuntungan >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                            Rp {{ number_format($trade->keuntungan, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap sticky right-0 z-10 bg-white hover:bg-gray-100">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('phone-trades.show', $trade) }}"
                                            class="p-1.5 text-gray-600 hover:bg-gray-50 rounded-lg" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button"
                                            @click="openEditModal({{ Js::from($tradePayload) }})"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            @click="confirmDelete = { url: '{{ route('phone-trades.destroy', $trade) }}', message: 'Yakin hapus data HP {{ $trade->type_hp }} ({{ $trade->kode }})?' }"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="px-5 py-12 text-center text-gray-400">
                                    @if(request()->anyFilled(['search', 'status', 'sumber', 'from', 'to']))
                                        <i class="fas fa-search text-3xl mb-2 block"></i>
                                        Data tidak ditemukan
                                    @else
                                        <i class="fas fa-mobile-screen-button text-3xl mb-2 block"></i>
                                        Belum ada data jual beli HP
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="sticky bottom-0 z-20 overflow-x-auto h-4 bg-white border-t border-gray-200" x-ref="mirror" @scroll="syncFromMirror">
                    <div :style="{ width: ($refs.table?.scrollWidth||0)+'px', height:'1px' }"></div>
                </div>
            </div>

            @if($trades->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">
                    {{ $trades->links() }}
                </div>
            @endif
        </div>

        {{-- Laporan Jual Beli HP (kartu di bawah tabel) --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-chart-simple text-yellow-500"></i>
                        Laporan Jual Beli HP
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">
                        Ringkasan penjualan mengikuti periode transaksi keluar.
                        @if($hasPeriodFilter)
                            Periode: {{ $filters['from'] ? \Carbon\Carbon::parse($filters['from'])->format('d M Y') : 'awal' }}
                            s.d. {{ $filters['to'] ? \Carbon\Carbon::parse($filters['to'])->format('d M Y') : 'sekarang' }}.
                        @else
                            Menampilkan seluruh periode.
                        @endif
                        Stok dan modal stok selalu menunjukkan kondisi saat ini.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('phone-trades.export-pdf', $exportQuery) }}" target="_blank" rel="noopener noreferrer"
                        class="px-3 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-medium hover:bg-red-100 flex items-center gap-1.5">
                        <i class="fas fa-file-pdf"></i> Laporan PDF
                    </a>
                    <a href="{{ route('phone-trades.export-excel', $exportQuery) }}" target="_blank" rel="noopener noreferrer"
                        class="px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-medium hover:bg-emerald-100 flex items-center gap-1.5">
                        <i class="fas fa-file-excel"></i> Laporan Excel
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-xs font-medium">
                        <span class="w-7 h-7 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center">
                            <i class="fas fa-mobile-screen-button text-xs"></i>
                        </span>
                        Stok Tersedia
                    </div>
                    <p class="mt-3 text-xl font-bold text-gray-900">{{ number_format($summary['stock_count'], 0, ',', '.') }} <span class="text-xs font-medium text-gray-400">unit</span></p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-xs font-medium">
                        <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                            <i class="fas fa-wallet text-xs"></i>
                        </span>
                        Modal Stok
                    </div>
                    <p class="mt-3 text-lg font-bold text-gray-900 tabular-nums">Rp {{ number_format($summary['stock_modal'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-xs font-medium">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-check-circle text-xs"></i>
                        </span>
                        Terjual
                    </div>
                    <p class="mt-3 text-xl font-bold text-gray-900">{{ number_format($summary['sold_count'], 0, ',', '.') }} <span class="text-xs font-medium text-gray-400">unit</span></p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-xs font-medium">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-xs"></i>
                        </span>
                        Omzet
                    </div>
                    <p class="mt-3 text-lg font-bold text-gray-900 tabular-nums">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-xs font-medium">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                            <i class="fas fa-box-open text-xs"></i>
                        </span>
                        Modal Terjual
                    </div>
                    <p class="mt-3 text-lg font-bold text-gray-900 tabular-nums">Rp {{ number_format($summary['modal_terjual'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-xs font-medium">
                        <span class="w-7 h-7 rounded-lg {{ $summary['laba'] >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center">
                            <i class="fas fa-chart-line text-xs"></i>
                        </span>
                        Profit
                    </div>
                    <p class="mt-3 text-lg font-bold tabular-nums {{ $summary['laba'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        Rp {{ number_format($summary['laba'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Form Jual Beli HP --}}
    <div x-show="showModal" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-3xl mx-4 border border-gray-200 relative max-h-[90vh] overflow-y-auto">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-400"></div>
            <div class="flex items-center justify-between mb-6 mt-2">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editingId ? 'Edit Jual Beli HP' : 'Tambah HP'"></h3>
                <button @click="closeModal()" title="Tutup" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>

            <form :action="editingId ? '{{ url('phone-trades') }}/' + editingId : '{{ route('phone-trades.store') }}'" method="POST"
                @submit="if (loading) { $event.preventDefault(); return; } loading = true">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'">
                <input type="hidden" name="editing_id" :value="editingId || ''">
                <input type="hidden" name="original_status" :value="originalStatus">

                <div class="space-y-5">
                    {{-- Data HP --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Data HP</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tipe HP <span class="text-yellow-600">*</span></label>
                                <input type="text" name="type_hp" x-model="formData.type_hp" x-ref="firstField" required
                                    placeholder="Contoh: iPhone 13 Pro Max"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('type_hp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Brand</label>
                                <input type="text" name="brand" x-model="formData.brand"
                                    placeholder="Apple, Samsung, Xiaomi..."
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">RAM / Penyimpanan</label>
                                <input type="text" name="storage" x-model="formData.storage"
                                    placeholder="8/256 GB"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('storage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Warna</label>
                                <input type="text" name="warna" x-model="formData.warna"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('warna') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kondisi <span class="text-yellow-600">*</span></label>
                                <select name="kondisi" x-model="formData.kondisi" required
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                    @foreach($kondisiLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('kondisi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Sumber Perolehan <span class="text-yellow-600">*</span></label>
                                <select name="sumber" x-model="formData.sumber" required
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                    @foreach($sumberLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('sumber') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelengkapan</label>
                                <input type="text" name="kelengkapan" x-model="formData.kelengkapan"
                                    placeholder="Unit, charger, dus..."
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('kelengkapan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Pembelian --}}
                    <div class="pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Pembelian / Masuk</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Masuk <span class="text-yellow-600">*</span></label>
                                <input type="date" name="tanggal_masuk" x-model="formData.tanggal_masuk" required
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('tanggal_masuk') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Harga Beli / Modal <span class="text-yellow-600">*</span></label>
                                <input type="text" inputmode="numeric" name="harga_beli"
                                    :value="formatNumber(formData.harga_beli)" @input="onCurrencyInput('harga_beli', $event)" required
                                    placeholder="0"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-right focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none tabular-nums">
                                @error('harga_beli') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Biaya Service</label>
                                <input type="text" inputmode="numeric" name="biaya_perbaikan"
                                    :value="formatNumber(formData.biaya_perbaikan)" @input="onCurrencyInput('biaya_perbaikan', $event)"
                                    placeholder="0"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-right focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none tabular-nums">
                                @error('biaya_perbaikan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Penjual</label>
                                <input type="text" name="penjual" x-model="formData.penjual"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('penjual') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Penjualan --}}
                    <div class="pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Penjualan / Keluar</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Status <span class="text-yellow-600">*</span></label>
                                <select name="status" x-model="formData.status" required
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                    <option value="stok" x-show="!statusLocked" :disabled="statusLocked">Tersedia</option>
                                    <option value="terjual">Terjual</option>
                                </select>
                                <p x-show="statusLocked" class="text-[11px] text-amber-600 mt-1">Status Terjual sudah final dan tidak dapat dikembalikan ke Tersedia.</p>
                                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex items-end">
                                <div x-show="formData.status === 'terjual'" class="w-full rounded-lg bg-emerald-50 border border-emerald-100 px-4 py-2">
                                    <p class="text-[11px] text-emerald-700/70">Perkiraan profit (setelah biaya perbaikan)</p>
                                    <p class="text-sm font-bold tabular-nums" :class="labaPreview >= 0 ? 'text-emerald-600' : 'text-red-600'"
                                        x-text="'Rp ' + formatNumber(labaPreview)"></p>
                                </div>
                                <p x-show="formData.status !== 'terjual'" class="text-xs text-gray-400 pb-2">HP masih tersedia.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Harga Jual</label>
                                <input type="text" inputmode="numeric" name="harga_jual"
                                    :value="formatNumber(formData.harga_jual)" @input="onCurrencyInput('harga_jual', $event)"
                                    placeholder="0"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-right focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none tabular-nums">
                                <p class="text-[10px] text-gray-400 mt-1">Harga jual bisa diisi sebelum status Terjual.</p>
                                @error('harga_jual') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div x-show="formData.status === 'terjual'" x-cloak>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Keluar <span class="text-yellow-600">*</span></label>
                                <input type="date" name="tanggal_keluar" x-model="formData.tanggal_keluar"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('tanggal_keluar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div x-show="formData.status === 'terjual'" x-cloak>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Pembeli</label>
                                <input type="text" name="pembeli" x-model="formData.pembeli"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                @error('pembeli') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Keterangan</label>
                        <textarea name="keterangan" x-model="formData.keterangan" rows="2"
                            placeholder="Catatan tambahan, minus, atau riwayat perbaikan..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"></textarea>
                        @error('keterangan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="loading"
                        class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i x-show="!loading" class="fas fa-save"></i>
                        <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                        <span x-text="editingId ? 'Update' : 'Save'"></span>
                    </button>
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
