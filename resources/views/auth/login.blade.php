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
        $subtitle = \App\Models\Setting::getValue('subtitle', 'Service Hp Android & iPhone');
    @endphp

    <link rel="icon" href="{{ $faviconUrl }}">
</head>
<body class="min-h-dvh bg-slate-950 font-sans text-slate-900 antialiased">
    <div class="fixed inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('bg-login.png') }}');"></div>
    <div class="fixed inset-0 bg-slate-950/75"></div>
    <div class="fixed inset-0 bg-gradient-to-br from-slate-950 via-slate-950/70 to-amber-950/40"></div>
    <div class="pointer-events-none fixed -right-40 -top-40 h-[28rem] w-[28rem] rounded-full bg-amber-500/10 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-40 -left-40 h-[28rem] w-[28rem] rounded-full bg-yellow-400/10 blur-3xl"></div>

    <main class="relative z-10 mx-auto flex min-h-dvh w-full max-w-6xl items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid w-full items-center gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16">
            {{-- Branding / desktop --}}
            <section class="hidden lg:block">
                <div class="max-w-lg">
                    <div class="mb-8 inline-flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-3 pr-6 backdrop-blur">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo {{ $namaToko }}"
                                class="h-16 w-16 rounded-xl bg-white object-contain p-1.5 shadow-lg shadow-black/20"
                                onerror="this.onerror=null;this.src='{{ asset('favicon.png') }}';">
                        @else
                            <span class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-yellow-300 to-amber-600 text-slate-950 shadow-lg shadow-black/20">
                                <i class="fas fa-mobile-screen-button text-2xl"></i>
                            </span>
                        @endif
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-300">Panel Administrasi</p>
                            <p class="mt-1 text-lg font-semibold text-white">{{ $namaToko }}</p>
                        </div>
                    </div>

                    <h1 class="text-4xl font-bold leading-tight tracking-tight text-white">
                        Kelola bisnis HP Anda dengan lebih rapi.
                    </h1>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-slate-300">
                        Service, stok sparepart, jual beli HP, dan laporan dalam satu dashboard yang mudah digunakan.
                    </p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                            <i class="fas fa-screwdriver-wrench text-sm text-amber-300"></i>
                            <p class="mt-2 text-sm font-semibold text-white">Service</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">Antrean & progres</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                            <i class="fas fa-boxes-stacked text-sm text-amber-300"></i>
                            <p class="mt-2 text-sm font-semibold text-white">Stok</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">Sparepart & HP</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                            <i class="fas fa-chart-line text-sm text-amber-300"></i>
                            <p class="mt-2 text-sm font-semibold text-white">Laporan</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">Omzet & profit</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Form login --}}
            <section class="mx-auto w-full max-w-md lg:mx-0 lg:justify-self-end">
                <div class="rounded-[1.75rem] border border-white/20 bg-white/95 p-6 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-8">
                    {{-- Brand mobile --}}
                    <div class="mb-6 flex items-center gap-3 lg:hidden">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo {{ $namaToko }}"
                                class="h-12 w-12 rounded-xl bg-white object-contain p-1 shadow-md ring-1 ring-amber-200"
                                onerror="this.onerror=null;this.src='{{ asset('favicon.png') }}';">
                        @else
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-yellow-300 to-amber-600 text-slate-950 shadow-md">
                                <i class="fas fa-mobile-screen-button"></i>
                            </span>
                        @endif
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $namaToko }}</p>
                            <p class="text-xs font-medium text-amber-700">{{ $subtitle }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-amber-600">Selamat datang</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Masuk ke akun</h2>
                        <p class="mt-1.5 text-sm text-slate-500">Gunakan email dan password yang terdaftar.</p>
                    </div>

                    @if(session('success'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak
                        x-transition:enter="transition-opacity duration-100"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="mb-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <i class="fas fa-check-circle mt-0.5 text-emerald-500"></i>
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
                        class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" x-data="{ loading: false, showPassword: false }"
                        @submit="loading = true" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                            <div class="relative">
                                <i class="fas fa-envelope pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                    autocomplete="email" placeholder="nama@email.com"
                                    class="h-11 w-full rounded-xl border bg-white pl-10 pr-3.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 @error('email') border-red-400 @else border-slate-300 @enderror">
                            </div>
                            @error('email')
                                <p class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-red-600">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                                <span class="text-[11px] font-medium text-slate-400">Hubungi admin jika lupa</span>
                            </div>
                            <div class="relative">
                                <i class="fas fa-lock pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password" required
                                    autocomplete="current-password" placeholder="Masukkan password"
                                    class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 @error('password') border-red-400 @enderror">
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-2.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
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

                        <label class="flex cursor-pointer select-none items-center gap-2.5 text-sm text-slate-600">
                            <input type="checkbox" name="remember"
                                class="h-4 w-4 rounded border-slate-300 accent-amber-500 focus:ring-2 focus:ring-amber-500/30">
                            <span>Ingat saya</span>
                        </label>

                        <button type="submit" :disabled="loading"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-yellow-500 to-amber-500 px-4 text-sm font-bold text-white shadow-lg shadow-amber-500/20 transition hover:from-yellow-400 hover:to-amber-400 hover:shadow-amber-500/30 focus:outline-none focus:ring-4 focus:ring-amber-500/30 active:translate-y-px disabled:cursor-not-allowed disabled:opacity-60">
                            <i x-show="!loading" class="fas fa-sign-in-alt"></i>
                            <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                            <span x-text="loading ? 'Memproses...' : 'Masuk'"></span>
                        </button>
                    </form>

                    <div class="my-5 flex items-center gap-3">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">atau</span>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>

                    <a href="{{ route('auth.google') }}"
                        class="flex h-11 w-full items-center justify-center gap-2.5 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50/60 focus:outline-none focus:ring-4 focus:ring-amber-500/20">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Masuk dengan Google</span>
                    </a>

                    <div class="mt-6 border-t border-slate-200 pt-4 text-center">
                        <p class="text-xs leading-relaxed text-slate-400">
                            &copy; {{ date('Y') }} {{ $namaToko }}<br>
                            Akses terbatas untuk pengguna yang berwenang.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
