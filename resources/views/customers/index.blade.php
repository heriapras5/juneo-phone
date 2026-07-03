@extends('layouts.app')

@section('title', 'Pelanggan')
@section('page-title', 'Data Pelanggan')

@section('content')
<script>
window._pageData = {
    @if($errors->any())
    hasErrors: true,
    _method: {{ Js::from(old('_method')) }},
    editing_id: {{ Js::from(old('editing_id')) }},
    old_nama: {{ Js::from(old('nama')) }},
    old_no_hp: {{ Js::from(old('no_hp')) }},
    old_alamat: {{ Js::from(old('alamat')) }},
    @endif
};
</script>
<div x-data="customerPage">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <form method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari pelanggan..."
                        class="pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none w-full sm:w-64">
                </div>
                <button type="submit" title="Cari"
                    class="px-3 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 text-white rounded-lg text-sm font-medium hover:from-yellow-400 hover:to-amber-400 shadow-sm active:scale-[0.97] transition-transform duration-150">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('customers.index') }}" title="Reset Pencarian" class="px-3 py-2 bg-gray-100 text-gray-500 rounded-lg text-sm hover:bg-gray-200 ">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
            <button type="button" @click="openCreateModal()"
                class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm  flex items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i> Tambah Pelanggan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">No</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Nama</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">No. HP</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Alamat</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Total Service</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-yellow-50/50">
                        <td class="px-5 py-4 text-sm text-gray-500">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                        <td class="px-5 py-4">
                            <a href="{{ route('customers.show', $customer) }}" class="text-sm font-medium text-yellow-600 hover:text-yellow-700">
                                {{ $customer->nama }}
                            </a>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $customer->no_hp ?? '-' }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $customer->alamat ?? '-' }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600 text-center">{{ $customer->services_count }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('customers.show', $customer) }}" title="Lihat Detail"
                                    class="p-2 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg ">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button @click="openEditModal({{ Js::from($customer->only(['id', 'nama', 'no_hp', 'alamat'])) }})"
                                    title="Edit Data"
                                    class="p-2 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg ">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete = { url: '{{ route('customers.destroy', $customer) }}', message: 'Yakin ingin menghapus {{ $customer->nama }}?' }"
                                    title="Hapus Data"
                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg ">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                            @if(request('search'))
                                <i class="fas fa-search text-3xl mb-2 block text-gray-300"></i>
                                Data Tidak Ditemukan
                            @else
                                <i class="fas fa-users text-3xl mb-2 block text-gray-300"></i>
                                Belum ada data pelanggan
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100">
            {{ $customers->links() }}
        </div>
    </div>

    {{-- Modal Form Pelanggan --}}
    <div x-show="showModal" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-400"></div>
            <div class="flex items-center justify-between mb-6 mt-2">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editingId ? 'Edit Pelanggan' : 'Tambah Pelanggan'"></h3>
                <button @click="showModal = false" title="Tutup" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="editingId ? '/customers/' + editingId : '/customers'" method="POST" @submit="loading = true">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'">
                <input type="hidden" name="editing_id" :value="editingId || ''">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-yellow-600">*</span></label>
                        <input type="text" name="nama" x-model="formData.nama" required
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                        @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" name="no_hp" x-model="formData.no_hp"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <input type="text" name="alamat" x-model="formData.alamat"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="loading"
                        class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg  flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i x-show="!loading" class="fas fa-save"></i>
                        <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                        <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan'"></span>
                    </button>
                        <button type="button" @click="showModal = false"
                            class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 ">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
