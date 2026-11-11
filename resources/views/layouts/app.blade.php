<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Juneo Phone') - Sistem Manajemen Service</title>
    @php
        $faviconPath = public_path('favicon.png');
        $faviconUrl = asset('favicon.png') . '?v=' . (file_exists($faviconPath) ? filemtime($faviconPath) : time());
        $faviconIcoPath = public_path('favicon.ico');
        $faviconIcoUrl = asset('favicon.ico') . '?v=' . (file_exists($faviconIcoPath) ? filemtime($faviconIcoPath) : time());
        $brandLogoSetting = \App\Models\Setting::getValue('logo');
        $brandDisplayPath = public_path('logo-display.png');
        $brandLogoDisplayUrl = file_exists($brandDisplayPath)
            ? asset('logo-display.png') . '?v=' . filemtime($brandDisplayPath)
            : ($brandLogoSetting ? route('storage.file', $brandLogoSetting) : null);
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $faviconIcoUrl }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .sidebar-desktop nav a {
            position: relative;
            transition: background-color 0.12s ease, color 0.12s ease;
        }
        .sidebar-desktop nav a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: linear-gradient(to bottom, #F59E0B, #D97706);
            border-radius: 0 3px 3px 0;
            transition: height 0.12s ease;
            opacity: 0;
        }
        .sidebar-desktop nav a:not(.sidebar-no-hover):hover::before {
            height: 50%;
            opacity: 1;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{
    sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
    sidebarMobileOpen: false,
    userMenuOpen: false,
    confirmDelete: null,
    confirmLogout: false,
    scrollLock: 0,
    pageLoaded: false,
    setScrollLock(v) { this.scrollLock = Math.max(0, this.scrollLock + (v ? 1 : -1)); if (this.scrollLock > 0) { document.body.style.overflow = 'hidden'; } else { document.body.style.overflow = ''; } }
}"
x-init="
    $watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
    $watch('sidebarMobileOpen', (val, old) => { if (val !== old) setScrollLock(val); });
    $watch('confirmDelete', (val, old) => { if (!!val !== !!old) setScrollLock(!!val); });
    $watch('confirmLogout', (val, old) => { if (val !== old) setScrollLock(val); });
    pageLoaded = true;
">

    <div class="flex h-screen">

        {{-- Mobile Overlay --}}
        <div x-show="sidebarMobileOpen" x-cloak
            x-transition:enter="transition-opacity duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-75"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 bg-black/60 md:hidden"
            @click="sidebarMobileOpen = false">
        </div>

        {{-- SIDEBAR DESKTOP --}}
        <aside class="sidebar-desktop hidden md:flex flex-col fixed inset-y-0 left-0 z-40 bg-gradient-to-b from-gray-900 via-gray-950 to-black text-white border-r border-gray-800/50 w-20 overflow-hidden"
            :class="{ 'sidebar-collapsed': !sidebarOpen }">

            {{-- Logo Area --}}
            <a href="{{ route('dashboard') }}"
                class="flex-shrink-0 flex justify-center flex-row items-center gap-3 px-3 pt-5 pb-5">
                <div :class="{ 'w-16 h-16': sidebarOpen, 'w-10 h-10': !sidebarOpen }" class="brand-logo-wrap relative flex-shrink-0 rounded-full w-16 h-16 transition-all duration-150 ease-in-out">
                    <div class="brand-logo-scene relative h-full w-full">
                        <div class="pointer-events-none absolute -bottom-2 left-1/2 z-0 h-2 w-3/4 -translate-x-1/2 rounded-full bg-black/50 blur-md"></div>
                        @if($brandLogoDisplayUrl)
                            <img src="{{ $brandLogoDisplayUrl }}" alt="Logo" width="64" height="64" decoding="async"
                                class="absolute inset-0 z-10 h-full w-full rounded-full object-cover"
                                onerror="this.onerror=null;this.src='{{ asset('favicon.png') }}';">
                        @else
                            <span class="absolute inset-0 z-10 flex items-center justify-center rounded-full bg-gradient-to-br from-yellow-300 to-amber-600 text-slate-950">
                                <i class="fas fa-mobile-screen-button"></i>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="sidebar-text flex-1 min-w-0">
                    <h1 class="text-sm font-bold tracking-wider truncate" style="color:#FFD700; text-shadow: 0 2px 8px rgba(255,215,0,0.35), 0 1px 2px rgba(0,0,0,0.5);">{{ \App\Models\Setting::getValue('nama_toko', 'Juneo Phone') }}</h1>
                    <p class="text-[10px] tracking-wide truncate" style="color:#FFD700; opacity:0.85; text-shadow: 0 1px 4px rgba(255,215,0,0.25), 0 1px 1px rgba(0,0,0,0.4);">Service HP | Android & iPhone</p>
                </div>
            </a>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-2 px-0 space-y-0.5">

                {{-- Section: MAIN --}}
                <div class="sidebar-text text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">MENU UTAMA</div>

                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('dashboard') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-chart-pie w-5 text-center {{ request()->routeIs('dashboard') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <a href="{{ route('customers.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('customers.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('customers.*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-users w-5 text-center {{ request()->routeIs('customers.*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Pelanggan</span>
                </a>

                <a href="{{ route('services.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('services.*') && !request()->routeIs('services.import*') && !request()->routeIs('reports.import*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('services.*') && !request()->routeIs('services.import*') && !request()->routeIs('reports.import*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-tools w-5 text-center {{ request()->routeIs('services.*') && !request()->routeIs('services.import*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Service</span>
                </a>

                <a href="{{ route('biaya-harian.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('biaya-harian.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('biaya-harian.*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-wallet w-5 text-center {{ request()->routeIs('biaya-harian.*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Kebutuhan Pribadi</span>
                </a>

                {{-- Section: INVENTARIS --}}
                <div class="sidebar-text text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">STOK BARANG</div>

                <a href="{{ route('spareparts.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('spareparts.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('spareparts.*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-boxes w-5 text-center {{ request()->routeIs('spareparts.*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Sparepart</span>
                </a>

                {{-- Section: JUAL BELI HP (satu grup dengan stok barang) --}}
                <a href="{{ route('phone-trades.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('phone-trades.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('phone-trades.*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-mobile-screen-button w-5 text-center {{ request()->routeIs('phone-trades.*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Jual Beli HP</span>
                </a>

                {{-- Section: REPORTS --}}
                <div class="sidebar-text text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">LAPORAN</div>

                <a href="{{ route('reports.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('reports.*') && !request()->routeIs('reports.import*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('reports.*') && !request()->routeIs('reports.import*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-file-alt w-5 text-center {{ request()->routeIs('reports.*') && !request()->routeIs('reports.import*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Laporan</span>
                </a>

                {{-- Section: ADMIN --}}
                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <div class="sidebar-text text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">MASTER DATA</div>
                <a href="{{ route('admin.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('admin.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-user-shield w-5 text-center {{ request()->routeIs('admin.*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Manajemen Users</span>
                </a>
                <a href="{{ route('settings') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('settings') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('settings') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-cog w-5 text-center {{ request()->routeIs('settings') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Pengaturan</span>
                </a>

                {{-- Section: DATA MANAGEMENT --}}
                <div class="sidebar-text text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">IMPORT, BACKUP & RESTORE</div>

                <a href="{{ route('reports.import') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('reports.import*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('reports.import*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-upload w-5 text-center {{ request()->routeIs('reports.import*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Import Data</span>
                </a>

                <a href="{{ route('data.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('data.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('data.*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-database w-5 text-center {{ request()->routeIs('data.*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Backup & Restore</span>
                </a>
                @endif
            </nav>

        </aside>

        {{-- SIDEBAR MOBILE --}}
        <aside x-show="sidebarMobileOpen" x-cloak
            x-transition:enter="transition-opacity duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-75"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="md:hidden fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-gray-900 via-gray-950 to-black text-white flex flex-col border-r border-gray-800/50">

            <div class="flex-shrink-0 flex items-center justify-between h-16 px-4 border-b border-gray-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="brand-logo-wrap relative w-9 h-9 rounded-full flex-shrink-0">
                        <div class="brand-logo-scene relative h-full w-full">
                            <div class="pointer-events-none absolute -bottom-1.5 left-1/2 z-0 h-1.5 w-3/4 -translate-x-1/2 rounded-full bg-black/50 blur-sm"></div>
                            @if($brandLogoDisplayUrl)
                                <img src="{{ $brandLogoDisplayUrl }}" alt="Logo" decoding="async"
                                    class="absolute inset-0 z-10 h-full w-full rounded-full object-cover"
                                    onerror="this.onerror=null;this.src='{{ asset('favicon.png') }}';">
                            @else
                                <span class="absolute inset-0 z-10 flex items-center justify-center rounded-full bg-gradient-to-br from-yellow-300 to-amber-600 text-slate-950">
                                    <i class="fas fa-mobile-screen-button text-xs"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="font-bold text-sm tracking-wider" style="color:#FFD700; text-shadow: 0 2px 6px rgba(255,215,0,0.3), 0 1px 2px rgba(0,0,0,0.5);">{{ \App\Models\Setting::getValue('nama_toko', 'Juneo Phone') }}</span>
                        <p class="text-[9px] tracking-wide leading-none mt-0.5" style="color:#FFD700; opacity:0.8; text-shadow: 0 1px 3px rgba(255,215,0,0.2);">Service HP | Android & iPhone</p>
                    </div>
                </a>
                <button @click="sidebarMobileOpen = false" title="Tutup" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-0 space-y-0.5">

                <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pb-1">MENU UTAMA</div>

                <a href="{{ route('dashboard') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('dashboard') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-chart-pie w-5 text-center"></i></span> Dashboard
                </a>
                <a href="{{ route('customers.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('customers.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('customers.*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-users w-5 text-center"></i></span> Pelanggan
                </a>
                <a href="{{ route('services.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('services.*') && !request()->routeIs('services.import*') && !request()->routeIs('reports.import*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('services.*') && !request()->routeIs('services.import*') && !request()->routeIs('reports.import*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-tools w-5 text-center"></i></span> Service
                </a>
                <a href="{{ route('biaya-harian.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('biaya-harian.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('biaya-harian.*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-wallet w-5 text-center"></i></span> Kebutuhan Pribadi
                </a>
                <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">STOK BARANG</div>
                <a href="{{ route('spareparts.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('spareparts.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('spareparts.*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-boxes w-5 text-center"></i></span> Sparepart
                </a>
                <a href="{{ route('phone-trades.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('phone-trades.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('phone-trades.*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-mobile-screen-button w-5 text-center"></i></span> Jual Beli HP
                </a>
                <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">LAPORAN</div>

                <a href="{{ route('reports.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('reports.*') && !request()->routeIs('reports.import*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('reports.*') && !request()->routeIs('reports.import*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-file-alt w-5 text-center"></i></span> Laporan
                </a>
                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">MASTER DATA</div>
                <a href="{{ route('admin.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('admin.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-user-shield w-5 text-center"></i></span> Manajemen Users
                </a>
                <a href="{{ route('settings') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('settings') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('settings') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-cog w-5 text-center"></i></span> Pengaturan
                </a>
                <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">PENGELOLAAN DATA</div>

                <a href="{{ route('reports.import') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('reports.import*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('reports.import*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-upload w-5 text-center"></i></span> Import Data
                </a>

                <a href="{{ route('data.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('data.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('data.*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-database w-5 text-center"></i></span> Backup & Restore
                </a>
                @endif
            </nav>

        </aside>

        {{-- Main Content Area --}}
        <div class="main-content-wrap flex-1 flex flex-col overflow-hidden">

            {{-- Header --}}
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <button @click="window.innerWidth < 768 ? sidebarMobileOpen = !sidebarMobileOpen : sidebarOpen = !sidebarOpen"
                        class="text-gray-500 hover:text-yellow-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-base lg:text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span x-show="!sidebarOpen" x-cloak style="color:#D97706;" class="font-bold">JP</span>
                        <span x-show="sidebarOpen" x-cloak>@yield('page-title', 'Dashboard')</span>
                        <span x-show="!sidebarOpen" x-cloak>@yield('page-title', 'Dashboard')</span>
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative" @click.outside="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2 text-sm text-gray-700 hover:text-yellow-600">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-gradient-to-br from-yellow-500 to-amber-600 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                @if(auth()->user()->photo_url)
                                    <img src="{{ auth()->user()->photo_url }}?v={{ time() }}" alt="Foto" class="w-full h-full object-cover">
                                @else
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                @endif
                            </div>
                            <span class="hidden sm:block font-medium">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>

                        <div x-show="userMenuOpen" x-cloak
                            x-transition:enter="transition-opacity duration-100"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity duration-75"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-xl py-1 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-yellow-100 to-amber-100 flex-shrink-0">
                                    @if(auth()->user()->photo_url)
                                        <img src="{{ auth()->user()->photo_url }}?v={{ time() }}" alt="Foto" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-sm font-bold text-yellow-600">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-yellow-600 capitalize">{{ auth()->user()->role }}</p>
                                </div>
                            </div>
                            <a href="{{ route('profile.edit') }}" @click="userMenuOpen = false"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-yellow-50 flex items-center gap-2">
                                <i class="fas fa-user-circle text-yellow-600"></i> Profil
                            </a>
                            <button @click="confirmLogout = true; userMenuOpen = false"
                                class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6 bg-gray-50">
                {{-- Flash Messages --}}
                @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak
                    x-transition:enter="transition-opacity duration-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2 shadow-sm">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" title="Tutup" class="ml-auto text-emerald-500 hover:text-emerald-700">
                        <i class="fas fa-times"></i>
                    </button>
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
                    class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2 shadow-sm">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" title="Tutup" class="ml-auto text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endif

                @if(session('warning'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak
                    x-transition:enter="transition-opacity duration-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl flex items-center gap-2 shadow-sm">
                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    <span>{{ session('warning') }}</span>
                    <button @click="show = false" title="Tutup" class="ml-auto text-yellow-500 hover:text-yellow-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endif

                <div x-show="pageLoaded" x-cloak>
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Confirm Delete Modal --}}
    <div x-show="confirmDelete" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200">
            <div class="text-center">
                <div class="flex items-center justify-end mb-2">
                    <button @click="confirmDelete = null" title="Tutup" class="text-gray-400 hover:text-gray-600 -mr-2"><i class="fas fa-times"></i></button>
                </div>
                <div class="mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-4"
                    :class="confirmDelete?.method === 'DELETE' || !confirmDelete?.method ? 'bg-red-100' : 'bg-blue-100'">
                    <i class="text-xl"
                        :class="confirmDelete?.method === 'DELETE' || !confirmDelete?.method ? 'fas fa-trash text-red-500' : (confirmDelete?.icon || 'fas fa-save text-blue-500')"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2" x-text="confirmDelete?.title || 'Konfirmasi Hapus'"></h3>
                <p class="text-sm text-gray-600 mb-6" x-text="confirmDelete?.message"></p>
                <div class="flex gap-3 justify-center">
                    <button @click="confirmDelete = null"
                        class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">
                        Batal
                    </button>
                    <form :action="confirmDelete?.url" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="confirmDelete?.method || 'DELETE'">
                        <button type="submit"
                            class="px-5 py-2 text-sm font-medium text-white rounded-xl"
                            :class="confirmDelete?.method === 'DELETE' || !confirmDelete?.method ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'">
                            <span x-text="confirmDelete?.confirmText || 'Ya, Hapus'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm Logout Modal --}}
    <div x-show="confirmLogout" x-cloak
        x-transition:enter="transition-opacity duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200">
            <div class="text-center">
                <div class="flex items-center justify-end mb-2">
                    <button @click="confirmLogout = false" title="Tutup" class="text-gray-400 hover:text-gray-600 -mr-2"><i class="fas fa-times"></i></button>
                </div>
                <div class="mx-auto w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center mb-4">
                    <i class="fas fa-sign-out-alt text-yellow-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Logout</h3>
                <p class="text-sm text-gray-600 mb-6">Yakin ingin keluar dari aplikasi?</p>
                <div class="flex gap-3 justify-center">
                    <button @click="confirmLogout = false"
                        class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 rounded-xl shadow-lg shadow-yellow-500/25">
                            Ya, Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
