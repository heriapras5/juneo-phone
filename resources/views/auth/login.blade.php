<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ \App\Models\Setting::getValue('nama_toko', 'Juneo Phone') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @php
        $logoSetting = \App\Models\Setting::getValue('logo');
        $logoExists = $logoSetting && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoSetting);
        $logoUrl = $logoExists ? route('storage.file', $logoSetting) : null;
        $faviconUrl = $logoUrl ?: asset('favicon.png');
        $namaToko = \App\Models\Setting::getValue('nama_toko', 'Juneo Phone');
    @endphp

    <link rel="icon" href="{{ $faviconUrl }}">

    <style>
        @keyframes loginLogoSpin {
            0%, 8% { transform: perspective(700px) rotateY(0deg); }
            45%, 62% { transform: perspective(700px) rotateY(360deg); }
            100% { transform: perspective(700px) rotateY(360deg); }
        }
        .login-logo-spin {
            animation: loginLogoSpin 7s cubic-bezier(.65,.05,.36,1) infinite;
            transform-style: preserve-3d;
        }
        .login-logo {
            filter: drop-shadow(0 12px 16px rgba(0, 0, 0, 0.5)) drop-shadow(0 0 18px rgba(245, 158, 11, 0.55));
            transition: filter 0.3s ease;
        }
        .login-logo:hover,
        .login-logo-wrap:hover .login-logo {
            filter: drop-shadow(0 16px 22px rgba(0, 0, 0, 0.55)) drop-shadow(0 0 28px rgba(245, 158, 11, 0.85));
        }
        .login-border {
            position: relative;
            overflow: hidden;
        }
        .login-border::before {
            content: '';
            position: absolute;
            inset: -150%;
            background: conic-gradient(
                from 0deg,
                rgba(253, 224, 71, 0) 0deg,
                rgba(253, 224, 71, 0.95) 40deg,
                rgba(245, 158, 11, 0.7) 90deg,
                rgba(251, 191, 36, 0) 150deg,
                rgba(251, 191, 36, 0) 360deg
            );
            animation: loginBorderSpin 5s linear infinite;
        }
        .login-card-inner {
            position: relative;
            z-index: 1;
        }
        @keyframes loginBorderSpin {
            to { transform: rotate(360deg); }
        }
        @media (prefers-reduced-motion: reduce) {
            .login-logo-spin,
            .login-border::before {
                animation: none;
            }
        }
    </style>
</head>
<body class="min-h-dvh bg-slate-950 font-sans text-slate-900 antialiased">
    <div class="fixed inset-0 bg-cover bg-center bg-no-repeat brightness-110 saturate-110" style="background-image: url('{{ asset('bg-login.png') }}');"></div>
    <div class="fixed inset-0 bg-slate-950/35"></div>
    <div class="fixed inset-0 bg-[radial-gradient(ellipse_at_center,_transparent_20%,_rgba(2,6,23,0.70)_100%)]"></div>
    <div class="fixed inset-0 bg-gradient-to-br from-slate-950/40 via-transparent to-amber-950/30"></div>
    <div class="pointer-events-none fixed -right-40 -top-40 h-[26rem] w-[26rem] rounded-full bg-amber-500/10 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-40 -left-40 h-[26rem] w-[26rem] rounded-full bg-yellow-400/10 blur-3xl"></div>

    <main class="relative z-10 flex min-h-dvh w-full items-center justify-center px-4 py-8 sm:px-6">
        <div class="w-full max-w-[22rem] sm:max-w-[24rem]">
            <div class="login-border group rounded-[1.65rem] bg-gradient-to-br from-yellow-300 via-amber-500 to-yellow-300 p-[2px] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.65),0_0_50px_-20px_rgba(245,158,11,0.55)] transition-shadow duration-300 hover:shadow-[0_30px_70px_-15px_rgba(0,0,0,0.7),0_0_65px_-18px_rgba(245,158,11,0.75)] focus-within:shadow-[0_30px_70px_-15px_rgba(0,0,0,0.7),0_0_65px_-18px_rgba(245,158,11,0.75)]">
                <div class="login-card-inner rounded-[1.55rem] bg-white p-5 sm:p-6">
                    {{-- Logo --}}
                    <div class="mb-4 flex justify-center">
                        @if($logoUrl)
                            <div class="login-logo-wrap relative rounded-full transition-all duration-300 hover:-translate-y-1 hover:scale-105">
                                <div class="pointer-events-none absolute -inset-2 rounded-full bg-amber-400/20 blur-xl"></div>
                                <img src="{{ $logoUrl }}" alt="Logo {{ $namaToko }}"
                                    class="login-logo login-logo-spin relative h-20 w-20 rounded-full object-cover sm:h-24 sm:w-24"
                                    onerror="this.onerror=null;this.src='{{ asset('favicon.png') }}';">
                            </div>
                        @else
                            <div class="login-logo-wrap relative rounded-full transition-all duration-300 hover:-translate-y-1 hover:scale-105">
                                <div class="pointer-events-none absolute -inset-2 rounded-full bg-amber-400/20 blur-xl"></div>
                                <span class="login-logo login-logo-spin relative flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-yellow-300 to-amber-600 text-slate-950 sm:h-24 sm:w-24">
                                    <i class="fas fa-mobile-screen-button text-xl"></i>
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="mb-5 text-center">
                        <h1 class="text-xl font-bold tracking-tight text-slate-900">Masuk ke akun</h1>
                        <p class="mt-1 text-sm text-slate-600">Gunakan email dan password yang terdaftar.</p>
                    </div>

                @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak
                    x-transition:enter="transition-opacity duration-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mb-4 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <i class="fas fa-check-circle mt-0.5 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
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
                    class="mb-4 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <i class="fas fa-exclamation-circle mt-0.5 text-red-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" x-data="{ loading: false, showPassword: false }"
                    @submit="loading = true" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-800">Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="email" placeholder="nama@email.com"
                                class="h-10 w-full rounded-xl border bg-white pl-10 pr-3.5 text-sm text-slate-900 placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 @error('email') border-red-400 @else border-slate-300 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-red-600">
                                <i class="fas fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <div class="mb-1.5">
                            <label for="password" class="block text-sm font-semibold text-slate-800">Password</label>
                        </div>
                        <div class="relative">
                            <i class="fas fa-lock pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>
                            <input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password" required
                                autocomplete="current-password" placeholder="Masukkan password"
                                class="h-10 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-11 text-sm text-slate-900 placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 @error('password') border-red-400 @enderror">
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-2.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                <i class="fas text-sm" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-red-600">
                                <i class="fas fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer select-none items-center gap-2.5 text-sm text-slate-700">
                        <input type="checkbox" name="remember"
                            class="h-4 w-4 rounded border-slate-300 accent-amber-500 focus:ring-2 focus:ring-amber-500/30">
                        <span>Ingat saya</span>
                    </label>

                    <button type="submit" :disabled="loading"
                        class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-yellow-400 to-amber-500 px-4 text-sm font-bold text-slate-950 shadow-lg shadow-amber-500/20 transition hover:from-yellow-300 hover:to-amber-400 hover:shadow-amber-500/30 focus:outline-none focus:ring-4 focus:ring-amber-500/30 active:translate-y-px disabled:cursor-not-allowed disabled:opacity-60">
                        <i x-show="!loading" class="fas fa-sign-in-alt"></i>
                        <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                        <span x-text="loading ? 'Memproses...' : 'Masuk'"></span>
                    </button>
                </form>

                <div class="my-4 flex items-center gap-3">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">atau</span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <a href="{{ route('auth.google') }}"
                    class="flex h-10 w-full items-center justify-center gap-2.5 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-amber-400 hover:bg-amber-50 focus:outline-none focus:ring-4 focus:ring-amber-500/20">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Masuk dengan Google</span>
                </a>

                <div class="mt-5 border-t border-slate-200 pt-3 text-center">
                    <p class="text-xs leading-relaxed text-slate-500">
                        &copy; {{ date('Y') }} {{ $namaToko }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    </main>
</body>
</html>
