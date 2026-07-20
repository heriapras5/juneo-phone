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
                    <div x-data="{
                        brandSearch: '',
                        brandDropdownOpen: false,
                        brand: '{{ old('brand') }}',
                        brandOptions: {{ Js::from($brandOptions) }},
                        get filteredBrand() {
                            var q = (this.brandSearch || '').toLowerCase();
                            if (!q) return this.brandOptions;
                            return this.brandOptions.filter(function(o) { return (o.name || '').toLowerCase().indexOf(q) !== -1; });
                        },
                        selectBrand(name) { this.brand = name; this.brandDropdownOpen = false; this.brandSearch = ''; },
                        quickAddBrand() {
                            var name = (this.brandSearch || '').trim(); if (!name) { alert('Nama brand harus diisi.'); return; }
                            var self = this; var csrf = document.querySelector('meta[name=\'csrf-token\']'); if (!csrf) return;
                            fetch('/spareparts/brand/quick', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content }, body: JSON.stringify({ name: name }) })
                            .then(function(r) { return r.json(); })
                            .then(function(data) { if (data.success) { self.brandOptions.push({ id: data.brand.id, name: data.brand.name }); self.brand = data.brand.name; self.brandDropdownOpen = false; self.brandSearch = ''; } else { alert(data.message || 'Gagal menyimpan brand.'); } })
                            .catch(function(e) { console.error(e); alert('Gagal menyimpan brand.'); });
                        }
                    }">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Brand</label>
                        <div class="relative" @click.outside="brandDropdownOpen = false">
                            <button type="button" @click="brandDropdownOpen = !brandDropdownOpen; $nextTick(() => { if(brandDropdownOpen) { $refs.bSearchInput?.focus(); } })"
                                class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer text-left"
                                :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': brandDropdownOpen }">
                                <span class="truncate" x-text="brand || '-- Pilih Brand --'"></span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0" :class="{ 'rotate-180': brandDropdownOpen }"></i>
                                </span>
                            </button>
                            <div x-show="brandDropdownOpen" x-cloak
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-ref="bSearchInput" x-model="brandSearch" placeholder="Cari brand..." @click.stop
                                        class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                                </div>
                                <div class="max-h-40 overflow-y-auto">
                                    <template x-for="o in filteredBrand" :key="o.id">
                                        <button type="button" @click="selectBrand(o.name)"
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors flex items-center justify-between"
                                            :class="{ 'bg-yellow-50 text-yellow-700 font-medium': brand === o.name }">
                                            <span x-text="o.name"></span>
                                            <i x-show="brand === o.name" class="fas fa-check text-yellow-500 text-xs"></i>
                                        </button>
                                    </template>
                                    <div x-show="brandSearch.trim() !== '' && filteredBrand.length === 0" class="px-3 py-2 border-t border-gray-100">
                                        <button type="button" @click="quickAddBrand()"
                                            class="w-full text-left text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                            <i class="fas fa-plus text-xs"></i>
                                            <span>Tambah: "</span><span x-text="brandSearch"></span><span>"</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="brand" :value="brand">
                        @error('brand') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div x-data="{
                        kategoriSearch: '',
                        kategoriDropdownOpen: false,
                        kategori: '{{ old('kategori') }}',
                        kategoriOptions: {{ Js::from($kategoriOptions) }},
                        get filteredKategori() {
                            var q = (this.kategoriSearch || '').toLowerCase();
                            if (!q) return this.kategoriOptions;
                            return this.kategoriOptions.filter(function(o) { return (o.name || '').toLowerCase().indexOf(q) !== -1; });
                        },
                        selectKategori(name) { this.kategori = name; this.kategoriDropdownOpen = false; this.kategoriSearch = ''; },
                        quickAddKategori() {
                            var name = (this.kategoriSearch || '').trim(); if (!name) { alert('Nama kategori harus diisi.'); return; }
                            var self = this; var csrf = document.querySelector('meta[name=\'csrf-token\']'); if (!csrf) return;
                            fetch('/spareparts/kategori/quick', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content }, body: JSON.stringify({ name: name }) })
                            .then(function(r) { return r.json(); })
                            .then(function(data) { if (data.success) { self.kategoriOptions.push({ id: data.kategori.id, name: data.kategori.name }); self.kategori = data.kategori.name; self.kategoriDropdownOpen = false; self.kategoriSearch = ''; } else { alert(data.message || 'Gagal menyimpan kategori.'); } })
                            .catch(function(e) { console.error(e); alert('Gagal menyimpan kategori.'); });
                        }
                    }">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <div class="relative" @click.outside="kategoriDropdownOpen = false">
                            <button type="button" @click="kategoriDropdownOpen = !kategoriDropdownOpen; $nextTick(() => { if(kategoriDropdownOpen) { $refs.kSearchInput?.focus(); } })"
                                class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer text-left"
                                :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': kategoriDropdownOpen }">
                                <span class="truncate" x-text="kategori || '-- Pilih Kategori --'"></span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0" :class="{ 'rotate-180': kategoriDropdownOpen }"></i>
                                </span>
                            </button>
                            <div x-show="kategoriDropdownOpen" x-cloak
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-ref="kSearchInput" x-model="kategoriSearch" placeholder="Cari kategori..." @click.stop
                                        class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                                </div>
                                <div class="max-h-40 overflow-y-auto">
                                    <template x-for="o in filteredKategori" :key="o.id">
                                        <button type="button" @click="selectKategori(o.name)"
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors flex items-center justify-between"
                                            :class="{ 'bg-yellow-50 text-yellow-700 font-medium': kategori === o.name }">
                                            <span x-text="o.name"></span>
                                            <i x-show="kategori === o.name" class="fas fa-check text-yellow-500 text-xs"></i>
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
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" required
                            placeholder="LCD, Baterai, Touch Panel, Flexi..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        @error('nama_barang') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Type</label>
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
                    <div x-data="{
                        tokoSearch: '',
                        tokoDropdownOpen: false,
                        toko: '{{ old('toko') }}',
                        tokoOptions: {{ Js::from($tokoOptions) }},
                        get filteredToko() {
                            var q = (this.tokoSearch || '').toLowerCase();
                            if (!q) return this.tokoOptions;
                            return this.tokoOptions.filter(function(o) { return (o.nama || '').toLowerCase().indexOf(q) !== -1; });
                        },
                        selectToko(name) { this.toko = name; this.tokoDropdownOpen = false; this.tokoSearch = ''; },
                        quickAddToko() {
                            var name = (this.tokoSearch || '').trim(); if (!name) { alert('Nama toko harus diisi.'); return; }
                            var self = this; var csrf = document.querySelector('meta[name=\'csrf-token\']'); if (!csrf) return;
                            fetch('/tokos/quick', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content }, body: JSON.stringify({ name: name }) })
                            .then(function(r) { return r.json(); })
                            .then(function(data) { if (data.success) { self.tokoOptions.push({ id: data.toko.id, nama: data.toko.name }); self.toko = data.toko.name; self.tokoDropdownOpen = false; self.tokoSearch = ''; } else { alert(data.message || 'Gagal menyimpan toko.'); } })
                            .catch(function(e) { console.error(e); alert('Gagal menyimpan toko.'); });
                        }
                    }">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Toko</label>
                        <div class="relative" @click.outside="tokoDropdownOpen = false">
                            <button type="button" @click="tokoDropdownOpen = !tokoDropdownOpen; $nextTick(() => { if(tokoDropdownOpen) { $refs.tSearchInput?.focus(); } })"
                                class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer text-left"
                                :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': tokoDropdownOpen }">
                                <span class="truncate" x-text="toko || '-- Pilih Toko --'"></span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0" :class="{ 'rotate-180': tokoDropdownOpen }"></i>
                                </span>
                            </button>
                            <div x-show="tokoDropdownOpen" x-cloak
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-ref="tSearchInput" x-model="tokoSearch" placeholder="Cari toko..." @click.stop
                                        class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                                </div>
                                <div class="max-h-40 overflow-y-auto">
                                    <template x-for="(o, idx) in filteredToko" :key="o.id">
                                        <button type="button" @click="selectToko(o.nama)"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-yellow-50 flex items-center justify-between"
                                            :class="{ 'bg-yellow-50 text-yellow-700 font-medium': toko === o.nama }">
                                            <span x-text="o.nama"></span>
                                            <i x-show="toko === o.nama" class="fas fa-check text-yellow-500 text-xs"></i>
                                        </button>
                                    </template>
                                    <div x-show="tokoSearch.trim() !== '' && filteredToko.length === 0" class="px-3 py-2 border-t border-gray-100">
                                        <button type="button" @click="quickAddToko()"
                                            class="w-full text-left text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                            <i class="fas fa-plus text-xs"></i>
                                            <span>Tambah: "</span><span x-text="tokoSearch"></span><span>"</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="toko" :value="toko">
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
