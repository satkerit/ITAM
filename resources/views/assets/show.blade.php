@extends('layouts.app')

@section('title', $asset->name . ' (' . $asset->asset_code . ')')
@section('header', 'Detail Informasi Perangkat')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Asset Details -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-800 font-mono text-xs font-bold mr-2">
                            {{ $asset->asset_code }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                            @if($asset->status->value === 'available') bg-emerald-50 text-emerald-700 border border-emerald-200
                            @elseif($asset->status->value === 'deployed') bg-indigo-50 text-indigo-700 border border-indigo-200
                            @elseif($asset->status->value === 'maintenance') bg-amber-50 text-amber-700 border border-amber-200
                            @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                            {{ ucfirst($asset->status->value) }}
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">{{ $asset->name }}</h2>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-4 border-t border-slate-100 text-sm">
                    <div>
                        <span class="block text-slate-400 text-xs uppercase font-bold">Kategori</span>
                        <span class="font-semibold text-slate-800">{{ $asset->category->name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 text-xs uppercase font-bold">Lokasi Fisik</span>
                        <span class="font-semibold text-slate-800">{{ $asset->location->name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 text-xs uppercase font-bold">Nomor Seri</span>
                        <span class="font-mono text-slate-800 font-medium">{{ $asset->serial_number ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 text-xs uppercase font-bold">Tanggal Beli</span>
                        <span class="text-slate-800">{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 text-xs uppercase font-bold">Biaya Perolehan</span>
                        <span class="font-bold text-slate-900">
                            {{ $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 0, ',', '.') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-slate-400 text-xs uppercase font-bold">Tempat / Toko Pembelian</span>
                        <span class="text-slate-800">{{ $asset->purchase_store ?? '-' }}</span>
                    </div>
                    @if($asset->photo_path)
                        <div class="col-span-2 md:col-span-3">
                            <span class="block text-slate-400 text-xs uppercase font-bold mb-2">Foto Inventaris</span>
                            <img src="{{ Storage::url($asset->photo_path) }}" alt="Foto {{ $asset->name }}" class="h-48 rounded-xl border border-slate-200 object-cover">
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex gap-2">
                    <a href="{{ route('web.assets.edit', $asset) }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition">
                        Edit Aset
                    </a>
                    <form action="{{ route('web.assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Hapus aset ini beserta seluruh riwayat operasionalnya? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-semibold text-xs transition">
                            Hapus Aset
                        </button>
                    </form>
                </div>
            </div>

            <!-- Depreciation Card -->
            @if($asset->useful_life_months)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Penyusutan & Nilai Buku
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-slate-400 text-xs uppercase font-bold">Lama Susut</span>
                            <span class="font-bold text-slate-900">{{ $asset->useful_life_months }} bulan</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-slate-400 text-xs uppercase font-bold">Susut / Bulan</span>
                            <span class="font-bold text-slate-900">
                                {{ $asset->monthly_depreciation !== null ? 'Rp ' . number_format($asset->monthly_depreciation, 0, ',', '.') : '-' }}
                            </span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-slate-400 text-xs uppercase font-bold">Akumulasi Susut</span>
                            <span class="font-bold text-amber-700">
                                {{ $asset->accumulated_depreciation !== null ? 'Rp ' . number_format($asset->accumulated_depreciation, 0, ',', '.') : '-' }}
                            </span>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                            <span class="block text-indigo-500 text-xs uppercase font-bold">Nilai Buku</span>
                            <span class="font-bold text-indigo-800">
                                {{ $asset->book_value !== null ? 'Rp ' . number_format($asset->book_value, 0, ',', '.') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Dynamic Specifications -->
            @if(!empty($asset->specifications))
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        Spesifikasi Teknis
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($asset->specifications as $key => $val)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between text-xs">
                                <span class="font-bold uppercase text-slate-500">{{ str_replace('_', ' ', $key) }}</span>
                                <span class="font-semibold text-slate-900">{{ is_array($val) ? json_encode($val) : $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Operation Timeline Tabs -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6" x-data="{ tab: 'assignments' }">
                <div class="flex items-center gap-2 sm:gap-4 border-b border-slate-200 pb-3 mb-6 overflow-x-auto no-scrollbar">
                    <button @click="tab = 'assignments'" :class="tab === 'assignments' ? 'text-indigo-600 border-indigo-600 font-bold' : 'text-slate-400 font-medium'" class="pb-2 border-b-2 text-xs sm:text-sm border-transparent whitespace-nowrap transition">
                        Riwayat Penugasan ({{ $asset->assignments->count() }})
                    </button>
                    <button @click="tab = 'mutations'" :class="tab === 'mutations' ? 'text-indigo-600 border-indigo-600 font-bold' : 'text-slate-400 font-medium'" class="pb-2 border-b-2 text-xs sm:text-sm border-transparent whitespace-nowrap transition">
                        Riwayat Mutasi ({{ $asset->mutations->count() }})
                    </button>
                    <button @click="tab = 'maintenances'" :class="tab === 'maintenances' ? 'text-indigo-600 border-indigo-600 font-bold' : 'text-slate-400 font-medium'" class="pb-2 border-b-2 text-xs sm:text-sm border-transparent whitespace-nowrap transition">
                        Riwayat Servis ({{ $asset->maintenanceLogs->count() }})
                    </button>
                </div>

                <!-- Assignments -->
                <div x-show="tab === 'assignments'" class="space-y-3">
                    @forelse($asset->assignments as $assign)
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 text-xs flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-900 block text-sm">{{ $assign->user->name }}</span>
                                <span class="text-slate-500">Mulai: {{ $assign->assigned_date->format('d M Y') }} &bull; Selesai: {{ $assign->returned_date?->format('d M Y') ?? 'Masih Aktif' }}</span>
                                @if($assign->notes) <p class="text-slate-600 mt-1 italic">"{{ $assign->notes }}"</p> @endif
                            </div>
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold {{ $assign->status->value === 'active' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-700' }}">
                                {{ ucfirst($assign->status->value) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada catatan peminjaman.</p>
                    @endforelse
                </div>

                <!-- Mutations -->
                <div x-show="tab === 'mutations'" class="space-y-3">
                    @forelse($asset->mutations as $mut)
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 text-xs">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-slate-900">{{ $mut->fromLocation->name }} &rarr; {{ $mut->toLocation->name }}</span>
                                <span class="text-slate-400">{{ $mut->mutation_date->format('d M Y') }}</span>
                            </div>
                            <p class="text-slate-600">Alasan: {{ $mut->reason }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada riwayat mutasi perpindahan lokasi.</p>
                    @endforelse
                </div>

                <!-- Maintenances -->
                <div x-show="tab === 'maintenances'" class="space-y-3">
                    @forelse($asset->maintenanceLogs as $maint)
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 text-xs flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-900 block text-sm">{{ $maint->description }}</span>
                                <span class="text-slate-500">Tgl: {{ $maint->maintenance_date->format('d M Y') }} &bull; Vendor: {{ $maint->vendor_name ?? '-' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-slate-900 block">Rp {{ number_format($maint->cost, 0, ',', '.') }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $maint->status->value === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ ucfirst($maint->status->value) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada catatan servis/perbaikan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Actions & QR Code Card -->
        <div class="space-y-6">
            <!-- QR Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-3">Label Fisik QR Code</span>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl inline-block mb-4 shadow-inner">
                    @if($asset->qr_code_path)
                        <img src="{{ asset('storage/' . $asset->qr_code_path) }}" alt="QR Code" class="w-48 h-48 mx-auto">
                    @else
                        <div class="w-48 h-48 flex items-center justify-center text-xs text-slate-400">QR Code Belum Terbit</div>
                    @endif
                </div>
                <div class="font-mono text-sm font-bold text-slate-800">{{ $asset->asset_code }}</div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ asset('storage/' . $asset->qr_code_path) }}" download="{{ $asset->asset_code }}.svg" class="flex-1 py-2 px-3 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 transition border border-indigo-200">
                        Download SVG
                    </a>
                    <a href="{{ route('web.assets.print', ['ids' => [$asset->id]]) }}" class="flex-1 py-2 px-3 text-xs font-semibold bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition text-center">
                        Cetak Label
                    </a>
                </div>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-4">Aksi Cepat Operasional</span>
                <div class="space-y-2">
                    @if($asset->status->value === 'available')
                        <a href="{{ route('web.assignments.index', ['asset_id' => $asset->id]) }}" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs flex items-center justify-between transition">
                            <span>Pinjamkan ke Karyawan</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="{{ route('web.mutations.index', ['asset_id' => $asset->id]) }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs flex items-center justify-between transition">
                            <span>Mutasi Lokasi Cabang</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="{{ route('web.maintenances.index', ['asset_id' => $asset->id]) }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs flex items-center justify-between transition">
                            <span>Kirim ke Servis/Vendor</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="{{ route('web.disposals.index', ['asset_id' => $asset->id]) }}" class="w-full py-2.5 px-4 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs flex items-center justify-between transition border border-rose-200">
                            <span>Jual / Pemusnahan (Disposal)</span>
                            <span>&rarr;</span>
                        </a>
                    @elseif($asset->status->value === 'deployed')
                        <a href="{{ route('web.assignments.index') }}" class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs flex items-center justify-between transition">
                            <span>Kembalikan Aset (Check-in)</span>
                            <span>&rarr;</span>
                        </a>
                    @elseif($asset->status->value === 'maintenance')
                        <a href="{{ route('web.maintenances.index') }}" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs flex items-center justify-between transition">
                            <span>Selesaikan Perbaikan Servis</span>
                            <span>&rarr;</span>
                        </a>
                    @else
                        <div class="p-3 bg-slate-50 rounded-xl text-center text-xs text-slate-500 font-medium">
                            Aset berstatus terminal ({{ $asset->status->value }}). Tidak dapat diproses lagi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
