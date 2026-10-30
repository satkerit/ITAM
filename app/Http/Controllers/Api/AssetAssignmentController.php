<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assignment\AssignAssetRequest;
use App\Http\Requests\Assignment\ReturnAssetRequest;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Services\AssetOperationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AssetAssignment::query()
            ->whereHas('asset', fn (Builder $q) => $q->visibleTo($request->user()))
            ->with([
                'asset:id,asset_code,name,status',
                'user:id,name,email',
                'assignedBy:id,name',
            ]);

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->query('asset_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $assignments = $query->latest('id')->paginate(15);

        return response()->json($assignments);
    }

    public function assign(AssignAssetRequest $request, AssetOperationService $operations): JsonResponse
    {
        $validated = $request->validated();

        $this->authorize('update', Asset::findOrFail($validated['asset_id']));

        $assignment = $operations->assign(
            $validated['asset_id'],
            $validated,
            $validated['assigned_by'] ?? $request->user()->id,
        );
        $assignment->load(['asset', 'user', 'assignedBy']);

        return response()->json([
            'message' => 'Asset assigned successfully',
            'data' => $assignment,
        ], 201);
    }

    public function return(ReturnAssetRequest $request, AssetAssignment $assignment, AssetOperationService $operations): JsonResponse
    {
        $this->authorize('update', $assignment->asset);

        $assignment = $operations->checkIn($assignment, $request->validated());
        $assignment->load(['asset', 'user']);

        return response()->json([
            'message' => 'Asset returned successfully and status updated to available',
            'data' => $assignment,
        ]);
    }
}
