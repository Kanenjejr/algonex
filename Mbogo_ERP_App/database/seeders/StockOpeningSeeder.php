<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockOpeningSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | PRODUCTION STOCK OPENING
        |--------------------------------------------------------------------------
        | No random opening stock.
        | No fake stock ledgers.
        | No fake batches.
        | Stock starts from zero.
        |--------------------------------------------------------------------------
        */

        DB::table('stock_ledgers')->delete();
        DB::table('stock_batches')->delete();

        $products = DB::table('products')->get();

        foreach ($products as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'opening_stock' => 0,
                    'total_qty' => 0,
                    'total_value' => 0,
                    'avg_cost' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('product_stocks')->updateOrInsert(
                [
                    'product_id' => $product->id,
                    'company_id' => $product->company_id,
                    'business_unit_id' => $product->comp_unit_id,
                    'work_point_id' => $product->work_point_id,
                ],
                [
                    'current_stock' => 0,
                    'minimum_stock' => $product->reorder_level ?? 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (DB::getSchemaBuilder()->hasTable('raw_material_stocks')) {
            DB::table('raw_material_stocks')->delete();

            $materials = DB::table('raw_materials')->get();

            foreach ($materials as $material) {
                DB::table('raw_material_stocks')->insert([
                    'company_id' => $material->company_id,
                    'comp_unit_id' => $material->comp_unit_id,
                    'work_point_id' => $material->work_point_id,
                    'raw_material_id' => $material->id,
                    'qty_in' => 0,
                    'qty_out' => 0,
                    'balance' => 0,
                    'unit_price' => 0,
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command->info('Production stock initialized at zero. No fake opening stock inserted.');
    }
}