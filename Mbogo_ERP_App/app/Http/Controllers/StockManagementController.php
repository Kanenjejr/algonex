<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAudit;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\WorkPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class StockManagementController extends Controller
{
    private function currentUser()
    {
        return auth()->user();
    }

    private function currentWorkPointId(): ?int
    {
        return auth()->user()->work_point_id ?? session('work_point_id');
    }

    private function currentCompanyId(): ?int
    {
        return auth()->user()->company_id ?? null;
    }

    private function currentCompanyUnitId(): ?int
    {
        return auth()->user()->comp_unit_id
            ?? auth()->user()->company_unit_id
            ?? auth()->user()->comp_unit_id
            ?? null;
    }

    private function resolveId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        try {
            return (int) decrypt($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sweetValidationBack($validator)
    {
        Alert::error('Validation Error', 'Please check the form and correct the highlighted fields.');
        return back()->withErrors($validator)->withInput();
    }

    private function sweetErrorBack(string $message)
    {
        Alert::error('Error', $message);
        return back()->withInput();
    }

    private function sweetSuccessBack(string $message)
    {
        Alert::success('Success', $message);
        return back();
    }

    private function productQuery()
    {
        return Product::query()
            ->where(function ($q) {
                $q->where('status', '!=', 'Deleted')
                ->orWhereNull('status');
            });
    }

    private function workPointQuery()
    {
        $companyId = $this->currentCompanyId();

        return WorkPoint::with('company')
            ->when($companyId, function ($q) use ($companyId) {
                $q->where(function ($qq) use ($companyId) {
                    $qq->where('company_id', $companyId)
                        ->orWhereNull('company_id');
                });
            })
            ->where('status', '!=', 'Deleted');
    }

    private function findCompanyProductOrFail($id): Product
    {
        $productId = $this->resolveId($id);

        if (!$productId) {
            abort(403, 'Invalid product identifier.');
        }

        $product = $this->productQuery()
            ->where('id', $productId)
            ->first();

        if (!$product) {
            abort(403, 'Product not found or unauthorized.');
        }

        return $product;
    }

    private function findCompanyWorkPointOrFail($id): WorkPoint
    {
        $workPointId = $this->resolveId($id);

        if (!$workPointId) {
            abort(403, 'Invalid work point identifier.');
        }

        $workPoint = $this->workPointQuery()
            ->where('id', $workPointId)
            ->first();

        if (!$workPoint) {
            abort(403, 'Work point not found or unauthorized.');
        }

        return $workPoint;
    }

    private function getStockRecord($productId, $companyId = null, $workPointId = null)
    {
        return DB::table('product_stocks')
            ->where('product_id', $productId)
            ->when($companyId, function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->when($workPointId, function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId);
            })
            ->first();
    }

    private function currentProductStock($productId, $companyId = null, $workPointId = null): float
    {
        $stock = $this->getStockRecord($productId, $companyId, $workPointId);

        return (float) ($stock->current_stock ?? 0);
    }

    private function upsertProductStock(
        Product $product,
        float $newStock,
        ?int $companyId = null,
        ?int $workPointId = null,
        ?int $unitId = null
    ): void {
        DB::table('product_stocks')->updateOrInsert(
            [
                'product_id' => $product->id,
                'work_point_id' => $workPointId,
            ],
            [
                'company_id' => $companyId ?? $product->company_id,
                'business_unit_id' => $unitId ?? $product->comp_unit_id ?? $this->currentCompanyUnitId(),
                'current_stock' => $newStock,
                'minimum_stock' => $product->reorder_level ?? 10,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function stockPageData(Request $request = null): array
{
    $request = $request ?: request();

    $companyId = $this->currentCompanyId();
    $workPointId = $this->currentWorkPointId();

    $companies = CompanySite::where('status', '!=', 'Deleted')
        ->orderBy('company_name')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT FIX
    |--------------------------------------------------------------------------
    | Product master list should NOT be filtered by auth company/work point here.
    | Your screen shows Total Products = 0, meaning the select is empty because
    | controller is returning no products.
    */
    $products = Product::query()
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('product_name')
        ->get();

    $workPoints = WorkPoint::with('company')
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('work_code')
        ->orderBy('work_name')
        ->get();

    $filterProductId = $this->resolveId($request->product_id);

    $ledgerQuery = StockLedger::with('product');

    if ($request->filled('from')) {
        $ledgerQuery->whereDate('date', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $ledgerQuery->whereDate('date', '<=', $request->to);
    }

    if ($filterProductId) {
        $ledgerQuery->where('product_id', $filterProductId);
    }

    if ($request->filled('type')) {
        if ($request->type === 'IN') {
            $ledgerQuery->where('qty_in', '>', 0);
        }

        if ($request->type === 'OUT') {
            $ledgerQuery->where('qty_out', '>', 0);
        }

        if ($request->type === 'SALE') {
            $ledgerQuery->where('reference_type', 'sale');
        }
    }

    $rows = $ledgerQuery
        ->orderByDesc('date')
        ->orderByDesc('id')
        ->get();

    $adjustments = StockAdjustment::with('product')
        ->orderByDesc('id')
        ->get();

    $itemStocks = DB::table('product_stocks')
        ->join('products', 'product_stocks.product_id', '=', 'products.id')
        ->select(
            'product_stocks.*',
            'products.product_name',
            'products.opening_stock',
            DB::raw('product_stocks.current_stock as total_available'),
            DB::raw('product_stocks.current_stock as total_received'),
            DB::raw('0 as total_used'),
            DB::raw('0 as total_damaged')
        )
        ->orderBy('products.product_name')
        ->get();

    $lowStockProducts = $products->filter(function ($product) {
        $stock = DB::table('product_stocks')
            ->where('product_id', $product->id)
            ->first();

        $current = (float) ($stock->current_stock ?? $product->total_qty ?? 0);
        $reorder = (float) ($product->reorder_level ?? 10);

        return $current <= $reorder;
    })->values();

    $movement = $products->map(function ($product) {
        $received = StockLedger::where('product_id', $product->id)
            ->sum('qty_in');

        $issued = StockLedger::where('product_id', $product->id)
            ->sum('qty_out');

        $opening = (float) ($product->opening_stock ?? 0);

        return [
            'item_name' => $product->product_name,
            'opening' => $opening,
            'received' => (float) $received,
            'issued' => (float) $issued,
            'closing' => $opening + (float) $received - (float) $issued,
        ];
    });

    $openingStock = (float) $products->sum('opening_stock');

    $receivedStock = (float) StockLedger::sum('qty_in');

    $issuedStock = (float) StockLedger::sum('qty_out');

    $totalStock = (float) DB::table('product_stocks')
        ->sum('current_stock');

    $stats = [
        'opening_stock' => $openingStock,
        'received' => $receivedStock,
        'issued' => $issuedStock,
        'closing_stock' => $openingStock + $receivedStock - $issuedStock,
        'low_stock' => $lowStockProducts->count(),
        'total_products' => $products->count(),
        'total_stock' => $totalStock,
        'inventory_qty' => $totalStock,
        'balance' => $totalStock,
    ];

    $chartData = StockLedger::select(
            DB::raw('DATE(date) as date'),
            DB::raw('SUM(qty_in) as total_in'),
            DB::raw('SUM(qty_out) as total_out')
        )
        ->groupBy(DB::raw('DATE(date)'))
        ->orderBy('date')
        ->get()
        ->map(function ($item) {
            return [
                'date' => $item->date,
                'total_in' => (float) $item->total_in,
                'total_out' => (float) $item->total_out,
            ];
        })
        ->values();

    $deliveries = class_exists(Delivery::class)
    ? Delivery::with(['customer','invoice'])->latest()->take(10)->get()
    : collect();

    $audits = StockAudit::latest()->get();

    return compact(
        'companies',
        'products',
        'workPoints',
        'rows',
        'adjustments',
        'lowStockProducts',
        'itemStocks',
        'movement',
        'stats',
        'chartData',
        'deliveries',
        'audits'
    );
}

    public function dashboard(Request $request)
    {
        try {
            $data = $this->stockPageData($request);

            return view('admin.store.stock.dashboard', $data);
        } catch (\Throwable $e) {
            Log::error('Stock dashboard failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', 'Failed to load stock dashboard: ' . $e->getMessage());
            return back();
        }
    }

    public function movement(Request $request)
    {
        return $this->dashboard($request);
    }

    public function module($module)
    {
        try {
            if ($module === 'movement') {
                return $this->dashboard(request());
            }

            if ($module === 'adjustment' || $module === 'adjust') {
                return $this->adjust();
            }

            if ($module === 'receive') {
                return $this->receive();
            }

            if ($module === 'stock-out') {
                return $this->stockOut();
            }

            $data = $this->stockPageData(request());

            return view('admin.store.stock.dashboard', $data);
        } catch (\Throwable $e) {
            Log::error('Stock module failed', [
                'module' => $module,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', 'Failed to load stock module: ' . $e->getMessage());
            return back();
        }
    }

    public function receive()
    {
        try {
            $data = $this->stockPageData(request());

            if (view()->exists('admin.store.stock.receive')) {
                return view('admin.store.stock.receive', $data);
            }

            return view('admin.store.stock.dashboard', $data);
        } catch (\Throwable $e) {
            Log::error('Receive stock page failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', 'Failed to load receive stock page: ' . $e->getMessage());
            return back();
        }
    }

    public function stockIn(Request $request)
    {
        return $this->receiveStore($request);
    }

    public function receiveStock(Request $request)
    {
        return $this->receiveStore($request);
    }

    public function receiveStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sweetValidationBack($validator);
        }

        DB::beginTransaction();

        try {
            $product = $this->findCompanyProductOrFail($request->product_id);

            $qty = (float) $request->qty;
            $unitCost = (float) $request->unit_cost;
            $companyId = $this->currentCompanyId() ?? $product->company_id;
            $unitId = $this->currentCompanyUnitId() ?? $product->comp_unit_id;
            $workPointId = $this->currentWorkPointId() ?? $product->work_point_id;

            $currentStock = $this->currentProductStock($product->id, $companyId, $workPointId);
            $newStock = $currentStock + $qty;

            $this->upsertProductStock($product, $newStock, $companyId, $workPointId, $unitId);

            StockBatch::create([
                'product_id' => $product->id,
                'batch_no' => 'BATCH-' . strtoupper(uniqid()),
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'source' => 'purchase',
                'company_id' => $companyId,
                'work_point_id' => $workPointId,
            ]);

            StockLedger::create([
                'product_id' => $product->id,
                'type' => 'IN',
                'transaction_type' => 'purchase',
                'qty_in' => $qty,
                'qty_out' => 0,
                'balance' => $newStock,
                'unit_cost' => $unitCost,
                'total_value' => $qty * $unitCost,
                'total_cost' => $qty * $unitCost,
                'account_code' => $product->inventory_account_code ?? null,
                'reference_type' => 'purchase',
                'reference_id' => null,
                'description' => $request->description ?? 'Stock received into store',
                'company_id' => $companyId,
                'company_unit_id' => $unitId,
                'work_point_id' => $workPointId,
                'date' => now(),
            ]);

            $oldQty = (float) ($product->total_qty ?? 0);
            $oldValue = (float) ($product->total_value ?? 0);
            $newTotalQty = $oldQty + $qty;
            $newTotalValue = $oldValue + ($qty * $unitCost);

            $product->update([
                'total_qty' => $newTotalQty,
                'total_value' => $newTotalValue,
                'avg_cost' => $newTotalQty > 0 ? ($newTotalValue / $newTotalQty) : 0,
            ]);

            DB::commit();

            Alert::success('Success', 'Stock received successfully.');
            return back();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Receive stock failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return $this->sweetErrorBack($e->getMessage());
        }
    }

    public function stockOut()
    {
        try {
            $data = $this->stockPageData(request());

            if (view()->exists('admin.store.stock.stock_out')) {
                return view('admin.store.stock.stock_out', $data);
            }

            return view('admin.store.stock.dashboard', $data);
        } catch (\Throwable $e) {
            Log::error('Stock out page failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', 'Failed to load stock out page: ' . $e->getMessage());
            return back();
        }
    }

    public function stockOutStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'qty' => ['nullable', 'numeric', 'min:0.01'],
            'quantity' => ['nullable', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sweetValidationBack($validator);
        }

        $qty = (float) ($request->qty ?? $request->quantity ?? 0);

        if ($qty <= 0) {
            Alert::error('Validation Error', 'Please enter a valid stock out quantity.');
            return back()->withInput();
        }

        DB::beginTransaction();

        try {
            $product = $this->findCompanyProductOrFail($request->product_id);

            $companyId = $this->currentCompanyId() ?? $product->company_id;
            $unitId = $this->currentCompanyUnitId() ?? $product->comp_unit_id;
            $workPointId = $this->currentWorkPointId() ?? $product->work_point_id;

            $currentStock = $this->currentProductStock($product->id, $companyId, $workPointId);

            if ($qty > $currentStock) {
                DB::rollBack();
                return $this->sweetErrorBack('Insufficient stock. Current stock is ' . number_format($currentStock, 2));
            }

            $newStock = $currentStock - $qty;

            $this->upsertProductStock($product, $newStock, $companyId, $workPointId, $unitId);

            StockLedger::create([
                'product_id' => $product->id,
                'type' => 'OUT',
                'transaction_type' => 'issue',
                'qty_in' => 0,
                'qty_out' => $qty,
                'balance' => $newStock,
                'unit_cost' => $product->avg_cost ?? 0,
                'total_value' => $qty * (float) ($product->avg_cost ?? 0),
                'total_cost' => $qty * (float) ($product->avg_cost ?? 0),
                'account_code' => $product->inventory_account_code ?? null,
                'reference_type' => 'STOCK-OUT',
                'reference_id' => null,
                'description' => $request->description ?? 'Stock issued from store',
                'company_id' => $companyId,
                'company_unit_id' => $unitId,
                'work_point_id' => $workPointId,
                'date' => now(),
            ]);

            $product->update([
                'total_qty' => max(0, (float) ($product->total_qty ?? 0) - $qty),
            ]);

            DB::commit();

            Alert::success('Success', 'Stock out recorded successfully.');
            return back();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Stock out failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return $this->sweetErrorBack($e->getMessage());
        }
    }

    public function stockMovement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'type' => ['required', Rule::in(['in', 'out', 'IN', 'OUT'])],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sweetValidationBack($validator);
        }

        $type = strtolower($request->type);

        if ($type === 'in') {
            $request->merge([
                'qty' => $request->quantity,
                'unit_cost' => $request->unit_cost ?? 0,
            ]);

            return $this->receiveStore($request);
        }

        $request->merge([
            'qty' => $request->quantity,
        ]);

        return $this->stockOutStore($request);
    }

    public function adjust()
    {
        try {
            $data = $this->stockPageData(request());

            if (view()->exists('admin.store.stock.adjust')) {
                return view('admin.store.stock.adjust', $data);
            }

            return view('admin.store.stock.dashboard', $data);
        } catch (\Throwable $e) {
            Log::error('Adjustment page failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', 'Failed to load adjustment page: ' . $e->getMessage());
            return back();
        }
    }

    public function adjustmentIndex()
    {
        return $this->adjust();
    }

    public function adjustStockSave(Request $request)
    {
        return $this->adjustStore($request);
    }

    public function storeAdjustment(Request $request)
    {
        return $this->adjustStore($request);
    }

    public function adjustStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'type' => ['required', Rule::in(['increase', 'decrease', 'loss', 'gain'])],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sweetValidationBack($validator);
        }

        DB::beginTransaction();

        try {
            $product = $this->findCompanyProductOrFail($request->product_id);

            $rawType = $request->type;
            $type = in_array($rawType, ['increase', 'gain']) ? 'increase' : 'decrease';

            $qty = (float) $request->qty;
            $companyId = $this->currentCompanyId() ?? $product->company_id;
            $unitId = $this->currentCompanyUnitId() ?? $product->comp_unit_id;
            $workPointId = $this->currentWorkPointId() ?? $product->work_point_id;

            $currentStock = $this->currentProductStock($product->id, $companyId, $workPointId);

            if ($type === 'increase') {
                $newStock = $currentStock + $qty;
                $qtyIn = $qty;
                $qtyOut = 0;
                $movementType = 'IN';
            } else {
                if ($qty > $currentStock) {
                    DB::rollBack();
                    return $this->sweetErrorBack('Insufficient stock quantity.');
                }

                $newStock = $currentStock - $qty;
                $qtyIn = 0;
                $qtyOut = $qty;
                $movementType = 'OUT';
            }

            $this->upsertProductStock($product, $newStock, $companyId, $workPointId, $unitId);

            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'qty' => $qty,
                'type' => $type,
                'reason' => $request->reason ?? 'Stock adjustment',
            ]);

            StockLedger::create([
                'product_id' => $product->id,
                'type' => $movementType,
                'transaction_type' => 'adjustment',
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'balance' => $newStock,
                'unit_cost' => $product->avg_cost ?? 0,
                'total_value' => $qty * (float) ($product->avg_cost ?? 0),
                'total_cost' => $qty * (float) ($product->avg_cost ?? 0),
                'account_code' => $product->inventory_account_code ?? null,
                'reference_type' => 'ADJUSTMENT',
                'reference_id' => $adjustment->id,
                'description' => $request->reason ?? 'Stock adjustment',
                'company_id' => $companyId,
                'company_unit_id' => $unitId,
                'work_point_id' => $workPointId,
                'date' => now(),
            ]);

            if ($type === 'increase') {
                $product->update([
                    'total_qty' => (float) ($product->total_qty ?? 0) + $qty,
                ]);
            } else {
                $product->update([
                    'total_qty' => max(0, (float) ($product->total_qty ?? 0) - $qty),
                ]);
            }

            DB::commit();

            Alert::success('Success', 'Stock adjustment saved successfully.');
            return back();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Stock adjustment failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return $this->sweetErrorBack($e->getMessage());
        }
    }

    public function transferStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'from_work_point' => ['required'],
            'to_work_point' => ['required'],
            'qty' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($validator->fails()) {
            return $this->sweetValidationBack($validator);
        }

        DB::beginTransaction();

        try {
            $product = $this->findCompanyProductOrFail($request->product_id);
            $fromWorkPoint = $this->findCompanyWorkPointOrFail($request->from_work_point);
            $toWorkPoint = $this->findCompanyWorkPointOrFail($request->to_work_point);

            if ((int) $fromWorkPoint->id === (int) $toWorkPoint->id) {
                DB::rollBack();
                return $this->sweetErrorBack('Source and destination work points cannot be the same.');
            }

            $qty = (float) $request->qty;
            $companyId = $this->currentCompanyId() ?? $product->company_id;
            $unitCost = (float) ($product->avg_cost ?? 0);

            $fromCurrent = $this->currentProductStock($product->id, $companyId, $fromWorkPoint->id);

            if ($qty > $fromCurrent) {
                DB::rollBack();
                return $this->sweetErrorBack('Not enough stock in source warehouse.');
            }

            $toCurrent = $this->currentProductStock($product->id, $companyId, $toWorkPoint->id);

            $fromBalance = $fromCurrent - $qty;
            $toBalance = $toCurrent + $qty;

            $this->upsertProductStock($product, $fromBalance, $companyId, $fromWorkPoint->id, $fromWorkPoint->comp_unit_id);
            $this->upsertProductStock($product, $toBalance, $companyId, $toWorkPoint->id, $toWorkPoint->comp_unit_id);

            StockLedger::create([
                'product_id' => $product->id,
                'type' => 'OUT',
                'transaction_type' => 'transfer',
                'qty_in' => 0,
                'qty_out' => $qty,
                'balance' => $fromBalance,
                'unit_cost' => $unitCost,
                'total_value' => $qty * $unitCost,
                'total_cost' => $qty * $unitCost,
                'account_code' => $product->inventory_account_code ?? null,
                'reference_type' => 'transfer_out',
                'reference_id' => null,
                'description' => 'Transfer to ' . $toWorkPoint->work_name,
                'company_id' => $companyId,
                'company_unit_id' => $fromWorkPoint->comp_unit_id,
                'work_point_id' => $fromWorkPoint->id,
                'date' => now(),
            ]);

            StockLedger::create([
                'product_id' => $product->id,
                'type' => 'IN',
                'transaction_type' => 'transfer',
                'qty_in' => $qty,
                'qty_out' => 0,
                'balance' => $toBalance,
                'unit_cost' => $unitCost,
                'total_value' => $qty * $unitCost,
                'total_cost' => $qty * $unitCost,
                'account_code' => $product->inventory_account_code ?? null,
                'reference_type' => 'transfer_in',
                'reference_id' => null,
                'description' => 'Transfer from ' . $fromWorkPoint->work_name,
                'company_id' => $companyId,
                'company_unit_id' => $toWorkPoint->comp_unit_id,
                'work_point_id' => $toWorkPoint->id,
                'date' => now(),
            ]);

            DB::commit();

            Alert::success('Success', 'Stock transferred successfully.');
            return back();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Stock transfer failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return $this->sweetErrorBack($e->getMessage());
        }
    }

    public function processDelivery($id)
    {
        try {
            Alert::info('Info', 'Delivery stock processing is not configured in this controller yet.');
            return back();
        } catch (\Throwable $e) {
            Log::error('Process delivery failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', $e->getMessage());
            return back();
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            Alert::info('Info', 'Excel export is not configured yet.');
            return back();
        } catch (\Throwable $e) {
            Log::error('Export stock failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', $e->getMessage());
            return back();
        }
    }

    public function store(Request $request, $module)
    {
        Alert::info('Info', 'Generic store for module "' . $module . '" is not configured.');
        return back()->withInput();
    }

    public function update(Request $request, $module, $id)
    {
        Alert::info('Info', 'Generic update for module "' . $module . '" is not configured.');
        return back()->withInput();
    }

    public function destroy($module, $id)
    {
        Alert::info('Info', 'Generic delete for module "' . $module . '" is not configured.');
        return back();
    }

    public function rawMaterialIssues()
    {
        try {
            $data = $this->stockPageData(request());

            if (view()->exists('admin.store.stock.partials.raw-material-issues')) {
                return view('admin.store.stock.partials.raw-material-issues', $data);
            }

            Alert::info('Info', 'Raw material issues view was not found.');
            return back();
        } catch (\Throwable $e) {
            Log::error('Raw material issues failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Alert::error('Error', $e->getMessage());
            return back();
        }
    }
public function inventoryReport()
{
    return redirect()
        ->route('stock.management.dashboard', [
            'report' => 1
        ]);
}
}