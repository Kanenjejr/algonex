<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | PRODUCTION PRODUCTS
        |--------------------------------------------------------------------------
        | - No fake opening stock.
        | - No fake received stock.
        | - Products are seeded from 8-digit inventory sub accounts only.
        | - Product stock is created with current_stock = 0.
        |--------------------------------------------------------------------------
        */

        DB::table('product_stocks')->delete();
        DB::table('stock_batches')->delete();
        DB::table('stock_ledgers')->delete();
        DB::table('products')->delete();

        $inventoryAccount = DB::table('accounts')->where('account_code', 'INV001')->first();
        $revenueAccount = DB::table('accounts')->where('account_code', 'REV001')->first();
        $cogsAccount = DB::table('accounts')->where('account_code', 'COGS001')->first();

        if (!$inventoryAccount || !$revenueAccount || !$cogsAccount) {
            $this->command->error('Missing legacy accounts: INV001, REV001, COGS001.');
            return;
        }

        $inventoryAccounts = DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->whereNotNull('SubDescription')
            ->where(function ($q) {
                $q->where('SubCode', 'LIKE', '30%')
                  ->orWhere('SubCode', 'LIKE', '35%');
            })
            ->orderBy('SubCode')
            ->get();

        $created = [];

        foreach ($inventoryAccounts as $inventorySubAccount) {
            $productName = $this->cleanProductName($inventorySubAccount->SubDescription);

            if (!$this->isValidProductName($productName)) {
                continue;
            }

            $uniqueKey = strtoupper($inventorySubAccount->SubCode);

            if (isset($created[$uniqueKey])) {
                continue;
            }

            $location = $this->resolveProductLocation($inventorySubAccount->SubCode, $productName);

            if (!$location) {
                continue;
            }

            $businessUnit = DB::table('company_units')->where('id', $location->comp_unit_id)->first();

            if (!$businessUnit) {
                continue;
            }

            $revenueSubAccount = $this->first8DigitAccount(['70', '71'], ['sales', 'revenue', 'income']);
            $cogsSubAccount = $this->first8DigitAccount(['60', '61'], ['cost', 'cogs', 'consumed']);

            if (!$revenueSubAccount || !$cogsSubAccount) {
                $this->command->warn('Revenue or COGS 8-digit account missing for product: ' . $productName);
                continue;
            }

            $productId = DB::table('products')->insertGetId([
                'user_id' => 1,

                'company_id' => $location->company_id,
                'comp_unit_id' => $businessUnit->id,
                'work_point_id' => $location->id,

                'product_name' => $productName,
                'product_size' => $this->defaultUnit($productName),

                'avg_cost' => 0,
                'total_qty' => 0,
                'total_value' => 0,
                'selling_price' => 0,
                'reorder_level' => 10,
                'opening_stock' => 0,

                'cogs_account_code' => $cogsSubAccount->SubCode,
                'cogs_account_id' => $cogsAccount->id,

                'inventory_account_code' => $inventorySubAccount->SubCode,
                'inventory_account_id' => $inventoryAccount->id,

                'revenue_account_code' => $revenueSubAccount->SubCode,
                'revenue_account_id' => $revenueAccount->id,

                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('product_stocks')->insert([
                'product_id' => $productId,
                'company_id' => $location->company_id,
                'business_unit_id' => $businessUnit->id,
                'work_point_id' => $location->id,
                'current_stock' => 0,
                'minimum_stock' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $created[$uniqueKey] = true;
        }

        $this->command->info('Production products seeded with zero stock and 8-digit accounting codes only.');
    }

    private function cleanProductName($name): string
    {
        $name = trim((string) $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return $name;
    }

    private function isValidProductName(string $name): bool
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
            'purchases',
            'expenses',
            'goods',
            'merchandise',
            'finished products',
            'products in progress',
            'stored goods',
            'inventory',
        ];

        foreach ($blocked as $word) {
            if ($lower === $word || str_contains($lower, $word . ' in stock')) {
                return false;
            }
        }

        return true;
    }

    private function resolveProductLocation(string $subCode, string $productName)
    {
        $lower = strtolower($productName);

        if (
            str_starts_with($subCode, '35') ||
            str_contains($lower, 'explosive') ||
            str_contains($lower, 'detonator') ||
            str_contains($lower, 'anfo') ||
            str_contains($lower, 'booster')
        ) {
            return $this->firstWorkPointByUnitCode('MGL0012') ?: $this->firstWorkPointByCompanyCode('MGL001');
        }

        return $this->firstWorkPointByCompanyCode('MGL001');
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

    private function firstWorkPointByUnitCode(string $unitCode)
    {
        $unit = DB::table('company_units')
            ->where('unit_code', $unitCode)
            ->first();

        if (!$unit) {
            return null;
        }

        return DB::table('work_points')
            ->where('comp_unit_id', $unit->id)
            ->where('status', 'Active')
            ->orderBy('id')
            ->first();
    }

    private function first8DigitAccount(array $prefixes, array $keywords)
    {
        return DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->where(function ($q) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $q->orWhere('SubCode', 'LIKE', $prefix . '%');
                }
            })
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('SubDescription', 'LIKE', '%' . $keyword . '%');
                }
            })
            ->orderBy('SubCode')
            ->first();
    }

    private function defaultUnit(string $productName): string
    {
        $lower = strtolower($productName);

        if (str_contains($lower, 'kg')) {
            return 'KG';
        }

        if (str_contains($lower, 'liter') || str_contains($lower, 'litre') || str_contains($lower, 'fuel')) {
            return 'LTR';
        }

        if (str_contains($lower, 'box')) {
            return 'BOX';
        }

        if (str_contains($lower, 'bag')) {
            return 'BAG';
        }

        return 'PCS';
    }
}