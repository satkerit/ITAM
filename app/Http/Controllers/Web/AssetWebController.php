<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asset::query()->with(['category', 'location']);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
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

        $assets = $query->latest('id')->paginate(12)->withQueryString();
        $locations = Location::all();
        $categories = AssetCategory::all();

        return view('assets.index', [
            'assets' => $assets,
            'locations' => $locations,
            'categories' => $categories,
        ]);
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_code' => ['required', 'string', 'max:100', 'unique:assets,asset_code'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:asset_categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'useful_life_months' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
        ]);

        $qrPath = 'qrcodes/'.$validated['asset_code'].'.svg';
        $svg = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($validated['asset_code']);
        Storage::disk('public')->put($qrPath, $svg);
        $validated['qr_code_path'] = $qrPath;

        $asset = Asset::create($validated);

        return redirect()->route('web.assets.show', $asset)->with('success', 'Aset baru berhasil didaftarkan dan QR Code telah diterbitkan!');
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
        ]);
    }

    public function scan(Request $request): View
    {
        $foundAsset = null;
        if ($request->filled('code')) {
            $foundAsset = Asset::where('asset_code', $request->query('code'))
                ->with(['category', 'location', 'activeAssignment.user'])
                ->first();
        }

        return view('assets.scan', [
            'foundAsset' => $foundAsset,
            'code' => $request->query('code'),
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

            if ($assetCode === '' || $name === '' || $categoryId === null || $locationId === null || Asset::where('asset_code', $assetCode)->exists()) {
                $skipped++;

                continue;
            }

            $qrPath = 'qrcodes/'.$assetCode.'.svg';
            $svg = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($assetCode);
            Storage::disk('public')->put($qrPath, $svg);

            $purchaseDate = trim((string) ($row['purchase_date'] ?? ''));

            Asset::create([
                'asset_code' => $assetCode,
                'name' => $name,
                'category_id' => $categoryId,
                'location_id' => $locationId,
                'serial_number' => trim((string) ($row['serial_number'] ?? '')) ?: null,
                'purchase_date' => $purchaseDate !== '' ? $purchaseDate : null,
                'purchase_cost' => is_numeric($row['purchase_cost'] ?? null) ? $row['purchase_cost'] : null,
                'useful_life_months' => is_numeric($row['useful_life_months'] ?? null) ? (int) $row['useful_life_months'] : null,
                'status' => trim((string) ($row['status'] ?? '')) ?: 'available',
                'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
                'qr_code_path' => $qrPath,
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
