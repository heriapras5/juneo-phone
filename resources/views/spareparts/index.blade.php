@extends('layouts.app')

@section('title', 'Sparepart')
@section('page-title', 'Sparepart')

@section('content')
@if($pageData)
<script>window._pageData = {{ Js::from($pageData) }};</script>
@endif
<div x-data="sparepartPage" x-init="if (window._pageData?.editSparepart) { $nextTick(() => openEditModal(window._pageData.editSparepart)); }">
    <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama barang, type HP..."
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none w-56">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Kategori</label>
                        <select name="kategori"
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoriLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Toko</label>
                        <select name="toko"
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            <option value="">Semua Toko</option>
                            @foreach($tokoOptions as $toko)
                            <option value="{{ $toko }}" {{ request('toko') === $toko ? 'selected' : '' }}>{{ $toko }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 text-white rounded-lg text-sm font-medium hover:from-yellow-400 hover:to-amber-400 shadow-sm">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'kategori', 'toko']))
                    <a href="{{ route('spareparts.index') }}"
                        class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                        <i class="fas fa-times"></i> Reset
                    </a>
                    @endif
                </form>
                <button type="button" @click="openTokoModal()"
                    class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm flex-shrink-0">
                    <i class="fas fa-store"></i> Kelola Toko
                </button>
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" type="button"
                        class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm flex-shrink-0">
                        <i class="fas fa-download"></i> Export
                        <i class="fas fa-chevron-down text-xs" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-cloak
                        class="absolute right-0 mt-1 w-36 bg-white border border-gray-200 rounded-lg shadow-lg z-20 overflow-hidden">
                        <a href="{{ route('spareparts.export-excel', request()->only(['kategori', 'toko', 'search'])) }}"
                            target="_blank"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-excel text-emerald-600"></i> Excel
                        </a>
                        <a href="{{ route('spareparts.export-pdf', request()->only(['kategori', 'toko', 'search'])) }}"
                            target="_blank"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-pdf text-red-600"></i> PDF
                        </a>
                    </div>
                </div>
                <button type="button" @click="openCreateModal()"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm flex-shrink-0">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="overflow-x-auto rounded-xl">
                <table class="w-full min-w-[1000px]">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center w-10">No</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Kategori</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Nama Barang</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Type</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Stok</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Harga Beli</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Toko</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($spareparts as $sparepart)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-3 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration + ($spareparts->currentPage() - 1) * $spareparts->perPage() }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $kategoriLabels[$sparepart->kategori] ?? $sparepart->kategori }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                <a href="{{ route('spareparts.show', $sparepart) }}" class="text-yellow-600 hover:text-yellow-700 hover:underline">{{ $sparepart->nama_barang }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $sparepart->type_hp ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $sparepart->stok > 10 ? 'bg-emerald-500 text-white' : ($sparepart->stok > 0 ? 'bg-yellow-500 text-white' : 'bg-red-500 text-white') }}">
                                    <i class="fas {{ $sparepart->stok > 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} text-[10px]"></i>
                                    {{ $sparepart->stok }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap tabular-nums">
                                {{ $sparepart->harga_beli ? 'Rp ' . number_format($sparepart->harga_beli, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">
                                @if($sparepart->toko)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                                    {{ $sparepart->toko }}
                                </span>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('spareparts.mutasi-create', $sparepart) }}"
                                        class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Tambah/Kurang Stok">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                    <button type="button" @click="openEditModal({{ Js::from($sparepart->only(['id', 'nama_barang', 'kategori', 'type_hp', 'harga_beli', 'toko', 'keterangan'])) }})"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                        @click="confirmDelete = { url: '{{ route('spareparts.destroy', $sparepart) }}', message: 'Yakin hapus sparepart {{ $sparepart->nama_barang }}?' }"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                                @if(request()->anyFilled(['search', 'kategori', 'toko']))
                                <i class="fas fa-search text-3xl mb-2 block"></i>
                                Data tidak ditemukan
                                @else
                                <i class="fas fa-box-open text-3xl mb-2 block"></i>
                                Belum ada data sparepart
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($spareparts->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $spareparts->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Modal Form Sparepart --}}
    <div x-show="showModal" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 border border-gray-200 relative max-h-[90vh] overflow-y-auto overflow-x-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-400"></div>
            <div class="flex items-center justify-between mb-6 mt-2">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editingId ? 'Edit Sparepart' : 'Tambah Sparepart'"></h3>
                <button @click="closeModal()" title="Tutup" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="editingId ? '{{ url('spareparts') }}/' + editingId : '{{ route('spareparts.store') }}'" method="POST"
                @submit="if (loading) { $event.preventDefault(); return; } loading = true">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'">
                <input type="hidden" name="editing_id" :value="editingId || ''">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                            <select name="kategori" required x-model="formData.kategori"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_barang" x-model="formData.nama_barang" x-ref="firstField" required
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            @error('nama_barang') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Type</label>
                            <input type="text" name="type_hp" x-model="formData.type_hp"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            @error('type_hp') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Harga Beli</label>
                            <input type="text" inputmode="numeric"
                                x-bind:value="formData.harga_beli ? Number(formData.harga_beli).toLocaleString('id-ID') : ''"
                                @input="formData.harga_beli = $event.target.value.replace(/\D/g, '')"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right">
                            <input type="hidden" name="harga_beli" :value="formData.harga_beli">
                            @error('harga_beli') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="!editingId">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Stok <span class="text-red-500">*</span></label>
                            <input type="number" name="stok" x-model="formData.stok" :required="!editingId" min="0"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            @error('stok') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Toko</label>
                            <select name="toko" x-model="formData.toko"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                <option value="">-- Pilih Toko --</option>
                                @foreach($tokoOptions as $toko)
                                <option value="{{ $toko }}">{{ $toko }}</option>
                                @endforeach
                            </select>
                            @error('toko') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Keterangan</label>
                        <textarea name="keterangan" x-model="formData.keterangan" rows="2"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"></textarea>
                        @error('keterangan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="loading"
                        class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i x-show="!loading" class="fas fa-save"></i>
                        <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                        <span x-text="editingId ? 'Update' : 'Save'"></span>
                    </button>
                    <button type="button" @click="closeModal()"
                        class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Toko --}}
    <div x-show="showTokoModal" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 via-blue-500 to-blue-400"></div>
            <div class="flex items-center justify-between mb-4 mt-2">
                <h3 class="text-lg font-semibold text-gray-900">Kelola Toko</h3>
                <button @click="closeTokoModal()" title="Tutup" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>

            <form :action="tokoEditingId ? '{{ url('tokos') }}/' + tokoEditingId : '{{ route('tokos.store') }}'" method="POST" class="mb-5">
                @csrf
                <input type="hidden" name="_method" :value="tokoEditingId ? 'PUT' : 'POST'">
                <input type="hidden" name="_toko_form" value="1">
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Toko</label>
                        <input type="text" name="nama" x-model="tokoNama" required
                            placeholder="Masukkan nama toko..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-gray-900 outline-none">
                        @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-lg text-sm whitespace-nowrap shadow-sm">
                        <i class="fas fa-save"></i> <span x-text="tokoEditingId ? 'Update' : 'Simpan'"></span>
                    </button>
                </div>
            </form>

            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Daftar Toko</p>
                @if($tokos->isNotEmpty())
                <div class="space-y-2">
                    @foreach($tokos as $toko)
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 border border-gray-100">
                        <span class="text-sm font-medium text-gray-900">{{ $toko->nama }}</span>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="openTokoEditModal({{ Js::from($toko->only(['id', 'nama'])) }})"
                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button type="button"
                                @click="confirmDelete = { url: '{{ route('tokos.destroy', $toko) }}', message: 'Yakin hapus toko {{ $toko->nama }}?' }"
                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-4">Belum ada toko. Tambah toko baru di atas.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
