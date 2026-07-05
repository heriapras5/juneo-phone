@extends('layouts.app')

@section('title', 'Pengaturan Profil')
@section('page-title', 'Pengaturan Profil')

@section('content')
@php $user = auth()->user(); @endphp
<div class="max-w-2xl mx-auto"
    x-data="{ loading: false, showDeleteModal: false, previewUrl: null }">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
            @submit="loading = true">
            @csrf
            @method('PUT')

            <div class="space-y-4 mb-6 pb-6 border-b border-gray-100">
                <label class="block text-sm font-medium text-gray-700">Foto Profil</label>
                <div class="flex items-start gap-4">
                    <div class="relative w-20 h-20 rounded-full overflow-hidden flex items-center justify-center bg-gray-50 flex-shrink-0 ring-4 ring-yellow-500/30 shadow-2xl shadow-yellow-500/30" style="filter: drop-shadow(0 4px 8px rgba(255,215,0,0.25));">
                        <template x-if="!previewUrl">
                            @if($user->photo_url)
                                <img src="{{ $user->photo_url }}?v={{ time() }}" alt="Foto" class="w-full h-full object-cover">
                                <button type="button" @click="showDeleteModal = true"
                                    class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full text-[10px] flex items-center justify-center shadow hover:bg-red-600 transition-colors z-10">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <i class="fas fa-user text-2xl text-gray-300"></i>
                            @endif
                        </template>
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Preview" class="w-full h-full object-cover">
                        </template>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="foto" id="foto_input" accept="image/jpeg,image/png"
                            @change="previewUrl = URL.createObjectURL($event.target.files[0])"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 text-gray-700 outline-none">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG/PNG, maks 2MB. Biarkan kosong jika tidak diubah.</p>
                        @error('foto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <input type="hidden" name="remove_foto" id="remove_foto" value="0">
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-yellow-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none @error('name') border-red-400 @enderror"
                        placeholder="Nama lengkap Anda">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-yellow-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none @error('email') border-red-400 @enderror"
                        placeholder="email@contoh.com">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($user->password)
            <div class="mt-6 pt-4 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-800 mb-4">Ubah Password</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                        <input type="password" name="current_password"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none @error('current_password') border-red-400 @enderror"
                            placeholder="Masukkan password saat ini">
                        @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="new_password" minlength="6"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none @error('new_password') border-red-400 @enderror"
                            placeholder="Minimal 6 karakter">
                        @error('new_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" minlength="6"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"
                            placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-6 pt-4 border-t border-gray-100">
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg  flex items-center gap-2 shadow-sm shadow-yellow-500/25 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i x-show="!loading" class="fas fa-save"></i>
                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                </button>
            </div>
        </form>
    </div>

<div x-show="showDeleteModal" x-cloak
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-sm bg-white rounded-xl shadow-2xl p-6 text-center">
        <div class="flex items-center justify-end mb-2">
            <button type="button" @click="showDeleteModal = false" title="Tutup" class="text-gray-400 hover:text-gray-600 -mr-2"><i class="fas fa-times"></i></button>
        </div>
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
            <i class="fas fa-trash text-red-500 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Foto?</h3>
        <p class="text-sm text-gray-500 mb-6">Foto profil akan dihapus permanent</p>
        <div class="flex gap-3">
            <button type="button" @click="showDeleteModal = false"
                class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm">
                Batal
            </button>
            <button type="button" @click="document.getElementById('remove_foto').value = '1'; showDeleteModal = false; document.querySelector('form').submit()"
                class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg text-sm">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
</div>
@endsection
