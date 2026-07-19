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
            addTambahan() { if (this._adding) return; this._adding = true; this.tambahanFees.push({ kategori: 'fee_tambahan', nama: '', jumlah: '', keterangan: '' }); this.$nextTick(() => { this._adding = false; }); },
            removeTambahan(i) {
                var self = this;
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Item?',
                    text: 'Item tambahan ini akan dihapus saat data disimpan.',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        self.tambahanFees.splice(i, 1);
                    }
                });
            }
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
                <div class="flex flex-wrap items-center gap-3 p-3 rounded-lg bg-orange-50/50">
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
                        <button type="button" @click.stop="removeTambahan(i)"
                            class="text-red-400 hover:text-red-600 text-sm flex-shrink-0">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </template>

                <button type="button" @click.stop.prevent="addTambahan()"
                    class="flex flex-wrap items-center gap-3 p-3 rounded-lg bg-gray-50 border border-dashed border-gray-300 hover:bg-gray-100 hover:border-gray-400 transition-all w-full text-left">
                    <div class="flex-1 min-w-[120px] flex items-center gap-2">
                        <i class="fas fa-plus-circle text-xs text-gray-400"></i>
                        <span class="text-xs font-medium text-gray-500">Tambah</span>
                    </div>
                </button>

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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
