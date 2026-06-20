<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RawMaterialSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | PRODUCTION RAW MATERIALS
        |--------------------------------------------------------------------------
        | - No depreciation / impairment names.
        | - No fake stock.
        | - No repeated material names.
        | - Uses only existing 8-digit accnt_subcharts.SubCode.
        | - Raw materials are seeded as a real master list, not only COA headings.
        |--------------------------------------------------------------------------
        */

        DB::table('raw_materials')->delete();

        $materials = [
            // ================= EXPLOSIVES RAW MATERIALS =================
            [
                'name' => 'Bulk Emulsion',
                'unit' => 'KG',
                'account_code' => '31130101',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'ANFO',
                'unit' => 'KG',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Ammonium Nitrate',
                'unit' => 'KG',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Fuel Oil',
                'unit' => 'LTR',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Emulsion Matrix',
                'unit' => 'KG',
                'account_code' => '31130101',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Detonating Cord',
                'unit' => 'ROLL',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Safety Fuse',
                'unit' => 'ROLL',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Shock Tube',
                'unit' => 'PCS',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Electric Detonator',
                'unit' => 'PCS',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Non Electric Detonator',
                'unit' => 'PCS',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Booster 200G',
                'unit' => 'PCS',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Booster 400G',
                'unit' => 'PCS',
                'account_code' => '31130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Blasting Accessories',
                'unit' => 'PCS',
                'account_code' => '31530101',
                'company_code' => 'MGL001',
            ],

            // ================= PACKAGING RAW MATERIALS =================
            [
                'name' => 'Non Recoverable Packaging',
                'unit' => 'PCS',
                'account_code' => '33130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Returnable Packaging',
                'unit' => 'PCS',
                'account_code' => '33230100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Packaging Carton',
                'unit' => 'PCS',
                'account_code' => '33130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Packaging Bag',
                'unit' => 'PCS',
                'account_code' => '33130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Packaging Label',
                'unit' => 'PCS',
                'account_code' => '33130100',
                'company_code' => 'MGL001',
            ],
            [
                'name' => 'Packaging Tape',
                'unit' => 'ROLL',
                'account_code' => '33130100',
                'company_code' => 'MGL001',
            ],

            // ================= FUEL / LUBRICANT RAW MATERIALS =================
            [
                'name' => 'Diesel',
                'unit' => 'LTR',
                'account_code' => '31530101',
                'company_code' => 'NCL001',
            ],
            [
                'name' => 'Petrol',
                'unit' => 'LTR',
                'account_code' => '31530101',
                'company_code' => 'NCL001',
            ],
            [
                'name' => 'Kerosene',
                'unit' => 'LTR',
                'account_code' => '31530101',
                'company_code' => 'NCL001',
            ],
            [
                'name' => 'Grease',
                'unit' => 'KG',
                'account_code' => '31530101',
                'company_code' => 'NCL001',
            ],
            [
                'name' => 'Engine Oil',
                'unit' => 'LTR',
                'account_code' => '31530101',
                'company_code' => 'NCL001',
            ],
            [
                'name' => 'Lubricants',
                'unit' => 'LTR',
                'account_code' => '31530101',
                'company_code' => 'NCL001',
            ],
            [
                'name' => 'Coolant',
                'unit' => 'LTR',
                'account_code' => '31530101',
                'company_code' => 'NCL001',
            ],

            // ================= GENERAL SUPPLIES RAW MATERIALS =================
            [
                'name' => 'General Supplies',
                'unit' => 'PCS',
                'account_code' => '31530101',
                'company_code' => 'MGL001',
            ],
        ];

        $created = [];

        foreach ($materials as $material) {
            $materialName = $this->cleanName($material['name']);

            if (!$this->isAllowedMaterial($materialName)) {
                continue;
            }

            $uniqueKey = strtoupper($materialName);

            if (isset($created[$uniqueKey])) {
                continue;
            }

            $accountCode = $this->validEightDigitAccount($material['account_code']);

            if (!$accountCode) {
                $this->command->warn('Skipped raw material because 8-digit account code not found: ' . $materialName . ' / ' . $material['account_code']);
                continue;
            }

            $location = $this->firstWorkPointByCompanyCode($material['company_code']);

            if (!$location) {
                $location = $this->firstActiveWorkPoint();
            }

            if (!$location) {
                $this->command->warn('Skipped raw material because no active work point found: ' . $materialName);
                continue;
            }

            $insertData = [
                'user_id' => 1,
                'company_id' => $location->company_id,
                'comp_unit_id' => $location->comp_unit_id,
                'work_point_id' => $location->id,

                'material_name' => $materialName,
                'material_code' => $this->makeMaterialCode($materialName, $accountCode),
                'unit_name' => $material['unit'],

                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL COMPATIBILITY
            |--------------------------------------------------------------------------
            | If later you add account_code to raw_materials table, this will fill it.
            | If the column does not exist, it will not break migration/seed.
            |--------------------------------------------------------------------------
            */
            if (Schema::hasColumn('raw_materials', 'account_code')) {
                $insertData['account_code'] = $accountCode;
            }

            if (Schema::hasColumn('raw_materials', 'inventory_account_code')) {
                $insertData['inventory_account_code'] = $accountCode;
            }

            DB::table('raw_materials')->insert($insertData);

            $created[$uniqueKey] = true;
        }

        $this->command->info(count($created) . ' production raw materials seeded successfully with valid 8-digit account codes.');
    }

    private function cleanName($name): string
    {
        $name = trim((string) $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return $name;
    }

    private function isAllowedMaterial(string $name): bool
    {
        if ($name === '') {
            return false;
        }

        $lower = strtolower($name);

        $blocked = [
            'depreciation',
            'impairment',
            'accumulated',
            'amortization',
            'provision',
            'cost of',
            'expense',
            'expenses',
            'work in progress',
            'finished products',
            'semi-finished',
            'waste',
            'scrap',
            'inventory depreciation',
            'stock depreciation',
        ];

        foreach ($blocked as $word) {
            if (str_contains($lower, $word)) {
                return false;
            }
        }

        return true;
    }

    private function validEightDigitAccount(string $preferredCode): ?string
    {
        $account = DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->where('SubCode', $preferredCode)
            ->first();

        if ($account) {
            return $account->SubCode;
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACKS
        |--------------------------------------------------------------------------
        | These are still 8-digit inventory/raw-material related accounts only.
        |--------------------------------------------------------------------------
        */

        $fallback = DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->whereIn('SubCode', [
                '31130100',
                '31130101',
                '31530101',
                '31530100',
                '33130100',
                '33230100',
            ])
            ->orderBy('SubCode')
            ->first();

        return $fallback ? $fallback->SubCode : null;
    }

    private function makeMaterialCode(string $materialName, string $accountCode): string
    {
        $shortName = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $materialName), 0, 6));

        if ($shortName === '') {
            $shortName = 'RAW';
        }

        return 'RM-' . $shortName . '-' . $accountCode;
    }

    private function firstWorkPointByCompanyCode(string $companyCode)
    {
        $company = DB::table('company_sites')
            ->where('company_code', $companyCode)
            ->first();

        if (!$company) {
            return null;
        }

        return DB::table('work_points')
            ->where('company_id', $company->id)
            ->where('status', 'Active')
            ->orderBy('id')
            ->first();
    }

    private function firstActiveWorkPoint()
    {
        return DB::table('work_points')
            ->where('status', 'Active')
            ->orderBy('id')
            ->first();
    }
}