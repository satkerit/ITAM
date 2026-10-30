<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;

class AssetCsvImporter
{
    /**
     * Impor aset dari berkas CSV, abaikan baris yang tidak valid.
     *
     * @return array{created: int, skipped: int}
     */
    public function import(string $path, ?int $visibleLocationId = null): array
    {
        $categories = AssetCategory::query()
            ->pluck('id', 'code')
            ->mapWithKeys(fn ($id, $code) => [strtoupper((string) $code) => $id]);

        $locationQuery = Location::query()
            ->when($visibleLocationId !== null, fn ($query) => $query->whereKey($visibleLocationId));

        $locations = $locationQuery->pluck('id', 'name');
        $defaultLocationId = $locations->first();

        $rows = $this->parseCsv($path);
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $assetCode = trim((string) ($row['asset_code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $categoryId = $categories[strtoupper(trim((string) ($row['category_code'] ?? '')))] ?? null;
            $locationId = $locations[trim((string) ($row['location_name'] ?? ''))] ?? $defaultLocationId;

            if ($assetCode === '' || $name === '' || $categoryId === null || $locationId === null
                || preg_match('/^[A-Za-z0-9._-]+$/', $assetCode) !== 1
                || Asset::query()->where('asset_code', $assetCode)->exists()) {
                $skipped++;

                continue;
            }

            $purchaseDate = trim((string) ($row['purchase_date'] ?? ''));

            if ($purchaseDate !== '' && strtotime($purchaseDate) === false) {
                $skipped++;

                continue;
            }

            $status = strtolower(trim((string) ($row['status'] ?? ''))) ?: AssetStatus::Available->value;

            if (! in_array($status, array_column(AssetStatus::cases(), 'value'), true)) {
                $skipped++;

                continue;
            }

            Asset::create([
                'asset_code' => $assetCode,
                'name' => $name,
                'category_id' => $categoryId,
                'location_id' => $locationId,
                'serial_number' => trim((string) ($row['serial_number'] ?? '')) ?: null,
                'purchase_date' => $purchaseDate !== '' ? $purchaseDate : null,
                'purchase_cost' => is_numeric($row['purchase_cost'] ?? null) ? $row['purchase_cost'] : null,
                'useful_life_months' => is_numeric($row['useful_life_months'] ?? null) ? (int) $row['useful_life_months'] : null,
                'status' => $status,
                'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
            ]);
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return $rows;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        while (($line = fgetcsv($handle)) !== false) {
            if (count(array_filter($line, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        fclose($handle);

        return $rows;
    }
}
