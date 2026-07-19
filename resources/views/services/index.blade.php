@extends('layouts.app')

@section('title', 'Data Service')
@section('page-title', 'Data Service')

@section('content')
@php
$statusLabels = \App\Models\Service::statusLabels();
$statusClasses = \App\Models\Service::statusClasses();
$nextStatus = \App\Models\Service::allowedTransitions();
@endphp
<script>
window._pageData = {
    kerusakanOptions: @json($kerusakanOptions ?? []),
    typeHpOptions: @json($typeHpOptions ?? []),
    helpers: @json($helpers ?? []),
    statusCounts: @json($statusCount),
    statusLabels: @json(\App\Models\Service::statusLabels()),
    allowedTransitions: @json($nextStatus),
    customers: @json($customers->map(fn($c) => ['id' => $c->id, 'nama' => $c->nama, 'no_hp' => $c->no_hp])->values()),
    tanggal: '{{ date('Y-m-d') }}',
    editService: @json($editService),
    @if($errors->any())
    hasErrors: true,
    _method: {{ Js::from(old('_method')) }},
    editing_id: {{ Js::from(old('editing_id')) }},
    old_tanggal: {{ Js::from(old('tanggal')) }},
    old_customer_id: {{ Js::from(old('customer_id')) }},
    old_keterangan: {{ Js::from(old('keterangan')) }},
    old_status: {{ Js::from(old('status')) }},
    old_tanggal_selesai: {{ Js::from(old('tanggal_selesai')) }},
    old_estimasi_selesai: {{ Js::from(old('estimasi_selesai')) }},
    old_teknisi: {{ Js::from(old('teknisi')) }},
    old_items: {{ Js::from(old('items', [])) }},
    @endif
};
</script>
<div class="space-y-4">
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="No. service, tipe HP, pelanggan..."
                        class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none w-64">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none" autocomplete="off">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none" autocomplete="off">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none">
                        <option value="">Semua Status</option>
                        @foreach(\App\Models\Service::statusLabels() as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 text-white rounded-lg text-sm font-medium hover:from-yellow-400 hover:to-amber-400 shadow-sm">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'date_from', 'date_to', 'status']))
                    <a href="{{ route('services.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 ">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </form>
            <button type="button" onclick="Alpine.$data(document.querySelector('[x-data=&quot;servicePage&quot;]')).openCreateModal()"
                class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm flex-shrink-0">
                <i class="fas fa-plus"></i> Tambah
            </button>
        </div>
    </div>
</div>

<div x-data="servicePage" x-init="if (window._pageData?.editService) { $nextTick(() => openEditModal(window._pageData.editService)); }">
    <div class="space-y-4">

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="rounded-xl" x-data="scrollSync">
                <div class="overflow-x-auto" x-ref="table" @scroll="syncFromTable">
                <table class="w-full min-w-[1100px]">
                    <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-10">No</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Pelanggan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap w-44">Type HP</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap w-52">Kerusakan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-20">Teknisi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Pengeluaran</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Jasa</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Keuntungan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Keterangan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">No. Service</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Estimasi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Tgl Selesai</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-16 sticky right-0 bg-gray-50 z-10">Aksi</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($services as $service)
                    @php
                        $serviceItems = $service->serviceItems;
                    @endphp
                    <tr class="hover:bg-gray-50/50" data-row="{{ $service->id }}">
                        <td class="px-5 py-3 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap text-center">
                            <a href="{{ route('services.show', $service) }}" class="text-gray-600 hover:text-yellow-600">{{ \Carbon\Carbon::parse($service->tanggal)->format('d/m/Y') }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-700 whitespace-nowrap">
                            <a href="{{ route('services.show', $service) }}" class="text-gray-700 hover:text-yellow-600 hover:underline">{{ $service->customer?->nama ?? '-' }}</a>
                        <td class="px-5 py-3 text-center whitespace-nowrap">
                            <div class="inline-flex flex-col items-center">
                                <button data-status-badge @click="toggleStatus({{ $service->id }}, $event)"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium border {{ $statusClasses[$service->status] ?? 'bg-gray-100 text-gray-600' }} cursor-pointer hover:opacity-80">
                                    {{ $statusLabels[$service->status] ?? $service->status }}
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </button>
                                <div x-show="rowState[{{ $service->id }}]?.openStatus"
                                    data-status-dropdown
                                    x-transition:enter="transition-opacity duration-75"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition-opacity duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    :style="rowState[{{ $service->id }}]?.statusStyle"
                                    class="fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-lg py-1 min-w-[140px] text-left">
                                    @php
                                        $allowedNext = $nextStatus[$service->status] ?? [];
                                        $hasOptions = !empty($allowedNext);
                                    @endphp
                                    @foreach($allowedNext as $key)
                                        <button @click.stop="closeRowDropdowns({{ $service->id }}); quickUpdateStatus({{ $service->id }}, '{{ $key }}')"
                                            class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100 text-gray-700">
                                            {{ $statusLabels[$key] ?? $key }}
                                        </button>
                                    @endforeach
                                    @unless($hasOptions)
                                        <div class="px-3 py-2 text-xs text-gray-400 italic">Tidak ada aksi tersedia</div>
                                    @endunless
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">
                            @php
                                $allTypes = $serviceItems->pluck('type_hp')->unique()->values();
                                if ($allTypes->isEmpty()) { $allTypes = collect([$service->type_hp]); }
                            @endphp
                            @foreach($allTypes as $tipe)
                                <div class="leading-relaxed whitespace-nowrap">
                                    <span class="text-gray-400">•</span>
                                    <span>{{ $tipe }}</span>
                                </div>
                            @endforeach
                        </td>
                        @php
                            $mergerList = collect();
                            if ($serviceItems->isNotEmpty()) {
                                foreach ($serviceItems as $item) {
                                    $krList = is_array($item->kerusakan) ? $item->kerusakan : (json_decode($item->kerusakan, true) ?: []);
                                    foreach ($krList as $kr) {
                                        $mergerList->push([
                                            'nama' => is_array($kr) ? ($kr['nama'] ?? '') : $kr,
                                            'teknisi' => is_array($kr) ? ($kr['teknisi'] ?? '') : ($item->teknisi ?? $service->teknisi ?? ''),
                                        ]);
                                    }
                                }
                            } else {
                                $krList = $service->kerusakan ? (is_array($service->kerusakan) ? $service->kerusakan : (json_decode($service->kerusakan, true) ?: [])) : [];
                                foreach ($krList as $kr) {
                                    $mergerList->push([
                                        'nama' => is_array($kr) ? ($kr['nama'] ?? '') : $kr,
                                        'teknisi' => is_array($kr) ? ($kr['teknisi'] ?? '') : ($service->teknisi ?? ''),
                                    ]);
                                }
                            }
                        @endphp
                        <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">
                            @forelse($mergerList as $kr)
                                <div class="leading-relaxed whitespace-nowrap">
                                    <span class="text-gray-400">•</span>
                                    <span>{{ $kr['nama'] }}</span>
                                </div>
                            @empty
                                <span class="text-gray-400">-</span>
                            @endforelse
                        </td>
                        <td class="px-5 py-3 text-sm text-center whitespace-nowrap">
                            @forelse($mergerList as $kr)
                                @php
                                    $krTekHelper = $kr['teknisi'] ? $helpers->first(fn($h) => strtolower($h->nama) === strtolower($kr['teknisi'])) : null;
                                @endphp
                                <div class="leading-relaxed">
                                    @if($kr['teknisi'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium border {{ $krTekHelper?->warna ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">{{ $krTekHelper?->nama ?? ucfirst($kr['teknisi']) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-gray-400">-</span>
                            @endforelse
                        </td>
                        <td class="px-5 py-3 text-sm text-red-700 text-right whitespace-nowrap tabular-nums">Rp {{ number_format($service->pengeluaran, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-gray-900 text-right whitespace-nowrap tabular-nums">Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-right whitespace-nowrap tabular-nums {{ $service->keuntungan >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                            Rp {{ number_format($service->keuntungan, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600 max-w-[180px] truncate whitespace-nowrap">{{ $service->keterangan ?? '' }}</td>
                        <td class="px-5 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                            <a href="{{ route('services.show', $service) }}" class="text-yellow-600 hover:text-yellow-700 hover:underline">{{ $service->service_number }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm text-center whitespace-nowrap">
                            @if($service->estimasi_selesai)
                                @php
                                    $estimasiTgl = $service->estimasi_selesai->format('d/m/Y');
                                    $selesai = in_array($service->status, ['selesai', 'diambil']);
                                    $lewat = !$selesai && $service->estimasi_selesai->isPast();
                                    $estClass = $selesai ? 'text-gray-500' : ($lewat ? 'text-red-600 font-semibold' : 'text-emerald-600 font-semibold');
                                @endphp
                                <span class="{{ $estClass }}">{{ $estimasiTgl }}</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap text-center">{{ $service->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-5 py-3 text-center whitespace-nowrap sticky right-0 bg-white z-10">
                            <div class="inline-flex flex-col items-center">
                                <button @click="toggleAction({{ $service->id }}, $event)"
                                    class="text-gray-400 hover:text-gray-600 p-1">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div x-show="rowState[{{ $service->id }}]?.openAction"
                                    data-action-dropdown
                                    x-transition:enter="transition-opacity duration-75"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition-opacity duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    :style="rowState[{{ $service->id }}]?.actionStyle"
                                    class="fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-lg py-1 min-w-[120px] text-left">
                                     <button @click="openEditModal({{ Js::from($service) }}); closeRowDropdowns({{ $service->id }})"
                                        class="block w-full text-left px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50">
                                        <i class="fas fa-edit mr-2"></i> Edit
                                    </button>
                                    <button @click="deleteData = { url: '{{ route('services.destroy', $service) }}', message: 'Yakin hapus service <strong>'+'{{ $service->service_number }}'+'</strong>?' }; showDeleteConfirm = true; closeRowDropdowns({{ $service->id }})"
                                        class="block w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-trash mr-2"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="px-5 py-12 text-center text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            Belum ada data service
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="sticky bottom-0 z-20 overflow-x-auto h-4 bg-white border-t border-gray-200" x-ref="mirror" @scroll="syncFromMirror">
                <div :style="{ width: ($refs.table?.scrollWidth||0)+'px', height:'1px' }"></div>
            </div>
            </div>
            @if($services->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $services->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Modal Form Service --}}
    <div x-show="showModal" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-2xl mx-4 border border-gray-200 relative max-h-[90vh] overflow-y-auto overflow-x-hidden form-scroll">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-400"></div>
            <div class="flex items-center justify-between mb-6 mt-2">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editingId ? 'Edit Service' : 'Tambah Service'"></h3>
                <button @click="showModal = false" title="Tutup" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="editingId ? '/services/' + editingId : '/services'" method="POST" enctype="multipart/form-data" @submit="if (loading) { $event.preventDefault(); return; } loading = true">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'">
                <input type="hidden" name="editing_id" :value="editingId || ''">

                @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <strong><i class="fas fa-exclamation-circle mr-1"></i> Terjadi kesalahan:</strong>
                    <ul class="mt-1 ml-4 list-disc">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Section 1: Data Pelanggan --}}
                <div class="mb-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-5 bg-yellow-500 rounded-full"></div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Data Pelanggan</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal</label>
                            <input type="date" name="tanggal" x-model="formData.tanggal" required x-ref="firstField"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Pelanggan</label>
                            <div class="relative" @click.outside="customerDropdownOpen = false">
                                <button type="button" @click="customerDropdownOpen = !customerDropdownOpen; $nextTick(() => { if(customerDropdownOpen) { const el = $refs.customerSearchInput; if(el) setTimeout(() => el.focus(), 50); } })"
                                    class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer"
                                    :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': customerDropdownOpen }">
                                    <span class="truncate" x-text="selectedCustomer ? (selectedCustomer.nama + (selectedCustomer.no_hp ? ' (' + selectedCustomer.no_hp + ')' : '')) : '-- Pilih Pelanggan --'"></span>
                                    <i class="fas fa-chevron-down text-gray-400 text-xs" :class="{ 'rotate-180': customerDropdownOpen }"></i>
                                </button>
                                <div x-show="customerDropdownOpen" x-cloak
                                    class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                                    <div class="p-2 border-b border-gray-100">
                                        <input type="text" x-ref="customerSearchInput" x-model="customerSearch"
                                            placeholder="Cari pelanggan..."
                                            @click.stop
                                            class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-md text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto">
                                        <template x-for="c in filteredCustomers" :key="c.id">
                                            <button type="button" @click="selectCustomer(c.id)"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-yellow-50 flex items-center justify-between"
                                                :class="{ 'bg-yellow-50 text-yellow-700 font-medium': formData.customer_id == c.id }">
                                                <span x-text="c.nama"></span>
                                                <span class="text-xs text-gray-400" x-text="c.no_hp || ''"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredCustomers.length === 0 && customerSearch.trim() === ''" class="px-3 py-4 text-sm text-gray-400 text-center">
                                            <i class="fas fa-search mr-1"></i> Ketik nama untuk mencari
                                        </div>
                                        <div x-show="filteredCustomers.length === 0 && customerSearch.trim() !== ''" class="px-3 py-2 border-t border-gray-100">
                                            <button type="button" @click="quickAddCustomer(customerSearch)"
                                                class="w-full text-left text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1 py-1">
                                                <i class="fas fa-plus text-xs"></i>
                                                <span>Buat baru: "</span><span x-text="customerSearch"></span><span>"</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="customer_id" :value="formData.customer_id">
                            @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            <template x-if="selectedCustomer && selectedCustomer.no_hp">
                                <p class="text-xs text-gray-400 mt-1" x-text="'No. HP: ' + selectedCustomer.no_hp"></p>
                            </template>
                        </div>
                    </div>
                </div>



                {{-- Section 3: Data Service (items) --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 bg-indigo-500 rounded-full"></div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Data Service</span>
                        </div>
                        <button type="button" @click="addItem" class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg flex items-center gap-1">
                            <i class="fas fa-plus"></i> Tambah HP
                        </button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(item, index) in formData.items" :key="index">
                            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                                    <span class="text-sm font-semibold text-gray-700" x-text="'HP #' + (index + 1)"></span>
                                    <button type="button" @click="removeItem(index)"
                                        class="text-gray-300 hover:text-red-500 transition-colors text-sm px-2 py-1 rounded hover:bg-red-50"
                                        x-show="formData.items.length > 1">
                                        <i class="fas fa-times mr-1"></i> Hapus
                                    </button>
                                </div>
                                <div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Type HP</label>
                                        <div class="relative" @click.outside="item.typeHpDropdownOpen = false">
                                            <button type="button" @click="item.typeHpDropdownOpen = !item.typeHpDropdownOpen"
                                                class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer"
                                                :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': item.typeHpDropdownOpen }">
                                                <span class="truncate text-sm" x-text="item.type_hp ? item.type_hp : '-- Pilih Type HP --'"></span>
                                                <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0 ml-1" :class="{ 'rotate-180': item.typeHpDropdownOpen }"></i>
                                            </button>
                                            <div x-show="item.typeHpDropdownOpen" x-cloak
                                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                                                <div class="p-2 border-b border-gray-100">
                                                    <input type="text" x-model="item.typeHpSearch"
                                                        placeholder="Cari type HP..."
                                                        @click.stop
                                                        class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                                                </div>
                                                <div class="max-h-40 overflow-y-auto">
                                                    <template x-for="o in filteredTypeHpForItem(index)" :key="o">
                                                        <button type="button" @click="selectTypeHpItem(index, o)"
                                                            class="w-full text-left px-3 py-2 text-sm hover:bg-yellow-50 flex items-center justify-between"
                                                            :class="{ 'bg-yellow-50 text-yellow-700 font-medium': item.type_hp === o }">
                                                            <span x-text="o"></span>
                                                            <i x-show="item.type_hp === o" class="fas fa-check text-yellow-500 text-xs"></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="item.typeHpSearch.trim() !== ''" class="px-3 py-2 border-t border-gray-100">
                                                        <button type="button" @click="selectTypeHpItem(index, item.typeHpSearch)"
                                                            class="w-full text-left text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                                            <i class="fas fa-plus text-xs"></i>
                                                            <span>Tambah: "</span><span x-text="item.typeHpSearch"></span><span>"</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Kerusakan</label>
                                    <div class="space-y-1.5 mb-2">
                                        <template x-for="(k, ki) in (item.kerusakanList || [])" :key="ki">
                                            <div class="flex items-center gap-3 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg text-xs">
                                                <span class="flex-1 text-indigo-700 font-medium min-w-[80px]" x-text="kerusakanNama(k)"></span>
                                                <input type="text" inputmode="numeric" placeholder="Pengeluaran"
                                                    :value="displayNumber(k.pengeluaran)"
                                                    @input="formatCurrencyKerusakan(index, ki, 'pengeluaran', $event)"
                                                    class="w-32 px-2 py-1 bg-white border border-gray-200 rounded text-xs text-right outline-none focus:ring-1 focus:ring-yellow-500/30 text-gray-900">
                                                <input type="text" inputmode="numeric" placeholder="Jasa"
                                                    :value="displayNumber(k.jasa)"
                                                    @input="formatCurrencyKerusakan(index, ki, 'jasa', $event)"
                                                    class="w-32 px-2 py-1 bg-white border border-gray-200 rounded text-xs text-right outline-none focus:ring-1 focus:ring-yellow-500/30 text-gray-900">
                                                <select x-model="k.teknisi"
                                                    class="w-36 px-1 py-1 bg-white border border-indigo-200 rounded text-xs outline-none focus:ring-1 focus:ring-yellow-500/30 text-gray-700">
                                                    <option value="">Teknisi</option>
                                                    <template x-for="h in helpers" :key="h.id">
                                                        <option :value="h.nama.toLowerCase()" x-text="h.nama"></option>
                                                    </template>
                                                </select>
                                                <button type="button" @click="openAddTeknisi(index)"
                                                    class="flex-shrink-0 text-indigo-300 hover:text-indigo-600" title="Tambah teknisi">
                                                    <i class="fas fa-plus text-[10px]"></i>
                                                </button>
                                                <button type="button" @click="removeKerusakan(index, ki)"
                                                    class="flex-shrink-0 text-indigo-300 hover:text-red-500">
                                                    <i class="fas fa-times text-[10px]"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="relative" @click.outside="item.kerusakanDropdownOpen = false">
                                        <button type="button" @click="item.kerusakanDropdownOpen = !item.kerusakanDropdownOpen"
                                            class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer"
                                            :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': item.kerusakanDropdownOpen }">
                                            <span class="truncate text-sm" x-text="(item.kerusakanList || []).length > 0 ? '+ Tambah Kerusakan' : '-- Pilih Kerusakan --'"></span>
                                            <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0 ml-1" :class="{ 'rotate-180': item.kerusakanDropdownOpen }"></i>
                                        </button>
                                        <div x-show="item.kerusakanDropdownOpen" x-cloak
                                            class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                                            <div class="p-2 border-b border-gray-100">
                                                <input type="text" x-model="item.kerusakanSearch"
                                                    placeholder="Cari kerusakan..."
                                                    @click.stop
                                                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                                            </div>
                                            <div class="max-h-40 overflow-y-auto">
                                                <template x-for="o in filteredKerusakanForItem(index)" :key="o">
                                                    <button type="button" @click="selectKerusakanItem(index, o); item.kerusakanSearch = '';"
                                                        class="w-full text-left px-3 py-2 text-sm hover:bg-yellow-50 flex items-center justify-between"
                                                        :class="{ 'bg-yellow-50 text-yellow-700 font-medium': kerusakanTerpilih(item.kerusakanList || [], o) }">
                                                        <span x-text="o"></span>
                                                        <i x-show="kerusakanTerpilih(item.kerusakanList || [], o)" class="fas fa-check text-yellow-500 text-xs"></i>
                                                    </button>
                                                </template>
                                                <div x-show="item.kerusakanSearch.trim() !== ''" class="px-3 py-2 border-t border-gray-100">
                                                    <button type="button" @click="selectKerusakanItem(index, item.kerusakanSearch); item.kerusakanSearch = '';"
                                                        class="w-full text-left text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                                        <i class="fas fa-plus text-xs"></i>
                                                        <span>Tambah: "</span><span x-text="item.kerusakanSearch"></span><span>"</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="addTeknisiIndex === index" x-cloak class="mt-2 p-2 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <input type="text" x-model="newTeknisiNama" placeholder="Nama teknisi..."
                                                class="flex-1 px-2 py-1.5 text-xs border border-gray-200 rounded outline-none focus:ring-1 focus:ring-yellow-500/30 focus:border-yellow-500">
                                            <select x-model="newTeknisiWarna"
                                                class="flex-shrink-0 px-2 py-1.5 text-xs border border-gray-200 rounded outline-none focus:ring-1 focus:ring-yellow-500/30">
                                                @foreach([
                                                    'bg-gray-100 text-gray-600 border-gray-200',
                                                    'bg-blue-100 text-blue-700 border-blue-200',
                                                    'bg-purple-100 text-purple-700 border-purple-200',
                                                    'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                    'bg-red-100 text-red-700 border-red-200',
                                                    'bg-amber-100 text-amber-700 border-amber-200',
                                                    'bg-rose-100 text-rose-700 border-rose-200',
                                                    'bg-cyan-100 text-cyan-700 border-cyan-200',
                                                    'bg-indigo-100 text-indigo-700 border-indigo-200',
                                                ] as $c)
                                                <option value="{{ $c }}">{{ $c }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" @click="saveNewTeknisi()"
                                                class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700 flex-shrink-0">Simpan</button>
                                            <button type="button" @click="addTeknisiIndex = null"
                                                class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800 flex-shrink-0">Batal</button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        </template>
                    </div>
                    <template x-for="(item, index) in formData.items" :key="'hidden-' + index">
                        <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id">
                    </template>
                    <template x-for="(item, index) in formData.items" :key="'type-' + index">
                        <input type="hidden" :name="'items[' + index + '][type_hp]'" :value="item.type_hp">
                    </template>
                    <template x-for="(item, index) in formData.items" :key="'kerusakan-' + index">
                        <input type="hidden" :name="'items[' + index + '][kerusakan]'" :value="JSON.stringify(item.kerusakanList || [])">
                    </template>


                </div>

                {{-- Section 2: Ringkasan Biaya (otomatis dari item) --}}
                <div class="mb-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-5 bg-green-500 rounded-full"></div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Ringkasan Biaya</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-3 bg-white border border-gray-200 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">Total Pengeluaran</div>
                            <div class="text-lg font-semibold text-gray-900" x-text="'Rp ' + totalPengeluaran.toLocaleString('id-ID')"></div>
                            @error('pengeluaran') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="p-3 bg-white border border-gray-200 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">Total Jasa</div>
                            <div class="text-lg font-semibold text-gray-900" x-text="'Rp ' + totalJasa.toLocaleString('id-ID')"></div>
                            @error('jasa') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <input type="hidden" name="pengeluaran" :value="totalPengeluaran">
                    <input type="hidden" name="jasa" :value="totalJasa">
                </div>

                {{-- Section 4: Estimasi & Keterangan --}}
                <div class="mb-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-5 bg-blue-500 rounded-full"></div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimasi &amp; Keterangan</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Estimasi Selesai</label>
                            <input type="date" name="estimasi_selesai" x-model="formData.estimasi_selesai"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            @error('estimasi_selesai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="editingId">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" x-model="formData.tanggal_selesai"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            @error('tanggal_selesai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <input type="hidden" name="teknisi" :value="formData.teknisi">
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Keterangan</label>
                        <input type="text" name="keterangan" x-model="formData.keterangan" placeholder="Catatan tambahan..."
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                        @error('keterangan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>

                {{-- Section 5: Status (hanya saat EDIT) --}}
                <div x-show="editingId" class="mb-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-5 bg-purple-500 rounded-full"></div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Service</span>
                    </div>
                    <div>
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
                            <select name="status" x-model="formData.status"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                <template x-for="s in formData.allowedStatuses" :key="s">
                                    <option :value="s" x-text="(window._pageData?.statusLabels || {})[s] || s" :disabled="s === formData.status"></option>
                                </template>
                            </select>
                            @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="showModal = false"
                        class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm"
                        :disabled="loading">
                        <i class="fas fa-spinner fa-spin" x-show="loading"></i>
                        <i class="fas fa-save" x-show="!loading"></i>
                        <span x-text="editingId ? 'Update' : 'Save'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-show="showDeleteConfirm" x-cloak
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
                    <button @click="showDeleteConfirm = false" title="Tutup" class="text-gray-400 hover:text-gray-600 -mr-2"><i class="fas fa-times"></i></button>
                </div>
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-500 mb-6" x-html="deleteData.message"></p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" @click="showDeleteConfirm = false"
                        class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                        Batal
                    </button>
                    <form :action="deleteData.url" method="POST" @submit="loading = true">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm"
                            :disabled="loading">
                            <i class="fas fa-spinner fa-spin" x-show="loading"></i>
                            <i class="fas fa-trash" x-show="!loading"></i>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection