<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Service - {{ App\Models\Setting::getValue('nama_toko', 'Juneo Phone') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; }
        .header { text-align: center; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #B8860B; }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: 1px; }
        .header .subtitle { margin: 4px 0 0; font-size: 13px; font-weight: bold; color: #333; }
        .header .periode { margin: 2px 0 0; font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #B8860B; color: white; padding: 6px 10px; text-align: left; font-size: 10px; }
        td { padding: 5px 10px; border-bottom: 1px solid #ddd; font-size: 10px; }
        tr:nth-child(even) { background: #f9fafb; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; padding-top: 8px; }
        .footer { text-align: center; margin-top: 15px; font-size: 8px; color: #888; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    @php $namaUsaha = App\Models\Setting::getValue('nama_toko', 'Juneo Phone'); @endphp
    <div class="header">
        <h1>{{ $namaUsaha }}</h1>
        <p class="subtitle">LAPORAN SERVICE</p>
        <p class="periode">Periode: {{ request('date_from', 'Semua') }} s/d {{ request('date_to', 'Semua') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Service</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Type HP</th>
                <th>Pengeluaran</th>
                <th>Jasa</th>
                <th>Teknisi</th>
                <th>Keuntungan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $service->service_number }}</td>
                <td>{{ $service->tanggal->format('d/m/Y') }}</td>
                <td>{{ $service->customer->nama ?? '-' }}</td>
                <td>@php $pdfTypes = collect([$service->type_hp])->concat($service->serviceItems->pluck('type_hp'))->unique()->values(); @endphp{{ $pdfTypes->implode(' | ') }}</td>
                <td>Rp {{ number_format($service->pengeluaran, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                <td>{{ match($service->teknisi) { 'edi' => 'E', 'nur' => 'N', default => '-' } }}</td>
                <td>Rp {{ number_format($service->keuntungan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align:right;">Total</td>
                <td>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($totalJasa, 0, ',', '.') }}</td>
                <td></td>
                <td>Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }} | {{ $namaUsaha }} - Sistem Manajemen Service</p>
    </div>
</body>
</html>
