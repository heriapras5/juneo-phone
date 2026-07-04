@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" x-data="{
            loading: false,
            previewLogo: null,
        }" @submit="loading = true">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    <div class="flex items-start gap-4">
                        <div class="w-20 h-20 rounded-full overflow-hidden flex items-center justify-center bg-gray-50 flex-shrink-0 ring-4 ring-yellow-500/30 shadow-2xl shadow-yellow-500/30" style="filter: drop-shadow(0 4px 8px rgba(255,215,0,0.25));">
                            <template x-if="!previewLogo">
                                @if($settings['logo'])
                                    <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-store text-2xl text-gray-300"></i>
                                @endif
                            </template>
                            <template x-if="previewLogo">
                                <img :src="previewLogo" alt="Preview" class="w-full h-full object-cover">
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/svg+xml"
                                @change="previewLogo = URL.createObjectURL($event.target.files[0])"
                                class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 text-gray-700 outline-none @error('logo') border-red-400 @enderror">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, SVG. Maks 2MB. Biarkan kosong jika tidak diubah.</p>
                            @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="nama_toko" value="{{ old('nama_toko', $settings['nama_toko'] ?? 'Juneo Phone') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $settings['subtitle'] ?? '') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"
                        placeholder="Service Hp Android & iPhone">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="alamat" rows="2"
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">{{ old('alamat', $settings['alamat'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $settings['telepon'] ?? '') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                </div>
            </div>

        {{-- Kebutuhan Pribadi & Pengaturan Nota --}}
        <div class="mt-6">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-5 bg-orange-500 rounded-full"></div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kebutuhan Pribadi (Default)</span>
                </div>
                <p class="text-xs text-gray-500 mb-4">Nominal default Kebutuhan Pribadi. Akan otomatis tercatat setiap hari. Bisa diubah per hari di halaman Kebutuhan Pribadi.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Jago</label>
                        <input type="text" inputmode="numeric" name="biaya_bank_jago"
                            value="{{ old('biaya_bank_jago', number_format((int)($settings['biaya_bank_jago'] ?? 110000), 0, ',', '.')) }}"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right"
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fee Edi</label>
                        <input type="text" inputmode="numeric" name="biaya_fee_edi"
                            value="{{ old('biaya_fee_edi', number_format((int)($settings['biaya_fee_edi'] ?? 60000), 0, ',', '.')) }}"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right"
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fee Nur</label>
                        <input type="text" inputmode="numeric" name="biaya_fee_nur"
                            value="{{ old('biaya_fee_nur', number_format((int)($settings['biaya_fee_nur'] ?? 30000), 0, ',', '.')) }}"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right"
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lainnya</label>
                        <input type="text" inputmode="numeric" name="biaya_lainnya"
                            value="{{ old('biaya_lainnya', ((int)($settings['biaya_lainnya'] ?? 0) > 0 ? number_format((int)($settings['biaya_lainnya'] ?? 0), 0, ',', '.') : '')) }}"
                            placeholder="0"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right"
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                    </div>
                </div>
            </div>

            {{-- Pengaturan Nota --}}
            <div class="mt-6">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-5 bg-purple-500 rounded-full"></div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengaturan Nota</span>
                </div>
                <p class="text-xs text-gray-500 mb-4">Data yang akan tampil di nota cetak / preview service.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP (Nota)</label>
                        <input type="text" name="nota_telepon" value="{{ old('nota_telepon', $settings['nota_telepon'] ?? '') }}"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"
                            placeholder="08xxxxxxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Rekening</label>
                        <input type="text" name="nota_rekening" value="{{ old('nota_rekening', $settings['nota_rekening'] ?? '') }}"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"
                            placeholder="BCA 1234567890 a.n. Juneo Phone">
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100">
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg  flex items-center gap-2 shadow-sm shadow-yellow-500/25 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i x-show="!loading" class="fas fa-save"></i>
                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan Pengaturan'"></span>
                </button>
            </div>
        </form>

        {{-- Kategori Kerusakan --}}
        <div class="mt-6">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-1 h-5 bg-yellow-500 rounded-full"></div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Kerusakan</span>
            </div>
            <p class="text-sm text-gray-500 mb-3">Kelola daftar jenis kerusakan HP yang muncul di form Service. Data akan tersimpan otomatis di database.</p>

            @php $damageList = $damageCategories ?? collect(); @endphp

            <div id="damage-list" class="flex flex-wrap gap-1.5 mb-4 @if($damageList->isEmpty()) hidden @endif">
                @foreach($damageList as $cat)
                <div class="group inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">
                    <span>{{ $cat->name }}</span>
                    <button type="button"
                        @click="confirmDelete = { url: '{{ route('settings.damage-categories.destroy', $cat) }}', message: 'Hapus jenis kerusakan ini?' }"
                        class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Hapus">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
                @endforeach
            </div>
            <p id="damage-empty-msg" class="text-sm text-gray-400 italic mb-4 @if($damageList->isNotEmpty()) hidden @endif">Belum ada jenis kerusakan. Tambahkan melalui form di bawah.</p>

            <form method="POST" action="{{ route('settings.damage-categories.store') }}" class="flex items-center gap-2">
                @csrf
                <input type="text" name="name" placeholder="Nama kerusakan..."
                    class="flex-1 px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none"
                    maxlength="255">
                <button type="submit"
                    class="px-3 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
