@extends('layouts.app')

@section('title', 'Detail Jual Beli HP')
@section('page-title', 'Detail Jual Beli HP')

@section('content')
@php
    $kondisiLabels = \App\Models\PhoneTrade::kondisiLabels();
    $statusLabels = \App\Models\PhoneTrade::statusLabels();
    $statusTimelineClasses = [
        \App\Models\PhoneTrade::STATUS_STOK => 'border-yellow-500',
        \App\Models\PhoneTrade::STATUS_TERJUAL => 'border-emerald-500',
    ];
    $isSold = $phoneTrade->status === \App\Models\PhoneTrade::STATUS_TERJUAL;
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Detail Jual Beli HP</h3>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-sm font-mono text-yellow-600">{{ $phoneTrade->kode }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $phoneTrade->status_class }}">
                        {{ $phoneTrade->status_label }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $phoneTrade->sumber_class }}">
                        {{ $phoneTrade->sumber_label }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('phone-trades.index', ['edit' => $phoneTrade->id]) }}"
                    class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button"
                    @click="confirmDelete = { url: '{{ route('phone-trades.destroy', $phoneTrade) }}', message: 'Yakin hapus data HP {{ $phoneTrade->type_hp }} ({{ $phoneTrade->kode }})?' }"
                    class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-medium rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                <a href="{{ route('phone-trades.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                    Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Data HP --}}
            <div class="space-y-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Data HP</h4>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Tipe HP</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $phoneTrade->type_hp }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Brand</p>
                        <p class="text-sm font-medium text-gray-900">{{ $phoneTrade->brand ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Penyimpanan</p>
                        <p class="text-sm font-medium text-gray-900">{{ $phoneTrade->storage ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Warna</p>
                        <p class="text-sm font-medium text-gray-900">{{ $phoneTrade->warna ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Kondisi</p>
                        <p class="text-sm font-medium text-gray-900">{{ $kondisiLabels[$phoneTrade->kondisi] ?? $phoneTrade->kondisi }}</p>
                    </div>
                </div>
                @if($phoneTrade->kelengkapan)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Kelengkapan</p>
                    <p class="text-sm text-gray-700">{{ $phoneTrade->kelengkapan }}</p>
                </div>
                @endif
            </div>

            {{-- Pembelian --}}
            <div class="space-y-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pembelian</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Tanggal Masuk</p>
                        <p class="text-sm font-medium text-gray-900">{{ $phoneTrade->tanggal_masuk?->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Nama Penjual</p>
                        <p class="text-sm font-medium text-gray-900">{{ $phoneTrade->penjual ?: '-' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Harga Beli</p>
                        <p class="text-sm font-medium text-gray-900 tabular-nums">Rp {{ number_format($phoneTrade->harga_beli, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Biaya Service</p>
                        <p class="text-sm font-medium text-gray-900 tabular-nums">Rp {{ number_format($phoneTrade->biaya_perbaikan, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="rounded-lg bg-amber-50 border border-amber-100 px-4 py-3">
                    <p class="text-xs text-amber-700/70">Total Modal</p>
                    <p class="text-lg font-bold text-amber-700 tabular-nums">Rp {{ number_format($phoneTrade->total_modal, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Penjualan --}}
        <div class="mt-6 pt-5 border-t border-gray-100">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Penjualan</h4>
            @if($phoneTrade->status === \App\Models\PhoneTrade::STATUS_TERJUAL)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Tanggal Keluar</p>
                    <p class="text-sm font-medium text-gray-900">{{ $phoneTrade->tanggal_keluar?->format('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Nama Pembeli</p>
                    <p class="text-sm font-medium text-gray-900">{{ $phoneTrade->pembeli ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Harga Jual</p>
                    <p class="text-sm font-medium text-gray-900 tabular-nums">Rp {{ number_format($phoneTrade->harga_jual, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Profit</p>
                    <p class="text-lg font-bold tabular-nums {{ $phoneTrade->keuntungan >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        Rp {{ number_format($phoneTrade->keuntungan, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Harga Jual (Rencana)</p>
                    <p class="text-sm font-medium text-gray-900 tabular-nums">
                        {{ $phoneTrade->harga_jual !== null ? 'Rp ' . number_format($phoneTrade->harga_jual, 0, ',', '.') : '-' }}
                    </p>
                </div>
            </div>
            @endif
        </div>

        {{-- Keterangan --}}
        @if($phoneTrade->keterangan)
        <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Keterangan</p>
            <p class="text-sm text-gray-600">{{ $phoneTrade->keterangan }}</p>
        </div>
        @endif
    </div>

    {{-- Status & Penjualan --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6"
        x-data="{ status: '{{ old('status', $phoneTrade->status) }}' }">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-arrows-rotate text-yellow-500"></i>
                Status &amp; Penjualan
            </h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $phoneTrade->status_class }}">
                {{ $phoneTrade->status_label }}
            </span>
        </div>

        <form method="POST" action="{{ route('phone-trades.update-status', $phoneTrade) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Status <span class="text-yellow-600">*</span></label>
                    <select name="status" x-model="status" required
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 outline-none {{ $isSold ? 'bg-gray-100 text-gray-500' : 'text-gray-900' }}">
                        @if(!$isSold)
                            <option value="stok">Tersedia</option>
                        @endif
                        <option value="terjual">Terjual</option>
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Harga Jual</label>
                    <input type="text" inputmode="numeric" name="harga_jual"
                        value="{{ old('harga_jual', $phoneTrade->harga_jual !== null ? number_format((float) $phoneTrade->harga_jual, 0, ',', '.') : '') }}"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-right focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none tabular-nums">
                    @error('harga_jual') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div x-show="status === 'terjual'" x-cloak>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Keluar <span class="text-yellow-600">*</span></label>
                    <input type="date" name="tanggal_keluar"
                        value="{{ old('tanggal_keluar', $phoneTrade->tanggal_keluar?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                    @error('tanggal_keluar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div x-show="status === 'terjual'" x-cloak>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Pembeli</label>
                    <input type="text" name="pembeli" value="{{ old('pembeli', $phoneTrade->pembeli) }}"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                    @error('pembeli') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <p class="text-xs text-gray-400">Perubahan status dan nominal otomatis tercatat di riwayat.</p>
            </div>
        </form>
    </div>

    {{-- Timeline Riwayat --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-clock-rotate-left text-yellow-500"></i>
            Riwayat Aktivitas
        </h3>

        @if($phoneTrade->histories->isNotEmpty())
            <div class="relative">
                <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-200"></div>
                <div class="space-y-4">
                    @foreach($phoneTrade->histories as $history)
                        @php
                            $isUpdate = $history->tipe === \App\Models\PhoneTrade::TIPE_UPDATE;
                            $dotClass = (!$isUpdate && $history->status_to)
                                ? ($statusTimelineClasses[$history->status_to] ?? 'border-gray-500')
                                : 'border-amber-500';
                        @endphp
                        <div class="relative pl-10">
                            <div class="absolute left-2.5 top-1.5 w-3 h-3 rounded-full border-2 bg-white {{ $dotClass }}"></div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ \App\Models\PhoneTradeHistory::tipeClasses()[$history->tipe] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $history->tipe_label }}
                                    </span>
                                    @if(!$isUpdate && $history->status_to)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border {{ \App\Models\PhoneTrade::statusClasses()[$history->status_to] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                            {{ $statusLabels[$history->status_to] ?? $history->status_to }}
                                        </span>
                                    @endif
                                    @if($history->status_from)
                                        <span class="text-xs text-gray-400">
                                            dari {{ $statusLabels[$history->status_from] ?? $history->status_from }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $history->created_at->format('d M Y H:i') }}
                                    @if($history->user)
                                        - oleh {{ $history->user->name }}
                                    @endif
                                </p>
                                @if($history->note)
                                    <p class="text-sm text-gray-600 mt-1">{{ $history->note }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-clock-rotate-left text-3xl mb-2 block"></i>
                <p class="text-sm">Belum ada riwayat perubahan.</p>
            </div>
        @endif
    </div>
</div>
@endsection
