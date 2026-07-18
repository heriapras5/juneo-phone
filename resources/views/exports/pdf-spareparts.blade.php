<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Sparepart - {{ $namaToko }}</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: sans-serif; font-size: 8px; color: #000; }
        .header { text-align: center; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #B8860B; }
        .header h1 { margin: 0; font-size: 18px; letter-spacing: 1px; color: #000; }
        .header .subtitle { margin: 4px 0 0; font-size: 12px; font-weight: bold; color: #000; }
        .header .info { margin: 2px 0 0; font-size: 7px; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 7px; }
        th { background: #B8860B; color: #fff; padding: 4px 4px; text-align: left; border: 1px solid #B8860B; }
        td { padding: 2px 4px; border: 1px solid #ddd; color: #000; }
        tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { text-align: center; margin-top: 10px; font-size: 7px; color: #000; border-top: 1px solid #ddd; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $namaToko }}</h1>
        <p class="subtitle">DATA SPAREPART</p>
        @php $alamatToko = \App\Models\Setting::getValue('alamat', ''); $telpToko = \App\Models\Setting::getValue('nota_telepon', ''); @endphp
        @if($alamatToko || $telpToko)
        <p class="info">{{ $alamatToko }}@if($alamatToko && $telpToko) — @endif{{ $telpToko ? 'No WA: '.$telpToko : '' }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%; text-align:center;">No</th>
                <th style="width:12%;">Kategori</th>
                <th style="width:22%;">Nama Barang</th>
                <th style="width:18%;">Type</th>
                <th style="width:6%; text-align:center;">Stok</th>
                <th style="width:12%; text-align:right;">Harga Beli</th>
                <th style="width:10%;">Toko</th>
                <th style="width:16%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($spareparts as $sparepart)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $kategoriLabels[$sparepart->kategori] ?? $sparepart->kategori }}</td>
                <td>{{ $sparepart->nama_barang }}</td>
                <td>{{ $sparepart->type_hp ?? '-' }}</td>
                <td class="text-center">{{ $sparepart->stok }}</td>
                <td class="text-right">{{ $sparepart->harga_beli ? 'Rp '.number_format($sparepart->harga_beli, 0, ',', '.') : '-' }}</td>
                <td>{{ $sparepart->toko ?? '-' }}</td>
                <td>{{ $sparepart->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:20px; color:#999;">Tidak ada data sparepart.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }} | {{ $namaToko }}</p>
    </div>
</body>
</html>
