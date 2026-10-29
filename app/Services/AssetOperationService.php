<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\AssignmentStatus;
use App\Enums\DisposalType;
use App\Enums\MaintenanceStatus;
use App\Exceptions\AssetOperationException;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetDisposal;
use App\Models\AssetMutation;
use App\Models\MaintenanceLog;
use Illuminate\Support\Facades\DB;

class AssetOperationService
{
    /**
     * @var list<AssetStatus>
     */
    private const END_OF_LIFE_STATUSES = [AssetStatus::Sold, AssetStatus::Disposed, AssetStatus::Retired];

    /**
     * @param  array{user_id: int, assigned_date: string, notes?: ?string}  $attributes
     */
    public function assign(int $assetId, array $attributes, int $assignedBy): AssetAssignment
    {
        return DB::transaction(function () use ($assetId, $attributes, $assignedBy): AssetAssignment {
            $asset = $this->lockAsset($assetId);

            if ($asset->status !== AssetStatus::Available) {
                throw new AssetOperationException("Asset cannot be assigned because its status is '{$asset->status->value}' (must be 'available')");
            }

            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id,
                'user_id' => $attributes['user_id'],
                'assigned_date' => $attributes['assigned_date'],
                'status' => AssignmentStatus::Active,
                'notes' => $attributes['notes'] ?? null,
                'assigned_by' => $assignedBy,
            ]);

            $asset->update(['status' => AssetStatus::Deployed]);

            return $assignment;
        });
    }

    /**
     * @param  array{returned_date: string, notes?: ?string}  $attributes
     */
    public function checkIn(AssetAssignment $assignment, array $attributes): AssetAssignment
    {
        return DB::transaction(function () use ($assignment, $attributes): AssetAssignment {
            $asset = $this->lockAsset($assignment->asset_id);
            $lockedAssignment = AssetAssignment::query()->lockForUpdate()->findOrFail($assignment->id);

            if ($lockedAssignment->status === AssignmentStatus::Returned) {
                throw new AssetOperationException('Asset assignment is already marked as returned');
            }

            $lockedAssignment->update([
                'returned_date' => $attributes['returned_date'],
                'status' => AssignmentStatus::Returned,
                'notes' => $attributes['notes'] ?? $lockedAssignment->notes,
            ]);

            $asset->update(['status' => AssetStatus::Available]);

            return $lockedAssignment;
        });
    }

    /**
     * @param  array{to_location_id: int, mutation_date: string, reason: string}  $attributes
     */
    public function mutate(int $assetId, array $attributes, int $processedBy): AssetMutation
    {
        return DB::transaction(function () use ($assetId, $attributes, $processedBy): AssetMutation {
            $asset = $this->lockAsset($assetId);

            if ($this->isEndOfLife($asset)) {
                throw new AssetOperationException("Cannot mutate asset with end-of-life status '{$asset->status->value}'");
            }

            if ($asset->location_id === (int) $attributes['to_location_id']) {
                throw new AssetOperationException('Destination location cannot be the same as current asset location');
            }

            $mutation = AssetMutation::create([
                'asset_id' => $asset->id,
                'from_location_id' => $asset->location_id,
                'to_location_id' => $attributes['to_location_id'],
                'mutation_date' => $attributes['mutation_date'],
                'reason' => $attributes['reason'],
                'processed_by' => $processedBy,
            ]);

            $asset->update(['location_id' => $attributes['to_location_id']]);

            return $mutation;
        });
    }

    /**
     * Catat servis. Jika completed_date diisi, servis langsung dianggap selesai.
     *
     * @param  array{maintenance_date: string, description: string, completed_date?: ?string, cost?: ?float, vendor_name?: ?string}  $attributes
     */
    public function recordMaintenance(int $assetId, array $attributes, int $performedBy): MaintenanceLog
    {
        return DB::transaction(function () use ($assetId, $attributes, $performedBy): MaintenanceLog {
            $asset = $this->lockAsset($assetId);

            if ($this->isEndOfLife($asset)) {
                throw new AssetOperationException("Cannot send asset with status '{$asset->status->value}' to maintenance");
            }

            $isCompleted = filled($attributes['completed_date'] ?? null);

            $log = MaintenanceLog::create([
                'asset_id' => $asset->id,
                'maintenance_date' => $attributes['maintenance_date'],
                'completed_date' => $attributes['completed_date'] ?? null,
                'description' => $attributes['description'],
                'cost' => $attributes['cost'] ?? 0,
                'vendor_name' => $attributes['vendor_name'] ?? null,
                'status' => $isCompleted ? MaintenanceStatus::Completed : MaintenanceStatus::InProgress,
                'performed_by' => $performedBy,
            ]);

            $asset->update(['status' => $isCompleted ? AssetStatus::Available : AssetStatus::Maintenance]);

            return $log;
        });
    }

    /**
     * @param  array{completed_date: string, cost?: ?float, notes?: ?string}  $attributes
     */
    public function completeMaintenance(MaintenanceLog $maintenance, array $attributes): MaintenanceLog
    {
        return DB::transaction(function () use ($maintenance, $attributes): MaintenanceLog {
            $asset = $this->lockAsset($maintenance->asset_id);
            $lockedMaintenance = MaintenanceLog::query()->lockForUpdate()->findOrFail($maintenance->id);

            if ($lockedMaintenance->status === MaintenanceStatus::Completed) {
                throw new AssetOperationException('Maintenance record is already marked as completed');
            }

            $updateData = [
                'completed_date' => $attributes['completed_date'],
                'status' => MaintenanceStatus::Completed,
            ];

            if (isset($attributes['cost'])) {
                $updateData['cost'] = $attributes['cost'];
            }

            if (! empty($attributes['notes'])) {
                $updateData['description'] = $lockedMaintenance->description."\n[Completion notes: {$attributes['notes']}]";
            }

            $lockedMaintenance->update($updateData);

            $asset->update(['status' => AssetStatus::Available]);

            return $lockedMaintenance;
        });
    }

    /**
     * @param  array{disposal_type: string, date: string, value?: ?float, buyer_name?: ?string, notes?: ?string}  $attributes
     */
    public function dispose(int $assetId, array $attributes, int $authorizedBy): AssetDisposal
    {
        return DB::transaction(function () use ($assetId, $attributes, $authorizedBy): AssetDisposal {
            $asset = $this->lockAsset($assetId);

            if ($asset->status === AssetStatus::Deployed) {
                throw new AssetOperationException('Asset cannot be disposed while still deployed to an employee. Return it first.');
            }

            if ($this->isEndOfLife($asset)) {
                throw new AssetOperationException("Asset is already disposed or has terminal status '{$asset->status->value}'");
            }

            $disposal = AssetDisposal::create([
                'asset_id' => $asset->id,
                'disposal_type' => $attributes['disposal_type'],
                'date' => $attributes['date'],
                'value' => $attributes['value'] ?? null,
                'buyer_name' => $attributes['buyer_name'] ?? null,
                'authorized_by' => $authorizedBy,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $asset->update([
                'status' => $attributes['disposal_type'] === DisposalType::Sale->value
                    ? AssetStatus::Sold
                    : AssetStatus::Disposed,
            ]);

            return $disposal;
        });
    }

    private function lockAsset(int $assetId): Asset
    {
        return Asset::query()->lockForUpdate()->findOrFail($assetId);
    }

    private function isEndOfLife(Asset $asset): bool
    {
        return in_array($asset->status, self::END_OF_LIFE_STATUSES, true);
    }
}
