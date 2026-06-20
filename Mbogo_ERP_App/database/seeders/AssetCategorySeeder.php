<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;
use Illuminate\Support\Facades\Auth;
class AssetCategorySeeder extends Seeder
{
    public function run()
    {
        // NOTE: Adjust user_id/company_id/work_point_id to real values in your environment.
        // If you have a system user or admin, replace 1 with that user's id.
        $defaults = [
            ['name' => 'Land', 'code' => 'LAND', 'description' => 'Land (non-depreciable)', 'depreciation_rate' => 0.00],
            ['name' => 'Buildings', 'code' => 'BUID', 'description' => 'Buildings', 'depreciation_rate' => 5.00],
            ['name' => 'Motor Vehicles', 'code' => 'MV', 'description' => 'Cars and motor vehicles', 'depreciation_rate' => 25.00],
            ['name' => 'Office Equipment', 'code' => 'OE', 'description' => 'Office equipment and furniture', 'depreciation_rate' => 12.50],
            ['name' => 'Computers', 'code' => 'ICT', 'description' => 'Computer hardware', 'depreciation_rate' => 37.5],
            ['name' => 'Plant & Machinery', 'code' => 'PM', 'description' => 'Plant and machinery', 'depreciation_rate' => 12.50],
        ];
        foreach ($defaults as $d) {
            AssetCategory::updateOrCreate(
                ['code' => $d['code']],
                array_merge($d, [
                    'user_id' => 1,
                    'company_id' => 1,
                    'work_point_id' => null,
                    'status' => 'Active'
                ])
            );
        }
    }
}