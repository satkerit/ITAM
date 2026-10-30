<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MasterDataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->admin()->create());
    }

    public function test_can_create_and_list_locations(): void
    {
        $payload = [
            'name' => 'Kantor Pusat Jakarta',
            'address' => 'Jl. Jenderal Sudirman No. 1, Jakarta Pusat',
            'description' => 'Gedung Menara IT Lantai 12',
        ];

        $createResponse = $this->postJson('/api/v1/locations', $payload);
        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Kantor Pusat Jakarta');

        $this->assertDatabaseHas('locations', ['name' => 'Kantor Pusat Jakarta']);

        $listResponse = $this->getJson('/api/v1/locations');
        $listResponse->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_update_and_delete_location(): void
    {
        $location = Location::create([
            'name' => 'Cabang Surabaya',
            'address' => 'Jl. Pemuda No. 10, Surabaya',
        ]);

        $updateResponse = $this->putJson("/api/v1/locations/{$location->id}", [
            'name' => 'Cabang Surabaya Barat',
        ]);
        $updateResponse->assertOk()
            ->assertJsonPath('data.name', 'Cabang Surabaya Barat');

        $deleteResponse = $this->deleteJson("/api/v1/locations/{$location->id}");
        $deleteResponse->assertOk();

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_can_create_and_update_asset_category_with_json_schema(): void
    {
        $payload = [
            'name' => 'Laptop',
            'code' => 'LPT',
            'description' => 'Perangkat komputer jinjing',
            'schema_fields' => [
                'processor' => 'string',
                'ram_gb' => 'number',
                'storage' => 'string',
            ],
        ];

        $createResponse = $this->postJson('/api/v1/asset-categories', $payload);
        $createResponse->assertCreated()
            ->assertJsonPath('data.code', 'LPT')
            ->assertJsonPath('data.schema_fields.ram_gb', 'number');

        $categoryId = $createResponse->json('data.id');

        $updateResponse = $this->putJson("/api/v1/asset-categories/{$categoryId}", [
            'name' => 'Laptop & Notebook',
        ]);
        $updateResponse->assertOk()
            ->assertJsonPath('data.name', 'Laptop & Notebook');

        $deleteResponse = $this->deleteJson("/api/v1/asset-categories/{$categoryId}");
        $deleteResponse->assertOk();

        $this->assertDatabaseMissing('asset_categories', ['id' => $categoryId]);
    }
}
