<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\User;
use App\Models\AssetCategory;
use App\Models\AssetTransaction;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use App;
use Carbon\Carbon;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Validator;
class AssetsController extends Controller
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
    /* ----------------------------
       Asset Categories
       ---------------------------- */

    public function categoriesIndex()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $query = AssetCategory::where('status','!=','Deleted');
        // scope to company/workpoint like your example
        if ($isSuper) {
            $query->where('company_id', $user->company_id);
            $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->orderBy('work_name')->get();
        } else {
            $query->where('company_id', $user->company_id)->where(function($q) use ($user) {
                $q->whereNull('work_point_id')->orWhere('work_point_id', $user->work_point_id);
            });
            $workPoints = collect();
        }
        $categories = $query->orderBy('name')->get();
        return view('admin.assets.categories', compact('categories','workPoints','isSuper'));
    }

    public function storeCategory(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'name' => ['required','string','max:255'],
            'depreciation_rate' => ['required','numeric','min:0'],
            'code' => ['nullable','string','max:50'],
            'description' => ['nullable','string'],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['nullable','integer', Rule::exists('work_points','id')->where(fn($q) => $q->where('company_id', $user->company_id)->where('status','!=','Deleted'))];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $data = $request->only(['name','code','description','depreciation_rate']);
        $data['user_id'] = $user->id;
        $data['company_id'] = $user->company_id;
        $data['work_point_id'] = $isSuper ? $request->work_point_id : $user->work_point_id;
        $data['status'] = 'Active';

        AssetCategory::create($data);
        Alert::success('Congrats ' . Auth()->user()->name, 'Asset category saved successfully.');
        return redirect()->route('assets.categories');
    }

    public function updateCategory(Request $request, $id)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $cat = AssetCategory::findOrFail($id);
        if ($cat->company_id !== $user->company_id)

        Alert::error('Sorry ' . Auth()->user()->name, 'Unauthorized.');
        return back();
        $rules = [
            'name' => ['required','string','max:255'],
            'depreciation_rate' => ['required','numeric','min:0'],
            'code' => ['nullable','string','max:50'],
            'description' => ['nullable','string'],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['nullable','integer', Rule::exists('work_points','id')->where(fn($q) => $q->where('company_id', $user->company_id)->where('status','!=','Deleted'))];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        $cat->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'depreciation_rate' => $request->depreciation_rate,
            'work_point_id' => $isSuper ? $request->work_point_id : $cat->work_point_id
        ]);

        Alert::success('Congrats ' . Auth()->user()->name, 'Asset category updated successfully.');
        return redirect()->route('assets.categories');
    }

    public function removeCategory($id)
    {
        $user = auth()->user();
        $cat = AssetCategory::findOrFail($id);
        if ($cat->company_id !== $user->company_id) Alert::error('Sorry ' . Auth()->user()->name, 'Unauthorized.');
        return back();
        $cat->update(['status' => 'Deleted']);

        Alert::success('Congrats ' . Auth()->user()->name, 'Asset category removed successfully.');
        return redirect()->route('assets.categories');
    }

    /* ----------------------------
       Asset Transactions (Acquisition, Disposal, Revalue)
       ---------------------------- */

    public function assetsIndex()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $query = AssetTransaction::with('category')->where('status','!=','Deleted');

        if ($isSuper) {
            $query->where('company_id', $user->company_id);
            $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->orderBy('work_name')->get();
        } else {
            $query->where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id);
            $workPoints = collect();
        }

        $assets = $query->orderBy('created_at','desc')->get();
        $categories = AssetCategory::where('company_id', $user->company_id)->where('status','!=','Deleted')->orderBy('name')->get();
        return view('admin.assets.transactions', compact('assets','categories','workPoints','isSuper'));
    }
    /**
     * Store acquisition (transaction_type = acquisition)
     * We copy depreciation_rate from category at time of purchase.
     */
    public function storeAsset(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'asset_name' => ['required','string','max:255'],
            'asset_category_id' => ['nullable','integer', Rule::exists('asset_categories','id')->where(fn($q) => $q->where('company_id', $user->company_id)->where('status','!=','Deleted'))],
            'purchase_date' => ['required','date'],
            'purchase_cost' => ['required','numeric','min:0'],
            'useful_life_years' => ['nullable','integer','min:0'],
            'description' => ['nullable','string'],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(fn($q) => $q->where('company_id', $user->company_id)->where('status','!=','Deleted'))];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        // Determine depreciation rate
        $depRate = 0;
        if ($request->asset_category_id) {
            $cat = AssetCategory::where('id', $request->asset_category_id)->first();
            $depRate = $cat ? $cat->depreciation_rate : 0;
        }

        $asset = AssetTransaction::create([
            'asset_name' => $request->asset_name,
            'asset_tag' => $request->asset_tag,
            'asset_category_id' => $request->asset_category_id,
            'purchase_date' => $request->purchase_date,
            'purchase_cost' => round($request->purchase_cost, 2),
            'depreciation_rate' => $depRate,
            'useful_life_years' => $request->useful_life_years,
            'accumulated_depreciation' => 0,
            'transaction_type' => 'acquisition',
            'transaction_date' => $request->purchase_date,
            'description' => $request->description,
            'status' => 'Active',
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        ]);

        Alert::success('Congrats ' . Auth()->user()->name, 'Asset recorded successfully.');
        return redirect()->route('assets.index');
    }

    /**
     * Update main asset record (only allowed if not disposed)
     */
    public function updateAsset(Request $request, $id)
    {
        $user = auth()->user();
        $asset = AssetTransaction::findOrFail($id);
        if ($asset->company_id !== $user->company_id)
        Alert::error('Sorry ' . Auth()->user()->name, 'Unauthorized.');
        return back();;
        if ($asset->status === 'Disposed')
        Alert::warning('Sorry ' . Auth()->user()->name, 'Cannot edit disposed asset.');
        return back();

        $isSuper = $this->isSuperRole();

        $rules = [
            'asset_name' => ['required','string','max:255'],
            'asset_category_id' => ['nullable','integer', Rule::exists('asset_categories','id')->where(fn($q) => $q->where('company_id', $user->company_id)->where('status','!=','Deleted'))],
            'purchase_date' => ['required','date'],
            'purchase_cost' => ['required','numeric','min:0'],
            'useful_life_years' => ['nullable','integer','min:0'],
            'description' => ['nullable','string'],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(fn($q) => $q->where('company_id', $user->company_id)->where('status','!=','Deleted'))];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        // update depreciation_rate if category changed
        $depRate = $asset->depreciation_rate;
        if ($request->asset_category_id && $request->asset_category_id != $asset->asset_category_id) {
            $cat = AssetCategory::find($request->asset_category_id);
            $depRate = $cat ? $cat->depreciation_rate : $depRate;
        }

        $asset->update([
            'asset_name' => $request->asset_name,
            'asset_tag' => $request->asset_tag,
            'asset_category_id' => $request->asset_category_id,
            'purchase_date' => $request->purchase_date,
            'purchase_cost' => round($request->purchase_cost,2),
            'depreciation_rate' => $depRate,
            'useful_life_years' => $request->useful_life_years,
            'description' => $request->description,
            'work_point_id' => $isSuper ? $request->work_point_id : $asset->work_point_id,
        ]);

        Alert::success('Congrats ' . Auth()->user()->name, 'Asset updated successfully.');
        return redirect()->route('assets.index');
    }
    /**
     * Soft-delete asset (mark Status = Deleted)
     */
    public function removeAsset($id)
    {
        $user = auth()->user();
        $asset = AssetTransaction::findOrFail($id);
        if ($asset->company_id !== $user->company_id)
            Alert::error('Sorry ' . Auth()->user()->name, 'Unauthorized.');
        return back();
        $asset->update(['status' => 'Deleted']);
        Alert::success('Congrats ' . Auth()->user()->name, 'Asset removed successfully.');
        return redirect()->route('assets.index');
    }

    /**
     * Disposal action: mark as disposed and record disposal_value and date
     */
    public function disposeAsset(Request $request, $id)
    {
        $user = auth()->user();
        $asset = AssetTransaction::findOrFail($id);
        if ($asset->company_id !== $user->company_id)
            Alert::error('Sorry ' . Auth()->user()->name, 'Unauthorized.');
        return back();
        if ($asset->status === 'Disposed')

        Alert::info('Sorry ' . Auth()->user()->name, 'Already disposed.');
        return back();

        $v = Validator::make($request->all(), [
            'disposal_value' => ['required','numeric'],
            'transaction_date' => ['required','date'],
            'description' => ['nullable','string'],
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();

        // compute any final depreciation up to disposal date and increase accumulated_depreciation
        $disposalDate = Carbon::parse($request->transaction_date);
        $purchaseYear = Carbon::parse($asset->purchase_date)->year;
        $computedExtraDep = $asset->proratedDepreciationForYear($disposalDate->year);

        // A safer approach: compute depreciation between purchase_date and disposalDate across years.
        // For simplicity: compute days held total and prorate annual depreciation across full span:
        $daysHeld = $disposalDate->diffInDays(Carbon::parse($asset->purchase_date)) + 1;
        $daysTotalYears = 365;
        if (Carbon::parse($asset->purchase_date)->isLeapYear()) $daysTotalYears = 366;
        $annual = ($asset->purchase_cost * ($asset->depreciation_rate / 100));
        $finalDep = round($annual * ($daysHeld / $daysTotalYears), 2);

        // If accumulated_depreciation already has some value, we ensure it doesn't exceed cost
        $accum = min($asset->accumulated_depreciation + $finalDep, $asset->purchase_cost);

        $asset->update([
            'status' => 'Disposed',
            'disposal_value' => $request->disposal_value,
            'transaction_type' => 'disposal',
            'transaction_date' => $request->transaction_date,
            'accumulated_depreciation' => $accum,
            'description' => $request->description,
        ]);
        Alert::success('Congrats ' . Auth()->user()->name, 'Asset disposed successfully.');
        return redirect()->route('assets.index');
    }
    /**
     * Revaluation (resupluss): record revalue_amount and update book value accordingly.
     * revalue_amount is positive (increase) or negative (decrease).
     */
    public function revalueAsset(Request $request, $id)
    {
        $user = auth()->user();
        $asset = AssetTransaction::findOrFail($id);
        if ($asset->company_id !== $user->company_id)
        Alert::error('Sorry ' . Auth()->user()->name, 'Unauthorized.');
        return back();
        if ($asset->status !== 'Active')
            Alert::error('Sorry ' . Auth()->user()->name, 'Only active assets can be revalued.');
            return back();
        $v = Validator::make($request->all(), [
            'revalue_amount' => ['required','numeric'],
            'transaction_date' => ['required','date'],
            'description' => ['nullable','string'],
        ]);
        if ($v->fails()) return back()->withErrors($v)->withInput();
        $reval = round($request->revalue_amount, 2);
        $bookValue = max(0, $asset->purchase_cost - $asset->accumulated_depreciation);
        $newBookValue = $bookValue + $reval;
        if ($newBookValue < 0) $newBookValue = 0;
        $asset->update([
            'revalue_amount' => $reval,
            'transaction_type' => 'revaluation',
            'transaction_date' => $request->transaction_date,
            'purchase_cost' => round($asset->purchase_cost + $reval, 2),
            'description' => $request->description,
        ]);
        Alert::success('Congrats ' . Auth()->user()->name, 'Asset revalued successfully.');
        return redirect()->route('assets.index');
    }
    public function assertreport(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::now();
        $defaultYear = $today->year;

        $start_date = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::create($defaultYear, 1, 1)->startOfDay();
        $end_date = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::create($defaultYear, 12, 31)->endOfDay();

        $companyId = $user->company_id;
        $workPointId = $request->input('work_point_id') ?? null;
        $isSuper = in_array(optional($user)->role, ['Admin','CEO','Admin-Developer']);
        if (! $isSuper) {
            $workPointId = $user->work_point_id;
        }

        // load asset categories
        $assetTypes = AssetCategory::where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })->where('status','!=','Deleted')
            ->orderBy('name')->get();

        // initialise summary arrays keyed by category id
        $openingBalances = $additions = $revaluationValues = $disposalValues =
        $totalAsAtEnd = $depreciation = $cumulativeDepreciation = $bookValues =
        $closingBalancesPreviousYear = [];

        foreach ($assetTypes as $type) {
            $tid = $type->id;
            $openingBalances[$tid] = 0.0;
            $additions[$tid] = 0.0;
            $revaluationValues[$tid] = 0.0;
            $disposalValues[$tid] = 0.0;
            $totalAsAtEnd[$tid] = 0.0;
            $depreciation[$tid] = 0.0;
            $cumulativeDepreciation[$tid] = 0.0;
            $bookValues[$tid] = 0.0;
            $closingBalancesPreviousYear[$tid] = 0.0;
        }

        // base query
        $baseQuery = AssetTransaction::where('company_id', $companyId)
            ->where('status', '!=', 'Deleted');

        if ($workPointId) {
            $baseQuery->where('work_point_id', $workPointId);
        }

        // fetch transactions sets used in calculations
        $transactionsUpToEnd = (clone $baseQuery)
            ->where(function($q) use ($end_date) {
                $q->where(function($q2) use ($end_date) {
                    $q2->where('transaction_type', 'acquisition')
                        ->whereDate('purchase_date', '<=', $end_date->toDateString());
                });
                $q->orWhere(function($q3) use ($end_date) {
                    $q3->where('transaction_type', '!=', 'acquisition')
                        ->whereDate('transaction_date', '<=', $end_date->toDateString());
                });
            })->get();

        $transactionsBefore = (clone $baseQuery)
            ->where(function($q) use ($start_date) {
                $q->where(function($q2) use ($start_date) {
                    $q2->where('transaction_type', 'acquisition')
                        ->whereDate('purchase_date', '<', $start_date->toDateString());
                });
                $q->orWhere(function($q3) use ($start_date) {
                    $q3->where('transaction_type', '!=', 'acquisition')
                        ->whereDate('transaction_date', '<', $start_date->toDateString());
                });
            })->get();

        $transactionsInPeriod = (clone $baseQuery)
            ->where(function($q) use ($start_date, $end_date) {
                $q->where(function($q2) use ($start_date, $end_date) {
                    $q2->where('transaction_type', 'acquisition')
                        ->whereBetween(DB::raw('DATE(purchase_date)'), [$start_date->toDateString(), $end_date->toDateString()]);
                });
                $q->orWhere(function($q3) use ($start_date, $end_date) {
                    $q3->where('transaction_type', '!=', 'acquisition')
                        ->whereBetween(DB::raw('DATE(transaction_date)'), [$start_date->toDateString(), $end_date->toDateString()]);
                });
            })->get();

        // --- SUMMARY: Opening balances from transactionsBefore (historical cost)
        foreach ($transactionsBefore as $tx) {
            $cat = $tx->asset_category_id;
            if (!isset($openingBalances[$cat])) continue;
            if ($tx->transaction_type === 'acquisition') {
                $openingBalances[$cat] += floatval($tx->purchase_cost ?? 0);
            } elseif (in_array($tx->transaction_type, ['addition','revaluation'])) {
                $openingBalances[$cat] += floatval($tx->revalue_amount ?? 0);
                $revaluationValues[$cat] += floatval($tx->revalue_amount ?? 0); // included historically
            } elseif ($tx->transaction_type === 'disposal') {
                $openingBalances[$cat] -= floatval($tx->disposal_value ?? $tx->purchase_cost ?? 0);
            }
        }

        // --- SUMMARY: Period sums
        foreach ($transactionsInPeriod as $tx) {
            $cat = $tx->asset_category_id;
            if (!isset($additions[$cat])) continue;

            if ($tx->transaction_type === 'acquisition') {
                $additions[$cat] += floatval($tx->purchase_cost ?? 0);
            } elseif ($tx->transaction_type === 'addition') {
                $additions[$cat] += floatval($tx->revalue_amount ?? 0);
            } elseif ($tx->transaction_type === 'revaluation') {
                $revaluationValues[$cat] += floatval($tx->revalue_amount ?? 0);
            } elseif ($tx->transaction_type === 'disposal') {
                $disposalValues[$cat] += floatval($tx->disposal_value ?? $tx->purchase_cost ?? 0);
            }
        }

        // --- SUMMARY: total at end
        foreach ($assetTypes as $t) {
            $tid = $t->id;
            $totalAsAtEnd[$tid] = $openingBalances[$tid] + $additions[$tid] + $revaluationValues[$tid] - $disposalValues[$tid];
        }

        // Build disposal lookup by tag to limit depreciation after disposal
        $disposalByTag = [];
        $disposalsUpToEnd = $transactionsUpToEnd->filter(function($tx) {
            return $tx->transaction_type === 'disposal';
        });
        foreach ($disposalsUpToEnd as $dtx) {
            $tag = $dtx->asset_tag ?? null;
            $dDate = $dtx->transaction_date ? Carbon::parse($dtx->transaction_date) : null;
            if ($tag && $dDate) {
                if (!isset($disposalByTag[$tag]) || $dDate->lt($disposalByTag[$tag])) {
                    $disposalByTag[$tag] = $dDate;
                }
            }
        }

        // Depreciation & rates lookup (category-level)
        $categoryRates = $assetTypes->pluck('depreciation_rate','id')->toArray();

        // --- SUMMARY: category-level depreciation and cumulative depreciation
        $valueEventsUpToEnd = $transactionsUpToEnd->filter(function($tx) {
            return in_array($tx->transaction_type, ['acquisition','addition','revaluation']);
        });

        foreach ($valueEventsUpToEnd as $evt) {
            $catId = $evt->asset_category_id;
            if (!isset($cumulativeDepreciation[$catId])) continue;

            $amount = 0.0;
            $evtDate = null;
            if ($evt->transaction_type === 'acquisition') {
                $amount = floatval($evt->purchase_cost ?? 0);
                $evtDate = $evt->purchase_date ? Carbon::parse($evt->purchase_date)->startOfDay() : null;
            } else {
                $amount = floatval($evt->revalue_amount ?? 0);
                $evtDate = $evt->transaction_date ? Carbon::parse($evt->transaction_date)->startOfDay() : null;
            }
            if ($amount <= 0 || !$evtDate) continue;

            $annualRatePerc = floatval($evt->depreciation_rate ?? ($categoryRates[$catId] ?? 0));
            $annualRate = $annualRatePerc / 100.0;

            $tag = $evt->asset_tag ?? null;
            $disposedAt = ($tag && isset($disposalByTag[$tag])) ? $disposalByTag[$tag] : null;
            $trancheEndForCum = $disposedAt ? min(Carbon::parse($disposedAt)->endOfDay(), $end_date->copy()->endOfDay()) : $end_date->copy()->endOfDay();

            if ($trancheEndForCum->lt($evtDate)) continue;

            $daysOwnedCumulative = $evtDate->diffInDays($trancheEndForCum) + 1;
            $cumDepAmount = ($amount) * ($annualRate) * ($daysOwnedCumulative / 365.0);
            $cumulativeDepreciation[$catId] += $cumDepAmount;

            // depreciation during selected period (category-level)
            $periodStart = $start_date->copy();
            $periodEnd = $end_date->copy();
            $intStart = $evtDate->gt($periodStart) ? $evtDate : $periodStart;
            $intEnd = $trancheEndForCum->lt($periodEnd) ? $trancheEndForCum : $periodEnd;
            if ($intEnd->lt($intStart)) {
                $depDuring = 0;
            } else {
                $daysOwnedDuring = $intStart->diffInDays($intEnd) + 1;
                $depDuring = ($amount) * ($annualRate) * ($daysOwnedDuring / 365.0);
            }
            $depreciation[$catId] += $depDuring;
        }

        // rounding and book values for summaries
        foreach ($assetTypes as $type) {
            $tid = $type->id;
            $openingBalances[$tid] = round($openingBalances[$tid], 2);
            $additions[$tid] = round($additions[$tid], 2);
            $revaluationValues[$tid] = round($revaluationValues[$tid], 2);
            $disposalValues[$tid] = round($disposalValues[$tid], 2);
            $totalAsAtEnd[$tid] = round($totalAsAtEnd[$tid], 2);
            $depreciation[$tid] = round($depreciation[$tid], 2);
            $cumulativeDepreciation[$tid] = round($cumulativeDepreciation[$tid], 2);
            $bookValues[$tid] = round(max(0, $totalAsAtEnd[$tid] - $cumulativeDepreciation[$tid]), 2);

            // previous closing up to day before start_date
            $prevCut = $start_date->copy()->subDay()->endOfDay();
            $txsPrev = (clone $baseQuery)->where(function($q) use ($prevCut) {
                $q->where(function($q2) use ($prevCut) {
                    $q2->where('transaction_type', 'acquisition')
                        ->whereDate('purchase_date', '<=', $prevCut->toDateString());
                });
                $q->orWhere(function($q3) use ($prevCut) {
                    $q3->where('transaction_type', '!=', 'acquisition')
                        ->whereDate('transaction_date', '<=', $prevCut->toDateString());
                });
            })->get();

            $prevTotal = 0;
            foreach ($txsPrev as $txp) {
                if ($txp->asset_category_id != $tid) continue;
                if ($txp->transaction_type === 'acquisition') $prevTotal += floatval($txp->purchase_cost ?? 0);
                if (in_array($txp->transaction_type, ['addition','revaluation'])) $prevTotal += floatval($txp->revalue_amount ?? 0);
                if ($txp->transaction_type === 'disposal') $prevTotal -= floatval($txp->disposal_value ?? $txp->purchase_cost ?? 0);
            }
            $closingBalancesPreviousYear[$tid] = round($prevTotal, 2);
        }

        // --- DETAILS: build grouped details per category and per asset (asset_tag or fallback)
        $groups = $transactionsUpToEnd->groupBy(function($tx) {
            return $tx->asset_tag ?? ('__untagged_' . ($tx->asset_id ?? $tx->id));
        });

        $detailsByCategory = [];
        $prevCut = $start_date->copy()->subDay()->endOfDay();

        foreach ($groups as $groupKey => $txs) {
            $txs = $txs->sortBy(function($r){
                if ($r->transaction_type === 'acquisition') {
                    return $r->purchase_date ?: null;
                }
                return $r->transaction_date ?: null;
            })->values();

            $first = $txs->first();
            if (! $first) continue;
            $catId = $first->asset_category_id;
            if (! $catId) continue;

            if (!isset($detailsByCategory[$catId])) {
                $detailsByCategory[$catId] = [
                    'name' => optional($assetTypes->firstWhere('id', $catId))->name ?? 'Unknown',
                    'rows' => collect(),
                    'totals' => [
                        'opening' => 0.0,
                        'additions' => 0.0,
                        'revaluations' => 0.0,
                        'disposals' => 0.0,
                        'end' => 0.0,
                        'cum_dep_start' => 0.0,
                        'period_dep' => 0.0,
                        'book_start' => 0.0,
                        'book_end' => 0.0,
                    ],
                    'rate' => $categoryRates[$catId] ?? 0.0,
                ];
            }

            // serial/ref fallback (kept for fallback use)
            $serialDisplay = $first->asset_tag ?? ($first->serial_no ?? ($first->asset_serial ?? (strpos($groupKey,'__untagged_') === 0 ? 'Untagged (id:'.($first->asset_id ?? $first->id).')' : $groupKey)));

            // --- NEW: asset name lookup (tries common fields)
            $assetName = $first->asset_name ?? $first->name ?? $first->asset_title ?? null;

            // earliest purchase / value date for this group (used as purchase date)
            $purchaseDate = null;
            $acqDates = $txs->filter(function($t){ return $t->transaction_type === 'acquisition' && $t->purchase_date; })
                            ->pluck('purchase_date')->filter()->map(function($d){ return Carbon::parse($d); })->all();
            if (!empty($acqDates)) {
                $purchaseDate = collect($acqDates)->min();
            } else {
                $valDates = $txs->filter(function($t){ return in_array($t->transaction_type,['addition','revaluation']) && $t->transaction_date; })
                            ->pluck('transaction_date')->filter()->map(function($d){ return Carbon::parse($d); })->all();
                if (!empty($valDates)) {
                    $purchaseDate = collect($valDates)->min();
                } else {
                    $anyDate = $txs->pluck('purchase_date')->merge($txs->pluck('transaction_date'))->filter()->first();
                    if ($anyDate) $purchaseDate = Carbon::parse($anyDate);
                }
            }

            // compute opening, period additions/revals, disposals
            $openingCost = $additionsCost = $revaluationCost = $disposalCost = 0.0;
            foreach ($txs as $tt) {
                $d = null;
                if ($tt->transaction_type === 'acquisition') $d = $tt->purchase_date ? Carbon::parse($tt->purchase_date) : null;
                else $d = $tt->transaction_date ? Carbon::parse($tt->transaction_date) : null;

                if (!$d) continue;
                if ($d->lt($start_date)) {
                    if ($tt->transaction_type === 'acquisition') $openingCost += floatval($tt->purchase_cost ?? 0);
                    if (in_array($tt->transaction_type, ['addition','revaluation'])) $openingCost += floatval($tt->revalue_amount ?? 0);
                    if ($tt->transaction_type === 'disposal') $openingCost -= floatval($tt->disposal_value ?? $tt->purchase_cost ?? 0);
                }

                if ($d->between($start_date, $end_date)) {
                    if ($tt->transaction_type === 'acquisition') $additionsCost += floatval($tt->purchase_cost ?? 0);
                    if ($tt->transaction_type === 'addition') $additionsCost += floatval($tt->revalue_amount ?? 0);
                    if ($tt->transaction_type === 'revaluation') $revaluationCost += floatval($tt->revalue_amount ?? 0);
                    if ($tt->transaction_type === 'disposal') $disposalCost += floatval($tt->disposal_value ?? $tt->purchase_cost ?? 0);
                }
            }

            // compute depreciation cumulative up to end (for this group) and up to prevCut (start of period - 1)
            $valueEvents = $txs->filter(function($t) {
                return in_array($t->transaction_type, ['acquisition','addition','revaluation']);
            });

            // disposal date for this group (if any)
            $disposeTx = $txs->first(function($t) { return $t->transaction_type === 'disposal'; });
            $disposeDate = $disposeTx ? ($disposeTx->transaction_date ? Carbon::parse($disposeTx->transaction_date)->startOfDay() : null) : null;

            $cumDepToEnd = 0.0;
            $cumDepToStart = 0.0;
            $periodDep = 0.0;

            foreach ($valueEvents as $ev) {
                if ($ev->transaction_type === 'acquisition') {
                    $evDate = $ev->purchase_date ? Carbon::parse($ev->purchase_date)->startOfDay() : null;
                    $amt = floatval($ev->purchase_cost ?? 0);
                } else {
                    $evDate = $ev->transaction_date ? Carbon::parse($ev->transaction_date)->startOfDay() : null;
                    $amt = floatval($ev->revalue_amount ?? 0);
                }
                if (!$evDate || $amt <= 0) continue;

                $ratePerc = floatval($ev->depreciation_rate ?? ($categoryRates[$ev->asset_category_id] ?? 0));
                $rate = $ratePerc / 100.0;

                $trEndForThis = $disposeDate ? min($disposeDate->endOfDay(), $end_date->endOfDay()) : $end_date->endOfDay();
                if ($trEndForThis->gte($evDate)) {
                    $daysCum = $evDate->diffInDays($trEndForThis) + 1;
                    $cumDepToEnd += ($amt) * $rate * ($daysCum / 365.0);
                }

                // up to previous cut (day before start_date)
                $trEndForPrev = $disposeDate ? min($disposeDate->endOfDay(), $prevCut) : $prevCut;
                if ($trEndForPrev->gte($evDate)) {
                    $daysPrev = $evDate->diffInDays($trEndForPrev) + 1;
                    $cumDepToStart += ($amt) * $rate * ($daysPrev / 365.0);
                }

                // depreciation during selected period: overlap of [evDate, trEndForThis] and [start_date, end_date]
                $intStart = $evDate->gt($start_date) ? $evDate : $start_date;
                $intEnd = $trEndForThis->lt($end_date) ? $trEndForThis : $end_date;
                if ($intEnd->gte($intStart)) {
                    $daysDuring = $intStart->diffInDays($intEnd) + 1;
                    $periodDep += ($amt) * $rate * ($daysDuring / 365.0);
                }
            }

            // compute book values at start and end for this group
            $bookStart = max(0, $openingCost - $cumDepToStart);
            $totalAtEnd = $openingCost + $additionsCost + $revaluationCost - $disposalCost;
            $bookEnd = max(0, $totalAtEnd - $cumDepToEnd);

            // push row (include asset_name)
            $row = (object)[
                'asset_name' => $assetName ?? null,
                'serial_no' => $serialDisplay,
                'purchase_date' => $purchaseDate ? $purchaseDate->toDateString() : null,
                'opening_cost' => round($openingCost, 2),
                'additions' => round($additionsCost, 2),
                'revaluations' => round($revaluationCost, 2),
                'disposals' => round($disposalCost, 2),
                'total_at_end' => round($totalAtEnd, 2),
                'cum_dep_start' => round($cumDepToStart, 2),
                'cum_dep_end' => round($cumDepToEnd, 2),
                'depreciation_period' => round($periodDep, 2),
                'book_start' => round($bookStart, 2),
                'book_end' => round($bookEnd, 2),
                'rate' => round($detailsByCategory[$catId]['rate'] ?? ($categoryRates[$catId] ?? 0), 2),
                'category_id' => $catId,
            ];

            $detailsByCategory[$catId]['rows']->push($row);

            // accumulate totals for category
            $detailsByCategory[$catId]['totals']['opening'] += $row->opening_cost;
            $detailsByCategory[$catId]['totals']['additions'] += $row->additions;
            $detailsByCategory[$catId]['totals']['revaluations'] += $row->revaluations;
            $detailsByCategory[$catId]['totals']['disposals'] += $row->disposals;
            $detailsByCategory[$catId]['totals']['end'] += $row->total_at_end;
            $detailsByCategory[$catId]['totals']['cum_dep_start'] += $row->cum_dep_start;
            $detailsByCategory[$catId]['totals']['period_dep'] += $row->depreciation_period;
            $detailsByCategory[$catId]['totals']['book_start'] += $row->book_start;
            $detailsByCategory[$catId]['totals']['book_end'] += $row->book_end;
        }

        // Round category totals
        foreach ($detailsByCategory as $catId => $catData) {
            foreach ($catData['totals'] as $k => $v) {
                $detailsByCategory[$catId]['totals'][$k] = round($v, 2);
            }
        }

        // Prepare workPoints list for filter (scoped to company)
        $workPoints = WorkPoint::where('company_id', $companyId)
            ->where('status','!=','Deleted')
            ->orderBy('work_name')->get();

        // return view with the grouped details
        return view('admin.assets.report', [
            'assetTypes' => $assetTypes,
            'start_date' => $start_date->toDateString(),
            'end_date' => $end_date->toDateString(),
            'openingBalances' => $openingBalances,
            'additions' => $additions,
            'revaluationValues' => $revaluationValues,
            'disposalValues' => $disposalValues,
            'totalAsAtEnd' => $totalAsAtEnd,
            'depreciation' => $depreciation,
            'cumulativeDepreciation' => $cumulativeDepreciation,
            'bookValues' => $bookValues,
            'closingBalancesPreviousYear' => $closingBalancesPreviousYear,
            'detailsByCategory' => $detailsByCategory,
            'workPoints' => $workPoints,
            'selectedWorkPoint' => $workPointId,
        ]);
    }
    /*
    PHASE 2 - paste these methods inside AssetsController.
    Imports required if not alrea
    */
    public function assetImportForm(Request $request)
    {
        $user = auth()->user();
        $workPoints = WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        $selectedWorkPoint = $request->query('work_point_id') ?: $user->work_point_id;

        $categories = AssetCategory::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('name')->get();
        $previewRows = session('asset_import_preview', []);

        return view('admin.assets.import', compact('workPoints', 'selectedWorkPoint', 'categories', 'previewRows'));
    }
    public function assetImportExcel(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'asset_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
            'work_point_id' => ['required', 'integer'],
            'default_category_id' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            Alert::error('Sorry ' . $user->name, 'Please check the uploaded file and required fields.');
            return back()->withErrors($validator)->withInput();
        }

        $workPoint = WorkPoint::where('id', $request->work_point_id)
            ->where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->first();

        if (!$workPoint) {
            Alert::error('Sorry ' . $user->name, 'Invalid work point selected.');
            return back()->withInput();
        }

        $defaultCategory = null;
        if ($request->filled('default_category_id')) {
            $defaultCategory = AssetCategory::where('id', $request->default_category_id)
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->first();
        }

        try {
            $sheets = Excel::toCollection(null, $request->file('asset_file'));
        } catch (\Throwable $e) {
            Alert::error('Sorry ' . $user->name, 'The file could not be read. Please upload a valid Excel or CSV file.');
            return back()->withInput();
        }

        $sheet = $sheets->first();
        if (!$sheet || $sheet->count() < 2) {
            Alert::error('Sorry ' . $user->name, 'The uploaded file has no usable rows.');
            return back()->withInput();
        }

        $header = $sheet->first()->map(function ($value) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', (string) $value), '_'));
        })->toArray();

        $findIndex = function (array $names) use ($header) {
            foreach ($names as $name) {
                $idx = array_search($name, $header, true);
                if ($idx !== false) {
                    return $idx;
                }
            }
            return null;
        };

        $idxDescription = $findIndex(['description', 'asset_name', 'asset', 'item', 'items_properties', 'property']);
        $idxTag         = $findIndex(['asset_tag', 'tag', 'serial', 'serial_no', 'ref', 'reference']);
        $idxCategory    = $findIndex(['category', 'asset_category', 'class', 'group']);
        $idxDate        = $findIndex(['purchase_date', 'date', 'transaction_date', 'acquisition_date']);
        $idxCost        = $findIndex(['purchase_cost', 'cost', 'amount', 'cost_valuation', 'as_at_31_12_2024', 'as_at_end']);
        $idxOpeningCost = $findIndex(['opening_cost', 'as_at_1_1_2024', 'as_at_01_01_2024', 'opening']);
        $idxAdditions   = $findIndex(['additions', 'addition', 'additions_disposal']);
        $idxRate        = $findIndex(['depreciation_rate', 'dep_rate', 'rate', 'percentage']);
        $idxAccDep      = $findIndex(['accumulated_depreciation', 'depreciation_as_at_31_12_2024', 'accum_dep', 'depreciation_end']);

        if ($idxDescription === null) {
            Alert::error('Sorry ' . $user->name, 'Missing asset description/name column.');
            return back()->withInput();
        }

        $created = 0;
        $skipped = 0;
        $previewRows = [];

        foreach ($sheet->slice(1) as $lineNo => $row) {
            $get = function ($idx) use ($row) {
                return $idx === null ? null : trim((string) ($row[$idx] ?? ''));
            };

            $assetName = $get($idxDescription);
            if ($assetName === '' || $assetName === '-' || strtolower($assetName) === 'subtotal' || stripos($assetName, 'total ') === 0) {
                $skipped++;
                continue;
            }

            $assetTag = $get($idxTag) ?: null;
            $categoryName = $get($idxCategory);

            $category = $defaultCategory;
            if ($categoryName) {
                $category = AssetCategory::firstOrCreate(
                    [
                        'company_id' => $user->company_id,
                        'name' => $categoryName,
                        'status' => 'Active',
                    ],
                    [
                        'code' => null,
                        'description' => null,
                        'depreciation_rate' => 0,
                        'user_id' => $user->id,
                        'work_point_id' => $workPoint->id,
                    ]
                );
            }

            if (!$category) {
                $skipped++;
                continue;
            }

            $cleanNumber = function ($value) {
                $value = str_replace([',', ' ', 'Tshs', 'TZS'], '', (string) $value);
                if ($value === '' || $value === '-') return 0;
                return (float) preg_replace('/[^0-9.\-]/', '', $value);
            };

            $purchaseCost = $cleanNumber($get($idxCost));
            if ($purchaseCost <= 0) {
                $purchaseCost = $cleanNumber($get($idxOpeningCost)) + $cleanNumber($get($idxAdditions));
            }
            if ($purchaseCost <= 0) {
                $skipped++;
                continue;
            }

            $purchaseDate = $get($idxDate);
            try {
                $purchaseDate = $purchaseDate ? Carbon::parse($purchaseDate)->toDateString() : now()->startOfYear()->toDateString();
            } catch (\Throwable $e) {
                $purchaseDate = now()->startOfYear()->toDateString();
            }

            $rate = $cleanNumber($get($idxRate));
            if ($rate <= 0) {
                $rate = (float) ($category->depreciation_rate ?? 0);
            }

            $accDep = $cleanNumber($get($idxAccDep));

            AssetTransaction::create([
                'asset_name' => $assetName,
                'asset_tag' => $assetTag,
                'asset_category_id' => $category->id,
                'purchase_date' => $purchaseDate,
                'purchase_cost' => round($purchaseCost, 2),
                'depreciation_rate' => round($rate, 4),
                'useful_life_years' => null,
                'accumulated_depreciation' => round($accDep, 2),
                'transaction_type' => 'acquisition',
                'transaction_date' => $purchaseDate,
                'disposal_value' => null,
                'revalue_amount' => null,
                'description' => 'Imported from Excel/CSV asset register',
                'status' => 'Active',
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'work_point_id' => $workPoint->id,
            ]);

            $created++;
            if (count($previewRows) < 20) {
                $previewRows[] = [
                    'asset_name' => $assetName,
                    'asset_tag' => $assetTag,
                    'category' => $category->name,
                    'purchase_date' => $purchaseDate,
                    'purchase_cost' => $purchaseCost,
                    'rate' => $rate,
                ];
            }
        }

        session()->flash('asset_import_preview', $previewRows);

        Alert::success('Congrats ' . $user->name, 'Asset import completed. Saved: ' . $created . ', Skipped: ' . $skipped . '.');
        return redirect()->route('assets.import.form', ['work_point_id' => $workPoint->id]);
    }

}