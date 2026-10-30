<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Disposal\DisposeAssetRequest;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Services\AssetOperationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetDisposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AssetDisposal::query()
            ->whereHas('asset', fn (Builder $q) => $q->visibleTo($request->user()))
            ->with([
                'asset:id,asset_code,name,status,location_id,category_id',
                'authorizedBy:id,name,email',
            ]);

        if ($request->filled('disposal_type')) {
            $query->where('disposal_type', $request->query('disposal_type'));
        }

        $disposals = $query->latest('id')->paginate(15);

        return response()->json($disposals);
    }

    public function store(DisposeAssetRequest $request, AssetOperationService $operations): JsonResponse
    {
        $validated = $request->validated();

        $this->authorize('update', Asset::findOrFail($validated['asset_id']));

        $disposal = $operations->dispose(
            $validated['asset_id'],
            $validated,
            $validated['authorized_by'] ?? $request->user()->id,
        );
        $disposal->load(['asset', 'authorizedBy']);

        return response()->json([
            'message' => 'Asset disposal recorded successfully and status changed to terminal state',
            'data' => $disposal,
        ], 201);
    }
}
