@extends('layouts.app')

@section('title', 'Detail Sparepart')
@section('page-title', 'Detail Sparepart')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ $sparepart->nama_barang }}</h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                        {{ $kategoriLabels[$sparepart->kategori] ?? $sparepart->kategori }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $sparepart->stok > 10 ? 'bg-emerald-500 text-white' : ($sparepart->stok > 0 ? 'bg-yellow-500 text-white' : 'bg-red-500 text-white') }}">
                        <i class="fas {{ $sparepart->stok > 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} text-[10px]"></i>
                        Stok: {{ $sparepart->stok }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('spareparts.mutasi-create', $sparepart) }}"
                    class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-exchange-alt"></i> Mutasi Stok
                </a>
                <a href="{{ route('spareparts.index', ['edit' => $sparepart->id]) }}"
                    class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('spareparts.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Type HP</p>
                <p class="text-sm font-medium text-gray-900">{{ $sparepart->type_hp ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Harga Beli</p>
                <p class="text-sm font-medium text-gray-900">{{ $sparepart->harga_beli ? 'Rp ' . number_format($sparepart->harga_beli, 0, ',', '.') : '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Toko</p>
                <p class="text-sm font-medium text-gray-900">{{ $sparepart->toko ?? '-' }}</p>
            </div>
        </div>
        @if($sparepart->keterangan)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Keterangan</p>
            <p class="text-sm text-gray-600">{{ $sparepart->keterangan }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-clock-rotate-left text-yellow-500"></i>
            Riwayat Mutasi Stok
        </h3>

        @if($sparepart->mutasi->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="text-right py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="text-right py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Stok Sebelum</th>
                        <th class="text-right py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Stok Sesudah</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($sparepart->mutasi as $mutasi)
                    <tr>
                        <td class="py-2 px-2 text-gray-600 whitespace-nowrap">{{ $mutasi->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2 px-2 whitespace-nowrap">
                            @if($mutasi->tipe === 'masuk')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">
                                <i class="fas fa-arrow-down text-[10px]"></i> Masuk
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                <i class="fas fa-arrow-up text-[10px]"></i> Keluar
                            </span>
                            @endif
                        </td>
                        <td class="py-2 px-2 text-right font-medium {{ $mutasi->tipe === 'masuk' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $mutasi->jumlah }}
                        </td>
                        <td class="py-2 px-2 text-right text-gray-600">{{ $mutasi->stok_sebelum }}</td>
                        <td class="py-2 px-2 text-right text-gray-900 font-medium">{{ $mutasi->stok_sesudah }}</td>
                        <td class="py-2 px-2 text-gray-600">{{ $mutasi->keterangan ?? '-' }}</td>
                        <td class="py-2 px-2 text-gray-500">{{ $mutasi->user?->name ?? 'Sistem' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-clock-rotate-left text-3xl mb-2 block"></i>
            <p class="text-sm">Belum ada mutasi stok.</p>
        </div>
        @endif
    </div>
</div>
@endsection
