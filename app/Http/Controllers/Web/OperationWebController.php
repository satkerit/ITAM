<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Enums\AssignmentStatus;
use App\Enums\DisposalType;
use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetDisposal;
use App\Models\AssetMutation;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function assignmentsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'user_id' => ['required', 'exists:users,id'],
            'assigned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $actorId = auth()->id() ?? User::query()->first()?->id ?? 1;

        DB::transaction(function () use ($validated, $actorId) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($validated['asset_id']);

            if ($asset->status !== AssetStatus::Available) {
                throw new \RuntimeException('Aset hanya dapat dipinjamkan jika berstatus Available.');
            }

            AssetAssignment::create([
                'asset_id' => $asset->id,
                'user_id' => $validated['user_id'],
                'assigned_by' => $actorId,
                'assigned_date' => $validated['assigned_date'],
                'status' => AssignmentStatus::Active,
                'notes' => $validated['notes'] ?? null,
            ]);

            $asset->update(['status' => AssetStatus::Deployed]);
        });

        return redirect()->route('web.assignments.index')->with('success', 'Penugasan aset (Check-out) berhasil disimpan!');
    }

    public function assignmentsCheckin(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'returned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($assignment, $validated) {
            $assignment->update([
                'returned_date' => $validated['returned_date'],
                'status' => AssignmentStatus::Returned,
                'notes' => $validated['notes'] ?? $assignment->notes,
            ]);

            $assignment->asset->update([
                'status' => AssetStatus::Available,
            ]);
        });

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

    public function mutationsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'to_location_id' => ['required', 'exists:locations,id', 'different:from_location_id'],
            'mutation_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $actorId = auth()->id() ?? User::query()->first()?->id ?? 1;

        DB::transaction(function () use ($validated, $actorId) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($validated['asset_id']);

            AssetMutation::create([
                'asset_id' => $asset->id,
                'from_location_id' => $asset->location_id,
                'to_location_id' => $validated['to_location_id'],
                'processed_by' => $actorId,
                'mutation_date' => $validated['mutation_date'],
                'reason' => $validated['reason'],
            ]);

            $asset->update([
                'location_id' => $validated['to_location_id'],
            ]);
        });

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

    public function maintenancesStore(Request $request): RedirectResponse
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

        $actorId = auth()->id() ?? User::query()->first()?->id ?? 1;

        DB::transaction(function () use ($validated, $actorId) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($validated['asset_id']);

            MaintenanceLog::create([
                'asset_id' => $asset->id,
                'performed_by' => $actorId,
                'maintenance_date' => $validated['service_date'],
                'completed_date' => $validated['completion_date'] ?? null,
                'description' => $validated['issue_description'],
                'cost' => $validated['cost'] ?? 0,
                'vendor_name' => $validated['vendor'] ?? null,
                'status' => empty($validated['completion_date']) ? MaintenanceStatus::InProgress : MaintenanceStatus::Completed,
            ]);

            $newStatus = empty($validated['completion_date'])
                ? AssetStatus::Maintenance
                : AssetStatus::Available;

            $asset->update(['status' => $newStatus]);
        });

        return redirect()->route('web.maintenances.index')->with('success', 'Pencatatan servis/pemeliharaan aset berhasil disimpan!');
    }

    public function maintenancesComplete(Request $request, MaintenanceLog $maintenance): RedirectResponse
    {
        $validated = $request->validate([
            'completion_date' => ['required', 'date'],
            'action_taken' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($maintenance, $validated) {
            $maintenance->update([
                'completed_date' => $validated['completion_date'],
                'cost' => $validated['cost'] ?? $maintenance->cost,
                'status' => MaintenanceStatus::Completed,
            ]);

            $maintenance->asset->update([
                'status' => AssetStatus::Available,
            ]);
        });

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

    public function disposalsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'disposal_type' => ['required', 'in:sale,disposal'],
            'disposal_date' => ['required', 'date'],
            'sale_amount' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $actorId = auth()->id() ?? User::query()->first()?->id ?? 1;

        DB::transaction(function () use ($validated, $actorId) {
            $asset = Asset::query()->lockForUpdate()->findOrFail($validated['asset_id']);

            if ($asset->status === AssetStatus::Deployed) {
                throw new \RuntimeException('Aset berstatus Deployed tidak boleh di-disposal langsung.');
            }

            AssetDisposal::create([
                'asset_id' => $asset->id,
                'authorized_by' => $actorId,
                'disposal_type' => $validated['disposal_type'],
                'date' => $validated['disposal_date'],
                'value' => $validated['sale_amount'] ?? 0,
                'notes' => $validated['reason'].($validated['notes'] ? ' - '.$validated['notes'] : ''),
            ]);

            $newStatus = $validated['disposal_type'] === DisposalType::Sale->value
                ? AssetStatus::Sold
                : AssetStatus::Disposed;

            $asset->update(['status' => $newStatus]);
        });

        return redirect()->route('web.disposals.index')->with('success', 'Penjualan / Pemusnahan aset telah dicatat.');
    }
}
