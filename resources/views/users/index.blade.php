@extends('layouts.app')

@section('title', 'Manajemen Pengguna & RBAC')
@section('header', 'Manajemen Pengguna & Kontrol Akses (RBAC)')

@section('content')
<div class="space-y-6" x-data="{
    openCreateModal: false,
    openEditModal: false,
    editUrl: '',
    editData: { name: '', email: '', role: 'staff', location_id: '' }
}">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengguna & Peran Akses</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola staf, teknisi hardware, dan administrator sistem berdasarkan Role-Based Access Control (RBAC).</p>
        </div>
        <button 
            @click="openCreateModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Tambah Pengguna Baru
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row gap-4 items-center justify-between">
        <form method="GET" action="{{ route('web.users.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email pengguna..." class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
            </div>

            <div class="w-44">
                <select name="role" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                    <option value="">Semua Role (RBAC)</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                            {{ ucfirst($role->value) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-48">
                <select name="location_id" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                    <option value="">Semua Lokasi Kantor</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'role', 'location_id']))
                <a href="{{ route('web.users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table Pengguna -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nama & Email</th>
                        <th class="px-6 py-3.5">Hak Akses (Role)</th>
                        <th class="px-6 py-3.5">Lokasi Kantor</th>
                        <th class="px-6 py-3.5 text-center">Unit Dipinjam</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-xs block">{{ $item->name }}</span>
                                <span class="text-[11px] text-slate-400 font-mono block">{{ $item->email }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $roleBadge = match($item->role->value) {
                                        'admin' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'technician' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $roleBadge }}">
                                    {{ strtoupper($item->role->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $item->location?->name ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-xs text-slate-900">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $item->assignments_count }} Unit
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button 
                                        @click="
                                            openEditModal = true;
                                            editUrl = '{{ route('web.users.update', $item) }}';
                                            editData = {
                                                name: '{{ addslashes($item->name) }}',
                                                email: '{{ addslashes($item->email) }}',
                                                role: '{{ $item->role->value }}',
                                                location_id: '{{ $item->location_id ?? '' }}'
                                            }
                                        "
                                        class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('web.users.destroy', $item) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-400 italic">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create User -->
    <div x-show="openCreateModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Tambah Akun Pengguna</h3>
                <button @click="openCreateModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>

            <form method="POST" action="{{ route('web.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required placeholder="Nama karyawan..." class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email *</label>
                    <input type="email" name="email" required placeholder="user@company.com" class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password Awal *</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter..." class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Role (RBAC) *</label>
                        <select name="role" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                            @foreach($roles as $role)
                                <option value="{{ $role->value }}">{{ ucfirst($role->value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Kantor</label>
                        <select name="location_id" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                            <option value="">Pilih Kantor</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="openEditModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="openEditModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Perbarui Akun Pengguna</h3>
                <button @click="openEditModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>

            <form method="POST" :action="editUrl" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email *</label>
                    <input type="email" name="email" x-model="editData.email" required class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Password (Kosongkan bila tidak diganti)</label>
                    <input type="password" name="password" minlength="8" placeholder="Password baru..." class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Role (RBAC) *</label>
                        <select name="role" x-model="editData.role" required class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                            @foreach($roles as $role)
                                <option value="{{ $role->value }}">{{ ucfirst($role->value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Kantor</label>
                        <select name="location_id" x-model="editData.location_id" class="w-full text-xs rounded-xl border-slate-200 border px-3 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                            <option value="">Pilih Kantor</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs">Perbarui Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
