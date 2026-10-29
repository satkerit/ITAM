<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Asset::query()->with(['category:id,name,code', 'location:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('location_id')) {
            $query->inLocation((int) $request->query('location_id'));
        }

        if ($request->filled('category_id')) {
            $query->inCategory((int) $request->query('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $assets = $query->latest('id')->paginate(15);

        return response()->json($assets);
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if (! isset($validated['status'])) {
            $validated['status'] = AssetStatus::Available;
        }

        $asset = Asset::create($validated);
        $asset->load(['category', 'location']);

        return response()->json([
            'message' => 'Asset created successfully',
            'data' => $asset,
        ], 201);
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load([
            'category',
            'location',
            'activeAssignment.user:id,name,email',
            'mutations.fromLocation:id,name',
            'mutations.toLocation:id,name',
            'mutations.processedBy:id,name',
            'maintenanceLogs.performedBy:id,name',
            'disposal.authorizedBy:id,name',
        ]);

        return response()->json([
            'data' => $asset,
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $asset->update($request->validated());
        $asset->load(['category', 'location']);

        return response()->json([
            'message' => 'Asset updated successfully',
            'data' => $asset,
        ]);
    }

    public function destroy(Asset $asset): JsonResponse
    {
        if ($asset->assignments()->exists() || $asset->mutations()->exists() || $asset->disposal()->exists()) {
            return response()->json([
                'message' => 'Cannot delete asset with operational history or disposal records',
            ], 422);
        }

        if ($asset->qr_code_path && Storage::disk('public')->exists($asset->qr_code_path)) {
            Storage::disk('public')->delete($asset->qr_code_path);
        }

        $asset->delete();

        return response()->json([
            'message' => 'Asset deleted successfully',
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        $code = $request->query('code');

        if (! $code) {
            return response()->json(['message' => 'QR code parameter is required'], 400);
        }

        $asset = Asset::where('asset_code', $code)
            ->with(['category', 'location', 'activeAssignment.user:id,name,email'])
            ->first();

        if (! $asset) {
            return response()->json(['message' => 'Asset not found with code: '.$code], 404);
        }

        return response()->json([
            'message' => 'Asset scanned successfully',
            'data' => $asset,
        ]);
    }

    public function qrImage(Asset $asset): Response
    {
        $svg = QrCode::format('svg')->size(300)->generate($asset->asset_code);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }
}
