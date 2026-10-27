<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Enums\AssignmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assignment\AssignAssetRequest;
use App\Http\Requests\Assignment\ReturnAssetRequest;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AssetAssignment::query()->with([
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

    public function assign(AssignAssetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $asset = Asset::findOrFail($validated['asset_id']);

        if ($asset->status !== AssetStatus::Available) {
            return response()->json([
                'message' => "Asset cannot be assigned because its status is '{$asset->status->value}' (must be 'available')",
            ], 422);
        }

        $assignment = DB::transaction(function () use ($validated, $asset, $request) {
            $assignedBy = $validated['assigned_by'] ?? $request->user()?->id;

            $record = AssetAssignment::create([
                'asset_id' => $asset->id,
                'user_id' => $validated['user_id'],
                'assigned_date' => $validated['assigned_date'],
                'status' => AssignmentStatus::Active,
                'notes' => $validated['notes'] ?? null,
                'assigned_by' => $assignedBy,
            ]);

            $asset->update(['status' => AssetStatus::Deployed]);

            return $record;
        });

        $assignment->load(['asset', 'user', 'assignedBy']);

        return response()->json([
            'message' => 'Asset assigned successfully',
            'data' => $assignment,
        ], 201);
    }

    public function return(ReturnAssetRequest $request, AssetAssignment $assignment): JsonResponse
    {
        if ($assignment->status === AssignmentStatus::Returned) {
            return response()->json([
                'message' => 'Asset assignment is already marked as returned',
            ], 422);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $assignment) {
            $assignment->update([
                'returned_date' => $validated['returned_date'],
                'status' => AssignmentStatus::Returned,
                'notes' => $validated['notes'] ?? $assignment->notes,
            ]);

            $assignment->asset->update([
                'status' => AssetStatus::Available,
            ]);
        });

        $assignment->load(['asset', 'user']);

        return response()->json([
            'message' => 'Asset returned successfully and status updated to available',
            'data' => $assignment,
        ]);
    }
}
