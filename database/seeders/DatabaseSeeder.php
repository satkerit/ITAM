<?php

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\DisposalType;
use App\Enums\MaintenanceStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetDisposal;
use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Company Profile
        CompanyProfile::firstOrCreate(['id' => 1], [
            'name' => 'PT Solusi Teknologi Nusantara',
            'tagline' => 'Enterprise IT Asset Management & Digital Infrastructure',
            'description' => 'Penyedia infrastruktur teknologi informasi dan pengelolaan siklus hidup aset IT korporasi terintegrasi.',
            'email' => 'contact@solusitekno.co.id',
            'phone' => '+62 21 5550 8989',
            'mobile' => '+62 812 3456 7890',
            'website' => 'https://solusitekno.co.id',
            'tax_id' => '01.234.567.8-012.000',
            'address' => 'Gedung Menara Sudirman Lt. 15, Jl. Jend. Sudirman Kav. 60',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12190',
            'country' => 'Indonesia',
        ]);

        // 1. Locations
        $locHQ = Location::firstOrCreate(['name' => 'Kantor Pusat Jakarta'], [
            'address' => 'Gedung Menara Sudirman Lt. 15, Jl. Jend. Sudirman Kav. 60, Jakarta Selatan',
            'description' => 'Head Office & Data Center Utama',
        ]);

        $locSurabaya = Location::firstOrCreate(['name' => 'Kantor Cabang Surabaya'], [
            'address' => 'Jl. Pemuda No. 45, Genteng, Surabaya',
            'description' => 'Operasional Jawa Timur',
        ]);

        $locBandung = Location::firstOrCreate(['name' => 'R&D Center Bandung'], [
            'address' => 'Jl. Ir. H. Juanda No. 102, Dago, Bandung',
            'description' => 'Pusat Riset dan Tim Developer',
        ]);

        // 2. Users
        $admin = User::firstOrCreate(['email' => 'admin@itam.local'], [
            'name' => 'Administrator IT',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'location_id' => $locHQ->id,
        ]);

        $staff1 = User::firstOrCreate(['email' => 'budi.santoso@itam.local'], [
            'name' => 'Budi Santoso',
            'password' => Hash::make('password'),
            'role' => UserRole::Staff,
            'location_id' => $locHQ->id,
        ]);

        $staff2 = User::firstOrCreate(['email' => 'siti.rahma@itam.local'], [
            'name' => 'Siti Rahma',
            'password' => Hash::make('password'),
            'role' => UserRole::Staff,
            'location_id' => $locBandung->id,
        ]);

        // 3. Asset Categories
        $catLaptop = AssetCategory::firstOrCreate(['code' => 'LPT'], [
            'name' => 'Laptop & Notebook',
            'description' => 'Perangkat komputasi portabel untuk karyawan',
            'schema_fields' => [
                ['name' => 'processor', 'label' => 'Processor', 'type' => 'text'],
                ['name' => 'ram_gb', 'label' => 'RAM (GB)', 'type' => 'number'],
                ['name' => 'storage', 'label' => 'Storage (SSD/HDD)', 'type' => 'text'],
            ],
        ]);

        $catServer = AssetCategory::firstOrCreate(['code' => 'SRV'], [
            'name' => 'Server & Rackmount',
            'description' => 'Infrastruktur server on-premise',
            'schema_fields' => [
                ['name' => 'rack_unit', 'label' => 'Rack Unit (U)', 'type' => 'text'],
                ['name' => 'ip_address', 'label' => 'IP Address Management', 'type' => 'text'],
            ],
        ]);

        $catPrinter = AssetCategory::firstOrCreate(['code' => 'PRT'], [
            'name' => 'Printer & Scanner',
            'description' => 'Perangkat cetak dokumen kantor',
            'schema_fields' => [
                ['name' => 'print_type', 'label' => 'Tipe Cetak', 'type' => 'text'],
            ],
        ]);

        // 4. Sample Assets
        $assetsData = [
            [
                'asset_code' => 'LPT-2026-001',
                'name' => 'MacBook Pro 16" M3 Pro',
                'category_id' => $catLaptop->id,
                'location_id' => $locHQ->id,
                'status' => AssetStatus::Deployed,
                'serial_number' => 'C02G8790MD6R',
                'purchase_date' => '2026-01-15',
                'purchase_cost' => 38000000,
                'specifications' => ['processor' => 'Apple M3 Pro 12-Core', 'ram_gb' => 36, 'storage' => '1TB SSD'],
                'notes' => 'Aset operasional Lead Software Engineer',
            ],
            [
                'asset_code' => 'LPT-2026-002',
                'name' => 'Lenovo ThinkPad X1 Carbon Gen 11',
                'category_id' => $catLaptop->id,
                'location_id' => $locBandung->id,
                'status' => AssetStatus::Available,
                'serial_number' => 'PF49B310',
                'purchase_date' => '2026-02-10',
                'purchase_cost' => 27500000,
                'specifications' => ['processor' => 'Intel Core i7-1365U', 'ram_gb' => 16, 'storage' => '512GB NVMe SSD'],
                'notes' => 'Tersedia di loker IT Bandung',
            ],
            [
                'asset_code' => 'SRV-2026-001',
                'name' => 'Dell PowerEdge R750',
                'category_id' => $catServer->id,
                'location_id' => $locHQ->id,
                'status' => AssetStatus::Available,
                'serial_number' => '9HKL872',
                'purchase_date' => '2025-11-20',
                'purchase_cost' => 85000000,
                'specifications' => ['rack_unit' => '2U Rack', 'ip_address' => '192.168.10.50'],
                'notes' => 'Server virtualization Proxmox cluster 1',
            ],
            [
                'asset_code' => 'PRT-2026-001',
                'name' => 'Epson EcoTank L15150 A3',
                'category_id' => $catPrinter->id,
                'location_id' => $locSurabaya->id,
                'status' => AssetStatus::Maintenance,
                'serial_number' => 'X792A00192',
                'purchase_date' => '2025-08-05',
                'purchase_cost' => 14000000,
                'specifications' => ['print_type' => 'Multifunction Color Inktank'],
                'notes' => 'Sedang diganti gear roller di Service Center Surabaya',
            ],
            [
                'asset_code' => 'LPT-2025-099',
                'name' => 'Dell Latitude 3410',
                'category_id' => $catLaptop->id,
                'location_id' => $locHQ->id,
                'status' => AssetStatus::Sold,
                'serial_number' => 'DL3410-0988',
                'purchase_date' => '2023-03-10',
                'purchase_cost' => 11000000,
                'specifications' => ['processor' => 'Intel Core i3 10th Gen', 'ram_gb' => 8, 'storage' => '256GB SSD'],
                'notes' => 'Lelang scrap pergantian unit baru',
            ],
        ];

        foreach ($assetsData as $item) {
            $asset = Asset::updateOrCreate(['asset_code' => $item['asset_code']], $item);

            // Relasi contoh
            if ($asset->asset_code === 'LPT-2026-001') {
                AssetAssignment::firstOrCreate([
                    'asset_id' => $asset->id,
                    'user_id' => $staff1->id,
                    'status' => 'active',
                ], [
                    'assigned_date' => '2026-01-20',
                    'assigned_by' => $admin->id,
                    'notes' => 'Penyerahan laptop kerja awal masuk',
                ]);
            }

            if ($asset->asset_code === 'PRT-2026-001') {
                MaintenanceLog::firstOrCreate([
                    'asset_id' => $asset->id,
                    'status' => MaintenanceStatus::InProgress,
                ], [
                    'maintenance_date' => '2026-10-20',
                    'description' => 'Kerusakan penarik kertas (roller error)',
                    'cost' => 450000,
                    'vendor_name' => 'Master Print Surabaya',
                    'performed_by' => $admin->id,
                ]);
            }

            if ($asset->asset_code === 'LPT-2025-099') {
                AssetDisposal::firstOrCreate([
                    'asset_id' => $asset->id,
                ], [
                    'disposal_type' => DisposalType::Sale,
                    'date' => '2026-10-15',
                    'value' => 2500000,
                    'buyer_name' => 'PT Rekondisi Jaya',
                    'authorized_by' => $admin->id,
                    'notes' => 'Unit lama kondisi baterai drop',
                ]);
            }
        }
    }
}
