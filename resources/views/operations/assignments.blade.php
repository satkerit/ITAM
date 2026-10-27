@extends('layouts.app')

@section('title', 'Peminjaman / Check-out & Check-in')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, openReturnModal: false, returnUrl: '', returnAsset: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Peminjaman & Check-out</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola sirkulasi penugasan perangkat ke karyawan serta riwayat pengembalian.</p>
        </div>
        <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Check-out Baru
        </button>
    </div>

    <!-- Tabel Daftar Penugasan -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800">Daftar Penugasan Aktif & Riwayat</h3>
            <span class="text-xs font-semibold text-slate-500">Total: {{ $assignments->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Perangkat</th>
                        <th class="px-6 py-3.5">Peminjam (User)</th>
                        <th class="px-6 py-3.5">Tgl Peminjaman</th>
                        <th class="px-6 py-3.5">Status Sirkulasi</th>
                        <th class="px-6 py-3.5">Petugas PIC</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assignments as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('web.assets.show', $item->asset) }}" class="font-bold text-indigo-600 hover:text-indigo-800 block text-xs">
                                    {{ $item->asset->name }}
                                </a>
                                <span class="font-mono text-[11px] text-slate-500">{{ $item->asset->asset_code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800 text-xs block">{{ $item->user->name }}</span>
                                <span class="text-[11px] text-slate-400">{{ $item->user->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ \Carbon\Carbon::parse($item->assigned_date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if(empty($item->returned_date))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Dipinjam (Active)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Dikembalikan ({{ \Carbon\Carbon::parse($item->returned_date)->format('d/m/Y') }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $item->assigner?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if(empty($item->returned_date))
                                    <button 
                                        @click="openReturnModal = true; returnUrl = '{{ route('web.assignments.checkin', $item) }}'; returnAsset = '{{ $item->asset->name }} ({{ $item->asset->asset_code }})'"
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                        Check-in / Kembali
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">Belum ada riwayat penugasan perangkat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assignments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Check-out Baru -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100" @click.away="openModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-base text-slate-900">Check-out Aset ke User</h3>
                <button @click="openModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <form action="{{ route('web.assignments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Pilih Aset Tersedia *</label>
                    <select name="asset_id" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Perangkat (Available) --</option>
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} [{{ $asset->asset_code }}] - {{ $asset->location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Pilih Karyawan / Peminjam *</label>
                    <select name="user_id" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Peminjaman *</label>
                    <input type="date" name="assigned_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Catatan / Keperluan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tujuan atau kelengkapan..." class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs">Simpan Check-out</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Return / Check-in -->
    <div x-show="openReturnModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100" @click.away="openReturnModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-base text-slate-900">Check-in Pengembalian Aset</h3>
                <button @click="openReturnModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <form :action="returnUrl" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs text-slate-600">Anda akan mencatat pengembalian untuk unit: <strong x-text="returnAsset" class="text-slate-900"></strong></p>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Pengembalian *</label>
                    <input type="date" name="returned_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Kondisi Saat Kembali / Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Unit kembali dalam kondisi baik, charger lengkap..." class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openReturnModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs">Check-in Aset</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
