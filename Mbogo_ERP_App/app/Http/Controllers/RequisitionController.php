<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\AccntChart;
use App\Models\AccntSubchart;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\Department;
use App\Models\MoneyRequest;
use App\Models\Section;
use App\Models\GeneralSupplyItem;
use App\Models\GeneralSupplyItemDescription;
use App\Models\GeneralSupplyReceiving;
use App\Models\GeneralSupplyStock;
use App\Models\GeneralSupplyRequest;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
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
use Symfony\Component\HttpFoundation\StreamedResponse;


class RequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // parent::__construct();
    }
    public function requisition(Request $request)
    {
        $user = auth()->user();

        $moneyBase = $this->moneyScopeQuery($user);

        $money_total = (int) (clone $moneyBase)->count();

        $money_pending = (int) (clone $moneyBase)->where('Status', 'Pending')->count();
        $money_verified = (int) (clone $moneyBase)->where('Status', 'Verified')->count();
        $money_approved = (int) (clone $moneyBase)->where('Status', 'Approved')->count();
        $money_cashed_out = (int) (clone $moneyBase)->where('Status', 'Cashed-out')->count();
        $money_retired = (int) (clone $moneyBase)->where('Status', 'Retired')->count();
        $money_rejected = (int) (clone $moneyBase)->whereIn('Status', ['Rejected', 'Declined'])->count();

        $money_approved_like = (int) (clone $moneyBase)
            ->whereIn('Status', ['Approved', 'Cashed-out', 'Retired'])
            ->count();

        $money_unapproved = (int) (clone $moneyBase)
            ->whereIn('Status', ['Pending', 'Verified'])
            ->count();

        // IMPORTANT: rejected/declined amount is NOT included
        $money_total_amount = (float) (clone $moneyBase)
            ->whereNotIn('Status', ['Rejected', 'Declined'])
            ->sum('total_amount');

        $money_pending_amount = (float) (clone $moneyBase)
            ->where('Status', 'Pending')
            ->sum('total_amount');

        $money_verified_amount = (float) (clone $moneyBase)
            ->where('Status', 'Verified')
            ->sum('total_amount');

        $money_approved_amount = (float) (clone $moneyBase)
            ->whereIn('Status', ['Approved', 'Cashed-out', 'Retired'])
            ->sum('approved_amount');

        $money_cashed_amount = (float) (clone $moneyBase)
            ->whereIn('Status', ['Cashed-out', 'Retired'])
            ->sum('approved_amount');

        $money_rejected_amount = (float) (clone $moneyBase)
            ->whereIn('Status', ['Rejected', 'Declined'])
            ->sum('total_amount');

        $recentMoney = (clone $moneyBase)
            ->whereNotIn('Status', ['Deleted'])
            ->orderByDesc('RequestDate')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $months = collect();
        $moneyCounts = collect();

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);

            $months->push($dt->format('M Y'));

            $from = $dt->copy()->startOfMonth()->toDateString();
            $to = $dt->copy()->endOfMonth()->toDateString();

            $count = (int) (clone $moneyBase)
                ->whereNotIn('Status', ['Rejected', 'Declined'])
                ->whereBetween('RequestDate', [$from, $to])
                ->count();

            $moneyCounts->push($count);
        }

        $showCompanyChart = $this->canViewAllMoneyRequests($user);

        $companyChartLabels = [];
        $companyChartData = [];

        if ($showCompanyChart) {
            $companyRows = (clone $moneyBase)
                ->reorder()
                ->whereNotIn('Status', ['Rejected', 'Declined'])
                ->select('company_id', DB::raw('COUNT(*) as total'))
                ->groupBy('company_id')
                ->orderByDesc('total')
                ->get();

            $companyChartLabels = $companyRows->map(function ($row) {
                return optional($row->company)->company_name ?? 'Unknown';
            })->values()->all();

            $companyChartData = $companyRows->pluck('total')->values()->all();
        }

        return view('admin.home.requisition', compact(
            'money_total',
            'money_pending',
            'money_verified',
            'money_approved',
            'money_cashed_out',
            'money_retired',
            'money_rejected',
            'money_approved_like',
            'money_unapproved',
            'money_total_amount',
            'money_pending_amount',
            'money_verified_amount',
            'money_approved_amount',
            'money_cashed_amount',
            'money_rejected_amount',
            'recentMoney',
            'months',
            'moneyCounts',
            'showCompanyChart',
            'companyChartLabels',
            'companyChartData'
        ))->with([
            'chartLabels' => $months->values()->all(),
            'moneyChartData' => $moneyCounts->values()->all(),
        ]);
    }
    // ---------------- Money Request CRUD (full) ----------------
    protected function globalMoneyRoles(): array
    {
        return ['Admin', 'CEO', 'Managing Director (MD)', 'Accountant Director (DAF)', 'Chief Accountant', 'Admin-Developer'];
    }
    protected function canViewAllMoneyRequests($user): bool
    {
        return $user->can('View-All-MoneyRequest') || in_array($user->role, $this->globalMoneyRoles(), true);
    }
    protected function canViewCompanyMoneyRequests($user): bool
    {
        return $user->can('View-Company-MoneyRequest') || $user->role === 'Company Manager';
    }
    protected function canViewUnitMoneyRequests($user): bool
    {
        return $user->can('View-Unit-MoneyRequest') || $user->role === 'Unit Manager';
    }
    /**
     * User can choose another work point only when user has the "all" money permission.
     * This now follows permission ability, not Blade role list.
     */
    protected function canSelectWorkPoint($user): bool
    {
        return $this->canViewAllMoneyRequests($user);
    }

    protected function moneyScopeQuery($user)
    {
        return MoneyRequest::with([
                'company', 'unit','workpoint','requester',
                'verifier','approver','cashier','retreater',
                'account','subAccount','department','section',
            ])
            ->where('Status', '!=', 'Deleted')
            ->visibleTo($user)
            ->orderByRaw("
                CASE Status
                    WHEN 'Pending' THEN 1
                    WHEN 'Verified' THEN 2
                    WHEN 'Approved' THEN 3
                    WHEN 'Cashed-out' THEN 4
                    WHEN 'Retired' THEN 5
                    WHEN 'Declined' THEN 6
                    WHEN 'Rejected' THEN 7
                    ELSE 8
                END
            ")
            ->orderByDesc('id');
    }

    protected function generateRequestNo(int $workId, string $requestDate): string
    {
        $work = WorkPoint::find($workId);
        $workCode = strtoupper(trim(optional($work)->work_code ?: 'WRK'));
        $datePart = Carbon::parse($requestDate)->format('dmY');
        $monthKey = Carbon::parse($requestDate)->format('Ym');

        $lastSeq = MoneyRequest::where('work_point_id', $workId)
            ->whereRaw("DATE_FORMAT(RequestDate, '%Y%m') = ?", [$monthKey])
            ->lockForUpdate()
            ->max(DB::raw("CAST(SUBSTRING(RequestNo, 3, 4) AS UNSIGNED)"));

        $nextSeq = ((int) $lastSeq) + 1;

        return 'RV' . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT) . '-' . $workCode . '/' . $datePart;
    }

    /**
     * Returns only selectable sub accounts:
     * - active
     * - exactly 8 digits only
     */
    protected function selectableMoneySubAccounts()
    {
        return AccntSubchart::query()->where('Status', 'Active')->whereRaw('CHAR_LENGTH(TRIM(SubCode)) = 8')
            ->orderBy('SubCode')->get();
    }
    /**
     * Resolve the 6-digit accounting code that belongs to the selected 8-digit sub account.
     * It searches inside accnt_subcharts and stays inside the correct master/root chart.
     */
    protected function resolveParentAccountingSubCode(AccntSubchart $sub): ?AccntSubchart
    {
        $subCode = trim((string) $sub->SubCode);

        if (strlen($subCode) !== 8) {
            return null;
        }
        $parentCode = substr($subCode, 0, 6);
        return AccntSubchart::query()->where('Status', 'Active')->where('accnt_chart_id', $sub->accnt_chart_id)
            ->where('SubCode', $parentCode)->first();
    }
    /**
     * Build display payload for table/view:
     * - root account from accnt_charts
     * - 6 digit accounting code from accnt_subcharts
     * - 8 digit sub accounting code from accnt_subcharts
     */
    protected function decorateMoneyRequestAccounting($moneyRequests)
    {
        $moneyRequests->each(function ($row) {
            $selectedSub = $row->subAccount;
            $parentSub = $selectedSub ? $this->resolveParentAccountingSubCode($selectedSub) : null;

            $row->accounting_code_6 = $parentSub ? $parentSub->SubCode : null;
            $row->accounting_name_6 = $parentSub ? $parentSub->SubDescription : null;

            $row->sub_accounting_code_8 = $selectedSub ? $selectedSub->SubCode : null;
            $row->sub_accounting_name_8 = $selectedSub ? $selectedSub->SubDescription : null;

            $row->root_account_code = optional($row->account)->AccCode;
            $row->root_account_name = optional($row->account)->AccDescription;
        });

        return $moneyRequests;
    }

    protected function resolveMoneyAccountingData(Request $request): array
    {
        $subAccountId = $request->sub_account_id ?: null;
        $sectionId = $request->section_id ?: null;

        if (!$subAccountId) {
            throw ValidationException::withMessages([
                'sub_account_id' => 'Please select a sub account.',
            ]);
        }

        if (!$sectionId) {
            throw ValidationException::withMessages([
                'section_id' => 'Please select a section.',
            ]);
        }

        $sub = AccntSubchart::findOrFail($subAccountId);
        $section = Section::findOrFail($sectionId);

        $subCode = trim((string) $sub->SubCode);
        if (strlen($subCode) !== 8) {
            throw ValidationException::withMessages([
                'sub_account_id' => 'Please select only an 8 digit sub accounting code.',
            ]);
        }

        return [
            'account_id' => $sub->accnt_chart_id, // root accnt_charts id
            'sub_account_id' => $sub->id,         // selected 8-digit sub code
            'department_id' => $section->dept_id,
            'section_id' => $section->id,
        ];
    }

        public function moneyIndex(Request $request)
    {
        return $this->moneyRequestQueue($request, ['Pending'], 'Pending Money Requisitions', 'pending');
    }

    public function moneyPending(Request $request)
    {
        return $this->moneyRequestQueue($request, ['Pending'], 'Pending Money Requisitions', 'pending');
    }

    public function moneyVerified(Request $request)
    {
        return $this->moneyRequestQueue($request, ['Verified'], 'Verified Requisitions Need Approval', 'verified');
    }

    public function moneyApproved(Request $request)
    {
        return $this->moneyRequestQueue($request, ['Approved', 'Cashed-out', 'Retired'], 'Approved Requisitions Need Cash-out / Retirement', 'approved');
    }

    public function moneyRejected(Request $request)
    {
        return $this->moneyRequestQueue($request, ['Rejected', 'Declined'], 'Rejected / Declined Requisitions', 'rejected');
    }

    protected function moneyRequestQueue(Request $request, array $statuses, string $pageTitle, string $activeQueue)
    {
        $user = auth()->user();

        $query = $this->moneyScopeQuery($user)
            ->whereIn('Status', $statuses);

        $start = $request->start_date ?: null;
        $end = $request->end_date ?: null;

        if ($start && $end) {
            $query->whereBetween('RequestDate', [$start, $end]);
        } elseif ($start) {
            $query->whereDate('RequestDate', '>=', $start);
        } elseif ($end) {
            $query->whereDate('RequestDate', '<=', $end);
        }

        $moneyRequests = $query->get();
        $moneyRequests = $this->decorateMoneyRequestAccounting($moneyRequests);

        if ($this->canViewAllMoneyRequests($user)) {
            $workPoints = WorkPoint::where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } elseif ($this->canViewCompanyMoneyRequests($user)) {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } elseif ($this->canViewUnitMoneyRequests($user)) {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $workPoints = collect();
        }

        $companies = CompanySite::where('Status', 'Active')->get();
        $departments = Department::where('Status', 'Active')->get();
        $subAccounts = $this->selectableMoneySubAccounts();
        $sections = Section::where('Status', 'Active')->orderBy('secName')->get();
        $canEditWorkPoint = $this->canSelectWorkPoint($user);

        return view('admin.reqsts.moneyrequest', compact(
            'moneyRequests',
            'workPoints',
            'companies',
            'departments',
            'subAccounts',
            'sections',
            'canEditWorkPoint',
            'pageTitle',
            'activeQueue',
            'start',
            'end'
        ));
    }
    // public function moneyIndex()
    // {
    //     $user = auth()->user();

    //     $moneyRequests = $this->moneyScopeQuery($user)->get();
    //     $moneyRequests = $this->decorateMoneyRequestAccounting($moneyRequests);
    //     if ($this->canViewAllMoneyRequests($user)) {
    //         $workPoints = WorkPoint::where('status', '!=', 'Deleted')
    //             ->orderBy('work_name')->get();
    //     } elseif ($this->canViewCompanyMoneyRequests($user)) {
    //         $workPoints = WorkPoint::where('company_id', $user->company_id)
    //             ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
    //     } elseif ($this->canViewUnitMoneyRequests($user)) {
    //         $workPoints = WorkPoint::where('company_id', $user->company_id)
    //             ->where('comp_unit_id', $user->comp_unit_id)->where('status', '!=', 'Deleted')
    //             ->orderBy('work_name')->get();
    //     } else {
    //         $workPoints = collect();
    //     }
    //     // Startup data for cascading select boxes. Do not change existing money logic.
    //     $companies = CompanySite::where('Status', 'Active')->get();
    //     $departments = Department::where('Status', 'Active')->get();
    //     // only 8-digit sub accounting codes in select box
    //     $subAccounts = $this->selectableMoneySubAccounts();
    //     $sections = Section::where('Status', 'Active')
    //         ->orderBy('secName')->get();
    //     $canEditWorkPoint = $this->canSelectWorkPoint($user);
    //     return view('admin.reqsts.moneyrequest', compact(
    //         'moneyRequests',
    //         'workPoints',
    //         'companies',
    //         'departments',
    //         'subAccounts',
    //         'sections',
    //         'canEditWorkPoint'
    //     ));
    // }
    public function moneyStore(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'RequestDate' => ['required', 'date'],
            'PayeeName' => ['required', 'string', 'max:255'],
            'PayeeContact' => ['nullable', 'string', 'max:255'],
            'Description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'sub_account_id' => [
                'required',
                'integer',
                Rule::exists('accnt_subcharts', 'id')->where(function ($q) {
                    $q->where('Status', 'Active')
                    ->whereRaw('CHAR_LENGTH(TRIM(SubCode)) = 8');
                }),
            ],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')->where('Status', 'Active')],
        ];
        if ($this->canSelectWorkPoint($user)) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) {
                    $q->where('status', '!=', 'Deleted');
                }),
            ];
        }
        $request->validate($rules);
        $workPointId = $this->canSelectWorkPoint($user)
            ? (int) $request->work_point_id
            : (int) $user->work_point_id;
        $work = WorkPoint::findOrFail($workPointId);
        $companyId = (int) $work->company_id;
        $unitId = (int) $work->comp_unit_id;
        $accounting = $this->resolveMoneyAccountingData($request);

        DB::beginTransaction();

        try {
            $requestNo = $this->generateRequestNo($workPointId, $request->RequestDate);

            MoneyRequest::create([
                'company_id' => $companyId,
                'company_unit_id' => $unitId,
                'work_point_id' => $workPointId,
                'User_id' => $user->id,
                'account_id' => $accounting['account_id'],
                'sub_account_id' => $accounting['sub_account_id'],
                'department_id' => $accounting['department_id'],
                'section_id' => $accounting['section_id'],
                'RequestNo' => $requestNo,
                'RequestDate' => $request->RequestDate,
                'PayeeName' => $request->PayeeName,
                'PayeeContact' => $request->PayeeContact,
                'Description' => $request->Description,
                'total_amount' => $request->total_amount,
                'Status' => 'Pending',
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            Alert::success('Success', 'Money request created.');
            return redirect()->route('moneyrequest.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function moneyEdit($id)
    {
        $mr = MoneyRequest::findOrFail(decrypt($id));
        $user = auth()->user();

        abort_unless(
            $this->canViewAllMoneyRequests($user)
            || $mr->User_id === $user->id
            || $mr->company_id === $user->company_id
            || $mr->company_unit_id === $user->comp_unit_id,
            403
        );

        return response()->json([
            'money' => [
                'id' => $mr->id,
                'RequestDate' => $mr->RequestDate ? Carbon::parse($mr->RequestDate)->format('Y-m-d') : '',
                'PayeeName' => $mr->PayeeName,
                'PayeeContact' => $mr->PayeeContact,
                'Description' => $mr->Description,
                'total_amount' => $mr->total_amount,
                'remarks' => $mr->remarks,
                'Status' => $mr->Status,
                'work_point_id' => $mr->work_point_id,
                'sub_account_id' => $mr->sub_account_id,
                'section_id' => $mr->section_id,
            ]
        ]);
    }

    public function moneyUpdate(Request $request, $id)
    {
        $user = auth()->user();
        $mr = MoneyRequest::findOrFail(decrypt($id));

        abort_unless(
            $this->canViewAllMoneyRequests($user)
            || $mr->User_id === $user->id
            || $mr->company_id === $user->company_id
            || $mr->company_unit_id === $user->comp_unit_id,
            403
        );

        abort_unless(in_array($mr->Status, ['Pending', 'Rejected', 'Verified'], true), 403);

        $rules = [
            'RequestDate' => ['required', 'date'],
            'PayeeName' => ['required', 'string', 'max:255'],
            'PayeeContact' => ['nullable', 'string', 'max:255'],
            'Description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'sub_account_id' => [
                'required',
                'integer',
                Rule::exists('accnt_subcharts', 'id')->where(function ($q) {
                    $q->where('Status', 'Active')
                    ->whereRaw('CHAR_LENGTH(TRIM(SubCode)) = 8');
                }),
            ],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')->where('Status', 'Active')],
        ];

        if ($this->canSelectWorkPoint($user)) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) {
                    $q->where('status', '!=', 'Deleted');
                }),
            ];
        }

        $request->validate($rules);

        $workPointId = $this->canSelectWorkPoint($user)
            ? (int) $request->work_point_id
            : (int) $mr->work_point_id;

        $work = WorkPoint::findOrFail($workPointId);
        $companyId = (int) $work->company_id;
        $unitId = (int) $work->comp_unit_id;

        $accounting = $this->resolveMoneyAccountingData($request);

        DB::beginTransaction();

        try {
            $needsNewNo = (
                $mr->company_id != $companyId ||
                $mr->company_unit_id != $unitId ||
                $mr->work_point_id != $workPointId ||
                ($mr->RequestDate ? Carbon::parse($mr->RequestDate)->format('dmY') : '') !== Carbon::parse($request->RequestDate)->format('dmY')
            );

            $newRequestNo = $mr->RequestNo;

            if ($needsNewNo) {
                $newRequestNo = $this->generateRequestNo($workPointId, $request->RequestDate);
            }

            $mr->update([
                'company_id' => $companyId,
                'company_unit_id' => $unitId,
                'work_point_id' => $workPointId,
                'account_id' => $accounting['account_id'],
                'sub_account_id' => $accounting['sub_account_id'],
                'department_id' => $accounting['department_id'],
                'section_id' => $accounting['section_id'],
                'RequestNo' => $newRequestNo,
                'RequestDate' => $request->RequestDate,
                'PayeeName' => $request->PayeeName,
                'PayeeContact' => $request->PayeeContact,
                'Description' => $request->Description,
                'total_amount' => $request->total_amount,
                'remarks' => $request->remarks,
                'Status' => 'Pending',
                'verified_by' => null,
                'verified_at' => null,
                'verified_comment' => null,
                'approved_by' => null,
                'approved_at' => null,
                'approval_comment' => null,
                'approved_amount' => null,
                'Payment_mode' => null,
                'cashed_by' => null,
                'cashed_at' => null,
                'payment_vocher_no' => null,
                'cashier_comment' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_comment' => null,
                'retired_by' => null,
                'retired_at' => null,
                'retirement_docs' => null,
                'retirement_comment' => null,
                'returned_amount' => 0,
            ]);

            DB::commit();

            Alert::success('Success', 'Money request updated.');
            return redirect()->route('moneyrequest.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function moneyRemove($id)
    {
        $user = auth()->user();
        $mr = MoneyRequest::findOrFail(decrypt($id));

        abort_unless(
            $this->canViewAllMoneyRequests($user)
            || $mr->User_id === $user->id
            || $mr->company_id === $user->company_id
            || $mr->company_unit_id === $user->comp_unit_id,
            403
        );

        abort_unless(in_array($mr->Status, ['Pending', 'Rejected'], true), 403);

        $mr->update(['Status' => 'Deleted']);

        Alert::success('Success', 'Money request deleted.');
        return redirect()->route('moneyrequest.index');
    }

    public function moneyVerify(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user->can('Verify-MoneyRequest') || $this->canViewAllMoneyRequests($user), 403);

        $mr = MoneyRequest::findOrFail(decrypt($id));
        abort_unless($mr->Status === 'Pending', 403);

        $request->validate([
            'decision' => ['required', 'in:verified,rejected'],
            'verified_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $comment = trim((string) $request->verified_comment);
        if ($comment === '') {
            $comment = 'Okay';
        }

        if ($request->decision === 'rejected') {
            $mr->update([
                'Status' => 'Declined',
                'verified_by' => $user->id,
                'verified_at' => now(),
                'rejection_comment' => $comment,
            ]);

            Alert::success('Success', 'Money request declined.');
            return redirect()->route('moneyrequest.index');
        }

        $mr->update([
            'Status' => 'Verified',
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verified_comment' => $comment,
        ]);

        Alert::success('Success', 'Money request verified.');
        return redirect()->route('moneyrequest.index');
    }
    public function moneyApprove(Request $request, $id)
    {
        $user = auth()->user();

        abort_unless($user->can('Approve-MoneyRequest') || $this->canViewAllMoneyRequests($user), 403);
        $mr = MoneyRequest::findOrFail(decrypt($id));
        abort_unless($mr->Status === 'Verified', 403);

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'approval_comment' => ['required', 'string', 'max:5000'],
            'approved_amount' => ['nullable', 'numeric', 'min:0.01'],
            'Payment_mode' => ['nullable', 'in:Cash,Cheque'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'cheque_bank_account' => ['nullable', 'string', 'max:255'],
        ]);

        $comment = trim((string) $request->approval_comment);

        if ($request->decision === 'rejected') {
            $mr->update([
                'Status' => 'Rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_comment' => $comment,
            ]);

            Alert::success('Success', 'Money request rejected.');
            return redirect()->route('moneyrequest.index');
        }

        abort_unless($request->approved_amount !== null, 422);
        abort_unless($request->Payment_mode !== null, 422);

        if ($request->Payment_mode === 'Cheque') {
            $comment .= "\nBank Name: " . trim((string) $request->bank_name);
            $comment .= "\nCheque Bank Account: " . trim((string) $request->cheque_bank_account);
        }

        $mr->update([
            'Status' => 'Approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approved_amount' => $request->approved_amount,
            'Payment_mode' => $request->Payment_mode,
            'approval_comment' => $comment,
        ]);

        Alert::success('Success', 'Money request approved.');
        return redirect()->route('moneyrequest.index');
    }
    public function moneyReject(Request $request, $id)
    {
        $user = auth()->user();
        $mr = MoneyRequest::findOrFail(decrypt($id));

        abort_unless(
            $user->can('Verify-MoneyRequest')
            || $user->can('Approve-MoneyRequest')
            || $this->canViewAllMoneyRequests($user),
            403
        );

        $request->validate([
            'rejection_comment' => ['required', 'string', 'max:5000'],
        ]);

        $mr->update([
            'Status' => 'Rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_comment' => $request->rejection_comment,
        ]);

        Alert::success('Success', 'Money request rejected.');
        return redirect()->route('moneyrequest.index');
    }

    public function moneyCashOut(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user->can('CashOut-MoneyRequest') || $this->canViewAllMoneyRequests($user), 403);

        $mr = MoneyRequest::findOrFail(decrypt($id));
        abort_unless($mr->Status === 'Approved', 403);

        $request->validate([
            'payment_vocher_no' => ['required', 'string', 'max:255'],
            'cashier_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $mr->update([
            'Status' => 'Cashed-out',
            'cashed_by' => $user->id,
            'cashed_at' => now(),
            'payment_vocher_no' => $request->payment_vocher_no,
            'cashier_comment' => $request->cashier_comment,
        ]);

        Alert::success('Success', 'Money request cashed out.');
        return redirect()->route('moneyrequest.index');
    }

    public function moneyRetire(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user->can('Reteirement-MoneyRequest') || $this->canViewAllMoneyRequests($user), 403);

        $mr = MoneyRequest::findOrFail(decrypt($id));
        abort_unless($mr->Status === 'Cashed-out', 403);

        $request->validate([
            'returned_amount' => ['nullable', 'numeric', 'min:0'],
            'retirement_comment' => ['nullable', 'string', 'max:5000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'retirement_docs' => ['nullable', 'file', 'max:10240'],
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'Status' => 'Retired',
                'retired_by' => $user->id,
                'retired_at' => now(),
                'returned_amount' => $request->returned_amount !== null && $request->returned_amount !== '' ? $request->returned_amount : 0,
                'retirement_comment' => $request->retirement_comment,
                'remarks' => $request->remarks !== null && $request->remarks !== '' ? $request->remarks : $mr->remarks,
            ];

            if ($request->hasFile('retirement_docs')) {
                $file = $request->file('retirement_docs');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $publicRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                $destFolder = $publicRoot . '/assets';

                if (!File::exists($destFolder)) {
                    File::makeDirectory($destFolder, 0755, true);
                }

                $file->move($destFolder, $filename);
                $data['retirement_docs'] = 'assets/' . $filename;
            }

            $mr->update($data);

            DB::commit();
            Alert::success('Success', 'Money request retired.');
            return redirect()->route('moneyrequest.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

        protected function decorateSingleMoneyRequestAccounting($mr)
        {
            $selectedSub = $mr->subAccount ?: $mr->subaccount;
            $parentSub = $selectedSub ? $this->resolveParentAccountingSubCode($selectedSub) : null;

            $mr->accounting_code_6 = $parentSub ? $parentSub->SubCode : null;
            $mr->accounting_name_6 = $parentSub ? $parentSub->SubDescription : null;

            $mr->sub_accounting_code_8 = $selectedSub ? $selectedSub->SubCode : null;
            $mr->sub_accounting_name_8 = $selectedSub ? $selectedSub->SubDescription : null;

            $mr->root_account_code = optional($mr->account)->AccCode;
            $mr->root_account_name = optional($mr->account)->AccDescription;
            return $mr;
        }
        public function moneyShow($id)
        {
            $user = auth()->user();

            $mr = MoneyRequest::with([
                'company',
                'unit',
                'workpoint',
                'requester',
                'verifier',
                'approver',
                'cashier',
                'retreater',
                'account',
                'subAccount',
                'department',
                'section',
            ])->findOrFail(decrypt($id));

            abort_unless(
                $this->canViewAllMoneyRequests($user)
                || $mr->User_id === $user->id
                || $mr->company_id === $user->company_id
                || $mr->company_unit_id === $user->comp_unit_id,
                403
            );

            $mr = $this->decorateSingleMoneyRequestAccounting($mr);

            return view('admin.reqsts.moneyrequest_show', compact('mr'));
        }

        public function moneyPrint($id)
        {
            $user = auth()->user();

            $mr = MoneyRequest::with([
                'company',
                'unit',
                'workpoint',
                'requester',
                'verifier',
                'approver',
                'cashier',
                'retreater',
                'account',
                'subAccount',
                'department',
                'section',
            ])->findOrFail(decrypt($id));

            abort_unless(
                $this->canViewAllMoneyRequests($user)
                || $mr->User_id === $user->id
                || $mr->company_id === $user->company_id
                || $mr->company_unit_id === $user->comp_unit_id,
                403
            );

            $mr = $this->decorateSingleMoneyRequestAccounting($mr);

            return view('admin.reqsts.moneyrequest_show', compact('mr'));
        }
      public function requisitionReport(Request $request)
    {
        $user = auth()->user();
        $start = $request->start_date ?: null;
        $end = $request->end_date ?: null;
        $companyId = $request->company_id ?: null;
        $workPointId = $request->work_point_id ?: null;

        $useDateFilter = ($start && $end);

        $moneyRequestsQuery = $this->moneyScopeQuery($user);

        if ($companyId && $this->canViewAllMoneyRequests($user)) {
            $moneyRequestsQuery->where('company_id', $companyId);
        }
        if ($workPointId) {
            $moneyRequestsQuery->where('work_point_id', $workPointId);
        }
        if ($useDateFilter) {
            $moneyRequestsQuery->whereBetween('RequestDate', [$start, $end]);
        }
        $moneyRequests = $moneyRequestsQuery->orderByDesc('RequestDate')
            ->orderByDesc('id')->get();

        $moneyRequestsGrandTotal = (float) $moneyRequests->sum('total_amount');
        $moneyRequestsApprovedTotal = (int) $moneyRequests->where('Status', 'Approved')->count();
        $moneyRequestsCashedOutTotal = (int) $moneyRequests->where('Status', 'Cashed-out')->count();
        $moneyRequestsRetiredTotal = (int) $moneyRequests->where('Status', 'Retired')->count();

        $moneyApprovedLike = (int) $moneyRequests->whereIn('Status', ['Approved', 'Cashed-out', 'Retired'])->count();
        $moneyUnapproved = $moneyRequests->count() - $moneyApprovedLike;

        $companies = $this->canViewAllMoneyRequests($user)
            ? CompanySite::where('id','!=','1')->orderBy('company_name')->get()
            : CompanySite::where('id','!=','1')->where('id', $user->company_id)->orderBy('company_name')->get();

        $workPointsQuery = WorkPoint::where('id','!=','1')->orderBy('work_name');

        if ($this->canViewAllMoneyRequests($user)) {
            $workPoints = $workPointsQuery->get();
        } elseif ($user->can('View-Company-MoneyRequest') || $user->role === 'Company Manager') {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->orderBy('work_name')->get();
        } elseif ($user->can('View-Unit-MoneyRequest') || $user->role === 'Unit Manager') {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->orderBy('work_name')->get();
        } else {
            $workPoints = WorkPoint::where('id', $user->work_point_id)
                ->orderBy('work_name')->get();
        }
        $months = collect();
        $moneyChartData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $months->push($dt->format('M Y'));
            $from = $dt->copy()->startOfMonth()->toDateString();
            $to = $dt->copy()->endOfMonth()->toDateString();
            $count = (int) $this->moneyScopeQuery($user)
                ->when($this->canViewAllMoneyRequests($user) && $companyId, function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->when($workPointId, function ($q) use ($workPointId) {
                    $q->where('work_point_id', $workPointId);
                })->whereBetween('RequestDate', [$from, $to])->count();
            $moneyChartData->push($count);
        }
        return view('admin.reqsts.requisition_report', [
            'moneyRequests' => $moneyRequests,
            'start' => $start,
            'end' => $end,
            'companies' => $companies,
            'workPoints' => $workPoints,
            'moneyRequestsGrandTotal' => $moneyRequestsGrandTotal,
            'moneyRequestsApprovedTotal' => $moneyRequestsApprovedTotal,
            'moneyRequestsCashedOutTotal' => $moneyRequestsCashedOutTotal,
            'moneyRequestsRetiredTotal' => $moneyRequestsRetiredTotal,
            'moneyApprovedLike' => $moneyApprovedLike,
            'moneyUnapproved' => $moneyUnapproved,
            'chartLabels' => $months->values()->all(),
            'moneyChartData' => $moneyChartData->values()->all(),
            'companyId' => $companyId,
            'workPointId' => $workPointId,
        ]);
    }

    public function cashoutRetirementReport(Request $request)
    {
        $user = auth()->user();

        $start = $request->start_date ?: null;
        $end = $request->end_date ?: null;
        $companyId = $request->company_id ?: null;
        $workPointId = $request->work_point_id ?: null;
        $useDateFilter = ($start && $end);

        $query = MoneyRequest::with([
                'company',
                'unit',
                'workpoint',
                'requester',
                'approver',
                'cashier',
                'retreater',
            ])
            ->visibleTo($user)
            ->whereIn('Status', ['Cashed-out', 'Retired']);

        if ($companyId && $this->canViewAllMoneyRequests($user)) {
            $query->where('company_id', $companyId);
        }

        if ($workPointId) {
            $query->where('work_point_id', $workPointId);
        }

        if ($useDateFilter) {
            $query->whereBetween('RequestDate', [$start, $end]);
        }

        $moneyRequests = $query->orderByDesc('RequestDate')->orderByDesc('id')->get();

        $totalApprovedAmount = (float) $moneyRequests->sum(function ($r) {
            return (float) ($r->approved_amount ?? $r->total_amount ?? 0);
        });

        $totalReturnedAmount = (float) $moneyRequests->sum(function ($r) {
            return (float) ($r->returned_amount ?? 0);
        });

        $actualSpendings = (float) $moneyRequests->sum(function ($r) {
            $approved = (float) ($r->approved_amount ?? $r->total_amount ?? 0);
            $returned = (float) ($r->returned_amount ?? 0);
            return $approved - $returned;
        });

        $companies = $this->canViewAllMoneyRequests($user)
            ? CompanySite::orderBy('company_name')->get()
            : CompanySite::where('id', $user->company_id)->orderBy('company_name')->get();

        if ($this->canViewAllMoneyRequests($user)) {
            $workPoints = WorkPoint::orderBy('work_name')->get();
        } elseif ($this->canViewCompanyMoneyRequests($user)) {
            $workPoints = WorkPoint::where('company_id', $user->company_id)->orderBy('work_name')->get();
        } elseif ($this->canViewUnitMoneyRequests($user)) {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->orderBy('work_name')->get();
        } else {
            $workPoints = collect();
        }

        return view('admin.reqsts.cashout_retirement_report', compact(
            'moneyRequests',
            'start',
            'end',
            'companies',
            'workPoints',
            'companyId',
            'workPointId',
            'totalApprovedAmount',
            'totalReturnedAmount',
            'actualSpendings'
        ));
    }

    public function cashoutRetirementShow($id)
    {
        $mr = MoneyRequest::with([
                'company',
                'unit',
                'workpoint',
                'requester',
                'verifier',
                'approver',
                'cashier',
                'retreater',
                'account',
                'subAccount',
                'department',
                'section',
            ])->findOrFail(decrypt($id));

        abort_unless(
            $this->canViewAllMoneyRequests(auth()->user())
            || $mr->User_id === auth()->id()
            || $mr->company_id === auth()->user()->company_id
            || $mr->company_unit_id === auth()->user()->comp_unit_id,
            403
        );

        abort_unless(in_array($mr->Status, ['Cashed-out', 'Retired'], true), 403);

        return view('admin.reqsts.cashout_retirement_show', compact('mr'));
    }

    public function retirementDocument($id)
    {
        $mr = MoneyRequest::findOrFail(decrypt($id));

        abort_unless($this->canViewAllMoneyRequests(auth()->user())
            || $mr->User_id === auth()->id()
            || $mr->company_id === auth()->user()->company_id
            || $mr->company_unit_id === auth()->user()->comp_unit_id, 403);

        abort_unless(!empty($mr->retirement_docs), 404);

        $path = public_path($mr->retirement_docs);

        abort_unless(File::exists($path), 404);

        return response()->file($path);
    }
    public function requisitionBook(Request $request)
    {
        $user = auth()->user();

        $start = $request->start_date ?: null;
        $end = $request->end_date ?: null;
        $workPointId = $request->work_point_id ?: null;
        $statusFilter = $request->status ?: null;

        $query = MoneyRequest::with([
                'company',
                'unit',
                'workpoint',
                'requester',
                'verifier',
                'approver',
                'cashier',
                'retreater',
                'account',
                'subAccount',
                'department',
                'section',
            ])
            ->visibleTo($user)
            ->where('Status', '!=', 'Deleted');

        if ($workPointId) {
            $query->where('work_point_id', $workPointId);
        }

        if ($start && $end) {
            $query->whereBetween('RequestDate', [$start, $end]);
        }

        // Used only when dashboard box sends approved filter
        if ($statusFilter === 'approved') {
            $query->whereIn('Status', ['Approved', 'Cashed-out', 'Retired']);
        }

        $moneyRequests = $query
            ->orderByRaw("
                CASE Status
                    WHEN 'Pending' THEN 1
                    WHEN 'Verified' THEN 2
                    WHEN 'Approved' THEN 3
                    WHEN 'Cashed-out' THEN 4
                    WHEN 'Retired' THEN 5
                    WHEN 'Declined' THEN 6
                    WHEN 'Rejected' THEN 7
                    ELSE 8
                END
            ")
            ->orderByDesc('RequestDate')
            ->orderByDesc('id')
            ->get();

        $moneyRequests = $this->decorateMoneyRequestAccounting($moneyRequests);

        $grandTotal = (float) $moneyRequests->sum(function ($r) use ($statusFilter) {
            if ($statusFilter === 'approved') {
                return (float) ($r->approved_amount ?? $r->total_amount ?? 0);
            }

            if (in_array($r->Status, ['Rejected', 'Declined'], true)) {
                return 0;
            }

            return (float) ($r->total_amount ?? 0);
        });

        if ($this->canViewAllMoneyRequests($user)) {
            $workPoints = WorkPoint::where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } elseif ($this->canViewCompanyMoneyRequests($user)) {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        } elseif ($this->canViewUnitMoneyRequests($user)) {
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        } else {
            $workPoints = collect();
        }

        return view('admin.reqsts.requisition_book', compact(
            'moneyRequests',
            'workPoints',
            'start',
            'end',
            'workPointId',
            'grandTotal',
            'statusFilter'
        ));
    }

    public function requisitionBookExportExcel(Request $request)
    {
        $user = auth()->user();
        $start = $request->start_date ?: null;
        $end = $request->end_date ?: null;
        $workPointId = $request->work_point_id ?: null;
        $useDateFilter = ($start && $end);

        $query = MoneyRequest::with([
                'company',
                'unit',
                'workpoint',
                'requester',
                'verifier',
                'approver',
                'cashier',
                'retreater',
                'account',
                'subAccount',
                'department',
                'section',
            ])
            ->visibleTo($user)
            ->where('Status', '!=', 'Deleted');

        if ($workPointId) {
            $query->where('work_point_id', $workPointId);
        }

        if ($useDateFilter) {
            $query->whereBetween('RequestDate', [$start, $end]);
        }

        $moneyRequests = $query
            ->orderByRaw("
                CASE Status
                    WHEN 'Pending' THEN 1
                    WHEN 'Verified' THEN 2
                    WHEN 'Approved' THEN 3
                    WHEN 'Cashed-out' THEN 4
                    WHEN 'Retired' THEN 5
                    WHEN 'Declined' THEN 6
                    WHEN 'Rejected' THEN 7
                    ELSE 8
                END
            ")
            ->orderByDesc('RequestDate')
            ->orderByDesc('id')
            ->get();

        $moneyRequests = $this->decorateMoneyRequestAccounting($moneyRequests);
        $grandTotal = (float) $moneyRequests->sum('total_amount');

        $fileName = 'requisition_book_' . now()->format('Ymd_His') . '.xls';

        $html = '';
        $html .= '<html>';
        $html .= '<head><meta charset="UTF-8"></head>';
        $html .= '<body>';
        $html .= '<table border="1">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Date</th>';
        $html .= '<th>Ref No.</th>';
        $html .= '<th>Payee</th>';
        $html .= '<th>ACCOUNT CODE</th>';
        $html .= '<th>ACCOUNT DESCRIPTION</th>';
        $html .= '<th>SUB ACCOUNT CODE</th>';
        $html .= '<th>SUB ACCOUNT DESCRIPTION</th>';
        $html .= '<th>COMPANY CODE</th>';
        $html .= '<th>COMPANY DESCRIPTION</th>';
        $html .= '<th>BUSINESS CODE</th>';
        $html .= '<th>BUSINESS DESCRIPTION</th>';
        $html .= '<th>DEPARTMENT CODE</th>';
        $html .= '<th>DEPARTMENT DESCRIPTION</th>';
        $html .= '<th>SECTION CODE</th>';
        $html .= '<th>SECTION DESCRIPTION</th>';
        $html .= '<th>CODE</th>';
        $html .= '<th>LOCATION</th>';
        $html .= '<th>MEMO</th>';
        $html .= '<th>AMOUNT</th>';
        $html .= '<th>Raised By</th>';
        $html .= '<th>Status</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($moneyRequests as $m) {
            $html .= '<tr>';
            $html .= '<td>' . e($m->RequestDate ? \Carbon\Carbon::parse($m->RequestDate)->format('d.m.Y') : '-') . '</td>';
            $html .= '<td>' . e($m->RequestNo) . '</td>';
            $html .= '<td>' . e($m->PayeeName) . '</td>';

            $html .= '<td>' . e($m->accounting_code_6 ?? '-') . '</td>';
            $html .= '<td>' . e($m->accounting_name_6 ?? '-') . '</td>';

            $html .= '<td>' . e($m->sub_accounting_code_8 ?? '-') . '</td>';
            $html .= '<td>' . e($m->sub_accounting_name_8 ?? '-') . '</td>';

            $html .= '<td>' . e(optional($m->company)->company_code ?? '-') . '</td>';
            $html .= '<td>' . e(optional($m->company)->company_name ?? '-') . '</td>';

            $html .= '<td>' . e(optional($m->unit)->unit_code ?? '-') . '</td>';
            $html .= '<td>' . e(optional($m->unit)->unit_name ?? '-') . '</td>';

            $html .= '<td>' . e(optional($m->department)->depCode ?? '-') . '</td>';
            $html .= '<td>' . e(optional($m->department)->depName ?? '-') . '</td>';

            $html .= '<td>' . e(optional($m->section)->secCode ?? '-') . '</td>';
            $html .= '<td>' . e(optional($m->section)->secName ?? '-') . '</td>';

            $html .= '<td>' . e(optional($m->workpoint)->work_code ?? '-') . '</td>';
            $html .= '<td>' . e(optional($m->workpoint)->work_name ?? '-') . '</td>';

            $html .= '<td>' . e($m->remarks ?? '-') . '</td>';
            $html .= '<td>' . e(number_format((float) $m->total_amount, 2, '.', '')) . '</td>';
            $html .= '<td>' . e(optional($m->requester)->name ?? '-') . '</td>';
            $html .= '<td>' . e($m->Status) . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr>';
        $html .= '<td colspan="18" style="text-align:right;"><strong>Grand Total</strong></td>';
        $html .= '<td><strong>' . number_format($grandTotal, 2, '.', '') . '</strong></td>';
        $html .= '<td colspan="2"></td>';
        $html .= '</tr>';

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</body>';
        $html .= '</html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    
    public function moneyUnverify(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user->can('Verify-MoneyRequest') || $this->canViewAllMoneyRequests($user), 403);
        $mr = MoneyRequest::visibleTo($user)->findOrFail(decrypt($id));
        abort_unless($mr->Status === 'Verified', 403);

        abort_unless(
            $this->canViewAllMoneyRequests($user)
            || (int) $mr->verified_by === (int) $user->id,
            403
        );

        $mr->update([
            'Status' => 'Pending',
            'verified_by' => null,
            'verified_at' => null,
            'verified_comment' => null,
        ]);

        Alert::success('Success', 'Money request returned to pending.');
        return redirect()->back();
    }
    public function moneyBulkApprove(Request $request)
    {
        $user = auth()->user();

        abort_unless($user->can('Approve-MoneyRequest') || $this->canViewAllMoneyRequests($user), 403);

        $request->validate([
            'money_ids' => ['required', 'array', 'min:1'],
            'money_ids.*' => ['required'],
            'approval_comment' => ['nullable', 'string', 'max:5000'],
            'Payment_mode' => ['required', 'in:Cash,Cheque'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'cheque_bank_account' => ['nullable', 'string', 'max:255'],
        ]);

        $ids = collect($request->money_ids)->map(function ($id) {
            return decrypt($id);
        })->values()->all();

        DB::beginTransaction();

        try {
            $moneyRequests = MoneyRequest::visibleTo($user)
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            abort_unless($moneyRequests->count() === count($ids), 403);

            foreach ($moneyRequests as $mr) {
                abort_unless($mr->Status === 'Verified', 403);
            }

            $comment = trim((string) $request->approval_comment);
            if ($comment === '') {
                $comment = 'Okay';
            }

            if ($request->Payment_mode === 'Cheque') {
                $comment .= "\nBank Name: " . trim((string) $request->bank_name);
                $comment .= "\nCheque Bank Account: " . trim((string) $request->cheque_bank_account);
            }

            foreach ($moneyRequests as $mr) {
                $mr->update([
                    'Status' => 'Approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approved_amount' => $mr->total_amount,
                    'Payment_mode' => $request->Payment_mode,
                    'approval_comment' => $comment,
                ]);
            }

            DB::commit();

            Alert::success('Success', 'Selected money requests approved.');
            return redirect()->back();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function moneyBulkCashOut(Request $request)
    {
        $user = auth()->user();

        abort_unless($user->can('CashOut-MoneyRequest') || $this->canViewAllMoneyRequests($user), 403);

        $request->validate([
            'money_ids' => ['required', 'array', 'min:1'],
            'money_ids.*' => ['required'],
            'payment_vocher_no' => ['required', 'string', 'max:255'],
            'cashier_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $ids = collect($request->money_ids)->map(function ($id) {
            return decrypt($id);
        })->values()->all();

        DB::beginTransaction();

        try {
            $moneyRequests = MoneyRequest::visibleTo($user)
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            abort_unless($moneyRequests->count() === count($ids), 403);

            foreach ($moneyRequests as $mr) {
                abort_unless($mr->Status === 'Approved', 403);
            }

            $cashierComment = trim((string) $request->cashier_comment);
            if ($cashierComment === '') {
                $cashierComment = 'Okay';
            }

            foreach ($moneyRequests as $mr) {
                $mr->update([
                    'Status' => 'Cashed-out',
                    'cashed_by' => $user->id,
                    'cashed_at' => now(),
                    'payment_vocher_no' => $request->payment_vocher_no,
                    'cashier_comment' => $cashierComment,
                ]);
            }

            DB::commit();

            Alert::success('Success', 'Selected money requests cashed out.');
            return redirect()->back();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    protected function gsReqGlobalRoles(): array
    {
        return ['Admin', 'CEO', 'Managing Director (MD)', 'Admin-Developer'];
    }

    protected function gsReqCanViewAll($user): bool
    {
        return $user->can('View-All-General-Supply-Requests') || in_array($user->role, $this->gsReqGlobalRoles(), true);
    }

    protected function gsReqCanViewCompany($user): bool
    {
        return $user->can('View-Company-General-Supply-Requests') || $user->role === 'Company Manager';
    }

    protected function gsReqCanViewUnit($user): bool
    {
        return $user->can('View-Unit-General-Supply-Requests') || $user->role === 'Unit Manager';
    }

    protected function gsRequestVisibleQuery($user)
    {
        $q = GeneralSupplyRequest::with([
            'company','unit','workpoint','department','section','item','description','issues'
        ])->orderByDesc('id');

        if ($this->gsReqCanViewAll($user)) {
            return $q;
        }

        if ($this->gsReqCanViewCompany($user)) {
            return $q->where('company_id', $user->company_id);
        }

        if ($this->gsReqCanViewUnit($user)) {
            return $q->where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id);
        }

        return $q->where('requested_by', $user->id);
    }

    protected function gsGenerateRequestNo(int $workPointId, string $requestDate): string
    {
        $work = WorkPoint::find($workPointId);
        $workCode = strtoupper(trim(optional($work)->work_code ?: 'WRK'));
        $datePart = \Carbon\Carbon::parse($requestDate)->format('dmY');
        $monthKey = \Carbon\Carbon::parse($requestDate)->format('Ym');

        $lastId = GeneralSupplyRequest::where('work_point_id', $workPointId)
            ->whereRaw("DATE_FORMAT(request_date, '%Y%m') = ?", [$monthKey])
            ->max('id');

        return 'REQ' . str_pad((string)(((int)$lastId) + 1), 4, '0', STR_PAD_LEFT) . '-' . $workCode . '/' . $datePart;
    }
    public function gsRequisitionIndex()
    {
        $user = auth()->user();

        $requests = $this->gsRequestVisibleQuery($user)->get();
        $workPoints = WorkPoint::where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        $sections = Section::where('Status', 'Active')->orderBy('secName')->get();
        $items = GeneralSupplyItem::where('status', 'Active')->orderBy('item_name')->get();

        $companies = CompanySite::all();
        $businessUnits = DB::table('company_units')->get();
        $departments = Department::all();

        // HII NDIO FIX
        $accounts = \App\Models\AccntSubchart::where('Status','Active')
        ->whereRaw('CHAR_LENGTH(TRIM(SubCode)) = 8')
        ->orderBy('SubCode')
        ->get();

        return view('admin.reqsts.requisition', compact(
            'requests',
            'workPoints',
            'sections',
            'items',
            'companies',
            'businessUnits',
            'departments',
            'accounts' 
        ));
    }

    public function gsRequisitionStore(Request $request)
    {
        $user = auth()->user();

        try {
            // VALIDATION (IMEONGEZWA account_code)
            $request->validate([
                'work_point_id' => 'required|exists:work_points,id',
                'section_id' => 'nullable|exists:sections,id',
                'item_id' => 'required|exists:general_supply_items,id',
                'item_description_id' => 'required|exists:general_supply_item_descriptions,id',
                'request_date' => 'required|date',
                'requested_qty' => 'required|numeric|min:0.01',
                'account_code' => 'required', 
                'reason' => 'nullable|string',
            ]);

            $desc = GeneralSupplyItemDescription::findOrFail($request->item_description_id);

            if ((int)$desc->item_id !== (int)$request->item_id) {
                Alert::error('Error', 'Selected description does not belong to selected item');
                return back()->withInput();
            }

            $deptId = null;
            $sectionId = $request->section_id ?: null;

            if ($sectionId) {
                $section = Section::findOrFail($sectionId);
                $deptId = $section->dept_id;
            }

            //  CHECK DUPLICATE OPEN REQUEST
            $openReq = GeneralSupplyRequest::where('requested_by', $user->id)
                ->where('work_point_id', $request->work_point_id)
                ->where('section_id', $sectionId)
                ->where('item_id', $request->item_id)
                ->where('item_description_id', $request->item_description_id)
                ->whereIn('status', ['Pending', 'Partial'])
                ->exists();

            if ($openReq) {
                Alert::error('Error', 'You already have an unfinished request for this item/description.');
                return back()->withInput();
            }

            // STOCK CHECK
            $sharedStock = GeneralSupplyStock::where('work_point_id', $request->work_point_id)
                ->where('item_id', $request->item_id)
                ->where('item_description_id', $request->item_description_id)
                ->where('stock_scope', 'Shared')
                ->sum('balance');

            $dedicatedStock = 0;
            if ($sectionId) {
                $dedicatedStock = GeneralSupplyStock::where('work_point_id', $request->work_point_id)
                    ->where('item_id', $request->item_id)
                    ->where('item_description_id', $request->item_description_id)
                    ->where('stock_scope', 'Dedicated')
                    ->where('section_id', $sectionId)
                    ->sum('balance');
            }

            $available = (float)$sharedStock + (float)$dedicatedStock;

            // AUTO DECISION (ERP LOGIC)
            $requestType = $available >= $request->requested_qty ? 'stock' : 'purchase';

            $workPoint = WorkPoint::findOrFail($request->work_point_id);

            //  SAVE REQUEST (UPDATED)
            GeneralSupplyRequest::create([
                'company_id' => $workPoint->company_id,
                'comp_unit_id' => $workPoint->comp_unit_id,
                'work_point_id' => $request->work_point_id,
                'dept_id' => $deptId,
                'section_id' => $sectionId,

                'stock_scope' => $sectionId ? 'Dedicated' : 'Shared',

                'item_id' => $request->item_id,
                'item_description_id' => $request->item_description_id,

                'request_date' => $request->request_date,
                'request_no' => $this->gsGenerateRequestNo($request->work_point_id, $request->request_date),

                'requested_qty' => $request->requested_qty,
                'issued_qty' => 0,

                'reason' => $request->reason,

                //  NEW FIELDS
                'account_code' => $request->account_code,
                'request_type' => $requestType,

                'requested_by' => $user->id,
                'status' => 'Pending',
            ]);

            Alert::success('Success', 'Request created as ' . strtoupper($requestType));
            return redirect()->route('req.gs.index');

        } catch (\Throwable $e) {
            Alert::error('Error', $e->getMessage());
            return back()->withInput();
        }
    }

    public function gsRequisitionUpdate(Request $request, $id)
    {
        $user = auth()->user();

        try {
            $realId = decrypt($id);
            $row = GeneralSupplyRequest::findOrFail($realId);

            if (!$this->gsReqCanViewAll($user) && (int)$row->requested_by !== (int)$user->id) {
                Alert::error('Error', 'Not allowed');
                return back();
            }

            if ($row->status === 'Issued' || (float)$row->issued_qty > 0) {
                Alert::error('Error', 'Issued request cannot be updated');
                return back();
            }

            $request->validate([
                'request_date' => 'required|date',
                'requested_qty' => 'required|numeric|min:0.01',
                'reason' => 'nullable|string',
            ]);

            $sharedStock = GeneralSupplyStock::where('work_point_id', $row->work_point_id)
                ->where('item_id', $row->item_id)
                ->where('item_description_id', $row->item_description_id)
                ->where('stock_scope', 'Shared')
                ->sum('balance');

            $dedicatedStock = 0;
            if ($row->section_id) {
                $dedicatedStock = GeneralSupplyStock::where('work_point_id', $row->work_point_id)
                    ->where('item_id', $row->item_id)
                    ->where('item_description_id', $row->item_description_id)
                    ->where('stock_scope', 'Dedicated')
                    ->where('section_id', $row->section_id)
                    ->sum('balance');
            }

            $maxAllowed = ((float)$sharedStock + (float)$dedicatedStock + (float)$row->issued_qty);

            if ((float)$request->requested_qty > $maxAllowed) {
                Alert::error('Error', 'Requested quantity exceeds available stock');
                return back()->withInput();
            }

            if ((float)$request->requested_qty < (float)$row->issued_qty) {
                Alert::error('Error', 'Requested quantity cannot be less than already issued quantity');
                return back()->withInput();
            }

            $status = 'Pending';
            if ((float)$row->issued_qty > 0 && (float)$row->issued_qty < (float)$request->requested_qty) {
                $status = 'Partial';
            }
            if ((float)$row->issued_qty == (float)$request->requested_qty) {
                $status = 'Issued';
            }

            $row->update([
                'request_date' => $request->request_date,
                'requested_qty' => $request->requested_qty,
                'reason' => $request->reason,
                'status' => $status,
            ]);

            Alert::success('Success', 'Request updated successfully');
            return redirect()->route('req.gs.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to update request');
            return back()->withInput();
        }
    }

    public function gsRequisitionDestroy($id)
    {
        $user = auth()->user();

        try {
            $realId = decrypt($id);
            $row = GeneralSupplyRequest::findOrFail($realId);

            if (!$this->gsReqCanViewAll($user) && (int)$row->requested_by !== (int)$user->id) {
                Alert::error('Error', 'Not allowed');
                return back();
            }

            if ($row->status === 'Issued' || (float)$row->issued_qty > 0) {
                Alert::error('Error', 'Cannot remove request that was already issued');
                return back();
            }

            $row->delete();

            Alert::success('Success', 'Request removed successfully');
            return redirect()->route('req.gs.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to remove request');
            return back();
        }
    }

    public function gsRequisitionConfirmReceipt(Request $request, $id)
    {
        $user = auth()->user();

        try {
            $realId = decrypt($id);
            $row = GeneralSupplyRequest::findOrFail($realId);

            if (!$this->gsReqCanViewAll($user) && (int)$row->requested_by !== (int)$user->id) {
                Alert::error('Error', 'Not allowed');
                return back();
            }

            if ((float)$row->issued_qty <= 0) {
                Alert::error('Error', 'No issued quantity found for this request');
                return back();
            }

            $request->validate([
                'received_qty' => 'required|numeric|min:0.01',
                'received_date' => 'required|date',
                'received_remarks' => 'nullable|string',
            ]);

            if ((float)$request->received_qty > (float)$row->issued_qty) {
                Alert::error('Error', 'Received quantity cannot exceed issued quantity');
                return back()->withInput();
            }

            $row->update([
                'received_qty' => $request->received_qty,
                'received_date' => $request->received_date,
                'received_remarks' => $request->received_remarks,
                'received_confirmed_by' => $user->id,
            ]);

            Alert::success('Success', 'Receipt confirmation saved successfully');
            return redirect()->route('req.gs.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to save receipt confirmation');
            return back()->withInput();
        }
    }

    public function gsAjaxDescriptionsByItem($itemId)
    {
        $rows = GeneralSupplyItemDescription::where('item_id', $itemId)
            ->where('status', 'Active')
            ->orderBy('description_name')
            ->get(['id', 'description_name', 'unit_name']);

        return response()->json($rows);
    }

    public function gsAjaxAvailableStock(Request $request)
    {
        $request->validate([
            'work_point_id' => 'required|exists:work_points,id',
            'section_id' => 'nullable|exists:sections,id',
            'item_id' => 'required|exists:general_supply_items,id',
            'item_description_id' => 'required|exists:general_supply_item_descriptions,id',
        ]);

        $shared = GeneralSupplyStock::where('work_point_id', $request->work_point_id)
            ->where('item_id', $request->item_id)
            ->where('item_description_id', $request->item_description_id)
            ->where('stock_scope', 'Shared')
            ->sum('balance');

        $dedicated = 0;
        if ($request->section_id) {
            $dedicated = GeneralSupplyStock::where('work_point_id', $request->work_point_id)
                ->where('item_id', $request->item_id)
                ->where('item_description_id', $request->item_description_id)
                ->where('stock_scope', 'Dedicated')
                ->where('section_id', $request->section_id)
                ->sum('balance');
        }

        return response()->json([
            'shared_qty' => (float)$shared,
            'dedicated_qty' => (float)$dedicated,
            'total_available' => (float)$shared + (float)$dedicated,
        ]);
    }
    public function gsRequisitionReport()
    {
        $user = auth()->user();
        $rows = $this->gsRequestVisibleQuery($user)->get();

        return view('admin.reqsts.gsrequisition_report', compact('rows'));
    }
    // Add these functions inside RequisitionController.
    // Keep your existing gsAjaxDescriptionsByItem() and gsAjaxAvailableStock() as they are.
    public function gsAjaxCompanyUnitsByCompany($companyId)
    {
        $rows = \App\Models\Company_unit::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('status', 'Active')
                ->orWhereNull('status');
            })
            ->orderBy('unit_name')
            ->get(['id', 'unit_code', 'unit_name']);

        return response()->json($rows);
    }

    public function gsAjaxWorkPointsByUnit($unitId)
    {
        $unit = \App\Models\Company_unit::findOrFail($unitId);

        $rows = \App\Models\WorkPoint::where('comp_unit_id', $unitId)->where('company_id', $unit->company_id)
            ->where(function ($q) {
                $q->where('status', 'Active')->orWhere('status', '!=', 'Deleted') ->orWhereNull('status');
            })->orderBy('work_name')->get(['id', 'work_code', 'work_name', 'location']);
        return response()->json($rows);
    }
    public function gsAjaxSectionsByDepartment($departmentId)
    {
        $department = \App\Models\Department::findOrFail($departmentId);
        $rows = \App\Models\Section::where('dept_id', $department->id)
            ->where(function ($q) {
                $q->where('Status', 'Active')->orWhereNull('Status');
            })->orderBy('secName')->get(['id', 'secCode', 'secName']);
        return response()->json($rows);
    }
}
