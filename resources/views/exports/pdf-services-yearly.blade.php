<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Service {{ $tahun }} - {{ $namaToko }}</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: sans-serif; font-size: 8px; }
    .header { text-align: center; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #B8860B; }
    .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
    .header .subtitle { margin: 4px 0 0; font-size: 14px; font-weight: bold; color: #333; }
    .header .info { margin: 2px 0 0; font-size: 8px; color: #555; }
    .month-title { font-size: 12px; font-weight: bold; margin-top: 16px; margin-bottom: 6px; color: #B8860B; border-bottom: 1px solid #B8860B; padding-bottom: 3px; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    th { background: #B8860B; color: white; padding: 5px 6px; text-align: left; font-size: 7px; }
    td { padding: 3px 6px; border-bottom: 1px solid #ddd; font-size: 7px; }
    tr:nth-child(even) { background: #f9fafb; }
    .subtotal-row td { font-weight: bold; border-top: 2px solid #B8860B; background: #fff8e1; padding-top: 5px; }
    .grand-title { font-size: 12px; font-weight: bold; margin-top: 18px; margin-bottom: 6px; color: #333; border-bottom: 2px solid #333; padding-bottom: 3px; }
    .grand-table td { padding: 4px 10px; font-size: 10px; }
    .grand-table .label { font-weight: bold; }
    .grand-table .value { text-align: right; font-weight: bold; }
    .grand-table .total-row td { border-top: 2px solid #333; font-size: 11px; padding-top: 6px; }
    .footer { text-align: center; margin-top: 16px; font-size: 8px; color: #888; border-top: 1px solid #ddd; padding-top: 8px; }
    .page-break { page-break-before: always; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-green { color: #059669; font-weight: bold; }
    .text-red { color: #DC2626; font-weight: bold; }
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

        @if(!$loop->first)
        <div class="page-break"></div>
        @endif

        <div class="month-title">{{ $label }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width:3%;">No</th>
                    <th style="width:9%;">No. Service</th>
                    <th style="width:8%;">Tanggal</th>
                    <th style="width:11%;">Pelanggan</th>
                    <th style="width:10%;">Type HP</th>
                    <th style="width:18%;">Kerusakan</th>
                    <th style="width:9%;">Pengeluaran</th>
                    <th style="width:9%;">Jasa</th>
                    <th style="width:5%;">Teknisi</th>
                    <th style="width:9%;">Keterangan</th>
                    <th style="width:9%;">Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $service)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $service->service_number }}</td>
                    <td class="text-center">{{ $service->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $service->customer->nama ?? '-' }}</td>
                    @php
                        $pdfTypes = collect([$service->type_hp])
                            ->concat($service->serviceItems->pluck('type_hp'))
                            ->unique()->values();
                    @endphp
                    <td>
                        @foreach($pdfTypes as $type)
                        <div>• {{ $type }}</div>
                        @endforeach
                    </td>
                    <td>
                        @php
                            $krService = $service->kerusakan;
                            $krDecoded = is_string($krService) ? (json_decode($krService, true) ?? [$krService]) : (array) $krService;
                            $pdfKerusakan = collect($krDecoded)
                                ->concat($service->serviceItems->pluck('kerusakan')->flatten())
                                ->unique()->values();
                        @endphp
                        @foreach($pdfKerusakan as $kr)
                        <div>• {{ $kr }}</div>
                        @endforeach
                    </td>
                    <td class="text-right">Rp {{ number_format($service->pengeluaran, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($service->jasa, 0, ',', '.') }}</td>
                    <td class="text-center">{{ match($service->teknisi) { 'edi' => 'E', 'nur' => 'N', default => '-' } }}</td>
                    <td style="font-size:6px; color:#666; max-width:80px; word-wrap:break-word;">{{ $service->keterangan ?? '' }}</td>
                    <td class="text-right {{ $service->keuntungan >= 0 ? 'text-green' : 'text-red' }}">Rp {{ number_format($service->keuntungan, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="subtotal-row">
                    <td colspan="7" class="text-right">Subtotal {{ $label }}</td>
                    <td class="text-right">Rp {{ number_format($rekap['totalJasa'], 0, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($rekap['totalJasa'] - $rekap['totalPengeluaran'] - $rekap['biayaHarian'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="text-right" style="font-size:6px; color:#888;">Kebutuhan Pribadi: Rp {{ number_format($rekap['biayaHarian'], 0, ',', '.') }} | Pengeluaran: Rp {{ number_format($rekap['totalPengeluaran'], 0, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right" style="font-size:6px; color:#888;">Biaya: Rp {{ number_format($rekap['biayaHarian'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach

    @if(empty($bulanList))
        <p style="text-align:center; margin-top:40px; color:#999;">
            @if(!empty($from) && !empty($to))
                Tidak ada data service untuk periode {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}.
            @else
                Tidak ada data service untuk tahun {{ $tahun }}.
            @endif
        </p>
    @endif

    @php $grandProfit = $grandJasa - $grandPengeluaran - $grandBiayaHarian; @endphp
    <div class="grand-title">REKAP {{ !empty($from) && !empty($to) ? 'PERIODE' : 'TAHUNAN ' . $tahun }}</div>

    <table class="grand-table" style="width:60%; margin-left:auto; margin-right:auto;">
        <tr>
            <td class="label">Pengeluaran</td>
            <td class="value">Rp {{ number_format($grandPengeluaran, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Omzet</td>
            <td class="value">Rp {{ number_format($grandJasa, 0, ',', '.') }}</td>
        </tr>
        <tr><td colspan="2" style="padding:2px;"></td></tr>
        <tr>
            <td class="label">Kebutuhan Pribadi</td>
            <td class="value">Rp {{ number_format($grandBiayaHarian, 0, ',', '.') }}</td>
        </tr>
        <tr><td colspan="2" style="padding:2px;"></td></tr>
        <tr class="total-row">
            <td class="label">Profit</td>
            <td class="value {{ $grandProfit >= 0 ? 'text-green' : 'text-red' }}">Rp {{ number_format($grandProfit, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }} | {{ $namaToko }}</p>
        @if(!empty($customerName))
        <p>Filter Pelanggan: {{ $customerName }}</p>
        @endif
    </div>
</body>
</html>
