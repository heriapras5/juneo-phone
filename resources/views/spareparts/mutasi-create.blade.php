@extends('layouts.app')

@section('title', 'Mutasi Stok')
@section('page-title', 'Mutasi Stok')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Mutasi Stok</h3>
            <p class="text-sm text-gray-500 mt-1">
                Barang: <strong>{{ $sparepart->nama_barang }}</strong>
                | Stok saat ini: <strong>{{ $sparepart->stok }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('spareparts.mutasi-store', $sparepart) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Tipe Mutasi <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-emerald-50 has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-300">
                            <input type="radio" name="tipe" value="masuk" {{ old('tipe') === 'masuk' ? 'checked' : '' }} required class="text-emerald-600">
                            <span class="text-sm text-gray-700"><i class="fas fa-arrow-down text-emerald-500"></i> Stok Masuk</span>
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-red-50 has-[:checked]:bg-red-50 has-[:checked]:border-red-300">
                            <input type="radio" name="tipe" value="keluar" {{ old('tipe') === 'keluar' ? 'checked' : '' }} required class="text-red-600">
                            <span class="text-sm text-gray-700"><i class="fas fa-arrow-up text-red-500"></i> Stok Keluar</span>
                        </label>
                    </div>
                    @error('tipe') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah" value="{{ old('jumlah') }}" required min="1"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                    @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Keterangan</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                        placeholder="Misal: Pembelian dari Next Shop DC, atau Dipakai untuk service..."
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                    @error('keterangan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <a href="{{ route('spareparts.show', $sparepart) }}"
                    class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">Batal</a>
                <button type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> Simpan Mutasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
