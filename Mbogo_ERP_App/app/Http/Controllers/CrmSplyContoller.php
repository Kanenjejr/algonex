<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\User;
use App\Models\CstmSply;
use App\Models\CstmOrder;
use App\Models\CstmTx;
use App\Models\CstmProduct;
use App\Models\Product;
use App\Models\HiredEquipment;
use App\Models\HiredEquipmentWorking;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use App;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Validator;
class CrmSplyContoller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // parent::__construct();
    }
    protected function isSuperRole()
    {
        return in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'], true);
    }
     // CRM
    public function crm()
    {
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    // Customers
    $qCustomers = CstmSply::query()
        ->where('company_id', $user->company_id)
        ->where('category','Customer')
        ->where('status','!=','Deleted');

    // Suppliers
    $qSuppliers = CstmSply::query()
        ->where('company_id', $user->company_id)
        ->where('category','Supplier')
        ->where('status','!=','Deleted');

    if (!$isSuper) {
        $qCustomers->where('work_point_id', $user->work_point_id);
        $qSuppliers->where('work_point_id', $user->work_point_id);
    }

    $customers = $qCustomers->get();
    $suppliers = $qSuppliers->get();

    // Totals for dashboard
    $totalDebitCustomer = CstmTx::whereIn('cstm_id',$customers->pluck('id'))
        ->where('type','debit')->sum('amount');

    $totalCreditCustomer = CstmTx::whereIn('cstm_id',$customers->pluck('id'))
        ->where('type','credit')->sum('amount');

    $totalDebitSupplier = CstmTx::whereIn('cstm_id',$suppliers->pluck('id'))
        ->where('type','debit')->sum('amount');

    $totalCreditSupplier = CstmTx::whereIn('cstm_id',$suppliers->pluck('id'))
        ->where('type','credit')->sum('amount');
        // Chart data
        $chartLabels = ['Customers', 'Suppliers'];
        $chartData   = [$customers->count(), $suppliers->count()];
        return view('admin.home.crm', compact(
            'customers','suppliers',
            'totalDebitCustomer','totalCreditCustomer',
            'totalDebitSupplier','totalCreditSupplier',
            'chartLabels','chartData'
        ));
    }
// ------------------------------
    // CSTM (Customers & Suppliers) CRUD
    // ------------------------------
    public function indexCstm()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $items = CstmSply::where('company_id', $user->company_id)->where('status', '!=', 'Deleted')->orderBy('customer_name')->get();
            $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        } else {
            $items = CstmSply::where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id)->where('status', '!=', 'Deleted')->orderBy('customer_name')->get();
            $workPoints = collect();
        }
        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.crm.cstm_splies', compact('items','workPoints','companies','items')); // adapt in view
    }

    public function storeCstm(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'customer_name' => ['required','string','max:255'],
            'category' => ['required', Rule::in(['Customer','Supplier'])],
            'status' => ['nullable', Rule::in(['Active','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) { $q->where('company_id', $user->company_id); })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        CstmSply::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'customer_name' => $request->customer_name,
            'phone_no' => $request->phone_no,
            'location' => $request->location,
            'address_line' => $request->address_line,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'category' => $request->category,
            'status' => $request->status ?? 'Active',
        ]);

        Alert::success('Success','Customer/Supplier created.');
        return redirect()->route('crm.cstm.index');
    }

    public function updateCstm(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $row = CstmSply::findOrFail($decrypted);
        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $rules = [
            'customer_name' => ['required','string','max:255'],
            'category' => ['required', Rule::in(['Customer','Supplier'])],
            'status' => ['required', Rule::in(['Active','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) { $q->where('company_id', $user->company_id); })];
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $row->update([
            'work_point_id' => $isSuper ? $request->work_point_id : $row->work_point_id,
            'customer_name' => $request->customer_name,
            'phone_no' => $request->phone_no,
            'location' => $request->location,
            'address_line' => $request->address_line,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'category' => $request->category,
            'status' => $request->status,
        ]);

        Alert::success('Success','Customer/Supplier updated.');
        return redirect()->route('crm.cstm.index');
    }

    public function removeCstm($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $row = CstmSply::findOrFail($decrypted);
        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }
        $row->update(['status' => 'Deleted']);
        Alert::success('Success','Customer/Supplier removed.');
        return redirect()->route('crm.cstm.index');
    }

    public function showCstm($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { return response()->json(['error'=>'invalid id'], 400); }
        $user = auth()->user();
        $row = CstmSply::with(['orders','transactions','items'])->findOrFail($decrypted);
        if ($row->company_id !== $user->company_id) return response()->json(['error'=>'unauthorized'],403);
        return response()->json($row);
    }

    // ------------------------------
    // Orders CRUD
    // ------------------------------
    public function indexOrders()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        if ($isSuper) {
            $cstmSplies  = CstmSply::where('company_id', $user->company_id)->where('status', '!=', 'Deleted')->orderBy('customer_name')->get();
            $orders = CstmOrder::with('customer')->where('company_id', $user->company_id)->orderByDesc('order_date')->get();
            $workPoints = WorkPoint::where('company_id', $user->company_id)->get();
        } else {
            $cstmSplies  = CstmSply::where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id)->where('status', '!=', 'Deleted')->orderBy('customer_name')->get();
            $orders = CstmOrder::with('customer')->where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id)->orderByDesc('order_date')->get();
            $workPoints = collect();
        }
        return view('admin.crm.cstm_orders', compact('orders','workPoints','cstmSplies'));
    }

    public function storeOrder(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user) { $q->where('company_id', $user->company_id);} )],
            'order_date' => ['nullable','date'],
            'total_amount' => ['nullable','numeric'],
            'currency' => ['nullable','string','max:10'],
            'type' => ['required', Rule::in(['sale','purchase'])],
            'status' => ['nullable', Rule::in(['Pending','Confirmed','Completed','Cancelled'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        // generate order_no simple pattern; you can adjust
        $orderNo = 'ORD-' . strtoupper(substr(sha1(time().rand()),0,8));

        $order = CstmOrder::create([
            'cstm_id' => $request->cstm_id,
            'order_no' => $orderNo,
            'order_date' => $request->order_date,
            'total_amount' => $request->total_amount ?? 0,
            'currency' => $request->currency ?? 'USD',
            'type' => $request->type,
            'status' => $request->status ?? 'Pending',
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        ]);

        Alert::success('Success','Order created.');
        return redirect()->route('crm.orders.index');
    }

    public function updateOrder(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $order = CstmOrder::findOrFail($decrypted);
        if ($order->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $rules = [
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user) { $q->where('company_id', $user->company_id);} )],
            'order_date' => ['nullable','date'],
            'total_amount' => ['nullable','numeric'],
            'currency' => ['nullable','string','max:10'],
            'type' => ['required', Rule::in(['sale','purchase'])],
            'status' => ['nullable', Rule::in(['Pending','Confirmed','Completed','Cancelled'])],
        ];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $order->update([
            'cstm_id' => $request->cstm_id,
            'order_date' => $request->order_date,
            'total_amount' => $request->total_amount ?? 0,
            'currency' => $request->currency ?? 'USD',
            'type' => $request->type,
            'status' => $request->status ?? $order->status,
            'work_point_id' => $order->work_point_id, // keep original unless super wants to change
        ]);

        Alert::success('Success','Order updated.');
        return redirect()->route('crm.orders.index');
    }

    public function removeOrder($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $order = CstmOrder::findOrFail($decrypted);
        if ($order->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }
        $order->update(['status' => 'Cancelled']);
        Alert::success('Success','Order cancelled.');
        return redirect()->route('crm.orders.index');
    }

    public function showOrder($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { return response()->json(['error'=>'invalid id'],400); }
        $user = auth()->user();
        $order = CstmOrder::with('items')->findOrFail($decrypted);
        if ($order->company_id !== $user->company_id) return response()->json(['error'=>'unauthorized'],403);
        return response()->json($order);
    }
    // ------------------------------
    // Order items (cstm_products) CRUD
    // ------------------------------
    public function indexOrderItems($orderId = null)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        // base query limited to company
        $q = CstmProduct::query()->where('company_id', $user->company_id);

        // decoded order id for filtering
        $orderIdDecoded = null;
        if ($orderId) {
            try {
                $orderIdDecoded = decrypt($orderId);
                $q->where('order_id', $orderIdDecoded);
            } catch (\Throwable $th) {
                abort(404);
            }
        }

        // work point restriction
        if (!$isSuper) {
            $q->where('work_point_id', $user->work_point_id);
            $workPoints = collect();
        } else {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        }

        $items = $q->get();

        // fetch orders for dropdown
        if ($orderIdDecoded) {
            $orders = CstmOrder::where('company_id', $user->company_id)
                ->where('id', $orderIdDecoded)
                ->get();
        } else {
            $ordersQuery = CstmOrder::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('order_date', 'desc');
            if (!$isSuper) $ordersQuery->where('work_point_id', $user->work_point_id);
            $orders = $ordersQuery->get();
        }

        $products = Product::where('company_id', $user->company_id)
            ->where('status', 'Active')->get();

        $cstmSplies = CstmSply::where('company_id', $user->company_id)
            ->where('status', 'Active')->get();

        return view('admin.crm.cstm_order_items', compact('items','orders','products','cstmSplies','workPoints'));
    }
    public function storeOrderItem(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'order_id' => ['nullable','integer', Rule::exists('cstm_orders','id')->where(function($q) use ($user){ $q->where('company_id', $user->company_id); })],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){ $q->where('company_id', $user->company_id); })],
            'product_id' => ['nullable','integer', Rule::exists('products','id')],
            'product_name' => ['nullable','string','max:255'],
            'quantity' => ['required','numeric'],
            'unit' => ['nullable','string'],
            'unit_price' => ['nullable','numeric'],
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $total = ($request->quantity * ($request->unit_price ?? 0));

        $it = CstmProduct::create([
            'order_id' => $request->order_id,
            'cstm_id' => $request->cstm_id,
            'product_id' => $request->product_id,
            'product_name' => $request->product_name ?? ($request->product_id ? \App\Models\Product::find($request->product_id)->product_name : null),
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price ?? 0,
            'total_price' => $total,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $user->work_point_id,
        ]);

        // Optionally update order total_amount
        if ($it->order_id) {
            $order = CstmOrder::find($it->order_id);
            if ($order) {
                $sum = $order->items()->sum('total_price');
                $order->update(['total_amount' => $sum]);
            }
        }

        Alert::success('Success','Item added.');
        return redirect()->back();
    }

    public function updateOrderItem(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $it = CstmProduct::findOrFail($decrypted);
        if ($it->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $rules = [
            'quantity' => ['required','numeric'],
            'unit_price' => ['nullable','numeric'],
            'product_name' => ['nullable','string'],
            'unit' => ['nullable','string'],
        ];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $it->update([
            'product_name' => $request->product_name ?? $it->product_name,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price ?? $it->unit_price,
            'total_price' => ($request->quantity * ($request->unit_price ?? $it->unit_price)),
        ]);

        // update order totals if linked
        if ($it->order_id) {
            $order = CstmOrder::find($it->order_id);
            if ($order) $order->update(['total_amount' => $order->items()->sum('total_price')]);
        }

        Alert::success('Success','Item updated.');
        return redirect()->back();
    }

    public function removeOrderItem($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $it = CstmProduct::findOrFail($decrypted);
        if ($it->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $orderId = $it->order_id;
        $it->delete();

        // update order total
        if ($orderId) {
            $order = CstmOrder::find($orderId);
            if ($order) $order->update(['total_amount' => $order->items()->sum('total_price')]);
        }

        Alert::success('Success','Item removed.');
        return redirect()->back();
    }

    // ------------------------------
    // Ledger / Transactions CRUD
    // ------------------------------
   public function indexTx()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    // get transactions with related customer
    if ($isSuper) {
        $txs = CstmTx::with('customer')
            ->where('company_id', $user->company_id)
            ->orderByDesc('tx_date')
            ->get();

        // all work points for the company
        $workPoints = WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
    } else {
        $txs = CstmTx::with('customer') ->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id)->orderByDesc('tx_date')->get();
        $workPoints = collect();
    }
        $cstmSplies = CstmSply::where('company_id', $user->company_id)
            ->where('status', 'Active')->get();

    return view('admin.crm.cstm_tx', compact('txs', 'workPoints','cstmSplies'));
}

    public function storeTx(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'cstm_id' => ['required','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){ $q->where('company_id', $user->company_id);} )],
            'tx_date' => ['nullable','date'],
            'type' => ['required', Rule::in(['credit','debit'])],
            'amount' => ['required','numeric','min:0.0001'],
            'reference' => ['nullable','string'],
            'notes' => ['nullable','string'],
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        // compute balance_after: get last tx for this customer (company & workpoint scope)
        $last = CstmTx::where('cstm_id', $request->cstm_id)->orderByDesc('tx_date')->orderByDesc('id')->first();
        $lastBalance = $last ? (float)$last->balance_after : 0.0;
        $amount = (float)$request->amount;
        if ($request->type === 'debit') {
            // business rule: debit increases balance (customer owes more); credit decreases balance
            $newBalance = $lastBalance + $amount;
        } else {
            $newBalance = $lastBalance - $amount;
        }

        $tx = CstmTx::create([
            'cstm_id' => $request->cstm_id,
            'tx_date' => $request->tx_date ?? now(),
            'type' => $request->type,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'reference' => $request->reference,
            'notes' => $request->notes,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $user->work_point_id,
        ]);

        Alert::success('Success','Transaction recorded. New balance: ' . number_format($newBalance,2));
        return redirect()->route('crm.tx.index');
    }

    public function updateTx(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $tx = CstmTx::findOrFail($decrypted);
        if ($tx->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        // For simplicity we only allow editing notes/reference and type/amount with recalculation of subsequent balances.
        $rules = [
            'type' => ['required', Rule::in(['credit','debit'])],
            'amount' => ['required','numeric','min:0.0001'],
            'reference' => ['nullable','string'],
            'notes' => ['nullable','string'],
            'tx_date' => ['nullable','date'],
        ];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        // update this tx; but then need to recalc all subsequent tx balances for that customer
        $tx->update([
            'type' => $request->type,
            'amount' => $request->amount,
            'reference' => $request->reference,
            'notes' => $request->notes,
            'tx_date' => $request->tx_date ?? $tx->tx_date,
        ]);

        // Recalculate balances for all txs for this cstm ordered by date,id
        $all = CstmTx::where('cstm_id', $tx->cstm_id)->orderBy('tx_date')->orderBy('id')->get();
        $bal = 0.0;
        foreach ($all as $t) {
            if ($t->type === 'debit') $bal += (float)$t->amount; else $bal -= (float)$t->amount;
            $t->balance_after = $bal;
            $t->save();
        }

        Alert::success('Success','Transaction updated.');
        return redirect()->route('crm.tx.index');
    }

    public function removeTx($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $tx = CstmTx::findOrFail($decrypted);
        if ($tx->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $cstmId = $tx->cstm_id;
        $tx->delete();

        // recalc balances
        $all = CstmTx::where('cstm_id', $cstmId)->orderBy('tx_date')->orderBy('id')->get();
        $bal = 0.0;
        foreach ($all as $t) {
            if ($t->type === 'debit') $bal += (float)$t->amount; else $bal -= (float)$t->amount;
            $t->balance_after = $bal;
            $t->save();
        }
        Alert::success('Success','Transaction removed.');
        return redirect()->route('crm.tx.index');
    }

    /**
 * Reporting: list all customers (summary) with links to detail
 */
public function reportsIndex(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $customers = CstmSply::where('company_id', $user->company_id)->where('status','!=','Deleted')->orderBy('customer_name')->get();
    } else {
        $customers = CstmSply::where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id)
            ->where('status','!=','Deleted')->orderBy('customer_name')->get();
    }

    return view('admin.crm.reports_customers', [
        'customers' => $customers,
    ]);
}

/**
 * Detailed report for one customer: show orders and transactions with date filter
 */
public function reportCustomerDetail(Request $request, $encryptedId)
{
    try { $id = decrypt($encryptedId); } catch (\Throwable $th) { abort(404); }

    $user = auth()->user();
    $customer = CstmSply::with('workpoint')->findOrFail($id);
    if ($customer->company_id !== $user->company_id) abort(403);

    $from = $request->from ? date('Y-m-d', strtotime($request->from)) : null;
    $to   = $request->to ? date('Y-m-d', strtotime($request->to)) : null;

    // Orders
    $ordersQ = CstmOrder::where('cstm_id', $customer->id)->where('company_id', $user->company_id);
    // Transactions
    $txQ = CstmTx::where('cstm_id', $customer->id)->where('company_id', $user->company_id);

    if ($from) {
        $ordersQ->whereDate('order_date', '>=', $from);
        $txQ->whereDate('tx_date', '>=', $from);
    }
    if ($to) {
        $ordersQ->whereDate('order_date', '<=', $to);
        $txQ->whereDate('tx_date', '<=', $to);
    }

    $orders = $ordersQ->orderByDesc('order_date')->get();
    $txsRaw = $txQ->orderBy('tx_date')->get(); // ascending to compute running balance

    // compute running balance and separate debit/credit
    $running = 0;
    $txs = $txsRaw->map(function($t) use (&$running){
        if ($t->type === 'credit') {
            $t->credit = $t->amount;
            $t->debit = 0;
            $running += $t->amount; // assuming positive balance for credit
            $t->balance_after = $running;
            $t->balance_side = 'Credit';
        } else {
            $t->debit = $t->amount;
            $t->credit = 0;
            $running -= $t->amount;
            $t->balance_after = abs($running);
            $t->balance_side = ($running >= 0) ? 'Credit' : 'Debit';
        }
        return $t;
    });
    return view('admin.crm.reports_customer_detail', [
        'customer' => $customer,
        'orders' => $orders,
        'txs' => $txs,
        'from' => $from,
        'to' => $to,
    ]);
}
/**
 * Optional AJAX simpler endpoint returning JSON (used by JS filters)
 */
public function ajaxCustomerTransactions(Request $request)
{
    $user = auth()->user();
    $cstmEnc = $request->cstm_id;
    try { $cstmId = decrypt($cstmEnc); } catch (\Throwable $th) { return response()->json(['error'=>'Invalid id'], 422); }
    $customer = CstmSply::findOrFail($cstmId);
    if ($customer->company_id !== $user->company_id) return response()->json(['error'=>'Unauthorized'], 403);

    $from = $request->from ? date('Y-m-d', strtotime($request->from)) : null;
    $to = $request->to ? date('Y-m-d', strtotime($request->to)) : null;

    $txQ = CstmTx::where('cstm_id', $customer->id);
    if ($from) $txQ->whereDate('tx_date', '>=', $from);
    if ($to)   $txQ->whereDate('tx_date', '<=', $to);
    $txs = $txQ->orderByDesc('tx_date')->get();
    return response()->json(['txs' => $txs]);
}

public function hiredIndex()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $items = HiredEquipment::where('company_id', $user->company_id)->orderBy('EqpmntNo')->get();
            $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        } else {
            $items = HiredEquipment::where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->orderBy('EqpmntNo')->get();
            $workPoints = collect();
        }

        $customers = CstmSply::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.crm.equipment_index', compact('items','workPoints','customers','companies'));
    }

    public function storeHired(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'EqpmntNo' => ['required','string','max:255','unique:hired_equipment,EqpmntNo'],
            'OperatorName' => ['nullable','string','max:255'],
            'PaymentPerDay' => ['nullable','integer','min:0'],
            'Status' => ['nullable', Rule::in(['Active','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        HiredEquipment::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'cstm_id' => $request->cstm_id ?: null,
            'Model' => $request->Model,
            'EqpmntNo' => $request->EqpmntNo,
            'OperatorName' => $request->OperatorName,
            'PaymentPerDay' => $request->PaymentPerDay ?? 0,
            'Status' => $request->Status ?? 'Active',
        ]);

        Alert::success('Success','Hired equipment created.');
        return redirect()->route('hired.index');
    }

    public function updateHired(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }

        $user = auth()->user();
        $row = HiredEquipment::findOrFail($decrypted);
        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $isSuper = $this->isSuperRole();

        $rules = [
            'EqpmntNo' => ['required','string','max:255', Rule::unique('hired_equipment','EqpmntNo')->ignore($row->id)],
            'OperatorName' => ['nullable','string','max:255'],
            'PaymentPerDay' => ['nullable','integer','min:0'],
            'Status' => ['required', Rule::in(['Active','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $row->update([
            'work_point_id' => $isSuper ? $request->work_point_id : $row->work_point_id,
            'cstm_id' => $request->cstm_id ?: $row->cstm_id,
            'Model' => $request->Model,
            'EqpmntNo' => $request->EqpmntNo,
            'OperatorName' => $request->OperatorName,
            'PaymentPerDay' => $request->PaymentPerDay ?? $row->PaymentPerDay,
            'Status' => $request->Status,
        ]);

        Alert::success('Success','Hired equipment updated.');
        return redirect()->route('hired.index');
    }

    public function removeHired($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $row = HiredEquipment::findOrFail($decrypted);
        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }
        $row->update(['Status' => 'Deleted']);
        Alert::success('Success','Hired equipment removed.');
        return redirect()->route('hired.index');
    }

    public function indexHiredWrk()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $items = HiredEquipmentWorking::where('company_id', $user->company_id)->orderByDesc('WorkingDate')->get();
            $equipments = HiredEquipment::where('company_id', $user->company_id)->where('Status','!=','Deleted')->orderBy('EqpmntNo')->get();
            $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        } else {
            $items = HiredEquipmentWorking::where('company_id', $user->company_id)
                        ->where('work_point_id', $user->work_point_id)
                        ->orderByDesc('WorkingDate')->get();

            $equipments = HiredEquipment::where('company_id', $user->company_id)
                        ->where('work_point_id', $user->work_point_id)
                        ->where('Status','!=','Deleted')->orderBy('EqpmntNo')->get();

            $workPoints = collect();
        }

        return view('admin.crm.hired_workings_index', compact('items','equipments','workPoints'));
    }

    /**
     * Parse times (H:i) server-side and return [hours, minutes, totalHours].
     * If timeOut <= timeIn treat timeOut as next day.
     */
    protected function computeDurationFromTimes($timeIn, $timeOut)
    {
        if (!$timeIn || !$timeOut) return [0, 0, 0.0];

        try {
            $in = Carbon::createFromFormat('H:i', $timeIn);
            $out = Carbon::createFromFormat('H:i', $timeOut);
        } catch (\Throwable $th) {
            return [0, 0, 0.0];
        }

        if ($out->lessThanOrEqualTo($in)) {
            $out->addDay();
        }

        $diffMinutes = $out->diffInMinutes($in);
        $hours = intdiv($diffMinutes, 60);
        $minutes = $diffMinutes % 60;
        $totalHours = $diffMinutes / 60.0;

        return [$hours, $minutes, $totalHours];
    }

    /**
     * Compute total price using per-day rate (day = 8 hours).
     * Prefers timeIn/timeOut when provided; otherwise uses workingHours & minutes.
     * Returns array: ['TotalPrice'=>..., 'WorkingHours'=>..., 'Minutes'=>..., 'TotalHours'=>...]
     */
    protected function computeTotalPrice($equipment, $workingHours = null, $minutes = null, $timeIn = null, $timeOut = null)
    {
        // determine day rate
        $dayRate = $equipment->PaymentPerDay ?? null;
        if ($dayRate === null && isset($equipment->PaymentPerHour)) {
            $dayRate = $equipment->PaymentPerHour * 8;
        }
        $dayRate = (float) ($dayRate ?: 0);

        if ($timeIn && $timeOut) {
            [$h, $m, $totalHours] = $this->computeDurationFromTimes($timeIn, $timeOut);
        } else {
            $h = (int) round($workingHours ?? 0);
            $m = (int) round($minutes ?? 0);
            $totalHours = $h + ($m / 60.0);
        }

        $daysFraction = $totalHours / 8.0;
        $total = round($daysFraction * $dayRate, 2);

        return [
            'TotalPrice' => $total,
            'WorkingHours' => $h,
            'Minutes' => $m,
            'TotalHours' => $totalHours,
        ];
    }

    /**
     * Store new working record.
     */
    public function storeHiredWrk(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'hired_equipment_id' => ['required','integer', Rule::exists('hired_equipment','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })],
            'WorkingDate' => ['required','date'],
            'TimeIn' => ['nullable','date_format:H:i'],
            'TimeOut' => ['nullable','date_format:H:i'],
            'WorkingHours' => ['nullable','numeric','min:0'],
            'Minutes' => ['nullable','numeric','min:0','max:59.99'],
            'PaymentStatus' => ['nullable', Rule::in(['Pending','Paid'])],
            'Status' => ['nullable', Rule::in(['Active','Deleted'])],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['nullable','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $equipment = HiredEquipment::findOrFail($request->hired_equipment_id);

        $timeIn = $request->TimeIn;
        $timeOut = $request->TimeOut;

        $calc = $this->computeTotalPrice(
            $equipment,
            $request->WorkingHours ?? null,
            $request->Minutes ?? null,
            $timeIn,
            $timeOut
        );

        HiredEquipmentWorking::create([
            'hired_equipment_id' => $equipment->id,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'WorkingHours' => $calc['WorkingHours'],
            'Minutes' => $calc['Minutes'],
            'TotalPrice' => $calc['TotalPrice'],
            'WorkingDate' => $request->WorkingDate,
            'TimeIn' => $timeIn,
            'TimeOut' => $timeOut,
            'PaymentStatus' => $request->PaymentStatus ?? 'Pending',
            'Status' => $request->Status ?? 'Active',
        ]);

        Alert::success('Success','Working record created.');
        return redirect()->route('hired.workings.index');
    }

    /**
     * Update an existing working record.
     */
    public function updateHiredWrk(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $row = HiredEquipmentWorking::findOrFail($decrypted);
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $rules = [
            'hired_equipment_id' => ['required','integer', Rule::exists('hired_equipment','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })],
            'WorkingDate' => ['required','date'],
            'TimeIn' => ['nullable','date_format:H:i'],
            'TimeOut' => ['nullable','date_format:H:i'],
            'WorkingHours' => ['nullable','numeric','min:0'],
            'Minutes' => ['nullable','numeric','min:0','max:59.99'],
            'PaymentStatus' => ['nullable', Rule::in(['Pending','Paid'])],
            'Status' => ['nullable', Rule::in(['Active','Deleted'])],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['nullable','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $equipment = HiredEquipment::findOrFail($request->hired_equipment_id);

        $timeIn = $request->TimeIn;
        $timeOut = $request->TimeOut;

        $calc = $this->computeTotalPrice(
            $equipment,
            $request->WorkingHours ?? null,
            $request->Minutes ?? null,
            $timeIn,
            $timeOut
        );

        $row->update([
            'hired_equipment_id' => $equipment->id,
            'WorkingHours' => $calc['WorkingHours'],
            'Minutes' => $calc['Minutes'],
            'TotalPrice' => $calc['TotalPrice'],
            'WorkingDate' => $request->WorkingDate,
            'TimeIn' => $timeIn,
            'TimeOut' => $timeOut,
            'PaymentStatus' => $request->PaymentStatus ?? 'Pending',
            'Status' => $request->Status ?? 'Active',
        ]);

        Alert::success('Success','Working record updated.');
        return redirect()->route('hired.workings.index');
    }

    /**
     * Soft remove (mark as Deleted).
     */
    public function removeHiredWrk($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $row = HiredEquipmentWorking::findOrFail($decrypted);
        $user = auth()->user();
        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }
        $row->update(['Status' => 'Deleted']);
        Alert::success('Success','Working record removed.');
        return redirect()->route('hired.workings.index');
    }
    /**
     * Reports: show paid vs unpaid working hours & totals, with filters and paginated list.
     */
    public function reportHiredWrk(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        // filters
        $start = $request->input('start_date'); // YYYY-MM-DD
        $end = $request->input('end_date');     // YYYY-MM-DD
        $equipmentId = $request->input('equipment_id');
        $workPointId = $request->input('work_point_id');
        $paymentStatus = $request->input('payment_status'); // 'All'|'Paid'|'Pending'
        // base query scoped to company
        $query = HiredEquipmentWorking::query()->with('equipment')
            ->where('company_id', $user->company_id);
        if (!$isSuper) {
            $query->where('work_point_id', $user->work_point_id);
        }
        // apply filters
        if ($start) $query->whereDate('WorkingDate', '>=', $start);
        if ($end)   $query->whereDate('WorkingDate', '<=', $end);
        if ($equipmentId) $query->where('hired_equipment_id', $equipmentId);
        if ($workPointId && $isSuper) $query->where('work_point_id', $workPointId);
        if ($paymentStatus && in_array($paymentStatus, ['Paid','Pending'])) {
            $query->where('PaymentStatus', $paymentStatus);
        }
        // paginated listing for the table
        $items = $query->orderByDesc('WorkingDate')->paginate(25)->appends($request->all());
        // summary aggregates for Paid vs Pending (unpaid)
        // We must compute hours as WorkingHours + Minutes/60.0
        // Do two DB aggregates, grouping by PaymentStatus to get sums.
        $aggQuery = HiredEquipmentWorking::select( DB::raw("PaymentStatus"),
                DB::raw("SUM(WorkingHours + (Minutes / 60.0)) as total_hours"),
                DB::raw("SUM(TotalPrice) as total_amount"), DB::raw("COUNT(*) as records_count")
            )->where('company_id', $user->company_id);

        if (!$isSuper) {
            $aggQuery->where('work_point_id', $user->work_point_id);
        }
        if ($start) $aggQuery->whereDate('WorkingDate', '>=', $start);
        if ($end)   $aggQuery->whereDate('WorkingDate', '<=', $end);
        if ($equipmentId) $aggQuery->where('hired_equipment_id', $equipmentId);
        if ($workPointId && $isSuper) $aggQuery->where('work_point_id', $workPointId);

        $aggQuery->groupBy('PaymentStatus');

        $groups = $aggQuery->get()->keyBy('PaymentStatus');

        // compute values with safe defaults
        $paidHours = isset($groups['Paid']) ? (float)$groups['Paid']->total_hours : 0.0;
        $paidAmount = isset($groups['Paid']) ? (float)$groups['Paid']->total_amount : 0.0;
        $paidCount  = isset($groups['Paid']) ? (int)$groups['Paid']->records_count : 0;

        $unpaidHours = isset($groups['Pending']) ? (float)$groups['Pending']->total_hours : 0.0;
        $unpaidAmount = isset($groups['Pending']) ? (float)$groups['Pending']->total_amount : 0.0;
        $unpaidCount  = isset($groups['Pending']) ? (int)$groups['Pending']->records_count : 0;

        // totals across statuses (Active + Deleted? we rely on status filter externally if needed)
        $totals = [
            'total_hours' => $paidHours + $unpaidHours,
            'total_amount' => $paidAmount + $unpaidAmount,
            'total_records' => $paidCount + $unpaidCount,
        ];

        // helpers for equipment / workpoints lists (for filter selects)
        $equipments = HiredEquipment::where('company_id', $user->company_id)
                        ->where('Status', '!=', 'Deleted')
                        ->when(!$isSuper, function($q) use ($user){ $q->where('work_point_id', $user->work_point_id); })
                        ->orderBy('EqpmntNo')->get();

        $workPoints = $isSuper ? WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get() : collect();

        return view('admin.crm.hired_reports', compact(
            'items','equipments','workPoints',
            'paidHours','paidAmount','paidCount',
            'unpaidHours','unpaidAmount','unpaidCount',
            'totals','start','end','equipmentId','workPointId','paymentStatus'
        ));
    }
    /**
     * Export current filtered report to CSV.
     */
    public function exportReportHiredWrk(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        // reuse same filtering logic as report()
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $equipmentId = $request->input('equipment_id');
        $workPointId = $request->input('work_point_id');
        $paymentStatus = $request->input('payment_status');
        $query = HiredEquipmentWorking::with('equipment')
            ->where('company_id', $user->company_id);
        if (!$isSuper) {
            $query->where('work_point_id', $user->work_point_id);
        }
        if ($start) $query->whereDate('WorkingDate', '>=', $start);
        if ($end)   $query->whereDate('WorkingDate', '<=', $end);
        if ($equipmentId) $query->where('hired_equipment_id', $equipmentId);
        if ($workPointId && $isSuper) $query->where('work_point_id', $workPointId);
        if ($paymentStatus && in_array($paymentStatus, ['Paid','Pending'])) {
            $query->where('PaymentStatus', $paymentStatus);
        }
        $rows = $query->orderByDesc('WorkingDate')->get();
        $filename = 'hired_workings_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];
        $callback = function() use ($rows) {
            $handle = fopen('php://output', 'w');
            // header row
            fputcsv($handle, [
                'Owner Name','EquipmentNo','Operator Name','Rate Per Day','WorkingDate','TimeIn','TimeOut','WorkingHours','Minutes','TotalHours','TotalPrice','PaymentStatus','Status'
            ]);
            foreach ($rows as $r) {
                $totalHours = (float)$r->WorkingHours + ((float)$r->Minutes / 60.0);
                fputcsv($handle, [
                    optional($r->equipment->cstm)->customer_name ?? '',
                    optional($r->equipment)->EqpmntNo ?? '',
                    optional($r->equipment)->OperatorName ?? '',
                    number_format($r->equipment->PaymentPerDay,2),
                    optional($r->WorkingDate)->format('Y-m-d') ?? '',
                    $r->TimeIn ?? '',
                    $r->TimeOut ?? '',
                    number_format($r->WorkingHours, 2),
                    number_format($r->Minutes, 2),
                    number_format($totalHours, 2),
                    number_format($r->TotalPrice, 2),
                    $r->PaymentStatus,
                    $r->Status,
                ]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }
    public function markAsPaidHiredWrk($id, Request $request)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid id');
            return back();
        }
        $user = auth()->user();
        $row = HiredEquipmentWorking::findOrFail($decrypted);
        // ownership / company guard
        if ($row->company_id !== $user->company_id) {
            Alert::error('Unauthorized','');
            return back();
        }
        // update payment status
        $row->update([
            'PaymentStatus' => 'Paid'
        ]);
        Alert::success('Success','Working record marked as Paid.');
        // redirect back to where the user came from (preserves filters if they came from reports)
        return redirect()->to(url()->previous());
    }
}