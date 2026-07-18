@extends('layouts.app')

@section('title', 'Tambah Sparepart')
@section('page-title', 'Tambah Sparepart')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('spareparts.store') }}" onsubmit="this.harga_beli.value = this.harga_beli.value.replace(/\D/g, '')">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori" required
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriLabels as $key => $label)
                            <option value="{{ $key }}" {{ old('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kategori') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" required
                            placeholder="LCD, Baterai, Touch Panel, Flexi..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        @error('nama_barang') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Type HP</label>
                        <input type="text" name="type_hp" value="{{ old('type_hp') }}"
                            placeholder="Samsung A12, iPhone 11, Redmi Note 10..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        @error('type_hp') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Stok Awal <span class="text-red-500">*</span></label>
                        <input type="number" name="stok" value="{{ old('stok', 0) }}" required min="0"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                        @error('stok') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Harga Beli</label>
                        <input type="text" inputmode="numeric" name="harga_beli" value="{{ old('harga_beli') ? number_format(old('harga_beli'), 0, ',', '.') : '' }}"
                            placeholder="0"
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none text-right">
                        @error('harga_beli') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Toko</label>
                        <select name="toko"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            <option value="">-- Pilih Toko --</option>
                            @foreach($tokoOptions as $toko)
                            <option value="{{ $toko }}" {{ old('toko') === $toko ? 'selected' : '' }}>{{ $toko }}</option>
                            @endforeach
                        </select>
                        @error('toko') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Keterangan</label>
                    <textarea name="keterangan" rows="2"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none"
                        placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
                    @error('keterangan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <a href="{{ route('spareparts.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">Batal</a>
                <button type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
