<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mutation\MutateAssetRequest;
use App\Models\AssetMutation;
use App\Services\AssetOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function store(MutateAssetRequest $request, AssetOperationService $operations): JsonResponse
    {
        $validated = $request->validated();

        $mutation = $operations->mutate(
            $validated['asset_id'],
            $validated,
            $validated['processed_by'] ?? $request->user()->id,
        );
        $mutation->load(['asset', 'fromLocation', 'toLocation', 'processedBy']);

        return response()->json([
            'message' => 'Asset mutated successfully to new location',
            'data' => $mutation,
        ], 201);
    }
}
