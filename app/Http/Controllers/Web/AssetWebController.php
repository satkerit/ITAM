<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\ScanAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asset::query()->with(['category', 'location']);
        $this->applyCatalogFilters($query, $request);

        $assets = $query->latest('id')->paginate(12)->withQueryString();
        $locations = Location::all();
        $categories = AssetCategory::all();

        return view('assets.index', [
            'assets' => $assets,
            'locations' => $locations,
            'categories' => $categories,
        ]);
    }

    /**
     * Filter katalog yang dipakai bersama oleh daftar aset dan cetak label QR.
     */
    private function applyCatalogFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
    }

    public function create(): View
    {
        $locations = Location::all();
        $categories = AssetCategory::all();

        return view('assets.create', [
            'locations' => $locations,
            'categories' => $categories,
        ]);
    }

    private function validationRules(?int $assetId = null): array
    {
        return [
            'asset_code' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:assets,asset_code'.($assetId ? ','.$assetId : '')],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:asset_categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'purchase_store' => ['nullable', 'string', 'max:255'],
            'useful_life_months' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:'.implode(',', array_column(AssetStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'specifications' => ['nullable', 'array'],
            'specifications.*' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('asset-photos', 'public');
        }

        $asset = Asset::create($validated);

        return redirect()->route('web.assets.show', $asset)->with('success', 'Aset baru berhasil didaftarkan dan QR Code telah diterbitkan!');
    }

    public function edit(Asset $asset): View
    {
        $locations = Location::all();
        $categories = AssetCategory::all();

        return view('assets.edit', [
            'asset' => $asset,
            'locations' => $locations,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate($this->validationRules($asset->id));

        if ($request->hasFile('photo')) {
            if ($asset->photo_path) {
                Storage::disk('public')->delete($asset->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('asset-photos', 'public');
        }

        $asset->update($validated);

        return redirect()->route('web.assets.show', $asset)->with('success', 'Data aset berhasil diperbarui!');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->hasOperationalHistory()) {
            return redirect()->route('web.assets.index')->with('error', 'Aset memiliki riwayat operasional (peminjaman, mutasi, maintenance, atau disposal) sehingga tidak dapat dihapus.');
        }

        $asset->delete();

        if ($asset->photo_path) {
            Storage::disk('public')->delete($asset->photo_path);
        }
        if ($asset->qr_code_path) {
            Storage::disk('public')->delete($asset->qr_code_path);
        }

        return redirect()->route('web.assets.index')->with('success', 'Aset berhasil dihapus.');
    }

    public function show(Asset $asset): View
    {
        $asset->load([
            'category',
            'location',
            'assignments.user',
            'assignments.assignedBy',
            'mutations.fromLocation',
            'mutations.toLocation',
            'mutations.processedBy',
            'maintenanceLogs.performedBy',
            'disposal.authorizedBy',
        ]);

        return view('assets.show', [
            'asset' => $asset,
            'qrSvg' => $asset->qrSvg(192),
        ]);
    }

    public function printLabels(Request $request): View
    {
        $ids = collect((array) $request->query('ids', []))
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->all();

        $query = Asset::query()->with('location');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        } else {
            $this->applyCatalogFilters($query, $request);
        }

        $assets = $query->orderBy('asset_code')->limit(500)->get();

        $size = in_array($request->query('size'), ['small', 'medium', 'large'], true)
            ? $request->query('size')
            : 'medium';

        $qrPixels = ['small' => 120, 'medium' => 180, 'large' => 240][$size];

        $qrCodes = $assets->mapWithKeys(fn (Asset $asset) => [
            $asset->id => $asset->qrSvg($qrPixels),
        ]);

        return view('assets.print', [
            'assets' => $assets,
            'qrCodes' => $qrCodes,
            'size' => $size,
            'filters' => $request->only(['search', 'location_id', 'category_id', 'status', 'ids']),
        ]);
    }

    public function scan(ScanAssetRequest $request): View
    {
        $code = $request->validated('code');
        $foundAsset = null;

        if ($code !== null && $code !== '') {
            $foundAsset = Asset::where('asset_code', $code)
                ->with(['category', 'location', 'activeAssignment.user'])
                ->first();
        }

        return view('assets.scan', [
            'foundAsset' => $foundAsset,
            'code' => $code,
        ]);
    }

    /**
     * Unduh template CSV untuk impor aset.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['asset_code', 'name', 'category_code', 'location_name', 'serial_number', 'purchase_date', 'purchase_cost', 'useful_life_months', 'status', 'notes']);
            fputcsv($out, ['LPT-2026-001', 'Dell Latitude 5420', 'LPT', 'Kantor Pusat', 'SN123456', '2026-01-15', '15000000', '48', 'available', 'Unit operasional baru']);
            fclose($out);
        }, 'template-aset.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Impor data aset dari file CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        $categories = AssetCategory::pluck('id', 'code')->mapWithKeys(fn ($id, $code) => [strtoupper($code) => $id]);
        $locations = Location::pluck('id', 'name');
        $defaultLocationId = Location::query()->value('id');

        $rows = $this->parseCsv($request->file('file')->getRealPath());
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $assetCode = trim((string) ($row['asset_code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $categoryId = $categories[strtoupper(trim((string) ($row['category_code'] ?? '')))] ?? null;
            $locationId = $locations[trim((string) ($row['location_name'] ?? ''))] ?? $defaultLocationId;

            if ($assetCode === '' || $name === '' || $categoryId === null || $locationId === null
                || preg_match('/^[A-Za-z0-9._-]+$/', $assetCode) !== 1
                || Asset::where('asset_code', $assetCode)->exists()) {
                $skipped++;

                continue;
            }

            $purchaseDate = trim((string) ($row['purchase_date'] ?? ''));
            $status = strtolower(trim((string) ($row['status'] ?? ''))) ?: AssetStatus::Available->value;

            if ($purchaseDate !== '' && strtotime($purchaseDate) === false) {
                $skipped++;

                continue;
            }

            if (! in_array($status, array_column(AssetStatus::cases(), 'value'), true)) {
                $skipped++;

                continue;
            }

            Asset::create([
                'asset_code' => $assetCode,
                'name' => $name,
                'category_id' => $categoryId,
                'location_id' => $locationId,
                'serial_number' => trim((string) ($row['serial_number'] ?? '')) ?: null,
                'purchase_date' => $purchaseDate !== '' ? $purchaseDate : null,
                'purchase_cost' => is_numeric($row['purchase_cost'] ?? null) ? $row['purchase_cost'] : null,
                'useful_life_months' => is_numeric($row['useful_life_months'] ?? null) ? (int) $row['useful_life_months'] : null,
                'status' => $status,
                'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
            ]);
            $created++;
        }

        return redirect()->route('web.assets.index')
            ->with('success', "Impor selesai: {$created} aset ditambahkan, {$skipped} baris dilewati (kategori/lokasi tidak cocok, duplikat, atau invalid).");
    }

    /**
     * @return array<int, array<string, string|null>>
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
