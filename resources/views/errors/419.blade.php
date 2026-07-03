<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Berakhir - Juneo Phone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-white via-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center mx-auto mb-6 shadow-lg">
            <i class="fas fa-clock text-orange-600 text-3xl"></i>
        </div>
        <h1 class="text-6xl font-bold text-gray-900 mb-2">419</h1>
        <p class="text-lg text-gray-500 mb-6">Sesi Anda telah berakhir. Silakan login kembali.</p>
        <a href="{{ url('/login') }}"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-400 hover:to-amber-400 text-white font-medium rounded-xl shadow-lg shadow-orange-500/25">
            <i class="fas fa-sign-in-alt"></i> Login Kembali
        </a>
    </div>
</body>
</html>
