@extends('layouts.AdminMaster')
@section('content')
    <style>
        .payroll-print-area {
            width: 100%;
            background: #fff;
        }

        .payroll-header-img {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
            display: block;
            margin: 0 auto 8px auto;
        }

        .payroll-wide-table {
            width: 100%;
            table-layout: auto;
            font-size: 11px;
        }

        .payroll-wide-table th,
        .payroll-wide-table td {
            padding: 3px 4px !important;
            vertical-align: middle !important;
            white-space: nowrap;
        }

        .payroll-wide-table .name-col {
            min-width: 130px;
            white-space: normal;
        }

        .variation-link {
            color: #1d4ed8;
            font-weight: 800;
            cursor: pointer;
            text-decoration: underline;
            border: none;
            background: transparent;
            padding: 0;
        }

        .company-total-row {
            background: #eaf4ff;
            font-weight: 800;
        }

        .unit-total-row {
            background: #f7f7f7;
            font-weight: 800;
        }

        .grand-total-row {
            background: yellow;
            font-weight: 900;
        }

        .diff-positive {
            color: green;
            font-weight: 800;
        }

        .diff-negative {
            color: red;
            font-weight: 800;
        }

        .diff-zero {
            color: #555;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 4mm;
            }

            .page-heading,
            .no-print,
            .footer,
            footer,
            .pace,
            .theme-config,
            .navbar,
            .sidebar,
            .minimalize-styl-2 {
                display: none !important;
            }

            .wrapper,
            .wrapper-content,
            .animated,
            .fadeInRight,
            .row,
            .col-lg-12,
            .ibox,
            .ibox-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .payroll-wide-table {
                width: 100% !important;
                table-layout: fixed !important;
                font-size: 5.4px !important;
                border-collapse: collapse !important;
            }

            .payroll-wide-table th,
            .payroll-wide-table td {
                padding: 1.5px 2px !important;
                line-height: 1.1 !important;
                white-space: normal !important;
                word-break: break-word !important;
            }

            .print-hide-col {
                display: none !important;
            }
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Payroll Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li><a href="{{ route('hr') }}">Human Resource</a></li><span
                    style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Staff/User Payroll</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong><?php use Carbon\Carbon;
                $carbon = Carbon::now();
                $carbon1 = Carbon::now()->toDateString();
                echo $carbon->format('l');
                echo ' , ';
                echo $carbon1; ?></strong></li>
            </ol>
        </div>
        <div class="col-lg-1">
            <h2>Time</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong>
                        <table>
                            <tr>
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            <tr>
                        </table>
                    </strong></li>
            </ol>
        </div>
    </div>
    <script type="text/javascript">
        function timedMsg() {
            setInterval("change_time();", 1000);
        }

        function change_time() {
            var d = new Date();
            document.getElementById('Hour').innerHTML = d.getHours() + ':';
            document.getElementById('Minut').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second').innerHTML = d.getSeconds();
        }
        timedMsg();
    </script>

    <div class="col-12 no-print">
        <h3 class="mb-2 page-title">Payroll Details — Period: {{ $payroll->period }}</h3>
        <div style="position:absolute; top:4.5%; right:1.7%;">
            @can('Approve-Payroll')
                @if ($payroll->status === 'Prepared')
                    <form style="display:inline" action="{{ route('hr.payrolls.approve', encrypt($payroll->id)) }}"
                        method="POST">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                @endif
            @endcan
            @can('Pay-Payroll')
                @if ($payroll->status === 'Approved')
                    <form style="display:inline" action="{{ route('hr.payrolls.pay', encrypt($payroll->id)) }}" method="POST">
                        @csrf<button class="btn btn-sm btn-primary">Pay</button></form>
                @endif
            @endcan
            @can('Print-Payroll-Sheets')
                <a class="btn btn-sm btn-info" href="{{ route('hr.payrolls.reports', encrypt($payroll->id)) }}">Payroll
                    Sheets</a>
            @endcan
            @can('Delete-Payroll')
                @if ($payroll->status === 'Prepared')
                    <button class="btn btn-sm btn-warning btn-rollback-payroll"
                        data-id="{{ encrypt($payroll->id) }}">Rollback</button>
                @endif
            @endcan
            <button onclick="printReceipt('form1')" class="btn btn-sm btn-primary"><i class="fa fa-print"></i>
                Print</button>
            <a class="btn btn-sm btn-secondary" href="{{ route('hr.payrolls.index') }}">Back</a>
        </div>
    </div>

    @php
        $lines = $payroll->lines ?? collect();
        $userIds = $lines->pluck('user_id')->filter()->unique()->values();

        /* Previous payroll lines used by the Reference popup. If your controller passes
 $previousLinesByUser, this block will use it; otherwise it builds the map here. */
        if (!isset($previousLinesByUser)) {
            $previousLinesByUser = \App\Models\PayrollLine::with('payroll')
                ->whereIn('user_id', $userIds)
                ->whereHas('payroll', function ($q) use ($payroll) {
                    $q->where('period', '<', $payroll->period)->whereIn('status', ['Prepared', 'Approved', 'Paid']);
                })
                ->orderByDesc('id')
                ->get()
                ->unique('user_id')
                ->keyBy('user_id');
        }

        $companiesGrouped = $lines->groupBy(function ($line) {
            $companyName = optional(optional($line->user)->company)->company_name;
            $companyCode = optional(optional($line->user)->company)->company_code;
            return trim(($companyName ?: 'NO COMPANY') . ($companyCode ? ' (' . $companyCode . ')' : ''));
        });

        $grandBasic = $grandGross = $grandAllowance = $grandBonus = $grandOvertime = $grandAbsence = 0;
        $grandHeslb = $grandLoan = $grandPaye = $grandNssf = $grandNet = 0;
        $grandNssfEmployer = $grandSdl = $grandWcf = $grandEmployer = $grandCost = 0;
    @endphp

    <div id="form1" class="wrapper wrapper-content animated fadeInRight mt-5">
        <div class="payroll-print-area">
            <img src="{{ asset('img/header.png') }}" style="width:100%; max-height:100%; object-fit:contain;"
                alt="Company Header" class="payroll-header-img">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox ">
                        <div class="ibox-title bg-info">
                            <h5>Salary & Wages Sheet - {{ $payroll->period }}</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="text-center mb-3">
                                <h4 style="font-weight:bold;">NEW SALARY & WAGES
                                    {{ strtoupper(\Carbon\Carbon::parse($payroll->period . '-01')->format('F Y')) }}</h4>
                                <p>Status: <strong>{{ $payroll->status }}</strong> | Scope:
                                    <strong>{{ $payroll->scope_type ?? 'All' }}</strong> | Days:
                                    <strong>{{ $payroll->days_in_month }}</strong> | Prepared By:
                                    <strong>{{ optional($payroll->preparer)->name ?? '-' }}</strong>
                                </p>
                                @if ($payroll->status === 'Rolled Back')
                                    <p style="color:red"><strong>Rollback Reason:</strong> {{ $payroll->rollback_reason }}
                                    </p>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm payroll-wide-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="name-col">FULL NAME</th>
                                            <th>BRANCH</th>
                                            <th>CAL DAYS</th>
                                            <th>ABSENT</th>
                                            <th>PAID</th>
                                            <th class="text-right">BASIC</th>
                                            <th class="text-right">ALLOW</th>
                                            <th class="text-right">BONUS</th>
                                            <th class="text-right">OT</th>
                                            <th class="text-right">ABS DED</th>
                                            <th class="text-right">GROSS</th>
                                            <th class="text-right">PAYE</th>
                                            <th class="text-right">NSSF 10%</th>
                                            <th class="text-right">HESLB</th>
                                            <th class="text-right">LOANS</th>
                                            <th class="text-right">NET</th>
                                            <th class="text-right">PREV NET</th>
                                            <th class="text-right print-hide-col">REFERENCE</th>
                                            <th class="text-right">NSSF ER</th>
                                            <th class="text-right">SDL</th>
                                            <th class="text-right">WCF</th>
                                            <th class="text-right">TOTAL ER</th>
                                            <th class="text-right">TOTAL COST</th>
                                            <th class="print-hide-col">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($companiesGrouped as $companyName => $companyLines)
                                            @php
                                                $companyBasic = $companyGross = $companyAllowance = $companyBonus = $companyOvertime = $companyAbsence = 0;
                                                $companyHeslb = $companyLoan = $companyPaye = $companyNssf = $companyNet = 0;
                                                $companyNssfEmployer = $companySdl = $companyWcf = $companyEmployer = $companyCost = 0;
                                            @endphp

                                            <tr>
                                                <th colspan="25" style="background:#d9edf7;font-size:13px;">
                                                    COMPANY SITE: {{ strtoupper($companyName) }}
                                                </th>
                                            </tr>

                                            @php
                                                $unitsGrouped = $companyLines->groupBy(function ($line) {
                                                    return optional(optional($line->user)->comp_unit)->unit_name ??
                                                        'NO BUSINESS UNIT';
                                                });
                                            @endphp

                                            @foreach ($unitsGrouped as $unitName => $unitLines)
                                                <tr>
                                                    <th colspan="25" style="background:#ffffff;font-size:12px;">
                                                        COMPANY UNIT: {{ strtoupper($unitName) }}</th>
                                                </tr>

                                                @php
                                                    $unitBasic = $unitGross = $unitAllowance = $unitBonus = $unitOvertime = $unitAbsence = 0;
                                                    $unitHeslb = $unitLoan = $unitPaye = $unitNssf = $unitNet = 0;
                                                    $unitNssfEmployer = $unitSdl = $unitWcf = $unitEmployer = $unitCost = 0;
                                                @endphp

                                                @foreach ($unitLines as $i => $line)
                                                    @php
                                                        $previousLine = $previousLinesByUser->get($line->user_id);

                                                        $currentPayload = [
                                                            'period' => $payroll->period,
                                                            'staff' => optional($line->user)->name ?? '-',
                                                            'company' =>
                                                                optional(optional($line->user)->company)
                                                                    ->company_name ?? '-',
                                                            'workpoint' =>
                                                                optional(optional($line->user)->workpoint)->work_name ??
                                                                '-',
                                                            'basic_salary' => (float) ($line->basic_salary ?? 0),
                                                            'allowances' => (float) ($line->allowances ?? 0),
                                                            'bonus' => (float) ($line->bonus ?? 0),
                                                            'overtime_payment' =>
                                                                (float) ($line->overtime_payment ?? 0),
                                                            'absence_deduction' =>
                                                                (float) ($line->absence_deduction ?? 0),
                                                            'gross' => (float) ($line->gross ?? 0),
                                                            'paye' => (float) ($line->paye ?? 0),
                                                            'nssf_employee' => (float) ($line->nssf_employee ?? 0),
                                                            'heslb_deduction' => (float) ($line->heslb_deduction ?? 0),
                                                            'loan_deduction' => (float) ($line->loan_deduction ?? 0),
                                                            'net_pay' => (float) ($line->net_pay ?? 0),
                                                            'nssf_employer' => (float) ($line->nssf_employer ?? 0),
                                                            'sdl' => (float) ($line->sdl ?? 0),
                                                            'wcf' => (float) ($line->wcf ?? 0),
                                                            'employer_cost' => (float) ($line->employer_cost ?? 0),
                                                            'total_payroll_cost' =>
                                                                (float) ($line->total_payroll_cost ?? 0),
                                                        ];

                                                        $previousPayload = $previousLine
                                                            ? [
                                                                'period' =>
                                                                    optional($previousLine->payroll)->period ?? '-',
                                                                'staff' => optional($line->user)->name ?? '-',
                                                                'company' =>
                                                                    optional(optional($line->user)->company)
                                                                        ->company_name ?? '-',
                                                                'workpoint' =>
                                                                    optional(optional($line->user)->workpoint)
                                                                        ->work_name ?? '-',
                                                                'basic_salary' =>
                                                                    (float) ($previousLine->basic_salary ?? 0),
                                                                'allowances' =>
                                                                    (float) ($previousLine->allowances ?? 0),
                                                                'bonus' => (float) ($previousLine->bonus ?? 0),
                                                                'overtime_payment' =>
                                                                    (float) ($previousLine->overtime_payment ?? 0),
                                                                'absence_deduction' =>
                                                                    (float) ($previousLine->absence_deduction ?? 0),
                                                                'gross' => (float) ($previousLine->gross ?? 0),
                                                                'paye' => (float) ($previousLine->paye ?? 0),
                                                                'nssf_employee' =>
                                                                    (float) ($previousLine->nssf_employee ?? 0),
                                                                'heslb_deduction' =>
                                                                    (float) ($previousLine->heslb_deduction ?? 0),
                                                                'loan_deduction' =>
                                                                    (float) ($previousLine->loan_deduction ?? 0),
                                                                'net_pay' => (float) ($previousLine->net_pay ?? 0),
                                                                'nssf_employer' =>
                                                                    (float) ($previousLine->nssf_employer ?? 0),
                                                                'sdl' => (float) ($previousLine->sdl ?? 0),
                                                                'wcf' => (float) ($previousLine->wcf ?? 0),
                                                                'employer_cost' =>
                                                                    (float) ($previousLine->employer_cost ?? 0),
                                                                'total_payroll_cost' =>
                                                                    (float) ($previousLine->total_payroll_cost ?? 0),
                                                            ]
                                                            : null;

                                                        $currentJson = e(json_encode($currentPayload));
                                                        $previousJson = e(json_encode($previousPayload));

                                                        $unitBasic += (float) ($line->basic_salary ?? 0);
                                                        $unitGross += (float) ($line->gross ?? 0);
                                                        $unitAllowance += (float) ($line->allowances ?? 0);
                                                        $unitBonus += (float) ($line->bonus ?? 0);
                                                        $unitOvertime += (float) ($line->overtime_payment ?? 0);
                                                        $unitAbsence += (float) ($line->absence_deduction ?? 0);
                                                        $unitHeslb += (float) ($line->heslb_deduction ?? 0);
                                                        $unitLoan += (float) ($line->loan_deduction ?? 0);
                                                        $unitPaye += (float) ($line->paye ?? 0);
                                                        $unitNssf += (float) ($line->nssf_employee ?? 0);
                                                        $unitNet += (float) ($line->net_pay ?? 0);
                                                        $unitNssfEmployer += (float) ($line->nssf_employer ?? 0);
                                                        $unitSdl += (float) ($line->sdl ?? 0);
                                                        $unitWcf += (float) ($line->wcf ?? 0);
                                                        $unitEmployer += (float) ($line->employer_cost ?? 0);
                                                        $unitCost += (float) ($line->total_payroll_cost ?? 0);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td class="name-col">{{ optional($line->user)->name ?? '-' }}</td>
                                                        <td>{{ optional(optional($line->user)->workpoint)->work_name ?? '-' }}
                                                        </td>
                                                        <td>{{ $line->calendar_days }}</td>
                                                        <td>{{ $line->absent_days }}</td>
                                                        <td>{{ $line->paid_days }}</td>
                                                        <td class="text-right">{{ number_format($line->basic_salary, 2) }}
                                                        </td>
                                                        <td class="text-right">{{ number_format($line->allowances, 2) }}
                                                        </td>
                                                        <td class="text-right">{{ number_format($line->bonus ?? 0, 2) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($line->overtime_payment, 2) }}</td>
                                                        <td class="text-right">
                                                            {{ number_format($line->absence_deduction, 2) }}</td>
                                                        <td class="text-right">{{ number_format($line->gross, 2) }}</td>
                                                        <td class="text-right">{{ number_format($line->paye, 2) }}</td>
                                                        <td class="text-right">{{ number_format($line->nssf_employee, 2) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($line->heslb_deduction ?? 0, 2) }}</td>
                                                        <td class="text-right">
                                                            {{ number_format($line->loan_deduction, 2) }}</td>
                                                        <td class="text-right">{{ number_format($line->net_pay, 2) }}</td>
                                                        <td class="text-right">
                                                            {{ number_format($line->previous_net_pay ?? 0, 2) }}</td>
                                                        <td class="text-right print-hide-col">
                                                            @if ($previousLine)
                                                                <button type="button"
                                                                    class="variation-link btn-payroll-reference"
                                                                    data-current="{{ $currentJson }}"
                                                                    data-previous="{{ $previousJson }}">
                                                                    {{ number_format($line->net_variation ?? 0, 2) }} Ref
                                                                </button>
                                                            @else
                                                                <span class="text-muted">No previous</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($line->nssf_employer ?? 0, 2) }}</td>
                                                        <td class="text-right">{{ number_format($line->sdl ?? 0, 2) }}</td>
                                                        <td class="text-right">{{ number_format($line->wcf ?? 0, 2) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($line->employer_cost, 2) }}</td>
                                                        <td class="text-right">
                                                            {{ number_format($line->total_payroll_cost, 2) }}</td>
                                                        <td class="print-hide-col">
                                                            @can('Edit-Payroll')
                                                                @if ($payroll->status === 'Prepared')
                                                                    <button class="btn btn-xs btn-warning btn-edit-line"
                                                                        data-toggle="modal" data-target="#lineEditModal"
                                                                        data-action="{{ route('hr.payrolls.line.update', [$payroll->id, $line->id]) }}"
                                                                        data-basic="{{ $line->basic_salary }}"
                                                                        data-allowances="{{ $line->allowances }}"
                                                                        data-bonus="{{ $line->bonus ?? 0 }}"
                                                                        data-absence="{{ $line->absence_deduction }}"
                                                                        data-loan="{{ $line->loan_deduction }}"
                                                                        data-heslb="{{ $line->heslb_deduction ?? 0 }}"
                                                                        data-ot="{{ $line->overtime_payment }}"
                                                                        data-note="{{ $line->note }}">Edit</button>
                                                                @endif
                                                            @endcan
                                                            @can('View-Payslip')
                                                                <a href="{{ route('hr.payrolls.slip', [encrypt($payroll->id), encrypt($line->user_id)]) }}"
                                                                    class="btn btn-xs btn-secondary">Slip</a>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                @php
                                                    $companyBasic += $unitBasic;
                                                    $companyGross += $unitGross;
                                                    $companyAllowance += $unitAllowance;
                                                    $companyBonus += $unitBonus;
                                                    $companyOvertime += $unitOvertime;
                                                    $companyAbsence += $unitAbsence;
                                                    $companyHeslb += $unitHeslb;
                                                    $companyLoan += $unitLoan;
                                                    $companyPaye += $unitPaye;
                                                    $companyNssf += $unitNssf;
                                                    $companyNet += $unitNet;
                                                    $companyNssfEmployer += $unitNssfEmployer;
                                                    $companySdl += $unitSdl;
                                                    $companyWcf += $unitWcf;
                                                    $companyEmployer += $unitEmployer;
                                                    $companyCost += $unitCost;
                                                @endphp
                                                <tr class="unit-total-row">
                                                    <td colspan="6" class="text-right">TOTAL FOR
                                                        {{ strtoupper($unitName) }}</td>
                                                    <td class="text-right">{{ number_format($unitBasic, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitAllowance, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitBonus, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitOvertime, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitAbsence, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitGross, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitPaye, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitNssf, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitHeslb, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitLoan, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitNet, 2) }}</td>
                                                    <td colspan="2" class="print-hide-col"></td>
                                                    <td class="text-right">{{ number_format($unitNssfEmployer, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitSdl, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitWcf, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitEmployer, 2) }}</td>
                                                    <td class="text-right">{{ number_format($unitCost, 2) }}</td>
                                                    <td class="print-hide-col"></td>
                                                </tr>
                                            @endforeach

                                            @php
                                                $grandBasic += $companyBasic;
                                                $grandGross += $companyGross;
                                                $grandAllowance += $companyAllowance;
                                                $grandBonus += $companyBonus;
                                                $grandOvertime += $companyOvertime;
                                                $grandAbsence += $companyAbsence;
                                                $grandHeslb += $companyHeslb;
                                                $grandLoan += $companyLoan;
                                                $grandPaye += $companyPaye;
                                                $grandNssf += $companyNssf;
                                                $grandNet += $companyNet;
                                                $grandNssfEmployer += $companyNssfEmployer;
                                                $grandSdl += $companySdl;
                                                $grandWcf += $companyWcf;
                                                $grandEmployer += $companyEmployer;
                                                $grandCost += $companyCost;
                                            @endphp
                                            <tr class="company-total-row">
                                                <td colspan="6" class="text-right">TOTAL FOR COMPANY SITE:
                                                    {{ strtoupper($companyName) }}</td>
                                                <td class="text-right">{{ number_format($companyBasic, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyAllowance, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyBonus, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyOvertime, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyAbsence, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyGross, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyPaye, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyNssf, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyHeslb, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyLoan, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyNet, 2) }}</td>
                                                <td colspan="2" class="print-hide-col"></td>
                                                <td class="text-right">{{ number_format($companyNssfEmployer, 2) }}</td>
                                                <td class="text-right">{{ number_format($companySdl, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyWcf, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyEmployer, 2) }}</td>
                                                <td class="text-right">{{ number_format($companyCost, 2) }}</td>
                                                <td class="print-hide-col"></td>
                                            </tr>
                                        @endforeach

                                        <tr class="grand-total-row">
                                            <td colspan="6" class="text-right">GENERAL / GRAND TOTAL</td>
                                            <td class="text-right">{{ number_format($grandBasic, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandAllowance, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandBonus, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandOvertime, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandAbsence, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandGross, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandPaye, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandNssf, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandHeslb, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandLoan, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandNet, 2) }}</td>
                                            <td colspan="2" class="print-hide-col"></td>
                                            <td class="text-right">{{ number_format($grandNssfEmployer, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandSdl, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandWcf, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandEmployer, 2) }}</td>
                                            <td class="text-right">{{ number_format($grandCost, 2) }}</td>
                                            <td class="print-hide-col"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade no-print" id="variationReferenceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payroll Difference Reference</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="variationSummary" class="alert alert-info"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-right">Previous Payroll</th>
                                    <th class="text-right">Current Payroll</th>
                                    <th class="text-right">Difference</th>
                                </tr>
                            </thead>
                            <tbody id="variationTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade no-print" id="lineEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="lineEditForm" method="POST">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payroll Line</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Basic Salary</label><input id="line_basic" type="number"
                                step="0.01" name="basic_salary" class="form-control"></div>
                        <div class="form-group"><label>Allowance</label><input id="line_allowances" type="number"
                                step="0.01" name="allowances" class="form-control"></div>
                        <div class="form-group"><label>Bonus</label><input id="line_bonus" type="number" step="0.01"
                                name="bonus" class="form-control"></div>
                        <div class="form-group"><label>Overtime</label><input id="line_ot" type="number"
                                step="0.01" name="overtime_payment" class="form-control"></div>
                        <div class="form-group"><label>Absence Deduction</label><input id="line_absence" type="number"
                                step="0.01" name="absence_deduction" class="form-control"></div>
                        <div class="form-group"><label>Loan Deduction</label><input id="line_loan" type="number"
                                step="0.01" name="loan_deduction" class="form-control"></div>
                        <div class="form-group"><label>HESLB Deduction</label><input id="line_heslb" type="number"
                                step="0.01" name="heslb_deduction" class="form-control"></div>
                        <div class="form-group"><label>Note</label>
                            <textarea id="line_note" name="note" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('lineEditForm')" class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <form id="rollbackPayrollForm" method="POST" style="display:none">@csrf<input type="hidden"
            name="rollback_reason" id="rollback_reason"></form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function money(value) {
                value = parseFloat(value || 0);
                return value.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function diffClass(value) {
                value = parseFloat(value || 0);
                if (value > 0) return 'diff-positive';
                if (value < 0) return 'diff-negative';
                return 'diff-zero';
            }

            function labelFor(key) {
                var labels = {
                    basic_salary: 'Basic Salary',
                    allowances: 'Allowance',
                    bonus: 'Bonus',
                    overtime_payment: 'Overtime',
                    absence_deduction: 'Absence Deduction',
                    gross: 'Gross Pay',
                    paye: 'PAYE',
                    nssf_employee: 'NSSF Employee',
                    nssf_employer: 'NSSF Employer Cost',
                    sdl: 'SDL',
                    wcf: 'WCF',
                    heslb_deduction: 'HESLB Deduction',
                    loan_deduction: 'Loan / Advance Deduction',
                    net_pay: 'Net Pay',
                    employer_cost: 'Employer Cost',
                    total_payroll_cost: 'Total Payroll Cost'
                };
                return labels[key] || key;
            }

            document.querySelectorAll('.btn-payroll-reference').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var current = JSON.parse(this.dataset.current || '{}');
                    var previous = JSON.parse(this.dataset.previous || 'null');
                    var body = document.getElementById('variationTableBody');
                    body.innerHTML = '';

                    if (!previous) {
                        body.innerHTML =
                            '<tr><td colspan="4" class="text-center text-danger">No previous payroll found for this staff.</td></tr>';
                    } else {
                        var keys = [
                            'basic_salary', 'allowances', 'bonus', 'overtime_payment',
                            'absence_deduction',
                            'gross', 'paye', 'nssf_employee', 'heslb_deduction',
                            'loan_deduction', 'net_pay',
                            'nssf_employer', 'sdl', 'wcf', 'employer_cost', 'total_payroll_cost'
                        ];

                        keys.forEach(function(key) {
                            var prev = parseFloat(previous[key] || 0);
                            var curr = parseFloat(current[key] || 0);
                            var diff = curr - prev;
                            var changedStyle = Math.abs(diff) > 0.00001 ?
                                'background:#fff7ed;font-weight:800;' : '';

                            body.innerHTML += '<tr style="' + changedStyle + '">' +
                                '<td>' + labelFor(key) + '</td>' +
                                '<td class="text-right">' + money(prev) + '</td>' +
                                '<td class="text-right">' + money(curr) + '</td>' +
                                '<td class="text-right ' + diffClass(diff) + '">' + money(
                                    diff) + '</td>' +
                                '</tr>';
                        });
                    }

                    document.getElementById('variationSummary').innerHTML =
                        '<strong>Staff:</strong> ' + (current.staff || '-') + '<br>' +
                        '<strong>Company:</strong> ' + (current.company || '-') + '<br>' +
                        '<strong>Work Point:</strong> ' + (current.workpoint || '-') + '<br>' +
                        '<strong>Previous Period:</strong> ' + ((previous && previous.period) ?
                            previous.period : '-') +
                        ' | <strong>Current Period:</strong> ' + (current.period || '-');

                    $('#variationReferenceModal').modal('show');
                });
            });

            document.querySelectorAll('.btn-edit-line').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('lineEditForm').action = this.dataset.action;
                    document.getElementById('line_basic').value = this.dataset.basic || 0;
                    document.getElementById('line_allowances').value = this.dataset.allowances || 0;
                    document.getElementById('line_bonus').value = this.dataset.bonus || 0;
                    document.getElementById('line_ot').value = this.dataset.ot || 0;
                    document.getElementById('line_absence').value = this.dataset.absence || 0;
                    document.getElementById('line_loan').value = this.dataset.loan || 0;
                    document.getElementById('line_heslb').value = this.dataset.heslb || 0;
                    document.getElementById('line_note').value = this.dataset.note || '';
                });
            });

            document.querySelectorAll('.btn-rollback-payroll').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                        title: 'Rollback payroll?',
                        input: 'textarea',
                        inputLabel: 'Reason',
                        inputPlaceholder: 'Write rollback reason...',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Rollback'
                    }).then(function(res) {
                        if (res.isConfirmed && res.value) {
                            var form = document.getElementById('rollbackPayrollForm');
                            document.getElementById('rollback_reason').value = res.value;
                            form.action = "{{ route('hr.payrolls.rollback', ':id') }}"
                                .replace(':id', enc);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
