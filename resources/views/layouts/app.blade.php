<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'IT Asset Management') - ITAM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 antialiased bg-slate-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex">
        <!-- Mobile Sidebar Backdrop -->
        <div 
            x-show="sidebarOpen" 
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden transition-opacity"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        <!-- Sidebar (Desktop static & Mobile drawer) -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 flex flex-col shrink-0 border-r border-slate-800 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0">
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-md shadow-indigo-600/30 overflow-hidden shrink-0">
                        @if (isset($companyProfile) && $companyProfile?->logo_url)
                            <img src="{{ $companyProfile->logo_url }}" alt="Logo" class="w-full h-full object-contain p-1 bg-white">
                        @else
                            IT
                        @endif
                    </div>
                    <div class="truncate">
                        <span class="font-bold text-white tracking-wide block text-sm truncate">{{ $companyProfile?->name ?? 'ITAM SYSTEM' }}</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest block font-semibold truncate">{{ $companyProfile?->city ?? 'Asset Control' }}</span>
                    </div>
                </div>
                <!-- Close Button on Mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto text-sm">
                <a href="{{ route('web.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <div class="pt-4 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Manajemen Aset</div>

                <a href="{{ route('web.assets.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.assets.index', 'web.assets.show', 'web.assets.edit') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Katalog Perangkat
                </a>

                <a href="{{ route('web.assets.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.assets.create') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Registrasi Aset Baru
                </a>

                <a href="{{ route('web.assets.scan') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.assets.scan') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Scan QR Code
                </a>

                <div class="pt-4 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Sirkulasi & Operasional</div>

                <a href="{{ route('web.assignments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.assignments.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Peminjaman / Check-out
                </a>

                <a href="{{ route('web.mutations.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.mutations.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Mutasi Antar Lokasi
                </a>

                <a href="{{ route('web.maintenances.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.maintenances.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Servis & Maintenance
                </a>

                <a href="{{ route('web.disposals.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.disposals.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Disposal & Pemusnahan
                </a>

                <div class="pt-4 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Laporan & Analitik</div>

                <a href="{{ route('web.reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.reports.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Laporan Inventaris
                </a>

                <div class="pt-4 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Master & RBAC</div>

                <a href="{{ route('web.locations.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.locations.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Lokasi Cabang
                </a>

                <a href="{{ route('web.categories.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.categories.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Kategori Aset
                </a>

                <a href="{{ route('web.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.users.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Pengguna & Role
                </a>

                <div class="pt-4 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Konfigurasi Sistem</div>

                <a href="{{ route('web.company.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('web.company.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Profil Perusahaan
                </a>
            </nav>

            <div class="p-3 border-t border-slate-800 bg-slate-900/40">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center font-bold text-xs text-indigo-300 shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'IT', 0, 2)) }}
                        </div>
                        <div class="text-xs truncate">
                            <span class="block text-slate-200 font-semibold truncate">{{ auth()->user()->name ?? 'Pengguna' }}</span>
                            <span class="block text-slate-400 text-[11px] truncate">{{ auth()->user()->email ?? 'user@itam.local' }}</span>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="shrink-0" onsubmit="return confirm('Keluar dari sesi aplikasi ITAM?');">
                        @csrf
                        <button type="submit" title="Keluar / Logout" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition" aria-label="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Navbar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Mobile Hamburger Button -->
                    <button 
                        @click="sidebarOpen = true" 
                        class="lg:hidden p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg focus:outline-hidden"
                        aria-label="Buka menu navigasi"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 truncate">@yield('header', $header ?? 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    <a href="{{ route('web.assets.scan') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition border border-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span class="hidden sm:inline">Scan Aset</span>
                        <span class="sm:hidden">Scan</span>
                    </a>
                    <a href="{{ route('web.assets.create') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="hidden sm:inline">Tambah Aset</span>
                        <span class="sm:hidden">+ Aset</span>
                    </a>
                </div>
            </header>

            <!-- Alerts / Notifications -->
            <div class="px-4 sm:px-6 lg:px-8 pt-6">
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span class="font-medium text-xs sm:text-sm">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span class="font-medium text-xs sm:text-sm">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="px-4 sm:px-6 lg:px-8 pb-12 flex-1">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
