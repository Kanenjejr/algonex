<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PosSalesManagementController extends Controller
{
    public function pos()
    {
        $user = auth()->user();

        $products = DB::table('products')
            ->join('product_stocks', function ($j) use ($user) {
                $j->on('products.id', '=', 'product_stocks.product_id')
                  ->where('product_stocks.company_id', $user->company_id)
                  ->where('product_stocks.business_unit_id', $user->business_unit_id)
                  ->where('product_stocks.work_point_id', $user->work_point_id);
            })
            ->select(
                'products.id',
                'products.product_name',
                'products.selling_price as price',
                'products.cost_price', 
                'product_stocks.current_stock',
                'product_stocks.minimum_stock'
            )
            ->get();

        $alerts = $products->where('current_stock', '<=', 'minimum_stock');

        return view('admin.store.sales_management.pos', compact('products','alerts'));
    }

    private function totals($cart)
    {
        $sub = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $vat = $sub * 0.18;

        return [
            'items' => array_values($cart),
            'sub_total' => $sub,
            'vat' => $vat,
            'total' => $sub + $vat
        ];
    }

    public function getCart()
    {
        return response()->json($this->totals(Session::get('cart', [])));
    }

    public function addToCart(Request $r)
    {
        $cart = Session::get('cart', []);

        $p = DB::table('products')->where('id', $r->product_id)->first();

        if (!$p) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $stock = DB::table('product_stocks')
            ->where('product_id', $p->id)
            ->value('current_stock');

        if (isset($cart[$p->id])) {
            if ($cart[$p->id]['qty'] + 1 > $stock) {
                return response()->json(['error' => 'Stock not enough'], 400);
            }
            $cart[$p->id]['qty']++;
        } else {
            if ($stock <= 0) {
                return response()->json(['error' => 'Out of stock'], 400);
            }

            $cart[$p->id] = [
                'id' => $p->id,
                'name' => $p->product_name,
                'price' => $p->selling_price,
                'cost' => $p->cost_price ?? 0, 
                'qty' => 1
            ];
        }

        Session::put('cart', $cart);

        return response()->json($this->totals($cart));
    }

    public function removeFromCart(Request $r)
    {
        $cart = Session::get('cart', []);
        unset($cart[$r->product_id]);

        Session::put('cart', $cart);

        return response()->json($this->totals($cart));
    }

    public function clearCart()
    {
        Session::forget('cart');
        return response()->json(['success' => true]);
    }

    public function checkout(Request $r)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return response()->json(['error' => 'Cart empty'], 400);
        }

        $user = auth()->user();

        $totals = $this->totals($cart);

        $discount = $r->discount ?? 0;
        $final_total = $totals['total'] - $discount;

        $paid = $r->paid ?? 0;
        $paid_from = $r->paid_from;
        $balance = $final_total - $paid;

        $sale_date = $r->sale_date ?? now();
        $currency = $r->currency ?? 'TZS';
        $exchange_rate = $r->exchange_rate ?? 1;

        DB::beginTransaction();

        try {

            $total_cost = 0;

            $saleId = DB::table('pos_sales')->insertGetId([
                'company_id' => $user->company_id,
                'business_unit_id' => $user->business_unit_id,
                'work_point_id' => $user->work_point_id,

                'sub_total' => $totals['sub_total'],
                'tax' => $totals['vat'],
                'discount' => $discount, 
                'total' => $final_total,

                'paid' => $paid,
                'paid_from' => $paid_from,
                'balance' => $balance,

                'currency' => $currency,
                'exchange_rate' => $exchange_rate,
                'sale_date' => $sale_date,

                'created_at' => now()
            ]);

            foreach ($cart as $item) {

                $cost = ($item['cost'] ?? 0) * $item['qty'];
                $total_cost += $cost;

                DB::table('pos_sale_items')->insert([
                    'pos_sale_id' => $saleId,
                    'product_id' => $item['id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'cost' => $item['cost'] ?? 0, 
                    'total' => $item['qty'] * $item['price'],
                    'created_at' => now()
                ]);

                DB::table('product_stocks')
                    ->where('product_id', $item['id'])
                    ->where('company_id', $user->company_id)
                    ->where('business_unit_id', $user->business_unit_id)
                    ->where('work_point_id', $user->work_point_id)
                    ->decrement('current_stock', $item['qty']);
            }

            $profit = $totals['sub_total'] - $total_cost;

            DB::table('pos_sales')
                ->where('id', $saleId)
                ->update([
                    'cost_total' => $total_cost,
                    'profit' => $profit
                ]);

            // ACCOUNTING
            DB::table('journal_entries')->insert([

                [
                    'account' => $paid_from,
                    'type' => 'DEBIT',
                    'amount' => $final_total,
                    'reference_id' => $saleId,
                    'created_at' => now()
                ],

                [
                    'account' => 'SALES_REVENUE',
                    'type' => 'CREDIT',
                    'amount' => $totals['sub_total'],
                    'reference_id' => $saleId,
                    'created_at' => now()
                ],

                [
                    'account' => 'COGS',
                    'type' => 'DEBIT',
                    'amount' => $total_cost,
                    'reference_id' => $saleId,
                    'created_at' => now()
                ],

                [
                    'account' => 'INVENTORY',
                    'type' => 'CREDIT',
                    'amount' => $total_cost,
                    'reference_id' => $saleId,
                    'created_at' => now()
                ]

            ]);

            Session::forget('cart');

            DB::commit();

            return response()->json([
                'success' => true,
                'receipt' => route('pos.receipt', $saleId)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

public function receipt($id)
    {
        $sale = DB::table('pos_sales')->where('id', $id)->first();

        $items = DB::table('pos_sale_items')
            ->join('products', 'products.id', '=', 'pos_sale_items.product_id')
            ->where('pos_sale_id', $id)
            ->select('products.product_name', 'qty', 'price', 'total')
            ->get();

        return view('admin.store.sales_management.receipt', compact('sale', 'items'));
    }
public function search(Request $request)
{
    $user = auth()->user();

    $query = trim(
        $request->get('query')
    );

    if(empty($query)){

        return response()->json([]);

    }

$products = DB::table('products')

    ->join(
        'product_stocks',
        'products.id',
        '=',
        'product_stocks.product_id'
    )

    ->where(
        'product_stocks.company_id',
        auth()->user()->company_id
    )

    ->where(
        'product_stocks.business_unit_id',
        auth()->user()->business_unit_id
    )

    ->where(
        'product_stocks.work_point_id',
        auth()->user()->work_point_id
    )

    ->select(

        'products.id',

        'products.product_name',

        'products.selling_price as price',

        'product_stocks.current_stock',

        'product_stocks.stock_in',

        'product_stocks.stock_out'

    )

    ->get();
}
}