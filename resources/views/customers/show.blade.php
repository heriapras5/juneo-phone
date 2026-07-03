@extends('layouts.app')

@section('title', 'Detail Pelanggan')
@section('page-title', 'Detail Pelanggan')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ $customer->nama }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $customer->no_hp ?? 'Tidak ada no. HP' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 ">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if($customer->alamat)
        <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
            <p class="text-sm text-gray-600">{{ $customer->alamat }}</p>
        </div>
        @endif

        <div class="mt-4 flex items-center gap-2 text-sm text-gray-500">
            <i class="fas fa-tools text-yellow-600"></i>
            <span>{{ $customer->services->count() }} kali service</span>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Riwayat Service</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
@php
$statusClasses = \App\Models\Service::statusClasses();
$statusLabels = \App\Models\Service::statusLabels();
@endphp
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">No. Service</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Type HP</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Kerusakan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Jasa</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Teknisi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customer->services as $service)
                    <tr class="hover:bg-yellow-50/50 ">
                        <td class="px-5 py-4 text-sm font-mono text-yellow-600">{{ $service->service_number }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $service->tanggal->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $service->type_hp }}</td>
                                                @php $sKerusakan = json_decode($service->kerusakan, true); @endphp
                        <td class="px-5 py-4 text-sm text-gray-600 max-w-xs truncate">{{ is_array($sKerusakan) ? implode(', ', $sKerusakan) : $service->kerusakan }}</td>
                        <td class="px-5 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$service->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$service->status] ?? $service->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($service->teknisi === 'edi')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">E</span>
                            @elseif($service->teknisi === 'nur')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">N</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('services.show', $service) }}" title="Lihat Detail" class="p-2 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg  inline-block">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-gray-500">Belum ada riwayat service</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
