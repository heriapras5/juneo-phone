@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title')
    @if($from && $to)
        Laporan Service {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
    @else
        Laporan Service {{ $tahun }}
    @endif
@endsection

@section('content')
<div class="space-y-4" x-data="{ loading: false }">
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <form method="GET" class="space-y-3">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                    <select name="tahun"
                        class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none min-w-[100px]">
                        @forelse($tahunTersedia as $th)
                        <option value="{{ $th }}" {{ (int) $tahun === $th && !$from && !$to ? 'selected' : '' }}>{{ $th }}</option>
                        @empty
                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from ?? '' }}"
                        class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to ?? '' }}"
                        class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pelanggan</label>
                    <select name="customer_id"
                        class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 text-gray-900 outline-none min-w-[180px]">
                        <option value="">Semua Pelanggan</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }} ({{ $c->no_hp }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 text-white rounded-lg text-sm font-medium hover:from-yellow-400 hover:to-amber-400 shadow-sm">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if(request()->filled('customer_id') || $from || $to)
                <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 flex items-center gap-1 shadow-sm">
                    <i class="fas fa-times"></i> Reset
                </a>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-gray-100">
                <a href="{{ route('reports.export-excel', request()->query()) }}"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>
                <a href="{{ route('reports.export-csv', request()->query()) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-file-csv"></i> Download CSV
                </a>
                <a href="{{ route('reports.export-pdf', request()->query()) }}" target="_blank"
                    @click.prevent="loading = true; $nextTick(() => { window.open('{{ route('reports.export-pdf', request()->query()) }}', '_blank'); setTimeout(() => loading = false, 8000); })"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 flex items-center gap-1.5 shadow-sm"
                    :class="{ 'opacity-50 cursor-not-allowed': loading }">
                    <i x-show="!loading" class="fas fa-file-pdf"></i>
                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                    <span x-text="loading ? 'Memproses...' : 'Cetak PDF'"></span>
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="overflow-x-auto rounded-xl">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap w-10">No</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">No. Service</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Pelanggan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Type HP</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Kerusakan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Modal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Jasa</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Teknisi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">Keterangan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">Keuntungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($services as $service)
                    <tr class="hover:bg-yellow-50/50">
                        <td class="px-5 py-3 text-sm text-gray-500 text-center whitespace-nowrap">{{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}</td>
                        <td class="px-5 py-3 text-sm font-medium text-yellow-600 whitespace-nowrap">{{ $service->service_number }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600 text-center whitespace-nowrap">{{ $service->tanggal->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-sm text-gray-900">{{ $service->customer->nama ?? '-' }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">
                            @php
                                $allTypes = collect([$service->type_hp])
                                    ->concat($service->serviceItems->pluck('type_hp'))
                                    ->unique()
                                    ->values();
                            @endphp
                            @foreach($allTypes as $type)
                            <div>• {{ $type }}</div>
                            @endforeach
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600">
                            @php
                                $krService = $service->kerusakan;
                                $krDecoded = is_string($krService) ? (json_decode($krService, true) ?? [$krService]) : (array) $krService;
                                $allKerusakan = collect($krDecoded)
                                    ->concat($service->serviceItems->pluck('kerusakan')->flatten())
                                    ->unique()
                                    ->values();
                            @endphp
                            @foreach($allKerusakan as $kr)
                            <div>• {{ $kr }}</div>
                            @endforeach
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-900 text-right whitespace-nowrap tabular-nums">Rp {{ number_format($service->pengeluaran, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-gray-900 text-right whitespace-nowrap tabular-nums">Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-center whitespace-nowrap">
                            @if($service->teknisi === 'edi') <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">E</span>
                            @elseif($service->teknisi === 'nur') <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">N</span>
                            @else <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600 max-w-[150px] truncate">{{ $service->keterangan ?? '-' }}</td>
                        <td class="px-5 py-3 text-sm text-right whitespace-nowrap tabular-nums {{ $service->keuntungan >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            Rp {{ number_format($service->keuntungan, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-5 py-8 text-center text-gray-500">
                            @if(request()->filled('customer_id'))
                                <i class="fas fa-search text-3xl mb-2 block text-gray-300"></i>
                                Data Tidak Ditemukan
                            @else
                                <i class="fas fa-file-alt text-3xl mb-2 block text-gray-300"></i>
                                Tidak ada data untuk periode ini
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-gray-100">
            {{ $services->links() }}
        </div>
    </div>

    {{-- Loading Overlay PDF --}}
    <div x-show="loading" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl px-10 py-8 text-center" @click.stop>
            <i class="fas fa-spinner fa-spin text-4xl text-yellow-500 mb-4 block"></i>
            <p class="text-sm font-medium text-gray-700">Menyiapkan laporan...</p>
            <p class="text-xs text-gray-400 mt-1">Mohon tunggu sebentar</p>
        </div>
    </div>
</div>
@endsection
