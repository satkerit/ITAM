<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\MaintenanceLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $visible = fn (Builder $query): Builder => $query->visibleTo($user);

        $statusCounts = Asset::query()
            ->visibleTo($user)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalAssets = array_sum($statusCounts);
        $totalAssetValue = Asset::query()->visibleTo($user)->sum('purchase_cost');
        $totalMaintenanceCost = MaintenanceLog::query()
            ->whereHas('asset', $visible)
            ->sum('cost');

        $byLocation = Location::query()
            ->withCount(['assets' => $visible])
            ->select('id', 'name')
            ->get();

        $byCategory = AssetCategory::query()
            ->withCount(['assets' => $visible])
            ->select('id', 'name', 'code')
            ->get();

        return response()->json([
            'summary' => [
                'total_assets' => $totalAssets,
                'total_asset_value' => (float) $totalAssetValue,
                'total_maintenance_cost' => (float) $totalMaintenanceCost,
                'status_breakdown' => $statusCounts,
            ],
            'by_location' => $byLocation,
            'by_category' => $byCategory,
        ]);
    }
}
