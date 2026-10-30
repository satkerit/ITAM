<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\MaintenanceLog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
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
            ->get();

        $byCategory = AssetCategory::query()
            ->withCount(['assets' => $visible])
            ->get();

        $recentAssets = Asset::query()
            ->visibleTo($user)
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
