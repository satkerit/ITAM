<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function stockByLocation(Request $request): JsonResponse
    {
        $locations = Location::query()
            ->withCount([
                'assets',
                'assets as available_count' => fn ($q) => $q->where('status', 'available'),
                'assets as deployed_count' => fn ($q) => $q->where('status', 'deployed'),
                'assets as maintenance_count' => fn ($q) => $q->where('status', 'maintenance'),
            ])
            ->withSum('assets', 'purchase_cost')
            ->get();

        return response()->json([
            'data' => $locations,
        ]);
    }

    public function assetHistory(Asset $asset): JsonResponse
    {
        $asset->load([
            'category:id,name,code',
            'location:id,name',
            'assignments.user:id,name,email',
            'assignments.assignedBy:id,name',
            'mutations.fromLocation:id,name',
            'mutations.toLocation:id,name',
            'mutations.processedBy:id,name',
            'maintenanceLogs.performedBy:id,name',
            'disposal.authorizedBy:id,name',
        ]);

        return response()->json([
            'asset' => [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'status' => $asset->status->value,
                'category' => $asset->category,
                'current_location' => $asset->location,
                'purchase_cost' => $asset->purchase_cost,
                'purchase_date' => $asset->purchase_date,
            ],
            'assignments' => $asset->assignments,
            'mutations' => $asset->mutations,
            'maintenance_logs' => $asset->maintenanceLogs,
            'disposal' => $asset->disposal,
        ]);
    }

    public function disposals(Request $request): JsonResponse
    {
        $query = AssetDisposal::query()->with([
            'asset:id,asset_code,name,purchase_cost,purchase_date,category_id,location_id',
            'asset.category:id,name',
            'asset.location:id,name',
            'authorizedBy:id,name,email',
        ]);

        if ($request->filled('disposal_type')) {
            $query->where('disposal_type', $request->query('disposal_type'));
        }

        $totalDisposalValue = (clone $query)->sum('value');
        $disposals = $query->latest('date')->paginate(20);

        return response()->json([
            'total_disposal_value' => (float) $totalDisposalValue,
            'disposals' => $disposals,
        ]);
    }
}
