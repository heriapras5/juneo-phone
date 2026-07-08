@extends('layouts.app')

@section('title', 'Backup & Restore Data')
@section('page-title', 'Backup & Restore Data')

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

    {{-- Restore Section --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-emerald-500 rounded-full"></div>
            <span class="text-sm font-semibold text-gray-700">Restore Data</span>
        </div>
        <p class="text-xs text-gray-500 mb-4">Upload file backup (.xlsx) yang telah di-download sebelumnya untuk mengembalikan data Service, Pelanggan, dan Kebutuhan Pribadi.</p>

        <form method="POST" action="{{ route('data.restore') }}" enctype="multipart/form-data"
              x-data="{ loading: false, fileName: '' }" @submit="loading = true">
            @csrf

            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-emerald-400/50 cursor-pointer bg-gray-50/50"
                :class="{ 'border-emerald-400/50 bg-emerald-50/50': fileName }"
                @click="$refs.fileInput.click()">
                <div class="flex flex-col items-center gap-2">
                    <i class="fas fa-cloud-upload-alt text-3xl text-emerald-500/50"></i>
                    <p class="text-sm text-gray-500">Klik untuk pilih file backup</p>
                    <p class="text-xs text-gray-400">Format: .xlsx (maks 5MB)</p>
                </div>
                <input type="file" name="file" x-ref="fileInput" accept=".xlsx"
                    class="hidden" @change="fileName = $event.target.files[0]?.name || ''">
                <template x-if="fileName">
                    <div class="mt-3 flex items-center justify-center gap-2 text-sm text-emerald-700">
                        <i class="fas fa-file-excel"></i>
                        <span x-text="fileName"></span>
                    </div>
                </template>
            </div>

            @error('file')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror

            <div class="mt-4">
                <button type="submit" :disabled="loading || !fileName"
                        class="px-5 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <i x-show="!loading" class="fas fa-upload"></i>
                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                    <span x-text="loading ? 'Merestorasi...' : 'Restore Data'"></span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
