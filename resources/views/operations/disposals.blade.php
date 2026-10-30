@extends('layouts.app')

@section('title', 'Disposal & Pemusnahan')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Disposal & Pemusnahan</h1>
            <p class="text-xs text-slate-500 mt-1">Terminal akhir siklus hidup aset untuk penjualan lelang atau penghapusan scrap.</p>
        </div>
        <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Catat Disposal Baru
        </button>
    </div>

    <!-- Tabel Disposal -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800">Riwayat Penghapusan Aset</h3>
            <span class="text-xs font-semibold text-slate-500">Total: {{ $disposals->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Perangkat</th>
                        <th class="px-6 py-3.5">Tipe Disposal</th>
                        <th class="px-6 py-3.5">Tanggal Disposal</th>
                        <th class="px-6 py-3.5">Nilai Jual (Sale Amount)</th>
                        <th class="px-6 py-3.5">Alasan Disposal</th>
                        <th class="px-6 py-3.5">Disetujui Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($disposals as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('web.assets.show', $item->asset) }}" class="font-bold text-indigo-600 hover:text-indigo-800 block text-xs">
                                    {{ $item->asset->name }}
                                </a>
                                <span class="font-mono text-[11px] text-slate-500">{{ $item->asset->asset_code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($item->disposal_type->value === 'sale')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Dijual / Lelang (Sale)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Pemusnahan (Scrap)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-900">
                                Rp {{ number_format($item->value ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-800 font-medium">
                                {{ $item->notes ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $item->authorizedBy?->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">Belum ada catatan penghapusan aset.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($disposals->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $disposals->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Disposal Baru -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100" @click.away="openModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-base text-slate-900">Form Penghapusan / Disposal Aset</h3>
                <button @click="openModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <form action="{{ route('web.disposals.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Pilih Aset (Kecuali Deployed) *</label>
                    <select name="asset_id" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Perangkat --</option>
                        @foreach($eligibleAssets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} [{{ $asset->asset_code }}] ({{ $asset->status->value }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Metode Disposal *</label>
                        <select name="disposal_type" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                            <option value="disposal">Pemusnahan / Scrap (Disposal)</option>
                            <option value="sale">Penjualan / Lelang (Sale)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Disposal *</label>
                        <input type="date" name="disposal_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nominal Penjualan (Jika Dijual)</label>
                    <input type="number" name="sale_amount" min="0" placeholder="0" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Alasan Penghapusan *</label>
                    <input type="text" name="reason" required placeholder="Contoh: Rusak total tidak dapat diperbaiki / End-of-Life 5 tahun" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Catatan Berita Acara (BAST)</label>
                    <textarea name="notes" rows="2" placeholder="Nomor BAST Penghapusan Barang Milik Perusahaan..." class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-xs">Simpan Terminal Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
