@extends('layouts.app')

@section('title', 'Import Data')
@section('page-title', 'Import Data Service')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Upload File Excel / CSV</h3>
            <a href="{{ route('reports.import.template') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm flex items-center gap-2 transition-colors">
                <i class="fas fa-download"></i>
                Download Template
            </a>
        </div>

        <form method="POST" action="{{ route('reports.import') }}" enctype="multipart/form-data" x-data="{ loading: false, fileName: '', fileSize: '' }" @submit="loading = true">
            @csrf

            <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-yellow-400/50 cursor-pointer bg-gray-50/50"
                :class="{ 'border-yellow-400/50 bg-yellow-50/50': loading || fileName }"
                @click="$refs.fileInput.click()"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files">
                <div class="flex flex-col items-center gap-3">
                    <i class="fas fa-cloud-upload-alt text-4xl text-yellow-500/50"></i>
                    <p class="text-sm text-gray-500">Seret file ke sini atau <span class="text-yellow-600 font-medium">klik untuk pilih</span></p>
                    <p class="text-xs text-gray-400">Format: XLSX, XLS, CSV (maks 5MB)</p>
                </div>
                <input type="file" name="file" x-ref="fileInput" accept=".xlsx,.xls,.csv"
                    class="hidden" @change="fileName = $event.target.files[0]?.name || ''; fileSize = $event.target.files[0] ? ($event.target.files[0].size / 1024).toFixed(1) + ' KB' : ''">
                <template x-if="fileName">
                    <div class="mt-3 flex items-center justify-center gap-2 text-sm text-yellow-700">
                        <i class="fas fa-file-excel"></i>
                        <span x-text="fileName"></span>
                        <span class="text-yellow-400" x-text="'(' + fileSize + ')'"></span>
                    </div>
                </template>
            </div>

            @error('file')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" :disabled="loading || !fileName"
                        class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg flex items-center gap-2 shadow-sm shadow-yellow-500/25 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <i x-show="!loading" class="fas fa-upload"></i>
                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                    <span x-text="loading ? 'Mengimport...' : 'Import Data'"></span>
                </button>
                <template x-if="!fileName">
                    <span class="text-xs text-gray-400">Pilih file terlebih dahulu</span>
                </template>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-2">Format File Import</h3>
        <p class="text-sm text-gray-500 mb-4">File harus memiliki header kolom berikut (urutan bebas):</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Kolom</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Keterangan</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Wajib</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr><td class="px-3 py-2 text-gray-700">No</td><td class="px-3 py-2 text-gray-500">Diabaikan saat import</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 font-medium text-gray-800">Tanggal</td><td class="px-3 py-2 text-gray-500">Format YYYY-MM-DD</td><td class="px-3 py-2 text-emerald-600 font-medium">Ya</td></tr>
                    <tr><td class="px-3 py-2 font-medium text-gray-800">Pelanggan</td><td class="px-3 py-2 text-gray-500">Nama pelanggan</td><td class="px-3 py-2 text-emerald-600 font-medium">Ya</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">No. HP</td><td class="px-3 py-2 text-gray-500">Nomor HP pelanggan</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Type HP</td><td class="px-3 py-2 text-gray-500">Tipe HP</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Kerusakan</td><td class="px-3 py-2 text-gray-500">Deskripsi kerusakan</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Pengeluaran</td><td class="px-3 py-2 text-gray-500">Modal/biaya</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Jasa</td><td class="px-3 py-2 text-gray-500">Total tagihan ke pelanggan</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Teknisi</td><td class="px-3 py-2 text-gray-500">Kosongkan (Adit), "edi", atau "nur"</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Status</td><td class="px-3 py-2 text-gray-500">menunggu / dikerjakan / selesai / diambil / batal</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Estimasi Selesai</td><td class="px-3 py-2 text-gray-500">Format YYYY-MM-DD</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Tanggal Selesai</td><td class="px-3 py-2 text-gray-500">Format YYYY-MM-DD</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                    <tr><td class="px-3 py-2 text-gray-700">Keterangan</td><td class="px-3 py-2 text-gray-500">Catatan tambahan</td><td class="px-3 py-2 text-gray-400">Opsional</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
