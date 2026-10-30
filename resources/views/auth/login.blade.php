<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Masuk - ITAM Asset Control</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-slate-100 bg-slate-950 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-hidden">
    <!-- Ambient Background Lighting -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
        <!-- Application Showcase & Value Proposition (Left Column) -->
        <div class="hidden lg:flex lg:col-span-6 flex-col justify-between space-y-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                    IT Asset Management System v1.0
                </div>

                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white font-extrabold text-xl shadow-lg shadow-indigo-500/25">
                        IT
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-white tracking-tight">ITAM Platform</h1>
                        <p class="text-xs text-indigo-300 font-medium">Enterprise Asset Control & Lifecycle</p>
                    </div>
                </div>

                <p class="text-sm text-slate-300 leading-relaxed">
                    Sistem terpadu tata kelola inventaris teknologi informasi korporasi. Kendalikan perangkat keras, lisensi software, riwayat pemeliharaan, serta depresiasi aset secara akurat dan real-time.
                </p>
            </div>

            <!-- Key System Features Cards -->
            <div class="space-y-3">
                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-start gap-3.5">
                    <div class="p-2 rounded-lg bg-indigo-500/10 text-indigo-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white">Pelacakan Siklus Hidup Aset</div>
                        <div class="text-[11px] text-slate-400">Monitoring pengadaan, penempatan unit, audit rutin, hingga disposal secara transparan.</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-start gap-3.5">
                    <div class="p-2 rounded-lg bg-blue-500/10 text-blue-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white">Keamanan & Audit Kepatuhan</div>
                        <div class="text-[11px] text-slate-400">Pemberian akses berbasis peran, pelaporan otomatis, dan perlindungan data terstandar OWASP.</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm flex items-start gap-3.5">
                    <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white">Analitik & Nilai Depresiasi</div>
                        <div class="text-[11px] text-slate-400">Kalkulasi nilai buku aset, peramalan anggaran IT, dan efisiensi biaya operasional.</div>
                    </div>
                </div>
            </div>

            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                <span>Versi Stabil 1.0.0</span>
                <span>&bull;</span>
                <span>Multi-Site & Multi-Category</span>
            </div>
        </div>

        <!-- Auth Card (Right Column on Desktop, Centered on Mobile) -->
        <div class="lg:col-span-6 w-full max-w-md mx-auto">
            <!-- Mobile App Brand Header -->
            <div class="text-center mb-6 lg:hidden">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white font-black text-xl shadow-xl shadow-indigo-500/20 ring-1 ring-white/10 mb-3">
                    IT
                </div>
                <h1 class="text-xl font-extrabold text-white tracking-tight">IT Asset Management</h1>
                <p class="text-xs text-slate-400 mt-1">Enterprise Hardware, Software & License Control</p>
            </div>

            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/50">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-white">Masuk ke Portal</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Gunakan kredensial akun untuk mengakses sistem pengelolaan aset.</p>
                </div>

                <!-- Session Status / Errors -->
                @if (session('success'))
                    <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800/60 text-emerald-300 text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-950/60 border border-rose-800/60 text-rose-300 text-xs space-y-1">
                        <div class="font-semibold flex items-center gap-1.5 text-rose-200">
                            <svg class="w-4 h-4 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            Terjadi kesalahan validasi:
                        </div>
                        <ul class="list-disc list-inside pl-1 text-[11px] text-rose-300/90 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                required 
                                autofocus 
                                value="{{ old('email') }}" 
                                placeholder="nama@instansi.local"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                            >
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div x-data="{ show: false }">
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input 
                                :type="show ? 'text' : 'password'" 
                                name="password" 
                                id="password" 
                                required 
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                            >
                            <button 
                                type="button" 
                                @click="show = !show" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300"
                                tabindex="-1"
                                aria-label="Lihat kata sandi"
                            >
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Info -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember"
                                class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900"
                            >
                            <span class="text-xs text-slate-400">Ingat sesi saya</span>
                        </label>

                        <span class="text-[11px] text-slate-500">OWASP Protected</span>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition flex items-center justify-center gap-2"
                        >
                            <span>Masuk ke Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer Note -->
            <div class="mt-6 text-center text-xs text-slate-500 space-y-1">
                <p>Sistem dilindungi pembatasan frekuensi (Rate Limiting) & Proteksi Sesi.</p>
                <p class="text-[11px] text-slate-600">IT Asset Management Platform &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
