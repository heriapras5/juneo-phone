<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Service - {{ $service->service_number }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #000; margin: 0; padding: 0; }
        .nota-content h1 { font-family: 'Helvetica', 'Arial', sans-serif !important; font-weight: bold !important; color: #000 !important; }
    </style>
</head>
<body>
    @include('exports.partials.nota-content')
</body>
</html>
