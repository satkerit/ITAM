@extends('layouts.app')

@section('title', 'Servis & Pemeliharaan (Maintenance)')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, openCompleteModal: false, completeUrl: '', completeAsset: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Servis & Pemeliharaan</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau perbaikan hardware, biaya servis vendor, dan riwayat kerusakan perangkat.</p>
        </div>
        <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Servis Baru
        </button>
    </div>

    <!-- Tabel Servis -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800">Daftar Servis & Maintenance</h3>
            <span class="text-xs font-semibold text-slate-500">Total: {{ $maintenances->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Perangkat</th>
                        <th class="px-6 py-3.5">Deskripsi Kerusakan</th>
                        <th class="px-6 py-3.5">Vendor Servis</th>
                        <th class="px-6 py-3.5">Biaya (Cost)</th>
                        <th class="px-6 py-3.5">Status Servis</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($maintenances as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('web.assets.show', $item->asset) }}" class="font-bold text-indigo-600 hover:text-indigo-800 block text-xs">
                                    {{ $item->asset->name }}
                                </a>
                                <span class="font-mono text-[11px] text-slate-500">{{ $item->asset->asset_code }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <span class="text-slate-800 font-medium block">{{ $item->description }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 font-medium">
                                {{ $item->vendor_name ?? 'Internal IT Team' }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-900">
                                Rp {{ number_format($item->cost, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if(empty($item->completed_date))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Sedang Dikerjakan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Selesai ({{ \Carbon\Carbon::parse($item->completed_date)->format('d/m/Y') }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if(empty($item->completed_date))
                                    <button 
                                        @click="openCompleteModal = true; completeUrl = '{{ route('web.maintenances.complete', $item) }}'; completeAsset = '{{ $item->asset->name }} ({{ $item->asset->asset_code }})'"
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                        Tandai Selesai
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">Belum ada data servis pemeliharaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($maintenances->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $maintenances->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Servis Baru -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100" @click.away="openModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-base text-slate-900">Form Pemeliharaan / Servis Baru</h3>
                <button @click="openModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <form action="{{ route('web.maintenances.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Pilih Aset *</label>
                    <select name="asset_id" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Perangkat --</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} [{{ $asset->asset_code }}] ({{ $asset->status->value }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Masuk Servis *</label>
                        <input type="date" name="service_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tgl Selesai (Jika Selesai)</label>
                        <input type="date" name="completion_date" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Vendor / Tempat Servis</label>
                    <input type="text" name="vendor" placeholder="Contoh: Asus Service Center Roxy / Internal" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Deskripsi Gejala / Kerusakan *</label>
                    <textarea name="issue_description" required rows="2" placeholder="Layar flickering / Baterai drop / Kipas bising..." class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Perkiraan Biaya Servis (Rp)</label>
                    <input type="number" name="cost" min="0" placeholder="0" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs">Simpan Servis</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Selesaikan Servis -->
    <div x-show="openCompleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100" @click.away="openCompleteModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-base text-slate-900">Selesaikan Pemeliharaan Unit</h3>
                <button @click="openCompleteModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <form :action="completeUrl" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs text-slate-600">Perangkat: <strong x-text="completeAsset" class="text-slate-900"></strong></p>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Selesai *</label>
                    <input type="date" name="completion_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tindakan / Solusi Servis *</label>
                    <textarea name="action_taken" required rows="2" placeholder="Penggantian modul LCD dan thermal paste baru..." class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Total Biaya Akhir (Rp)</label>
                    <input type="number" name="cost" min="0" placeholder="0" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openCompleteModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs">Aset Kembali Available</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
