@extends('layouts.app')

@section('title', 'Edit Aset: '.$asset->asset_code)
@section('header', 'Edit Data Aset')

@section('content')
    <div class="max-w-4xl bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 lg:p-8"
         x-data="{
            categories: {{ Js::from($categories) }},
            selectedCategoryId: '{{ old('category_id', $asset->category_id) }}',
            specifications: {{ Js::from($asset->specifications ?? []) }},
            get currentCategory() {
                return this.categories.find(c => c.id == this.selectedCategoryId);
            },
            get isITGroup() {
                return this.currentCategory && this.currentCategory.group === 'IT';
            }
         }">
        <form action="{{ route('web.assets.update', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Kode Aset Unik *</label>
                    <input type="text" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border font-mono font-semibold">
                    @error('asset_code') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Nama Perangkat *</label>
                    <input type="text" name="name" value="{{ old('name', $asset->name) }}" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border">
                    @error('name') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Kategori Aset *</label>
                    <select name="category_id" x-model="selectedCategoryId" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border bg-white">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $asset->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Lokasi Fisik *</label>
                    <select name="location_id" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border bg-white">
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('location_id', $asset->location_id) == $location->id)>{{ $location->kdloc ? '[' . $location->kdloc . '] ' : '' }}{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Status Aset *</label>
                    <select name="status" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border bg-white">
                        @foreach(\App\Enums\AssetStatus::cases() as $statusCase)
                            <option value="{{ $statusCase->value }}" @selected(old('status', $asset->status->value) === $statusCase->value)>{{ ucfirst($statusCase->value) }}</option>
                        @endforeach
                    </select>
                    @error('status') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Nomor Seri (Serial Number)</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border">
                    @error('serial_number') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Tanggal Pembelian</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border">
                    @error('purchase_date') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Harga Pembelian (Rp)</label>
                    <input type="number" name="purchase_cost" min="0" step="0.01" value="{{ old('purchase_cost', $asset->purchase_cost) }}" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border">
                    @error('purchase_cost') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Tempat / Toko Pembelian</label>
                    <input type="text" name="purchase_store" value="{{ old('purchase_store', $asset->purchase_store) }}" placeholder="Contoh: Toko Komputer Jaya / Tokopedia" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border">
                    @error('purchase_store') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Masa Manfaat (Bulan)</label>
                    <input type="number" name="useful_life_months" min="1" value="{{ old('useful_life_months', $asset->useful_life_months) }}" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border">
                    @error('useful_life_months') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Spesifikasi Teknis (IT Group) -->
            <div class="pt-4 border-t border-slate-200" x-show="isITGroup">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    Spesifikasi Teknis
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-blue-50/50 p-5 rounded-2xl border border-blue-200/70">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Processor / CPU</label>
                        <input type="text" name="specifications[processor]" x-model="specifications.processor" placeholder="Contoh: Intel Core i7-1265U" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">RAM / Memory</label>
                        <input type="text" name="specifications[ram]" x-model="specifications.ram" placeholder="Contoh: 16GB DDR4 3200MHz" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Storage / Penyimpanan</label>
                        <input type="text" name="specifications[storage]" x-model="specifications.storage" placeholder="Contoh: 512GB SSD NVMe" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Operating System</label>
                        <input type="text" name="specifications[os]" x-model="specifications.os" placeholder="Contoh: Windows 11 Pro" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">IP Address</label>
                        <input type="text" name="specifications[ip_address]" x-model="specifications.ip_address" placeholder="Contoh: 192.168.1.100" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">MAC Address</label>
                        <input type="text" name="specifications[mac_address]" x-model="specifications.mac_address" placeholder="Contoh: AA:BB:CC:DD:EE:FF" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">GPU / VGA</label>
                        <input type="text" name="specifications[gpu]" x-model="specifications.gpu" placeholder="Contoh: NVIDIA GeForce RTX 3050" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Monitor / Display</label>
                        <input type="text" name="specifications[monitor]" x-model="specifications.monitor" placeholder="Contoh: 14 inch FHD IPS" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                </div>
            </div>

            <!-- Spesifikasi Umum (NON-IT Group) -->
            <div class="pt-4 border-t border-slate-200" x-show="currentCategory && !isITGroup">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    Spesifikasi Umum
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-amber-50/50 p-5 rounded-2xl border border-amber-200/70">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Merek / Brand</label>
                        <input type="text" name="specifications[brand]" x-model="specifications.brand" placeholder="Contoh: IKEA / Olympus" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Model / Tipe</label>
                        <input type="text" name="specifications[model]" x-model="specifications.model" placeholder="Contoh: Bekant Desk / FE-230" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Ukuran / Dimensi</label>
                        <input type="text" name="specifications[dimensions]" x-model="specifications.dimensions" placeholder="Contoh: 120x60x75 cm" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Warna</label>
                        <input type="text" name="specifications[color]" x-model="specifications.color" placeholder="Contoh: Hitam / Putih" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Bahan / Material</label>
                        <input type="text" name="specifications[material]" x-model="specifications.material" placeholder="Contoh: Kayu / Plastik / Metal" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Kapasitas</label>
                        <input type="text" name="specifications[capacity]" x-model="specifications.capacity" placeholder="Contoh: 2 orang / 500 ml" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Fitur Tambahan</label>
                        <textarea name="specifications[features]" rows="2" x-model="specifications.features" placeholder="Contoh: Adjustable height, Kabel management, dll" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2 border bg-white"></textarea>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Catatan Tambahan</label>
                <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border">{{ old('notes', $asset->notes) }}</textarea>
                @error('notes') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Foto Inventaris (JPG/PNG/WEBP, maks 2MB)</label>
                @if($asset->photo_path)
                    <div class="mb-3">
                        <img src="{{ Storage::url($asset->photo_path) }}" alt="Foto {{ $asset->name }}" class="h-32 rounded-xl border border-slate-200 object-cover">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="w-full text-sm rounded-xl border-slate-200 px-3.5 py-2.5 border file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-xs file:font-semibold">
                @error('photo') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <a href="{{ route('web.assets.show', $asset) }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
