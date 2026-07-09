<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Service {{ $tahun }} - {{ $namaToko }}</title>
    <style>
        @page { margin: 12mm; }
        body { font-family: sans-serif; font-size: 8px; color: #000; }
    .header { text-align: center; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 2px solid #B8860B; }
    .header h1 { margin: 0; font-size: 22px; letter-spacing: 1px; color: #000; }
    .header .subtitle { margin: 4px 0 0; font-size: 13px; font-weight: bold; color: #000; }
    .header .info { margin: 2px 0 0; font-size: 8px; color: #000; }
    .month-title { font-size: 12px; font-weight: bold; margin-top: 14px; margin-bottom: 5px; color: #000; border-bottom: 1px solid #B8860B; padding-bottom: 2px; }
    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    th { background: #B8860B; color: #fff; padding: 4px 4px; text-align: left; font-size: 7px; border: 1px solid #B8860B; }
    td { padding: 2px 4px; border: 1px solid #ddd; font-size: 7px; color: #000; }
    tr:nth-child(even) { background: #f9fafb; }
    .grand-title { font-size: 12px; font-weight: bold; margin-top: 16px; margin-bottom: 5px; color: #000; border-bottom: 2px solid #000; padding-bottom: 2px; }
    .footer { text-align: center; margin-top: 14px; font-size: 8px; color: #000; border-top: 1px solid #ddd; padding-top: 6px; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $namaToko }}</h1>
        <p class="subtitle">
            @if(!empty($from) && !empty($to))
                LAPORAN SERVICE ({{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }})
            @else
                LAPORAN SERVICE TAHUN {{ $tahun }}
            @endif
        </p>
        @php $alamatToko = App\Models\Setting::getValue('alamat', ''); $telpToko = App\Models\Setting::getValue('nota_telepon', ''); @endphp
        @if($alamatToko || $telpToko)
        <p class="info">{{ $alamatToko }}@if($alamatToko && $telpToko) — @endif{{ $telpToko ? 'No WA: '.$telpToko : '' }}</p>
        @endif
    </div>

    @foreach($bulanList as $ym => $data)
        @php $label = $bulanMap[$ym] ?? $ym; @endphp
        @php $rekap = $rekapBulan[$ym]; @endphp

        <div class="month-title">{{ $label }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width:4%;">No</th>
                    <th style="width:10%;">No. Service</th>
                    <th style="width:7%;">Tanggal</th>
                    <th style="width:12%;">Pelanggan</th>
                    <th style="width:12%;">Type HP</th>
                    <th style="width:10%;">Kerusakan</th>
                    <th style="width:10%; text-align:right;">Pengeluaran</th>
                    <th style="width:10%; text-align:right;">Jasa</th>
                    <th style="width:4%; text-align:center;">Teknisi</th>
                    <th style="width:11%;">Keterangan</th>
                    <th style="width:10%; text-align:right;">Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $service)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $service->service_number }}</td>
                    <td class="text-center">{{ $service->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $service->customer->nama ?? '' }}</td>
                    @php $pdfTypes = \App\Helpers\ServiceHelper::getAllTypesArray($service); @endphp
                    <td>
                        @foreach($pdfTypes as $type)
                        <div>• {{ $type }}</div>
                        @endforeach
                    </td>
                    <td>
                        @php $pdfKerusakan = \App\Helpers\ServiceHelper::getAllKerusakanArray($service); @endphp
                        @foreach($pdfKerusakan as $kr)
                        <div>• {{ $kr }}</div>
                        @endforeach
                    </td>
                    <td class="text-right">Rp {{ number_format($service->pengeluaran, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                    <td class="text-center">{{ match($service->teknisi) { 'edi' => 'E', 'nur' => 'N', default => '' } }}</td>
                    <td style="font-size:6px; word-break:break-word;">{{ $service->keterangan ?? '' }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($service->keuntungan, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                @php $subProfit = $rekap['totalJasa'] - $rekap['totalPengeluaran'] - $rekap['biayaHarian']; @endphp
            </tbody>
        </table>

        <div style="margin-top:8px; border:1px solid #B8860B; padding:6px 10px; background:#fff8e1; font-size:7px;">
            <strong style="font-size:8px;">RINGKASAN {{ strtoupper($label) }}</strong><br>
            Pengeluaran: <strong>Rp {{ number_format($rekap['totalPengeluaran'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
            Omzet: <strong>Rp {{ number_format($rekap['totalJasa'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
            Kebutuhan: <strong>Rp {{ number_format($rekap['biayaHarian'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
            Profit: <strong style="font-size:8px;">Rp {{ number_format($subProfit, 0, ',', '.') }}</strong>
        </div>
    @endforeach

    @if(empty($bulanList))
        <p style="text-align:center; margin-top:40px; color:#000;">
            @if(!empty($from) && !empty($to))
                Tidak ada data service untuk periode {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}.
            @else
                Tidak ada data service untuk tahun {{ $tahun }}.
            @endif
        </p>
    @endif

    @php $grandProfit = $grandJasa - $grandPengeluaran - $grandBiayaHarian; @endphp
    <div class="grand-title">REKAP {{ !empty($from) && !empty($to) ? 'PERIODE' : 'TAHUNAN ' . $tahun }}</div>

    <table style="width:100%; border-collapse:collapse; margin-top:8px;">
        <thead>
            <tr>
                <th style="width:20%;">Bulan</th>
                <th style="width:14%; text-align:center;">Total Service</th>
                <th style="width:18%; text-align:right;">Omzet</th>
                <th style="width:18%; text-align:right;">Pengeluaran</th>
                <th style="width:14%; text-align:right;">Kebutuhan Pribadi</th>
                <th style="width:16%; text-align:right;">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bulanList as $ym => $data)
            @php
                $r = $rekapBulan[$ym];
                $p = $r['totalJasa'] - $r['totalPengeluaran'] - $r['biayaHarian'];
            @endphp
            <tr>
                <td style="padding:3px 6px; font-size:7px;">{{ $bulanMap[$ym] ?? $ym }}</td>
                <td style="padding:3px 6px; font-size:7px; text-align:center;">{{ count($data) }}</td>
                <td style="padding:3px 6px; font-size:7px; text-align:right;">Rp {{ number_format($r['totalJasa'], 0, ',', '.') }}</td>
                <td style="padding:3px 6px; font-size:7px; text-align:right;">Rp {{ number_format($r['totalPengeluaran'], 0, ',', '.') }}</td>
                <td style="padding:3px 6px; font-size:7px; text-align:right;">Rp {{ number_format($r['biayaHarian'], 0, ',', '.') }}</td>
                <td style="padding:3px 6px; font-size:7px; text-align:right; font-weight:bold;">Rp {{ number_format($p, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="background:#fff8e1; font-weight:bold;">
                <td style="border:1px solid #B8860B; padding:4px 6px; font-size:8px;">TOTAL {{ count($bulanList) }} BULAN</td>
                <td style="border:1px solid #B8860B; padding:4px 6px; font-size:8px; text-align:center;">{{ array_sum(array_map('count', $bulanList)) }}</td>
                <td style="border:1px solid #B8860B; padding:4px 6px; font-size:8px; text-align:right;">Rp {{ number_format($grandJasa, 0, ',', '.') }}</td>
                <td style="border:1px solid #B8860B; padding:4px 6px; font-size:8px; text-align:right;">Rp {{ number_format($grandPengeluaran, 0, ',', '.') }}</td>
                <td style="border:1px solid #B8860B; padding:4px 6px; font-size:8px; text-align:right;">Rp {{ number_format($grandBiayaHarian, 0, ',', '.') }}</td>
                <td style="border:1px solid #B8860B; padding:4px 6px; font-size:8px; text-align:right; font-weight:bold;">Rp {{ number_format($grandProfit, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }} | {{ $namaToko }}</p>
        @if(!empty($customerName))
        <p>Filter Pelanggan: {{ $customerName }}</p>
        @endif
    </div>
</body>
</html>
