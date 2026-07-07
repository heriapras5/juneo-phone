<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Service - {{ $namaToko }}</title>
    <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { width:100%; height:100%; overflow:hidden; background:#f3f4f6; }
        embed { width:100%; height:100%; border:none; display:block; }
    </style>
</head>
<body>
    <embed src="{{ $pdfUrl }}" type="application/pdf">
</body>
</html>
