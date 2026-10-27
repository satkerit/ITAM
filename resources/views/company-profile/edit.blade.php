@extends('layouts.app')

@section('title', 'Profil Perusahaan')
@section('header', 'Pengaturan & Profil Perusahaan')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 text-white overflow-hidden shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center shrink-0 overflow-hidden shadow-inner">
                    @if ($profile->logo_url)
                        <img src="{{ $profile->logo_url }}" alt="{{ $profile->name }}" class="w-full h-full object-contain p-2">
                    @else
                        <span class="text-2xl font-black text-indigo-300">IT</span>
                    @endif
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight">{{ $profile->name }}</h2>
                    <p class="text-xs sm:text-sm text-indigo-200/80 mt-0.5">{{ $profile->tagline ?? 'Enterprise IT Asset Management' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs bg-white/10 px-3.5 py-1.5 rounded-full border border-white/15">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Organisasi Aktif</span>
            </div>
        </div>
    </div>

    <!-- Edit Form Card -->
    <form action="{{ route('web.company.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Visual Identity: Logo & Office Photo -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Identitas Visual & Media</h3>
                <p class="text-xs text-slate-500 mt-0.5">Upload logo resmi instansi dan dokumentasi foto gedung/kantor operasional.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Logo Upload Section -->
                <div x-data="{ 
                    previewUrl: '{{ $profile->logo_url }}',
                    updatePreview(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.previewUrl = URL.createObjectURL(file);
                        }
                    }
                }" class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Logo Perusahaan</label>
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <div class="w-20 h-20 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" alt="Logo Preview" class="w-full h-full object-contain p-2">
                            </template>
                            <template x-if="!previewUrl">
                                <span class="text-2xl font-bold text-slate-400">IT</span>
                            </template>
                        </div>
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <input 
                                type="file" 
                                name="logo" 
                                id="logo" 
                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                @change="updatePreview"
                                class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                            >
                            <p class="text-[11px] text-slate-400">PNG, JPG, WEBP, atau SVG. Maks 2MB.</p>
                            @error('logo')
                                <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Office Photo Section -->
                <div x-data="{ 
                    officePreview: '{{ $profile->office_photo_url }}',
                    updateOffice(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.officePreview = URL.createObjectURL(file);
                        }
                    }
                }" class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Foto Kantor / Gedung Utama</label>
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <div class="w-20 h-20 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                            <template x-if="officePreview">
                                <img :src="officePreview" alt="Office Preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!officePreview">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </template>
                        </div>
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <input 
                                type="file" 
                                name="office_photo" 
                                id="office_photo" 
                                accept="image/png,image/jpeg,image/webp"
                                @change="updateOffice"
                                class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                            >
                            <p class="text-[11px] text-slate-400">Foto lanskap kantor/lobi. Maks 4MB.</p>
                            @error('office_photo')
                                <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- General Info & Contacts -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Informasi Umum & Kontak</h3>
                <p class="text-xs text-slate-500 mt-0.5">Nama entitas legal, nomor kontak, website dan identitas perpajakan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <!-- Company Name -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-semibold text-slate-700">Nama Perusahaan / Organisasi <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name', $profile->name) }}" 
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                    @error('name')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tagline -->
                <div class="space-y-1.5">
                    <label for="tagline" class="block text-xs font-semibold text-slate-700">Slogan / Tagline Bisnis</label>
                    <input 
                        type="text" 
                        name="tagline" 
                        id="tagline" 
                        value="{{ old('tagline', $profile->tagline) }}" 
                        placeholder="e.g. Empowering Digital Excellence"
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                    @error('tagline')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-slate-700">Email Resmi Kantor</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $profile->email) }}" 
                            placeholder="admin@perusahaan.co.id"
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                        >
                    </div>
                    @error('email')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website -->
                <div class="space-y-1.5">
                    <label for="website" class="block text-xs font-semibold text-slate-700">Website URL</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <input 
                            type="url" 
                            name="website" 
                            id="website" 
                            value="{{ old('website', $profile->website) }}" 
                            placeholder="https://perusahaan.co.id"
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                        >
                    </div>
                    @error('website')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone Line -->
                <div class="space-y-1.5">
                    <label for="phone" class="block text-xs font-semibold text-slate-700">Telepon Kantor (Hunting/Fixed)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <input 
                            type="text" 
                            name="phone" 
                            id="phone" 
                            value="{{ old('phone', $profile->phone) }}" 
                            placeholder="+62 21 5550 8989"
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                        >
                    </div>
                </div>

                <!-- Mobile / WhatsApp -->
                <div class="space-y-1.5">
                    <label for="mobile" class="block text-xs font-semibold text-slate-700">Nomor Handphone / IT Helpdesk</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <input 
                            type="text" 
                            name="mobile" 
                            id="mobile" 
                            value="{{ old('mobile', $profile->mobile) }}" 
                            placeholder="+62 812 3456 7890"
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                        >
                    </div>
                </div>

                <!-- Tax ID / NPWP -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="tax_id" class="block text-xs font-semibold text-slate-700">NPWP / Tax Identification Number</label>
                    <input 
                        type="text" 
                        name="tax_id" 
                        id="tax_id" 
                        value="{{ old('tax_id', $profile->tax_id) }}" 
                        placeholder="01.234.567.8-012.000"
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Description -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-slate-700">Deskripsi Singkat Entitas</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3"
                        placeholder="Gambaran umum divisi atau lini bisnis korporasi..."
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >{{ old('description', $profile->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Address & Headquarter Location -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Alamat Fisik Kantor Pusat</h3>
                <p class="text-xs text-slate-500 mt-0.5">Lokasi domisili gedung, kota, kode pos dan negara.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                <!-- Street Address -->
                <div class="space-y-1.5 md:col-span-3">
                    <label for="address" class="block text-xs font-semibold text-slate-700">Alamat Lengkap Gedung / Jalan</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="2"
                        placeholder="Gedung Menara Sudirman Lt. 15, Jl. Jend. Sudirman Kav. 60"
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >{{ old('address', $profile->address) }}</textarea>
                </div>

                <!-- City -->
                <div class="space-y-1.5">
                    <label for="city" class="block text-xs font-semibold text-slate-700">Kota / Kabupaten</label>
                    <input 
                        type="text" 
                        name="city" 
                        id="city" 
                        value="{{ old('city', $profile->city) }}" 
                        placeholder="Jakarta Selatan"
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Postal Code -->
                <div class="space-y-1.5">
                    <label for="postal_code" class="block text-xs font-semibold text-slate-700">Kode Pos</label>
                    <input 
                        type="text" 
                        name="postal_code" 
                        id="postal_code" 
                        value="{{ old('postal_code', $profile->postal_code) }}" 
                        placeholder="12190"
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Country -->
                <div class="space-y-1.5">
                    <label for="country" class="block text-xs font-semibold text-slate-700">Negara</label>
                    <input 
                        type="text" 
                        name="country" 
                        id="country" 
                        value="{{ old('country', $profile->country) }}" 
                        placeholder="Indonesia"
                        class="w-full px-3.5 py-2.5 bg-slate-50/50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>
            </div>
        </div>

        <!-- Submit Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button 
                type="submit" 
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm rounded-xl shadow-xs transition flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perubahan Profil</span>
            </button>
        </div>
    </form>
</div>
@endsection
