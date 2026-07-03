@extends('layouts.app')

@section('title', 'Manajemen Admin')
@section('page-title', 'Manajemen Admin')

@section('content')
<div x-data="{
    showModal: false,
    editingId: null,
    selectedRole: 'admin',
    loading: false,
    formData: { name: '', email: '', password: '', password_confirmation: '' },
    openCreateModal() {
        this.editingId = null;
        this.selectedRole = 'admin';
        this.formData = { name: '', email: '', password: '', password_confirmation: '' };
        this.showModal = true;
    },
    openEditModal(data) {
        this.editingId = data.id;
        this.selectedRole = data.role;
        this.formData = { name: data.name, email: data.email, password: '', password_confirmation: '' };
        this.showModal = true;
    }
}" x-init="
    $watch('showModal', val => { document.body.style.overflow = val ? 'hidden' : ''; });
    @if($errors->any())
        showModal = true;
        if ({{ Js::from(old('_method')) }} === 'PUT') {
            editingId = {{ Js::from(old('editing_id')) }};
            selectedRole = {{ Js::from(old('role', 'admin')) }};
        } else {
            selectedRole = {{ Js::from(old('role', 'admin')) }};
        }
        formData.name = {{ Js::from(old('name', '')) }};
        formData.email = {{ Js::from(old('email', '')) }};
    @endif
">
    {{-- Header --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Daftar Pengguna</h3>
            <button type="button" @click="openCreateModal()"
                class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i> Tambah User
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">No</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Nama</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Email</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Role</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-yellow-50/50 ">
                        <td class="px-5 py-4 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize {{ $user->role === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $user->role === 'superadmin' ? 'SuperAdmin' : $user->role }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEditModal({{ Js::from($user->only(['id', 'name', 'email', 'role'])) }})"
                                    title="Edit Data"
                                    class="p-2 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg ">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                <button @click="confirmDelete = { url: '{{ route('admin.destroy', $user) }}', message: 'Yakin ingin menghapus {{ $user->role }} {{ $user->name }}?' }"
                                    title="Hapus Data"
                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg ">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                            <i class="fas fa-users text-3xl mb-2 block"></i>
                            Belum ada pengguna
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form Admin/Owner --}}
    <div x-show="showModal" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-400"></div>
            <div class="flex items-center justify-between mb-6 mt-2">
                <h3 class="text-lg font-semibold text-gray-900">
                    <span x-text="editingId ? 'Edit' : 'Tambah'"></span>
                    <span x-text="selectedRole === 'owner' ? 'Owner' : 'Admin'"></span>
                </h3>
                <button @click="showModal = false" title="Tutup" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="editingId ? '/admin/' + editingId : '/admin'" method="POST" @submit="loading = true">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'">
                <input type="hidden" name="editing_id" :value="editingId || ''">
                <div class="space-y-4">
                    <div x-show="!editingId">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-yellow-600">*</span></label>
                            <select name="role" x-model="selectedRole" :disabled="editingId"
                                class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                <option value="admin">Admin</option>
                                <option value="superadmin">SuperAdmin</option>
                            </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-yellow-600">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none @error('name') border-red-400 @enderror"
                            placeholder="Nama lengkap">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-yellow-600">*</span></label>
                        <input type="email" name="email" x-model="formData.email" required
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none @error('email') border-red-400 @enderror"
                            placeholder="email@example.com">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-yellow-600" x-text="editingId ? '(kosongkan jika tidak diubah)' : '*'"></span>
                        </label>
                        <input type="password" name="password" x-model="formData.password" minlength="6" :required="!editingId"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none @error('password') border-red-400 @enderror"
                            placeholder="Minimal 6 karakter">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" x-model="formData.password_confirmation" minlength="6"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"
                            placeholder="Ulangi password">
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
