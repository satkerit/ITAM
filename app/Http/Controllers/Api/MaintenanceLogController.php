<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\CompleteMaintenanceRequest;
use App\Http\Requests\Maintenance\StoreMaintenanceRequest;
use App\Models\Asset;
use App\Models\MaintenanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function store(StoreMaintenanceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $asset = Asset::findOrFail($validated['asset_id']);

        if (in_array($asset->status, [AssetStatus::Sold, AssetStatus::Disposed, AssetStatus::Retired])) {
            return response()->json([
                'message' => "Cannot send asset with status '{$asset->status->value}' to maintenance",
            ], 422);
        }

        $log = DB::transaction(function () use ($validated, $asset, $request) {
            $performedBy = $validated['performed_by'] ?? $request->user()?->id;

            $record = MaintenanceLog::create([
                'asset_id' => $asset->id,
                'maintenance_date' => $validated['maintenance_date'],
                'description' => $validated['description'],
                'cost' => $validated['cost'] ?? 0,
                'vendor_name' => $validated['vendor_name'] ?? null,
                'status' => MaintenanceStatus::InProgress,
                'performed_by' => $performedBy,
            ]);

            $asset->update(['status' => AssetStatus::Maintenance]);

            return $record;
        });

        $log->load(['asset', 'performedBy']);

        return response()->json([
            'message' => 'Maintenance record created and asset status changed to maintenance',
            'data' => $log,
        ], 201);
    }

    public function complete(CompleteMaintenanceRequest $request, MaintenanceLog $maintenance): JsonResponse
    {
        if ($maintenance->status === MaintenanceStatus::Completed) {
            return response()->json([
                'message' => 'Maintenance record is already marked as completed',
            ], 422);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $maintenance) {
            $updateData = [
                'completed_date' => $validated['completed_date'],
                'status' => MaintenanceStatus::Completed,
            ];

            if (isset($validated['cost'])) {
                $updateData['cost'] = $validated['cost'];
            }

            if (! empty($validated['notes'])) {
                $updateData['description'] = $maintenance->description."\n[Completion notes: {$validated['notes']}]";
            }

            $maintenance->update($updateData);

            $maintenance->asset->update([
                'status' => AssetStatus::Available,
            ]);
        });

        $maintenance->load(['asset', 'performedBy']);

        return response()->json([
            'message' => 'Maintenance marked as completed and asset status returned to available',
            'data' => $maintenance,
        ]);
    }
}
