@extends('layouts.app')

@section('title', 'Katalog Aset')
@section('header', 'Katalog Perangkat & Hardware')

@section('content')
    <div x-data="{ openImport: false, selected: [] }" class="space-y-6">
    <!-- Import Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-xs text-slate-500">Kelola katalog perangkat. Gunakan impor CSV untuk pendaftaran massal.</p>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('web.assets.template') }}" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh Template
            </a>
            <button @click="openImport = true" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Impor CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs mb-8">
        <form method="GET" action="{{ route('web.assets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, serial..." class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Lokasi</label>
                <select name="location_id" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Kategori</label>
                <select name="category_id" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2 px-4 rounded-xl transition shadow-xs">
                    Filter Data
                </button>
                @if(request()->hasAny(['search', 'location_id', 'category_id', 'status']))
                    <a href="{{ route('web.assets.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm py-2 px-3 rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Assets Grid Cards -->
    <form method="GET" action="{{ route('web.assets.print') }}" class="space-y-6">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                <input type="checkbox" @change="selected = $event.target.checked ? @js($assets->pluck('id')->all()) : []" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Pilih semua di halaman ini
            </label>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs text-slate-500"><span x-text="selected.length"></span> aset dipilih</span>
                <a href="{{ route('web.assets.print', request()->only(['search', 'location_id', 'category_id', 'status'])) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-50 transition">
                    Cetak Semua Hasil Filter
                </a>
                <button type="submit" :disabled="selected.length === 0" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak QR Terpilih
                </button>
            </div>
        </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($assets as $asset)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:border-indigo-300 transition group">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="ids[]" value="{{ $asset->id }}" x-model="selected" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-800 font-mono text-xs font-bold">
                                {{ $asset->asset_code }}
                            </span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                            @if($asset->status->value === 'available') bg-emerald-50 text-emerald-700 border border-emerald-200
                            @elseif($asset->status->value === 'deployed') bg-indigo-50 text-indigo-700 border border-indigo-200
                            @elseif($asset->status->value === 'maintenance') bg-amber-50 text-amber-700 border border-amber-200
                            @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                            {{ ucfirst($asset->status->value) }}
                        </span>
                    </div>

                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition mb-1 text-base">
                        <a href="{{ route('web.assets.show', $asset) }}">{{ $asset->name }}</a>
                    </h3>
                    <p class="text-xs text-slate-500 mb-4">{{ $asset->category->name }} &bull; {{ $asset->location->name }}</p>

                    @if(!empty($asset->specifications))
                        <div class="p-3 bg-slate-50 rounded-xl space-y-1 mb-4 text-xs text-slate-600">
                            @foreach(array_slice($asset->specifications, 0, 3) as $key => $val)
                                <div class="flex justify-between">
                                    <span class="text-slate-400 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                    <span class="font-medium text-slate-800">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-900">
                        {{ $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 0, ',', '.') : '-' }}
                    </span>
                    <a href="{{ route('web.assets.show', $asset) }}" class="font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                        Detail Lengkap &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-dashed border-slate-300">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <p class="text-slate-600 font-medium">Tidak ada aset yang sesuai kriteria pencarian.</p>
            </div>
        @endforelse
    </div>
    </form>

    <!-- Pagination -->
    <div>
        {{ $assets->links() }}
    </div>

    <!-- Modal Import CSV -->
    <div x-show="openImport" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="openImport = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Impor Aset dari CSV</h3>
                <button @click="openImport = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>
            <p class="text-xs text-slate-500">Kolom: asset_code, name, category_code, location_name, serial_number, purchase_date, purchase_cost, useful_life_months, status, notes. Kategori/lokasi dicocokkan berdasarkan kode/nama yang sudah ada.</p>
            <form method="POST" action="{{ route('web.assets.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="file" name="file" accept=".csv,.txt" required class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white">
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openImport = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs">Proses Impor</button>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
