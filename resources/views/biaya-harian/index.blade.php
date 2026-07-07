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

        <form method="POST" action="{{ route('biaya-harian.update') }}" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $date }}">
            <div class="space-y-3">
                @foreach(['bank_jago', 'fee_edi', 'fee_nur', 'lainnya'] as $kat)
                @php
                $b = $biaya->get($kat);
                $nama = $b->nama ?? ($kategoriLabels[$kat] ?? $kat);
                $jumlah = $b && $b->jumlah > 0 ? $b->jumlah : ($defaults[$kat] ?? 0);
                $keterangan = $b->keterangan ?? '';
                @endphp
                <div class="flex flex-wrap items-center gap-3 p-3 rounded-lg {{ $kat === 'lainnya' ? 'bg-orange-50/50' : 'bg-gray-50' }}">
                    <div class="flex-1 min-w-[120px]">
                        <label class="block text-xs font-medium text-gray-600">{{ $nama }}</label>
                        @if($kat === 'lainnya')
                        <p class="text-[10px] text-gray-400">Kebutuhan lainnya (makan, jajan, dll)</p>
                        @endif
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
                </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <p class="text-xs text-gray-400">Default dapat diubah di menu Pengaturan</p>
                </div>
                <button type="submit" :disabled="loading"
                    class="px-5 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm disabled:opacity-50">
                    <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                    <i class="fas fa-save" x-show="!loading"></i>
                    Simpan
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
        <div class="overflow-x-auto">
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
                    @php $cleanTanggal = \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d'); $formId = 'delete-form-' . str_replace('-', '', $cleanTanggal); @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-sm text-yellow-600 hover:text-yellow-700 font-medium cursor-pointer" onclick="window.location.href='?date={{ $cleanTanggal }}&bulan={{ $bulan }}'">{{ \Carbon\Carbon::parse($r->tanggal)->format('d F Y') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 cursor-pointer" onclick="window.location.href='?date={{ $cleanTanggal }}&bulan={{ $bulan }}'">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <button type="button"
                                @click="Swal.fire({
                                    icon: 'warning',
                                    title: 'Hapus Data?',
                                    text: 'Semua biaya untuk tanggal {{ \Carbon\Carbon::parse($cleanTanggal)->format('d/m/Y') }} akan dihapus.',
                                    showCancelButton: true,
                                    confirmButtonColor: '#EF4444',
                                    cancelButtonColor: '#6B7280',
                                    confirmButtonText: 'Ya, Hapus!',
                                    cancelButtonText: 'Batal',
                                    reverseButtons: true,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        document.getElementById('{{ $formId }}').submit();
                                    }
                                })"
                                class="text-red-500 hover:text-red-700" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <form id="{{ $formId }}" method="POST" action="{{ route('biaya-harian.destroy', $cleanTanggal) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="bulan" value="{{ $bulan }}">
                            </form>
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
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush