@extends('layouts.app')

@section('title', 'Template Spesifikasi: ' . $category->name)
@section('header', 'Template Spesifikasi Aset')

@section('content')
<div class="space-y-6" x-data="{
    fields: {{ $category->specificationFields->toJson() }},
    openModal: false,
    modalMode: 'create',
    editId: null,
    form: {
        field_name: '',
        field_label: '',
        field_type: 'text',
        field_options: '',
        is_required: false,
        sort_order: 0
    },
    loading: false,
    error: '',

    openCreate() {
        this.modalMode = 'create';
        this.editId = null;
        this.form = {
            field_name: '',
            field_label: '',
            field_type: 'text',
            field_options: '',
            is_required: false,
            sort_order: this.fields.length
        };
        this.error = '';
        this.openModal = true;
    },

    openEdit(field) {
        this.modalMode = 'edit';
        this.editId = field.id;
        this.form = {
            field_name: field.field_name,
            field_label: field.field_label,
            field_type: field.field_type,
            field_options: field.field_options ? JSON.stringify(field.field_options) : '',
            is_required: field.is_required,
            sort_order: field.sort_order
        };
        this.error = '';
        this.openModal = true;
    },

    async submit() {
        this.loading = true;
        this.error = '';

        const url = this.modalMode === 'create'
            ? '{{ route('web.categories.specifications.store', $category) }}'
            : '{{ route('web.categories.specifications.update', [$category, 'SPEC_ID']) }}'.replace('SPEC_ID', this.editId);

        const method = this.modalMode === 'create' ? 'POST' : 'PUT';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content
                },
                body: JSON.stringify({
                    _method: method === 'PUT' ? 'PUT' : undefined,
                    ...this.form
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (this.modalMode === 'create') {
                    this.fields.push(data.data);
                } else {
                    const idx = this.fields.findIndex(f => f.id === this.editId);
                    if (idx !== -1) this.fields[idx] = data.data;
                }
                this.openModal = false;
            } else {
                this.error = data.message || 'Gagal menyimpan data';
            }
        } catch (e) {
            this.error = 'Terjadi kesalahan jaringan';
        }

        this.loading = false;
    },

    async deleteField(id) {
        if (!confirm('Hapus field spesifikasi ini?')) return;

        try {
            const response = await fetch('{{ route('web.categories.specifications.destroy', [$category, 'SPEC_ID']) }}'.replace('SPEC_ID', id), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.fields = this.fields.filter(f => f.id !== id);
            } else {
                alert(data.message || 'Gagal menghapus');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan');
        }
    }
}">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('web.categories.index') }}" class="hover:text-indigo-600">Kategori Aset</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-900 font-medium">Template Spesifikasi</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                {{ $category->name }}
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $category->group === 'IT' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $category->group }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola field spesifikasi untuk kategori aset ini. Field akan muncul saat input aset baru.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('web.categories.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 text-slate-700 font-medium text-xs rounded-xl hover:bg-slate-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <button @click="openCreate()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Field
            </button>
        </div>
    </div>

    <!-- Table Fields -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 w-12">#</th>
                        <th class="px-6 py-3.5">Label Field</th>
                        <th class="px-6 py-3.5">Nama Field</th>
                        <th class="px-6 py-3.5">Tipe</th>
                        <th class="px-6 py-3.5">Options (JSON)</th>
                        <th class="px-6 py-3.5 text-center">Wajib</th>
                        <th class="px-6 py-3.5 text-center">Urutan</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(field, index) in fields" :key="field.id">
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 text-xs text-slate-400 font-mono" x-text="index + 1"></td>
                            <td class="px-6 py-4 font-semibold text-slate-900 text-xs" x-text="field.field_label"></td>
                            <td class="px-6 py-4">
                                <code class="text-xs font-mono text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded" x-text="field.field_name"></code>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-700" x-text="field.field_type"></span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 font-mono" x-text="field.field_options ? JSON.stringify(field.field_options) : '-'"></td>
                            <td class="px-6 py-4 text-center">
                                <span x-show="field.is_required" class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span x-show="!field.is_required" class="text-slate-300">-</span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-slate-500" x-text="field.sort_order"></td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button @click="openEdit(field)" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button @click="deleteField(field.id)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="fields.length === 0">
                        <td colspan="8" class="px-6 py-8 text-center text-xs text-slate-400 italic">
                            Belum ada field spesifikasi. Klik "Tambah Field" untuk mulai.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create/Edit Field -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900" x-text="modalMode === 'create' ? 'Tambah Field Spesifikasi' : 'Edit Field Spesifikasi'"></h3>
                <button @click="openModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>

            <form @submit.prevent="submit()" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Label Field *</label>
                        <input type="text" x-model="form.field_label" required placeholder="Contoh: Processor" class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Field (snake_case) *</label>
                        <input type="text" x-model="form.field_name" required pattern="^[a-z][a-z0-9_]*$" placeholder="Contoh: processor" class="w-full text-xs font-mono rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                        <p class="text-[10px] text-slate-400 mt-1">Huruf kecil, angka, underscore. Mulai dengan huruf.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Input *</label>
                        <select x-model="form.field_type" required class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Select (Dropdown)</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan</label>
                        <input type="number" x-model="form.sort_order" min="0" class="w-full text-xs rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                    </div>
                </div>

                <div x-show="form.field_type === 'select'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Options (JSON Array)</label>
                    <input type="text" x-model="form.field_options" placeholder='["Option 1", "Option 2"]' class="w-full text-xs font-mono rounded-xl border-slate-200 border px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-600">
                    <p class="text-[10px] text-slate-400 mt-1">Format: ["Intel", "AMD", "Apple Silicon"]</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="form.is_required" id="is_required" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                    <label for="is_required" class="text-xs text-slate-700">Field wajib diisi</label>
                </div>

                <div x-show="error" x-text="error" class="text-xs text-rose-600 bg-rose-50 border border-rose-200 px-3 py-2 rounded-xl"></div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="loading" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Simpan</span>
                        <span x-show="loading">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
