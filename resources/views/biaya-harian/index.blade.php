@extends('layouts.app')

@section('title', 'Kebutuhan Pribadi')
@section('page-title', 'Kebutuhan Pribadi')

@section('content')
<div class="space-y-4">

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between gap-2 mb-4">
            <div class="flex items-center gap-2">
                <div class="w-1 h-6 bg-orange-500 rounded-full"></div>
                <span class="text-sm font-semibold text-gray-700">Kebutuhan Pribadi</span>
            </div>
            <div class="flex items-center gap-2">
                @if($date !== now()->toDateString())
                <a href="{{ route('biaya-harian.index') }}"
                    class="px-3 py-1.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white rounded-lg text-xs font-medium flex items-center gap-1.5 shadow-sm transition-all">
                    <i class="fas fa-undo-alt"></i>
                    Reset ke Hari Ini
                </a>
                @endif
                <input type="date" id="datePicker" value="{{ $date }}"
                    onchange="window.location.href='?date=' + this.value"
                    class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
            </div>
        </div>
        <p class="text-xs text-gray-400 mb-4">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>

        @php
        $fixedKats = ['bank_jago', 'fee_edi', 'fee_nur'];
        @endphp

        <form method="POST" action="{{ route('biaya-harian.update') }}" x-data="{
            loading: false,
            _adding: false,
            tambahanFees: {{ Js::from($tambahan->map(fn($t) => ['kategori' => 'fee_tambahan', 'nama' => $t->nama, 'jumlah' => number_format($t->jumlah, 0, ',', '.'), 'keterangan' => $t->keterangan ?? ''])->values()->toArray()) }},
            confirmRemoveIndex: null,
            addTambahan() { if (this._adding) return; this._adding = true; this.tambahanFees.push({ kategori: 'fee_tambahan', nama: '', jumlah: '', keterangan: '' }); this.$nextTick(() => { this._adding = false; }); },
            confirmRemoveItem(i) { this.confirmRemoveIndex = i; },
            executeRemove() { if (this.confirmRemoveIndex !== null) { this.tambahanFees.splice(this.confirmRemoveIndex, 1); this.confirmRemoveIndex = null; } }
        }" @submit="loading = true">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $date }}">
            <div class="space-y-3">
                @foreach($fixedKats as $kat)
                @php
                $b = $biaya->get($kat);
                $jumlah = $b && $b->jumlah > 0 ? $b->jumlah : ($defaults[$kat] ?? 0);
                $keterangan = $b->keterangan ?? '';
                @endphp
                <div class="flex flex-wrap items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <div class="flex-1 min-w-[120px]">
                        <label class="block text-xs font-medium text-gray-600">{{ $kategoriLabels[$kat] ?? $kat }}</label>
                    </div>
                    <div class="w-36">
                        <input type="text" inputmode="numeric" name="items[{{ $kat }}][jumlah]"
                            value="{{ $jumlah !== null ? number_format($jumlah, 0, ',', '.') : '' }}"
                            placeholder="0"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right"
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <input type="text" name="items[{{ $kat }}][keterangan]"
                            value="{{ $keterangan }}"
                            placeholder="Keterangan (opsional)"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                    </div>
                    <input type="hidden" name="items[{{ $kat }}][kategori]" value="{{ $kat }}">
                    <input type="hidden" name="items[{{ $kat }}][nama]" value="{{ $kategoriLabels[$kat] ?? $kat }}">
                </div>
                @endforeach

                {{-- Lainnya --}}
                @php
                $bLain = $biaya->get('lainnya');
                $jumlahLain = $bLain && $bLain->jumlah > 0 ? $bLain->jumlah : ($defaults['lainnya'] ?? 0);
                $keteranganLain = $bLain->keterangan ?? '';
                @endphp
                <div class="p-3 rounded-lg bg-orange-50/50">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs font-medium text-gray-600">Lainnya</label>
                        </div>
                        <div class="w-36">
                            <input type="text" inputmode="numeric" name="items[lainnya][jumlah]"
                                value="{{ $jumlahLain !== null ? number_format($jumlahLain, 0, ',', '.') : '' }}"
                                placeholder="0"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right"
                                oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <input type="text" name="items[lainnya][keterangan]"
                                value="{{ $keteranganLain }}"
                                placeholder="Keterangan (opsional)"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        </div>
                        <input type="hidden" name="items[lainnya][kategori]" value="lainnya">
                    </div>
                    <button type="button" @click.stop.prevent="addTambahan()"
                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 mt-1">
                        <i class="fas fa-plus-circle text-[10px]"></i>
                        Tambah
                    </button>
                </div>

                {{-- Fee Tambahan --}}
                <template x-for="(tf, i) in tambahanFees" :key="'tf-' + i">
                    <div class="flex flex-wrap items-center gap-3 p-3 rounded-lg bg-gray-50">
                        <div class="flex-1 min-w-[120px]">
                            <input type="text" x-model="tf.nama" :name="'items[tambahan_' + i + '][nama]'"
                                class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 outline-none">
                        </div>
                        <div class="w-36">
                            <input type="text" inputmode="numeric" x-model="tf.jumlah" :name="'items[tambahan_' + i + '][jumlah]'"
                                placeholder="0"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none text-right"
                                oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <input type="text" x-model="tf.keterangan" :name="'items[tambahan_' + i + '][keterangan]'"
                                placeholder="Keterangan (opsional)"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        </div>
                        <input type="hidden" :name="'items[tambahan_' + i + '][kategori]'" value="fee_tambahan">
                        <button type="button" @click.stop="confirmRemoveItem(i)"
                            class="text-red-400 hover:text-red-600 text-sm flex-shrink-0">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </template>

            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <p class="text-xs text-gray-400">Default dapat diubah di menu Pengaturan</p>
                </div>
                <button type="submit" :disabled="loading"
                    class="px-5 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm disabled:opacity-50">
                    <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                    <i class="fas fa-save" x-show="!loading"></i>
                    Save
                </button>
            </div>

            {{-- Confirm Hapus Item Tambahan --}}
            <div x-show="confirmRemoveIndex !== null" x-cloak
                x-transition:enter="transition-opacity duration-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-75"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200">
                    <div class="text-center">
                        <div class="flex items-center justify-end mb-2">
                            <button type="button" @click="confirmRemoveIndex = null" title="Tutup" class="text-gray-400 hover:text-gray-600 -mr-2"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="mx-auto w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <i class="fas fa-trash text-red-500 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-600 mb-6">Item tambahan ini akan dihapus saat data disimpan.</p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" @click="confirmRemoveIndex = null"
                                class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">
                                Batal
                            </button>
                            <button type="button" @click="executeRemove()"
                                class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl">
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Riwayat --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Riwayat Bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->format('F Y') }}</h3>
            <div>
                <input type="month" value="{{ $bulan }}"
                    onchange="window.location.href='?date={{ $date }}&bulan=' + this.value"
                    class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
            </div>
        </div>
        <div x-data="scrollSync">
            <div class="overflow-x-auto" x-ref="table" @scroll="syncFromTable">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Total</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($riwayat as $r)
                    @php $cleanTanggal = \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d'); @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-sm text-yellow-600 hover:text-yellow-700 font-medium cursor-pointer" onclick="window.location.href='?date={{ $cleanTanggal }}&bulan={{ $bulan }}'">{{ \Carbon\Carbon::parse($r->tanggal)->format('d F Y') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 cursor-pointer" onclick="window.location.href='?date={{ $cleanTanggal }}&bulan={{ $bulan }}'">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <button type="button"
                                @click="confirmDelete = { url: '{{ route('biaya-harian.destroy', $cleanTanggal) . '?bulan=' . $bulan }}', message: 'Semua biaya untuk tanggal {{ \Carbon\Carbon::parse($cleanTanggal)->format('d/m/Y') }} akan dihapus.' }"
                                class="text-red-500 hover:text-red-700" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">Tidak ada data di bulan ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="sticky bottom-0 z-20 overflow-x-auto h-4 bg-white border-t border-gray-200" x-ref="mirror" @scroll="syncFromMirror">
                <div :style="{ width: ($refs.table?.scrollWidth||0)+'px', height:'1px' }"></div>
            </div>
        </div>
    </div>
</div>
@endsection
