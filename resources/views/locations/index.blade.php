@extends('layouts.app')

@section('title', 'Manajemen Lokasi Cabang')
@section('header', 'Manajemen Lokasi Cabang & Gedung')

@section('content')
<div class="space-y-6" x-data="{
    openCreateModal: false,
    openEditModal: false,
    editUrl: '',
    editData: { name: '', address: '', description: '' }
}">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Lokasi & Cabang Perusahaan</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar gedung, kantor cabang, dan area penempatan fisik seluruh aset IT.</p>
        </div>
        <button 
            @click="openCreateModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Lokasi Baru
        </button>
    </div>

    <!-- Table Lokasi -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nama Lokasi & Deskripsi</th>
                        <th class="px-6 py-3.5">Alamat Fisik</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Aset</th>
                        <th class="px-6 py-3.5 text-center">Pengguna Bertugas</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($locations as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-xs block">{{ $item->name }}</span>
                                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ $item->description ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $item->address ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-xs text-slate-900">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $item->assets_count }} Unit
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-slate-600 font-medium">
                                {{ $item->users_count }} Orang
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button 
                                        @click="
                                            openEditModal = true;
                                            editUrl = '{{ route('web.locations.update', $item) }}';
                                            editData = {
                                                name: '{{ addslashes($item->name) }}',
                                                address: '{{ addslashes($item->address ?? '') }}',
                                                description: '{{ addslashes($item->description ?? '') }}'
                                            }
                                        "
                                        class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    @if($item->assets_count === 0 && $item->users_count === 0)
                                        <form method="POST" action="{{ route('web.locations.destroy', $item) }}" onsubmit="return confirm('Hapus lokasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-400 italic">Belum ada data lokasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($locations->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Lokasi -->
    <div x-show="openCreateModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Tambah Lokasi Kantor Baru</h3>
                <button @click="openCreateModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>

            <form method="POST" action="{{ route('web.locations.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kantor / Cabang *</label>
                    <input type="text" name="name" required placeholder="Contoh: Kantor Cabang Surabaya" class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap *</label>
                    <textarea name="address" required rows="2" placeholder="Jalan, No, Kota, Provinsi..." class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Tambahan</label>
                    <input type="text" name="description" placeholder="Lantai 3, Server room, dsb." class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs">Simpan Lokasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Lokasi -->
    <div x-show="openEditModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="openEditModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Perbarui Data Lokasi Kantor</h3>
                <button @click="openEditModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>

            <form method="POST" :action="editUrl" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lokasi / Cabang *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap *</label>
                    <textarea name="address" required rows="2" x-model="editData.address" class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Tambahan</label>
                    <input type="text" name="description" x-model="editData.description" class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs">Perbarui Lokasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
