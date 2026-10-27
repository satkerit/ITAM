@extends('layouts.app')

@section('title', 'Mutasi Antar Lokasi')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Mutasi Antar Lokasi</h1>
            <p class="text-xs text-slate-500 mt-1">Pindahkan unit perangkat antar kantor cabang atau ruangan dengan audit trail akurat.</p>
        </div>
        <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            Buat Mutasi Baru
        </button>
    </div>

    <!-- Tabel Mutasi -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800">Riwayat Mutasi Perangkat</h3>
            <span class="text-xs font-semibold text-slate-500">Total: {{ $mutations->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Perangkat</th>
                        <th class="px-6 py-3.5">Rute Lokasi</th>
                        <th class="px-6 py-3.5">Tanggal Mutasi</th>
                        <th class="px-6 py-3.5">Alasan / Keperluan</th>
                        <th class="px-6 py-3.5">Petugas PIC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mutations as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('web.assets.show', $item->asset) }}" class="font-bold text-indigo-600 hover:text-indigo-800 block text-xs">
                                    {{ $item->asset->name }}
                                </a>
                                <span class="font-mono text-[11px] text-slate-500">{{ $item->asset->asset_code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium">{{ $item->fromLocation->name }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">{{ $item->toLocation->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ \Carbon\Carbon::parse($item->mutation_date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-800 font-medium">
                                {{ $item->reason }}
                                @if($item->notes)
                                    <p class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $item->notes }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $item->processedBy?->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs">Belum ada riwayat mutasi antar cabang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutations->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $mutations->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Mutasi Baru -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100" @click.away="openModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-base text-slate-900">Form Mutasi Lokasi Aset</h3>
                <button @click="openModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <form action="{{ route('web.mutations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Pilih Aset Yang Dimutasi *</label>
                    <select name="asset_id" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Perangkat --</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} [{{ $asset->asset_code }}] - Saat ini di: {{ $asset->location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Lokasi Tujuan *</label>
                    <select name="to_location_id" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Lokasi Kantor Cabang / Ruangan --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Mutasi *</label>
                    <input type="date" name="mutation_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Alasan Mutasi *</label>
                    <input type="text" name="reason" required placeholder="Contoh: Pembukaan cabang baru / Permintaan tim sales" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Surat jalan, kurir ekspedisi, atau nomor resi..." class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-white"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs">Kirim Mutasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
