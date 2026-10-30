@extends('layouts.app')

@section('title', 'Laporan Inventaris & Audit Trail')
@section('header', 'Laporan Inventaris & Audit Trail')

@section('content')
<div class="space-y-8 print:space-y-4">
    <!-- Header Page & Print Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Laporan Inventaris & Audit Trail</h1>
            <p class="text-xs text-slate-500 mt-1">Rekapitulasi sebaran stok fisik kantor cabang, valuasi kategori, dan riwayat siklus hidup individual aset.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-[11px] font-mono text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 print:hidden">
                Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </span>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs rounded-xl shadow-xs transition print:hidden">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Ekspor PDF
            </button>
        </div>
    </div>

    <!-- Executive Summary KPI Badges -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Aset Terdaftar</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalAssets) }} <span class="text-xs font-semibold text-slate-500">Unit</span></p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Valuasi Aset</span>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dalam Servis / Perbaikan</span>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ number_format($totalInMaintenance) }} <span class="text-xs font-semibold text-slate-500">Unit</span></p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Telah Di-Disposal / Dijual</span>
                <p class="text-2xl font-black text-slate-700 mt-1">{{ number_format($totalDisposed) }} <span class="text-xs font-semibold text-slate-500">Unit</span></p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
        </div>
    </div>

    <!-- Bagian 1: Ringkasan Sebaran per Lokasi -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-sm text-slate-900">1. Rekapitulasi Inventaris Fisik per Kantor Cabang</h3>
                <p class="text-xs text-slate-500 mt-0.5">Sebaran unit berdasarkan status fisik dan akumulasi modal investasi perangkat per lokasi kantor.</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200">{{ $locations->count() }} Cabang Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nama Lokasi & Alamat</th>
                        <th class="px-6 py-3.5 text-center">Total Unit</th>
                        <th class="px-6 py-3.5 text-center">Tersedia</th>
                        <th class="px-6 py-3.5 text-center">Digunakan</th>
                        <th class="px-6 py-3.5 text-center">Servis</th>
                        <th class="px-6 py-3.5 text-right">Akumulasi Nilai Aset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($locations as $loc)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-xs block">{{ $loc->name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $loc->address ?? 'Lokasi Terdaftar' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-xs text-slate-900">
                                {{ $loc->total_assets }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $loc->available_assets }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $loc->deployed_assets }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    {{ $loc->maintenance_assets }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-xs text-slate-900">
                                Rp {{ number_format($loc->total_asset_value ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-400 italic">Belum ada lokasi kantor tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bagian 2: Distribusi Kategori Aset -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-sm text-slate-900">2. Sebaran Kategori Perangkat & Valuasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Klasifikasi aset berdasarkan jenis perangkat dan alokasi modal pengadaan.</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200">{{ $categories->count() }} Kategori</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Kategori & Kode Prefix</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Unit</th>
                        <th class="px-6 py-3.5 text-right">Total Investasi</th>
                        <th class="px-6 py-3.5 text-right">Rata-rata Harga Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-xs">{{ $cat->name }}</span>
                                <span class="ml-2 font-mono text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $cat->code }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-xs text-slate-900">
                                {{ $cat->assets_count }} Unit
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-xs text-slate-900">
                                Rp {{ number_format($cat->total_value ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-600 font-mono">
                                Rp {{ number_format($cat->assets_count > 0 ? ($cat->total_value / $cat->assets_count) : 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-xs text-slate-400 italic">Belum ada kategori terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bagian 3: Audit Trail Lookup Kartu Riwayat Aset -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="font-bold text-sm text-slate-900">3. Pencarian Kartu Riwayat Hidup Individual Aset (Lifecycle Card)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Masukkan kode aset untuk mencetak rekam jejak lengkap dari pendaftaran hingga mutasi/servis.</p>
            </div>

            <form method="GET" action="{{ route('web.reports.index') }}" class="flex gap-2 max-w-md w-full print:hidden">
                <input type="text" name="asset_code" placeholder="Contoh: AST-LTP-001" value="{{ request('asset_code') }}" class="flex-1 text-xs rounded-xl border-slate-200 border px-3.5 py-2 uppercase font-mono bg-white focus:outline-hidden focus:ring-2 focus:ring-slate-900">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Cari Kartu
                </button>
            </form>
        </div>

        @if($selectedAsset)
            <div class="pt-6 border-t border-slate-200 space-y-6">
                <!-- Header Kartu Aset -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-5 rounded-2xl bg-slate-50 border border-slate-200/80 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-slate-800 font-mono text-sm shadow-xs">
                            {{ $selectedAsset->category->code }}
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kartu Riwayat Hidup Unit (Audit Trail)</span>
                            <h4 class="font-bold text-lg text-slate-900">{{ $selectedAsset->name }}</h4>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-mono text-xs font-semibold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $selectedAsset->asset_code }}</span>
                                <span class="text-xs text-slate-500">&bull; {{ $selectedAsset->category->name }} &bull; Lokasi Fisik Saat Ini: <strong class="text-slate-800">{{ $selectedAsset->location->name }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border
                            @if($selectedAsset->status->value === 'available') bg-emerald-50 text-emerald-700 border-emerald-200
                            @elseif($selectedAsset->status->value === 'deployed') bg-blue-50 text-blue-700 border-blue-200
                            @elseif($selectedAsset->status->value === 'maintenance') bg-amber-50 text-amber-700 border-amber-200
                            @else bg-slate-100 text-slate-700 border-slate-200 @endif">
                            <span class="w-2 h-2 rounded-full 
                                @if($selectedAsset->status->value === 'available') bg-emerald-500
                                @elseif($selectedAsset->status->value === 'deployed') bg-blue-500
                                @elseif($selectedAsset->status->value === 'maintenance') bg-amber-500
                                @else bg-slate-400 @endif"></span>
                            Status: {{ strtoupper($selectedAsset->status->value) }}
                        </span>
                    </div>
                </div>

                <!-- 3 Kolom Timeline -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Timeline Peminjaman -->
                    <div class="p-5 rounded-2xl border border-slate-200 bg-white space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h5 class="font-bold text-xs uppercase tracking-wider text-slate-900">Riwayat Penugasan</h5>
                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $selectedAsset->assignments->count() }}</span>
                        </div>
                        <div class="space-y-3 text-xs">
                            @forelse($selectedAsset->assignments as $asg)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                                    <span class="font-bold text-slate-900 block">{{ $asg->user->name }}</span>
                                    <span class="text-[11px] text-slate-500 block">Pinjam: {{ \Carbon\Carbon::parse($asg->assigned_date)->format('d/m/Y') }}</span>
                                    @if($asg->returned_date)
                                        <span class="text-[11px] text-emerald-700 font-semibold block">Kembali: {{ \Carbon\Carbon::parse($asg->returned_date)->format('d/m/Y') }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded mt-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Masih Dipinjam
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-slate-400 text-xs italic py-2">Belum ada riwayat penugasan.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Timeline Mutasi -->
                    <div class="p-5 rounded-2xl border border-slate-200 bg-white space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h5 class="font-bold text-xs uppercase tracking-wider text-slate-900">Riwayat Mutasi Lokasi</h5>
                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $selectedAsset->mutations->count() }}</span>
                        </div>
                        <div class="space-y-3 text-xs">
                            @forelse($selectedAsset->mutations as $mut)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                                    <span class="font-bold text-slate-900 block">{{ $mut->fromLocation->name }} &rarr; {{ $mut->toLocation->name }}</span>
                                    <span class="text-[11px] text-slate-500 block">{{ \Carbon\Carbon::parse($mut->mutation_date)->format('d/m/Y') }}</span>
                                    <span class="text-[11px] text-slate-600 italic block">Alasan: {{ $mut->reason }}</span>
                                </div>
                            @empty
                                <p class="text-slate-400 text-xs italic py-2">Belum ada mutasi lokasi.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Timeline Servis -->
                    <div class="p-5 rounded-2xl border border-slate-200 bg-white space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h5 class="font-bold text-xs uppercase tracking-wider text-slate-900">Riwayat Servis & Reparasi</h5>
                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $selectedAsset->maintenanceLogs->count() }}</span>
                        </div>
                        <div class="space-y-3 text-xs">
                            @forelse($selectedAsset->maintenanceLogs as $mnt)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                                    <span class="font-bold text-slate-900 block">{{ $mnt->description }}</span>
                                    <div class="flex justify-between items-center text-[11px] text-slate-500 pt-1">
                                        <span>Biaya: <strong class="text-slate-900">Rp {{ number_format($mnt->cost, 0, ',', '.') }}</strong></span>
                                        <span>Vendor: {{ $mnt->vendor_name ?? 'Internal' }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block">{{ \Carbon\Carbon::parse($mnt->maintenance_date)->format('d/m/Y') }}</span>
                                </div>
                            @empty
                                <p class="text-slate-400 text-xs italic py-2">Belum ada riwayat perbaikan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @elseif(request('asset_code'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Perangkat dengan kode <strong>"{{ request('asset_code') }}"</strong> tidak ditemukan di database. Pastikan kode inventaris yang dimasukkan sesuai.
            </div>
        @endif
    </div>

    <!-- Bagian 4: Catatan Pelepasan / Disposal Terkini -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-sm text-slate-900">4. Buku Catatan Pelepasan Aset (Disposal & Lelang)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar unit yang telah mencapai akhir siklus hidup, dilelang, atau dimusnahkan secara resmi.</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200">{{ $disposals->count() }} Terdata</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Perangkat & Kode</th>
                        <th class="px-6 py-3.5">Metode</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5 text-right">Nilai / Penerimaan</th>
                        <th class="px-6 py-3.5">Otorisator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($disposals as $disp)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-xs block">{{ $disp->asset?->name ?? 'Aset' }}</span>
                                <span class="text-[11px] font-mono text-slate-400">{{ $disp->asset?->asset_code ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $disp->disposal_type->value === 'sale' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ strtoupper($disp->disposal_type->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ \Carbon\Carbon::parse($disp->date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-xs text-slate-900">
                                Rp {{ number_format($disp->value ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $disp->authorizedBy?->name ?? 'Administrator' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-400 italic">Belum ada aset yang di-disposal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
