<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\DisposalType;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetFullLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_asset_lifecycle_procurement_to_disposal(): void
    {
        Storage::fake('public');

        $hq = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Jendral Sudirman Kav 1']);
        $branch = Location::create(['name' => 'Kantor Cabang Bali', 'address' => 'Jl. Sunset Road']);
        $category = AssetCategory::create(['name' => 'Server & Workstation', 'code' => 'WSN']);
        $employee = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Procurement & Register (Task 5)
        $assetRes = $this->postJson('/api/v1/assets', [
            'asset_code' => 'WSN-2026-009',
            'name' => 'Precision Workstation 7920',
            'category_id' => $category->id,
            'location_id' => $hq->id,
            'status' => 'available',
            'purchase_cost' => 65000000,
            'specifications' => [
                'cpu' => 'Dual Intel Xeon Gold',
                'ram' => '128GB ECC',
                'gpu' => 'NVIDIA RTX A5000',
            ],
        ]);
        $assetRes->assertCreated();
        $assetId = $assetRes->json('data.id');

        // 2. Assignment / Check-out ke Karyawan (Task 6)
        $assignRes = $this->postJson('/api/v1/assignments/assign', [
            'asset_id' => $assetId,
            'user_id' => $employee->id,
            'assigned_date' => '2026-10-01',
            'notes' => 'Deploy mesin render 3D',
        ]);
        $assignRes->assertCreated();
        $assignmentId = $assignRes->json('data.id');
        $this->assertEquals(AssetStatus::Deployed, Asset::find($assetId)->status);

        // 3. Return / Check-in Aset (Task 6)
        $returnRes = $this->postJson("/api/v1/assignments/{$assignmentId}/return", [
            'returned_date' => '2026-10-10',
            'notes' => 'Proyek render selesai',
        ]);
        $returnRes->assertOk();
        $this->assertEquals(AssetStatus::Available, Asset::find($assetId)->status);

        // 4. Mutasi Antar Cabang (Task 6)
        $mutateRes = $this->postJson('/api/v1/mutations', [
            'asset_id' => $assetId,
            'to_location_id' => $branch->id,
            'mutation_date' => '2026-10-12',
            'reason' => 'Dipindahkan untuk workstation cabang Bali',
            'processed_by' => $admin->id,
        ]);
        $mutateRes->assertCreated();
        $this->assertEquals($branch->id, Asset::find($assetId)->location_id);

        // 5. Maintenance / Perbaikan (Task 7)
        $maintRes = $this->postJson('/api/v1/maintenances', [
            'asset_id' => $assetId,
            'maintenance_date' => '2026-10-15',
            'description' => 'Overhaul cooling unit & thermal paste',
            'cost' => 1500000,
        ]);
        $maintRes->assertCreated();
        $this->assertEquals(AssetStatus::Maintenance, Asset::find($assetId)->status);

        $maintId = $maintRes->json('data.id');
        $completeMaintRes = $this->postJson("/api/v1/maintenances/{$maintId}/complete", [
            'completed_date' => '2026-10-17',
        ]);
        $completeMaintRes->assertOk();
        $this->assertEquals(AssetStatus::Available, Asset::find($assetId)->status);

        // 6. Report Riwayat Kartu Aset (Task 9)
        $historyRes = $this->getJson("/api/v1/reports/assets/{$assetId}/history");
        $historyRes->assertOk()
            ->assertJsonCount(1, 'assignments')
            ->assertJsonCount(1, 'mutations')
            ->assertJsonCount(1, 'maintenance_logs');

        // 7. End of Life / Disposal (Task 8)
        $disposalRes = $this->postJson('/api/v1/disposals', [
            'asset_id' => $assetId,
            'disposal_type' => DisposalType::Sale->value,
            'date' => '2026-10-25',
            'value' => 28000000,
            'buyer_name' => 'PT Studio Bali Animasi',
            'authorized_by' => $admin->id,
        ]);
        $disposalRes->assertCreated();
        $this->assertEquals(AssetStatus::Sold, Asset::find($assetId)->status);

        // 8. Proteksi Terminal State: Gagal jika mencoba mutasi atau hapus setelah disposal
        $failMutateRes = $this->postJson('/api/v1/mutations', [
            'asset_id' => $assetId,
            'to_location_id' => $hq->id,
            'mutation_date' => '2026-10-26',
            'reason' => 'Mutasi gagal karena aset sudah sold',
        ]);
        $failMutateRes->assertStatus(422);

        $failDeleteRes = $this->deleteJson("/api/v1/assets/{$assetId}");
        $failDeleteRes->assertStatus(422);
    }
}
