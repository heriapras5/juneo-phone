<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Jual Beli HP - {{ $namaToko }}</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: sans-serif; font-size: 8px; color: #000; }
        .header { text-align: center; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 2px solid #B8860B; }
        .header h1 { margin: 0; font-size: 18px; letter-spacing: 1px; color: #000; }
        .header .subtitle { margin: 4px 0 0; font-size: 12px; font-weight: bold; color: #000; }
        .header .info { margin: 2px 0 0; font-size: 7px; color: #000; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .summary td { border: 1px solid #ddd; padding: 5px 7px; background: #f9fafb; width: 16.66%; }
        .summary .label { display: block; font-size: 7px; color: #555; text-transform: uppercase; letter-spacing: .5px; }
        .summary .value { display: block; font-size: 10px; font-weight: bold; color: #000; margin-top: 2px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 7px; }
        table.data th { background: #B8860B; color: #fff; padding: 4px; text-align: left; border: 1px solid #B8860B; }
        table.data td { padding: 2px 4px; border: 1px solid #ddd; color: #000; }
        table.data tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #666; }
        .footer { text-align: center; margin-top: 10px; font-size: 7px; color: #000; border-top: 1px solid #ddd; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $namaToko }}</h1>
        <p class="subtitle">LAPORAN JUAL BELI HP</p>
        @php
            $alamatToko = \App\Models\Setting::getValue('alamat', '');
            $telpToko = \App\Models\Setting::getValue('nota_telepon', '');
            $dari = !empty($filters['from']) ? \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') : 'awal';
            $sampai = !empty($filters['to']) ? \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') : 'sekarang';
            $adaPeriode = !empty($filters['from']) || !empty($filters['to']);
        @endphp
        @if($alamatToko || $telpToko)
        <p class="info">{{ $alamatToko }}@if($alamatToko && $telpToko) - @endif{{ $telpToko ? 'No WA: '.$telpToko : '' }}</p>
        @endif
        <p class="info">{{ $adaPeriode ? 'Periode: ' . $dari . ' s.d. ' . $sampai : 'Seluruh periode' }}</p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="label">Stok Tersedia</span>
                <span class="value">{{ number_format($summary['stock_count'], 0, ',', '.') }} unit</span>
            </td>
            <td>
                <span class="label">Modal Stok</span>
                <span class="value">Rp {{ number_format($summary['stock_modal'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Terjual</span>
                <span class="value">{{ number_format($summary['sold_count'], 0, ',', '.') }} unit</span>
            </td>
            <td>
                <span class="label">Omzet</span>
                <span class="value">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Modal Terjual</span>
                <span class="value">Rp {{ number_format($summary['modal_terjual'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Profit</span>
                <span class="value">Rp {{ number_format($summary['laba'], 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:3%; text-align:center;">No</th>
                <th style="width:6%;">Kode</th>
                <th style="width:14%;">Tipe HP</th>
                <th style="width:10%;">Brand / Storage</th>
                <th style="width:8%;">Kondisi</th>
                <th style="width:8%;">Sumber</th>
                <th style="width:12%;">Masuk</th>
                <th style="width:7%; text-align:center;">Status</th>
                <th style="width:13%;">Keluar</th>
                <th style="width:10%; text-align:right;">Profit</th>
                <th style="width:9%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trades as $trade)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $trade->kode ?? '' }}</td>
                <td>{{ $trade->type_hp }}</td>
                <td>{{ collect([$trade->brand, $trade->storage])->filter()->implode(' / ') ?: '-' }}</td>
                <td>{{ \App\Models\PhoneTrade::kondisiLabels()[$trade->kondisi] ?? $trade->kondisi }}</td>
                <td>{{ $trade->sumber_label }}</td>
                <td>
                    {{ $trade->tanggal_masuk?->format('d/m/Y') }}
                    <br><span class="text-muted">Beli: Rp {{ number_format($trade->harga_beli, 0, ',', '.') }}</span>
                    @if((float) $trade->biaya_perbaikan > 0)
                    <br><span class="text-muted">Perbaikan: Rp {{ number_format($trade->biaya_perbaikan, 0, ',', '.') }}</span>
                    @endif
                </td>
                <td class="text-center">{{ $trade->status_label }}</td>
                <td>
                    @if($trade->status === \App\Models\PhoneTrade::STATUS_TERJUAL)
                        {{ $trade->tanggal_keluar?->format('d/m/Y') }}
                        <br><span class="text-muted">Jual: Rp {{ number_format($trade->harga_jual, 0, ',', '.') }}</span>
                        @if($trade->pembeli)
                        <br><span class="text-muted">{{ $trade->pembeli }}</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">
                    {{ $trade->status === \App\Models\PhoneTrade::STATUS_TERJUAL ? 'Rp ' . number_format($trade->keuntungan, 0, ',', '.') : '-' }}
                </td>
                <td>{{ $trade->keterangan ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align:center; padding:20px; color:#999;">Tidak ada data jual beli HP.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }} | {{ $namaToko }}</p>
    </div>
</body>
</html>
