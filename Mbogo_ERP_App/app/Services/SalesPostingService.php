<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\PrdStock;
use App\Models\StockLedger;
use App\Models\AccntTransaction;

class SalesPostingService
{
    public static function post($salesOrder)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | SALES TYPE CONFIGURATION
            |--------------------------------------------------------------------------
            */

            $affectStock = SalesTypeResolver::affectStock(
                $salesOrder->company_unit_id
            );

            $affectInventory = SalesTypeResolver::affectInventory(
                $salesOrder->company_unit_id
            );

            $affectCOGS = SalesTypeResolver::affectCOGS(
                $salesOrder->company_unit_id
            );

            /*
            |--------------------------------------------------------------------------
            | LOAD SALES ITEMS
            |--------------------------------------------------------------------------
            */

            $items = SalesOrderItem::where(
                'sales_order_id',
                $salesOrder->id
            )->get();

            /*
            |--------------------------------------------------------------------------
            | STOCK VALIDATION
            |--------------------------------------------------------------------------
            */

            $validationItems = [];

            foreach ($items as $item) {

                $validationItems[] = [

                    'product_id' =>
                        $item->product_id,

                    'qty' =>
                        $item->qty,

                    'company_id' =>
                        $item->company_id,

                    'work_point_id' =>
                        $item->work_point_id,
                ];
            }

            $errors = SalesTransactionValidator::validate(
                $salesOrder->company_unit_id,
                $validationItems
            );

            if (count($errors) > 0) {

                DB::rollBack();

                return [
                    'status' => false,
                    'errors' => $errors
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | PROCESS ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($items as $item) {

                /*
                |--------------------------------------------------------------------------
                | STOCK DEDUCTION
                |--------------------------------------------------------------------------
                */

                if ($affectStock) {

                    $stock = PrdStock::where(
                        'prd_id',
                        $item->product_id
                    )
                    ->where(
                        'company_id',
                        $item->company_id
                    )
                    ->where(
                        'work_point_id',
                        $item->work_point_id
                    )
                    ->first();

                    if ($stock) {

                        $stock->avlb_qnty =
                            $stock->avlb_qnty - $item->qty;

                        $stock->issd_qnty =
                            $stock->issd_qnty + $item->qty;

                        $stock->save();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | STOCK LEDGER
                |--------------------------------------------------------------------------
                */

                if ($affectInventory) {

                    StockLedger::create([

                        'product_id' =>
                            $item->product_id,

                        'type' =>
                            'OUT',

                        'qty_in' =>
                            0,

                        'qty_out' =>
                            $item->qty,

                        'balance' =>
                            0,

                        'account_code' =>
                            $item->account_code,

                        'reference_type' =>
                            'SALES_ORDER',

                        'reference_id' =>
                            $salesOrder->id,

                        'description' =>
                            'Sales Order Posting',

                        'company_id' =>
                            $item->company_id,

                        'work_point_id' =>
                            $item->work_point_id,

                        'warehouse_id' =>
                            $item->warehouse_id,

                        'company_unit_id' =>
                            $item->business_unit_id,

                        'date' =>
                            now(),

                        'transaction_type' =>
                            'SALE',

                        'unit_cost' =>
                            $item->cost_price,

                        'total_value' =>
                            $item->total,

                        'total_cost' =>
                            ($item->cost_price * $item->qty),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | SALES REVENUE ENTRY
                |--------------------------------------------------------------------------
                */

                AccntTransaction::create([

                    'transaction_group' =>
                        'SALES',

                    'trans_date' =>
                        now(),

                    'reference' =>
                        $salesOrder->order_no,

                    'category' =>
                        'Sales Revenue',

                    'currency' =>
                        $salesOrder->currency ?? 'TZS',

                    'exchange_rate' =>
                        1,

                    'memo' =>
                        'Sales Revenue Posting',

                    'payee' =>
                        $salesOrder->customer_id,

                    'user_id' =>
                        $salesOrder->created_by,

                    'company_id' =>
                        $salesOrder->company_id,

                    'work_point_id' =>
                        $salesOrder->work_point_id,

                    'account_id' =>
                        null,

                    'sub_account_id' =>
                        null,

                    'department_id' =>
                        null,

                    'section_id' =>
                        null,

                    'type' =>
                        'CR',

                    'amount' =>
                        $item->total,

                    'source_amount' =>
                        $item->total,

                    'imported_from_excel' =>
                        0,

                    'Status' =>
                        'POSTED',
                ]);

                /*
                |--------------------------------------------------------------------------
                | COGS ENTRY
                |--------------------------------------------------------------------------
                */

                if ($affectCOGS) {

                    AccntTransaction::create([

                        'transaction_group' =>
                            'SALES',

                        'trans_date' =>
                            now(),

                        'reference' =>
                            $salesOrder->order_no,

                        'category' =>
                            'Cost Of Goods Sold',

                        'currency' =>
                            $salesOrder->currency ?? 'TZS',

                        'exchange_rate' =>
                            1,

                        'memo' =>
                            'COGS Posting',

                        'payee' =>
                            $salesOrder->customer_id,

                        'user_id' =>
                            $salesOrder->created_by,

                        'company_id' =>
                            $salesOrder->company_id,

                        'work_point_id' =>
                            $salesOrder->work_point_id,

                        'account_id' =>
                            null,

                        'sub_account_id' =>
                            null,

                        'department_id' =>
                            null,

                        'section_id' =>
                            null,

                        'type' =>
                            'DR',

                        'amount' =>
                            ($item->cost_price * $item->qty),

                        'source_amount' =>
                            ($item->cost_price * $item->qty),

                        'imported_from_excel' =>
                            0,

                        'Status' =>
                            'POSTED',
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | INVENTORY REDUCTION
                    |--------------------------------------------------------------------------
                    */

                    AccntTransaction::create([

                        'transaction_group' =>
                            'SALES',

                        'trans_date' =>
                            now(),

                        'reference' =>
                            $salesOrder->order_no,

                        'category' =>
                            'Inventory Reduction',

                        'currency' =>
                            $salesOrder->currency ?? 'TZS',

                        'exchange_rate' =>
                            1,

                        'memo' =>
                            'Inventory Reduction Posting',

                        'payee' =>
                            $salesOrder->customer_id,

                        'user_id' =>
                            $salesOrder->created_by,

                        'company_id' =>
                            $salesOrder->company_id,

                        'work_point_id' =>
                            $salesOrder->work_point_id,

                        'account_id' =>
                            null,

                        'sub_account_id' =>
                            null,

                        'department_id' =>
                            null,

                        'section_id' =>
                            null,

                        'type' =>
                            'CR',

                        'amount' =>
                            ($item->cost_price * $item->qty),

                        'source_amount' =>
                            ($item->cost_price * $item->qty),

                        'imported_from_excel' =>
                            0,

                        'Status' =>
                            'POSTED',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE SALES ORDER STATUS
            |--------------------------------------------------------------------------
            */

            $salesOrder->status = 'POSTED';

            $salesOrder->save();

            DB::commit();

            return [

                'status' => true,

                'message' =>
                    'Sales Posted Successfully'
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            return [

                'status' => false,

                'message' =>
                    $e->getMessage()
            ];
        }
    }
}