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
<div x-data="servicePage" x-init="if (window._pageData?.editService) { $nextTick(() => openEditModal(window._pageData.editService)); }">
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
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none">
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
                <button type="button" @click="openCreateModal()"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm flex-shrink-0">
                    <i class="fas fa-plus"></i> Tambah Service
                </button>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="overflow-x-auto rounded-xl">
                <table class="w-full min-w-[1100px]">
                    <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-10">No</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">No. Service</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Tgl Selesai</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Pelanggan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Type HP</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Kerusakan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Modal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Jasa</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Keuntungan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Estimasi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Teknisi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-16">Aksi</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($services as $service)
                    @php
                        $serviceItems = $service->serviceItems;
                        $allTypes = $serviceItems->pluck('type_hp')->unique()->values();
                        if ($allTypes->isEmpty()) { $allTypes = collect([$service->type_hp]); }
                    @endphp
                    <tr class="hover:bg-gray-50/50" data-row="{{ $service->id }}">
                        <td class="px-5 py-3 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}</td>
                        <td class="px-5 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                            <a href="{{ route('services.show', $service) }}" class="text-yellow-600 hover:text-yellow-700 hover:underline">{{ $service->service_number }}</a>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap text-center">{{ \Carbon\Carbon::parse($service->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap text-center">{{ $service->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-5 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $service->customer?->nama ?? '-' }}</td>
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
                            @foreach($allTypes as $tipe)
                                <div class="leading-relaxed whitespace-nowrap">
                                    <span class="text-gray-400">•</span>
                                    <span>{{ $tipe }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600">
                            @foreach($serviceItems as $item)
                                @php
                                    $krList = is_array($item->kerusakan) ? $item->kerusakan : (json_decode($item->kerusakan, true) ?: []);
                                @endphp
                                @foreach($krList as $kr)
                                    <div class="leading-relaxed whitespace-nowrap">
                                        <span class="text-gray-400">•</span>
                                        <span>{{ $kr }}</span>
                                    </div>
                                @endforeach
                            @endforeach
                            @if($serviceItems->isEmpty() && $service->kerusakan)
                                @php
                                    $krList = is_array($service->kerusakan) ? $service->kerusakan : (json_decode($service->kerusakan, true) ?: []);
                                @endphp
                                @foreach($krList as $kr)
                                    <div class="leading-relaxed whitespace-nowrap">
                                        <span class="text-gray-400">•</span>
                                        <span>{{ $kr }}</span>
                                    </div>
                                @endforeach
                            @elseif($serviceItems->isEmpty())
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm text-red-700 text-right whitespace-nowrap tabular-nums">Rp {{ number_format($service->pengeluaran, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-gray-900 text-right whitespace-nowrap tabular-nums">Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-right whitespace-nowrap tabular-nums {{ $service->keuntungan >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                            Rp {{ number_format($service->keuntungan, 0, ',', '.') }}
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
                        <td class="px-5 py-3 text-center whitespace-nowrap">
                            @if($service->teknisi === 'edi')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">E</span>
                            @elseif($service->teknisi === 'nur')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">N</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center whitespace-nowrap">
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
                        <td colspan="14" class="px-5 py-12 text-center text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            Belum ada data service
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
                                    <span class="truncate" x-text="selectedCustomer ? selectedCustomer.nama + ' (' + selectedCustomer.no_hp + ')' : '-- Pilih Pelanggan --'"></span>
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
                                                <span class="text-xs text-gray-400" x-text="c.no_hp"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredCustomers.length === 0" class="px-3 py-4 text-sm text-gray-400 text-center">
                                            <i class="fas fa-search mr-1"></i> Tidak ditemukan
                                        </div>
                                        <div class="border-t border-gray-100 p-2">
                                            <button type="button" @click.stop="openAddCustomer()"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                                <i class="fas fa-plus-circle"></i>
                                                <span>Tambah Pelanggan Baru</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="customer_id" :value="formData.customer_id">
                            @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            <template x-if="selectedCustomer">
                                <p class="text-xs text-gray-400 mt-1" x-text="'No. HP: ' + selectedCustomer.no_hp"></p>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Quick Add Customer Modal --}}
                <div x-show="showAddCustomer" x-cloak
                    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40"
                    @click="showAddCustomer = false">
                    <div @click.stop class="bg-white rounded-2xl shadow-2xl p-5 w-full max-w-sm mx-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-900">Tambah Pelanggan Baru</h4>
                            <button type="button" @click="showAddCustomer = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Nama <span class="text-red-500">*</span></label>
                                <input type="text" x-model="addCustomerForm.nama" placeholder="Nama pelanggan..."
                                    @keydown.enter.prevent="saveNewCustomer"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">No. HP</label>
                                <input type="text" x-model="addCustomerForm.no_hp" placeholder="08xxx..."
                                    @keydown.enter.prevent="saveNewCustomer"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 mt-5 pt-3 border-t border-gray-100">
                            <button type="button" @click="showAddCustomer = false"
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">Batal</button>
                            <button type="button" @click="saveNewCustomer" :disabled="customerSaving"
                                class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm disabled:opacity-50">
                                <i x-show="customerSaving" class="fas fa-spinner fa-spin"></i>
                                <i x-show="!customerSaving" class="fas fa-save"></i>
                                Simpan
                            </button>
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Kerusakan</label>
                                        <div class="space-y-1.5">
                                            <template x-for="(k, ki) in (item.kerusakanList || [])" :key="ki">
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs">
                                                    <span x-text="k"></span>
                                                    <button type="button" @click="item.kerusakanList = item.kerusakanList.filter(function(_, i) { return i !== ki; })"
                                                        class="text-indigo-400 hover:text-red-500">
                                                        <i class="fas fa-times text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="relative" @click.outside="item.kerusakanDropdownOpen = false">
                                            <button type="button" @click="item.kerusakanDropdownOpen = !item.kerusakanDropdownOpen"
                                                class="w-full flex items-center justify-between px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none cursor-pointer"
                                                :class="{ 'border-yellow-500 ring-2 ring-yellow-500/30': item.kerusakanDropdownOpen }">
                                                <span class="truncate text-sm" x-text="(item.kerusakanList || []).length > 0 ? (item.kerusakanList || []).length + ' kerusakan dipilih' : '-- Pilih Kerusakan --'"></span>
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
                                                            :class="{ 'bg-yellow-50 text-yellow-700 font-medium': (item.kerusakanList || []).includes(o) }">
                                                            <span x-text="o"></span>
                                                            <i x-show="(item.kerusakanList || []).includes(o)" class="fas fa-check text-yellow-500 text-xs"></i>
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
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Modal</label>
                                        <input type="text" inputmode="numeric"
                                            :value="displayNumber(item.pengeluaran)"
                                            @input="formatCurrencyItem(index, 'pengeluaran', $event)"
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-right focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Jasa</label>
                                        <input type="text" inputmode="numeric"
                                            :value="displayNumber(item.jasa)"
                                            @input="formatCurrencyItem(index, 'jasa', $event)"
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-right focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
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
                    <template x-for="(item, index) in formData.items" :key="'jasa-' + index">
                        <input type="hidden" :name="'items[' + index + '][jasa]'" :value="item.jasa || '0'">
                    </template>
                    <template x-for="(item, index) in formData.items" :key="'pengeluaran-' + index">
                        <input type="hidden" :name="'items[' + index + '][pengeluaran]'" :value="item.pengeluaran || '0'">
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
                            <div class="text-xs text-gray-500 mb-1">Total Modal</div>
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
                        <div x-show="!editingId" x-cloak>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Teknisi</label>
                            <select name="teknisi" x-model="formData.teknisi"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                                <option value="">Adit</option>
                                <option value="edi">Edi (Karyawan 1)</option>
                                <option value="nur">Nur (Karyawan 2)</option>
                            </select>
                            @error('teknisi') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div x-show="editingId" class="mt-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Teknisi</label>
                        <select name="teknisi" x-model="formData.teknisi"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none md:w-1/3">
                            <option value="">Adit</option>
                            <option value="edi">Edi (Karyawan 1)</option>
                            <option value="nur">Nur (Karyawan 2)</option>
                        </select>
                        @error('teknisi') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                            <div x-show="formData.status === 'diambil'" class="mt-2" x-cloak>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Garansi</label>
                                <input type="text" name="garansi" x-model="formData.garansi" placeholder="Keterangan garansi (opsional)..."
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
                            </div>
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
                        <span x-text="editingId ? 'Update' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Garansi --}}
    <div x-show="showGaransiModal" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-shield text-yellow-500"></i>
                    Garansi Service
                </h3>
                <button @click="skipGaransi" title="Tutup" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <p class="text-sm text-gray-500 mb-4">
                Status akan diubah ke <strong>Diambil</strong>.
                Isi keterangan garansi jika ada:
            </p>
            <input type="text" x-model="garansiPending.garansi" placeholder="Keterangan garansi (opsional)..."
                class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
            <div class="flex items-center justify-end gap-3 mt-5">
                <button type="button" @click="skipGaransi"
                    class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                    Lewati
                </button>
                <button type="button" @click="confirmGaransi"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-check"></i> Konfirmasi
                </button>
            </div>
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