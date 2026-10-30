<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDisposal;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportWebController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $visible = fn (Builder $query): Builder => $query->visibleTo($user);

        $locations = Location::query()
            ->withCount([
                'assets as total_assets' => $visible,
                'assets as available_assets' => fn (Builder $q) => $visible($q)->where('status', AssetStatus::Available),
                'assets as deployed_assets' => fn (Builder $q) => $visible($q)->where('status', AssetStatus::Deployed),
                'assets as maintenance_assets' => fn (Builder $q) => $visible($q)->where('status', AssetStatus::Maintenance),
            ])
            ->withSum(['assets as total_asset_value' => $visible], 'purchase_cost')
            ->get();

        $categories = AssetCategory::query()
            ->withCount(['assets' => $visible])
            ->withSum(['assets as total_value' => $visible], 'purchase_cost')
            ->get();

        $disposals = AssetDisposal::query()
            ->whereHas('asset', $visible)
            ->with(['asset.category', 'authorizedBy'])
            ->latest('date')
            ->take(15)
            ->get();

        $totalAssets = Asset::query()->visibleTo($user)->count();
        $totalValuation = Asset::query()->visibleTo($user)->sum('purchase_cost');
        $totalInMaintenance = Asset::query()->visibleTo($user)->where('status', AssetStatus::Maintenance)->count();
        $totalDisposed = Asset::query()->visibleTo($user)
            ->whereIn('status', [AssetStatus::Retired, AssetStatus::Sold, AssetStatus::Disposed])
            ->count();

        $selectedAsset = null;
        if ($request->filled('asset_code')) {
            $selectedAsset = Asset::query()
                ->visibleTo($user)
                ->with([
                    'category',
                    'location',
                    'assignments.user',
                    'mutations.fromLocation',
                    'mutations.toLocation',
                    'maintenanceLogs.performedBy',
                    'disposal.authorizedBy',
                ])
                ->where('asset_code', trim($request->query('asset_code')))
                ->first();
        }

        return view('reports.index', [
            'locations' => $locations,
            'categories' => $categories,
            'disposals' => $disposals,
            'totalAssets' => $totalAssets,
            'totalValuation' => $totalValuation,
            'totalInMaintenance' => $totalInMaintenance,
            'totalDisposed' => $totalDisposed,
            'selectedAsset' => $selectedAsset,
        ]);
    }
}
