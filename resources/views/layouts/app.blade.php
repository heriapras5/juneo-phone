<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Juneo Phone') - Sistem Manajemen Service</title>
    @php $favicon = \App\Models\Setting::getValue('logo'); @endphp
    @if($favicon)
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/' . $favicon) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('storage/' . $favicon) }}">
    <link rel="shortcut icon" href="{{ asset('storage/' . $favicon) }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/' . $favicon) }}">
    @else
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    @endif
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
}" x-init="
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
                @php $logoSetting = \App\Models\Setting::getValue('logo'); @endphp
                <div :class="{ 'w-16 h-16': sidebarOpen, 'w-10 h-10': !sidebarOpen }" class="flex-shrink-0 overflow-hidden rounded-full ring-4 ring-yellow-500/30 shadow-2xl shadow-yellow-500/30 w-16 h-16 transition-all duration-150 ease-in-out" style="filter: drop-shadow(0 4px 8px rgba(255,215,0,0.25));">
                    @if($logoSetting)
                        <img src="{{ asset('storage/' . $logoSetting) }}" alt="Logo" width="64" height="64" class="w-full h-full object-cover">
                    @else
                    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                        <defs>
                            <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FFD700"/>
                                <stop offset="50%" stop-color="#FFA500"/>
                                <stop offset="100%" stop-color="#FFD700"/>
                            </linearGradient>
                            <linearGradient id="goldGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FFC107"/>
                                <stop offset="100%" stop-color="#FF8F00"/>
                            </linearGradient>
                            <path id="topArc" d="M 40 100 A 60 60 0 0 1 160 100" fill="none"/>
                            <path id="botArc" d="M 40 105 A 60 60 0 0 0 160 105" fill="none"/>
                        </defs>
                        <circle cx="100" cy="100" r="88" fill="none" stroke="url(#goldGrad)" stroke-width="3"/>
                        <circle cx="100" cy="100" r="82" fill="none" stroke="url(#goldGrad)" stroke-width="1" opacity="0.5"/>
                        <text font-size="12" font-weight="bold" fill="url(#goldGrad)" font-family="sans-serif" letter-spacing="2">
                            <textPath href="#topArc" startOffset="50%" text-anchor="middle">JUNEO PHONE</textPath>
                        </text>
                        <text font-size="5.5" fill="url(#goldGrad)" opacity="0.8" font-family="sans-serif" letter-spacing="1">
                            <textPath href="#botArc" startOffset="50%" text-anchor="middle">SERVICE HP | ANDROID &amp; iPHONE</textPath>
                        </text>
                        <g transform="translate(100, 52)" fill="url(#goldGrad2)">
                            <polygon points="0,-18 6,-8 12,-14 18,-4 24,-10 30,2 -30,2 -24,-10 -18,-4 -12,-14 -6,-8"/>
                            <rect x="-22" y="2" width="44" height="5" rx="2"/>
                        </g>
                        <path d="M 100 68 L 130 80 L 130 105 Q 130 130 100 142 Q 70 130 70 105 L 70 80 Z"
                            fill="#111" stroke="url(#goldGrad2)" stroke-width="1.5"/>
                        <path d="M 100 72 L 125 82 L 125 105 Q 125 126 100 137 Q 75 126 75 105 L 75 82 Z"
                            fill="url(#goldGrad)" opacity="0.15"/>
                        <text x="100" y="110" font-size="26" font-weight="900" fill="url(#goldGrad)"
                            font-family="sans-serif" text-anchor="middle" letter-spacing="1">JP</text>
                    </svg>
                    @endif
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
                @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('admin.index') }}"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('admin.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.*') ? 'bg-yellow-500/20' : '' }}">
                        <i class="fas fa-user-shield w-5 text-center {{ request()->routeIs('admin.*') ? 'text-yellow-400' : '' }}"></i>
                    </span>
                    <span class="sidebar-text">Manajemen Admin</span>
                </a>
                @endif
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
                @php $logoSetting = \App\Models\Setting::getValue('logo'); @endphp
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full ring-2 ring-yellow-500/30 shadow-xl shadow-yellow-500/20 bg-black flex items-center justify-center flex-shrink-0 overflow-hidden" style="filter: drop-shadow(0 2px 4px rgba(255,215,0,0.2));">
                        @if($logoSetting)
                            <img src="{{ asset('storage/' . $logoSetting) }}" alt="Logo" class="w-full h-full object-cover">
                        @else
                            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full"><defs><linearGradient id="mg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#FFD700"/><stop offset="100%" stop-color="#F59E0B"/></linearGradient></defs><circle cx="100" cy="100" r="92" fill="#111" stroke="url(#mg)" stroke-width="6"/><text x="100" y="138" font-size="85" font-weight="900" fill="url(#mg)" font-family="sans-serif" text-anchor="middle">JP</text></svg>
                        @endif
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
                <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">LAPORAN</div>

                <a href="{{ route('reports.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('reports.*') && !request()->routeIs('reports.import*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('reports.*') && !request()->routeIs('reports.import*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-file-alt w-5 text-center"></i></span> Laporan
                </a>
                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-[2px] px-4 pt-4 pb-1">MASTER DATA</div>
                @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('admin.index') }}" @click="sidebarMobileOpen = false"
                    class="flex items-center gap-3 pl-4 pr-3 py-2.5 text-sm {{ request()->routeIs('admin.*') ? 'bg-gradient-to-r from-yellow-500/15 to-amber-500/5 border-r-2 border-yellow-500 text-yellow-400' : 'text-gray-400 hover:bg-white/5 hover:text-yellow-400' }}">
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.*') ? 'bg-yellow-500/20' : '' }}"><i class="fas fa-user-shield w-5 text-center"></i></span> Manajemen Admin
                </a>
                @endif
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
                <div class="mx-auto w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <i class="fas fa-trash text-red-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-600 mb-6" x-text="confirmDelete?.message"></p>
                <div class="flex gap-3 justify-center">
                    <button @click="confirmDelete = null"
                        class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">
                        Batal
                    </button>
                    <form :action="confirmDelete?.url" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl">
                            Ya, Hapus
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
