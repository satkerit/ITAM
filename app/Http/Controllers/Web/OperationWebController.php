<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetDisposal;
use App\Models\AssetMutation;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\User;
use App\Services\AssetOperationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationWebController extends Controller
{
    // ==================== ASSIGNMENTS ====================

    public function assignmentsIndex(): View
    {
        $assignments = AssetAssignment::query()
            ->with(['asset', 'user', 'assignedBy'])
            ->latest('id')
            ->paginate(15);

        $availableAssets = Asset::query()
            ->where('status', AssetStatus::Available->value)
            ->get();

        $users = User::query()->orderBy('name')->get();

        return view('operations.assignments', [
            'assignments' => $assignments,
            'availableAssets' => $availableAssets,
            'users' => $users,
        ]);
    }

    public function assignmentsStore(Request $request, AssetOperationService $operations): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'user_id' => ['required', 'exists:users,id'],
            'assigned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $operations->assign($validated['asset_id'], $validated, $request->user()->id);

        return redirect()->route('web.assignments.index')->with('success', 'Penugasan aset (Check-out) berhasil disimpan!');
    }

    public function assignmentsCheckin(Request $request, AssetAssignment $assignment, AssetOperationService $operations): RedirectResponse
    {
        $validated = $request->validate([
            'returned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $operations->checkIn($assignment, $validated);

        return redirect()->route('web.assignments.index')->with('success', 'Pengembalian aset (Check-in) berhasil dicatat!');
    }

    // ==================== MUTATIONS ====================

    public function mutationsIndex(): View
    {
        $mutations = AssetMutation::query()
            ->with(['asset', 'fromLocation', 'toLocation', 'processedBy'])
            ->latest('id')
            ->paginate(15);

        $assets = Asset::query()
            ->whereNotIn('status', [AssetStatus::Disposed->value, AssetStatus::Sold->value])
            ->get();

        $locations = Location::all();

        return view('operations.mutations', [
            'mutations' => $mutations,
            'assets' => $assets,
            'locations' => $locations,
        ]);
    }

    public function mutationsStore(Request $request, AssetOperationService $operations): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'to_location_id' => ['required', 'exists:locations,id', 'different:from_location_id'],
            'mutation_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $operations->mutate($validated['asset_id'], $validated, $request->user()->id);

        return redirect()->route('web.mutations.index')->with('success', 'Mutasi lokasi aset berhasil diproses!');
    }

    // ==================== MAINTENANCES ====================

    public function maintenancesIndex(): View
    {
        $maintenances = MaintenanceLog::query()
            ->with(['asset', 'performedBy'])
            ->latest('id')
            ->paginate(15);

        $assets = Asset::query()
            ->whereNotIn('status', [AssetStatus::Disposed->value, AssetStatus::Sold->value])
            ->get();

        return view('operations.maintenances', [
            'maintenances' => $maintenances,
            'assets' => $assets,
        ]);
    }

    public function maintenancesStore(Request $request, AssetOperationService $operations): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'service_date' => ['required', 'date'],
            'completion_date' => ['nullable', 'date'],
            'issue_description' => ['required', 'string'],
            'action_taken' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'vendor' => ['nullable', 'string', 'max:255'],
        ]);

        $operations->recordMaintenance($validated['asset_id'], [
            'maintenance_date' => $validated['service_date'],
            'description' => $validated['issue_description'],
            'completed_date' => $validated['completion_date'] ?? null,
            'cost' => $validated['cost'] ?? 0,
            'vendor_name' => $validated['vendor'] ?? null,
        ], $request->user()->id);

        return redirect()->route('web.maintenances.index')->with('success', 'Pencatatan servis/pemeliharaan aset berhasil disimpan!');
    }

    public function maintenancesComplete(Request $request, MaintenanceLog $maintenance, AssetOperationService $operations): RedirectResponse
    {
        $validated = $request->validate([
            'completion_date' => ['required', 'date'],
            'action_taken' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $operations->completeMaintenance($maintenance, [
            'completed_date' => $validated['completion_date'],
            'cost' => $validated['cost'] ?? null,
        ]);

        return redirect()->route('web.maintenances.index')->with('success', 'Servis selesai! Status aset dikembalikan ke Available.');
    }

    // ==================== DISPOSALS ====================

    public function disposalsIndex(): View
    {
        $disposals = AssetDisposal::query()
            ->with(['asset', 'authorizedBy'])
            ->latest('id')
            ->paginate(15);

        $eligibleAssets = Asset::query()
            ->whereNotIn('status', [AssetStatus::Deployed->value, AssetStatus::Disposed->value, AssetStatus::Sold->value])
            ->get();

        return view('operations.disposals', [
            'disposals' => $disposals,
            'eligibleAssets' => $eligibleAssets,
        ]);
    }

    public function disposalsStore(Request $request, AssetOperationService $operations): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'disposal_type' => ['required', 'in:sale,disposal'],
            'disposal_date' => ['required', 'date'],
            'sale_amount' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $operations->dispose($validated['asset_id'], [
            'disposal_type' => $validated['disposal_type'],
            'date' => $validated['disposal_date'],
            'value' => $validated['sale_amount'] ?? 0,
            'notes' => $validated['reason'].(filled($validated['notes'] ?? null) ? ' - '.$validated['notes'] : ''),
        ], $request->user()->id);

        return redirect()->route('web.disposals.index')->with('success', 'Penjualan / Pemusnahan aset telah dicatat.');
    }
}
