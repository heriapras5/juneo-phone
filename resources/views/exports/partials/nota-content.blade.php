@php
$namaToko = App\Models\Setting::getValue('nama_toko', 'Juneo Phone');
$alamat = App\Models\Setting::getValue('alamat', '');
$notaTelepon = App\Models\Setting::getValue('nota_telepon', '');
$notaRekening = App\Models\Setting::getValue('nota_rekening', '');
$logo = App\Models\Setting::getValue('logo', '');

$namaTeknisi = match($service->teknisi) {
    'edi' => 'Edi',
    'nur' => 'Nur',
    default => 'Adit',
};
@endphp

<div class="nota-content" style="padding: 2mm 3mm; font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #000; line-height: 1.2;">
    <div style="margin-bottom: 6px; text-align: center;">
        <h1 style="margin: 0; font-size: 16px; color: #000; letter-spacing: 1px; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: 700;">{{ $namaToko }}</h1>
        <p style="margin: 1px 0 3px; font-size: 7px; color: #000; letter-spacing: 1px; font-family: 'Helvetica', 'Arial', sans-serif; font-weight: 700;">Service HP | Android &amp; iPhone</p>
        @if($alamat)
        <p style="color: #000; font-size: 8px; margin: 1px 0;">{{ $alamat }}</p>
        @endif
        @if($notaTelepon)
        <p style="color: #000; font-size: 8px; margin: 1px 0;">No WA: {{ $notaTelepon }}</p>
        @endif
        @if($notaRekening)
        <p style="color: #000; font-size: 8px; margin: 1px 0;">{{ $notaRekening }}</p>
        @endif
        <hr style="border: none; border-top: 1px solid #000; margin: 4px auto; width: 100%;">
        <p style="font-size: 11px; font-weight: bold; color: #000; letter-spacing: 1px; margin: 3px 0 1px;">NOTA SERVICE</p>
        <p style="font-size: 9px; color: #000; font-weight: bold; margin: 1px 0;">#{{ $service->service_number }}</p>
    </div>

    <table style="width: 100%; margin: 4px 0; border-collapse: collapse; font-size: 9px;">
        <tr>
            <td style="width:55px; font-weight: bold; color: #000; padding: 2px 3px; vertical-align: top;">Tgl</td>
            <td style="width:5px; padding: 2px 3px; vertical-align: top;">:</td>
            <td style="padding: 2px 3px; vertical-align: top;">{{ $service->tanggal_selesai ? $service->tanggal_selesai->format('d/m/Y') : now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #000; padding: 2px 3px; vertical-align: top;">Pelanggan</td>
            <td style="padding: 2px 3px; vertical-align: top;">:</td>
            <td style="padding: 2px 3px; vertical-align: top;">{{ $service->customer->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #000; padding: 2px 3px; vertical-align: top;">No. HP</td>
            <td style="padding: 2px 3px; vertical-align: top;">:</td>
            <td style="padding: 2px 3px; vertical-align: top;">{{ $service->customer->no_hp ?? '-' }}</td>
        </tr>
        @if($service->customer->alamat)
        <tr>
            <td style="font-weight: bold; color: #000; padding: 2px 3px; vertical-align: top;">Alamat</td>
            <td style="padding: 2px 3px; vertical-align: top;">:</td>
            <td style="padding: 2px 3px; vertical-align: top;">{{ $service->customer->alamat }}</td>
        </tr>
        @endif
        <tr>
            <td style="font-weight: bold; color: #000; padding: 2px 3px; vertical-align: top;">Teknisi</td>
            <td style="padding: 2px 3px; vertical-align: top;">:</td>
            <td style="padding: 2px 3px; vertical-align: top;">{{ $namaTeknisi }}</td>
        </tr>
    </table>

    @php
        $displayItems = $service->serviceItems ?? collect();
        if ($displayItems->isEmpty()) {
            $fallbackKerusakan = $service->kerusakan;
            if (is_string($fallbackKerusakan)) {
                $decoded = json_decode($fallbackKerusakan, true);
                if (is_array($decoded)) $fallbackKerusakan = implode(', ', $decoded);
            }
            $displayItems = collect([(object)[
                'type_hp' => $service->type_hp,
                'kerusakan' => $fallbackKerusakan,
                'jasa' => $service->jasa,
            ]]);
        }
    @endphp

    <table style="width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 9px;">
        <thead>
            <tr>
                <th style="background: #eee; color: #000; padding: 4px 4px; font-size: 8px; text-align: center; width: 20px;">No</th>
                <th style="background: #eee; color: #000; padding: 4px 4px; font-size: 8px; text-align: left;">Keterangan</th>
                <th style="background: #eee; color: #000; padding: 4px 4px; font-size: 8px; text-align: right; width: 85px;">Jasa</th>
            </tr>
        </thead>
        <tbody>
            @php $itemNo = 1; $prevTypeHp = ''; @endphp
            @foreach($displayItems as $item)
            <tr>
                <td style="text-align: center; padding: 4px 4px; border-bottom: 1px solid #ddd;">{{ $itemNo++ }}</td>
                <td style="padding: 4px 4px; border-bottom: 1px solid #ddd;">
                    @if($item->type_hp !== $prevTypeHp)
                    <span style="font-size: 8px; color: #000;">{{ $item->type_hp }}</span>
                    @endif
                    <span style="font-size: 7px; color: #555; display: block; margin-top: 1px;">{{ is_array($item->kerusakan) ? implode(', ', $item->kerusakan) : $item->kerusakan }}</span>
                </td>
                <td style="text-align: right; padding: 4px 4px; border-bottom: 1px solid #ddd;">Rp {{ number_format($item->jasa ?? 0, 0, ',', '.') }}</td>
            </tr>
            @php $prevTypeHp = $item->type_hp; @endphp
            @endforeach
        </tbody>
    </table>

    <div style="margin: 4px 0; padding-top: 3px; border-top: 2px solid #000; text-align: right;">
        <div style="font-size: 12px; font-weight: bold; color: #000; margin-top: 2px;">
            Total: Rp {{ number_format($service->jasa, 0, ',', '.') }}
        </div>
    </div>

    @if($service->garansi)
    <div style="margin: 4px 0; padding: 3px 6px; background: #fff; border: 1px solid #000; font-size: 8px; color: #000; text-align: center;">
        <strong>Garansi:</strong> {{ $service->garansi }}
    </div>
    @endif

    @if($service->keterangan)
    <div style="margin: 4px 0; padding: 3px 6px; background: #f9f9f9; border-left: 2px solid #000; font-size: 8px; color: #000;">
        <strong>Catatan:</strong> {{ $service->keterangan }}
    </div>
    @endif

</div>
