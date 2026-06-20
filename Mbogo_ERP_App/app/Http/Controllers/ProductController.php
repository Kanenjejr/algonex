<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;
use App\Models\Company_unit;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\WorkPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RealRashid\SweetAlert\Facades\Alert;

class ProductController extends Controller
{
    private function activeStatusValue(): string
    {
        return 'Active';
    }

    private function deletedStatusValue(): string
    {
        return 'Deleted';
    }

    private function getAccounts()
    {
        return DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->orderBy('SubCode')
            ->get();
    }

    private function getCompanyIdFromRequest(Request $request): ?int
    {
        if ($request->filled('company_id')) {
            return (int) $request->company_id;
        }

        if ($request->filled('comp_unit_id')) {
            $unit = Company_unit::find($request->comp_unit_id);

            if ($unit) {
                return $unit->company_id;
            }
        }

        if ($request->filled('work_point_id')) {
            $workPoint = WorkPoint::find($request->work_point_id);

            if ($workPoint) {
                return $workPoint->company_id;
            }
        }

        return auth()->user()->company_id ?? session('company_id') ?? null;
    }

    private function getCompUnitIdFromRequest(Request $request): ?int
    {
        if ($request->filled('comp_unit_id')) {
            return (int) $request->comp_unit_id;
        }

        if ($request->filled('work_point_id')) {
            $workPoint = WorkPoint::find($request->work_point_id);

            if ($workPoint) {
                return $workPoint->comp_unit_id;
            }
        }

        return auth()->user()->comp_unit_id
            ?? auth()->user()->company_unit_id
            ?? null;
    }

    private function getWorkPointIdFromRequest(Request $request): ?int
    {
        return $request->filled('work_point_id')
            ? (int) $request->work_point_id
            : (auth()->user()->work_point_id ?? null);
    }

    private function createOpeningStockLedger(Product $product): void
    {
        $openingStock = (float) ($product->opening_stock ?? 0);

        if ($openingStock <= 0) {
            return;
        }

        StockLedger::create([
            'product_id' => $product->id,
            'type' => 'IN',
            'transaction_type' => 'purchase',
            'qty_in' => $openingStock,
            'qty_out' => 0,
            'balance' => $openingStock,
            'unit_cost' => (float) ($product->avg_cost ?? 0),
            'total_value' => $openingStock * (float) ($product->avg_cost ?? 0),
            'total_cost' => $openingStock * (float) ($product->avg_cost ?? 0),
            'account_code' => $product->inventory_account_code,
            'reference_type' => 'opening_stock',
            'reference_id' => $product->id,
            'description' => 'Opening stock for ' . $product->product_name,
            'company_id' => $product->company_id,
            'company_unit_id' => $product->comp_unit_id,
            'work_point_id' => $product->work_point_id,
            'date' => now(),
        ]);
    }

    private function syncProductStock(Product $product): void
    {
        if (!Schema::hasTable('product_stocks')) {
            return;
        }

        DB::table('product_stocks')->updateOrInsert(
            [
                'product_id' => $product->id,
                'work_point_id' => $product->work_point_id,
            ],
            [
                'company_id' => $product->company_id,
                'business_unit_id' => $product->comp_unit_id,
                'current_stock' => (float) ($product->opening_stock ?? 0),
                'minimum_stock' => (float) ($product->reorder_level ?? 10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function index(Request $request)
    {
        $products = Product::query()
        ->with(['company', 'businessUnit', 'workPoint'])
        ->where('status', '!=', $this->deletedStatusValue());

    if ($request->filled('search')) {

        $search = trim($request->search);

        $products->where(function ($q) use ($search) {

            $q->where('product_name', 'LIKE', '%' . $search . '%')
            ->orWhere('product_size', 'LIKE', '%' . $search . '%');

        });
    }

        $products = $products->latest()->get();

        $stockData = StockLedger::select(
                'product_id',
                DB::raw('SUM(qty_in) as total_in'),
                DB::raw('SUM(qty_out) as total_out')
            )
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        foreach ($products as $product) {
            $stockIn = (float) ($stockData[$product->id]->total_in ?? 0);
            $stockOut = (float) ($stockData[$product->id]->total_out ?? 0);

            $product->stock_in = $stockIn;
            $product->stock_out = $stockOut;
            $product->current_stock = (float) ($product->opening_stock ?? 0) + $stockIn - $stockOut;
            $product->product_code = 'PRD-' . str_pad($product->id, 5, '0', STR_PAD_LEFT);
        }

        $companies = CompanySite::where('status', '!=', $this->deletedStatusValue())
            ->orderBy('company_name')
            ->get();

        $businessUnits = Company_unit::with('company')
            ->where('status', '!=', $this->deletedStatusValue())
            ->orderBy('unit_name')
            ->get();

        $workPoints = WorkPoint::with(['company', 'comp_unit'])
            ->where('status', '!=', $this->deletedStatusValue())
            ->orderBy('work_name')
            ->get();

        $accounts = $this->getAccounts();

        return view('admin.products.index', compact(
            'products',
            'companies',
            'businessUnits',
            'workPoints',
            'accounts'
        ));
    }

    public function create()
    {
        $companies = CompanySite::where('status', '!=', $this->deletedStatusValue())
            ->orderBy('company_name')
            ->get();

        $businessUnits = Company_unit::with('company')
            ->where('status', '!=', $this->deletedStatusValue())
            ->orderBy('unit_name')
            ->get();

        $workPoints = WorkPoint::with(['company', 'comp_unit'])
            ->where('status', '!=', $this->deletedStatusValue())
            ->orderBy('work_name')
            ->get();

        $accounts = $this->getAccounts();

        return view('admin.products.create', compact(
            'companies',
            'businessUnits',
            'workPoints',
            'accounts'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'product_size' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:company_sites,id',
            'comp_unit_id' => 'nullable|exists:company_units,id',
            'work_point_id' => 'nullable|exists:work_points,id',
            'opening_stock' => 'nullable|numeric|min:0',
            'avg_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'inventory_account_code' => 'nullable|string|max:20',
            'cogs_account_code' => 'nullable|string|max:20',
            'revenue_account_code' => 'nullable|string|max:20',
            'status' => 'required|in:Active,Inactive',
        ]);

        DB::beginTransaction();

        try {
            $openingStock = (float) ($request->opening_stock ?? 0);
            $avgCost = (float) ($request->avg_cost ?? 0);

            $product = Product::create([
                'user_id' => auth()->id(),
                'company_id' => $this->getCompanyIdFromRequest($request),
                'comp_unit_id' => $this->getCompUnitIdFromRequest($request),
                'work_point_id' => $this->getWorkPointIdFromRequest($request),

                'product_name' => $request->product_name,
                'product_size' => $request->product_size,

                'avg_cost' => $avgCost,
                'total_qty' => $openingStock,
                'total_value' => $openingStock * $avgCost,
                'selling_price' => (float) ($request->selling_price ?? 0),
                'reorder_level' => (float) ($request->reorder_level ?? 10),
                'opening_stock' => $openingStock,

                'inventory_account_code' => $request->inventory_account_code,
                'cogs_account_code' => $request->cogs_account_code,
                'revenue_account_code' => $request->revenue_account_code,

                'status' => $request->status,
            ]);

            $this->createOpeningStockLedger($product);
            $this->syncProductStock($product);

            DB::commit();

            Alert::success('Success', 'Product created successfully');
            return redirect()->route('products.index');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Product store failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', 'Failed to create product: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function show($id)
{
    $product = Product::findOrFail($id);

    $products = Product::with([
        'company',
        'businessUnit',
        'workPoint'
    ])
    ->where('status','!=','Deleted')
    ->orderBy('product_name')
    ->get();

    foreach($products as $item){

        $stock = StockLedger::select(
            DB::raw('SUM(qty_in) as total_in'),
            DB::raw('SUM(qty_out) as total_out')
        )
        ->where('product_id',$item->id)
        ->first();

        $item->stock_in = (float)($stock->total_in ?? 0);
        $item->stock_out = (float)($stock->total_out ?? 0);

        $item->current_stock =
            (float)($item->opening_stock ?? 0)
            + $item->stock_in
            - $item->stock_out;
    }

    return view(
        'admin.products.show',
        compact('product','products')
    );
}

   public function edit($id)
{
    $product = Product::findOrFail($id);

    return response()->json([
        'id' => $product->id,
        'product_name' => $product->product_name,
        'product_size' => $product->product_size,
        'company_id' => $product->company_id,
        'comp_unit_id' => $product->comp_unit_id,
        'work_point_id' => $product->work_point_id,
        'opening_stock' => $product->opening_stock,
        'avg_cost' => $product->avg_cost,
        'selling_price' => $product->selling_price,
        'reorder_level' => $product->reorder_level,
        'inventory_account_code' => $product->inventory_account_code,
        'cogs_account_code' => $product->cogs_account_code,
        'revenue_account_code' => $product->revenue_account_code,
        'status' => $product->status
    ]);
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'product_size' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:company_sites,id',
            'comp_unit_id' => 'nullable|exists:company_units,id',
            'work_point_id' => 'nullable|exists:work_points,id',
            'avg_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'inventory_account_code' => 'nullable|string|max:20',
            'cogs_account_code' => 'nullable|string|max:20',
            'revenue_account_code' => 'nullable|string|max:20',
            'status' => 'required|in:Active,Inactive',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            $product->update([
                'company_id' => $this->getCompanyIdFromRequest($request),
                'comp_unit_id' => $this->getCompUnitIdFromRequest($request),
                'work_point_id' => $this->getWorkPointIdFromRequest($request),

                'product_name' => $request->product_name,
                'product_size' => $request->product_size,

                'avg_cost' => (float) ($request->avg_cost ?? 0),
                'selling_price' => (float) ($request->selling_price ?? 0),
                'reorder_level' => (float) ($request->reorder_level ?? 10),

                'inventory_account_code' => $request->inventory_account_code,
                'cogs_account_code' => $request->cogs_account_code,
                'revenue_account_code' => $request->revenue_account_code,

                'status' => $request->status,
            ]);

            DB::commit();

            Alert::success('Success', 'Product updated successfully');
            return redirect()->route('products.index');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Product update failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', 'Failed to update product: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            $product->update([
                'status' => $this->deletedStatusValue(),
            ]);

            Alert::success('Success', 'Product deleted successfully');
            return back();
        } catch (\Throwable $e) {
            Log::error('Product delete failed', [
                'message' => $e->getMessage(),
            ]);

            Alert::error('Error', 'Failed to delete product: ' . $e->getMessage());
            return back();
        }
    }

    public function print($id)
    {
        $product = Product::with(['company', 'businessUnit', 'workPoint'])->findOrFail($id);

        return view('admin.products.print', compact('product'));
    }

    public function export()
    {
        $products = Product::with(['company', 'businessUnit', 'workPoint'])
            ->where('status', '!=', $this->deletedStatusValue())
            ->orderBy('product_name')
            ->get();

        return response()->streamDownload(function () use ($products) {
            echo "Product Code,Product Name,Details,Company,Company Unit,Work Point,Opening Stock,Average Cost,Selling Price,Reorder Level,Inventory Account,COGS Account,Revenue Account,Status\n";

            foreach ($products as $product) {
                echo '"' . ('PRD-' . str_pad($product->id, 5, '0', STR_PAD_LEFT)) . '",';
                echo '"' . str_replace('"', '""', $product->product_name) . '",';
                echo '"' . str_replace('"', '""', $product->product_size ?? '') . '",';
                echo '"' . str_replace('"', '""', optional($product->company)->company_name ?? '') . '",';
                echo '"' . str_replace('"', '""', optional($product->businessUnit)->unit_name ?? '') . '",';
                echo '"' . str_replace('"', '""', optional($product->workPoint)->work_name ?? '') . '",';
                echo '"' . number_format((float) $product->opening_stock, 2, '.', '') . '",';
                echo '"' . number_format((float) $product->avg_cost, 2, '.', '') . '",';
                echo '"' . number_format((float) $product->selling_price, 2, '.', '') . '",';
                echo '"' . number_format((float) $product->reorder_level, 2, '.', '') . '",';
                echo '"' . ($product->inventory_account_code ?? '') . '",';
                echo '"' . ($product->cogs_account_code ?? '') . '",';
                echo '"' . ($product->revenue_account_code ?? '') . '",';
                echo '"' . ($product->status ?? '') . '"' . "\n";
            }
        }, 'products.csv');
    }

    public function getBanks($company_id)
    {
        $banks = DB::table('accnt_subcharts')
            ->where(function ($query) use ($company_id) {
                $query->where('company_id', $company_id)
                    ->where('SubDescription', 'LIKE', '%Bank%');
            })
            ->orWhereIn('SubDescription', [
                'CRDB Bank Plc',
                'NMB Bank Plc',
                'DTB Bank',
                'HABIB Bank',
            ])
            ->select('id', 'SubCode', 'SubDescription')
            ->get();

        return response()->json($banks);
    }
}