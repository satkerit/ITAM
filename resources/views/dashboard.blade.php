@extends('layouts.app')

@section('title', 'Executive Dashboard')
@section('header', 'Executive Dashboard & Overview')

@section('content')
    <!-- Company Profile Summary Card -->
    @if (isset($companyProfile))
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-5 sm:p-6 mb-6 text-white border border-slate-700/80 shadow-md">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center shrink-0 overflow-hidden shadow-inner">
                        @if ($companyProfile->logo_url)
                            <img src="{{ $companyProfile->logo_url }}" alt="Logo" class="w-full h-full object-contain p-2">
                        @else
                            <span class="text-2xl font-black text-indigo-300">IT</span>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg sm:text-xl font-bold tracking-tight text-white">{{ $companyProfile->name }}</h2>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Pusat IT</span>
                        </div>
                        <p class="text-xs text-slate-300 mt-1 max-w-2xl line-clamp-2">{{ $companyProfile->tagline ?? $companyProfile->description }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs text-slate-300 bg-slate-950/40 px-4 py-3 rounded-xl border border-white/10">
                    @if ($companyProfile->phone)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $companyProfile->phone }}</span>
                        </div>
                    @endif
                    @if ($companyProfile->mobile)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>{{ $companyProfile->mobile }}</span>
                        </div>
                    @endif
                    @if ($companyProfile->city)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $companyProfile->city }}</span>
                        </div>
                    @endif
                    <a href="{{ route('web.company.edit') }}" class="text-indigo-300 hover:text-white font-medium hover:underline text-xs ml-auto">Ubah Profil &rarr;</a>
                </div>
            </div>
        </div>
    @endif

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Perangkat</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-900">{{ number_format($totalAssets) }}</div>
            <p class="text-xs text-slate-400 mt-2">Seluruh aset tercatat di database</p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Nilai Akuisisi</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</div>
            <p class="text-xs text-slate-400 mt-2">Investasi hardware aktif</p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Aset Tersedia (Pool)</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-blue-600">{{ $statusCounts['available'] ?? 0 }}</div>
            <p class="text-xs text-slate-400 mt-2">Siap untuk dipinjamkan (check-out)</p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Biaya Servis</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($totalMaintenanceCost, 0, ',', '.') }}</div>
            <p class="text-xs text-slate-400 mt-2">Kumulatif perbaikan & vendor</p>
        </div>
    </div>

    <!-- Status Breakdown Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Status Pills -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Distribusi Status Aset</h2>
            <div class="space-y-3">
                @php
                    $statusColors = [
                        'available' => ['bg' => 'bg-emerald-500', 'text' => 'Tersedia'],
                        'deployed' => ['bg' => 'bg-indigo-500', 'text' => 'Dipinjam / In-use'],
                        'maintenance' => ['bg' => 'bg-amber-500', 'text' => 'Sedang Servis'],
                        'retired' => ['bg' => 'bg-slate-400', 'text' => 'Purna Pakai'],
                        'sold' => ['bg' => 'bg-blue-400', 'text' => 'Terjual'],
                        'disposed' => ['bg' => 'bg-rose-500', 'text' => 'Dimusnahkan'],
                    ];
                @endphp

                @foreach($statusColors as $code => $meta)
                    @php $count = $statusCounts[$code] ?? 0; @endphp
                    <div class="flex items-center justify-between text-sm py-1">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full {{ $meta['bg'] }}"></span>
                            <span class="font-medium text-slate-700">{{ $meta['text'] }}</span>
                        </div>
                        <span class="font-bold text-slate-900">{{ $count }} unit</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kantor Cabang -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Aset Berdasarkan Lokasi</h2>
            <div class="space-y-3">
                @foreach($byLocation as $loc)
                    <div class="flex items-center justify-between text-sm py-1 border-b border-slate-100 last:border-none">
                        <span class="font-medium text-slate-700 truncate pr-2">{{ $loc->name }}</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 font-bold text-xs">{{ $loc->assets_count }} unit</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kategori -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Aset Berdasarkan Kategori</h2>
            <div class="space-y-3">
                @foreach($byCategory as $cat)
                    <div class="flex items-center justify-between text-sm py-1 border-b border-slate-100 last:border-none">
                        <div>
                            <span class="font-medium text-slate-700 block">{{ $cat->name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $cat->code }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold text-xs">{{ $cat->assets_count }} unit</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Assets Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-sm">Aset Terbaru Terdaftar</h2>
            <a href="{{ route('web.assets.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lihat Semua Katalog &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Kode Aset</th>
                        <th class="px-6 py-3">Nama Perangkat</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">Lokasi</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-normal">
                    @foreach($recentAssets as $asset)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 font-mono font-bold text-xs text-indigo-600">
                                {{ $asset->asset_code }}
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-900">
                                {{ $asset->name }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $asset->category->name }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $asset->location->name }}
                            </td>
                            <td class="px-6 py-3.5 text-xs">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold
                                    @if($asset->status->value === 'available') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($asset->status->value === 'deployed') bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @elseif($asset->status->value === 'maintenance') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                    {{ ucfirst($asset->status->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs">
                                <a href="{{ route('web.assets.show', $asset) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Rincian</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
