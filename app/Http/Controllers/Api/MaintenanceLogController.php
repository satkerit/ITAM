<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\CompleteMaintenanceRequest;
use App\Http\Requests\Maintenance\StoreMaintenanceRequest;
use App\Models\MaintenanceLog;
use App\Services\AssetOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MaintenanceLog::query()->with([
            'asset:id,asset_code,name,status,location_id',
            'performedBy:id,name',
        ]);

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->query('asset_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $logs = $query->latest('id')->paginate(15);

        return response()->json($logs);
    }

    public function store(StoreMaintenanceRequest $request, AssetOperationService $operations): JsonResponse
    {
        $validated = $request->validated();

        $log = $operations->recordMaintenance(
            $validated['asset_id'],
            $validated,
            $validated['performed_by'] ?? $request->user()->id,
        );
        $log->load(['asset', 'performedBy']);

        return response()->json([
            'message' => 'Maintenance record created and asset status changed to maintenance',
            'data' => $log,
        ], 201);
    }

    public function complete(CompleteMaintenanceRequest $request, MaintenanceLog $maintenance, AssetOperationService $operations): JsonResponse
    {
        $maintenance = $operations->completeMaintenance($maintenance, $request->validated());
        $maintenance->load(['asset', 'performedBy']);

        return response()->json([
            'message' => 'Maintenance marked as completed and asset status returned to available',
            'data' => $maintenance,
        ]);
    }
}
