<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\User;
use App\Models\AccntChart;
use App\Models\Department;
use App\Models\Section;
use App\Models\AccntTransaction;
use App\Models\AccntSubchart;
use App\Models\AssetTransaction;
use App\Models\MoneyRequest;
use Illuminate\Validation\Rule;
use App\Imports\AccTransImport;
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
use Illuminate\Database\Eloquent\Builder;
class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // parent::__construct();
    }
     // Accounting
    public function accounting()
    {
        return view('admin.home.accounting');
    }
     protected function isSuperRole()
    {
        return in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'], true);
    }
    // index: show charts according to role (Admin/CEO -> company charts; others -> workpoint charts)
    protected function globalAccountingRoles(): array
    {
        return ['Admin', 'CEO', 'Managing Director (MD)', 'Accountant Director (DAF)', 'Chief Accountant', 'Admin-Developer'];
    }

    protected function canAccessAllAccountingCodes($user): bool
    {
        return $user->can('View-Accounting-Code')
            || $user->can('Register-Accounting-Code')
            || $user->can('Edit-Accounting-Code')
            || $user->can('Delete-Accounting-Code')
            || $user->can('View-Sub-Accounting-Code')
            || $user->can('Register-Sub-Accounting-Code')
            || $user->can('Edit-Sub-Accounting-Code')
            || $user->can('Delete-Sub-Accounting-Code')
            || in_array($user->role, $this->globalAccountingRoles(), true);
    }

    protected function canPickWorkPointForAccounting($user): bool
    {
        return $this->canAccessAllAccountingCodes($user);
    }

    protected function resolveAccountingScopeFromWorkPoint($workPointId, $user)
    {
        $workPoint = WorkPoint::where('id', $workPointId)
            ->where('status', '!=', 'Deleted')
            ->first();

        if (!$workPoint) {
            return null;
        }

        return [
            'company_id'   => $workPoint->company_id,
            'comp_unit_id' => $workPoint->comp_unit_id,
            'work_point_id'=> $workPoint->id,
            'workpoint'    => $workPoint,
        ];
    }

    /**
     * 2 digit = root account -> accnt_charts
     */
    public function chartinfo()
    {
        $user = auth()->user();

        if ($this->canAccessAllAccountingCodes($user)) {
            $charts = AccntChart::with(['company', 'workpoint'])
                ->where('Status', '!=', 'Deleted')
                ->orderBy('AccCode')
                ->get();

            $workPoints = WorkPoint::where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        } else {
            $charts = AccntChart::with(['company', 'workpoint'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('Status', '!=', 'Deleted')
                ->orderBy('AccCode')
                ->get();

            $workPoints = collect();
        }

        $companies = CompanySite::where('status', '!=', 'Deleted')->orderBy('company_name')->get();

        return view('admin.accnt.charts', compact('charts', 'workPoints', 'companies'));
    }

    public function storechart(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'work_point_id'    => ['required', 'integer', Rule::exists('work_points', 'id')],
            'AccCode'          => ['required', 'digits:2', Rule::unique('accnt_charts', 'AccCode')],
            'AccDescription'   => ['required', 'string', 'max:255'],
            'AccType'          => ['required', Rule::in(['EQUITY','CAPITAL','INVENTORY','ADJUSTMENTS','FINANCIAL','EXPENSES','REVENUE','OTHER'])],
            'Status'           => ['nullable', Rule::in(['Active','Deleted'])],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $scope = $this->resolveAccountingScopeFromWorkPoint($request->work_point_id, $user);

        if (!$scope) {
            Alert::error('Error', 'Selected location is not valid.');
            return back()->withInput();
        }

        AccntChart::create([
            'company_id'      => $scope['company_id'],
            'work_point_id'   => $scope['work_point_id'],
            'AccCode'         => $request->AccCode,
            'AccDescription'  => $request->AccDescription,
            'AccType'         => $request->AccType,
            'Status'          => $request->Status ?? 'Active',
        ]);

        Alert::success('Success', 'Root accounting code created successfully.');
        return redirect()->route('accntcharts.index');
    }

    public function updatechart(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $chart = AccntChart::findOrFail($decrypted);

        $rules = [
            'work_point_id'    => ['required', 'integer', Rule::exists('work_points', 'id')],
            'AccCode'          => ['required', 'digits:2', Rule::unique('accnt_charts', 'AccCode')->ignore($chart->id)],
            'AccDescription'   => ['required', 'string', 'max:255'],
            'AccType'          => ['required', Rule::in(['EQUITY','CAPITAL','INVENTORY','ADJUSTMENTS','FINANCIAL','EXPENSES','REVENUE','OTHER'])],
            'Status'           => ['required', Rule::in(['Active','Deleted'])],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $scope = $this->resolveAccountingScopeFromWorkPoint($request->work_point_id, $user);

        if (!$scope) {
            Alert::error('Error', 'Selected location is not valid.');
            return back()->withInput();
        }

        $chart->update([
            'company_id'      => $scope['company_id'],
            'work_point_id'   => $scope['work_point_id'],
            'AccCode'         => $request->AccCode,
            'AccDescription'  => $request->AccDescription,
            'AccType'         => $request->AccType,
            'Status'          => $request->Status,
        ]);

        Alert::success('Success', 'Root accounting code updated successfully.');
        return redirect()->route('accntcharts.index');
    }

    public function removechart($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier');
            return back();
        }

        $chart = AccntChart::findOrFail($decrypted);

        $chart->update([
            'Status' => 'Deleted',
        ]);

        Alert::success('Success', 'Account chart removed successfully.');
        return redirect()->route('accntcharts.index');
    }

    /**
     * 3 digit = first root
     * 6 digit = accounting code
     * 8 digit = sub accounting code
     * all saved into accnt_subcharts linked to one root accnt_chart_id
     */
    public function subchartIndex()
    {
        $user = auth()->user();

        if ($this->canAccessAllAccountingCodes($user)) {
            $subcharts = AccntSubchart::with(['masterChart', 'company', 'workpoint'])
                ->where('Status', '!=', 'Deleted')
                ->orderBy('SubCode')
                ->get();

            $workPoints = WorkPoint::where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        } else {
            $subcharts = AccntSubchart::with(['masterChart', 'company', 'workpoint'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('Status', '!=', 'Deleted')
                ->orderBy('SubCode')
                ->get();

            $workPoints = collect();
        }

        $charts = AccntChart::where('Status', '!=', 'Deleted')
            ->orderBy('AccCode')
            ->get();

        $companies = CompanySite::where('status', '!=', 'Deleted')->orderBy('company_name')->get();
        return view('admin.accnt.subcharts', compact('subcharts', 'workPoints', 'companies', 'charts'));
    }
    public function storeSubchart(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'work_point_id'             => ['required', 'integer', Rule::exists('work_points', 'id')],
            'accnt_chart_id'            => ['required', 'integer', Rule::exists('accnt_charts', 'id')],
            'first_root_code'           => ['required', 'digits:3'],
            'first_root_name'           => ['required', 'string', 'max:255'],
            'accounting_code'           => ['required', 'digits:6'],
            'accounting_name'           => ['required', 'string', 'max:255'],
            'sub_accounting_code'       => ['required', 'digits:8'],
            'sub_accounting_name'       => ['required', 'string', 'max:255'],
            'Status'                    => ['nullable', Rule::in(['Active','Deleted'])],
        ];
        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $root = AccntChart::find($request->accnt_chart_id);
            if (!$root) {
                return;
            }
            if (substr($request->first_root_code, 0, 2) !== $root->AccCode) {
                $validator->errors()->add('first_root_code', 'First root code must start with selected root code.');
            }
            if (substr($request->accounting_code, 0, 3) !== $request->first_root_code) {
                $validator->errors()->add('accounting_code', 'Accounting code must start with first root code.');
            }
            if (substr($request->sub_accounting_code, 0, 6) !== $request->accounting_code) {
                $validator->errors()->add('sub_accounting_code', 'Sub accounting code must start with accounting code.');
            }
        });
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $scope = $this->resolveAccountingScopeFromWorkPoint($request->work_point_id, $user);
        if (!$scope) {
            Alert::error('Error', 'Selected location is not valid.');
            return back()->withInput();
        }
        $status = $request->Status ?? 'Active';
        $rows = [
            [
                'company_id'      => $scope['company_id'],
                'work_point_id'   => $scope['work_point_id'],
                'accnt_chart_id'  => $request->accnt_chart_id,
                'SubCode'         => $request->first_root_code,
                'SubDescription'  => $request->first_root_name,
                'Status'          => $status,
            ],
            [
                'company_id'      => $scope['company_id'],
                'work_point_id'   => $scope['work_point_id'],
                'accnt_chart_id'  => $request->accnt_chart_id,
                'SubCode'         => $request->accounting_code,
                'SubDescription'  => $request->accounting_name,
                'Status'          => $status,
            ],
            [
                'company_id'      => $scope['company_id'],
                'work_point_id'   => $scope['work_point_id'],
                'accnt_chart_id'  => $request->accnt_chart_id,
                'SubCode'         => $request->sub_accounting_code,
                'SubDescription'  => $request->sub_accounting_name,
                'Status'          => $status,
            ],
        ];
        try {
            DB::beginTransaction();
            foreach ($rows as $row) {
                AccntSubchart::create($row);
            }
            DB::commit();
            Alert::success('Success', 'Sub accounting levels created successfully.');
            return redirect()->route('accntsubcharts.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Subchart save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);
            Alert::error('Error', 'Data failed to save. Error: ' . $e->getMessage());
            return back()->withInput();
        }
    }
    public function updateSubchart(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $sub = AccntSubchart::findOrFail($decrypted);

        $rules = [
            'work_point_id'   => ['required', 'integer', Rule::exists('work_points', 'id')],
            'accnt_chart_id'  => ['required', 'integer', Rule::exists('accnt_charts', 'id')],
            'SubCode'         => ['required', 'digits_between:3,8'],
            'SubDescription'  => ['required', 'string', 'max:255'],
            'Status'          => ['required', Rule::in(['Active','Deleted'])],
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $root = AccntChart::find($request->accnt_chart_id);

            if (!$root) {
                return;
            }

            if (substr($request->SubCode, 0, 2) !== $root->AccCode) {
                $validator->errors()->add('SubCode', 'This code must belong to the selected root code.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $scope = $this->resolveAccountingScopeFromWorkPoint($request->work_point_id, $user);

        if (!$scope) {
            Alert::error('Error', 'Selected location is not valid.');
            return back()->withInput();
        }

        $sub->update([
            'company_id'      => $scope['company_id'],
            'work_point_id'   => $scope['work_point_id'],
            'accnt_chart_id'  => $request->accnt_chart_id,
            'SubCode'         => $request->SubCode,
            'SubDescription'  => $request->SubDescription,
            'Status'          => $request->Status,
        ]);

        Alert::success('Success', 'Sub accounting code updated successfully.');
        return redirect()->route('accntsubcharts.index');
    }

    public function removeSubchart($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier');
            return back();
        }

        $sub = AccntSubchart::findOrFail($decrypted);

        $sub->update([
            'Status' => 'Deleted',
        ]);

        Alert::success('Success', 'Sub chart removed successfully.');
        return redirect()->route('accntsubcharts.index');
    }
    // ---------- Transactions: final permission-based + requisition autofill ----------
    
    protected function canViewAllAccountingTransactions($user): bool
    {
        return $user->can('View-All-Accounting-Transactions')
            || in_array($user->role, $this->globalAccountingRoles(), true);
    }
    protected function canViewCompanyAccountingTransactions($user): bool
    {
        return $user->can('View-Company-Accounting-Transactions');
    }
    protected function canViewUnitAccountingTransactions($user): bool
    {
        return $user->can('View-Unit-Accounting-Transactions');
    }
    protected function canSelectWorkPoint($user): bool
    {
        return $this->canViewAllAccountingTransactions($user);
    }
    /**
     * Same logic as money request:
     * 8 digit selected code = sub accounting code
     * 6 digit parent under same accnt_chart_id = accounting code
     */
    protected function resolveParentAccountingSubCode(AccntSubchart $sub): ?AccntSubchart
    {
        $subCode = trim((string) $sub->SubCode);

        if (strlen($subCode) !== 8) {
            return null;
        }
        $parentCode = substr($subCode, 0, 6);
        return AccntSubchart::query() ->where('Status', '!=', 'Deleted')
            ->where('accnt_chart_id', $sub->accnt_chart_id)
            ->where('SubCode', $parentCode)->first();
    }

    protected function decorateTransactionAccountDisplay(?AccntSubchart $sub = null): array
    {
        $parent = $sub ? $this->resolveParentAccountingSubCode($sub) : null;
        return [
            'accounting_code_6' => $parent ? $parent->SubCode : null,
            'accounting_name_6' => $parent ? $parent->SubDescription : null,
            'sub_accounting_code_8' => $sub ? $sub->SubCode : null,
            'sub_accounting_name_8' => $sub ? $sub->SubDescription : null,
        ];
    }
    protected function selectableTransactionSubAccounts()
    {
        return AccntSubchart::query()->where('Status', '!=', 'Deleted')
            ->whereRaw('CHAR_LENGTH(TRIM(SubCode)) = 8')
            ->orderBy('SubCode')->get();
    }
    protected function resolveSubAccountInput($value, $companyId, $workPointId, $user)
    {
        $value = (string) $value;

        if (substr($value, 0, 2) === 's_') {
            $id = (int) substr($value, 2);
            $sub = AccntSubchart::where('id', $id)->where('Status', '!=', 'Deleted')
                ->first();
            if (!$sub) {
                throw new \Exception("Sub-account (s_{$id}) not found.");
            }
            $subCode = trim((string) $sub->SubCode);
            if (strlen($subCode) !== 8) {
                throw new \Exception('Please select only 8 digit sub accounting code.');
            }
            return ['sub_id' => $sub->id, 'master_chart_id' => $sub->accnt_chart_id];
        }
        if (substr($value, 0, 2) === 'c_') {
            $chartId = (int) substr($value, 2);
            $chart = AccntChart::where('id', $chartId)->where('Status', '!=', 'Deleted')->first();
            if (!$chart) {
                throw new \Exception("Account chart (c_{$chartId}) not found.");
            }
            $sub = AccntSubchart::where('accnt_chart_id', $chart->id)
                ->where('Status', '!=', 'Deleted')
                ->whereRaw('CHAR_LENGTH(TRIM(SubCode)) = 8')
                ->first();

            if (!$sub) {
                throw new \Exception('No selectable 8 digit sub account exists for this account chart.');
            }
            return ['sub_id' => $sub->id, 'master_chart_id' => $chart->id];
        }
        if (is_numeric($value)) {
            $nv = (int) $value;
            $sub = AccntSubchart::where('id', $nv)->where('Status', '!=', 'Deleted')->first();
            if ($sub) {
                $subCode = trim((string) $sub->SubCode);
                if (strlen($subCode) !== 8) {
                    throw new \Exception('Please select only 8 digit sub accounting code.');
                }
                return ['sub_id' => $sub->id, 'master_chart_id' => $sub->accnt_chart_id];
            }
            $chart = AccntChart::where('id', $nv)->where('Status', '!=', 'Deleted')->first();
            if ($chart) {
                return $this->resolveSubAccountInput('c_' . $chart->id, $companyId, $workPointId, $user);
            }
        }
        throw new \Exception('Invalid sub-account selection.');
    }
    protected function generatePcvNo($workPointId, $transDate)
    {
        $work = WorkPoint::find($workPointId);
        $workCode = strtoupper(trim(optional($work)->work_code ?: 'WRK'));
        $datePart = Carbon::parse($transDate)->format('dmY');
        $monthKey = Carbon::parse($transDate)->format('Ym');

        $count = AccntTransaction::where('work_point_id', $workPointId)
            ->whereRaw("DATE_FORMAT(trans_date, '%Y%m') = ?", [$monthKey])->select('transaction_group')
            ->distinct()->count('transaction_group');
        $next = $count + 1;
        return 'PCV' . str_pad($next, 4, '0', STR_PAD_LEFT) . '-' . $workCode . '/' . $datePart;
    }

    protected function approvedAccountingRequisitionIds(array $excludeTransactionGroups = []): array
    {
        $query = AccntTransaction::query()
            ->whereNotNull('requisition_id')
            ->where('approved', 'approved')
            ->where('Status', '!=', 'Deleted');

        if (!empty($excludeTransactionGroups)) {
            $query->whereNotIn('transaction_group', $excludeTransactionGroups);
        }

        return $query->pluck('requisition_id')->filter()->unique()->values()->all();
    }
    public function accntrans()
    {
        $user = auth()->user();

        $baseQuery = AccntTransaction::with([
                'subaccount','account','department','section','workpoint',
                'user', 'requisition','verifiedBy','approvedBy',
            ])->where('Status', '!=', 'Deleted')->visibleTo($user);
        $rows = $baseQuery->orderBy('trans_date', 'desc')->get()->groupBy('transaction_group');
        $groups = $rows->map(function ($groupRows, $groupKey) {
            $debit = $groupRows->firstWhere('type', 'debit');
            $credit = $groupRows->firstWhere('type', 'credit');
            $row = $debit ?: $credit;
            $debitDisplay = $this->decorateTransactionAccountDisplay(optional($debit)->subaccount);
            $creditDisplay = $this->decorateTransactionAccountDisplay(optional($credit)->subaccount);
            return (object) [
                'transaction_group' => $groupKey,
                'group_enc' => encrypt($groupKey),
                'pcv_no' => optional($row)->pcv_no,
                'trans_date' => optional($row)->trans_date,
                'reference' => optional($row)->reference,
                'request_no' => optional($row)->request_no,
                'requisition' => optional($row)->requisition,
                'requisition_id' => optional($row)->requisition_id,
                'check_no' => optional($row)->check_no,
                'category' => optional($row)->category,
                'payee' => optional($row)->payee,
                'currency' => optional($row)->currency,
                'exchange_rate' => optional($row)->exchange_rate,
                'source_amount' => optional($row)->source_amount,
                'memo' => optional($row)->memo,

                'debit_subaccount_id' => optional($debit)->sub_account_id,
                'debit_subaccount' => optional($debit)->subaccount,
                'credit_subaccount_id' => optional($credit)->sub_account_id,
                'credit_subaccount' => optional($credit)->subaccount,

                'debit_accounting_code_6' => $debitDisplay['accounting_code_6'],
                'debit_accounting_name_6' => $debitDisplay['accounting_name_6'],
                'debit_sub_accounting_code_8' => $debitDisplay['sub_accounting_code_8'],
                'debit_sub_accounting_name_8' => $debitDisplay['sub_accounting_name_8'],

                'credit_accounting_code_6' => $creditDisplay['accounting_code_6'],
                'credit_accounting_name_6' => $creditDisplay['accounting_name_6'],
                'credit_sub_accounting_code_8' => $creditDisplay['sub_accounting_code_8'],
                'credit_sub_accounting_name_8' => $creditDisplay['sub_accounting_name_8'],

                'amount' => optional($debit)->amount ?: optional($credit)->amount,
                'work_point' => optional($row)->workpoint,
                'work_point_id' => optional($row)->work_point_id,
                'department' => optional($row)->department,
                'department_id' => optional($row)->department_id,
                'section' => optional($row)->section,
                'section_id' => optional($row)->section_id,
                'verified' => optional($row)->verified ?: 'pending',
                'approved' => optional($row)->approved ?: 'pending',
                'verification_comment' => optional($row)->verification_comment,
                'approval_comment' => optional($row)->approval_comment,
            ];
        })->values();

        if ($this->canViewAllAccountingTransactions($user)) {
            $workPoints = WorkPoint::where('id', '!=', '1')
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        } elseif ($this->canViewCompanyAccountingTransactions($user)) {
            $workPoints = WorkPoint::where('id', '!=', '1')
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        } elseif ($this->canViewUnitAccountingTransactions($user)) {
            $workPoints = WorkPoint::where('id', '!=', '1')
                ->where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        } else {
            $workPoints = collect();
        }

        $charts = AccntChart::where('Status', '!=', 'Deleted')->orderBy('AccCode')->get();
        $subcharts = $this->selectableTransactionSubAccounts();
        $departments = Department::where('Status', '!=', 'Deleted')->orderBy('depName')->get();
        $sections = Section::where('Status', '!=', 'Deleted')->orderBy('secName')->get();

        $usedApprovedRequisitionIds = $this->approvedAccountingRequisitionIds();

        $approvedRequisitionsQuery = MoneyRequest::with(['workpoint','subAccount','section',])
                ->where('Status', 'Approved')->whereNotIn('id', $usedApprovedRequisitionIds);

        if (!$this->canViewAllAccountingTransactions($user)) {
            $approvedRequisitionsQuery->where('company_id', $user->company_id);
            if ($this->canViewUnitAccountingTransactions($user) && !$this->canViewCompanyAccountingTransactions($user)) {
                $approvedRequisitionsQuery->where('company_unit_id', $user->comp_unit_id);
            }
        }
        $approvedRequisitions = $approvedRequisitionsQuery->orderByDesc('RequestDate')->get();
        return view('admin.accnt.trans', [
            'groups' => $groups,
            'workPoints' => $workPoints,
            'charts' => $charts,
            'subcharts' => $subcharts,
            'accounts' => $charts,
            'departments' => $departments,
            'sections' => $sections,
            'approvedRequisitions' => $approvedRequisitions,
        ]);
    }

    public function storeaccntrans(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'trans_date' => ['required', 'date'],
            'currency' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'credit_account' => ['required', 'string'],
            'source_amount' => ['required', 'numeric', 'min:0.01'],
            'requisition_id' => ['nullable', 'integer', Rule::exists('money_requests', 'id')->where(function ($q) {
                $q->where('Status', 'Approved');
            })],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')->where('Status', '!=', 'Deleted')],
            'check_no' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string'],
            'payee' => ['required', 'string', 'max:255'],
            'debit_account' => ['nullable', 'string'],
        ];

        if ($this->canSelectWorkPoint($user)) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($user) {
                    $q->where('status', '!=', 'Deleted');

                    if (!$this->canViewAllAccountingTransactions($user)) {
                        $q->where('company_id', $user->company_id);
                    }
                }),
            ];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $workPointId = $this->canSelectWorkPoint($user) ? (int) $request->work_point_id : (int) $user->work_point_id;
        $work = WorkPoint::findOrFail($workPointId);
        $companyId = (int) $work->company_id;

        $requisition = null;
        $requestNo = null;
        $requisitionId = null;
        $debitAccountInput = $request->debit_account;
        $sectionId = (int) $request->section_id;
        $payee = $request->payee;
        $memo = $request->memo;
        $sourceAmount = (float) $request->source_amount;

        if ($request->filled('requisition_id')) {
            $alreadyRecorded = AccntTransaction::query()
                ->where('requisition_id', $request->requisition_id)
                ->where('approved', 'approved')->where('Status', '!=', 'Deleted')->exists();
            if ($alreadyRecorded) {
                return back()->withErrors([
                    'requisition_id' => 'This approved requisition is already recorded in approved accounting transactions.'
                ])->withInput();
            }
            $requisition = MoneyRequest::with(['subAccount', 'section'])->where('id', $request->requisition_id)
                ->where('Status', 'Approved')->first();
            if (!$requisition) {
                return back()->withErrors(['requisition_id' => 'Only approved requisitions are allowed.'])->withInput();
            }
            $requisitionId = $requisition->id;
            $requestNo = $requisition->RequestNo;

            $sourceAmount = (float) ($requisition->approved_amount ?: $requisition->total_amount);
            $payee = $requisition->PayeeName;
            $memo = $requisition->Description;
            $sectionId = (int) $requisition->section_id;
            $debitAccountInput = $requisition->sub_account_id ? ('s_' . $requisition->sub_account_id) : null;

            if (!$debitAccountInput) {
                return back()->withErrors(['requisition_id' => 'Selected requisition has no sub account.'])->withInput();
            }
        } else {
            if (!$request->filled('debit_account')) {
                return back()->withErrors(['debit_account' => 'Debit sub account is required when no requisition is selected.'])->withInput();
            }
        }
        if ($debitAccountInput === $request->credit_account) {
            return back()->withErrors(['credit_account' => 'Debit and Credit sub accounts cannot be the same'])->withInput();
        }
        $section = Section::findOrFail($sectionId);
        $departmentId = $section->dept_id;

        $exchangeRate = (float) $request->exchange_rate;
        $amount = round($sourceAmount * $exchangeRate, 2);
        $category = $request->filled('check_no') ? 'Bank' : 'Cash';
        $pcvNo = $this->generatePcvNo($workPointId, $request->trans_date);
        $transactionGroup = (string) Str::uuid();

        DB::beginTransaction();
        try {
            $debitResolved = $this->resolveSubAccountInput($debitAccountInput, $companyId, $workPointId, $user);
            $creditResolved = $this->resolveSubAccountInput($request->credit_account, $companyId, $workPointId, $user);

            AccntTransaction::create([
                'transaction_group' => $transactionGroup,
                'pcv_no' => $pcvNo,
                'trans_date' => $request->trans_date,
                'reference' => $pcvNo . '-D1',
                'check_no' => $request->check_no,
                'request_no' => $requestNo,
                'requisition_id' => $requisitionId,
                'category' => $category,
                'currency' => $request->currency,
                'exchange_rate' => $exchangeRate,
                'memo' => $memo,
                'payee' => $payee,
                'user_id' => $user->id,
                'company_id' => $companyId,
                'work_point_id' => $workPointId,
                'account_id' => $debitResolved['master_chart_id'],
                'sub_account_id' => $debitResolved['sub_id'],
                'department_id' => $departmentId,
                'section_id' => $sectionId,
                'type' => 'debit',
                'amount' => $amount,
                'source_amount' => $sourceAmount,
                'imported_from_excel' => false,
                'Status' => 'Active',
                'verified' => 'pending',
                'approved' => 'pending',
            ]);

            AccntTransaction::create([
                'transaction_group' => $transactionGroup,
                'pcv_no' => $pcvNo,
                'trans_date' => $request->trans_date,
                'reference' => $pcvNo . '-C2',
                'check_no' => $request->check_no,
                'request_no' => $requestNo,
                'requisition_id' => $requisitionId,
                'category' => $category,
                'currency' => $request->currency,
                'exchange_rate' => $exchangeRate,
                'memo' => $memo,
                'payee' => $payee,
                'user_id' => $user->id,
                'company_id' => $companyId,
                'work_point_id' => $workPointId,
                'account_id' => $creditResolved['master_chart_id'],
                'sub_account_id' => $creditResolved['sub_id'],
                'department_id' => $departmentId,
                'section_id' => $sectionId,
                'type' => 'credit',
                'amount' => $amount,
                'source_amount' => $sourceAmount,
                'imported_from_excel' => false,
                'Status' => 'Active',
                'verified' => 'pending',
                'approved' => 'pending',
            ]);

            DB::commit();
            Alert::success('Success', 'Accounting transactions recorded.');
            return redirect()->route('accnttransactions.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Alert::error('Error', 'Technical error: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function updateaccntrans(Request $request, $id)
    {
        try {
            $groupUuid = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $rows = AccntTransaction::where('transaction_group', $groupUuid)->get();
        if ($rows->isEmpty()) {
            Alert::error('Not found');
            return back();
        }

        $first = $rows->first();

        if (!$this->canViewAllAccountingTransactions($user) && $first->company_id != $user->company_id) {
            Alert::error('Unauthorized');
            return back();
        }

        if ($first->approved === 'approved') {
            Alert::warning('Cannot edit approved transactions');
            return back();
        }

        $rules = [
            'trans_date' => ['required', 'date'],
            'currency' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'credit_account' => ['required', 'string'],
            'source_amount' => ['required', 'numeric', 'min:0.01'],
            'requisition_id' => ['nullable', 'integer', Rule::exists('money_requests', 'id')->where(function ($q) {
                $q->where('Status', 'Approved');
            })],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')->where('Status', '!=', 'Deleted')],
            'check_no' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string'],
            'payee' => ['required', 'string', 'max:255'],
            'debit_account' => ['nullable', 'string'],
        ];

        if ($this->canSelectWorkPoint($user)) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($user) {
                    $q->where('status', '!=', 'Deleted');

                    if (!$this->canViewAllAccountingTransactions($user)) {
                        $q->where('company_id', $user->company_id);
                    }
                }),
            ];
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $workPointId = $this->canSelectWorkPoint($user) ? (int) $request->work_point_id : (int) $user->work_point_id;
        $work = WorkPoint::findOrFail($workPointId);
        $companyId = (int) $work->company_id;

        $requisition = null;
        $requestNo = null;
        $requisitionId = null;
        $debitAccountInput = $request->debit_account;
        $sectionId = (int) $request->section_id;
        $payee = $request->payee;
        $memo = $request->memo;
        $sourceAmount = (float) $request->source_amount;

        if ($request->filled('requisition_id')) {
            $alreadyRecorded = AccntTransaction::query()
                ->where('requisition_id', $request->requisition_id)->where('approved', 'approved')
                ->where('Status', '!=', 'Deleted')->where('transaction_group', '!=', $groupUuid)
                ->exists();
            if ($alreadyRecorded) {
                return back()->withErrors([
                    'requisition_id' => 'This approved requisition is already recorded in another approved accounting transaction.'
                ])->withInput();
            }
            $requisition = MoneyRequest::with(['subAccount', 'section'])->where('id', $request->requisition_id)
                ->where('Status', 'Approved')->first();
            if (!$requisition) {
                return back()->withErrors(['requisition_id' => 'Only approved requisitions are allowed.'])->withInput();
            }
            $requisitionId = $requisition->id;
            $requestNo = $requisition->RequestNo;

            $sourceAmount = (float) ($requisition->approved_amount ?: $requisition->total_amount);
            $payee = $requisition->PayeeName;
            $memo = $requisition->Description;
            $sectionId = (int) $requisition->section_id;
            $debitAccountInput = $requisition->sub_account_id ? ('s_' . $requisition->sub_account_id) : null;
            if (!$debitAccountInput) {
                return back()->withErrors(['requisition_id' => 'Selected requisition has no sub account.'])->withInput();
            }
        } else {
            if (!$request->filled('debit_account')) {
                return back()->withErrors(['debit_account' => 'Debit sub account is required when no requisition is selected.'])->withInput();
            }
        }
        if ($debitAccountInput === $request->credit_account) {
            return back()->withErrors(['credit_account' => 'Debit and Credit sub accounts cannot be the same'])->withInput();
        }
        $section = Section::findOrFail($sectionId);
        $departmentId = $section->dept_id;

        $exchangeRate = (float) $request->exchange_rate;
        $amount = round($sourceAmount * $exchangeRate, 2);
        $category = $request->filled('check_no') ? 'Bank' : 'Cash';
        DB::beginTransaction();
        try {
            $debitResolved = $this->resolveSubAccountInput($debitAccountInput, $companyId, $workPointId, $user);
            $creditResolved = $this->resolveSubAccountInput($request->credit_account, $companyId, $workPointId, $user);

            $pcvNo = $first->pcv_no ?: $this->generatePcvNo($workPointId, $request->trans_date);

            $debitRow = $rows->firstWhere('type', 'debit') ?: $rows->first();
            $creditRow = $rows->firstWhere('type', 'credit');

            $debitRow->update([
                'pcv_no' => $pcvNo,
                'trans_date' => $request->trans_date,
                'reference' => $pcvNo . '-D1',
                'check_no' => $request->check_no,
                'request_no' => $requestNo,
                'requisition_id' => $requisitionId,
                'category' => $category,
                'currency' => $request->currency,
                'exchange_rate' => $exchangeRate,
                'memo' => $memo,
                'payee' => $payee,
                'user_id' => $user->id,
                'company_id' => $companyId,
                'work_point_id' => $workPointId,
                'account_id' => $debitResolved['master_chart_id'],
                'sub_account_id' => $debitResolved['sub_id'],
                'department_id' => $departmentId,
                'section_id' => $sectionId,
                'amount' => $amount,
                'source_amount' => $sourceAmount,
            ]);

            if ($creditRow) {
                $creditRow->update([
                    'pcv_no' => $pcvNo,
                    'trans_date' => $request->trans_date,
                    'reference' => $pcvNo . '-C2',
                    'check_no' => $request->check_no,
                    'request_no' => $requestNo,
                    'requisition_id' => $requisitionId,
                    'category' => $category,
                    'currency' => $request->currency,
                    'exchange_rate' => $exchangeRate,
                    'memo' => $memo,
                    'payee' => $payee,
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'work_point_id' => $workPointId,
                    'account_id' => $creditResolved['master_chart_id'],
                    'sub_account_id' => $creditResolved['sub_id'],
                    'department_id' => $departmentId,
                    'section_id' => $sectionId,
                    'amount' => $amount,
                    'source_amount' => $sourceAmount,
                ]);
            }

            DB::commit();
            Alert::success('Success', 'Accounting transactions updated.');
            return redirect()->route('accnttransactions.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Alert::error('Error', 'Technical error: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function removeaccntrans($id)
    {
        try {
            $groupUuid = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $rows = AccntTransaction::where('transaction_group', $groupUuid)->get();
        if ($rows->isEmpty()) {
            Alert::error('Not found');
            return back();
        }

        if (!$this->canViewAllAccountingTransactions($user) && $rows->first()->company_id != $user->company_id) {
            Alert::error('Unauthorized');
            return back();
        }

        $rows->each(function ($r) {
            $r->update(['Status' => 'Deleted']);
        });

        Alert::success('Success', 'Transactions removed.');
        return redirect()->route('accnttransactions.index');
    }

    public function verifyaccntrans(Request $request, $id)
    {
        if (!auth()->user()->can('Verify-Accounting-Transactions')) {
            abort(403);
        }

        try {
            $groupUuid = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Invalid identifier');
            return back();
        }

        $request->validate([
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $rows = AccntTransaction::where('transaction_group', $groupUuid)->get();
        if ($rows->isEmpty()) {
            Alert::error('Not found');
            return back();
        }

        foreach ($rows as $r) {
            $r->update([
                'verified' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_comment' => $request->comment,
            ]);
        }

        Alert::success('Success', 'Transactions verified.');
        return redirect()->route('accnttransactions.index');
    }

    public function approveaccntrans(Request $request, $id)
    {
        if (!auth()->user()->can('Approve-Accounting-Transactions')) {
            abort(403);
        }

        try {
            $groupUuid = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Invalid identifier');
            return back();
        }

        $request->validate([
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $rows = AccntTransaction::where('transaction_group', $groupUuid)->get();
        if ($rows->isEmpty()) {
            Alert::error('Not found');
            return back();
        }

        if ($rows->first()->verified !== 'verified') {
            Alert::warning('Transaction must be verified first.');
            return back();
        }

        foreach ($rows as $r) {
            $r->update([
                'approved' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_comment' => $request->comment,
            ]);
        }

        Alert::success('Success', 'Transactions approved.');
        return redirect()->route('accnttransactions.index');
    }

    public function importaccntrans(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            Excel::import(new AccTransImport(auth()->user()), $request->file('excel_file'));
            Alert::success('Success', 'Excel imported successfully.');
            return redirect()->route('accnttransactions.index');
        } catch (\Throwable $e) {
            Alert::error('Import failed', $e->getMessage());
            return back();
        }
    }
    //financial reports methods
public function reportaccntrans(Request $request)
{
    return view('admin.accnt.transreport');
}

protected function resolveLedgerPeriodDates(int $year, ?string $period = null): array
{
    $period = strtoupper(trim((string) ($period ?: 'ANNUAL')));
    switch ($period) {
        case 'H1': $start = Carbon::create($year, 1, 1)->startOfDay(); $end = Carbon::create($year, 6, 30)->endOfDay(); break;
        case 'H2': $start = Carbon::create($year, 7, 1)->startOfDay(); $end = Carbon::create($year, 12, 31)->endOfDay(); break;
        case 'Q1': $start = Carbon::create($year, 1, 1)->startOfDay(); $end = Carbon::create($year, 3, 31)->endOfDay(); break;
        case 'Q2': $start = Carbon::create($year, 4, 1)->startOfDay(); $end = Carbon::create($year, 6, 30)->endOfDay(); break;
        case 'Q3': $start = Carbon::create($year, 7, 1)->startOfDay(); $end = Carbon::create($year, 9, 30)->endOfDay(); break;
        case 'Q4': $start = Carbon::create($year, 10, 1)->startOfDay(); $end = Carbon::create($year, 12, 31)->endOfDay(); break;
        default: $period = 'ANNUAL'; $start = Carbon::create($year, 1, 1)->startOfDay(); $end = Carbon::create($year, 12, 31)->endOfDay(); break;
    }
    return [$period, $start, $end];
}

protected function reportDatesFromRequest(int $year, Request $request): array
{
    try { $start = $request->query('start_date') ? Carbon::parse($request->query('start_date'))->startOfDay() : Carbon::create($year, 1, 1)->startOfDay(); }
    catch (\Throwable $e) { $start = Carbon::create($year, 1, 1)->startOfDay(); }

    try { $end = $request->query('end_date') ? Carbon::parse($request->query('end_date'))->endOfDay() : Carbon::create($year, 12, 31)->endOfDay(); }
    catch (\Throwable $e) { $end = Carbon::create($year, 12, 31)->endOfDay(); }

    if ($end->lt($start)) { $tmp = $start; $start = $end->copy()->startOfDay(); $end = $tmp->copy()->endOfDay(); }
    return [$start, $end];
}

protected function requestedCompanyId(Request $request)
{
    return $request->query('company_id', $request->query('company_site_id'));
}

protected function reportFilterLists(Request $request): array
{
    $selectedCompany = $this->requestedCompanyId($request);

    $companies = CompanySite::where(function ($q) {
        $q->where('status', '!=', 'Deleted')->orWhereNull('status');
    })->orderBy('company_name')->get();

    $companyUnitsQuery = Company_unit::where(function ($q) {
        $q->where('status', '!=', 'Deleted')->orWhereNull('status');
    });

    if (!empty($selectedCompany)) { $companyUnitsQuery->where('company_id', $selectedCompany); }

    return [$companies, $companyUnitsQuery->orderBy('unit_name')->get()];
}

protected function selectedReportCompany(Request $request, $user)
{
    $selectedCompany = $this->requestedCompanyId($request);
    if (!empty($selectedCompany)) { return CompanySite::find($selectedCompany); }
    if (!empty($user->company_id)) { return CompanySite::find($user->company_id); }
    return CompanySite::find(1);
}

protected function holdingCompany()
{
    return CompanySite::find(1);
}

protected function commonReportViewData($year, Carbon $start, Carbon $end, Request $request, $user): array
{
    [$companies, $companyUnits] = $this->reportFilterLists($request);
    $selectedCompany = $this->requestedCompanyId($request);

    return [
        'year' => (int) $year,
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
        'companies' => $companies,
        'companySites' => $companies,
        'companyUnits' => $companyUnits,
        'selectedCompany' => $selectedCompany,
        'selectedCompanySite' => $selectedCompany,
        'selectedCompanyUnit' => $request->query('company_unit_id'),
        'reportCompany' => $this->selectedReportCompany($request, $user),
        'holdingCompany' => $this->holdingCompany(),
    ];
}

protected function approvedReportBaseQuery($user, Carbon $start, Carbon $end, Request $request)
{
    $selectedCompany = $this->requestedCompanyId($request);

    $query = AccntTransaction::with(['account','subaccount','workpoint','department','section','user'])
        ->where(function ($q) { $q->where('Status', '!=', 'Deleted')->orWhereNull('Status'); })
        ->whereBetween('trans_date', [$start->toDateString(), $end->toDateString()]);

    if (method_exists(AccntTransaction::class, 'scopeVisibleTo')) { $query->visibleTo($user); }
    elseif (!empty($user->company_id)) { $query->where('company_id', $user->company_id); }

    if (!empty($selectedCompany)) { $query->where('company_id', $selectedCompany); }

    if ($request->filled('company_unit_id')) {
        $unitId = $request->query('company_unit_id');
        $query->whereHas('workpoint', function ($q) use ($unitId) { $q->where('comp_unit_id', $unitId); });
    }

    return $query;
}

protected function transactionAccountDisplay($transaction): array
{
    $rawSubCode = optional($transaction->subaccount)->SubCode;
    $rawAccCode = optional($transaction->account)->AccCode;

    $code6 = null;
    $name6 = null;
    $code8 = null;
    $name8 = null;

    if (!empty($rawSubCode)) {
        $rawSubCode = trim((string) $rawSubCode);
        $code8 = strlen($rawSubCode) === 8 ? $rawSubCode : null;
        $name8 = optional($transaction->subaccount)->SubDescription;

        // Reports must be grouped by the 6 digit accounting code.  The posted
        // transaction uses the 8 digit sub-account, therefore the display name
        // must be resolved from the 6 digit parent when it exists.
        $code6 = strlen($rawSubCode) >= 6 ? substr($rawSubCode, 0, 6) : $rawSubCode;
        $parent = null;
        if (strlen($code6) === 6 && optional($transaction->subaccount)->accnt_chart_id) {
            $parent = AccntSubchart::where('accnt_chart_id', optional($transaction->subaccount)->accnt_chart_id)
                ->where('SubCode', $code6)
                ->where('Status', '!=', 'Deleted')
                ->first();
        }
        $name6 = optional($parent)->SubDescription ?: optional($transaction->subaccount)->SubDescription ?: optional($transaction->account)->AccDescription;
    }

    if (empty($code6) && !empty($rawAccCode)) {
        $code6 = (string) $rawAccCode;
    }

    if (empty($name6)) {
        $name6 = optional($transaction->account)->AccDescription ?: 'Unknown';
    }

    return [
        'accounting_code_6' => $code6,
        'accounting_name_6' => $name6,
        'sub_accounting_code_8' => $code8,
        'sub_accounting_name_8' => $name8,
    ];
}

protected function buildNetRowsByAccountingCode($transactions)
{
    return collect($transactions)->groupBy(function ($t) {
        $display = $this->transactionAccountDisplay($t);
        return ($display['accounting_code_6'] ?: 'NO-CODE') . '||' . ($display['accounting_name_6'] ?: 'Unknown');
    })->map(function ($rows, $key) {
        [$code, $name] = explode('||', $key);
        $first = $rows->first();
        $debitTotal = (float) $rows->where('type', 'debit')->sum('amount');
        $creditTotal = (float) $rows->where('type', 'credit')->sum('amount');
        $balance = $debitTotal - $creditTotal;

        return (object) [
            'account' => optional($first)->account,
            'account_id' => optional(optional($first)->account)->id,
            'accounting_code_6' => $code === 'NO-CODE' ? null : $code,
            'accounting_name_6' => $name === 'Unknown' ? null : $name,
            'debit_total' => $debitTotal,
            'credit_total' => $creditTotal,
            'debit' => $balance > 0 ? abs($balance) : 0,
            'credit' => $balance < 0 ? abs($balance) : 0,
            'balance' => $balance,
            'side' => $balance >= 0 ? 'debit' : 'credit',
        ];
    })->filter(fn($row) => !empty($row->accounting_code_6) && ((float) $row->debit !== 0.0 || (float) $row->credit !== 0.0))
      ->sortBy('accounting_code_6')->values();
}

protected function sumRowsByPrefix($rows, array $prefixes, string $normalSide = 'debit'): float
{
    $sum = 0.0;
    foreach ($rows as $row) {
        $code = (string) ($row->accounting_code_6 ?? '');
        foreach ($prefixes as $prefix) {
            if (str_starts_with($code, (string) $prefix)) {
                $sum += $normalSide === 'credit' ? (float) $row->credit : (float) $row->debit;
                break;
            }
        }
    }
    return round($sum, 2);
}

protected function noteRowsByPrefix($rows, array $prefixes, string $normalSide = 'debit')
{
    return collect($rows)->filter(function ($row) use ($prefixes, $normalSide) {
        $code = (string) ($row->accounting_code_6 ?? '');
        foreach ($prefixes as $prefix) {
            if (str_starts_with($code, (string) $prefix)) {
                return ($normalSide === 'credit' ? (float) $row->credit : (float) $row->debit) != 0.0;
            }
        }
        return false;
    })->values();
}

protected function assetRegisterBookValue(Carbon $end, Request $request, $user): float
{
    if (!class_exists(AssetTransaction::class)) { return 0.0; }

    $selectedCompany = $this->requestedCompanyId($request);
    $query = AssetTransaction::with(['category','workPoint'])
        ->where('status', '!=', 'Deleted')
        ->whereDate('purchase_date', '<=', $end->toDateString());

    if (!empty($selectedCompany)) { $query->where('company_id', $selectedCompany); }
    elseif (!empty($user->company_id)) { $query->where('company_id', $user->company_id); }

    if ($request->filled('company_unit_id')) {
        $unitId = $request->query('company_unit_id');
        $query->whereHas('workPoint', function ($q) use ($unitId) { $q->where('comp_unit_id', $unitId); });
    }

    $total = 0.0;
    foreach ($query->get() as $asset) {
        if ($asset->status === 'Disposed' && $asset->transaction_date && Carbon::parse($asset->transaction_date)->lte($end)) { continue; }
        $cost = (float) ($asset->purchase_cost ?? 0);
        $rate = (float) ($asset->depreciation_rate ?? optional($asset->category)->depreciation_rate ?? 0);
        $purchaseDate = $asset->purchase_date ? Carbon::parse($asset->purchase_date)->startOfDay() : null;
        if (!$purchaseDate || $cost <= 0) { continue; }
        $days = max(0, $purchaseDate->diffInDays($end) + 1);
        $depreciation = ($cost * ($rate / 100)) * ($days / 365);
        $total += max(0, $cost - $depreciation);
    }
    return round($total, 2);
}

public function ledgerReport($year, Request $request)
{
    $user = auth()->user(); $year = (int) $year;

    if ($request->filled('start_date') || $request->filled('end_date')) {
        [$start, $end] = $this->reportDatesFromRequest($year, $request);
        $period = $request->query('period', 'CUSTOM');
    } else {
        [$period, $start, $end] = $this->resolveLedgerPeriodDates($year, $request->query('period'));
    }

    $transactions = $this->approvedReportBaseQuery($user, $start, $end, $request)->orderBy('trans_date')->orderBy('id')->get();
    $consolidatedSummary = $this->buildNetRowsByAccountingCode($transactions);

    $companySummaries = $transactions->groupBy('company_id')->map(function ($rows) {
        $first = $rows->first();
        return (object) [
            'company_id' => optional($first)->company_id,
            'company_name' => optional(CompanySite::find(optional($first)->company_id))->company_name ?? 'Unknown Company',
            'rows' => $this->buildNetRowsByAccountingCode($rows),
        ];
    })->sortBy('company_name')->values();

    return view('admin.accnt.ledger', $this->commonReportViewData($year, $start, $end, $request, $user) + compact('period','consolidatedSummary','companySummaries'));
}

public function ledgerDetailReport($year, $accountId, Request $request)
{
    $user = auth()->user(); $year = (int) $year; $companyId = $this->requestedCompanyId($request);

    if ($request->filled('start_date') || $request->filled('end_date')) {
        [$start, $end] = $this->reportDatesFromRequest($year, $request);
        $period = $request->query('period', 'CUSTOM');
    } else {
        [$period, $start, $end] = $this->resolveLedgerPeriodDates($year, $request->query('period'));
    }

    $transactions = $this->approvedReportBaseQuery($user, $start, $end, $request)->orderBy('trans_date')->orderBy('id')->get();

    $filtered = $transactions->filter(function ($t) use ($accountId) {
        $display = $this->transactionAccountDisplay($t);
        return (string) optional($t->account)->id === (string) $accountId || (string) $display['accounting_code_6'] === (string) $accountId;
    })->values();

    $groupedAll = $transactions->groupBy('transaction_group');
    $groupedFiltered = $filtered->groupBy('transaction_group');
    $firstTx = $filtered->first();
    $headerDisplay = $firstTx ? $this->transactionAccountDisplay($firstTx) : ['accounting_code_6' => $accountId, 'accounting_name_6' => null];

    $companyName = $firstTx ? (optional(CompanySite::find($firstTx->company_id))->company_name ?? '-') : '-';
    $debitTotal = (float) $filtered->where('type', 'debit')->sum('amount');
    $creditTotal = (float) $filtered->where('type', 'credit')->sum('amount');
    $finalBalance = $debitTotal - $creditTotal;
    $finalSide = $finalBalance >= 0 ? 'debit' : 'credit';
    $running = 0.0;

    $txRows = $groupedFiltered->map(function ($rows, $transactionGroup) use (&$running, $groupedAll) {
        $first = $rows->sortBy('id')->first();
        $display = $this->transactionAccountDisplay($first);
        $debit = (float) $rows->where('type', 'debit')->sum('amount');
        $credit = (float) $rows->where('type', 'credit')->sum('amount');
        $running += ($debit - $credit);

        $groupRows = collect($groupedAll->get($transactionGroup, []));
        $oppositeRows = $groupRows->filter(fn($r) => $r->type !== $first->type);
        $correspondingEntries = $oppositeRows->map(function ($r) {
            $opp = $this->transactionAccountDisplay($r);
            return ['type'=>$r->type, 'sub_accounting_code_8'=>$opp['sub_accounting_code_8'] ?: '-', 'sub_accounting_name_8'=>$opp['sub_accounting_name_8'] ?: '-'];
        })->unique(fn($row) => $row['type'].'|'.$row['sub_accounting_code_8'].'|'.$row['sub_accounting_name_8'])->values()->all();

        return (object) [
            'id'=>$first->id,
            'trans_date'=>$first->trans_date,
            'accounting_code_6'=>$display['accounting_code_6'],
            'accounting_name_6'=>$display['accounting_name_6'],
            'sub_accounting_code_8'=>$display['sub_accounting_code_8'],
            'sub_accounting_name_8'=>$display['sub_accounting_name_8'],
            'reference'=>$first->reference,
            'memo'=>$first->memo,
            'payee'=>$first->payee,
            'debit'=>$debit,
            'credit'=>$credit,
            'running'=>$running,
            'workpoint_name'=>optional($first->workpoint)->work_name,
            'company_name'=>optional(CompanySite::find($first->company_id))->company_name ?? '-',
            'section_code'=>optional($first->section)->secCode ?? '-',
            'section_name'=>optional($first->section)->secName ?? '-',
            'corresponding_entries'=>$correspondingEntries,
            'verified'=>$first->verified ?? 'pending',
            'approved'=>$first->approved ?? 'pending',
        ];
    })->sortBy(fn($row) => ($row->trans_date ? $row->trans_date->format('Y-m-d') : '0000-00-00') . '-' . $row->id)->values();

    return view('admin.accnt.ledger_detail',
        $this->commonReportViewData($year, $start, $end, $request, $user)
        + compact('accountId','companyId','companyName','period','txRows','debitTotal','creditTotal','finalBalance','finalSide')
        + ['accountingCode'=>$headerDisplay['accounting_code_6'], 'accountingName'=>$headerDisplay['accounting_name_6']]
    );
}


public function trialBalanceReport($year, Request $request)
{
    $user = auth()->user();
    $year = (int) $year;

    [$start, $end] = $this->reportDatesFromRequest($year, $request);

    $transactions = $this->approvedReportBaseQuery($user, $start, $end, $request)->get();
    $data = $this->buildNetRowsByAccountingCode($transactions);

    $totalDebit = (float) $data->sum('debit');
    $totalCredit = (float) $data->sum('credit');

    return view(
        'admin.accnt.trial_balance',
        $this->commonReportViewData($year, $start, $end, $request, $user)
        + compact('data', 'totalDebit', 'totalCredit')
    );
}

public function monthlyTrialBalanceReport($year, Request $request)
{
    $user = auth()->user();
    $year = (int) $year;
    $selectedAccount = $request->query('account');

    $start = Carbon::create($year, 1, 1)->startOfDay();
    $end = Carbon::create($year, 12, 31)->endOfDay();

    $base = $this->approvedReportBaseQuery($user, $start, $end, $request);

    if ($selectedAccount) {
        $base->where('account_id', $selectedAccount);
    }

    $transactions = $base->get();

    $monthAgg = [];

    foreach ($transactions as $tx) {
        $display = $this->transactionAccountDisplay($tx);

        $key = ($display['accounting_code_6'] ?: optional($tx->account)->AccCode)
            . '||'
            . ($display['accounting_name_6'] ?: optional($tx->account)->AccDescription);

        $month = Carbon::parse($tx->trans_date)->month;

        if (!isset($monthAgg[$key])) {
            $monthAgg[$key] = [
                'code' => $display['accounting_code_6'] ?: optional($tx->account)->AccCode,
                'name' => $display['accounting_name_6'] ?: optional($tx->account)->AccDescription,
                'account_id' => optional($tx->account)->id,
                'months' => array_fill(1, 12, ['debit' => 0.0, 'credit' => 0.0]),
            ];
        }

        if ($tx->type === 'debit') {
            $monthAgg[$key]['months'][$month]['debit'] += (float) $tx->amount;
        } else {
            $monthAgg[$key]['months'][$month]['credit'] += (float) $tx->amount;
        }
    }

    $matrix = collect($monthAgg)
        ->map(fn ($row) => (object) [
            'account' => (object) [
                'id' => $row['account_id'],
                'AccCode' => $row['code'],
                'AccDescription' => $row['name'],
            ],
            'months' => $row['months'],
        ])
        ->sortBy(fn ($row) => $row->account->AccCode)
        ->values();

    $footerTotals = [];

    for ($m = 1; $m <= 12; $m++) {
        $footerTotals[$m] = [
            'debit' => $matrix->sum(fn ($row) => $row->months[$m]['debit']),
            'credit' => $matrix->sum(fn ($row) => $row->months[$m]['credit']),
        ];
    }

    $accounts = AccntChart::where('Status', '!=', 'Deleted')
        ->orderBy('AccCode')
        ->get();

    return view(
        'admin.accnt.monthly_trial_balance',
        $this->commonReportViewData($year, $start, $end, $request, $user)
        + compact('matrix', 'footerTotals', 'accounts', 'selectedAccount')
    );
}
public function profitLossReport($year, Request $request)
{
    $user = auth()->user(); $year = (int) $year; [$start, $end] = $this->reportDatesFromRequest($year, $request);
    $rows = $this->buildNetRowsByAccountingCode($this->approvedReportBaseQuery($user, $start, $end, $request)->get());

    $totalRevenue = $this->sumRowsByPrefix($rows, ['70','71','72','73','74','77','78'], 'credit');
    $costOfRevenue = $this->sumRowsByPrefix($rows, ['60','61','62'], 'debit');
    $grossProfit = $totalRevenue - $costOfRevenue;
    $adminExpenses = $this->sumRowsByPrefix($rows, ['63','64','65'], 'debit');
    $depreciation = $this->sumRowsByPrefix($rows, ['68'], 'debit');
    $financeCost = $this->sumRowsByPrefix($rows, ['66','67'], 'debit');
    $taxExpense = $this->sumRowsByPrefix($rows, ['69'], 'debit');
    $operatingExpenses = $adminExpenses + $depreciation;
    $totalExpenses = $costOfRevenue + $operatingExpenses + $financeCost + $taxExpense;
    $profitBeforeTax = $grossProfit - $operatingExpenses - $financeCost;
    $netProfit = $profitBeforeTax - $taxExpense;

    return view('admin.accnt.profit_loss', $this->commonReportViewData($year, $start, $end, $request, $user) + compact('rows','totalRevenue','costOfRevenue','grossProfit','adminExpenses','depreciation','financeCost','taxExpense','operatingExpenses','totalExpenses','profitBeforeTax','netProfit'));
}

public function balanceSheetReport($year, Request $request)
{
    $user = auth()->user(); $year = (int) $year; [$start, $end] = $this->reportDatesFromRequest($year, $request);
    $rows = $this->buildNetRowsByAccountingCode($this->approvedReportBaseQuery($user, $start, $end, $request)->get());

    $ppe = $this->assetRegisterBookValue($end, $request, $user);
    if ($ppe <= 0) { $ppe = $this->sumRowsByPrefix($rows, ['20','21','22','23','24','25','26','28','29'], 'debit'); }
    $cash = $this->sumRowsByPrefix($rows, ['52','53','54','55','56','57','58'], 'debit');
    $overdraft = $this->sumRowsByPrefix($rows, ['52','53','54','55','56','57','58'], 'credit');
    $receivables = $this->sumRowsByPrefix($rows, ['41'], 'debit');
    $inventories = $this->sumRowsByPrefix($rows, ['30','31','32','33','34','35','36','37','38','39'], 'debit');
    $prepayments = $this->sumRowsByPrefix($rows, ['42','43','44','46','47','48'], 'debit');
    $shareCapital = $this->sumRowsByPrefix($rows, ['10'], 'credit');
    $reserves = $this->sumRowsByPrefix($rows, ['11','12','13','14','15'], 'credit');
    $longTermLoans = $this->sumRowsByPrefix($rows, ['16','17','19'], 'credit');
    $payables = $this->sumRowsByPrefix($rows, ['40'], 'credit');
    $taxLiabilities = $this->sumRowsByPrefix($rows, ['43'], 'credit');
    $otherCurrentLiabilities = $this->sumRowsByPrefix($rows, ['42','44','45','46','47','48'], 'credit');

    $totalNonCurrentAssets = $ppe; $totalCurrentAssets = $cash + $receivables + $inventories + $prepayments; $totalAssets = $totalNonCurrentAssets + $totalCurrentAssets;
    $totalEquity = $shareCapital + $reserves; $totalLongTermLiabilities = $longTermLoans; $totalCurrentLiabilities = $overdraft + $payables + $taxLiabilities + $otherCurrentLiabilities;
    $totalLiabilities = $totalLongTermLiabilities + $totalCurrentLiabilities; $totalEquityLiabilities = $totalEquity + $totalLiabilities;

    $sheet = [
        'Property, Plant & Equipment' => $ppe,
        'Cash & Cash Equivalents' => $cash,
        'Accounts Receivables' => $receivables,
        'Inventories' => $inventories,
        'Prepayment and Advances' => $prepayments,
        'Total Assets' => $totalAssets,
        'Share Capital' => $shareCapital,
        'Reserve / Retained Earnings' => $reserves,
        'Long Term Liabilities' => $longTermLoans,
        'Bank Overdraft' => $overdraft,
        'Trade and other payables' => $payables,
        'Tax Liabilities' => $taxLiabilities,
        'Other Current Liabilities' => $otherCurrentLiabilities,
        'Total Equity & Liabilities' => $totalEquityLiabilities,
    ];

    return view('admin.accnt.balance_sheet', $this->commonReportViewData($year, $start, $end, $request, $user) + compact('rows','sheet','ppe','cash','receivables','inventories','prepayments','overdraft','shareCapital','reserves','longTermLoans','payables','taxLiabilities','otherCurrentLiabilities','totalNonCurrentAssets','totalCurrentAssets','totalAssets','totalEquity','totalLongTermLiabilities','totalCurrentLiabilities','totalLiabilities','totalEquityLiabilities'));
}

public function changeInEquityReport($year, Request $request)
{
    $user = auth()->user(); $year = (int) $year; [$start, $end] = $this->reportDatesFromRequest($year, $request);
    $currentRows = $this->buildNetRowsByAccountingCode($this->approvedReportBaseQuery($user, $start, $end, $request)->get());
    $openingStart = Carbon::create($year - 1, 1, 1)->startOfDay(); $openingEnd = Carbon::create($year - 1, 12, 31)->endOfDay();
    $openingRows = $this->buildNetRowsByAccountingCode($this->approvedReportBaseQuery($user, $openingStart, $openingEnd, $request)->get());

    $revenue = $this->sumRowsByPrefix($currentRows, ['70','71','72','73','74','77','78'], 'credit');
    $costs = $this->sumRowsByPrefix($currentRows, ['60','61','62','63','64','65','66','67','68','69'], 'debit');
    $netProfit = $revenue - $costs;
    $openingEquity = $this->sumRowsByPrefix($openingRows, ['10','11','12','13','14','15'], 'credit');
    $currentEquity = $this->sumRowsByPrefix($currentRows, ['10','11','12','13','14','15'], 'credit');
    $endingEquity = $openingEquity + $currentEquity + $netProfit;

    return view('admin.accnt.change_in_equity', $this->commonReportViewData($year, $start, $end, $request, $user) + compact('currentRows','openingRows','openingEquity','netProfit','currentEquity','endingEquity'));
}

public function cashFlowReport($year, Request $request)
{
    $user = auth()->user(); $year = (int) $year; [$start, $end] = $this->reportDatesFromRequest($year, $request);
    $rows = $this->buildNetRowsByAccountingCode($this->approvedReportBaseQuery($user, $start, $end, $request)->get());
    $cashRows = $this->noteRowsByPrefix($rows, ['52','53','54','55','56','57','58'], 'debit')->merge($this->noteRowsByPrefix($rows, ['52','53','54','55','56','57','58'], 'credit'))->unique('accounting_code_6')->values();

    $cashMovements = $cashRows->map(fn($r) => (object) ['account'=>(object) ['AccCode'=>$r->accounting_code_6, 'AccDescription'=>$r->accounting_name_6], 'debit'=>$r->debit_total, 'credit'=>$r->credit_total, 'net'=>$r->balance]);
    $totalNet = (float) $cashMovements->sum('net');
    $profitBeforeTax = $this->sumRowsByPrefix($rows, ['70','71','72','73','74','77','78'], 'credit') - $this->sumRowsByPrefix($rows, ['60','61','62','63','64','65','66','67','68','69'], 'debit');
    $depreciation = $this->sumRowsByPrefix($rows, ['68'], 'debit'); $financeCost = $this->sumRowsByPrefix($rows, ['66','67'], 'debit');

    return view('admin.accnt.cash_flow', $this->commonReportViewData($year, $start, $end, $request, $user) + compact('rows','cashMovements','totalNet','profitBeforeTax','depreciation','financeCost'));
}

protected function fsAmountByPrefixes($rows, array $prefixes, string $normalSide = 'debit'): float
{
    $amount = 0.0;

    foreach ($rows as $row) {
        $code = (string) ($row->accounting_code_6 ?? '');
        foreach ($prefixes as $prefix) {
            if (str_starts_with($code, (string) $prefix)) {
                $amount += $normalSide === 'credit'
                    ? ((float) $row->credit - (float) $row->debit)
                    : ((float) $row->debit - (float) $row->credit);
                break;
            }
        }
    }

    return round($amount, 2);
}

protected function fsRowsByPrefixes($rows, array $prefixes, string $normalSide = 'debit')
{
    return collect($rows)->filter(function ($row) use ($prefixes) {
        $code = (string) ($row->accounting_code_6 ?? '');
        foreach ($prefixes as $prefix) {
            if (str_starts_with($code, (string) $prefix)) {
                return true;
            }
        }
        return false;
    })->map(function ($row) use ($normalSide) {
        $amount = $normalSide === 'credit'
            ? ((float) $row->credit - (float) $row->debit)
            : ((float) $row->debit - (float) $row->credit);

        $row->fs_amount = round($amount, 2);
        return $row;
    })->filter(fn ($row) => round((float) $row->fs_amount, 2) != 0.0)
      ->sortBy('accounting_code_6')
      ->values();
}

protected function fsComparativeNote($currentRows, $previousRows, array $prefixes, string $normalSide = 'debit')
{
    $current = $this->fsRowsByPrefixes($currentRows, $prefixes, $normalSide)->keyBy('accounting_code_6');
    $previous = $this->fsRowsByPrefixes($previousRows, $prefixes, $normalSide)->keyBy('accounting_code_6');

    return $current->keys()->merge($previous->keys())->unique()->sort()->map(function ($code) use ($current, $previous) {
        $cur = $current->get($code);
        $prev = $previous->get($code);

        return (object) [
            'accounting_code_6' => $code,
            'accounting_name_6' => optional($cur)->accounting_name_6 ?: optional($prev)->accounting_name_6 ?: '-',
            'current' => (float) optional($cur)->fs_amount,
            'previous' => (float) optional($prev)->fs_amount,
        ];
    })->values();
}

protected function fsAssetBookValue(Carbon $asAt, Request $request, $user): float
{
    if (!class_exists(AssetTransaction::class)) {
        return 0.0;
    }

    $selectedCompany = $this->requestedCompanyId($request);
    $query = AssetTransaction::with(['category', 'workPoint'])
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')->orWhereNull('status');
        })
        ->whereDate('purchase_date', '<=', $asAt->toDateString());

    if (!empty($selectedCompany)) {
        $query->where('company_id', $selectedCompany);
    } elseif (!empty($user->company_id)) {
        $query->where('company_id', $user->company_id);
    }

    if ($request->filled('company_unit_id')) {
        $unitId = $request->query('company_unit_id');
        $query->whereHas('workPoint', function ($q) use ($unitId) {
            $q->where('comp_unit_id', $unitId);
        });
    }

    $bookValue = 0.0;
    foreach ($query->get() as $asset) {
        $cost = (float) $asset->purchase_cost;
        $revaluation = (float) ($asset->revalue_amount ?? 0);
        $accDep = (float) ($asset->accumulated_depreciation ?? 0);

        if (method_exists($asset, 'proratedDepreciationForYear')) {
            try {
                $accDep = max($accDep, (float) $asset->proratedDepreciationForYear((int) $asAt->year));
            } catch (\Throwable $e) {
                // Keep the stored depreciation if the model helper cannot calculate.
            }
        }

        if ($asset->status === 'Disposed' && $asset->transaction_date && Carbon::parse($asset->transaction_date)->lte($asAt)) {
            continue;
        }

        $bookValue += max(0, ($cost + $revaluation) - $accDep);
    }

    return round($bookValue, 2);
}


protected function fsAssetNoteRows(Carbon $start, Carbon $end, Carbon $prevEnd, Request $request, $user)
{
    if (!class_exists(\App\Models\AssetTransaction::class)) {
        return collect();
    }

    $selectedCompany = $this->requestedCompanyId($request);

    $base = \App\Models\AssetTransaction::with(['category','workPoint'])
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')->orWhereNull('status');
        });

    if (!empty($selectedCompany)) {
        $base->where('company_id', $selectedCompany);
    } elseif (!empty($user->company_id)) {
        $base->where('company_id', $user->company_id);
    }

    if ($request->filled('company_unit_id')) {
        $unitId = $request->query('company_unit_id');
        $base->whereHas('workPoint', function ($q) use ($unitId) {
            $q->where('comp_unit_id', $unitId);
        });
    }

    $assets = $base->get();
    $rows = collect();

    foreach ($assets->groupBy('asset_category_id') as $categoryId => $items) {
        $first = $items->first();
        $categoryName = optional($first->category)->name ?: 'Uncategorised Assets';
        $rate = (float) (optional($first->category)->depreciation_rate ?? 0);

        $bookValueAt = function ($date) use ($items) {
            $total = 0.0;
            foreach ($items as $asset) {
                if (!$asset->purchase_date || Carbon::parse($asset->purchase_date)->gt($date)) {
                    continue;
                }
                if ($asset->status === 'Disposed' && $asset->transaction_date && Carbon::parse($asset->transaction_date)->lte($date)) {
                    continue;
                }
                $cost = (float) ($asset->purchase_cost ?? 0);
                $revalue = (float) ($asset->revalue_amount ?? 0);
                $accDep = (float) ($asset->accumulated_depreciation ?? 0);
                $total += max(0, ($cost + $revalue) - $accDep);
            }
            return round($total, 2);
        };

        $opening = $bookValueAt($start->copy()->subDay());
        $previous = $bookValueAt($prevEnd);
        $additions = (float) $items->filter(function ($asset) use ($start, $end) {
            return $asset->purchase_date && Carbon::parse($asset->purchase_date)->betweenIncluded($start, $end);
        })->sum('purchase_cost');
        $revaluation = (float) $items->filter(function ($asset) use ($start, $end) {
            return $asset->transaction_type === 'revaluation' && $asset->transaction_date && Carbon::parse($asset->transaction_date)->betweenIncluded($start, $end);
        })->sum('revalue_amount');
        $disposals = (float) $items->filter(function ($asset) use ($start, $end) {
            return $asset->status === 'Disposed' && $asset->transaction_date && Carbon::parse($asset->transaction_date)->betweenIncluded($start, $end);
        })->sum('purchase_cost');
        $depreciation = (float) $items->sum(function ($asset) use ($end) {
            if (method_exists($asset, 'proratedDepreciationForYear')) {
                try {
                    return (float) $asset->proratedDepreciationForYear((int) $end->year);
                } catch (\Throwable $e) {
                    return (float) ($asset->accumulated_depreciation ?? 0);
                }
            }
            return (float) ($asset->accumulated_depreciation ?? 0);
        });
        $closing = $bookValueAt($end);

        $rows->push((object) [
            'name' => $categoryName,
            'rate' => $rate,
            'opening' => round($opening, 2),
            'additions' => round($additions, 2),
            'revaluation' => round($revaluation, 2),
            'disposals' => round($disposals, 2),
            'depreciation' => round($depreciation, 2),
            'closing' => round($closing, 2),
            'previous' => round($previous, 2),
        ]);
    }

    return $rows->sortBy('name')->values();
}

public function financialStatementsReport($year, Request $request)
{
    $user = auth()->user();
    $year = (int) $year;

    [$start, $end] = $this->reportDatesFromRequest($year, $request);
    $start_date = $start->toDateString();
    $end_date = $end->toDateString();

    // Previous year is always derived from the selected current period.  The user
    // never selects it independently.
    $prevStart = $start->copy()->subYear();
    $prevEnd = $end->copy()->subYear();
    $previous_start_date = $prevStart->toDateString();
    $previous_end_date = $prevEnd->toDateString();

    $currentRows = $this->buildNetRowsByAccountingCode(
        $this->approvedReportBaseQuery($user, $start, $end, $request)->get()
    );

    $previousRows = $this->buildNetRowsByAccountingCode(
        $this->approvedReportBaseQuery($user, $prevStart, $prevEnd, $request)->get()
    );

    $trialDebit = round((float) $currentRows->sum('debit'), 2);
    $trialCredit = round((float) $currentRows->sum('credit'), 2);
    $trialDifference = round($trialDebit - $trialCredit, 2);
    $previousTrialDebit = round((float) $previousRows->sum('debit'), 2);
    $previousTrialCredit = round((float) $previousRows->sum('credit'), 2);
    $previousTrialDifference = round($previousTrialDebit - $previousTrialCredit, 2);

    // Comprehensive income / P&L mapping from your chart of accounts.
    $revenue = $this->fsAmountByPrefixes($currentRows, ['70', '71'], 'credit');
    $revenuePrev = $this->fsAmountByPrefixes($previousRows, ['70', '71'], 'credit');
    $otherIncome = $this->fsAmountByPrefixes($currentRows, ['72', '73', '74', '77', '78'], 'credit');
    $otherIncomePrev = $this->fsAmountByPrefixes($previousRows, ['72', '73', '74', '77', '78'], 'credit');
    $costOfRevenue = $this->fsAmountByPrefixes($currentRows, ['60', '61'], 'debit');
    $costOfRevenuePrev = $this->fsAmountByPrefixes($previousRows, ['60', '61'], 'debit');
    $sellingDistribution = $this->fsAmountByPrefixes($currentRows, ['62'], 'debit');
    $sellingDistributionPrev = $this->fsAmountByPrefixes($previousRows, ['62'], 'debit');
    $adminExpenses = $this->fsAmountByPrefixes($currentRows, ['63', '64', '65'], 'debit');
    $adminExpensesPrev = $this->fsAmountByPrefixes($previousRows, ['63', '64', '65'], 'debit');
    $financeCost = $this->fsAmountByPrefixes($currentRows, ['66', '67'], 'debit');
    $financeCostPrev = $this->fsAmountByPrefixes($previousRows, ['66', '67'], 'debit');
    $depreciation = $this->fsAmountByPrefixes($currentRows, ['68'], 'debit');
    $depreciationPrev = $this->fsAmountByPrefixes($previousRows, ['68'], 'debit');
    $taxExpense = $this->fsAmountByPrefixes($currentRows, ['69'], 'debit');
    $taxExpensePrev = $this->fsAmountByPrefixes($previousRows, ['69'], 'debit');

    $grossProfit = $revenue - $costOfRevenue;
    $grossProfitPrev = $revenuePrev - $costOfRevenuePrev;
    $totalIncome = $grossProfit + $otherIncome;
    $totalIncomePrev = $grossProfitPrev + $otherIncomePrev;
    $profitBeforeInterestTax = $totalIncome - $adminExpenses - $sellingDistribution - $depreciation;
    $profitBeforeInterestTaxPrev = $totalIncomePrev - $adminExpensesPrev - $sellingDistributionPrev - $depreciationPrev;
    $profitBeforeTax = $profitBeforeInterestTax - $financeCost;
    $profitBeforeTaxPrev = $profitBeforeInterestTaxPrev - $financeCostPrev;
    $profitAfterTax = $profitBeforeTax - $taxExpense;
    $profitAfterTaxPrev = $profitBeforeTaxPrev - $taxExpensePrev;

    // Statement of financial position mapping.
    $ppeCurrent = $this->fsAssetBookValue($end, $request, $user);
    $ppePrevious = $this->fsAssetBookValue($prevEnd, $request, $user);
    if ($ppeCurrent <= 0) {
        $ppeCurrent = $this->fsAmountByPrefixes($currentRows, ['20', '21', '22', '23', '24', '28', '29'], 'debit');
    }
    if ($ppePrevious <= 0) {
        $ppePrevious = $this->fsAmountByPrefixes($previousRows, ['20', '21', '22', '23', '24', '28', '29'], 'debit');
    }

    $intangibleAssets = $this->fsAmountByPrefixes($currentRows, ['20'], 'debit');
    $intangibleAssetsPrev = $this->fsAmountByPrefixes($previousRows, ['20'], 'debit');
    $biologicalAssets = $this->fsAmountByPrefixes($currentRows, ['227'], 'debit');
    $biologicalAssetsPrev = $this->fsAmountByPrefixes($previousRows, ['227'], 'debit');
    $nonCurrentReceivables = $this->fsAmountByPrefixes($currentRows, ['25'], 'debit');
    $nonCurrentReceivablesPrev = $this->fsAmountByPrefixes($previousRows, ['25'], 'debit');
    $investments = $this->fsAmountByPrefixes($currentRows, ['26'], 'debit');
    $investmentsPrev = $this->fsAmountByPrefixes($previousRows, ['26'], 'debit');
    $deferredTaxAssets = $this->fsAmountByPrefixes($currentRows, ['699'], 'debit');
    $deferredTaxAssetsPrev = $this->fsAmountByPrefixes($previousRows, ['699'], 'debit');
    $otherNonCurrentAssets = 0.0;
    $otherNonCurrentAssetsPrev = 0.0;

    $cash = $this->fsAmountByPrefixes($currentRows, ['52', '53', '54', '55', '56', '57', '58'], 'debit');
    $cashPrev = $this->fsAmountByPrefixes($previousRows, ['52', '53', '54', '55', '56', '57', '58'], 'debit');
    $overdraft = $this->fsAmountByPrefixes($currentRows, ['52', '53', '54', '55', '56', '57', '58'], 'credit');
    $overdraftPrev = $this->fsAmountByPrefixes($previousRows, ['52', '53', '54', '55', '56', '57', '58'], 'credit');
    $receivables = $this->fsAmountByPrefixes($currentRows, ['41'], 'debit');
    $receivablesPrev = $this->fsAmountByPrefixes($previousRows, ['41'], 'debit');
    $inventories = $this->fsAmountByPrefixes($currentRows, ['30', '31', '32', '33', '34', '35', '36', '37', '38', '39'], 'debit');
    $inventoriesPrev = $this->fsAmountByPrefixes($previousRows, ['30', '31', '32', '33', '34', '35', '36', '37', '38', '39'], 'debit');
    $goodsInTransit = $this->fsAmountByPrefixes($currentRows, ['38'], 'debit');
    $goodsInTransitPrev = $this->fsAmountByPrefixes($previousRows, ['38'], 'debit');
    $workInProgress = $this->fsAmountByPrefixes($currentRows, ['36'], 'debit');
    $workInProgressPrev = $this->fsAmountByPrefixes($previousRows, ['36'], 'debit');
    $prepayments = $this->fsAmountByPrefixes($currentRows, ['409', '422', '463', '465', '469', '471'], 'debit');
    $prepaymentsPrev = $this->fsAmountByPrefixes($previousRows, ['409', '422', '463', '465', '469', '471'], 'debit');
    $dueFromRelatedParties = $this->fsAmountByPrefixes($currentRows, ['444', '445', '448'], 'debit');
    $dueFromRelatedPartiesPrev = $this->fsAmountByPrefixes($previousRows, ['444', '445', '448'], 'debit');
    $taxReceivables = $this->fsAmountByPrefixes($currentRows, ['431', '433', '435', '437'], 'debit');
    $taxReceivablesPrev = $this->fsAmountByPrefixes($previousRows, ['431', '433', '435', '437'], 'debit');
    $interCompanyBalances = $this->fsAmountByPrefixes($currentRows, ['15'], 'debit');
    $interCompanyBalancesPrev = $this->fsAmountByPrefixes($previousRows, ['15'], 'debit');
    $otherCurrentAssets = $this->fsAmountByPrefixes($currentRows, ['42', '43', '44', '46', '47', '48'], 'debit') - $prepayments - $taxReceivables - $dueFromRelatedParties;
    $otherCurrentAssetsPrev = $this->fsAmountByPrefixes($previousRows, ['42', '43', '44', '46', '47', '48'], 'debit') - $prepaymentsPrev - $taxReceivablesPrev - $dueFromRelatedPartiesPrev;

    $shareCapital = $this->fsAmountByPrefixes($currentRows, ['10'], 'credit');
    $shareCapitalPrev = $this->fsAmountByPrefixes($previousRows, ['10'], 'credit');
    $sharePremium = $this->fsAmountByPrefixes($currentRows, ['102'], 'credit');
    $sharePremiumPrev = $this->fsAmountByPrefixes($previousRows, ['102'], 'credit');
    $reserves = $this->fsAmountByPrefixes($currentRows, ['11', '13'], 'credit');
    $reservesPrev = $this->fsAmountByPrefixes($previousRows, ['11', '13'], 'credit');
    $retainedEarnings = $this->fsAmountByPrefixes($currentRows, ['12'], 'credit');
    $retainedEarningsPrev = $this->fsAmountByPrefixes($previousRows, ['12'], 'credit');
    if (round($retainedEarnings, 2) == 0.0) {
        $retainedEarnings = $profitAfterTax;
    }
    if (round($retainedEarningsPrev, 2) == 0.0) {
        $retainedEarningsPrev = $profitAfterTaxPrev;
    }
    $advanceShareCapital = $this->fsAmountByPrefixes($currentRows, ['105'], 'credit');
    $advanceShareCapitalPrev = $this->fsAmountByPrefixes($previousRows, ['105'], 'credit');
    $otherEquity = $this->fsAmountByPrefixes($currentRows, ['14', '15'], 'credit');
    $otherEquityPrev = $this->fsAmountByPrefixes($previousRows, ['14', '15'], 'credit');

    $longTermLoans = $this->fsAmountByPrefixes($currentRows, ['16', '17'], 'credit');
    $longTermLoansPrev = $this->fsAmountByPrefixes($previousRows, ['16', '17'], 'credit');
    $deferredTaxLiabilities = $this->fsAmountByPrefixes($currentRows, ['697'], 'credit');
    $deferredTaxLiabilitiesPrev = $this->fsAmountByPrefixes($previousRows, ['697'], 'credit');
    $provisions = $this->fsAmountByPrefixes($currentRows, ['19'], 'credit');
    $provisionsPrev = $this->fsAmountByPrefixes($previousRows, ['19'], 'credit');
    $otherNonCurrentLiabilities = $this->fsAmountByPrefixes($currentRows, ['14'], 'credit') - $deferredTaxLiabilities;
    $otherNonCurrentLiabilitiesPrev = $this->fsAmountByPrefixes($previousRows, ['14'], 'credit') - $deferredTaxLiabilitiesPrev;

    $shortTermLoans = $this->fsAmountByPrefixes($currentRows, ['464', '53', '569'], 'credit');
    $shortTermLoansPrev = $this->fsAmountByPrefixes($previousRows, ['464', '53', '569'], 'credit');
    $currentTaxLiabilities = $this->fsAmountByPrefixes($currentRows, ['432', '434', '475'], 'credit');
    $currentTaxLiabilitiesPrev = $this->fsAmountByPrefixes($previousRows, ['432', '434', '475'], 'credit');
    $payables = $this->fsAmountByPrefixes($currentRows, ['40'], 'credit');
    $payablesPrev = $this->fsAmountByPrefixes($previousRows, ['40'], 'credit');
    $dueToRelatedParties = $this->fsAmountByPrefixes($currentRows, ['44'], 'credit');
    $dueToRelatedPartiesPrev = $this->fsAmountByPrefixes($previousRows, ['44'], 'credit');
    $deferredIncome = $this->fsAmountByPrefixes($currentRows, ['47'], 'credit');
    $deferredIncomePrev = $this->fsAmountByPrefixes($previousRows, ['47'], 'credit');
    $otherCurrentLiabilities = $this->fsAmountByPrefixes($currentRows, ['42', '43', '45', '46', '47', '48'], 'credit') - $currentTaxLiabilities - $dueToRelatedParties - $deferredIncome;
    $otherCurrentLiabilitiesPrev = $this->fsAmountByPrefixes($previousRows, ['42', '43', '45', '46', '47', '48'], 'credit') - $currentTaxLiabilitiesPrev - $dueToRelatedPartiesPrev - $deferredIncomePrev;

    $totalNonCurrentAssets = $ppeCurrent + $intangibleAssets + $biologicalAssets + $nonCurrentReceivables + $investments + $deferredTaxAssets + $otherNonCurrentAssets;
    $totalNonCurrentAssetsPrev = $ppePrevious + $intangibleAssetsPrev + $biologicalAssetsPrev + $nonCurrentReceivablesPrev + $investmentsPrev + $deferredTaxAssetsPrev + $otherNonCurrentAssetsPrev;

    $totalCurrentAssets = $cash + $receivables + $inventories + $goodsInTransit + $workInProgress + $prepayments + $dueFromRelatedParties + $taxReceivables + $interCompanyBalances + $otherCurrentAssets;
    $totalCurrentAssetsPrev = $cashPrev + $receivablesPrev + $inventoriesPrev + $goodsInTransitPrev + $workInProgressPrev + $prepaymentsPrev + $dueFromRelatedPartiesPrev + $taxReceivablesPrev + $interCompanyBalancesPrev + $otherCurrentAssetsPrev;

    $totalAssetsBeforeBalance = $totalNonCurrentAssets + $totalCurrentAssets;
    $totalAssetsBeforeBalancePrev = $totalNonCurrentAssetsPrev + $totalCurrentAssetsPrev;

    $totalEquity = $shareCapital + $sharePremium + $reserves + $retainedEarnings + $advanceShareCapital + $otherEquity;
    $totalEquityPrev = $shareCapitalPrev + $sharePremiumPrev + $reservesPrev + $retainedEarningsPrev + $advanceShareCapitalPrev + $otherEquityPrev;
    $totalLongTermLiabilities = $longTermLoans + $deferredTaxLiabilities + $provisions + $otherNonCurrentLiabilities;
    $totalLongTermLiabilitiesPrev = $longTermLoansPrev + $deferredTaxLiabilitiesPrev + $provisionsPrev + $otherNonCurrentLiabilitiesPrev;
    $totalCurrentLiabilities = $overdraft + $shortTermLoans + $currentTaxLiabilities + $payables + $dueToRelatedParties + $deferredIncome + $otherCurrentLiabilities;
    $totalCurrentLiabilitiesPrev = $overdraftPrev + $shortTermLoansPrev + $currentTaxLiabilitiesPrev + $payablesPrev + $dueToRelatedPartiesPrev + $deferredIncomePrev + $otherCurrentLiabilitiesPrev;
    $totalLiabilities = $totalLongTermLiabilities + $totalCurrentLiabilities;
    $totalLiabilitiesPrev = $totalLongTermLiabilitiesPrev + $totalCurrentLiabilitiesPrev;

    $unclassifiedBalance = round($totalAssetsBeforeBalance - ($totalEquity + $totalLiabilities), 2);
    $unclassifiedBalancePrev = round($totalAssetsBeforeBalancePrev - ($totalEquityPrev + $totalLiabilitiesPrev), 2);

    // Keep the report arithmetically balanced without hiding any mapping gap.
    // If this line is not zero, it means some chart codes still need exact statement mapping.
    $totalEquityLiabilities = $totalEquity + $totalLiabilities + $unclassifiedBalance;
    $totalEquityLiabilitiesPrev = $totalEquityPrev + $totalLiabilitiesPrev + $unclassifiedBalancePrev;
    $totalAssets = $totalAssetsBeforeBalance;
    $totalAssetsPrev = $totalAssetsBeforeBalancePrev;

    $notes = [
        'note2'  => $this->fsComparativeNote($currentRows, $previousRows, ['70', '71'], 'credit'),
        'note3'  => $this->fsComparativeNote($currentRows, $previousRows, ['60', '61'], 'debit'),
        'note4'  => $this->fsComparativeNote($currentRows, $previousRows, ['63', '64', '65'], 'debit'),
        'note5'  => $this->fsComparativeNote($currentRows, $previousRows, ['62'], 'debit'),
        'note6'  => $this->fsComparativeNote($currentRows, $previousRows, ['66', '67'], 'debit'),
        'note8'  => $this->fsComparativeNote($currentRows, $previousRows, ['30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '409', '422', '463', '465', '469', '471'], 'debit'),
        'note9'  => $this->fsComparativeNote($currentRows, $previousRows, ['41'], 'debit'),
        'note10' => $this->fsComparativeNote($currentRows, $previousRows, ['52', '53', '54', '55', '56', '57', '58'], 'debit'),
        'note11' => $this->fsComparativeNote($currentRows, $previousRows, ['40', '42', '43', '44', '45', '46', '47', '48'], 'credit'),
        'note12' => $this->fsComparativeNote($currentRows, $previousRows, ['16', '17', '464', '53', '569'], 'credit'),
        'note13' => $this->fsComparativeNote($currentRows, $previousRows, ['65'], 'debit'),
        'note14' => $this->fsComparativeNote($currentRows, $previousRows, ['633'], 'debit'),
        'note15' => $this->fsComparativeNote($currentRows, $previousRows, ['69', '431', '432', '433', '434', '435', '437', '697', '699'], 'debit'),
        'unclassified' => collect([(object) [
            'accounting_code_6' => '-',
            'accounting_name_6' => 'Unclassified / mapping difference',
            'current' => $unclassifiedBalance,
            'previous' => $unclassifiedBalancePrev,
        ]]),
    ];

    $cashFlow = [
        'profit_before_tax' => $profitBeforeTax,
        'profit_before_tax_prev' => $profitBeforeTaxPrev,
        'depreciation' => $depreciation,
        'depreciation_prev' => $depreciationPrev,
        'finance_cost' => $financeCost,
        'finance_cost_prev' => $financeCostPrev,
        'operating_before_working_capital' => $profitBeforeTax + $depreciation + $financeCost,
        'operating_before_working_capital_prev' => $profitBeforeTaxPrev + $depreciationPrev + $financeCostPrev,
        'inventory_change' => $inventoriesPrev - $inventories,
        'inventory_change_prev' => 0,
        'receivable_change' => $receivablesPrev - $receivables,
        'receivable_change_prev' => 0,
        'payable_change' => $payables - $payablesPrev,
        'payable_change_prev' => 0,
        'other_current_asset_change' => ($prepaymentsPrev + $otherCurrentAssetsPrev) - ($prepayments + $otherCurrentAssets),
        'other_current_asset_change_prev' => 0,
        'other_current_liability_change' => $otherCurrentLiabilities - $otherCurrentLiabilitiesPrev,
        'other_current_liability_change_prev' => 0,
        'taxation' => -1 * $taxExpense,
        'taxation_prev' => -1 * $taxExpensePrev,
        'interest_paid' => -1 * $financeCost,
        'interest_paid_prev' => -1 * $financeCostPrev,
        'ppe_purchase' => -1 * max(0, $ppeCurrent - $ppePrevious),
        'ppe_purchase_prev' => 0,
        'loan_change' => ($longTermLoans + $shortTermLoans) - ($longTermLoansPrev + $shortTermLoansPrev),
        'loan_change_prev' => 0,
    ];

    $cashFlow['cash_from_operations'] = $cashFlow['operating_before_working_capital'] + $cashFlow['inventory_change'] + $cashFlow['receivable_change'] + $cashFlow['payable_change'] + $cashFlow['other_current_asset_change'] + $cashFlow['other_current_liability_change'];
    $cashFlow['cash_from_operations_prev'] = $cashFlow['operating_before_working_capital_prev'];
    $cashFlow['net_operating'] = $cashFlow['cash_from_operations'] + $cashFlow['taxation'] + $cashFlow['interest_paid'];
    $cashFlow['net_operating_prev'] = $cashFlow['cash_from_operations_prev'] + $cashFlow['taxation_prev'] + $cashFlow['interest_paid_prev'];
    $cashFlow['net_investing'] = $cashFlow['ppe_purchase'];
    $cashFlow['net_investing_prev'] = $cashFlow['ppe_purchase_prev'];
    $cashFlow['net_financing'] = $cashFlow['loan_change'];
    $cashFlow['net_financing_prev'] = $cashFlow['loan_change_prev'];
    $cashFlow['cash_change'] = $cashFlow['net_operating'] + $cashFlow['net_investing'] + $cashFlow['net_financing'];
    $cashFlow['cash_change_prev'] = $cashFlow['net_operating_prev'] + $cashFlow['net_investing_prev'] + $cashFlow['net_financing_prev'];
    $cashFlow['cash_opening'] = $cashPrev;
    $cashFlow['cash_opening_prev'] = 0;
    $cashFlow['cash_closing'] = $cash;
    $cashFlow['cash_closing_prev'] = $cashPrev;

    $assetParams = array_filter([
        'start_date' => $start_date,
        'end_date' => $end_date,
        'company_id' => $this->requestedCompanyId($request),
        'company_unit_id' => $request->query('company_unit_id'),
    ], fn ($value) => $value !== null && $value !== '');

    $assetNoteRows = $this->fsAssetNoteRows($start, $end, $prevEnd, $request, $user);

    return view('admin.accnt.financial_statement',
        $this->commonReportViewData($year, $start, $end, $request, $user)
        + compact(
            'start','end','start_date','end_date','previous_start_date','previous_end_date','prevStart','prevEnd',
            'currentRows','previousRows','notes','cashFlow','assetParams','assetNoteRows',
            'trialDebit','trialCredit','trialDifference','previousTrialDebit','previousTrialCredit','previousTrialDifference',
            'ppeCurrent','ppePrevious','intangibleAssets','intangibleAssetsPrev','biologicalAssets','biologicalAssetsPrev',
            'nonCurrentReceivables','nonCurrentReceivablesPrev','investments','investmentsPrev','deferredTaxAssets','deferredTaxAssetsPrev',
            'otherNonCurrentAssets','otherNonCurrentAssetsPrev','revenue','revenuePrev','otherIncome','otherIncomePrev',
            'costOfRevenue','costOfRevenuePrev','grossProfit','grossProfitPrev','totalIncome','totalIncomePrev',
            'sellingDistribution','sellingDistributionPrev','adminExpenses','adminExpensesPrev','depreciation','depreciationPrev',
            'profitBeforeInterestTax','profitBeforeInterestTaxPrev','financeCost','financeCostPrev','profitBeforeTax','profitBeforeTaxPrev',
            'taxExpense','taxExpensePrev','profitAfterTax','profitAfterTaxPrev','cash','cashPrev','overdraft','overdraftPrev',
            'receivables','receivablesPrev','inventories','inventoriesPrev','goodsInTransit','goodsInTransitPrev','workInProgress','workInProgressPrev',
            'prepayments','prepaymentsPrev','dueFromRelatedParties','dueFromRelatedPartiesPrev','taxReceivables','taxReceivablesPrev',
            'interCompanyBalances','interCompanyBalancesPrev','otherCurrentAssets','otherCurrentAssetsPrev','shareCapital','shareCapitalPrev',
            'sharePremium','sharePremiumPrev','reserves','reservesPrev','retainedEarnings','retainedEarningsPrev','advanceShareCapital','advanceShareCapitalPrev',
            'otherEquity','otherEquityPrev','longTermLoans','longTermLoansPrev','deferredTaxLiabilities','deferredTaxLiabilitiesPrev',
            'provisions','provisionsPrev','otherNonCurrentLiabilities','otherNonCurrentLiabilitiesPrev','shortTermLoans','shortTermLoansPrev',
            'currentTaxLiabilities','currentTaxLiabilitiesPrev','payables','payablesPrev','dueToRelatedParties','dueToRelatedPartiesPrev',
            'deferredIncome','deferredIncomePrev','otherCurrentLiabilities','otherCurrentLiabilitiesPrev',
            'totalNonCurrentAssets','totalNonCurrentAssetsPrev','totalCurrentAssets','totalCurrentAssetsPrev','totalAssets','totalAssetsPrev',
            'totalEquity','totalEquityPrev','totalLongTermLiabilities','totalLongTermLiabilitiesPrev','totalCurrentLiabilities','totalCurrentLiabilitiesPrev',
            'totalLiabilities','totalLiabilitiesPrev','unclassifiedBalance','unclassifiedBalancePrev','totalEquityLiabilities','totalEquityLiabilitiesPrev'
        )
    );
}

}
