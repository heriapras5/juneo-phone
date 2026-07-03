@extends('layouts.app')

@section('title', 'Detail Service')
@section('page-title', 'Detail Service')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Detail Service</h3>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-sm font-mono text-yellow-600">{{ $service->service_number }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $service->getStatusClass() }}">
                        {{ $service->getStatusLabel() }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('services.index', ['edit' => $service->id]) }}"
                    class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button" onclick="document.getElementById('previewModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm">
                    <i class="fas fa-print"></i> Cetak Nota
                </button>
                <a href="{{ route('services.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                    Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Tanggal</p>
                        <p class="text-sm font-medium text-gray-900">{{ $service->tanggal->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Pelanggan</p>
                        <p class="text-sm font-medium text-gray-900">{{ $service->customer?->nama ?? '-' }}</p>
                        @if($service->customer?->no_hp)
                            <p class="text-xs text-gray-500">{{ $service->customer->no_hp }}</p>
                        @endif
                    </div>

                    @if($service->estimasi_selesai)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Estimasi Selesai</p>
                        <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($service->estimasi_selesai)->format('d F Y') }}</p>
                    </div>
                    @endif
                    @if($service->tanggal_selesai)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Tanggal Selesai</p>
                        <p class="text-sm font-medium text-gray-900">{{ $service->tanggal_selesai->format('d F Y') }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Teknisi</p>
                        <p class="text-sm font-medium text-gray-900">
                            @if($service->teknisi === 'edi') Edi (Karyawan 1) <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200 ml-1">E</span>
                            @elseif($service->teknisi === 'nur') Nur (Karyawan 2) <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200 ml-1">N</span>
                            @else Adit
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Pengeluaran (Modal)</p>
                        <p class="text-sm font-medium text-gray-900">Rp {{ number_format($service->pengeluaran, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Jasa</p>
                        <p class="text-lg font-bold text-yellow-600">Rp {{ number_format($service->jasa, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Keuntungan</p>
                        <p class="text-lg font-bold {{ $service->keuntungan >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            Rp {{ number_format($service->keuntungan, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($service->keterangan)
        <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Keterangan</p>
            <p class="text-sm text-gray-600">{{ $service->keterangan }}</p>
        </div>
        @endif
    </div>

    @php
        $itemsTotalJasa = $service->serviceItems->sum('jasa');
        $itemsTotalModal = $service->serviceItems->sum('pengeluaran');
    @endphp
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-mobile-alt text-yellow-500"></i>
            Item Service &amp; Biaya
        </h3>
        @if($service->serviceItems->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Type HP</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Kerusakan</th>
                        <th class="text-right py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Modal</th>
                        <th class="text-right py-2 px-2 text-xs text-gray-500 uppercase tracking-wider">Jasa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($service->serviceItems as $item)
                    <tr>
                        <td class="py-2 px-2 text-gray-900 font-medium">{{ $item->type_hp }}</td>
                        <td class="py-2 px-2 text-gray-600">
                            @php $krList = is_array($item->kerusakan) ? $item->kerusakan : (json_decode($item->kerusakan, true) ?: []); @endphp
                            @foreach($krList as $kr)
                                <div class="leading-relaxed whitespace-nowrap"><span class="text-gray-400">•</span> {{ $kr }}</div>
                            @endforeach
                            @if(empty($krList))
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-2 px-2 text-right text-gray-600">Rp {{ number_format($item->pengeluaran, 0, ',', '.') }}</td>
                        <td class="py-2 px-2 text-right text-yellow-600 font-semibold">Rp {{ number_format($item->jasa, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 font-semibold">
                        <td colspan="2" class="py-2 px-2 text-gray-900">Total</td>
                        <td class="py-2 px-2 text-right text-gray-900">Rp {{ number_format($itemsTotalModal, 0, ',', '.') }}</td>
                        <td class="py-2 px-2 text-right text-yellow-600">Rp {{ number_format($itemsTotalJasa, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- Garansi --}}
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fas fa-shield text-yellow-500"></i> Garansi
            </h4>
            <form method="POST" action="{{ route('services.garansi', $service) }}" class="flex items-center gap-2">
                @csrf
                @method('PUT')
                <input type="text" name="garansi" value="{{ old('garansi', $service->garansi) }}" placeholder="Isi keterangan garansi..."
                    class="flex-1 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none">
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-medium rounded-lg text-sm flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    {{-- Timeline Riwayat Status --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-clock-rotate-left text-yellow-500"></i>
            Riwayat Status
        </h3>

        @php
            $statusTimelineClasses = [
                'menunggu' => ['dot' => 'border-yellow-500', 'badge' => 'bg-yellow-100 text-yellow-700'],
                'dikerjakan' => ['dot' => 'border-blue-500', 'badge' => 'bg-blue-100 text-blue-700'],
                'selesai' => ['dot' => 'border-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700'],
                'diambil' => ['dot' => 'border-gray-500', 'badge' => 'bg-gray-100 text-gray-700'],
                'batal' => ['dot' => 'border-red-500', 'badge' => 'bg-red-100 text-red-700'],
            ];
            $statusLabels = \App\Models\Service::statusLabels();
        @endphp

        @if($service->histories->isNotEmpty())
            <div class="relative">
                <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-200"></div>
                <div class="space-y-4">
                    @foreach($service->histories as $history)
                        @php $cls = $statusTimelineClasses[$history->status_to] ?? ['dot' => 'border-gray-500', 'badge' => 'bg-gray-100 text-gray-700']; @endphp
                        <div class="relative pl-10">
                            <div class="absolute left-2.5 top-1.5 w-3 h-3 rounded-full border-2 bg-white {{ $cls['dot'] }}"></div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $cls['badge'] }}">
                                        {{ $statusLabels[$history->status_to] ?? $history->status_to }}
                                    </span>
                                    @if($history->status_from)
                                        <span class="text-xs text-gray-400">
                                            dari {{ $statusLabels[$history->status_from] ?? $history->status_from }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $history->created_at->format('d M Y H:i') }}
                                    @if($history->user)
                                        &mdash; oleh {{ $history->user->name }}
                                    @endif
                                </p>
                                @if($history->note)
                                    <p class="text-sm text-gray-600 mt-1">{{ $history->note }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-clock-rotate-left text-3xl mb-2 block"></i>
                <p class="text-sm">Belum ada riwayat perubahan status.</p>
                <p class="text-xs mt-1">Riwayat akan tercatat otomatis saat status service diubah.</p>
            </div>
        @endif
    </div>
</div>

{{-- Modal Preview Cetak Nota --}}
<div id="previewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div onclick="event.stopPropagation()" class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 border border-gray-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Preview Nota</h3>
            <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="bg-gray-100 rounded-lg p-4 flex justify-center" style="overflow-x: auto;">
            <div id="notaPreview" class="bg-white shadow-sm" style="width: 58mm; transform: scale(1.5); transform-origin: top center;">
                @include('exports.partials.nota-content')
            </div>
        </div>

        <div class="flex items-center justify-center gap-3 mt-4 pt-4 border-t border-gray-100">
            <button onclick="document.getElementById('previewModal').classList.add('hidden')"
                class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">
                Tutup
            </button>
            <button onclick="printPreview()"
                class="px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 rounded-xl shadow-lg shadow-yellow-500/25 flex items-center gap-2">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('services.print-pdf', $service) }}" target="_blank"
                class="px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 rounded-xl shadow-lg shadow-blue-500/25 flex items-center gap-2">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function printPreview() {
    const printContent = document.getElementById('notaPreview').innerHTML;
    const originalTitle = document.title;
    document.title = 'Nota Service - {{ $service->service_number }}';
    const win = window.open('', '', 'width=340,height=800');
    win.document.write('\
        <html>\
        <head>\
            <title>Nota Service - {{ $service->service_number }}</title>\
            <style>\
                @page { margin: 0; }\
                body { font-family: "Helvetica", "Arial", sans-serif; font-size: 9px; color: #000; margin: 0; padding: 0; width: 58mm; }\
                * { box-sizing: border-box; }\
                .nota-content h1 { font-family: "Helvetica", "Arial", sans-serif !important; font-weight: 700 !important; color: #000 !important; }\
            </style>\
        </head>\
        <body onload="window.print();window.close()">' + printContent + '\
        </body>\
        </html>\
    ');
    win.document.close();
    setTimeout(function() { document.title = originalTitle; }, 100);
}
</script>
@endpush
@endsection
