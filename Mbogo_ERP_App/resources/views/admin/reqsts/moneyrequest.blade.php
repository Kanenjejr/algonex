@extends('layouts.ReqstMaster')
@section('content')

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Requisition & Approvals Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('requisition') }}">Requisition & Approvals</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Money Request</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php
                        use Carbon\Carbon;
                        $carbon = Carbon::now();
                        $carbon1 = Carbon::now()->toDateString();
                        echo $carbon->format('l');
                        echo ' , ';
                        echo $carbon1;
                        ?>
                    </strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-1">
            <h2>Time</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <table>
                            <tr>
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg() {
            setInterval("change_time();", 1000);
        }

        function change_time() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            if (curr_hour > 24) curr_hour = curr_hour - 24;
            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }
        timedMsg();
    </script>

    @php
        /*
         |------------------------------------------------------------------
         | IMPORTANT
         |------------------------------------------------------------------
         | Controller should pass:
         | $canEditWorkPoint
         | $moneyRequests (already decorated with accounting_code_6, accounting_name_6,
         |                 sub_accounting_code_8, sub_accounting_name_8)
         | $workPoints
         | $companies
         | $departments
         | $subAccounts (8-digit only)
         | $sections
         */

        if (!isset($canEditWorkPoint)) {
            $canEditWorkPoint = false;
        }

        $companyOptions = [];
        foreach ($companies as $company) {
            $companyOptions[] = [
                'id' => $company->id,
                'name' => trim(($company->company_code ?? '') . ' - ' . $company->company_name, ' -'),
            ];
        }

        $departmentOptions = [];
        foreach ($departments as $department) {
            $departmentOptions[] = [
                'id' => $department->id,
                'name' => trim(($department->depCode ?? '') . ' - ' . $department->depName, ' -'),
            ];
        }

        $workPointOptions = [];
        foreach ($workPoints as $wp) {
            $workPointOptions[] = [
                'id' => $wp->id,
                'name' => $wp->work_code . ' - ' . $wp->work_name,
            ];
        }

        $subAccountOptions = [];
        foreach ($subAccounts as $sub) {
            $subAccountOptions[] = [
                'id' => $sub->id,
                'name' => $sub->SubCode . ' - ' . ($sub->SubDescription ?? ''),
            ];
        }

        $sectionOptions = [];
        foreach ($sections as $sec) {
            $sectionOptions[] = [
                'id' => $sec->id,
                'name' => ($sec->secCode ?? '') . ' - ' . $sec->secName,
            ];
        }

        $statusLabel = function ($status) {
            switch ($status) {
                case 'Pending':
                    return 'badge badge-warning';
                case 'Verified':
                    return 'badge badge-info';
                case 'Approved':
                    return 'badge badge-primary';
                case 'Cashed-out':
                    return 'badge badge-success';
                case 'Retired':
                    return 'badge badge-dark';
                case 'Declined':
                    return 'badge badge-danger-light';
                case 'Rejected':
                    return 'badge badge-danger';
                default:
                    return 'badge badge-secondary';
            }
        };
    @endphp

    <div class="col-12 mb-3">
        <h3 class="mb-2 page-title">Money Requests</h3>

        @can('Register-MoneyRequest')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openCreateModal()">
                Create Money Request
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox"
                    style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">
                    <div class="ibox-title"
                        style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <h5 style="margin:0; font-weight:800; color:#fff;">{{ $pageTitle ?? 'Money Requests Table' }}
                            </h5>
                        </div>
                    </div>

                    <div class="ibox-content">
                        @php
                            $pageTitle = $pageTitle ?? 'Pending Money Requisitions';
                            $activeQueue = $activeQueue ?? 'pending';
                        @endphp

                        <div class="row mb-3">
                            <div class="col-md-12">

                                <a href="{{ route('moneyrequest.pending') }}"
                                    class="btn btn-sm {{ $activeQueue == 'pending' ? 'btn-primary' : 'btn-default' }}">
                                    Pending
                                </a>

                                @can('Approve-MoneyRequest')
                                    <a href="{{ route('moneyrequest.verified') }}"
                                        class="btn btn-sm {{ $activeQueue == 'verified' ? 'btn-primary' : 'btn-default' }}">
                                        Verified Need Approval
                                    </a>
                                @endcan

                                @can('CashOut-MoneyRequest')
                                    <a href="{{ route('moneyrequest.approved') }}"
                                        class="btn btn-sm {{ $activeQueue == 'approved' ? 'btn-primary' : 'btn-default' }}">
                                        Approved / Cash-out / Retirement
                                    </a>
                                @endcan

                                <a href="{{ route('moneyrequest.rejected') }}"
                                    class="btn btn-sm {{ $activeQueue == 'rejected' ? 'btn-primary' : 'btn-default' }}">
                                    Rejected
                                </a>

                            </div>
                        </div>

                        <form method="GET" action="{{ url()->current() }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date"
                                        value="{{ request('start_date', $start ?? '') }}" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" value="{{ request('end_date', $end ?? '') }}"
                                        class="form-control">
                                </div>

                                <div class="col-md-3" style="padding-top:25px;">
                                    <button type="submit" class="btn btn-primary">
                                        Filter
                                    </button>

                                    <a href="{{ url()->current() }}" class="btn btn-danger">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                @can('Approve-MoneyRequest')
                                    <button type="button" class="btn btn-sm btn-success" onclick="openBulkApproveModal()">
                                        Bulk Approve Selected
                                    </button>
                                @endcan

                                @can('CashOut-MoneyRequest')
                                    <button type="button" class="btn btn-sm btn-primary" onclick="openBulkCashOutModal()">
                                        Bulk Cash Out Selected
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="checkAllMoney"></th>
                                        <th>#</th>
                                        <th>Reference No</th>
                                        <th>Request Date</th>
                                        <th>Working Point</th>
                                        <th>Details</th>
                                        <th>Acc & Sec Code</th>
                                        <th>Amount</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Requested By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($moneyRequests as $k => $m)
                                        @php
                                            $payload = [
                                                'id' => encrypt($m->id),
                                                'request_no' => $m->RequestNo,
                                                'request_date' => $m->RequestDate
                                                    ? \Carbon\Carbon::parse($m->RequestDate)->format('Y-m-d')
                                                    : '',
                                                'company_id' => $m->company_id,
                                                'company_unit_id' => $m->company_unit_id,
                                                'work_point_id' => $m->work_point_id,
                                                'department_id' => $m->department_id,
                                                'work_point_name' =>
                                                    optional($m->workpoint)->work_code .
                                                    ' - ' .
                                                    optional($m->workpoint)->work_name,
                                                'company_name' => optional($m->company)->company_name,
                                                'unit_name' => optional($m->unit)->unit_name,
                                                'details' => $m->Description,
                                                'amount' => $m->total_amount,
                                                'purpose' => $m->remarks,
                                                'status' => $m->Status,
                                                'requested_by' => optional($m->requester)->name,
                                                'verified_comment' => $m->verified_comment,
                                                'approval_comment' => $m->approval_comment,
                                                'cashier_comment' => $m->cashier_comment,
                                                'rejection_comment' => $m->rejection_comment,
                                                'payment_mode' => $m->Payment_mode,
                                                'sub_account_id' => $m->sub_account_id,
                                                'section_id' => $m->section_id,

                                                'accounting_code_6' => $m->accounting_code_6 ?? null,
                                                'accounting_name_6' => $m->accounting_name_6 ?? null,
                                                'sub_accounting_code_8' => $m->sub_accounting_code_8 ?? null,
                                                'sub_accounting_name_8' => $m->sub_accounting_name_8 ?? null,
                                            ];

                                            $u = auth()->user();
                                            $isOwner = $u && $u->id === $m->User_id;
                                            $isRequesterEditAllowed =
                                                $isOwner && in_array($m->Status, ['Pending', 'Rejected'], true);
                                            $isApproverEditAllowed =
                                                $u &&
                                                $u->can('Approve-MoneyRequest') &&
                                                in_array($m->Status, ['Pending', 'Verified'], true);
                                            $canEditNow = $isRequesterEditAllowed || $isApproverEditAllowed;
                                            $canDeleteNow = $isRequesterEditAllowed || $isApproverEditAllowed;
                                        @endphp
                                        <tr>
                                            <td>
                                                @if (in_array($m->Status, ['Verified', 'Approved'], true))
                                                    <input type="checkbox" class="money-check"
                                                        value="{{ encrypt($m->id) }}" data-status="{{ $m->Status }}"
                                                        data-request-no="{{ $m->RequestNo }}"
                                                        data-amount="{{ (float) $m->total_amount }}">
                                                @endif
                                            </td>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $m->RequestNo }}</td>
                                            <td>{{ $m->RequestDate ? \Carbon\Carbon::parse($m->RequestDate)->format('Y-m-d') : '-' }}
                                            </td>
                                            <td>{{ optional($m->workpoint)->work_code }} -
                                                {{ optional($m->workpoint)->work_name }}</td>
                                            <td>
                                                Payee: {{ $m->PayeeName }}<br>
                                                @if ($m->PayeeContact)
                                                    <small>Contact: {{ $m->PayeeContact }}</small><br>
                                                @endif
                                                <small>{{ $m->Description }}</small>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>Acc:</strong>
                                                    {{ $m->accounting_code_6 ?? '-' }}
                                                    @if (!empty($m->accounting_name_6))
                                                        -> {{ $m->accounting_name_6 }}
                                                    @endif
                                                    <br>

                                                    <strong>Sub Acc:</strong>
                                                    {{ $m->sub_accounting_code_8 ?? '-' }}
                                                    @if (!empty($m->sub_accounting_name_8))
                                                        -> {{ $m->sub_accounting_name_8 }}
                                                    @endif
                                                    <br>

                                                    <strong>Sec:</strong> {{ optional($m->section)->secCode }} ->
                                                    {{ optional($m->section)->secName }}
                                                </small>
                                            </td>
                                            <td>{{ number_format($m->total_amount, 2) }}</td>
                                            <td>{{ $m->remarks ?? '-' }}</td>
                                            <td><span class="{{ $statusLabel($m->Status) }}">{{ $m->Status }}</span>
                                            </td>
                                            <td>{{ optional($m->requester)->name ?? '-' }}</td>
                                            <td style="white-space:nowrap;">
                                                @can('View-MoneyRequest')
                                                    <a href="{{ route('moneyrequest.show', encrypt($m->id)) }}"
                                                        class="btn btn-sm btn-info">View</a>
                                                @endcan

                                                @can('Print-MoneyRequest')
                                                    <a href="{{ route('moneyrequest.print', encrypt($m->id)) }}"
                                                        target="_blank" class="btn btn-sm btn-dark">Print</a>
                                                @endcan

                                                @if ($canEditNow)
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openEdit(@json($payload))'>Edit</button>
                                                @endif

                                                @if ($canDeleteNow)
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteMoney(@json($payload))'>Delete</button>
                                                @endif

                                                @can('Verify-MoneyRequest')
                                                    @if ($m->Status === 'Pending')
                                                        <button type="button" class="btn btn-sm btn-primary btn-verify-money"
                                                            data-row='@json($payload)'>Verify</button>
                                                    @endif
                                                @endcan

                                                @can('Verify-MoneyRequest')
                                                    @if (
                                                        $m->Status === 'Verified' &&
                                                            ((int) $m->verified_by === (int) auth()->id() || auth()->user()->can('View-All-MoneyRequest')))
                                                        <form action="{{ route('moneyrequest.unverify', encrypt($m->id)) }}"
                                                            method="POST" style="display:inline-block;"
                                                            onsubmit="return confirm('Return this verified request back to pending?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-danger">Unverify</button>
                                                        </form>
                                                    @endif
                                                @endcan

                                                @can('Approve-MoneyRequest')
                                                    @if ($m->Status === 'Verified')
                                                        <button type="button"
                                                            class="btn btn-sm btn-success btn-approve-money"
                                                            data-row='@json($payload)'>Approve</button>
                                                    @endif
                                                @endcan

                                                @can('CashOut-MoneyRequest')
                                                    @if ($m->Status === 'Approved')
                                                        <button type="button"
                                                            class="btn btn-sm btn-success btn-cashout-money"
                                                            data-row='@json($payload)'>Cash Out</button>
                                                    @endif
                                                @endcan

                                                @can('Reteirement-MoneyRequest')
                                                    @if ($m->Status === 'Cashed-out')
                                                        <button type="button" class="btn btn-sm btn-dark btn-retire-money"
                                                            data-row='@json($payload)'>Retire</button>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">No money requests found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="moneyCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="moneyCreateForm" action="{{ route('moneyrequest.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Money Request</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        @if ($canEditWorkPoint)
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Company <span class="text-danger">*</span></label>
                                    <select name="company_id" id="money_company_id" class="form-control select2_modal"
                                        required>
                                        <option value="">-- Select company --</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">
                                                {{ trim(($company->company_code ?? '') . ' - ' . $company->company_name, ' -') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Business Unit <span class="text-danger">*</span></label>
                                    <select name="company_unit_id" id="money_company_unit_id"
                                        class="form-control select2_modal" required>
                                        <option value="">-- Select business unit --</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Working Point <span class="text-danger">*</span></label>
                                    <select name="work_point_id" id="money_work_point_id"
                                        class="form-control select2_modal" required>
                                        <option value="">-- Select working point --</option>
                                    </select>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Department <span class="text-danger">*</span></label>
                                <select name="department_id" id="money_department_id" class="form-control select2_modal"
                                    required>
                                    <option value="">-- Select department --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">
                                            {{ trim(($department->depCode ?? '') . ' - ' . $department->depName, ' -') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Section <span class="text-danger">*</span></label>
                                <select name="section_id" id="money_section_id" class="form-control select2_modal"
                                    required>
                                    <option value="">-- Select section --</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Request Date <span class="text-danger">*</span></label>
                                <input type="date" name="RequestDate" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Payee Name <span class="text-danger">*</span></label>
                                <input type="text" name="PayeeName" class="form-control" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Payee Contact</label>
                                <input type="text" name="PayeeContact" class="form-control">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="total_amount" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Sub Account <span class="text-danger">*</span></label>
                                <select name="sub_account_id" class="form-control select2_modal" required>
                                    <option value="">-- Select sub account --</option>
                                    @foreach ($subAccounts as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->SubCode }} -
                                            {{ $sub->SubDescription }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Details</label>
                                <textarea name="Description" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Purpose</label>
                                <textarea name="remarks" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="moneyEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="moneyEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Money Request</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="moneyEditBody">
                        <div class="text-center">Loading...</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- VERIFY MODAL --}}
    <div class="modal fade" id="verifyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="verifyForm">
                @csrf
                <input type="hidden" name="decision" id="verifyDecision">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Verify Money Request</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="verifySummary"></div>
                        <hr>
                        <div class="form-group">
                            <label>Comment <span class="text-danger">*</span></label>
                            <textarea name="verified_comment" id="verified_comment" class="form-control" rows="4">Okay</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="submitVerify('rejected')">Decline</button>
                        <button type="button" class="btn btn-primary" onclick="submitVerify('verified')">Verify</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- APPROVE MODAL --}}
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="approveForm">
                @csrf
                <input type="hidden" name="decision" id="approveDecision">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Approve Money Request</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="approveSummary"></div>
                        <hr>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Approved Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="approved_amount" id="approved_amount"
                                    class="form-control" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Payment Mode <span class="text-danger">*</span></label>
                                <select name="Payment_mode" id="Payment_mode" class="form-control select2_modal"
                                    onchange="setChequeVisibility()" required>
                                    <option value="">-- Select --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4" id="chequeFields" style="display:none;">
                                <label>Bank Name <span class="text-danger">*</span></label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control">
                            </div>

                            <div class="form-group col-md-4" id="chequeAccountFields" style="display:none;">
                                <label>Cheque Bank Account <span class="text-danger">*</span></label>
                                <input type="text" id="cheque_bank_account_input" name="cheque_bank_account"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Comment (Optional)</label>
                            <textarea name="approval_comment" id="approval_comment_text" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="submitApprove('rejected')">Reject</button>
                        <button type="button" class="btn btn-success"
                            onclick="submitApprove('approved')">Approve</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- CASH OUT MODAL --}}
    <div class="modal fade" id="cashModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="cashForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Cash Out Money Request</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="cashSummary"></div>
                        <hr>
                        <div class="form-group">
                            <label>Voucher No <span class="text-danger">*</span></label>
                            <input type="text" name="payment_vocher_no" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Cashier Comment</label>
                            <textarea name="cashier_comment" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="cashSubmitBtn">Cash Out</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- RETIRE MODAL --}}
    <div class="modal fade" id="retireModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="retireForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">Retirement</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="retireSummary"></div>
                        <hr>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Returned Amount</label>
                                <input type="number" step="0.01" name="returned_amount" class="form-control"
                                    placeholder="Leave blank for 0.00">
                            </div>
                            <div class="form-group col-md-8">
                                <label>Retirement Comment</label>
                                <textarea name="retirement_comment" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Attachment</label>
                            <input type="file" name="retirement_docs" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark" id="retireSubmitBtn">Retire</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- BULK APPROVE MODAL --}}
    <div class="modal fade" id="bulkApproveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" action="{{ route('moneyrequest.bulkapprove') }}" id="bulkApproveForm">
                @csrf
                <div id="bulkApproveIds"></div>

                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Bulk Approve Money Requests</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            Selected verified requests will be approved together using one payment mode. Different sub
                            accounts are allowed.
                        </div>

                        <div class="table-responsive" style="margin-bottom:12px;">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Request No</th>
                                        <th style="text-align:right;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="bulkApproveSelectedRows">
                                    <tr>
                                        <td colspan="2" class="text-center">No selected requests.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Payment Mode <span class="text-danger">*</span></label>
                                <select name="Payment_mode" id="bulkPaymentMode" class="form-control"
                                    onchange="bulkChequeVisibility()" required>
                                    <option value="">-- Select --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4 bulk-cheque" style="display:none;">
                                <label>Bank Name</label>
                                <input type="text" name="bank_name" class="form-control">
                            </div>

                            <div class="form-group col-md-4 bulk-cheque" style="display:none;">
                                <label>Cheque Bank Account</label>
                                <input type="text" name="cheque_bank_account" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Approval Comment</label>
                            <textarea name="approval_comment" class="form-control" rows="4">Okay</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Approve Selected</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- BULK CASH OUT MODAL --}}
    <div class="modal fade" id="bulkCashOutModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" action="{{ route('moneyrequest.bulkcashout') }}" id="bulkCashOutForm">
                @csrf
                <div id="bulkCashOutIds"></div>

                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Bulk Cash Out Money Requests</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            Selected approved requests will be cashed out together using one voucher number. Different sub
                            accounts are allowed.
                        </div>

                        <div class="table-responsive" style="margin-bottom:12px;">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Request No</th>
                                        <th style="text-align:right;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="bulkCashOutSelectedRows">
                                    <tr>
                                        <td colspan="2" class="text-center">No selected requests.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group">
                            <label>Voucher No <span class="text-danger">*</span></label>
                            <input type="text" name="payment_vocher_no" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Cashier Comment</label>
                            <textarea name="cashier_comment" class="form-control" rows="4">Okay</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cash Out Selected</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function selectedMoneyRows(status) {
                var rows = [];

                $('.money-check:checked').each(function() {
                    var rowStatus = String($(this).attr('data-status') || '');

                    if (rowStatus === status) {
                        rows.push({
                            id: $(this).val(),
                            request_no: $(this).attr('data-request-no') || '-',
                            amount: parseFloat($(this).attr('data-amount') || 0)
                        });
                    }
                });

                return rows;
            }

            function moneyFormat(n) {
                n = parseFloat(n || 0);
                if (isNaN(n)) n = 0;
                return n.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function buildSelectedRowsHtml(rows) {
                var html = '';
                var total = 0;

                rows.forEach(function(row) {
                    total += row.amount;
                    html += '<tr>' +
                        '<td>' + $('<div>').text(row.request_no).html() + '</td>' +
                        '<td style="text-align:right;">' + moneyFormat(row.amount) + '</td>' +
                        '</tr>';
                });

                html += '<tr>' +
                    '<th style="text-align:right;">Total</th>' +
                    '<th style="text-align:right;">' + moneyFormat(total) + '</th>' +
                    '</tr>';

                return html;
            }

            $('#checkAllMoney').on('change', function() {
                $('.money-check').prop('checked', $(this).prop('checked'));
            });

            window.openBulkApproveModal = function() {
                var rows = selectedMoneyRows('Verified');

                if (rows.length === 0) {
                    alert('Please select verified requests only.');
                    return;
                }

                $('#bulkApproveIds').html('');

                rows.forEach(function(row) {
                    $('#bulkApproveIds').append(
                        '<input type="hidden" name="money_ids[]" value="' + row.id + '">'
                    );
                });

                $('#bulkApproveSelectedRows').html(buildSelectedRowsHtml(rows));
                $('#bulkApproveForm')[0].reset();
                $('#bulkApproveForm textarea[name="approval_comment"]').val('Okay');
                $('.bulk-cheque').hide();
                $('#bulkApproveModal').modal('show');
            };

            window.openBulkCashOutModal = function() {
                var rows = selectedMoneyRows('Approved');

                if (rows.length === 0) {
                    alert('Please select approved requests only.');
                    return;
                }

                $('#bulkCashOutIds').html('');

                rows.forEach(function(row) {
                    $('#bulkCashOutIds').append(
                        '<input type="hidden" name="money_ids[]" value="' + row.id + '">'
                    );
                });

                $('#bulkCashOutSelectedRows').html(buildSelectedRowsHtml(rows));
                $('#bulkCashOutForm')[0].reset();
                $('#bulkCashOutForm textarea[name="cashier_comment"]').val('Okay');
                $('#bulkCashOutModal').modal('show');
            };

            window.bulkChequeVisibility = function() {
                if ($('#bulkPaymentMode').val() === 'Cheque') {
                    $('.bulk-cheque').show();
                } else {
                    $('.bulk-cheque').hide();
                }
            };


            var companyOptions = @json($companyOptions);
            var departmentOptions = @json($departmentOptions);
            var workPointOptions = @json($workPointOptions);
            var subAccountOptions = @json($subAccountOptions);

            function initSelect2WithParent($el, parentSelector) {
                if (!$el || !$el.length) return;
                if ($el.data('select2')) {
                    try {
                        $el.select2('destroy');
                    } catch (e) {}
                }
                var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
                $el.select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownParent: $parent
                });
            }

            function initAllModalSelects(modalSelector) {
                $(modalSelector).find('.select2_modal').each(function() {
                    initSelect2WithParent($(this), modalSelector);
                });
            }

            $('.select2_modal').each(function() {
                var $this = $(this);
                if ($this.closest('#moneyCreateModal').length) {
                    initSelect2WithParent($this, '#moneyCreateModal');
                    return;
                }
                if ($this.closest('#moneyEditModal').length) {
                    initSelect2WithParent($this, '#moneyEditModal');
                    return;
                }
                if ($this.closest('#approveModal').length) {
                    initSelect2WithParent($this, '#approveModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#moneyCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#moneyCreateModal');
            });

            $('#moneyEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#moneyEditModal');
            });

            $('#approveModal').on('shown.bs.modal', function() {
                initAllModalSelects('#approveModal');
                setChequeVisibility();
            });

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatMoney(value) {
                var n = parseFloat(value || 0);
                if (isNaN(n)) n = 0;
                return n.toFixed(2);
            }

            function setSelectHtml(selector, placeholder, html) {
                $(selector).html('<option value="">' + placeholder + '</option>' + (html || '')).trigger('change');
            }

            function clearMoneyOrganizationAfter(level, prefix) {
                if (level <= 1) setSelectHtml('#' + prefix + '_company_unit_id', '-- Select business unit --');
                if (level <= 2) setSelectHtml('#' + prefix + '_work_point_id', '-- Select working point --');
            }

            function loadMoneyUnits(companyId, prefix, selectedId = null, callback = null) {
                clearMoneyOrganizationAfter(1, prefix);
                if (!companyId) {
                    if (callback) callback();
                    return;
                }

                $('#' + prefix + '_company_unit_id').html('<option value="">Loading...</option>').trigger('change');

                $.get("{{ url('/admin/reqsts/general-supply/ajax/company-units') }}/" + companyId, function(res) {
                    var html = '';
                    res.forEach(function(row) {
                        var text = $.trim((row.unit_code || '') + ' - ' + row.unit_name).replace(
                            /^ - | - $/g, '');
                        var selected = String(row.id) === String(selectedId) ? ' selected' : '';
                        html += '<option value="' + row.id + '"' + selected + '>' + escapeHtml(
                            text) + '</option>';
                    });
                    setSelectHtml('#' + prefix + '_company_unit_id', '-- Select business unit --', html);
                    if (callback) callback();
                });
            }

            function loadMoneyWorkPoints(unitId, prefix, selectedId = null, callback = null) {
                clearMoneyOrganizationAfter(2, prefix);
                if (!unitId) {
                    if (callback) callback();
                    return;
                }

                $('#' + prefix + '_work_point_id').html('<option value="">Loading...</option>').trigger('change');

                $.get("{{ url('/admin/reqsts/general-supply/ajax/work-points') }}/" + unitId, function(res) {
                    var html = '';
                    res.forEach(function(row) {
                        var text = $.trim((row.work_code || '') + ' - ' + row.work_name).replace(
                            /^ - | - $/g, '');
                        var selected = String(row.id) === String(selectedId) ? ' selected' : '';
                        html += '<option value="' + row.id + '"' + selected + '>' + escapeHtml(
                            text) + '</option>';
                    });
                    setSelectHtml('#' + prefix + '_work_point_id', '-- Select working point --', html);
                    if (callback) callback();
                });
            }

            function loadMoneySections(departmentId, prefix, selectedId = null, callback = null) {
                setSelectHtml('#' + prefix + '_section_id', '-- Select section --');
                if (!departmentId) {
                    if (callback) callback();
                    return;
                }

                $('#' + prefix + '_section_id').html('<option value="">Loading...</option>').trigger('change');

                $.get("{{ url('/admin/reqsts/general-supply/ajax/sections') }}/" + departmentId, function(res) {
                    var html = '';
                    res.forEach(function(row) {
                        var text = $.trim((row.secCode || '') + ' - ' + row.secName).replace(
                            /^ - | - $/g, '');
                        var selected = String(row.id) === String(selectedId) ? ' selected' : '';
                        html += '<option value="' + row.id + '"' + selected + '>' + escapeHtml(
                            text) + '</option>';
                    });
                    setSelectHtml('#' + prefix + '_section_id', '-- Select section --', html);
                    if (callback) callback();
                });
            }

            function summaryHtml(row) {
                var html = '';
                html += '<div class="row">';
                html += '<div class="col-md-6">';
                html += '<div><strong>Reference No:</strong> ' + escapeHtml(row.request_no) + '</div>';
                html += '<div><strong>Request Date:</strong> ' + escapeHtml(row.request_date) + '</div>';
                html += '<div><strong>Working Point:</strong> ' + escapeHtml(row.work_point_name || '-') + '</div>';
                html += '<div><strong>Company:</strong> ' + escapeHtml(row.company_name || '-') + '</div>';
                html += '<div><strong>Unit:</strong> ' + escapeHtml(row.unit_name || '-') + '</div>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<div><strong>Details:</strong> ' + escapeHtml(row.details || '-') + '</div>';
                html += '<div><strong>Amount:</strong> ' + escapeHtml(formatMoney(row.amount)) + '</div>';
                html += '<div><strong>Purpose:</strong> ' + escapeHtml(row.purpose || '-') + '</div>';
                html += '<div><strong>Status:</strong> ' + escapeHtml(row.status || '-') + '</div>';
                html += '<div><strong>Requested By:</strong> ' + escapeHtml(row.requested_by || '-') + '</div>';
                html += '</div>';
                html += '</div>';
                return html;
            }

            window.openCreateModal = function() {
                $('#moneyCreateModal').modal('show');
            };

            $('#money_company_id').on('change', function() {
                loadMoneyUnits($(this).val(), 'money');
            });

            $('#money_company_unit_id').on('change', function() {
                loadMoneyWorkPoints($(this).val(), 'money');
            });

            $('#money_department_id').on('change', function() {
                loadMoneySections($(this).val(), 'money');
            });

            window.openEdit = function(row) {
                $('#moneyEditBody').html('<div class="text-center">Loading...</div>');
                $('#moneyEditModal').modal('show');

                $.get("{{ url('/admin/moneyrequest/edit') }}/" + encodeURIComponent(row.id))
                    .done(function(resp) {
                        var m = resp.money;
                        var html = '';

                        if ({{ $canEditWorkPoint ? 'true' : 'false' }}) {
                            html += '<div class="form-row">';

                            html += '<div class="form-group col-md-4">';
                            html += '<label>Company <span class="text-danger">*</span></label>';
                            html +=
                                '<select name="company_id" id="edit_money_company_id" class="form-control select2_modal" required>';
                            html += '<option value="">-- Select company --</option>';
                            for (var c = 0; c < companyOptions.length; c++) {
                                html += '<option value="' + companyOptions[c].id + '"' + (String(row
                                        .company_id) === String(companyOptions[c].id) ? ' selected' :
                                    '') + '>' + escapeHtml(companyOptions[c].name) + '</option>';
                            }
                            html += '</select>';
                            html += '</div>';

                            html += '<div class="form-group col-md-4">';
                            html += '<label>Business Unit <span class="text-danger">*</span></label>';
                            html +=
                                '<select name="company_unit_id" id="edit_money_company_unit_id" class="form-control select2_modal" required>';
                            html += '<option value="">-- Select business unit --</option>';
                            html += '</select>';
                            html += '</div>';

                            html += '<div class="form-group col-md-4">';
                            html += '<label>Working Point <span class="text-danger">*</span></label>';
                            html +=
                                '<select name="work_point_id" id="edit_money_work_point_id" class="form-control select2_modal" required>';
                            html += '<option value="">-- Select working point --</option>';
                            html += '</select>';
                            html += '</div>';

                            html += '</div>';
                        }

                        html += '<div class="form-row">';

                        html += '<div class="form-group col-md-4">';
                        html += '<label>Department <span class="text-danger">*</span></label>';
                        html +=
                            '<select name="department_id" id="edit_money_department_id" class="form-control select2_modal" required>';
                        html += '<option value="">-- Select department --</option>';
                        for (var d = 0; d < departmentOptions.length; d++) {
                            html += '<option value="' + departmentOptions[d].id + '"' + (String(row
                                    .department_id) === String(departmentOptions[d].id) ? ' selected' :
                                '') + '>' + escapeHtml(departmentOptions[d].name) + '</option>';
                        }
                        html += '</select>';
                        html += '</div>';

                        html += '<div class="form-group col-md-4">';
                        html += '<label>Section <span class="text-danger">*</span></label>';
                        html +=
                            '<select name="section_id" id="edit_money_section_id" class="form-control select2_modal" required>';
                        html += '<option value="">-- Select section --</option>';
                        html += '</select>';
                        html += '</div>';

                        html += '<div class="form-group col-md-4">';
                        html += '<label>Request Date <span class="text-danger">*</span></label>';
                        html += '<input type="date" name="RequestDate" class="form-control" value="' +
                            escapeHtml(m.RequestDate || '') + '" required>';
                        html += '</div>';

                        html += '</div>';

                        html += '<div class="form-row">';
                        html +=
                            '<div class="form-group col-md-4"><label>Payee Name <span class="text-danger">*</span></label><input type="text" name="PayeeName" class="form-control" value="' +
                            escapeHtml(m.PayeeName || '') + '" required></div>';
                        html +=
                            '<div class="form-group col-md-4"><label>Payee Contact</label><input type="text" name="PayeeContact" class="form-control" value="' +
                            escapeHtml(m.PayeeContact || '') + '"></div>';
                        html +=
                            '<div class="form-group col-md-4"><label>Amount <span class="text-danger">*</span></label><input type="number" step="0.01" name="total_amount" class="form-control" value="' +
                            escapeHtml(m.total_amount || 0) + '" required></div>';
                        html += '</div>';

                        html += '<div class="form-row">';
                        html +=
                            '<div class="form-group col-md-12"><label>Sub Account <span class="text-danger">*</span></label><select name="sub_account_id" class="form-control select2_modal" required><option value="">-- Select sub account --</option>';
                        for (var s = 0; s < subAccountOptions.length; s++) {
                            html += '<option value="' + subAccountOptions[s].id + '"' + (String(m
                                    .sub_account_id) === String(subAccountOptions[s].id) ? ' selected' :
                                '') + '>' + escapeHtml(subAccountOptions[s].name) + '</option>';
                        }
                        html += '</select></div>';
                        html += '</div>';

                        html += '<div class="form-row">';
                        html +=
                            '<div class="form-group col-md-6"><label>Details</label><textarea name="Description" class="form-control" rows="4">' +
                            escapeHtml(m.Description || '') + '</textarea></div>';
                        html +=
                            '<div class="form-group col-md-6"><label>Purpose</label><textarea name="remarks" class="form-control" rows="4">' +
                            escapeHtml(m.remarks || '') + '</textarea></div>';
                        html += '</div>';

                        $('#moneyEditBody').html(html);
                        $('#moneyEditForm').attr('action', "{{ url('/admin/moneyrequest') }}/" +
                            encodeURIComponent(row.id));
                        initAllModalSelects('#moneyEditModal');

                        if ({{ $canEditWorkPoint ? 'true' : 'false' }} && row.company_id) {
                            loadMoneyUnits(row.company_id, 'edit_money', row.company_unit_id, function() {
                                if (row.company_unit_id) {
                                    loadMoneyWorkPoints(row.company_unit_id, 'edit_money', m
                                        .work_point_id);
                                }
                            });
                        }

                        if (row.department_id) {
                            loadMoneySections(row.department_id, 'edit_money', m.section_id);
                        }

                        $(document).off('change', '#edit_money_company_id').on('change',
                            '#edit_money_company_id',
                            function() {
                                loadMoneyUnits($(this).val(), 'edit_money');
                            });

                        $(document).off('change', '#edit_money_company_unit_id').on('change',
                            '#edit_money_company_unit_id',
                            function() {
                                loadMoneyWorkPoints($(this).val(), 'edit_money');
                            });

                        $(document).off('change', '#edit_money_department_id').on('change',
                            '#edit_money_department_id',
                            function() {
                                loadMoneySections($(this).val(), 'edit_money');
                            });
                    })
                    .fail(function() {
                        $('#moneyEditBody').html('<div class="text-danger">Failed to load.</div>');
                    });
            };

            window.deleteMoney = function(row) {
                if (confirm('Delete this money request?')) {
                    window.location.href = "{{ url('/admin/moneyrequest/remove') }}/" + encodeURIComponent(row
                        .id);
                }
            };

            window.openVerify = function(row) {
                if (row.status !== 'Pending') {
                    alert('Only pending requests can be verified.');
                    return;
                }
                $('#verifySummary').html(summaryHtml(row));
                $('#verified_comment').val(row.verified_comment || '');
                $('#verifyDecision').val('verified');
                $('#verifyForm').attr('action', "{{ url('/admin/moneyrequest/verify') }}/" +
                    encodeURIComponent(row.id));
                $('#verifyModal').modal('show');
            };

            window.submitVerify = function(decision) {

                $('#verifyDecision').val(decision);
                document.getElementById('verifyForm').submit();

            };

            window.openApprove = function(row) {
                if (row.status !== 'Verified') {
                    alert('Only verified requests can be approved.');
                    return;
                }
                $('#approveSummary').html(summaryHtml(row));
                $('#approved_amount').val(row.amount || '');
                $('#Payment_mode').val('');
                $('#approval_comment_text').val(row.approval_comment || '');
                $('#bank_name').val('');
                $('#cheque_bank_account_input').val('');
                $('#approveDecision').val('approved');
                $('#approveForm').attr('action', "{{ url('/admin/moneyrequest/approve') }}/" +
                    encodeURIComponent(row.id));
                $('#approveModal').modal('show');
                setChequeVisibility();
            };

            window.setChequeVisibility = function() {
                var mode = $('#Payment_mode').val();
                if (mode === 'Cheque') {
                    $('#chequeFields').show();
                    $('#chequeAccountFields').show();
                } else {
                    $('#chequeFields').hide();
                    $('#chequeAccountFields').hide();
                    $('#bank_name').val('');
                    $('#cheque_bank_account_input').val('');
                }
            };

            window.submitApprove = function(decision) {
                var amount = $('#approved_amount').val();
                var mode = $('#Payment_mode').val();
                var commentText = $('#approval_comment_text').val();
                var bankName = $('#bank_name').val();
                var accountNo = $('#cheque_bank_account_input').val();

              /*   if (!commentText || !commentText.trim()) {
                    alert('Comment is required.');
                    return;
                }
 */
                if (decision === 'approved') {
                    if (!amount) {
                        alert('Approved amount is required.');
                        return;
                    }
                    if (!mode) {
                        alert('Payment mode is required.');
                        return;
                    }
                    if (mode === 'Cheque') {
                        if (!bankName || !bankName.trim()) {
                            alert('Bank name is required for cheque payment.');
                            return;
                        }
                        if (!accountNo || !accountNo.trim()) {
                            alert('Cheque bank account is required for cheque payment.');
                            return;
                        }
                    }
                }

                $('#approveDecision').val(decision);
                $('#approveForm')[0].submit();
            };

            window.openCashOut = function(row) {
                if (row.status !== 'Approved') {
                    alert('Only approved requests can be cashed out.');
                    return;
                }
                $('#cashSummary').html(summaryHtml(row));
                $('#cashForm')[0].reset();
                $('#cashForm').attr('action', "{{ url('/admin/moneyrequest/cashout') }}/" + encodeURIComponent(
                    row.id));
                $('#cashModal').modal('show');
            };

            window.openRetire = function(row) {
                if (row.status !== 'Cashed-out') {
                    alert('Only cashed-out requests can be retired.');
                    return;
                }
                $('#retireSummary').html(summaryHtml(row));
                $('#retireForm')[0].reset();
                $('#retireForm').attr('action', "{{ url('/admin/moneyrequest/retire') }}/" +
                    encodeURIComponent(row.id));
                $('#retireModal').modal('show');
            };

            $(document).on('click', '.btn-verify-money', function() {
                var row = JSON.parse($(this).attr('data-row'));
                openVerify(row);
            });

            $(document).on('click', '.btn-approve-money', function() {
                var row = JSON.parse($(this).attr('data-row'));
                openApprove(row);
            });

            $(document).on('click', '.btn-cashout-money', function() {
                var row = JSON.parse($(this).attr('data-row'));
                openCashOut(row);
            });

            $(document).on('click', '.btn-retire-money', function() {
                var row = JSON.parse($(this).attr('data-row'));
                openRetire(row);
            });

            $('#cashSubmitBtn').on('click', function() {
                var voucher = $('#cashForm').find('input[name="payment_vocher_no"]').val();
                if (!voucher || !voucher.trim()) {
                    alert('Voucher number is required.');
                    return;
                }
                $('#cashForm')[0].submit();
            });

            $('#retireSubmitBtn').on('click', function() {
                $('#retireForm')[0].submit();
            });
        });
    </script>
@endsection
