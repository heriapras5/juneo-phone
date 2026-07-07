@extends('layouts.app')

@section('title', 'Antrian Service')
@section('page-title', 'Antrian Service')

@section('content')
<div x-data="{
    loading: null,
    quickEditData: { id: null, teknisi: '' },
    showQuickEdit: false,
    showConfirm: false,
    confirmData: { id: null, status: '', label: '', message: '' },
    csrf: {{ Js::from(csrf_token()) }},
    confirmAction(id, status, label) {
        let messages = {
            'menunggu_dikerjakan': 'Yakin ingin mulai mengerjakan service ini?',
            'menunggu_batal': 'Yakin ingin membatalkan service ini?',
            'dikerjakan_selesai': 'Yakin service ini sudah selesai dikerjakan?',
            'dikerjakan_batal': 'Yakin ingin membatalkan service yang sedang dikerjakan?',
            'selesai_diambil': 'Konfirmasi bahwa HP sudah diambil pelanggan?',
        };
        // Pemanggilan: confirmAction(id, toStatus, fromStatus) -> key = from_to
        let key = label + '_' + status;
        this.confirmData = { id: id, status: status, label: label, message: messages[key] || 'Yakin?' };
        this.showConfirm = true;
    },
    executeConfirm() {
        let d = this.confirmData;
        this.showConfirm = false;
        this.quickUpdate(d.id, d.status);
    },
    removeRow(id) {
        let row = document.querySelector('tr[data-service-id="' + id + '"]');
        if (row) {
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        }
    },
    updateTeknisiBadge(id, teknisi) {
        let cell = document.querySelector('td[data-teknisi-id="' + id + '"]');
        if (cell) {
            let label = teknisi === 'edi' ? 'E' : (teknisi === 'nur' ? 'N' : '');
            let cls = teknisi === 'edi' ? 'bg-blue-100 text-blue-700 border-blue-200'
                : (teknisi === 'nur' ? 'bg-purple-100 text-purple-700 border-purple-200'
                : 'bg-gray-100 text-gray-600 border-gray-200');
            cell.innerHTML = label
                ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium ' + cls + ' border">' + label + '</span>'
                : '<span class="text-xs text-gray-400">-</span>';
        }
    },
    quickUpdate(id, s) {
        if (this.loading) return;
        this.loading = id;
        fetch('/services/' + id + '/status', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ status: s })
        })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(d => { if (d.success) this.removeRow(id); else alert(d.message || 'Gagal memperbarui status.'); })
        .catch(e => { console.error(e); alert('Terjadi kesalahan. Silakan coba lagi.'); })
        .finally(() => { this.loading = null; });
    },
    openQuickEdit(svc) {
        this.quickEditData = { id: svc.id, teknisi: svc.teknisi || '' };
        this.showQuickEdit = true;
    },
    submitQuickEdit() {
        if (this.loading) return;
        this.loading = 'quick';
        let id = this.quickEditData.id;
        let teknisi = this.quickEditData.teknisi;
        fetch('/services/' + id + '/quick-update', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ teknisi: teknisi })
        })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(d => { if (d.success) { this.showQuickEdit = false; this.updateTeknisiBadge(id, teknisi); } else alert(d.message || 'Gagal memperbarui teknisi.'); })
        .catch(e => { console.error(e); alert('Terjadi kesalahan. Silakan coba lagi.'); })
        .finally(() => { this.loading = null; });
    }
}" class="space-y-4">

    {{-- Notification Banners --}}
    @if($urgentCount > 0)
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
        <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
        <div>
            <span class="font-semibold">{{ $urgentCount }} service</span> sudah menunggu lebih dari <strong>5 hari</strong> — prioritaskan segera!
        </div>
    </div>
    @endif

    @if($warningCount > 0)
    <div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
        <i class="fas fa-clock text-orange-500 text-lg"></i>
        <div>
            <span class="font-semibold">{{ $warningCount }} service</span> sudah menunggu lebih dari <strong>3 hari</strong>
        </div>
    </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Legenda:</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Urgent</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Tinggi</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Normal</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Rendah</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">*Prioritas menyesuaikan lama tunggu</span>
            <a href="{{ route('services.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Semua Service
            </a>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px]">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center w-10">No</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">No. Service</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Tgl Masuk</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Pelanggan</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Type</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Kerusakan</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Prioritas</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Status</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Teknisi</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Hari ke-</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Estimasi</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($services as $service)
                    @php
                        $hariKe = $service->tanggal->diffInDays(now()->startOfDay()) + 1;
                        $displayPrio = $service->getDisplayPriority();
                        $displayPrioClass = $service->getDisplayPriorityClass();
                        $displayPrioLabel = $service->getDisplayPriorityLabel();
                        $estimasiClass = '';
                        $isOverdue = false;
                        if ($service->estimasi_selesai) {
                            $selisih = $service->estimasi_selesai->diffInDays(now()->startOfDay(), false);
                            if ($selisih < 0) { $estimasiClass = 'text-red-600 font-semibold'; $isOverdue = true; }
                            elseif ($selisih <= 2) $estimasiClass = 'text-orange-600';
                            else $estimasiClass = 'text-emerald-600';
                        }
                    @endphp
                    <tr data-service-id="{{ $service->id }}" class="hover:bg-gray-50/50 {{ $displayPrio === 'urgent' ? 'bg-red-50/40' : ($displayPrio === 'tinggi' ? 'bg-orange-50/30' : '') }}">
                        <td class="px-3 py-3 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-3 py-3 text-sm font-medium whitespace-nowrap">
                            <a href="{{ route('services.show', $service) }}" class="text-yellow-600 hover:text-yellow-700 hover:underline">{{ $service->service_number }}</a>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $service->tanggal->format('d/m/Y') }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700 whitespace-nowrap">
                            {{ $service->customer?->nama ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600">
                            @php $qTypes = $service->serviceItems->pluck('type_hp')->unique()->values(); @endphp
                            @if($qTypes->isNotEmpty())
                                @foreach($qTypes as $qTipe)
                                    <div class="leading-relaxed whitespace-nowrap"><span class="text-gray-400">•</span> {{ $qTipe }}</div>
                                @endforeach
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600 max-w-[160px]">
                            @php $qItems = $service->serviceItems; @endphp
                            @foreach($qItems as $qItem)
                                @php $qKrList = is_array($qItem->kerusakan) ? $qItem->kerusakan : []; @endphp
                                @foreach($qKrList as $qKr)
                                    <div class="leading-relaxed truncate"><span class="text-gray-400">•</span> {{ $qKr }}</div>
                                @endforeach
                            @endforeach
                            @if($qItems->isEmpty() && $service->kerusakan)
                                @php $qKrList = is_array($service->kerusakan) ? $service->kerusakan : (json_decode($service->kerusakan, true) ?: []); @endphp
                                @foreach($qKrList as $qKr)
                                    <div class="leading-relaxed truncate"><span class="text-gray-400">•</span> {{ $qKr }}</div>
                                @endforeach
                            @elseif($qItems->isEmpty())
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $displayPrioClass }}">
                                @if($displayPrio === 'urgent')
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1"></span>
                                @elseif($displayPrio === 'tinggi')
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1"></span>
                                @endif
                                {{ $displayPrioLabel }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $service->getStatusClass() }}">
                                {{ $service->getStatusLabel() }}
                            </span>
                        </td>
                        <td data-teknisi-id="{{ $service->id }}" class="px-3 py-3 text-center whitespace-nowrap">
                            @if($service->teknisi === 'edi')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">E</span>
                            @elseif($service->teknisi === 'nur')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">N</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-center whitespace-nowrap {{ $hariKe > 5 ? 'text-red-600 font-bold' : ($hariKe > 3 ? 'text-red-600 font-semibold' : ($hariKe > 1 ? 'text-orange-600' : 'text-gray-600')) }}">
                            @if($hariKe > 5)
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            @elseif($hariKe > 3)
                            <i class="fas fa-clock mr-1"></i>
                            @endif
                            {{ $hariKe }} hr
                        </td>
                        <td class="px-3 py-3 text-sm text-center whitespace-nowrap {{ $estimasiClass }}">
                            @if($isOverdue)
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            @endif
                            {{ $service->estimasi_selesai ? $service->estimasi_selesai->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                @if($service->status === 'menunggu')
                                <button type="button" @click="confirmAction({{ $service->id }}, 'dikerjakan', 'menunggu')" :disabled="loading === {{ $service->id }}"
                                    class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-medium flex items-center gap-1 disabled:opacity-50">
                                    <i x-show="loading === {{ $service->id }}" class="fas fa-spinner fa-spin"></i>
                                    <i x-show="loading !== {{ $service->id }}" class="fas fa-play"></i>
                                    Kerjakan
                                </button>
                                <button type="button" @click="confirmAction({{ $service->id }}, 'batal', 'menunggu')" :disabled="loading === {{ $service->id }}"
                                    class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-medium flex items-center gap-1 disabled:opacity-50">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                                @elseif($service->status === 'dikerjakan')
                                <button type="button" @click="confirmAction({{ $service->id }}, 'selesai', 'dikerjakan')" :disabled="loading === {{ $service->id }}"
                                    class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-medium flex items-center gap-1 disabled:opacity-50">
                                    <i x-show="loading === {{ $service->id }}" class="fas fa-spinner fa-spin"></i>
                                    <i x-show="loading !== {{ $service->id }}" class="fas fa-check"></i>
                                    Selesai
                                </button>
                                <button type="button" @click="confirmAction({{ $service->id }}, 'batal', 'dikerjakan')" :disabled="loading === {{ $service->id }}"
                                    class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-medium flex items-center gap-1 disabled:opacity-50">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                @endif
                                <button type="button" @click="openQuickEdit({{ Js::from($service) }})"
                                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-medium">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-check-circle text-3xl mb-2 block"></i>
                            Tidak ada antrian — semua service sudah selesai atau diambil
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($services->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $services->links() }}
        </div>
        @endif
    </div>

    {{-- Selesai — Siap Diambil --}}
    @if($completedServices->count() > 0)
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i>
                Selesai — Siap Diambil
                <span class="text-xs font-normal text-gray-400">({{ $completedServices->total() }})</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center w-10">No</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">No. Service</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Pelanggan</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Type HP</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Selesai</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Teknisi</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Total</th>
                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($completedServices as $service)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-3 py-3 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-3 py-3 text-sm font-medium whitespace-nowrap">
                            <a href="{{ route('services.show', $service) }}" class="text-yellow-600 hover:text-yellow-700 hover:underline">{{ $service->service_number }}</a>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $service->customer?->nama ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-600">
                            @php $cTypes = $service->serviceItems->pluck('type_hp')->unique()->values(); @endphp
                            @if($cTypes->isNotEmpty())
                                {{ $cTypes->implode(', ') }}
                            @else
                                {{ $service->type_hp ?? '-' }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $service->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            @if($service->teknisi === 'edi')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">E</span>
                            @elseif($service->teknisi === 'nur')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">N</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-700 text-right whitespace-nowrap">Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <button type="button" @click="confirmAction({{ $service->id }}, 'diambil', 'selesai')" :disabled="loading === {{ $service->id }}"
                                class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-medium flex items-center gap-1 disabled:opacity-50">
                                <i x-show="loading === {{ $service->id }}" class="fas fa-spinner fa-spin"></i>
                                <i x-show="loading !== {{ $service->id }}" class="fas fa-hand"></i>
                                Diambil
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                            <i class="fas fa-check-circle text-2xl mb-1 block"></i>
                            Tidak ada service yang selesai
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($completedServices->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $completedServices->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- Konfirmasi Modal --}}
    <div x-show="showConfirm" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200">
            <div class="text-center">
                <div class="flex items-center justify-end mb-2">
                    <button @click="showConfirm = false" title="Tutup" class="text-gray-400 hover:text-gray-600 -mr-2"><i class="fas fa-times"></i></button>
                </div>
                <div class="mx-auto w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center mb-4">
                    <i class="fas fa-question-circle text-yellow-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi</h3>
                <p class="text-sm text-gray-600 mb-6" x-text="confirmData.message"></p>
                <div class="flex gap-3 justify-center">
                    <button @click="showConfirm = false"
                        class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">
                        Batal
                    </button>
                    <button @click="executeConfirm()"
                        class="px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 rounded-xl shadow-lg shadow-yellow-500/25">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Edit Modal --}}
    <div x-show="showQuickEdit" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Edit Service</h3>
                <button @click="showQuickEdit = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Teknisi</label>
                    <select x-model="quickEditData.teknisi"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                        <option value="">Adit</option>
                        <option value="edi">Edi (Karyawan 1)</option>
                        <option value="nur">Nur (Karyawan 2)</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button @click="showQuickEdit = false"
                    class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">Batal</button>
                <button @click="submitQuickEdit()" :disabled="loading === 'quick'"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm disabled:opacity-50">
                    <i x-show="loading === 'quick'" class="fas fa-spinner fa-spin"></i>
                    <i x-show="loading !== 'quick'" class="fas fa-save"></i>
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection