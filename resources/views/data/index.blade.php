@extends('layouts.app')

@section('title', 'Backup & Reset Data')
@section('page-title', 'Backup & Reset Data')

@section('content')
<div class="lg:grid lg:grid-cols-2 lg:gap-6 space-y-6 lg:space-y-0">

    {{-- Backup Section --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
            <span class="text-sm font-semibold text-gray-700">Backup Data</span>
        </div>
        <p class="text-xs text-gray-500 mb-4">Download seluruh data transaksional (Service, Pelanggan, Kebutuhan Pribadi) dalam format Excel (.xlsx).</p>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50/50 rounded-lg p-3 text-center">
                <i class="fas fa-wrench text-blue-400 text-lg mb-1"></i>
                <p class="text-lg font-bold text-gray-800">{{ number_format($serviceCount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-500">Service</p>
            </div>
            <div class="bg-green-50/50 rounded-lg p-3 text-center">
                <i class="fas fa-users text-green-400 text-lg mb-1"></i>
                <p class="text-lg font-bold text-gray-800">{{ number_format($customerCount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-500">Pelanggan</p>
            </div>
            <div class="bg-purple-50/50 rounded-lg p-3 text-center">
                <i class="fas fa-shopping-bag text-purple-400 text-lg mb-1"></i>
                <p class="text-lg font-bold text-gray-800">{{ number_format($biayaCount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-500">Kebutuhan Pribadi</p>
            </div>
        </div>

        <form method="POST" action="{{ route('data.backup') }}" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <button type="submit" :disabled="loading"
                    class="px-5 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-400 hover:to-emerald-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <i x-show="!loading" class="fas fa-download"></i>
                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                <span x-text="loading ? 'Mengunduh...' : 'Download Backup Excel'"></span>
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
                    <p class="text-xs text-red-600">Tindakan ini akan menghapus SEMUA data service, pelanggan, dan Kebutuhan Pribadi. Data tidak dapat dikembalikan. <strong>Backup data terlebih dahulu sebelum reset.</strong></p>
                </div>
            </div>
        </div>

        <form id="resetForm" method="POST" action="{{ route('data.reset') }}" x-data="{ confirmText: '' }">
            @csrf
            <div class="mb-3">
                <label class="text-xs text-gray-500 mb-1 block">Ketik <strong>RESET</strong> untuk konfirmasi</label>
                <input type="text" x-model="confirmText" autocomplete="off"
                       class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <button type="submit" :disabled="confirmText !== 'RESET'"
                    class="px-5 py-2 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-400 hover:to-rose-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm disabled:opacity-40 disabled:cursor-not-allowed">
                <i class="fas fa-trash-alt"></i> Reset Semua Data
            </button>
        </form>
    </div>

</div>
@endsection
