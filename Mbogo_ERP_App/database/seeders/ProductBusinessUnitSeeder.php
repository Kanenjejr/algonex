<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductBusinessUnitSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUCT REPAIR FOR PRODUCTION
        |--------------------------------------------------------------------------
        | - Merge duplicates.
        | - Do not create fake stock.
        | - Ensure product_stocks exists with current_stock = 0 only.
        |--------------------------------------------------------------------------
        */

        $this->mergeDuplicateProducts();

        $now = Carbon::now();

        $products = DB::table('products')
            ->orderBy('product_name')
            ->get();

        foreach ($products as $product) {
            $workPoint = null;

            if (!empty($product->work_point_id)) {
                $workPoint = DB::table('work_points')
                    ->where('id', $product->work_point_id)
                    ->first();
            }

            if (!$workPoint) {
                $workPoint = DB::table('work_points')
                    ->where('status', 'Active')
                    ->orderBy('id')
                    ->first();
            }

            if (!$workPoint) {
                continue;
            }

            $businessUnit = DB::table('company_units')
                ->where('id', $workPoint->comp_unit_id)
                ->first();

            if (!$businessUnit) {
                continue;
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'company_id' => $workPoint->company_id,
                    'comp_unit_id' => $businessUnit->id,
                    'work_point_id' => $workPoint->id,
                    'opening_stock' => 0,
                    'total_qty' => 0,
                    'total_value' => 0,
                    'avg_cost' => 0,
                    'status' => $product->status ?: 'Active',
                    'updated_at' => $now,
                ]);

            DB::table('product_stocks')->updateOrInsert(
                [
                    'product_id' => $product->id,
                    'company_id' => $workPoint->company_id,
                    'business_unit_id' => $businessUnit->id,
                    'work_point_id' => $workPoint->id,
                ],
                [
                    'current_stock' => 0,
                    'minimum_stock' => $product->reorder_level ?? 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->command->info('Products repaired. Duplicate products merged. Stock remains zero for production.');
    }

    private function mergeDuplicateProducts()
    {
        $products = DB::table('products')
            ->orderBy('id')
            ->get();

        $seen = [];

        foreach ($products as $product) {
            $productName = strtoupper(trim($product->product_name ?? ''));
            $inventoryCode = strtoupper(trim($product->inventory_account_code ?? ''));

            if ($productName === '' && $inventoryCode === '') {
                continue;
            }

            $key = $inventoryCode !== ''
                ? 'INV:' . $inventoryCode
                : 'NAME:' . $productName;

            if (!isset($seen[$key])) {
                $seen[$key] = $product->id;
                continue;
            }

            $mainProductId = $seen[$key];
            $duplicateProductId = $product->id;

            DB::table('product_stocks')
                ->where('product_id', $duplicateProductId)
                ->update([
                    'product_id' => $mainProductId,
                    'current_stock' => 0,
                    'updated_at' => now(),
                ]);

            DB::table('stock_ledgers')
                ->where('product_id', $duplicateProductId)
                ->update([
                    'product_id' => $mainProductId,
                    'updated_at' => now(),
                ]);

            DB::table('stock_batches')
                ->where('product_id', $duplicateProductId)
                ->update([
                    'product_id' => $mainProductId,
                    'updated_at' => now(),
                ]);

            DB::table('products')
                ->where('id', $duplicateProductId)
                ->delete();
        }
    }
}