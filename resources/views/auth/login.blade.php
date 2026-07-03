<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Juneo Phone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>[x-cloak] { display: none !important; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @php $favicon = \App\Models\Setting::getValue('logo'); @endphp
    @if($favicon)
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
    @else
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @endif
</head>
<body class="bg-black min-h-screen flex items-center justify-center px-4 py-6 md:py-10 relative overflow-y-auto">
    <div class="fixed inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(255,215,0,0.07)_0%,_transparent_70%)] pointer-events-none"></div>
    <div class="fixed inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(255,215,0,0.03)_0%,_transparent_50%)] pointer-events-none"></div>

    <div class="w-full max-w-sm relative z-10">
        <div class="bg-white rounded-2xl shadow-2xl shadow-black/40 p-5 relative overflow-hidden border border-white/10">
            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-400"></div>

            <div class="text-center mb-3">
                <div class="relative inline-block">
                    @php $logoSetting = \App\Models\Setting::getValue('logo'); @endphp
                    @if($logoSetting)
                        <img src="{{ asset('storage/' . $logoSetting) }}" alt="Logo" class="w-20 h-20 mx-auto rounded-full ring-4 ring-yellow-500/30 shadow-xl shadow-yellow-500/20 object-cover">
                    @else
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 mx-auto drop-shadow-lg rounded-full ring-4 ring-yellow-500/30 shadow-xl shadow-yellow-500/20 bg-black">
                            <defs>
                                <linearGradient id="goldGradL" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#FFD700"/>
                                    <stop offset="50%" stop-color="#F59E0B"/>
                                    <stop offset="100%" stop-color="#FFD700"/>
                                </linearGradient>
                            </defs>
                            <circle cx="100" cy="100" r="88" fill="none" stroke="url(#goldGradL)" stroke-width="3"/>
                            <circle cx="100" cy="100" r="82" fill="none" stroke="url(#goldGradL)" stroke-width="1" opacity="0.4"/>
                            <g transform="translate(100, 52)" fill="url(#goldGradL)">
                                <polygon points="0,-18 6,-8 12,-14 18,-4 24,-10 30,2 -30,2 -24,-10 -18,-4 -12,-14 -6,-8"/>
                                <rect x="-22" y="2" width="44" height="5" rx="2"/>
                            </g>
                            <path d="M 100 68 L 130 80 L 130 105 Q 130 130 100 142 Q 70 130 70 105 L 70 80 Z" fill="#111" stroke="url(#goldGradL)" stroke-width="1.5"/>
                            <path d="M 100 72 L 125 82 L 125 105 Q 125 126 100 137 Q 75 126 75 105 L 75 82 Z" fill="url(#goldGradL)" opacity="0.15"/>
                            <text x="100" y="110" font-size="26" font-weight="900" fill="url(#goldGradL)" font-family="sans-serif" text-anchor="middle" letter-spacing="1">JP</text>
                        </svg>
                    @endif
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-16 h-0.5 bg-gradient-to-r from-transparent via-yellow-500 to-transparent rounded-full"></div>
                </div>
                <h1 class="text-base font-bold mt-2 tracking-wider text-gray-900">{{ \App\Models\Setting::getValue('nama_toko', 'Juneo Phone') }}</h1>
                <p class="text-xs mt-0.5 text-yellow-600 font-medium">{{ \App\Models\Setting::getValue('subtitle', 'Service Hp Android & iPhone') }}</p>
            </div>

            @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak
                x-transition:enter="transition-opacity duration-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak
                x-transition:enter="transition-opacity duration-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2 shadow-sm">
                <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
            </div>
            @endif

            <h2 class="text-sm font-semibold text-gray-900 mb-3 text-center">Masuk ke Akun</h2>

            <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <div class="space-y-2.5">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-9 pr-3.5 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none  text-sm @error('email') border-red-400 ring-red-500/30 @enderror"
                                placeholder="admin@juneo.com">
                        </div>
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="password" name="password" required
                                class="w-full pl-9 pr-3.5 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 placeholder-gray-400 outline-none  text-sm"
                                placeholder="Masukkan password">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500/30">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white font-semibold py-2 px-3.5 rounded-xl  flex items-center justify-center gap-2 shadow-lg shadow-yellow-500/25 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i x-show="!loading" class="fas fa-sign-in-alt"></i>
                        <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                        <span x-text="loading ? 'Memproses...' : 'Masuk'"></span>
                    </button>
                </div>
            </form>

            <div class="relative my-3">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-gray-400 font-medium">atau</span>
                </div>
            </div>

            <a href="{{ route('auth.google') }}"
               class="w-full bg-white hover:bg-gray-50 text-gray-800 font-medium py-2 px-3.5 rounded-xl flex items-center justify-center gap-2 border border-gray-200 shadow-sm text-sm transition-all duration-200">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>Masuk dengan Google</span>
            </a>

            <div class="mt-3 text-center">
                <p class="text-xs text-gray-400">
                    Akses terbatas untuk pengguna yang berwenang.
                </p>
            </div>

            <div class="mt-3 pt-2 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} {{ \App\Models\Setting::getValue('nama_toko', 'Juneo Phone') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>