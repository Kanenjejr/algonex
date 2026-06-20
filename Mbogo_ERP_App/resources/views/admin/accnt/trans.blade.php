@extends('layouts.AdminMaster')

@section('content')
    @php
        use Carbon\Carbon;

        $canSelectWorkPoint =
            auth()->user()->can('View-All-Accounting-Transactions') ||
            in_array(
                auth()->user()->role,
                [
                    'Admin',
                    'CEO',
                    'Managing Director (MD)',
                    'Accountant Director (DAF)',
                    'Chief Accountant',
                    'Admin-Developer',
                ],
                true,
            );

        $currencyOptions = ['TZS', 'USD', 'KES', 'UGX', 'RWF', 'EUR', 'GBP'];

        $fmt = function ($value) {
            return is_numeric($value) ? number_format((float) $value, 2) : '-';
        };

        $moneyDisplay = function ($value) {
            return $value === null || $value === '' ? '-' : number_format((float) $value, 2);
        };

        $pick = function ($source, array $paths, $default = '-') {
            foreach ($paths as $path) {
                $value = data_get($source, $path);
                if (filled($value)) {
                    return $value;
                }
            }
            return $default;
        };
    @endphp

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Accounting Transactions Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Accounting Transactions</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>{{ Carbon::now()->format('l') }} , {{ Carbon::now()->toDateString() }}</strong>
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

    <script>
        function timedMsg() {
            setInterval(change_time, 1000);
        }

        function change_time() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }
        timedMsg();
    </script>

    <div class="col-12">
        <h3 class="mb-2 page-title">Account Transactions</h3>

        @can('Import-Accounting-Transactions')
            <button style="position: absolute; top: 4.5%; right: 13%;" type="button" class="btn mb-2 btn-success"
                data-toggle="modal" data-target="#txImportModal">
                Upload Excel
            </button>
        @endcan

        @can('Register-Accounting-Transactions')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary"
                data-toggle="modal" data-target="#txCreateModal">
                Add Transaction
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight mt-5">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-success">
                        <h5>Transactions</h5>
                    </div>

                    <div class="ibox-content">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example"
                                id="accntTransactionsTable" style="white-space:nowrap;">
                                <thead>
                                    <tr>
                                        <th>Posting Date</th>
                                        <th>P.V.NO.</th>
                                        <th>CHQ N0.</th>
                                        <th>Cash/Bank</th>
                                        <th>PAYEE</th>
                                        <th>Sub Account Code</th>
                                        <th>Sub Account Description</th>
                                        <th>Account Code</th>
                                        <th>Account Description</th>
                                        <th>Section Code</th>
                                        <th>Section description</th>
                                        <th>Department Code</th>
                                        <th>Department Description</th>
                                        <th>Location code</th>
                                        <th>Location Description</th>
                                        <th>Business Code</th>
                                        <th>Business Description</th>
                                        <th>Company Code</th>
                                        <th>Company Description</th>
                                        <th>Currency</th>
                                        <th>Source Amount</th>
                                        <th>Exchange Rate</th>
                                        <th>Details</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse(($groups ?? []) as $g)
                                        @php
                                            $groupEnc = $pick($g, ['group_enc', 'transaction_group'], '');
                                            $pcvNo = $pick($g, ['pcv_no'], '-');
                                            $checkNo = $pick($g, ['check_no'], '-');
                                            $memo = $pick($g, ['memo'], '-');
                                            $currency = $pick($g, ['currency'], '-');
                                            $exchangeRate = $pick($g, ['exchange_rate'], 1);
                                            $sourceAmount = $pick($g, ['source_amount'], null);
                                            $amount = $pick($g, ['amount'], 0);
                                            $verified = $pick($g, ['verified'], 'pending');
                                            $approved = $pick($g, ['approved'], 'pending');
                                            $transDateRaw = $pick($g, ['trans_date'], null);
                                            $payee = $pick($g, ['payee'], '-');
                                            $category = $pick($g, ['category'], '-');

                                            $companyCode = $pick(
                                                $g,
                                                ['work_point.company.company_code', 'workpoint.company.company_code'],
                                                '-',
                                            );
                                            $companyDesc = $pick(
                                                $g,
                                                ['work_point.company.company_name', 'workpoint.company.company_name'],
                                                '-',
                                            );
                                            $locationCode = $pick(
                                                $g,
                                                ['work_point.work_code', 'workpoint.work_code'],
                                                '-',
                                            );
                                            $locationDesc = $pick(
                                                $g,
                                                ['work_point.work_name', 'workpoint.work_name'],
                                                '-',
                                            );
                                            $businessCode = $pick(
                                                $g,
                                                ['work_point.comp_unit.unit_code', 'workpoint.comp_unit.unit_code'],
                                                '-',
                                            );
                                            $businessDesc = $pick(
                                                $g,
                                                ['work_point.comp_unit.unit_name', 'workpoint.comp_unit.unit_name'],
                                                '-',
                                            );
                                            $sectionCode = $pick($g, ['section.secCode'], '-');
                                            $sectionDesc = $pick($g, ['section.secName'], '-');
                                            $departmentCode = $pick($g, ['department.depCode'], '-');
                                            $departmentDesc = $pick($g, ['department.depName'], '-');

                                            $payload = [
                                                'group_enc' => $groupEnc,
                                                'pcv_no' => $pcvNo,
                                                'trans_date' => $transDateRaw
                                                    ? Carbon::parse($transDateRaw)->format('Y-m-d')
                                                    : '',
                                                'request_no' => $pick($g, ['request_no'], ''),
                                                'requisition_id' => $pick($g, ['requisition_id'], ''),
                                                'check_no' => $checkNo === '-' ? '' : $checkNo,
                                                'memo' => $memo === '-' ? '' : $memo,
                                                'currency' => $currency === '-' ? '' : $currency,
                                                'payee' => $payee === '-' ? '' : $payee,
                                                'exchange_rate' => $exchangeRate,
                                                'source_amount' => $sourceAmount,
                                                'amount' => $amount,
                                                'work_point_id' => $pick(
                                                    $g,
                                                    ['work_point_id', 'workpoint.id', 'work_point.id'],
                                                    '',
                                                ),
                                                'department_id' => $pick($g, ['department_id', 'department.id'], ''),
                                                'section_id' => $pick($g, ['section_id', 'section.id'], ''),
                                                'debit_account' => $pick($g, ['debit_subaccount_id'], '')
                                                    ? 's_' . $g->debit_subaccount_id
                                                    : '',
                                                'credit_account' => $pick($g, ['credit_subaccount_id'], '')
                                                    ? 's_' . $g->credit_subaccount_id
                                                    : '',
                                            ];

                                            $lines = [
                                                [
                                                    'kind' => 'debit',
                                                    'sub_code' => $pick($g, ['debit_sub_accounting_code_8'], '-'),
                                                    'sub_desc' => $pick($g, ['debit_sub_accounting_name_8'], '-'),
                                                    'acc_code' => $pick($g, ['debit_accounting_code_6'], '-'),
                                                    'acc_desc' => $pick($g, ['debit_accounting_name_6'], '-'),
                                                    'debit' => $amount,
                                                    'credit' => null,
                                                ],
                                                [
                                                    'kind' => 'credit',
                                                    'sub_code' => $pick($g, ['credit_sub_accounting_code_8'], '-'),
                                                    'sub_desc' => $pick($g, ['credit_sub_accounting_name_8'], '-'),
                                                    'acc_code' => $pick($g, ['credit_accounting_code_6'], '-'),
                                                    'acc_desc' => $pick($g, ['credit_accounting_name_6'], '-'),
                                                    'debit' => null,
                                                    'credit' => $amount,
                                                ],
                                            ];

                                            $isLocked = $approved === 'approved';
                                        @endphp

                                        @foreach ($lines as $lineIndex => $line)
                                            <tr class="{{ $lineIndex === 0 ? 'table-light' : '' }}">
                                                <td>{{ $transDateRaw ? Carbon::parse($transDateRaw)->format('d.m.Y') : '-' }}
                                                </td>
                                                <td>{{ $pcvNo }}</td>
                                                <td>{{ $checkNo }}</td>
                                                <td>{{ $category }}</td>
                                                <td>{{ $payee }}</td>

                                                <td>{{ $line['sub_code'] }}</td>
                                                <td>{{ $line['sub_desc'] }}</td>

                                                <td>{{ $line['acc_code'] }}</td>
                                                <td>{{ $line['acc_desc'] }}</td>

                                                <td>{{ $sectionCode }}</td>
                                                <td>{{ $sectionDesc }}</td>

                                                <td>{{ $departmentCode }}</td>
                                                <td>{{ $departmentDesc }}</td>

                                                <td>{{ $locationCode }}</td>
                                                <td>{{ $locationDesc }}</td>

                                                <td>{{ $businessCode }}</td>
                                                <td>{{ $businessDesc }}</td>

                                                <td>{{ $companyCode }}</td>
                                                <td>{{ $companyDesc }}</td>
                                                <td>{{ $currency ?: '-' }}</td>
                                                <td>{{ $moneyDisplay($sourceAmount) }}</td>
                                                <td>{{ $fmt($exchangeRate) }}</td>
                                                <td>
                                                    {{ $memo }}
                                                    <div class="mt-1">
                                                        <span class="badge badge-info">{{ ucfirst($line['kind']) }}</span>
                                                    </div>
                                                </td>

                                                <td>{{ $line['debit'] !== null ? $fmt($line['debit']) : '' }}</td>
                                                <td>{{ $line['credit'] !== null ? $fmt($line['credit']) : '' }}</td>

                                                <td>
                                                    <span class="badge badge-warning d-block mb-1">Verified:
                                                        {{ ucfirst($verified) }}</span>
                                                    <span class="badge badge-success d-block">Approved:
                                                        {{ ucfirst($approved) }}</span>
                                                    @if ($isLocked)
                                                        <span class="badge badge-dark d-block mt-1">Locked</span>
                                                    @endif
                                                </td>

                                                <td style="white-space:nowrap;">
                                                    @if ($lineIndex === 0)
                                                        @can('Edit-Accounting-Transactions')
                                                            @if ($verified === 'pending' || ($verified === 'verified' && $approved === 'pending'))
                                                                <button type="button" class="btn btn-sm btn-warning mb-1"
                                                                    onclick='openEditTx(@json($payload))'>
                                                                    Edit
                                                                </button>
                                                            @endif
                                                        @endcan

                                                        @can('Delete-Accounting-Transactions')
                                                            @if ($verified === 'pending' && $approved !== 'approved')
                                                                <a href="javascript:void(0);"
                                                                    class="btn btn-sm btn-danger mb-1 btn-delete-tx"
                                                                    data-group="{{ $groupEnc }}">
                                                                    Remove
                                                                </a>
                                                            @endif
                                                        @endcan

                                                        @can('Verify-Accounting-Transactions')
                                                            @if ($verified === 'pending')
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary mb-1 btn-status-tx"
                                                                    data-title="Verify Transaction"
                                                                    data-url="{{ route('accnttransactions.verify', $groupEnc) }}"
                                                                    data-placeholder="Write verification comment...">
                                                                    Verify
                                                                </button>
                                                            @endif
                                                        @endcan

                                                        @can('Approve-Accounting-Transactions')
                                                            @if ($verified === 'verified' && $approved === 'pending')
                                                                <button type="button"
                                                                    class="btn btn-sm btn-success mb-1 btn-status-tx"
                                                                    data-title="Approve Transaction"
                                                                    data-url="{{ route('accnttransactions.approve', $groupEnc) }}"
                                                                    data-placeholder="Write approval comment...">
                                                                    Approve
                                                                </button>
                                                            @endif
                                                        @endcan
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="27" class="text-center text-muted">No transactions found.</td>
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

    <div class="modal fade" id="txImportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('accnttransactions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Cashbook Excel</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:13px;">
                            Upload the Excel with columns for posting date, PV no, check no, payee, sub account code,
                            section code, location code, business code, currency, exchange rate, details, source amount,
                            condition, and amount.
                        </p>
                        <div class="form-group">
                            <label>Excel file <span class="text-danger">*</span></label>
                            <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="txCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="txCreateForm" action="{{ route('accnttransactions.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Accounting Transaction Entry</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-row">
                            @if ($canSelectWorkPoint)
                                <div class="form-group col-md-6">
                                    <label>Work Point <span class="text-danger">*</span></label>
                                    <select name="work_point_id" id="create_work_point_id"
                                        class="form-control select2_modal" required>
                                        <option value="">-- Select work point --</option>
                                        @foreach ($workPoints ?? [] as $wp)
                                            <option value="{{ $wp->id }}">{{ $wp->work_code }} -
                                                {{ $wp->work_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
                            @endif

                            <div class="form-group col-md-6">
                                <label>Approved Requisition (optional)</label>
                                <select name="requisition_id" id="create_requisition_id"
                                    class="form-control select2_modal">
                                    <option value="">-- No requisition --</option>
                                    @foreach ($approvedRequisitions ?? [] as $r)
                                        <option value="{{ $r->id }}"
                                            data-amount="{{ (float) ($r->approved_amount ?: $r->total_amount) }}"
                                            data-workpoint="{{ $r->work_point_id }}"
                                            data-requestno="{{ $r->RequestNo }}"
                                            data-subaccount="{{ $r->sub_account_id ? 's_' . $r->sub_account_id : '' }}"
                                            data-section="{{ $r->section_id }}" data-payee="{{ $r->PayeeName }}"
                                            data-memo="{{ $r->Description }}">
                                            {{ $r->RequestNo }} - {{ optional($r->workpoint)->work_code }} -
                                            {{ optional($r->workpoint)->work_name }} -
                                            {{ number_format((float) ($r->approved_amount ?: $r->total_amount), 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">If requisition is selected, amount, debit account, section, payee
                                    and
                                    details will be pulled automatically from the approved requisition.</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" name="trans_date" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Currency <span class="text-danger">*</span></label>
                                <select name="currency" id="create_currency" class="form-control select2_modal" required>
                                    <option value="">-- select currency --</option>
                                    @foreach ($currencyOptions as $ccy)
                                        <option value="{{ $ccy }}">{{ $ccy }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Exchange Rate <span class="text-danger">*</span></label>
                                <input type="number" name="exchange_rate" id="create_exchange_rate"
                                    class="form-control js-rate" step="0.000001" min="0.000001" value="1" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Source Amount <span class="text-danger">*</span></label>
                                <input type="number" name="source_amount" id="create_source_amount"
                                    class="form-control js-source-amount" step="0.01" min="0.01" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Converted Amount</label>
                                <input type="number" name="amount" id="create_amount" class="form-control"
                                    step="0.01" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Cheque No</label>
                                <input type="text" name="check_no" id="create_check_no" class="form-control"
                                    placeholder="Leave blank for Cash">
                                <small class="text-muted">Blank cheque no means Cash. Filled cheque no means Bank.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Debit Sub Account <span class="text-danger">*</span></label>
                                <select name="debit_account" id="create_debit_account" class="form-control select2_modal"
                                    required>
                                    <option value="">-- select debit sub account --</option>
                                    @foreach ($subcharts ?? [] as $sub)
                                        <option value="s_{{ $sub->id }}">{{ $sub->SubCode }} -
                                            {{ $sub->SubDescription ?? '' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">When requisition is selected, this field is pulled from
                                    requisition.</small>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Credit Sub Account <span class="text-danger">*</span></label>
                                <select name="credit_account" id="create_credit_account"
                                    class="form-control select2_modal" required>
                                    <option value="">-- select credit sub account --</option>
                                    @foreach ($subcharts ?? [] as $sub)
                                        <option value="s_{{ $sub->id }}">{{ $sub->SubCode }} -
                                            {{ $sub->SubDescription ?? '' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Choose the cash/bank or other credit account here.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Section <span class="text-danger">*</span></label>
                                <select name="section_id" id="create_section_id" class="form-control select2_modal"
                                    required>
                                    <option value="">-- select section --</option>
                                    @foreach ($sections ?? [] as $sec)
                                        <option value="{{ $sec->id }}">{{ $sec->secCode }} - {{ $sec->secName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Payee <span class="text-danger">*</span></label>
                                <input type="text" name="payee" id="create_payee" class="form-control" required
                                    placeholder="Enter Payee Name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Memo / Details</label>
                            <textarea name="memo" id="create_memo" class="form-control" rows="3"
                                placeholder="Enter transaction details..."></textarea>
                        </div>

                        <div class="alert alert-info mb-0">
                            Source Amount is entered by user or pulled from approved requisition. Converted Amount is
                            calculated using exchange rate.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Submit Transaction</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="txEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="txEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Accounting Transaction Entry</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="edit_group_enc" name="group_enc">

                        <div class="form-row">
                            @if ($canSelectWorkPoint)
                                <div class="form-group col-md-6">
                                    <label>Work Point <span class="text-danger">*</span></label>
                                    <select id="edit_work_point_id" name="work_point_id"
                                        class="form-control select2_modal" required>
                                        <option value="">-- Select work point --</option>
                                        @foreach ($workPoints ?? [] as $wp)
                                            <option value="{{ $wp->id }}">{{ $wp->work_code }} -
                                                {{ $wp->work_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" id="edit_work_point_id" name="work_point_id"
                                    value="{{ auth()->user()->work_point_id }}">
                            @endif

                            <div class="form-group col-md-6">
                                <label>Approved Requisition (optional)</label>
                                <select id="edit_requisition_id" name="requisition_id"
                                    class="form-control select2_modal">
                                    <option value="">-- No requisition --</option>
                                    @foreach ($approvedRequisitions ?? [] as $r)
                                        <option value="{{ $r->id }}"
                                            data-amount="{{ (float) ($r->approved_amount ?: $r->total_amount) }}"
                                            data-workpoint="{{ $r->work_point_id }}"
                                            data-requestno="{{ $r->RequestNo }}"
                                            data-subaccount="{{ $r->sub_account_id ? 's_' . $r->sub_account_id : '' }}"
                                            data-section="{{ $r->section_id }}" data-payee="{{ $r->PayeeName }}"
                                            data-memo="{{ $r->Description }}">
                                            {{ $r->RequestNo }} - {{ optional($r->workpoint)->work_code }} -
                                            {{ optional($r->workpoint)->work_name }} -
                                            {{ number_format((float) ($r->approved_amount ?: $r->total_amount), 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">If requisition is selected, amount, debit account, section, payee
                                    and
                                    details will be pulled automatically from the approved requisition.</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Transaction Date <span class="text-danger">*</span></label>
                                <input id="edit_trans_date" type="date" name="trans_date" class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Currency <span class="text-danger">*</span></label>
                                <select name="currency" id="edit_currency" class="form-control select2_modal" required>
                                    <option value="">-- select currency --</option>
                                    @foreach ($currencyOptions as $ccy)
                                        <option value="{{ $ccy }}">{{ $ccy }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Exchange Rate <span class="text-danger">*</span></label>
                                <input type="number" name="exchange_rate" id="edit_exchange_rate"
                                    class="form-control js-rate" step="0.000001" min="0.000001" value="1" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Source Amount <span class="text-danger">*</span></label>
                                <input type="number" name="source_amount" id="edit_source_amount"
                                    class="form-control js-source-amount" step="0.01" min="0.01" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Converted Amount</label>
                                <input type="number" name="amount" id="edit_amount" class="form-control"
                                    step="0.01" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Cheque No</label>
                                <input id="edit_check_no" type="text" name="check_no" class="form-control"
                                    placeholder="Leave blank for Cash">
                                <small class="text-muted">Blank cheque no means Cash. Filled cheque no means
                                    Bank.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Debit Sub Account <span class="text-danger">*</span></label>
                                <select id="edit_debit_account" name="debit_account" class="form-control select2_modal"
                                    required>
                                    <option value="">-- select debit sub account --</option>
                                    @foreach ($subcharts ?? [] as $sub)
                                        <option value="s_{{ $sub->id }}">{{ $sub->SubCode }} -
                                            {{ $sub->SubDescription ?? '' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">When requisition is selected, this field is pulled from
                                    requisition.</small>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Credit Sub Account <span class="text-danger">*</span></label>
                                <select id="edit_credit_account" name="credit_account" class="form-control select2_modal"
                                    required>
                                    <option value="">-- select credit sub account --</option>
                                    @foreach ($subcharts ?? [] as $sub)
                                        <option value="s_{{ $sub->id }}">{{ $sub->SubCode }} -
                                            {{ $sub->SubDescription ?? '' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Choose the cash/bank or other credit account here.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Section <span class="text-danger">*</span></label>
                                <select id="edit_section_id" name="section_id" class="form-control select2_modal"
                                    required>
                                    <option value="">-- select section --</option>
                                    @foreach ($sections ?? [] as $sec)
                                        <option value="{{ $sec->id }}">{{ $sec->secCode }} -
                                            {{ $sec->secName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Payee <span class="text-danger">*</span></label>
                                <input type="text" id="edit_payee" name="payee" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Memo / Details</label>
                            <textarea id="edit_memo" name="memo" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Update Transaction</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="statusCommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="statusCommentForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalTitle">Confirm</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2" id="statusModalHint">Write a comment then submit.</p>
                        <div class="form-group">
                            <label>Comment</label>
                            <textarea name="comment" id="statusModalComment" class="form-control" rows="4"
                                placeholder="Write your comment here..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="statusModalSubmit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            function initModalSelects(modalSelector) {
                $(modalSelector).find('.select2_modal').each(function() {
                    initSelect2WithParent($(this), modalSelector);
                });
            }

            $('.select2_modal').each(function() {
                var $this = $(this);

                if ($this.closest('#txCreateModal').length) {
                    initSelect2WithParent($this, '#txCreateModal');
                    return;
                }

                if ($this.closest('#txEditModal').length) {
                    initSelect2WithParent($this, '#txEditModal');
                    return;
                }

                if ($this.closest('#statusCommentModal').length) {
                    initSelect2WithParent($this, '#statusCommentModal');
                    return;
                }

                initSelect2WithParent($this, null);
            });

            $('#txCreateModal').on('shown.bs.modal', function() {
                initModalSelects('#txCreateModal');
            });

            $('#txEditModal').on('shown.bs.modal', function() {
                initModalSelects('#txEditModal');
            });

            function calcConverted(source, rate) {
                source = parseFloat(source || 0);
                rate = parseFloat(rate || 1);
                if (isNaN(source) || isNaN(rate)) return '';
                return (source * rate).toFixed(2);
            }

            function bindAutoCalc(prefix) {
                var $source = $('#' + prefix + '_source_amount');
                var $rate = $('#' + prefix + '_exchange_rate');
                var $amount = $('#' + prefix + '_amount');

                function refresh() {
                    $amount.val(calcConverted($source.val(), $rate.val()));
                }

                $source.on('input change', refresh);
                $rate.on('input change', refresh);
                refresh();
            }

            function setRequisitionMode(prefix, enabled, reqData) {
                var $debit = $('#' + prefix + '_debit_account');
                var $section = $('#' + prefix + '_section_id');
                var $payee = $('#' + prefix + '_payee');
                var $memo = $('#' + prefix + '_memo');
                var $source = $('#' + prefix + '_source_amount');

                if (enabled) {
                    if (reqData.amount) {
                        $source.val(parseFloat(reqData.amount).toFixed(2)).trigger('input');
                    }
                    if (reqData.subaccount) {
                        $debit.val(String(reqData.subaccount)).trigger('change');
                    }
                    if (reqData.section) {
                        $section.val(String(reqData.section)).trigger('change');
                    }
                    if (reqData.payee !== undefined) {
                        $payee.val(reqData.payee);
                    }
                    if (reqData.memo !== undefined) {
                        $memo.val(reqData.memo);
                    }

                    $debit.prop('disabled', true);
                    $section.prop('disabled', true);
                    $payee.prop('readonly', true);
                    $memo.prop('readonly', true);
                    $source.prop('readonly', true);
                } else {
                    $debit.prop('disabled', false);
                    $section.prop('disabled', false);
                    $payee.prop('readonly', false);
                    $memo.prop('readonly', false);
                    $source.prop('readonly', false);
                }
            }

            function bindRequisitionAutoFill(prefix) {
                $('#' + prefix + '_requisition_id').on('change', function() {
                    var opt = $(this).find('option:selected');
                    var val = $(this).val();

                    if (val) {
                        setRequisitionMode(prefix, true, {
                            amount: opt.data('amount'),
                            workpoint: opt.data('workpoint'),
                            subaccount: opt.data('subaccount'),
                            section: opt.data('section'),
                            payee: opt.data('payee'),
                            memo: opt.data('memo')
                        });

                        if ($('#' + prefix + '_work_point_id').length && opt.data('workpoint')) {
                            $('#' + prefix + '_work_point_id').val(String(opt.data('workpoint'))).trigger(
                                'change');
                        }
                    } else {
                        setRequisitionMode(prefix, false, {});
                    }
                });
            }

            bindAutoCalc('create');
            bindAutoCalc('edit');
            bindRequisitionAutoFill('create');
            bindRequisitionAutoFill('edit');

            document.getElementById('txCreateForm').addEventListener('submit', function(event) {
                $('#create_debit_account').prop('disabled', false);
                $('#create_section_id').prop('disabled', false);

                var source = document.getElementById('create_source_amount').value;
                if (!source || source === '' || source === '0') {
                    alert('Source amount is required!');
                    event.preventDefault();
                }
            });

            document.getElementById('txEditForm').addEventListener('submit', function() {
                $('#edit_debit_account').prop('disabled', false);
                $('#edit_section_id').prop('disabled', false);
            });

            window.openEditTx = function(row) {
                $('#edit_group_enc').val(row.group_enc || '');
                $('#edit_trans_date').val(row.trans_date || '');
                $('#edit_memo').val(row.memo || '');
                $('#edit_payee').val(row.payee || '');
                $('#edit_check_no').val(row.check_no || '');
                $('#edit_currency').val(row.currency || '').trigger('change');
                $('#edit_exchange_rate').val(row.exchange_rate || 1);
                $('#edit_source_amount').val(row.source_amount ?? '').trigger('input');
                $('#edit_amount').val(row.amount || '');
                $('#edit_work_point_id').val(row.work_point_id || '').trigger('change');
                $('#edit_credit_account').val(row.credit_account || '').trigger('change');

                $('#edit_requisition_id').val(row.requisition_id || '').trigger('change');

                if (!row.requisition_id) {
                    $('#edit_section_id').val(row.section_id || '').trigger('change');
                    $('#edit_debit_account').val(row.debit_account || '').trigger('change');
                }

                $('#txEditForm').attr('action', "{{ route('accnttransactions.update', ':id') }}".replace(':id',
                    row.group_enc));
                $('#txEditModal').modal('show');
            };

            $(document).on('click', '.btn-delete-tx', function() {
                var enc = this.getAttribute('data-group');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will mark the transaction group as Deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it!'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href = "{{ url('/admin/accnt-transactions/remove') }}/" +
                            enc;
                    }
                });
            });

            $(document).on('click', '.btn-status-tx', function() {
                var url = $(this).data('url');
                var title = $(this).data('title') || 'Confirm';
                var placeholder = $(this).data('placeholder') || 'Write your comment...';

                $('#statusModalTitle').text(title);
                $('#statusModalComment').attr('placeholder', placeholder).val('');
                $('#statusCommentForm').attr('action', url);
                $('#statusCommentModal').modal('show');
            });

            $('#txCreateModal, #txEditModal').on('hidden.bs.modal', function() {
                $('body').addClass('modal-open');
            });
        });
    </script>
@endsection
