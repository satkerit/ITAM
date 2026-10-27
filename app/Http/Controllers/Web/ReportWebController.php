<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDisposal;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportWebController extends Controller
{
    public function index(Request $request): View
    {
        $locations = Location::query()
            ->withCount([
                'assets as total_assets',
                'assets as available_assets' => fn ($q) => $q->where('status', 'available'),
                'assets as deployed_assets' => fn ($q) => $q->where('status', 'deployed'),
                'assets as maintenance_assets' => fn ($q) => $q->where('status', 'maintenance'),
            ])
            ->withSum('assets as total_asset_value', 'purchase_cost')
            ->get();

        $categories = AssetCategory::query()
            ->withCount('assets')
            ->withSum('assets as total_value', 'purchase_cost')
            ->get();

        $disposals = AssetDisposal::query()
            ->with(['asset.category', 'authorizedBy'])
            ->latest('date')
            ->take(15)
            ->get();

        $totalAssets = Asset::count();
        $totalValuation = Asset::sum('purchase_cost');
        $totalInMaintenance = Asset::where('status', 'maintenance')->count();
        $totalDisposed = Asset::whereIn('status', ['archived', 'sold', 'defective'])->count();

        $selectedAsset = null;
        if ($request->filled('asset_code')) {
            $selectedAsset = Asset::query()
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
