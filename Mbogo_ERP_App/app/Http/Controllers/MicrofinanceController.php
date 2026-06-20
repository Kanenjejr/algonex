<?php

namespace App\Http\Controllers;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\BankNetwork;
use App\Models\MicroTransaction;
use App\Models\Commission;
use App\Models\User;

use App\Models\MicroSetting;
use App\Models\MicroLoanCategory;
use App\Models\MicroLoanProduct;
use App\Models\MicroLoanApplicant;
use App\Models\MicroLoanApplication;
use App\Models\MicroLoanGuarantor;
use App\Models\MicroLoanCollateral;
use App\Models\MicroLoanAttachment;
use App\Models\MicroLoanDisbursement;
use App\Models\MicroLoanRepayment;
use App\Models\MicroLoanPenalty;
use App\Models\MicroLoanReminder;
use App\Models\MicroCost;
use App\Models\MicroOtherIncome;

use Illuminate\Http\Request;
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
class MicrofinanceController extends Controller
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
    // Microfinancing
    public function microfinancing(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole(); // assume method exists as before
        // Filters
        $date = $request->date ?? date('Y-m-d'); // default today
        $workPointId = $request->work_point_id ?? null;
        $qBN = BankNetwork::where('company_id', $user->company_id)->where('status','!=','Deleted');
        if (!$isSuper) $qBN->where('work_point_id', $user->work_point_id);
        $bankNetworks = $qBN->orderBy('type')->orderBy('name')->get();
        // Transactions (for selected date range or single day)
        // We'll default to single day ($date). Optionally support from/to in future.
        $qTx = MicroTransaction::where('company_id', $user->company_id)
                ->whereDate('created_at', $date)->where('status','Completed');
        if (!$isSuper) {
            $qTx->where('work_point_id', $user->work_point_id);
        } else {
            if ($workPointId) $qTx->where('work_point_id', $workPointId);
        }
        $transactions = $qTx->get();
        // Totals by tx_group
        $byGroup = $qTx->selectRaw('tx_group, COUNT(*) as tx_count, SUM(amount) as total_amount')
                    ->groupBy('tx_group')->get()->keyBy('tx_group');
        // Totals overall
        $totalCount = $transactions->count();
        $totalAmount = $transactions->sum('amount');
        // Totals by bank/network (bank_network_id)
        $byBN = $qTx->whereNotNull('bank_network_id')->selectRaw('bank_network_id, COUNT(*) as tx_count, SUM(amount) as total_amount')
                    ->groupBy('bank_network_id')->get();
        // enrich with model
        $bnMap = [];
        foreach ($byBN as $b) {
            $bn = BankNetwork::find($b->bank_network_id);
            $bnMap[] = [
                'id' => $b->bank_network_id,
                'type' => optional($bn)->type,
                'name' => optional($bn)->name,
                'tx_count' => $b->tx_count,
                'total_amount' => $b->total_amount
            ];
        }
        // Commission totals (for date)
        $qComm = Commission::where('company_id', $user->company_id)->whereDate('created_at', $date);
        if (!$isSuper) $qComm->where('work_point_id', $user->work_point_id);
        else if ($workPointId) $qComm->where('work_point_id', $workPointId);

        $commTotal = $qComm->selectRaw('SUM(amount) as total_commission, COUNT(*) as comm_count')->first();
        // WorkPoints list (for filter) for super users
        $workPoints = $isSuper ? WorkPoint::where('company_id',$user->company_id)->where('status','!=','Deleted')->get() : collect();
        // Prepare chart data arrays (for JS)
        // tx group chart
        $groupLabels = [];
        $groupData = [];
        $groupsOrder = ['Deposit','Withdraw','FX-Sell','FX-Buy']; // keep stable order
        foreach ($groupsOrder as $g) {
            $groupLabels[] = $g;
            $groupData[] = isset($byGroup[$g]) ? (float) $byGroup[$g]->total_amount : 0;
        }
        // BN chart (top N)
        $bnLabels = [];
        $bnData = [];
        foreach ($bnMap as $b) {
            $bnLabels[] = ($b['type'] ? $b['type'].' - ' : '') . $b['name'];
            $bnData[] = (float) $b['total_amount'];
        }
        return view('admin.home.microfinance', compact('date','workPoints','bankNetworks','transactions','byGroup',
            'bnMap','commTotal','totalCount','totalAmount','groupLabels',
            'groupData','bnLabels','bnData','workPointId'
        ));
    }

    // ------------------- BANK & NETWORK CRUD -------------------
    public function indexBankNetworks()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $q = BankNetwork::where('company_id',$user->company_id)->where('status','!=','Deleted');
        if (!$isSuper) $q->where('work_point_id',$user->work_point_id);
        $items = $q->orderBy('type')->orderBy('name')->get();

        $workPoints = $isSuper ? WorkPoint::where('company_id',$user->company_id)->where('status','!=','Deleted')->get() : collect();

        return view('admin.micro.bank_networks', compact('items','workPoints'));
    }

    public function storeBankNetwork(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'type' => ['required', Rule::in(['Bank','Network'])],
            'name' => ['required','string','max:255'],
        ];
        if ($this->isSuperRole()) {
            $rules['work_point_id'] = ['nullable','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        BankNetwork::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $this->isSuperRole() ? $request->work_point_id : $user->work_point_id,
            'type' => $request->type,
            'name' => $request->name,
            'code' => $request->code,
            'account_or_wallet' => $request->account_or_wallet,
            'branch' => $request->branch,
            'status' => $request->status ?? 'Active'
        ]);

        Alert::success('Success','Record saved.');
        return redirect()->route('micro.bank_networks.index');
    }

    public function updateBankNetwork(Request $request, $id)
    {
        try { $id = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $row = BankNetwork::findOrFail($id);
        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $rules = ['type'=>['required',Rule::in(['Bank','Network'])],'name'=>['required','string','max:255'],'status'=>['required',Rule::in(['Active','Deleted'])]];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'work_point_id' => $this->isSuperRole() ? $request->work_point_id : $row->work_point_id,
            'type' => $request->type,
            'name' => $request->name,
            'code' => $request->code,
            'account_or_wallet' => $request->account_or_wallet,
            'branch' => $request->branch,
            'status' => $request->status
        ]);

        Alert::success('Success','Record updated.');
        return redirect()->route('micro.bank_networks.index');
    }

    public function removeBankNetwork($id)
    {
        try { $id = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $row = BankNetwork::findOrFail($id);
        if ($row->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }
        $row->update(['status'=>'Deleted']);
        Alert::success('Success','Removed.');
        return redirect()->route('micro.bank_networks.index');
    }

    // ------------------- TRANSACTIONS -------------------
    public function indexTransactions(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $q = MicroTransaction::with('bankNetwork','workpoint')->where('company_id',$user->company_id);
        if (!$isSuper) $q->where('work_point_id',$user->work_point_id);
        if ($request->work_point_id && $isSuper) $q->where('work_point_id',$request->work_point_id);
        if ($request->bank_network_id) $q->where('bank_network_id',$request->bank_network_id);
        if ($request->tx_group) $q->where('tx_group',$request->tx_group);
        if ($request->from) $q->whereDate('created_at','>=',$request->from);
        if ($request->to) $q->whereDate('created_at','<=',$request->to);

        $items = $q->orderBy('created_at','desc')->get();

        $workPoints = $isSuper ? WorkPoint::where('company_id',$user->company_id)->where('status','!=','Deleted')->get() : collect();
        $bankNetworks = BankNetwork::where('company_id',$user->company_id)->where('status','Active')->get();

        return view('admin.micro.transactions', compact('items','workPoints','bankNetworks'));
    }

    public function storeTransaction(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'tx_group' => ['required', Rule::in(['Withdraw','Deposit','FX-Sell','FX-Buy'])],
            'amount' => ['required','numeric'],
            'work_point_id' => ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'bank_network_id' => ['nullable','integer', Rule::exists('bank_networks','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'currency' => ['nullable','string','max:10']
        ];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();
        // compute commission (priority: fixed -> pct -> 0)
        $commission = 0;
        if ($request->has('commission_fixed') && is_numeric($request->commission_fixed)) {
            $commission = floatval($request->commission_fixed);
        } elseif ($request->has('commission_pct') && is_numeric($request->commission_pct)) {
            $commission = round($request->amount*($request->fx_rate ?? 1) * floatval($request->commission_pct) / 100, 2);
        }
        DB::transaction(function() use($request, $user, $commission) {
            $tx = MicroTransaction::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'work_point_id' => $this->isSuperRole() ? $request->work_point_id : $user->work_point_id,
                'bank_network_id' => $request->bank_network_id ?? null,
                'tx_group' => $request->tx_group,
                'currency' => $request->currency ?? 'TZS',
                'amount' => $request->amount*($request->fx_rate ?? 1),
                'fx_rate' => $request->fx_rate ?? 1,
                'commission' => $commission,
                'meta' => $request->meta ? json_decode($request->meta, true) : null,
                'status' => $request->status ?? 'Completed'
            ]);
            if ($commission > 0) {
                Commission::create([
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'work_point_id' => $this->isSuperRole() ? $request->work_point_id : $user->work_point_id,
                    'micro_transaction_id' => $tx->id,
                    'amount' => $commission,
                    'description' => $request->commission_desc ?? 'Commission for tx '.$tx->id
                ]);
            }
        });
        Alert::success('Success','Transaction recorded successfully.');
        return redirect()->route('micro.transactions.index');
    }
    // show single tx as json for edit modal
    public function showTransaction($id)
    {
        try { $id = decrypt($id); } catch (\Throwable $th) { return response()->json(['error'=>'invalid id'],400); }
        $user = auth()->user();
        $tx = MicroTransaction::with('bankNetwork')->findOrFail($id);
        if ($tx->company_id !== $user->company_id) return response()->json(['error'=>'unauthorized'],403);
        return response()->json($tx);
    }

    // update transaction (edit)
    public function updateTransaction(Request $request, $id)
    {
        try { $id = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $tx = MicroTransaction::findOrFail($id);
        if ($tx->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }

        $rules = [
            'tx_group' => ['required', Rule::in(['Withdraw','Deposit','FX-Sell','FX-Buy'])],
            'amount' => ['required','numeric'],
            'bank_network_id' => ['nullable','integer', Rule::exists('bank_networks','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'currency' => ['nullable','string','max:10']
        ];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();
        // recompute commission
        $newComm = 0;
        if ($request->has('commission_fixed') && is_numeric($request->commission_fixed)) {
            $newComm = floatval($request->commission_fixed);
        } elseif ($request->has('commission_pct') && is_numeric($request->commission_pct)) {
            $newComm = round($request->amount*($request->fx_rate ?? 1) * floatval($request->commission_pct) / 100, 2);
        }
        DB::transaction(function() use($request, $tx, $newComm, $user) {
            // update tx
            $tx->update([
                'bank_network_id' => $request->bank_network_id ?? null,
                'tx_group' => $request->tx_group,
                'currency' => $request->currency ?? $tx->currency,
                'amount' => $request->amount*($request->fx_rate ?? 1),
                'fx_rate' => $request->fx_rate ?? $tx->fx_rate,
                'commission' => $newComm,
                'meta' => $request->meta ? json_decode($request->meta, true) : $tx->meta,
                'status' => $request->status ?? $tx->status
            ]);
            // update/create commission record
            $comm = $tx->commissions()->first();
            if ($newComm > 0) {
                if ($comm) {
                    $comm->update(['amount'=>$newComm,'description'=>$request->commission_desc ?? $comm->description]);
                } else {
                    Commission::create([
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'work_point_id' => $tx->work_point_id,
                        'micro_transaction_id' => $tx->id,
                        'amount' => $newComm,
                        'description' => $request->commission_desc ?? 'Commission for tx '.$tx->id,
                    ]);
                }
            } else {
                // if new commission is zero, delete existing commission row
                if ($comm) $comm->delete();
            }
        });
        Alert::success('Success','Transaction updated successfully.');
        return redirect()->route('micro.transactions.index');
    }
    // cancel / remove transaction (mark Cancelled)
    public function removeTransaction($id)
    {
        try { $id = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid id'); return back(); }
        $user = auth()->user();
        $tx = MicroTransaction::findOrFail($id);
        if ($tx->company_id !== $user->company_id) { Alert::error('Unauthorized',''); return back(); }
        DB::transaction(function() use($tx) {
            $tx->update(['status'=>'Cancelled']);
            // also mark commissions deleted or pending? we'll delete commission rows to keep ledger clean
            $tx->commissions()->delete();
        });
        Alert::success('Success','Transaction cancelled successfully.');
        return redirect()->route('micro.transactions.index');
    }
    // ------------------- REPORTS -------------------

    // daily summary with breakdown by group, by bank/network and totals
    public function dailySummary(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $date = $request->date ?? date('Y-m-d');
        $q = MicroTransaction::where('company_id',$user->company_id)->whereDate('created_at',$date)->where('status','Completed');
        if (!$isSuper) $q->where('work_point_id',$user->work_point_id);
        if ($request->work_point_id && $isSuper) $q->where('work_point_id',$request->work_point_id);
        // totals by group
        $byGroup = $q->selectRaw('tx_group, COUNT(*) as tx_count, SUM(amount) as total_amount')
            ->groupBy('tx_group')->get();
        // breakdown by bank_network
        $byBN = $q->whereNotNull('bank_network_id')->selectRaw('bank_network_id, COUNT(*) as tx_count, SUM(amount) as total_amount')
            ->groupBy('bank_network_id')->with('bankNetwork')->get();
        // full totals (all tx)
        $totalAll = $q->selectRaw('COUNT(*) as tx_count, SUM(amount) as total_amount')->first();
        // commissions for the date
        $commQ = Commission::where('company_id',$user->company_id)->whereDate('created_at',$date);
        if (!$isSuper) $commQ->where('work_point_id',$user->work_point_id);
        if ($request->work_point_id && $isSuper) $commQ->where('work_point_id',$request->work_point_id);
        $commTotal = $commQ->selectRaw('SUM(amount) as total_commission, COUNT(*) as comm_count')->first();

        $workPoints = $isSuper ? WorkPoint::where('company_id',$user->company_id)->where('status','!=','Deleted')->get() : collect();
        return view('admin.micro.reports.daily_summary', compact('date','byGroup','byBN','totalAll','commTotal','workPoints'));
    }

    // report for a specific Bank/Network across a range
    public function bankNetworkReport(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $rules = ['bank_network_id'=>['required','integer',Rule::exists('bank_networks','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })]];
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();
        $from = $request->from ?? date('Y-m-d', strtotime('-30 days'));
        $to = $request->to ?? date('Y-m-d');
        $q = MicroTransaction::where('company_id',$user->company_id)->where('bank_network_id',$request->bank_network_id)
            ->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status','Completed');

        if (!$isSuper) $q->where('work_point_id',$user->work_point_id);
        if ($request->work_point_id && $isSuper) $q->where('work_point_id',$request->work_point_id);
        $byGroup = $q->selectRaw('tx_group, COUNT(*) as tx_count, SUM(amount) as total_amount')
            ->groupBy('tx_group')->get();
        $total = $q->selectRaw('COUNT(*) as tx_count, SUM(amount) as total_amount')->first();
        $bn = BankNetwork::find($request->bank_network_id);
        return view('admin.micro.reports.bn_report', compact('bn','from','to','byGroup','total'));
    }
    public function bankNetworkReportDetailed(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        // allow encrypted id or plain id
        $bnId = $request->bank_network_id ?? null;
        if (!$bnId) return back()->with('error','bank_network_id required');
        try {
            $maybe = decrypt($bnId);
            $bnId = is_numeric($maybe) ? $maybe : $bnId;
        } catch (\Throwable $th) {
            // it's ok, assume plain id
        }
        $bn = BankNetwork::where('id',$bnId)->where('company_id',$user->company_id)->first();
        if (!$bn) { Alert::error('Error','Bank/Network not found or unauthorized.'); return back(); }
        $from = $request->from ?? date('Y-m-d');
        $to   = $request->to   ?? $from;
        $workPointFilter = $request->work_point_id ?? null;
        // mapping: what we treat as credit and debit (change if needed)
        $creditGroups = ['Deposit','FX-Sell'];
        $debitGroups  = ['Withdraw','FX-Buy'];
        // 1) Opening balances grouped by currency (tx before $from)
        $openingQ = DB::table('micro_transactions')->select('currency',
                DB::raw("SUM(CASE WHEN tx_group IN ('".implode("','",$creditGroups)."') THEN amount ELSE 0 END) as credits"),
                DB::raw("SUM(CASE WHEN tx_group IN ('".implode("','",$debitGroups)."') THEN amount ELSE 0 END) as debits")
            )->where('company_id', $user->company_id)->where('bank_network_id', $bn->id)
            ->where('status','Completed') ->whereDate('created_at','<',$from);

        if (!$isSuper) $openingQ->where('work_point_id',$user->work_point_id);
        if ($workPointFilter && $isSuper) $openingQ->where('work_point_id', $workPointFilter);
        $openingRows = $openingQ->groupBy('currency')->get();
        // prepare opening balances array: currency => opening_balance
        $opening = [];
        foreach ($openingRows as $r) {
            $opening[$r->currency] = (float)$r->credits - (float)$r->debits;
        }
        // 2) Transactions in range (inclusive)
        $txQ = MicroTransaction::where('company_id',$user->company_id)->where('bank_network_id',$bn->id)->whereDate('created_at','>=',$from)
                ->whereDate('created_at','<=',$to)->where('status','Completed')
                ->with('workpoint','user')->orderBy('created_at','asc');
        if (!$isSuper) $txQ->where('work_point_id',$user->work_point_id);
        if ($workPointFilter && $isSuper) $txQ->where('work_point_id',$workPointFilter);
        $txs = $txQ->get();
        // group transactions by currency for running balance calculation
        $txsByCurrency = $txs->groupBy('currency');
        // compute running balances per currency
        $txs_with_balance = collect(); // flattened with balance_after
        $periodTotals = []; // currency => ['credits'=>..., 'debits'=>..., 'count'=>...]
        foreach ($txsByCurrency as $currency => $list) {
            $running = isset($opening[$currency]) ? (float)$opening[$currency] : 0.0;
            if (!isset($periodTotals[$currency])) $periodTotals[$currency] = ['credits'=>0.0,'debits'=>0.0,'count'=>0];
            foreach ($list as $tx) {
                $isCredit = in_array($tx->tx_group, $creditGroups);
                $isDebit  = in_array($tx->tx_group, $debitGroups);
                $creditVal = $isCredit ? (float)$tx->amount : 0.0;
                $debitVal  = $isDebit  ? (float)$tx->amount : 0.0;
                // update running
                $running = $running + $creditVal - $debitVal;
                // attach computed values for view
                $tx->computed = (object)[
                    'credit' => $creditVal,
                    'debit' => $debitVal,
                    'balance_after' => $running
                ];

                $periodTotals[$currency]['credits'] += $creditVal;
                $periodTotals[$currency]['debits']  += $debitVal;
                $periodTotals[$currency]['count']   += 1;
                $txs_with_balance->push($tx);
            }
        }
        // 3) Per WorkPoint summary for this BN in the date range (group by work_point_id & currency)
        $wpQ = DB::table('micro_transactions')->select('work_point_id','currency',
                DB::raw("SUM(CASE WHEN tx_group IN ('".implode("','",$creditGroups)."') THEN amount ELSE 0 END) as credits"),
                DB::raw("SUM(CASE WHEN tx_group IN ('".implode("','",$debitGroups)."') THEN amount ELSE 0 END) as debits"),
                DB::raw("COUNT(*) as tx_count"))
            ->where('company_id', $user->company_id)->where('bank_network_id', $bn->id) ->where('status','Completed')
            ->whereDate('created_at','>=',$from) ->whereDate('created_at','<=',$to);
        if (!$isSuper) $wpQ->where('work_point_id',$user->work_point_id);
        if ($workPointFilter && $isSuper) $wpQ->where('work_point_id', $workPointFilter);
        $wpRows = $wpQ->groupBy('work_point_id','currency')->get();
        $perWorkpoint = [];
        foreach ($wpRows as $r) {
            $wpId = $r->work_point_id ?: 0;
            if (!isset($perWorkpoint[$wpId])) $perWorkpoint[$wpId] = [];
            $net = (float)$r->credits - (float)$r->debits;
            $perWorkpoint[$wpId][$r->currency] = [
                'credits' => (float)$r->credits,
                'debits'  => (float)$r->debits,
                'net'     => $net,
                'tx_count'=> (int)$r->tx_count
            ];
        }
        $closingBalances = [];
        $allCurrencies = array_unique(array_merge(array_keys($opening), array_keys($periodTotals)));
        foreach ($allCurrencies as $cur) {
            $op = isset($opening[$cur]) ? (float)$opening[$cur] : 0.0;
            $pcredits = isset($periodTotals[$cur]) ? (float)$periodTotals[$cur]['credits'] : 0.0;
            $pdebits  = isset($periodTotals[$cur]) ? (float)$periodTotals[$cur]['debits']  : 0.0;
            $closingBalances[$cur] = $op + $pcredits - $pdebits;
        }
        // WorkPoints list for filter
        $workPoints = $isSuper ? WorkPoint::where('company_id',$user->company_id)->where('status','!=','Deleted')->get() : collect();
        return view('admin.micro.reports.bn_detailed', compact(
            'bn','from','to','txs_with_balance','opening','periodTotals','closingBalances','perWorkpoint','workPoints','workPointFilter'
        ));
    }
    //  Loans provission methods
    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function uploadMicroPublicFile($file, $folder = 'applications')
    {
        $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

        $publicRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $destFolder = $publicRoot . '/assets/micro/' . $folder;

        if (!File::exists($destFolder)) {
            File::makeDirectory($destFolder, 0755, true);
        }

        $file->move($destFolder, $filename);

        return 'assets/micro/' . $folder . '/' . $filename;
    }

    protected function unlinkPublicFile($relativePath = null)
    {
        if (!$relativePath) {
            return;
        }

        $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($relativePath, '/');

        if (File::exists($fullPath)) {
            @unlink($fullPath);
        }
    }
    protected function canAccessAllCompanies()
    {
        return auth()->user()->can('Access-All-Companies-Data');
    }

    protected function canAccessAllUnits()
    {
        return auth()->user()->can('Access-All-Units-Data');
    }

    protected function canAccessAllWorkPoints()
    {
        return auth()->user()->can('Access-All-WorkPoints-Data');
    }

    protected function getScopeIds(Request $request = null)
    {
        $user = auth()->user();

        $companyId = $this->canAccessAllCompanies()
            ? ($request && $request->filled('company_id') ? $request->company_id : $user->company_id)
            : $user->company_id;

        $compUnitId = $this->canAccessAllUnits()
            ? ($request && $request->filled('comp_unit_id') ? $request->comp_unit_id : $user->comp_unit_id)
            : $user->comp_unit_id;

        $workPointId = $this->canAccessAllWorkPoints()
            ? ($request && $request->filled('work_point_id') ? $request->work_point_id : $user->work_point_id)
            : $user->work_point_id;

        return [
            'company_id' => $companyId,
            'comp_unit_id' => $compUnitId,
            'work_point_id' => $workPointId,
        ];
    }

    protected function applyCompanyScope($query, $tableAlias = null)
    {
        $user = auth()->user();
        $companyCol = ($tableAlias ? $tableAlias . '.' : '') . 'company_id';
        $unitCol = ($tableAlias ? $tableAlias . '.' : '') . 'comp_unit_id';
        $workPointCol = ($tableAlias ? $tableAlias . '.' : '') . 'work_point_id';

        if (!$this->canAccessAllCompanies()) {
            $query->where($companyCol, $user->company_id);
        }

        if (!$this->canAccessAllUnits()) {
            $query->where($unitCol, $user->comp_unit_id);
        }

        if (!$this->canAccessAllWorkPoints()) {
            $query->where($workPointCol, $user->work_point_id);
        }

        return $query;
    }

    protected function scopeMasterData()
    {
        $user = auth()->user();

        $companies = $this->canAccessAllCompanies()
            ? CompanySite::where('status', '!=', 'Deleted')->orderBy('company_name')->get()
            : CompanySite::where('id', $user->company_id)->get();

        $units = Company_unit::where('status', '!=', 'Deleted');
        if (!$this->canAccessAllCompanies()) {
            $units->where('company_id', $user->company_id);
        }
        if (!$this->canAccessAllUnits()) {
            $units->where('id', $user->comp_unit_id);
        }
        $units = $units->orderBy('unit_name')->get();

        $workPoints = WorkPoint::where('status', '!=', 'Deleted');
        if (!$this->canAccessAllCompanies()) {
            $workPoints->where('company_id', $user->company_id);
        }
        if (!$this->canAccessAllUnits()) {
            $workPoints->where('comp_unit_id', $user->comp_unit_id);
        }
        if (!$this->canAccessAllWorkPoints()) {
            $workPoints->where('id', $user->work_point_id);
        }
        $workPoints = $workPoints->orderBy('work_name')->get();

        return compact('companies', 'units', 'workPoints');
    }

    protected function decryptIdOrBack($id)
    {
        try {
            return decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid id');
            return false;
        }
    }

    protected function ensureApplicationEditable($application)
    {
        if (in_array($application->approval_status, ['Approved']) || in_array($application->disbursement_status, ['Cashed-Out'])) {
            Alert::error('Not Allowed', 'Approved or cashed out application cannot be edited or deleted.');
            return false;
        }

        return true;
    }

    protected function ensureBelongsToScope($row)
    {
        $user = auth()->user();

        if (!$this->canAccessAllCompanies() && isset($row->company_id) && $row->company_id != $user->company_id) {
            Alert::error('Unauthorized', '');
            return false;
        }

        if (!$this->canAccessAllUnits() && isset($row->comp_unit_id) && $row->comp_unit_id != $user->comp_unit_id) {
            Alert::error('Unauthorized', '');
            return false;
        }

        if (!$this->canAccessAllWorkPoints() && isset($row->work_point_id) && $row->work_point_id != $user->work_point_id) {
            Alert::error('Unauthorized', '');
            return false;
        }

        return true;
    }

    protected function generateApplicationNo()
    {
        return 'MFL-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    protected function calculatePenaltyBaseAmount($application)
    {
        if ($application->penalty_basis == 'full_loan') {
            return (float) $application->approved_amount;
        }

        $paidPrincipal = (float) $application->repayments()->sum('principal_paid');
        $remaining = (float) $application->approved_amount - $paidPrincipal;

        return $remaining > 0 ? $remaining : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $loanQ = MicroLoanApplication::query();
        $this->applyCompanyScope($loanQ);

        $repayQ = MicroLoanRepayment::query()
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_repayments.loan_application_id');
        $this->applyCompanyScope($repayQ, 'micro_loan_applications');

        $costQ = MicroCost::query();
        $this->applyCompanyScope($costQ);

        $incomeQ = MicroOtherIncome::query();
        $this->applyCompanyScope($incomeQ);

        $reminderQ = MicroLoanReminder::query()
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_reminders.loan_application_id');
        $this->applyCompanyScope($reminderQ, 'micro_loan_applications');

        $penaltyQ = MicroLoanPenalty::query()
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_penalties.loan_application_id');
        $this->applyCompanyScope($penaltyQ, 'micro_loan_applications');

        $totalApplications = (clone $loanQ)->count();
        $totalApproved = (clone $loanQ)->where('approval_status', 'Approved')->count();
        $totalActiveLoans = (clone $loanQ)->whereIn('loan_status', ['Cashed-Out', 'Active'])->count();
        $overdueLoans = (clone $loanQ)
            ->whereDate('expected_end_date', '<', date('Y-m-d'))
            ->whereNotIn('loan_status', ['Closed'])
            ->count();

        $totalLoanAmount = (clone $loanQ)->sum('approved_amount');
        $returnedAmount = (clone $repayQ)->sum('micro_loan_repayments.amount_paid');
        $penaltyAmount = (clone $penaltyQ)->sum('micro_loan_penalties.penalty_amount');
        $officeCost = (clone $costQ)->where('cost_type', 'Office')->sum('amount');
        $recoverableCost = (clone $costQ)->where('cost_type', 'ApplicantRecoverable')->sum('amount');
        $otherIncome = (clone $incomeQ)->sum('amount');
        $reminderCharges = 0;

        // Graph 1: applications by status
        $statusLabels = ['Submitted', 'Verified', 'Approved', 'Rejected', 'Declined', 'Active', 'Closed', 'Defaulted'];
        $statusData = [];
        foreach ($statusLabels as $status) {
            $statusData[] = (clone $loanQ)->where('loan_status', $status)->count();
        }

        // Graph 2: monthly applications current year
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[] = (clone $loanQ)
                ->whereYear('application_date', date('Y'))
                ->whereMonth('application_date', $m)
                ->count();
        }

        // Graph 3: company vs approved amount
        $companyChartLabels = [];
        $companyChartData = [];
        $companies = CompanySite::where('status', '!=', 'Deleted')->orderBy('company_name')->get();
        foreach ($companies as $company) {
            if (!$this->canAccessAllCompanies() && auth()->user()->company_id != $company->id) {
                continue;
            }

            $companyChartLabels[] = $company->company_name;
            $companyChartData[] = MicroLoanApplication::where('company_id', $company->id)
                ->where('approval_status', 'Approved')
                ->sum('approved_amount');
        }

        return view('admin.micro.dashboard', compact(
            'totalApplications',
            'totalApproved',
            'totalActiveLoans',
            'overdueLoans',
            'totalLoanAmount',
            'returnedAmount',
            'penaltyAmount',
            'officeCost',
            'recoverableCost',
            'otherIncome',
            'statusLabels',
            'statusData',
            'monthlyLabels',
            'monthlyData',
            'companyChartLabels',
            'companyChartData'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    public function indexSettings()
    {
        $items = MicroSetting::query();
        $this->applyCompanyScope($items);
        $items = $items->latest()->get();

        extract($this->scopeMasterData());

        return view('admin.micro.settings', compact('items', 'companies', 'units', 'workPoints'));
    }

    public function storeSettings(Request $request)
    {
        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'sms_token_cost' => 'required|numeric|min:0',
            'default_reminder_charge' => 'required|numeric|min:0',
            'default_penalty_basis' => ['required', Rule::in(['full_loan', 'remaining_balance'])],
            'status' => ['nullable', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroSetting::create([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'sms_token_cost' => $request->sms_token_cost,
            'default_reminder_charge' => $request->default_reminder_charge,
            'default_penalty_basis' => $request->default_penalty_basis,
            'status' => $request->status ?: 'Active',
        ]);

        Alert::success('Success', 'Settings saved.');
        return redirect()->route('micro.settings.index');
    }

    public function updateSettings(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroSetting::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $v = Validator::make($request->all(), [
            'sms_token_cost' => 'required|numeric|min:0',
            'default_reminder_charge' => 'required|numeric|min:0',
            'default_penalty_basis' => ['required', Rule::in(['full_loan', 'remaining_balance'])],
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $scope = $this->getScopeIds($request);

        $row->update([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'sms_token_cost' => $request->sms_token_cost,
            'default_reminder_charge' => $request->default_reminder_charge,
            'default_penalty_basis' => $request->default_penalty_basis,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Settings updated.');
        return redirect()->route('micro.settings.index');
    }

    /*
    |--------------------------------------------------------------------------
    | LOAN CATEGORIES
    |--------------------------------------------------------------------------
    */
    public function indexLoanCategories()
    {
        $items = MicroLoanCategory::where('status', '!=', 'Deleted')->orderBy('category_name')->get();
        return view('admin.micro.loan_categories', compact('items'));
    }

    public function storeLoanCategory(Request $request)
    {
        $v = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255|unique:micro_loan_categories,category_name',
            'description' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroLoanCategory::create([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Category saved.');
        return redirect()->route('micro.loan_categories.index');
    }

    public function updateLoanCategory(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanCategory::findOrFail($id);

        $v = Validator::make($request->all(), [
            'category_name' => ['required', 'string', 'max:255', Rule::unique('micro_loan_categories', 'category_name')->ignore($row->id)],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Category updated.');
        return redirect()->route('micro.loan_categories.index');
    }

    public function removeLoanCategory($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanCategory::findOrFail($id);
        $row->update(['status' => 'Deleted']);

        Alert::success('Success', 'Category removed.');
        return redirect()->route('micro.loan_categories.index');
    }

    /*
    |--------------------------------------------------------------------------
    | LOAN PRODUCTS
    |--------------------------------------------------------------------------
    */
    public function indexLoanProducts()
    {
        $items = MicroLoanProduct::with(['category', 'company', 'companyUnit', 'workPoint']);
        $this->applyCompanyScope($items);
        $items = $items->where('status', '!=', 'Deleted')->latest()->get();

        $categories = MicroLoanCategory::where('status', 'Active')->orderBy('category_name')->get();
        extract($this->scopeMasterData());

        return view('admin.micro.loan_products', compact('items', 'categories', 'companies', 'units', 'workPoints'));
    }

    public function storeLoanProduct(Request $request)
    {
        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'loan_category_id' => 'required|exists:micro_loan_categories,id',
            'product_name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'min_duration_months' => 'required|integer|min:1',
            'max_duration_months' => 'required|integer|min:1',
            'default_interest_rate' => 'required|numeric|min:0',
            'interest_method' => ['required', Rule::in(['flat', 'reducing'])],
            'default_penalty_percent_per_day' => 'required|numeric|min:0',
            'default_penalty_basis' => ['required', Rule::in(['full_loan', 'remaining_balance'])],
            'default_reminder_charge' => 'required|numeric|min:0',
            'status' => ['nullable', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroLoanProduct::create([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'loan_category_id' => $request->loan_category_id,
            'product_name' => $request->product_name,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'min_duration_months' => $request->min_duration_months,
            'max_duration_months' => $request->max_duration_months,
            'default_interest_rate' => $request->default_interest_rate,
            'interest_method' => $request->interest_method,
            'default_penalty_percent_per_day' => $request->default_penalty_percent_per_day,
            'default_penalty_basis' => $request->default_penalty_basis,
            'default_reminder_charge' => $request->default_reminder_charge,
            'status' => $request->status ?: 'Active',
        ]);

        Alert::success('Success', 'Loan product saved.');
        return redirect()->route('micro.loan_products.index');
    }

    public function updateLoanProduct(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanProduct::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'loan_category_id' => 'required|exists:micro_loan_categories,id',
            'product_name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'min_duration_months' => 'required|integer|min:1',
            'max_duration_months' => 'required|integer|min:1',
            'default_interest_rate' => 'required|numeric|min:0',
            'interest_method' => ['required', Rule::in(['flat', 'reducing'])],
            'default_penalty_percent_per_day' => 'required|numeric|min:0',
            'default_penalty_basis' => ['required', Rule::in(['full_loan', 'remaining_balance'])],
            'default_reminder_charge' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'loan_category_id' => $request->loan_category_id,
            'product_name' => $request->product_name,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'min_duration_months' => $request->min_duration_months,
            'max_duration_months' => $request->max_duration_months,
            'default_interest_rate' => $request->default_interest_rate,
            'interest_method' => $request->interest_method,
            'default_penalty_percent_per_day' => $request->default_penalty_percent_per_day,
            'default_penalty_basis' => $request->default_penalty_basis,
            'default_reminder_charge' => $request->default_reminder_charge,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Loan product updated.');
        return redirect()->route('micro.loan_products.index');
    }

    public function removeLoanProduct($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanProduct::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $row->update(['status' => 'Deleted']);

        Alert::success('Success', 'Loan product removed.');
        return redirect()->route('micro.loan_products.index');
    }

    /*
    |--------------------------------------------------------------------------
    | APPLICANTS
    |--------------------------------------------------------------------------
    */
    public function indexApplicants()
    {
        $items = MicroLoanApplicant::query();
        $this->applyCompanyScope($items);
        $items = $items->where('status', '!=', 'Deleted')->latest()->get();

        extract($this->scopeMasterData());

        return view('admin.micro.applicants', compact('items', 'companies', 'units', 'workPoints'));
    }

    public function storeApplicant(Request $request)
    {
        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'mobile_no' => ['required', 'regex:/^255[0-9]{9}$/'],
            'referred_phone' => ['nullable', 'regex:/^255[0-9]{9}$/'],
            'personal_email' => 'nullable|email|max:255',
            'work_email' => 'nullable|email|max:255',
            'referred_by' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroLoanApplicant::create([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'created_by' => auth()->id(),
            'applicant_type' => $request->applicant_type ?: 'Individual',
            'full_name' => $request->full_name,
            'trading_as' => $request->trading_as,
            'national_id_no' => $request->national_id_no,
            'passport_no' => $request->passport_no,
            'marital_status' => $request->marital_status,
            'date_of_birth' => $request->date_of_birth,
            'age' => $request->age,
            'postal_address' => $request->postal_address,
            'permanent_address' => $request->permanent_address,
            'personal_email' => $request->personal_email,
            'office_phone' => $request->office_phone,
            'mobile_no' => $request->mobile_no,
            'work_email' => $request->work_email,
            'residence_town' => $request->residence_town,
            'residence_estate' => $request->residence_estate,
            'residence_street' => $request->residence_street,
            'house_no' => $request->house_no,
            'residence_type' => $request->residence_type,
            'building_name' => $request->building_name,
            'landmark' => $request->landmark,
            'referred_by' => $request->referred_by,
            'referred_phone' => $request->referred_phone,
            'employer' => $request->employer,
            'employment_terms' => $request->employment_terms,
            'contract_duration_months' => $request->contract_duration_months,
            'employment_date' => $request->employment_date,
            'designation' => $request->designation,
            'payroll_no' => $request->payroll_no,
            'gross_salary' => $request->gross_salary ?: 0,
            'net_salary' => $request->net_salary ?: 0,
            'salary_pay_date' => $request->salary_pay_date,
            'department' => $request->department,
            'workstation' => $request->workstation,
            'branch_name' => $request->branch_name,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'kra_pin' => $request->kra_pin,
            'business_tin' => $request->business_tin,
            'business_physical_address' => $request->business_physical_address,
            'business_town' => $request->business_town,
            'business_building' => $request->business_building,
            'nature_of_business' => $request->nature_of_business,
            'business_premise' => $request->business_premise,
            'business_landmark' => $request->business_landmark,
            'annual_turnover' => $request->annual_turnover ?: 0,
            'years_in_business' => $request->years_in_business,
            'status' => $request->status ?: 'Active',
        ]);

        Alert::success('Success', 'Applicant saved.');
        return redirect()->route('micro.applicants.index');
    }

    public function updateApplicant(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplicant::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'mobile_no' => ['required', 'regex:/^255[0-9]{9}$/'],
            'referred_phone' => ['nullable', 'regex:/^255[0-9]{9}$/'],
            'personal_email' => 'nullable|email|max:255',
            'work_email' => 'nullable|email|max:255',
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'applicant_type' => $request->applicant_type ?: 'Individual',
            'full_name' => $request->full_name,
            'trading_as' => $request->trading_as,
            'national_id_no' => $request->national_id_no,
            'passport_no' => $request->passport_no,
            'marital_status' => $request->marital_status,
            'date_of_birth' => $request->date_of_birth,
            'age' => $request->age,
            'postal_address' => $request->postal_address,
            'permanent_address' => $request->permanent_address,
            'personal_email' => $request->personal_email,
            'office_phone' => $request->office_phone,
            'mobile_no' => $request->mobile_no,
            'work_email' => $request->work_email,
            'residence_town' => $request->residence_town,
            'residence_estate' => $request->residence_estate,
            'residence_street' => $request->residence_street,
            'house_no' => $request->house_no,
            'residence_type' => $request->residence_type,
            'building_name' => $request->building_name,
            'landmark' => $request->landmark,
            'referred_by' => $request->referred_by,
            'referred_phone' => $request->referred_phone,
            'employer' => $request->employer,
            'employment_terms' => $request->employment_terms,
            'contract_duration_months' => $request->contract_duration_months,
            'employment_date' => $request->employment_date,
            'designation' => $request->designation,
            'payroll_no' => $request->payroll_no,
            'gross_salary' => $request->gross_salary ?: 0,
            'net_salary' => $request->net_salary ?: 0,
            'salary_pay_date' => $request->salary_pay_date,
            'department' => $request->department,
            'workstation' => $request->workstation,
            'branch_name' => $request->branch_name,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'kra_pin' => $request->kra_pin,
            'business_tin' => $request->business_tin,
            'business_physical_address' => $request->business_physical_address,
            'business_town' => $request->business_town,
            'business_building' => $request->business_building,
            'nature_of_business' => $request->nature_of_business,
            'business_premise' => $request->business_premise,
            'business_landmark' => $request->business_landmark,
            'annual_turnover' => $request->annual_turnover ?: 0,
            'years_in_business' => $request->years_in_business,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Applicant updated.');
        return redirect()->route('micro.applicants.index');
    }

    public function removeApplicant($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplicant::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $row->update(['status' => 'Deleted']);

        Alert::success('Success', 'Applicant removed.');
        return redirect()->route('micro.applicants.index');
    }

    /*
    |--------------------------------------------------------------------------
    | APPLICATIONS
    |--------------------------------------------------------------------------
    */
    public function indexApplications()
    {
        $items = MicroLoanApplication::with(['applicant', 'category', 'product']);
        $this->applyCompanyScope($items);
        $items = $items->where('status', '!=', 'Deleted')->latest()->get();

        return view('admin.micro.applications.index', compact('items'));
    }

    public function createApplication()
    {
        $applicants = MicroLoanApplicant::where('status', 'Active');
        $this->applyCompanyScope($applicants);
        $applicants = $applicants->orderBy('full_name')->get();

        $categories = MicroLoanCategory::where('status', 'Active')->orderBy('category_name')->get();

        $products = MicroLoanProduct::where('status', 'Active');
        $this->applyCompanyScope($products);
        $products = $products->orderBy('product_name')->get();

        $settings = MicroSetting::query();
        $this->applyCompanyScope($settings);
        $settings = $settings->latest()->first();

        extract($this->scopeMasterData());

        return view('admin.micro.applications.create', compact(
            'applicants',
            'categories',
            'products',
            'settings',
            'companies',
            'units',
            'workPoints'
        ));
    }

    public function storeApplication(Request $request)
    {
        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'applicant_id' => 'required|exists:micro_loan_applicants,id',
            'loan_category_id' => 'required|exists:micro_loan_categories,id',
            'loan_product_id' => 'nullable|exists:micro_loan_products,id',
            'application_date' => 'required|date',
            'amount_applied' => 'required|numeric|min:0',
            'project_cost' => 'nullable|numeric|min:0',
            'own_contribution' => 'nullable|numeric|min:0',
            'loan_period_months' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'interest_method' => ['required', Rule::in(['flat', 'reducing'])],
            'penalty_percent_per_day' => 'required|numeric|min:0',
            'penalty_basis' => ['required', Rule::in(['full_loan', 'remaining_balance'])],
            'reminder_charge' => 'nullable|numeric|min:0',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $setting = MicroSetting::query();
        $this->applyCompanyScope($setting);
        $setting = $setting->latest()->first();

        $monthlyRepayment = 0;
        if ((float)$request->loan_period_months > 0) {
            $monthlyRepayment = ((float)$request->amount_applied / (float)$request->loan_period_months);
        }

        $application = MicroLoanApplication::create([
            'application_no' => $this->generateApplicationNo(),
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'applicant_id' => $request->applicant_id,
            'loan_category_id' => $request->loan_category_id,
            'loan_product_id' => $request->loan_product_id,
            'created_by' => auth()->id(),
            'application_date' => $request->application_date,
            'amount_applied' => $request->amount_applied,
            'approved_amount' => 0,
            'project_cost' => $request->project_cost ?: 0,
            'own_contribution' => $request->own_contribution ?: 0,
            'loan_period_months' => $request->loan_period_months,
            'monthly_repayment' => $monthlyRepayment,
            'interest_rate' => $request->interest_rate,
            'interest_method' => $request->interest_method,
            'penalty_percent_per_day' => $request->penalty_percent_per_day,
            'penalty_basis' => $request->penalty_basis,
            'reminder_charge' => $request->reminder_charge ?: 0,
            'sms_token_cost' => $setting ? $setting->sms_token_cost : 25,
            'purpose' => $request->purpose,
            'notes' => $request->notes,
            'verification_status' => 'Pending',
            'approval_status' => 'Pending',
            'disbursement_status' => 'Pending',
            'loan_status' => 'Submitted',
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Loan application saved.');
        return redirect()->route('micro.applications.show', encrypt($application->id));
    }

    public function showApplication($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $item = MicroLoanApplication::with([
            'applicant',
            'category',
            'product',
            'guarantors',
            'collaterals',
            'attachments',
            'repayments.attachments',
            'penalties',
            'reminders',
            'costs',
            'otherIncome'
        ])->findOrFail($id);

        if (!$this->ensureBelongsToScope($item)) return back();

        return view('admin.micro.applications.show', compact('item'));
    }

    public function editApplication($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $item = MicroLoanApplication::findOrFail($id);
        if (!$this->ensureBelongsToScope($item)) return back();
        if (!$this->ensureApplicationEditable($item)) return back();

        $applicants = MicroLoanApplicant::where('status', 'Active');
        $this->applyCompanyScope($applicants);
        $applicants = $applicants->orderBy('full_name')->get();

        $categories = MicroLoanCategory::where('status', 'Active')->orderBy('category_name')->get();

        $products = MicroLoanProduct::where('status', 'Active');
        $this->applyCompanyScope($products);
        $products = $products->orderBy('product_name')->get();

        extract($this->scopeMasterData());

        return view('admin.micro.applications.edit', compact('item', 'applicants', 'categories', 'products', 'companies', 'units', 'workPoints'));
    }

    public function updateApplication(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplication::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();
        if (!$this->ensureApplicationEditable($row)) return back();

        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'applicant_id' => 'required|exists:micro_loan_applicants,id',
            'loan_category_id' => 'required|exists:micro_loan_categories,id',
            'loan_product_id' => 'nullable|exists:micro_loan_products,id',
            'application_date' => 'required|date',
            'amount_applied' => 'required|numeric|min:0',
            'project_cost' => 'nullable|numeric|min:0',
            'own_contribution' => 'nullable|numeric|min:0',
            'loan_period_months' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'interest_method' => ['required', Rule::in(['flat', 'reducing'])],
            'penalty_percent_per_day' => 'required|numeric|min:0',
            'penalty_basis' => ['required', Rule::in(['full_loan', 'remaining_balance'])],
            'reminder_charge' => 'nullable|numeric|min:0',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $monthlyRepayment = 0;
        if ((float)$request->loan_period_months > 0) {
            $monthlyRepayment = ((float)$request->amount_applied / (float)$request->loan_period_months);
        }

        $row->update([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'applicant_id' => $request->applicant_id,
            'loan_category_id' => $request->loan_category_id,
            'loan_product_id' => $request->loan_product_id,
            'application_date' => $request->application_date,
            'amount_applied' => $request->amount_applied,
            'project_cost' => $request->project_cost ?: 0,
            'own_contribution' => $request->own_contribution ?: 0,
            'loan_period_months' => $request->loan_period_months,
            'monthly_repayment' => $monthlyRepayment,
            'interest_rate' => $request->interest_rate,
            'interest_method' => $request->interest_method,
            'penalty_percent_per_day' => $request->penalty_percent_per_day,
            'penalty_basis' => $request->penalty_basis,
            'reminder_charge' => $request->reminder_charge ?: 0,
            'purpose' => $request->purpose,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Loan application updated.');
        return redirect()->route('micro.applications.show', encrypt($row->id));
    }

    public function removeApplication($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplication::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();
        if (!$this->ensureApplicationEditable($row)) return back();

        $row->update(['status' => 'Deleted']);

        Alert::success('Success', 'Loan application removed.');
        return redirect()->route('micro.applications.index');
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICATION / APPROVAL / CASHOUT
    |--------------------------------------------------------------------------
    */
    public function verifyApplication(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplication::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        if ($row->approval_status == 'Approved') {
            Alert::error('Error', 'Already approved.');
            return back();
        }

        $v = Validator::make($request->all(), [
            'verification_remarks' => 'nullable|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'verification_status' => 'Verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_remarks' => $request->verification_remarks,
            'loan_status' => 'Verified',
        ]);

        Alert::success('Success', 'Application verified.');
        return back();
    }

    public function declineApplication(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplication::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $v = Validator::make($request->all(), [
            'verification_remarks' => 'required|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'verification_status' => 'Declined',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_remarks' => $request->verification_remarks,
            'loan_status' => 'Declined',
        ]);

        Alert::success('Success', 'Application declined.');
        return back();
    }

    public function approveApplication(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplication::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        if ($row->verification_status != 'Verified') {
            Alert::error('Error', 'Application must be verified first.');
            return back();
        }

        $v = Validator::make($request->all(), [
            'approved_amount' => 'required|numeric|min:0',
            'approval_remarks' => 'nullable|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $approvedAmount = (float) $request->approved_amount;
        $monthlyRepayment = 0;
        if ((float)$row->loan_period_months > 0) {
            $monthlyRepayment = $approvedAmount / (float)$row->loan_period_months;
        }

        $row->update([
            'approved_amount' => $approvedAmount,
            'monthly_repayment' => $monthlyRepayment,
            'approval_status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_remarks' => $request->approval_remarks,
            'loan_status' => 'Approved',
        ]);

        Alert::success('Success', 'Application approved.');
        return back();
    }

    public function rejectApplication(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanApplication::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $v = Validator::make($request->all(), [
            'approval_remarks' => 'required|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'approval_status' => 'Rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_remarks' => $request->approval_remarks,
            'loan_status' => 'Rejected',
        ]);

        Alert::success('Success', 'Application rejected.');
        return back();
    }

    public function cashoutApplication(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        DB::beginTransaction();

        try {
            $row = MicroLoanApplication::findOrFail($id);
            if (!$this->ensureBelongsToScope($row)) {
                DB::rollBack();
                return back();
            }

            if ($row->approval_status != 'Approved') {
                DB::rollBack();
                Alert::error('Error', 'Only approved loan can be cashed out.');
                return back();
            }

            if ($row->disbursement_status == 'Cashed-Out') {
                DB::rollBack();
                Alert::error('Error', 'Already cashed out.');
                return back();
            }

            $v = Validator::make($request->all(), [
                'disbursement_date' => 'required|date',
                'amount_disbursed' => 'required|numeric|min:0',
                'channel' => 'nullable|string|max:255',
                'reference_no' => 'nullable|string|max:255',
                'bank_or_network' => 'nullable|string|max:255',
                'remarks' => 'nullable|string',
            ]);
            if ($v->fails()) {
                DB::rollBack();
                return back()->withErrors($v)->withInput();
            }

            MicroLoanDisbursement::create([
                'loan_application_id' => $row->id,
                'disbursement_date' => $request->disbursement_date,
                'amount_disbursed' => $request->amount_disbursed,
                'channel' => $request->channel,
                'reference_no' => $request->reference_no,
                'bank_or_network' => $request->bank_or_network,
                'remarks' => $request->remarks,
                'disbursed_by' => auth()->id(),
                'status' => 'Active',
            ]);

            $endDate = date('Y-m-d', strtotime($request->disbursement_date . ' +' . ((int)$row->loan_period_months) . ' months'));

            $row->update([
                'cashout_date' => $request->disbursement_date,
                'expected_start_date' => $request->disbursement_date,
                'expected_end_date' => $endDate,
                'disbursement_status' => 'Cashed-Out',
                'loan_status' => 'Active',
            ]);

            DB::commit();
            Alert::success('Success', 'Loan cashed out successfully.');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            Alert::error('Error', $th->getMessage());
            return back();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GUARANTORS / REFEREES
    |--------------------------------------------------------------------------
    */
    public function storeGuarantor(Request $request, $id)
    {
        $appId = $this->decryptIdOrBack($id);
        if (!$appId) return back();

        $application = MicroLoanApplication::findOrFail($appId);
        if (!$this->ensureBelongsToScope($application)) return back();
        if (!$this->ensureApplicationEditable($application)) return back();

        $v = Validator::make($request->all(), [
            'relation_type' => ['required', Rule::in(['NextOfKin', 'Referral', 'Guarantor', 'Referee'])],
            'full_name' => 'required|string|max:255',
            'phone_no' => ['nullable', 'regex:/^255[0-9]{9}$/'],
            'relationship' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'work_email' => 'nullable|email|max:255',
            'branch' => 'nullable|string|max:255',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroLoanGuarantor::create([
            'loan_application_id' => $application->id,
            'relation_type' => $request->relation_type,
            'full_name' => $request->full_name,
            'phone_no' => $request->phone_no,
            'relationship' => $request->relationship,
            'email' => $request->email,
            'work_email' => $request->work_email,
            'branch' => $request->branch,
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Guarantor/referee saved.');
        return back();
    }

    public function removeGuarantor($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanGuarantor::findOrFail($id);
        $application = MicroLoanApplication::findOrFail($row->loan_application_id);

        if (!$this->ensureBelongsToScope($application)) return back();
        if (!$this->ensureApplicationEditable($application)) return back();

        $row->delete();

        Alert::success('Success', 'Removed.');
        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | COLLATERAL
    |--------------------------------------------------------------------------
    */
    public function storeCollateral(Request $request, $id)
    {
        $appId = $this->decryptIdOrBack($id);
        if (!$appId) return back();

        $application = MicroLoanApplication::findOrFail($appId);
        if (!$this->ensureBelongsToScope($application)) return back();
        if (!$this->ensureApplicationEditable($application)) return back();

        $v = Validator::make($request->all(), [
            'collateral_type' => ['required', Rule::in(['BusinessShare', 'Asset', 'Vehicle', 'Plot', 'House', 'LogBook', 'Other'])],
            'item_name' => 'required|string|max:255',
            'no_of_items' => 'nullable|integer|min:1',
            'serial_number' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:100',
            'original_cost' => 'nullable|numeric|min:0',
            'estimated_value' => 'nullable|numeric|min:0',
            'discounted_value' => 'nullable|numeric|min:0',
            'ownership_notes' => 'nullable|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroLoanCollateral::create([
            'loan_application_id' => $application->id,
            'collateral_type' => $request->collateral_type,
            'item_name' => $request->item_name,
            'no_of_items' => $request->no_of_items ?: 1,
            'serial_number' => $request->serial_number,
            'color' => $request->color,
            'original_cost' => $request->original_cost ?: 0,
            'estimated_value' => $request->estimated_value ?: 0,
            'discounted_value' => $request->discounted_value ?: 0,
            'ownership_notes' => $request->ownership_notes,
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Collateral saved.');
        return back();
    }

    public function removeCollateral($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanCollateral::findOrFail($id);
        $application = MicroLoanApplication::findOrFail($row->loan_application_id);

        if (!$this->ensureBelongsToScope($application)) return back();
        if (!$this->ensureApplicationEditable($application)) return back();

        $row->delete();

        Alert::success('Success', 'Collateral removed.');
        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | ATTACHMENTS
    |--------------------------------------------------------------------------
    */
    public function storeApplicationAttachment(Request $request, $id)
    {
        $appId = $this->decryptIdOrBack($id);
        if (!$appId) return back();
        $application = MicroLoanApplication::findOrFail($appId);
        if (!$this->ensureBelongsToScope($application)) return back();
        if (!$this->ensureApplicationEditable($application)) return back();
        $v = Validator::make($request->all(), [
            'attachment_type' => ['required', Rule::in([
                'ApplicationLetter',
                'Contract',
                'AssetDocument',
                'BorrowerImage',
                'RefereeImage',
                'NationalID',
                'Passport',
                'BusinessCertificate',
                'TCC',
                'LogBook',
                'CourtOrder',
                'RepaymentSlip',
                'Other'
            ])],
            'file' => 'required|file|max:10240',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();
        $file = $request->file('file');
        $relativePath = $this->uploadMicroPublicFile($file, 'applications');
        MicroLoanAttachment::create([
            'loan_application_id' => $application->id,
            'attachment_type' => $request->attachment_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $relativePath,
            'file_ext' => $file->getClientOriginalExtension(),
            'uploaded_by_name' => auth()->user()->name,
            'uploaded_by' => auth()->id(),
            'status' => 'Active',
        ]);
        Alert::success('Success', 'Attachment uploaded.');
        return back();
    }

    public function removeAttachment($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanAttachment::findOrFail($id);

        $application = null;
        if ($row->loan_application_id) {
            $application = MicroLoanApplication::find($row->loan_application_id);
        } elseif ($row->repayment_id) {
            $rep = MicroLoanRepayment::find($row->repayment_id);
            if ($rep) {
                $application = MicroLoanApplication::find($rep->loan_application_id);
            }
        }

        if ($application && !$this->ensureBelongsToScope($application)) return back();
        if ($application && !$this->ensureApplicationEditable($application) && !$row->repayment_id) return back();

        $this->unlinkPublicFile($row->file_path);

        $row->delete();

        Alert::success('Success', 'Attachment removed.');
        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | REPAYMENTS
    |--------------------------------------------------------------------------
    */
    public function indexRepayments()
    {
        $items = MicroLoanRepayment::with(['application.applicant', 'attachments']);
        $items->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_repayments.loan_application_id');
        $this->applyCompanyScope($items, 'micro_loan_applications');
        $items = $items->select('micro_loan_repayments.*')->where('micro_loan_repayments.status', '!=', 'Deleted')->latest('micro_loan_repayments.id')->get();

        $applications = MicroLoanApplication::with('applicant')
            ->whereIn('loan_status', ['Active', 'Cashed-Out', 'Closed', 'Defaulted']);
        $this->applyCompanyScope($applications);
        $applications = $applications->orderBy('application_no')->get();

        return view('admin.micro.repayments', compact('items', 'applications'));
    }

    public function storeRepayment(Request $request)
    {
        DB::beginTransaction();

        try {
            $v = Validator::make($request->all(), [
                'loan_application_id' => 'required|exists:micro_loan_applications,id',
                'repayment_date' => 'required|date',
                'amount_paid' => 'required|numeric|min:0',
                'principal_paid' => 'nullable|numeric|min:0',
                'interest_paid' => 'nullable|numeric|min:0',
                'penalty_paid' => 'nullable|numeric|min:0',
                'reminder_charge_paid' => 'nullable|numeric|min:0',
                'recoverable_cost_paid' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string|max:255',
                'reference_no' => 'nullable|string|max:255',
                'remarks' => 'nullable|string',
                'repayment_slip' => 'nullable|file|max:10240',
            ]);

            if ($v->fails()) {
                DB::rollBack();
                return back()->withErrors($v)->withInput();
            }

            $application = MicroLoanApplication::findOrFail($request->loan_application_id);
            if (!$this->ensureBelongsToScope($application)) {
                DB::rollBack();
                return back();
            }

            if (!in_array($application->loan_status, ['Active', 'Cashed-Out', 'Defaulted', 'Closed'])) {
                DB::rollBack();
                Alert::error('Error', 'Loan is not active for repayment.');
                return back();
            }

            $rep = MicroLoanRepayment::create([
                'loan_application_id' => $application->id,
                'repayment_date' => $request->repayment_date,
                'amount_paid' => $request->amount_paid,
                'principal_paid' => $request->principal_paid ?: 0,
                'interest_paid' => $request->interest_paid ?: 0,
                'penalty_paid' => $request->penalty_paid ?: 0,
                'reminder_charge_paid' => $request->reminder_charge_paid ?: 0,
                'recoverable_cost_paid' => $request->recoverable_cost_paid ?: 0,
                'payment_method' => $request->payment_method,
                'reference_no' => $request->reference_no,
                'remarks' => $request->remarks,
                'received_by' => auth()->id(),
                'status' => 'Active',
            ]);

            if ($request->hasFile('repayment_slip')) {
                $file = $request->file('repayment_slip');
                $relativePath = $this->uploadMicroPublicFile($file, 'repayments');

                MicroLoanAttachment::create([
                    'repayment_id' => $rep->id,
                    'attachment_type' => 'RepaymentSlip',
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $relativePath,
                    'file_ext' => $file->getClientOriginalExtension(),
                    'uploaded_by_name' => auth()->user()->name,
                    'uploaded_by' => auth()->id(),
                    'status' => 'Active',
                ]);
            }

            $totalPrincipalPaid = (float) $application->repayments()->sum('principal_paid');
            if ($totalPrincipalPaid >= (float) $application->approved_amount && (float) $application->approved_amount > 0) {
                $application->update(['loan_status' => 'Closed']);
            }

            DB::commit();
            Alert::success('Success', 'Repayment saved.');
            return redirect()->route('micro.repayments.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Alert::error('Error', $th->getMessage());
            return back();
        }
    }

    public function updateRepayment(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        DB::beginTransaction();

        try {
            $row = MicroLoanRepayment::findOrFail($id);
            $application = MicroLoanApplication::findOrFail($row->loan_application_id);

            if (!$this->ensureBelongsToScope($application)) {
                DB::rollBack();
                return back();
            }

            $v = Validator::make($request->all(), [
                'repayment_date' => 'required|date',
                'amount_paid' => 'required|numeric|min:0',
                'principal_paid' => 'nullable|numeric|min:0',
                'interest_paid' => 'nullable|numeric|min:0',
                'penalty_paid' => 'nullable|numeric|min:0',
                'reminder_charge_paid' => 'nullable|numeric|min:0',
                'recoverable_cost_paid' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string|max:255',
                'reference_no' => 'nullable|string|max:255',
                'remarks' => 'nullable|string',
                'status' => ['required', Rule::in(['Active', 'Deleted'])],
                'repayment_slip' => 'nullable|file|max:10240',
            ]);

            if ($v->fails()) {
                DB::rollBack();
                return back()->withErrors($v)->withInput();
            }

            $row->update([
                'repayment_date' => $request->repayment_date,
                'amount_paid' => $request->amount_paid,
                'principal_paid' => $request->principal_paid ?: 0,
                'interest_paid' => $request->interest_paid ?: 0,
                'penalty_paid' => $request->penalty_paid ?: 0,
                'reminder_charge_paid' => $request->reminder_charge_paid ?: 0,
                'recoverable_cost_paid' => $request->recoverable_cost_paid ?: 0,
                'payment_method' => $request->payment_method,
                'reference_no' => $request->reference_no,
                'remarks' => $request->remarks,
                'status' => $request->status,
            ]);

            if ($request->hasFile('repayment_slip')) {
                $oldAttachment = MicroLoanAttachment::where('repayment_id', $row->id)
                    ->where('attachment_type', 'RepaymentSlip')
                    ->latest()
                    ->first();

                if ($oldAttachment) {
                    $this->unlinkPublicFile($oldAttachment->file_path);
                }

                $file = $request->file('repayment_slip');
                $relativePath = $this->uploadMicroPublicFile($file, 'repayments');

                if ($oldAttachment) {
                    $oldAttachment->update([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $relativePath,
                        'file_ext' => $file->getClientOriginalExtension(),
                        'uploaded_by_name' => auth()->user()->name,
                        'uploaded_by' => auth()->id(),
                        'status' => 'Active',
                    ]);
                } else {
                    MicroLoanAttachment::create([
                        'repayment_id' => $row->id,
                        'attachment_type' => 'RepaymentSlip',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $relativePath,
                        'file_ext' => $file->getClientOriginalExtension(),
                        'uploaded_by_name' => auth()->user()->name,
                        'uploaded_by' => auth()->id(),
                        'status' => 'Active',
                    ]);
                }
            }

            DB::commit();
            Alert::success('Success', 'Repayment updated.');
            return redirect()->route('micro.repayments.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Alert::error('Error', $th->getMessage());
            return back();
        }
    }
    public function removeRepayment($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();
        DB::beginTransaction();
        try {
            $row = MicroLoanRepayment::findOrFail($id);
            $application = MicroLoanApplication::findOrFail($row->loan_application_id);
            if (!$this->ensureBelongsToScope($application)) {
                DB::rollBack();
                return back();
            }
            $attachments = MicroLoanAttachment::where('repayment_id', $row->id)->get();
            foreach ($attachments as $att) {
                $this->unlinkPublicFile($att->file_path);
                $att->delete();
            }
            $row->update(['status' => 'Deleted']);
            DB::commit();
            Alert::success('Success', 'Repayment removed.');
            return redirect()->route('micro.repayments.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Alert::error('Error', $th->getMessage());
            return back();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | PENALTIES
    |--------------------------------------------------------------------------
    */
    public function indexPenalties()
    {
        $items = MicroLoanPenalty::with('application.applicant')
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_penalties.loan_application_id');
        $this->applyCompanyScope($items, 'micro_loan_applications');
        $items = $items->select('micro_loan_penalties.*')->where('micro_loan_penalties.status', '!=', 'Deleted')->latest('micro_loan_penalties.id')->get();

        $applications = MicroLoanApplication::with('applicant')->whereIn('loan_status', ['Active', 'Defaulted', 'Closed']);
        $this->applyCompanyScope($applications);
        $applications = $applications->get();

        return view('admin.micro.penalties', compact('items', 'applications'));
    }

    public function storePenalty(Request $request)
    {
        $v = Validator::make($request->all(), [
            'loan_application_id' => 'required|exists:micro_loan_applications,id',
            'penalty_date' => 'required|date',
            'days_overdue' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $application = MicroLoanApplication::findOrFail($request->loan_application_id);
        if (!$this->ensureBelongsToScope($application)) return back();

        $baseAmount = $this->calculatePenaltyBaseAmount($application);
        $rate = (float) $application->penalty_percent_per_day;
        $days = (int) $request->days_overdue;
        $penaltyAmount = (($baseAmount * $rate) / 100) * $days;

        MicroLoanPenalty::create([
            'loan_application_id' => $application->id,
            'penalty_date' => $request->penalty_date,
            'days_overdue' => $days,
            'base_amount' => $baseAmount,
            'penalty_percent_per_day' => $rate,
            'penalty_basis' => $application->penalty_basis,
            'penalty_amount' => $penaltyAmount,
            'remarks' => $request->remarks,
            'created_by' => auth()->id(),
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Penalty recorded.');
        return redirect()->route('micro.penalties.index');
    }

    public function updatePenalty(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanPenalty::findOrFail($id);
        $application = MicroLoanApplication::findOrFail($row->loan_application_id);

        if (!$this->ensureBelongsToScope($application)) return back();

        $v = Validator::make($request->all(), [
            'penalty_date' => 'required|date',
            'days_overdue' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $baseAmount = $this->calculatePenaltyBaseAmount($application);
        $rate = (float) $row->penalty_percent_per_day;
        $days = (int) $request->days_overdue;
        $penaltyAmount = (($baseAmount * $rate) / 100) * $days;

        $row->update([
            'penalty_date' => $request->penalty_date,
            'days_overdue' => $days,
            'base_amount' => $baseAmount,
            'penalty_amount' => $penaltyAmount,
            'remarks' => $request->remarks,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Penalty updated.');
        return redirect()->route('micro.penalties.index');
    }

    public function removePenalty($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroLoanPenalty::findOrFail($id);
        $application = MicroLoanApplication::findOrFail($row->loan_application_id);

        if (!$this->ensureBelongsToScope($application)) return back();

        $row->update(['status' => 'Deleted']);

        Alert::success('Success', 'Penalty removed.');
        return redirect()->route('micro.penalties.index');
    }

    /*
    |--------------------------------------------------------------------------
    | REMINDERS
    |--------------------------------------------------------------------------
    */
    public function indexReminders()
    {
        $items = MicroLoanReminder::with('application.applicant')
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_reminders.loan_application_id');
        $this->applyCompanyScope($items, 'micro_loan_applications');
        $items = $items->select('micro_loan_reminders.*')->latest('micro_loan_reminders.id')->get();

        $applications = MicroLoanApplication::with('applicant')->whereIn('loan_status', ['Active', 'Defaulted']);
        $this->applyCompanyScope($applications);
        $applications = $applications->get();

        return view('admin.micro.reminders', compact('items', 'applications'));
    }

    public function sendReminder(Request $request, $id)
    {
        $appId = $this->decryptIdOrBack($id);
        if (!$appId) return back();

        $application = MicroLoanApplication::with('applicant')->findOrFail($appId);
        if (!$this->ensureBelongsToScope($application)) return back();

        $v = Validator::make($request->all(), [
            'reminder_date' => 'required|date',
            'message' => 'required|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $phone = optional($application->applicant)->mobile_no;
        if (!$phone || !preg_match('/^255[0-9]{9}$/', $phone)) {
            Alert::error('Error', 'Applicant mobile number is invalid for SMS.');
            return back();
        }

        $smsCharge = (float) $application->sms_token_cost + (float) $application->reminder_charge;

        MicroLoanReminder::create([
            'loan_application_id' => $application->id,
            'reminder_date' => $request->reminder_date,
            'phone_no' => $phone,
            'message' => $request->message,
            'sms_charge' => $smsCharge,
            'delivery_status' => 'Pending',
            'provider_reference' => null,
            'created_by' => auth()->id(),
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Reminder queued/recorded.');
        return redirect()->route('micro.reminders.index');
    }

    /*
    |--------------------------------------------------------------------------
    | COSTS
    |--------------------------------------------------------------------------
    */
    public function indexCosts()
    {
        $items = MicroCost::with('application.applicant');
        $this->applyCompanyScope($items);
        $items = $items->where('status', '!=', 'Deleted')->latest()->get();

        $applications = MicroLoanApplication::with('applicant');
        $this->applyCompanyScope($applications);
        $applications = $applications->get();

        extract($this->scopeMasterData());

        return view('admin.micro.costs', compact('items', 'applications', 'companies', 'units', 'workPoints'));
    }

    public function storeCost(Request $request)
    {
        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'loan_application_id' => 'nullable|exists:micro_loan_applications,id',
            'cost_type' => ['required', Rule::in(['Office', 'ApplicantRecoverable'])],
            'cost_date' => 'required|date',
            'cost_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroCost::create([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'loan_application_id' => $request->loan_application_id,
            'cost_type' => $request->cost_type,
            'cost_date' => $request->cost_date,
            'cost_name' => $request->cost_name,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'recorded_by' => auth()->id(),
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Cost saved.');
        return redirect()->route('micro.costs.index');
    }

    public function updateCost(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroCost::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'loan_application_id' => 'nullable|exists:micro_loan_applications,id',
            'cost_type' => ['required', Rule::in(['Office', 'ApplicantRecoverable'])],
            'cost_date' => 'required|date',
            'cost_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'loan_application_id' => $request->loan_application_id,
            'cost_type' => $request->cost_type,
            'cost_date' => $request->cost_date,
            'cost_name' => $request->cost_name,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Cost updated.');
        return redirect()->route('micro.costs.index');
    }

    public function removeCost($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroCost::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $row->update(['status' => 'Deleted']);

        Alert::success('Success', 'Cost removed.');
        return redirect()->route('micro.costs.index');
    }

    /*
    |--------------------------------------------------------------------------
    | OTHER INCOME
    |--------------------------------------------------------------------------
    */
    public function indexOtherIncome()
    {
        $items = MicroOtherIncome::with('application.applicant');
        $this->applyCompanyScope($items);
        $items = $items->where('status', '!=', 'Deleted')->latest()->get();

        $applications = MicroLoanApplication::with('applicant');
        $this->applyCompanyScope($applications);
        $applications = $applications->get();

        extract($this->scopeMasterData());

        return view('admin.micro.other_income', compact('items', 'applications', 'companies', 'units', 'workPoints'));
    }

    public function storeOtherIncome(Request $request)
    {
        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'loan_application_id' => 'nullable|exists:micro_loan_applications,id',
            'income_date' => 'required|date',
            'income_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        MicroOtherIncome::create([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'loan_application_id' => $request->loan_application_id,
            'income_date' => $request->income_date,
            'income_name' => $request->income_name,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'recorded_by' => auth()->id(),
            'status' => 'Active',
        ]);

        Alert::success('Success', 'Other income saved.');
        return redirect()->route('micro.other_income.index');
    }

    public function updateOtherIncome(Request $request, $id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroOtherIncome::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $scope = $this->getScopeIds($request);

        $v = Validator::make($request->all(), [
            'loan_application_id' => 'nullable|exists:micro_loan_applications,id',
            'income_date' => 'required|date',
            'income_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => ['required', Rule::in(['Active', 'Deleted'])],
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $row->update([
            'company_id' => $scope['company_id'],
            'comp_unit_id' => $scope['comp_unit_id'],
            'work_point_id' => $scope['work_point_id'],
            'loan_application_id' => $request->loan_application_id,
            'income_date' => $request->income_date,
            'income_name' => $request->income_name,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'status' => $request->status,
        ]);

        Alert::success('Success', 'Other income updated.');
        return redirect()->route('micro.other_income.index');
    }

    public function removeOtherIncome($id)
    {
        $id = $this->decryptIdOrBack($id);
        if (!$id) return back();

        $row = MicroOtherIncome::findOrFail($id);
        if (!$this->ensureBelongsToScope($row)) return back();

        $row->update(['status' => 'Deleted']);

        Alert::success('Success', 'Other income removed.');
        return redirect()->route('micro.other_income.index');
    }

    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */
    public function indexReports()
    {
        $loanQ = MicroLoanApplication::query();
        $this->applyCompanyScope($loanQ);

        $repayQ = MicroLoanRepayment::query()
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_repayments.loan_application_id');
        $this->applyCompanyScope($repayQ, 'micro_loan_applications');

        $penaltyQ = MicroLoanPenalty::query()
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_penalties.loan_application_id');
        $this->applyCompanyScope($penaltyQ, 'micro_loan_applications');

        $reminderQ = MicroLoanReminder::query()
            ->join('micro_loan_applications', 'micro_loan_applications.id', '=', 'micro_loan_reminders.loan_application_id');
        $this->applyCompanyScope($reminderQ, 'micro_loan_applications');

        $costQ = MicroCost::query();
        $this->applyCompanyScope($costQ);

        $otherIncomeQ = MicroOtherIncome::query();
        $this->applyCompanyScope($otherIncomeQ);

        $report = [
            'loan_amount' => (clone $loanQ)->sum('approved_amount'),
            'returned_amount' => (clone $repayQ)->sum('micro_loan_repayments.amount_paid'),
            'overdue_loans' => (clone $loanQ)->whereDate('expected_end_date', '<', date('Y-m-d'))->whereNotIn('loan_status', ['Closed'])->count(),
            'penalty_amount' => (clone $penaltyQ)->sum('micro_loan_penalties.penalty_amount'),
            'office_cost' => (clone $costQ)->where('cost_type', 'Office')->sum('amount'),
            'recoverable_cost' => (clone $costQ)->where('cost_type', 'ApplicantRecoverable')->sum('amount'),
            'other_income' => (clone $otherIncomeQ)->sum('amount'),
            'reminder_sent' => (clone $reminderQ)->count(),
            'reminder_cost' => (clone $reminderQ)->sum('micro_loan_reminders.sms_charge'),
        ];

        $report['income_total'] =
            $report['penalty_amount']
            + $report['other_income']
            + (clone $repayQ)->sum('micro_loan_repayments.interest_paid');

        $report['loss_unreturned'] = max(0, $report['loan_amount'] - $report['returned_amount']);

        return view('admin.micro.reports', compact('report'));
    }
}