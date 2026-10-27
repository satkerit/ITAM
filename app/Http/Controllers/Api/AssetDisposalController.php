<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Enums\DisposalType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Disposal\DisposeAssetRequest;
use App\Models\Asset;
use App\Models\AssetDisposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetDisposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AssetDisposal::query()->with([
            'asset:id,asset_code,name,status,location_id,category_id',
            'authorizedBy:id,name,email',
        ]);

        if ($request->filled('disposal_type')) {
            $query->where('disposal_type', $request->query('disposal_type'));
        }

        $disposals = $query->latest('id')->paginate(15);

        return response()->json($disposals);
    }

    public function store(DisposeAssetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $asset = Asset::findOrFail($validated['asset_id']);

        if ($asset->status === AssetStatus::Deployed) {
            return response()->json([
                'message' => 'Asset cannot be disposed while still deployed to an employee. Return it first.',
            ], 422);
        }

        if (in_array($asset->status, [AssetStatus::Sold, AssetStatus::Disposed, AssetStatus::Retired])) {
            return response()->json([
                'message' => "Asset is already disposed or has terminal status '{$asset->status->value}'",
            ], 422);
        }

        $disposal = DB::transaction(function () use ($validated, $asset, $request) {
            $authorizedBy = $validated['authorized_by'] ?? $request->user()?->id ?? 1;

            $record = AssetDisposal::create([
                'asset_id' => $asset->id,
                'disposal_type' => $validated['disposal_type'],
                'date' => $validated['date'],
                'value' => $validated['value'] ?? null,
                'buyer_name' => $validated['buyer_name'] ?? null,
                'authorized_by' => $authorizedBy,
                'notes' => $validated['notes'] ?? null,
            ]);

            $newStatus = match ($validated['disposal_type']) {
                DisposalType::Sale->value, DisposalType::Sale => AssetStatus::Sold,
                default => AssetStatus::Disposed,
            };

            $asset->update(['status' => $newStatus]);

            return $record;
        });

        $disposal->load(['asset', 'authorizedBy']);

        return response()->json([
            'message' => 'Asset disposal recorded successfully and status changed to terminal state',
            'data' => $disposal,
        ], 201);
    }
}
