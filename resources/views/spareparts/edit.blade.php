@extends('layouts.app')

@section('title', 'Edit Sparepart')
@section('page-title', 'Edit Sparepart')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('spareparts.update', $sparepart) }}" onsubmit="this.harga_beli.value = this.harga_beli.value.replace(/\D/g, '')">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang', $sparepart->nama_barang) }}" required
                            placeholder="LCD, Baterai, Touch Panel, Flexi..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        @error('nama_barang') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Type HP</label>
                        <input type="text" name="type_hp" value="{{ old('type_hp', $sparepart->type_hp) }}"
                            placeholder="Samsung A12, iPhone 11, Redmi Note 10..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        @error('type_hp') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Harga Beli</label>
                        <input type="text" inputmode="numeric" name="harga_beli"
                            value="{{ old('harga_beli', $sparepart->harga_beli) ? number_format(old('harga_beli', $sparepart->harga_beli), 0, ',', '.') : '' }}"
                            placeholder="0"
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none text-right">
                        @error('harga_beli') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div x-data="{
                        kategoriSearch: '',
                        kategoriDropdownOpen: false,
                        kategori: '{{ old('kategori', $sparepart->kategori) }}',
                        kategoriOptions: {{ Js::from($kategoriOptions) }},
                        get filteredKategori() {
                            var q = (this.kategoriSearch || '').toLowerCase();
                            if (!q) return this.kategoriOptions;
                            return this.kategoriOptions.filter(function(o) { return (o || '').toLowerCase().indexOf(q) !== -1; });
                        },
                        selectKategori(name) { this.kategori = name; this.kategoriDropdownOpen = false; this.kategoriSearch = ''; },
                        quickAddKategori() {
                            var name = (this.kategoriSearch || '').trim(); if (!name) { alert('Nama kategori harus diisi.'); return; }
                            var self = this; var csrf = document.querySelector('meta[name=\'csrf-token\']'); if (!csrf) return;
                            fetch('/spareparts/kategori/quick', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content }, body: JSON.stringify({ name: name }) })
                            .then(function(r) { return r.json(); })
                            .then(function(data) { if (data.success) { self.kategoriOptions.push(data.kategori.name); self.kategori = data.kategori.name; self.kategoriDropdownOpen = false; self.kategoriSearch = ''; } else { alert(data.message || 'Gagal menyimpan kategori.'); } })
                            .catch(function(e) { console.error(e); alert('Gagal menyimpan kategori.'); });
                        }
                    }">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <div class="relative" @click.outside="kategoriDropdownOpen = false">
                            <button type="button" @click="kategoriDropdownOpen = !kategoriDropdownOpen; $nextTick(() => { if(kategoriDropdownOpen) { $refs.kSearchInput?.focus(); } })"
                                class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer text-left"
                                :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': kategoriDropdownOpen }">
                                <span class="truncate" x-text="kategori || '-- Pilih Kategori --'"></span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0 ml-1" :class="{ 'rotate-180': kategoriDropdownOpen }"></i>
                            </button>
                            <div x-show="kategoriDropdownOpen" x-cloak
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-ref="kSearchInput" x-model="kategoriSearch" placeholder="Cari kategori..." @click.stop
                                        class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                                </div>
                                <div class="max-h-40 overflow-y-auto">
                                    <template x-for="o in filteredKategori" :key="o">
                                        <button type="button" @click="selectKategori(o)"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-yellow-50 flex items-center justify-between"
                                            :class="{ 'bg-yellow-50 text-yellow-700 font-medium': kategori === o }">
                                            <span x-text="o"></span>
                                            <i x-show="kategori === o" class="fas fa-check text-yellow-500 text-xs"></i>
                                        </button>
                                    </template>
                                    <div x-show="kategoriSearch.trim() !== '' && filteredKategori.length === 0" class="px-3 py-2 border-t border-gray-100">
                                        <button type="button" @click="quickAddKategori()"
                                            class="w-full text-left text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                            <i class="fas fa-plus text-xs"></i>
                                            <span>Tambah: "</span><span x-text="kategoriSearch"></span><span>"</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="kategori" :value="kategori">
                        @error('kategori') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Keterangan</label>
                    <textarea name="keterangan" rows="2"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">{{ old('keterangan', $sparepart->keterangan) }}</textarea>
                    @error('keterangan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <a href="{{ route('spareparts.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">Batal</a>
                <button type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
