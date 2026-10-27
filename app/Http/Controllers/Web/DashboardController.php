<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\MaintenanceLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statusCounts = Asset::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalAssets = array_sum($statusCounts);
        $totalAssetValue = Asset::query()->sum('purchase_cost');
        $totalMaintenanceCost = MaintenanceLog::query()->sum('cost');

        $byLocation = Location::query()
            ->withCount('assets')
            ->get();

        $byCategory = AssetCategory::query()
            ->withCount('assets')
            ->get();

        $recentAssets = Asset::query()
            ->with(['location', 'category'])
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard', [
            'totalAssets' => $totalAssets,
            'totalAssetValue' => $totalAssetValue,
            'totalMaintenanceCost' => $totalMaintenanceCost,
            'statusCounts' => $statusCounts,
            'byLocation' => $byLocation,
            'byCategory' => $byCategory,
            'recentAssets' => $recentAssets,
        ]);
    }
}
