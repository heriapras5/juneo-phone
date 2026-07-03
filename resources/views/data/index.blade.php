@extends('layouts.app')

@section('title', 'Backup & Reset Data')
@section('page-title', 'Backup & Reset Data')

@section('content')
<div class="space-y-6">

    {{-- Backup Section --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
            <span class="text-sm font-semibold text-gray-700">Backup Data</span>
        </div>
        <p class="text-xs text-gray-500 mb-4">Download seluruh data transaksional (Service, Pelanggan, Biaya Harian, Gaji Karyawan) dalam format Excel (.xlsx).</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-lg font-bold text-gray-800">{{ number_format($serviceCount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-500">Service</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-lg font-bold text-gray-800">{{ number_format($customerCount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-500">Pelanggan</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-lg font-bold text-gray-800">{{ number_format($biayaCount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-500">Biaya Harian</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-lg font-bold text-gray-800">{{ number_format($gajiCount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-500">Gaji Karyawan</p>
            </div>
        </div>

        <form method="POST" action="{{ route('data.backup') }}">
            @csrf
            <button type="submit"
                class="px-5 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-400 hover:to-emerald-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                <i class="fas fa-download"></i>
                Download Backup Excel
            </button>
        </form>
    </div>

    {{-- Reset Section --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 border-red-200">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-red-500 rounded-full"></div>
            <span class="text-sm font-semibold text-gray-700">Reset Data</span>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium text-red-700">Peringatan!</p>
                    <p class="text-xs text-red-600">Tindakan ini akan menghapus SEMUA data service, pelanggan, biaya harian, dan gaji karyawan. Data tidak dapat dikembalikan. <strong>Backup data terlebih dahulu sebelum reset.</strong></p>
                </div>
            </div>
        </div>

        <form id="resetForm" method="POST" action="{{ route('data.reset') }}">
            @csrf
            <input type="hidden" name="confirmation" value="RESET">
            <button type="button" @click="Swal.fire({
                icon: 'warning',
                title: 'Reset Semua Data?',
                text: 'Data service, pelanggan, biaya harian, dan gaji karyawan akan dihapus permanen!',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('resetForm').submit();
            })"
                class="px-5 py-2 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-400 hover:to-rose-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                <i class="fas fa-trash-alt"></i>
                Reset Semua Data
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
