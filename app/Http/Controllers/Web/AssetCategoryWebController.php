<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\AssetCategorySpecification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetCategoryWebController extends Controller
{
    public function index(): View
    {
        $categories = AssetCategory::query()
            ->withCount(['assets', 'softwareLicenses'])
            ->latest('id')
            ->paginate(15);

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:asset_categories,name'],
            'code' => ['required', 'string', 'max:20', 'uppercase'],
            'group' => ['required', 'in:IT,NON-IT'],
            'description' => ['nullable', 'string'],
        ]);

        AssetCategory::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'group' => $validated['group'],
            'description' => $validated['description'] ?? null,
            'schema_fields' => [],
        ]);

        return redirect()->route('web.categories.index')->with('success', 'Kategori aset berhasil ditambahkan!');
    }

    public function update(Request $request, AssetCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:asset_categories,name,'.$category->id],
            'code' => ['required', 'string', 'max:20', 'uppercase'],
            'group' => ['required', 'in:IT,NON-IT'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'group' => $validated['group'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('web.categories.index')->with('success', 'Data kategori berhasil diperbarui!');
    }

    public function destroy(AssetCategory $category): RedirectResponse
    {
        if ($category->assets()->exists()) {
            return redirect()->route('web.categories.index')->with('error', 'Gagal menghapus: Masih terdapat aset yang menggunakan kategori ini.');
        }

        if ($category->softwareLicenses()->exists()) {
            return redirect()->route('web.categories.index')->with('error', 'Gagal menghapus: Masih terdapat lisensi software yang menggunakan kategori ini.');
        }

        $category->delete();

        return redirect()->route('web.categories.index')->with('success', 'Kategori aset berhasil dihapus.');
    }

    /**
     * Unduh template CSV untuk impor kategori.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'code', 'group', 'description']);
            fputcsv($out, ['Laptop & Notebook', 'LPT', 'IT', 'Perangkat komputer jinjing operasional']);
            fputcsv($out, ['Server & Rack', 'SVR', 'IT', 'Server rak data center']);
            fputcsv($out, ['Meja Kerja', 'MJX', 'NON-IT', 'Meja kantor standar']);
            fclose($out);
        }, 'template-kategori.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Impor data kategori dari file CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = $this->parseCsv($request->file('file')->getRealPath());
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $code = strtoupper(trim((string) ($row['code'] ?? '')));
            $group = strtoupper(trim((string) ($row['group'] ?? 'IT')));

            if ($name === '' || $code === '') {
                $skipped++;

                continue;
            }

            if (!in_array($group, ['IT', 'NON-IT'])) {
                $group = 'IT';
            }

            if (AssetCategory::where('name', $name)->exists()) {
                $skipped++;

                continue;
            }

            AssetCategory::create([
                'name' => $name,
                'code' => $code,
                'group' => $group,
                'description' => trim((string) ($row['description'] ?? '')) ?: null,
                'schema_fields' => [],
            ]);
            $created++;
        }

        return redirect()->route('web.categories.index')
            ->with('success', "Impor selesai: {$created} kategori ditambahkan, {$skipped} baris dilewati (duplikat/invalid).");
    }

    /**
     * Tampilkan halaman kelola spesifikasi template untuk kategori.
     */
    public function specifications(AssetCategory $category): View
    {
        $category->load('specificationFields');

        return view('categories.specifications', [
            'category' => $category,
        ]);
    }

    /**
     * Simpan field spesifikasi baru.
     */
    public function storeSpecification(Request $request, AssetCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'field_name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'field_label' => ['required', 'string', 'max:150'],
            'field_type' => ['required', 'in:text,number,date,select,textarea'],
            'field_options' => ['nullable', 'json'],
            'is_required' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        // Check unique field_name per category
        if ($category->specificationFields()->where('field_name', $validated['field_name'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Field name sudah digunakan dalam kategori ini.',
            ], 422);
        }

        $spec = $category->specificationFields()->create([
            'field_name' => $validated['field_name'],
            'field_label' => $validated['field_label'],
            'field_type' => $validated['field_type'],
            'field_options' => isset($validated['field_options']) ? json_decode($validated['field_options'], true) : null,
            'is_required' => $validated['is_required'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Field spesifikasi berhasil ditambahkan.',
            'data' => $spec,
        ]);
    }

    /**
     * Update field spesifikasi.
     */
    public function updateSpecification(Request $request, AssetCategory $category, AssetCategorySpecification $specification): JsonResponse
    {
        $validated = $request->validate([
            'field_name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'field_label' => ['required', 'string', 'max:150'],
            'field_type' => ['required', 'in:text,number,date,select,textarea'],
            'field_options' => ['nullable', 'json'],
            'is_required' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        // Check unique field_name per category (exclude current)
        if ($category->specificationFields()->where('field_name', $validated['field_name'])->where('id', '!=', $specification->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Field name sudah digunakan dalam kategori ini.',
            ], 422);
        }

        $specification->update([
            'field_name' => $validated['field_name'],
            'field_label' => $validated['field_label'],
            'field_type' => $validated['field_type'],
            'field_options' => isset($validated['field_options']) ? json_decode($validated['field_options'], true) : null,
            'is_required' => $validated['is_required'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Field spesifikasi berhasil diperbarui.',
            'data' => $specification,
        ]);
    }

    /**
     * Hapus field spesifikasi.
     */
    public function destroySpecification(AssetCategory $category, AssetCategorySpecification $specification): JsonResponse
    {
        $specification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Field spesifikasi berhasil dihapus.',
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return $rows;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        while (($line = fgetcsv($handle)) !== false) {
            if (count(array_filter($line, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        fclose($handle);

        return $rows;
    }
}
