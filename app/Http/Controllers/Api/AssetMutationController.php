<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mutation\MutateAssetRequest;
use App\Models\Asset;
use App\Models\AssetMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetMutationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AssetMutation::query()->with([
            'asset:id,asset_code,name,location_id,status',
            'fromLocation:id,name',
            'toLocation:id,name',
            'processedBy:id,name',
        ]);

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->query('asset_id'));
        }

        if ($request->filled('to_location_id')) {
            $query->where('to_location_id', $request->query('to_location_id'));
        }

        $mutations = $query->latest('id')->paginate(15);

        return response()->json($mutations);
    }

    public function store(MutateAssetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $asset = Asset::findOrFail($validated['asset_id']);

        if (in_array($asset->status, [AssetStatus::Sold, AssetStatus::Disposed, AssetStatus::Retired])) {
            return response()->json([
                'message' => "Cannot mutate asset with end-of-life status '{$asset->status->value}'",
            ], 422);
        }

        if ($asset->location_id === (int) $validated['to_location_id']) {
            return response()->json([
                'message' => 'Destination location cannot be the same as current asset location',
            ], 422);
        }

        $mutation = DB::transaction(function () use ($validated, $asset, $request) {
            $processedBy = $validated['processed_by'] ?? $request->user()?->id ?? 1;

            $record = AssetMutation::create([
                'asset_id' => $asset->id,
                'from_location_id' => $asset->location_id,
                'to_location_id' => $validated['to_location_id'],
                'mutation_date' => $validated['mutation_date'],
                'reason' => $validated['reason'],
                'processed_by' => $processedBy,
            ]);

            $asset->update([
                'location_id' => $validated['to_location_id'],
            ]);

            return $record;
        });

        $mutation->load(['asset', 'fromLocation', 'toLocation', 'processedBy']);

        return response()->json([
            'message' => 'Asset mutated successfully to new location',
            'data' => $mutation,
        ], 201);
    }
}
