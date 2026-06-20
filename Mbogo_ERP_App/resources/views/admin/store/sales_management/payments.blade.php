@extends('layouts.salesMaster')

@section('content')
    @php
        $enc = fn($id) => $id ? \Illuminate\Support\Facades\Crypt::encryptString((string) $id) : '';
        $total = $payments->sum('amount');
        $approved = $payments->where('status', 'approved')->sum('amount');
        $pending = $payments->where('status', 'pending')->sum('amount');

        $selectedCompanyEnc = request('company_id');
        $selectedUnitEnc = request('business_unit_id');
        $selectedWorkPointEnc = request('work_point_id');
    @endphp

    <style>
        .payments-page .select2-container {
            width: 100% !important;
        }

        .payments-page .summary-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            min-height: 88px;
        }

        .payments-page .summary-box h3 {
            margin: 0;
            color: #1c84c6;
            font-weight: bold;
        }

        .payments-page .report-title {
            display: none;
            text-align: center;
            margin-bottom: 10px;
        }

        .payments-page #payments_table {
            width: 100% !important;
        }

        .payments-page #payments_table th,
        .payments-page #payments_table td {
            vertical-align: top !important;
            white-space: normal !important;
            word-break: break-word !important;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 7mm;
            }

            .no-print,
            .navbar,
            .footer,
            .page-heading,
            .sidebar-collapse,
            #side-menu,
            .minimalize-styl-2,
            .filter-box {
                display: none !important;
            }

            #wrapper,
            #page-wrapper,
            .wrapper,
            .wrapper-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                overflow: visible !important;
                background: #fff !important;
            }

            .payments-page,
            .payments-page .print-area,
            .payments-page .ibox,
            .payments-page .ibox-content,
            .payments-page .table-responsive {
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .payments-page .ibox-title {
                display: none !important;
            }

            .payments-page .report-title {
                display: block !important;
            }

            .payments-page .summary-box {
                padding: 5px !important;
                min-height: 50px !important;
            }

            .payments-page .summary-box h5 {
                font-size: 10px !important;
                margin: 0 0 2px !important;
            }

            .payments-page .summary-box h3 {
                font-size: 13px !important;
            }

            .payments-page #payments_table {
                table-layout: fixed !important;
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 8px !important;
            }

            .payments-page #payments_table th,
            .payments-page #payments_table td {
                border: 1px solid #444 !important;
                padding: 3px !important;
                overflow-wrap: anywhere !important;
                line-height: 1.15 !important;
            }

            .payments-page .no-print-col {
                display: none !important;
            }
        }
    </style>

    <div class="payments-page wrapper wrapper-content">
        <div class="row wrapper border-bottom white-bg page-heading no-print">
            <div class="col-lg-8">
                <h2 class="dashboard-title">Sales Management Module</h2>
                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li><a href="{{ route('sales.dashboard') }}">Sales Management</a></li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li class="active"><strong>Payments Details</strong></li>
                </ol>
            </div>
            <div class="col-lg-2">
                <h4>Current Date</h4>
                <strong>{{ now()->format('l, Y-m-d') }}</strong>
            </div>
            <div class="col-lg-2">
                <h4>Time</h4>
                <strong>
                    <span id="Hour" style="color:green"></span>
                    <span id="Minut" style="color:green"></span>
                    <span id="Second" style="color:red"></span>
                </strong>
            </div>
        </div>

        <div class="ibox no-print filter-box">
            <div class="ibox-title bg-primary">
                <h5>Filter Payment Report</h5>
            </div>
            <div class="ibox-content">
                <form method="GET" action="{{ route('sales.payments.index') }}" id="paymentFilterForm">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Company</label>
                            <select name="company_id" id="company_id" class="form-control select2_demo_2">
                                <option value="">All</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $enc($c->id) }}"
                                        {{ $selectedCompanyId == $c->id ? 'selected' : '' }}>
                                        {{ $c->company_code }} - {{ $c->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Unit</label>
                            <select name="business_unit_id" id="business_unit_id" class="form-control select2_demo_2">
                                <option value="">All</option>
                                @if ($selectedUnit)
                                    <option value="{{ $selectedUnitEnc ?: $enc($selectedUnit->id) }}" selected>
                                        {{ $selectedUnit->unit_code }} - {{ $selectedUnit->unit_name }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Location</label>
                            <select name="work_point_id" id="work_point_id" class="form-control select2_demo_2">
                                <option value="">All</option>
                                @if ($selectedWorkPoint)
                                    <option value="{{ $selectedWorkPointEnc ?: $enc($selectedWorkPoint->id) }}" selected>
                                        {{ $selectedWorkPoint->work_code }} - {{ $selectedWorkPoint->work_name }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Product</label>
                            <select name="product_id" class="form-control select2_demo_2">
                                <option value="">All</option>
                                @foreach ($products as $p)
                                    <option value="{{ $enc($p->id) }}"
                                        {{ request('product_id') && request('product_id') == $enc($p->id) ? 'selected' : '' }}>
                                        {{ $p->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                                </option>
                            </select>
                        </div>

                        <div class="col-md-7 mt-4">
                            <button class="btn btn-primary">
                                <i class="fa fa-search"></i> Search
                            </button>

                            <a href="{{ route('sales.payments.index') }}" class="btn btn-default">
                                Reset
                            </a>

                            <button type="button" onclick="window.print()" class="btn btn-success">
                                <i class="fa fa-print"></i> Print Area
                            </button>

                            <button type="button" onclick="exportTableToExcel('payments_table','payments_report')"
                                class="btn btn-info">
                                <i class="fa fa-file-excel-o"></i> Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="print-area ibox">
            <div class="ibox-title">
                <h5>Payment Report</h5>
            </div>
            <div class="ibox-content">
                <div class="report-title">
                    <h3>Payment Report</h3>
                    <div>
                        Printed: {{ now()->format('Y-m-d H:i') }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="summary-box">
                            <h5>Total Payments</h5>
                            <h3>{{ number_format($total, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-box">
                            <h5>Approved</h5>
                            <h3>{{ number_format($approved, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-box">
                            <h5>Pending</h5>
                            <h3>{{ number_format($pending, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="payments_table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Payment No</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Company</th>
                                <th>Unit</th>
                                <th>Location</th>
                                <th>Method</th>
                                <th>Account</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="no-print-col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $p)
                                @php
                                    $inv = $p->invoice;
                                    $company = optional($inv)->company ?: $p->company;
                                    $unit = optional($inv)->businessUnit ?: $p->businessUnit;
                                    $work = optional($inv)->workPoint ?: $p->workPoint;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ optional($p->payment_date)->format('Y-m-d') }}</td>
                                    <td>{{ $p->payment_no }}</td>
                                    <td>{{ optional($inv)->invoice_no }}</td>
                                    <td>{{ optional($p->customer)->customer_name }}</td>
                                    <td>{{ optional($company)->company_name }}</td>
                                    <td>{{ optional($unit)->unit_name }}</td>
                                    <td>{{ optional($work)->work_name }}</td>
                                    <td>{{ ucfirst($p->payment_method) }}</td>
                                    <td>
                                        {{ optional($p->paymentAccount)->SubCode }}
                                        {{ optional($p->paymentAccount)->SubDescription ? ' - ' . optional($p->paymentAccount)->SubDescription : '' }}
                                    </td>
                                    <td>{{ $p->currency }} {{ number_format($p->amount, 2) }}</td>
                                    <td>
                                        <span
                                            class="label label-{{ $p->status == 'approved' ? 'success' : ($p->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </td>
                                    <td class="no-print-col">
                                        @if ($p->status == 'pending')
                                            @can('Verify-Payments')
                                                <form method="POST"
                                                    action="{{ route('sales.payments.verify', $enc($p->id)) }}"
                                                    style="display:inline">
                                                    @csrf
                                                    <button class="btn btn-xs btn-success"
                                                        onclick="return confirm('Approve payment and post accounting/stock?')">
                                                        Approve
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('Delete-Payments')
                                                <form method="POST"
                                                    action="{{ route('sales.payments.delete', $enc($p->id)) }}"
                                                    style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Delete pending payment?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif

                                        @can('Print-Payments')
                                            @if ($p->invoice)
                                                <a href="{{ route('sales.payments.print', $enc($p->id)) }}" target="_blank"
                                                    class="btn btn-xs btn-primary">
                                                    Print Invoice
                                                </a>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="10" class="text-right">TOTAL</th>
                                <th>{{ number_format($total, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function timedMsg() {
            setInterval(change_time, 1000);
        }

        function change_time() {
            const d = new Date();
            document.getElementById('Hour').innerHTML = String(d.getHours()).padStart(2, '0') + ':';
            document.getElementById('Minut').innerHTML = String(d.getMinutes()).padStart(2, '0') + ':';
            document.getElementById('Second').innerHTML = String(d.getSeconds()).padStart(2, '0');
        }

        timedMsg();

        document.addEventListener('DOMContentLoaded', function() {
            initSelect2();

            $('#company_id').on('change', function() {
                loadUnits(this.value, '', '');
            });

            $('#business_unit_id').on('change', function() {
                loadWorkPoints(this.value, '', '');
            });
        });

        function initSelect2() {
            if (window.$ && $.fn.select2) {
                $('.select2_demo_2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        }

        function refreshSelect2(selector) {
            if (window.$ && $.fn.select2) {
                $(selector).trigger('change.select2');
            }
        }

        function loadUnits(companyId, selectedValue = '', selectedText = '') {
            $('#business_unit_id')
                .empty()
                .append('<option value="">Loading...</option>');

            $('#work_point_id')
                .empty()
                .append('<option value="">All</option>');

            refreshSelect2('#business_unit_id');
            refreshSelect2('#work_point_id');

            if (!companyId) {
                $('#business_unit_id')
                    .empty()
                    .append('<option value="">All</option>');
                refreshSelect2('#business_unit_id');
                return;
            }

            fetch(`{{ url('/admin/ajax/company-units') }}/${encodeURIComponent(companyId)}`)
                .then(response => response.json())
                .then(data => {
                    $('#business_unit_id')
                        .empty()
                        .append('<option value="">All</option>');

                    data.forEach(unit => {
                        $('#business_unit_id').append(new Option(unit.text, unit.id, false, false));
                    });

                    if (selectedValue && $('#business_unit_id option[value="' + selectedValue + '"]').length === 0) {
                        $('#business_unit_id').append(new Option(selectedText || selectedValue, selectedValue, true,
                            true));
                    }

                    if (selectedValue) {
                        $('#business_unit_id').val(selectedValue);
                    }

                    refreshSelect2('#business_unit_id');
                });
        }

        function loadWorkPoints(unitId, selectedValue = '', selectedText = '') {
            $('#work_point_id')
                .empty()
                .append('<option value="">Loading...</option>');

            refreshSelect2('#work_point_id');

            if (!unitId) {
                $('#work_point_id')
                    .empty()
                    .append('<option value="">All</option>');
                refreshSelect2('#work_point_id');
                return;
            }

            fetch(`{{ url('/admin/ajax/unit-workpoints') }}/${encodeURIComponent(unitId)}`)
                .then(response => response.json())
                .then(data => {
                    $('#work_point_id')
                        .empty()
                        .append('<option value="">All</option>');

                    data.forEach(work => {
                        $('#work_point_id').append(new Option(work.text, work.id, false, false));
                    });

                    if (selectedValue && $('#work_point_id option[value="' + selectedValue + '"]').length === 0) {
                        $('#work_point_id').append(new Option(selectedText || selectedValue, selectedValue, true,
                            true));
                    }

                    if (selectedValue) {
                        $('#work_point_id').val(selectedValue);
                    }

                    refreshSelect2('#work_point_id');
                });
        }

        function exportTableToExcel(tableID, filename = '') {
            let table = document.getElementById(tableID).cloneNode(true);

            table.querySelectorAll('.no-print-col').forEach(el => el.remove());

            let dataType = 'application/vnd.ms-excel';
            let html = table.outerHTML.replace(/ /g, '%20');

            filename = filename ? filename + '.xls' : 'excel_data.xls';

            let a = document.createElement('a');
            document.body.appendChild(a);
            a.href = 'data:' + dataType + ', ' + html;
            a.download = filename;
            a.click();
            a.remove();
        }
    </script>
@endsection
