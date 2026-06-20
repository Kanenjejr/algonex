@extends('layouts.AdminMaster')
@section('content')
    <style>
        .payslip-print-area {
            max-width: 980px;
            margin: 0 auto;
            background: #fff;
        }

        .payslip-header-img {
            width: 100%;
            max-height: 165px;
            object-fit: contain;
            display: block;
            margin: 0 auto 10px auto;
        }

        .payslip-card {
            max-width: 760px;
            margin: 0 auto;
        }

        .payslip-card .ibox-content {
            background: #fff;
        }

        .payslip-card table th,
        .payslip-card table td {
            font-size: 13px;
            padding: 6px 8px !important;
        }

        .payslip-section-title {
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 5px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background: #fff !important;
                overflow: visible !important;
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
            .col-md-8,
            .offset-md-2,
            .col-lg-12 {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }

            #form1 {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .payslip-print-area {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }

            .payslip-header-img {
                width: 100% !important;
                max-height: 38mm !important;
                object-fit: contain !important;
                margin-bottom: 4mm !important;
            }

            .payslip-card {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }

            .ibox,
            .ibox-content {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .ibox-title {
                padding: 8px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .table {
                width: 100% !important;
                page-break-inside: avoid;
            }

            .table th,
            .table td {
                font-size: 10px !important;
                padding: 3px 5px !important;
            }

            h3,
            h5,
            h6,
            p {
                margin: 4px 0 !important;
            }
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Payroll Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('hr') }}">Human Resource</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Staff/User Payroll</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php use Carbon\Carbon;
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
                            <tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg() {
            var t = setInterval("change_time();", 1000);
        }

        function change_time() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();

            if (curr_hour > 24) {
                curr_hour = curr_hour - 24;
            }

            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }

        timedMsg();
    </script>

    @php
        $payroll = $payroll ?? null;
        $line = $line ?? null;
        $staff = $staff ?? null;

        $basicSalary = (float) ($line->basic_salary ?? 0);
        $allowances = (float) ($line->allowances ?? 0);
        $bonus = (float) ($line->bonus ?? 0);
        $overtimePayment = (float) ($line->overtime_payment ?? 0);
        $absenceDeduction = (float) ($line->absence_deduction ?? 0);
        $gross = (float) ($line->gross ?? 0);

        $paye = (float) ($line->paye ?? 0);
        $nssfEmployee = (float) ($line->nssf_employee ?? 0);
        $psssf = (float) ($line->psssf ?? 0);
        $loanDeduction = (float) ($line->loan_deduction ?? 0);
        $heslbDeduction = (float) ($line->heslb_deduction ?? 0);

        $employeeDeductions = $paye + $nssfEmployee + $psssf + $loanDeduction + $heslbDeduction;

        $totalDeductions = (float) ($line->total_deductions ?? $employeeDeductions);

        $nssfEmployer = (float) ($line->nssf_employer ?? 0);
        $sdl = (float) ($line->sdl ?? 0);
        $wcf = (float) ($line->wcf ?? 0);
        $employerCosts = $nssfEmployer + $sdl + $wcf;

        $calendarDays = $line->calendar_days ?? optional($payroll)->days_in_month;
        $absentDays = $line->absent_days ?? 0;
        $paidDays = $line->paid_days ?? ($calendarDays ?? 0) - ($absentDays ?? 0);
        $dailyRate = $line->daily_rate ?? 0;

        $previousNetPay = (float) ($line->previous_net_pay ?? 0);
        $netVariation = (float) ($line->net_variation ?? ($line->net_pay ?? 0) - $previousNetPay);
        $grossVariation = (float) ($line->gross_variation ?? 0);
    @endphp

    <div class="col-12 no-print">
        <h3 class="mb-2 page-title">Salary Slip - {{ optional($staff)->name ?? '-' }}</h3>
        <div style="position:absolute; top:4.5%; right:1.7%;">
            @if ($payroll)
                <a href="{{ route('hr.payrolls.show', encrypt($payroll->id)) }}" class="btn btn-sm btn-secondary">Back</a>
            @else
                <a href="{{ route('hr.payrolls.index') }}" class="btn btn-sm btn-secondary">Back</a>
            @endif
            @can('Print-Payslip')
                <button onclick="printReceipt('form1')"class="btn btn-sm btn-primary float-right ml-1"><i
                        class="fa fa-print"></i> Print Report</button>
            @endcan
        </div>
    </div>

    <div id="form1" class="wrapper wrapper-content animated fadeInRight">
        <div class="payslip-print-area">

            <img src="{{ asset('img/header.png') }}" alt="Company Header" class="payslip-header-img">

            <div class="payslip-card">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Payslip for {{ $payroll->period ?? '-' }}</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="row mb-3">
                            <div class="col-md-6 text-left">
                                <strong>Employee:</strong><br>
                                {{ optional($staff)->name ?? '-' }} <br>
                                <small>Username: {{ optional($staff)->username ?? '-' }}</small><br>
                                <small>Gender: {{ optional($staff)->gender ?? '-' }}</small><br>
                                <small>Role: {{ optional($staff)->role ?? '-' }}</small><br>
                                <small>Phone: {{ optional($staff)->phone_No ?? '-' }}</small><br>
                                <small>Email: {{ optional($staff)->email ?? '-' }}</small><br>
                                <small>NSSF No: {{ optional($staff)->nssfNo ?? 'N/A' }}</small><br>
                                <small>WCF No: {{ optional($staff)->wcfNo ?? 'N/A' }}</small><br>
                                <small>NHIF: {{ optional($staff)->NHIF ?? 'N/A' }}</small><br>
                                <small>TIN: {{ optional($staff)->TIN ?? 'N/A' }}</small>
                            </div>

                            <div class="col-md-6 text-right">
                                <strong>Company:</strong><br>
                                {{ optional(optional($staff)->company)->company_name ?? (optional($payroll->company)->company_name ?? '-') }}
                                <br>
                                <small>Company Code:
                                    {{ optional(optional($staff)->company)->company_code ?? (optional($payroll->company)->company_code ?? '-') }}</small><br>
                                <small>Business Unit:
                                    {{ optional(optional($staff)->comp_unit)->unit_name ?? '-' }}</small><br>
                                <small>Work Point:
                                    {{ optional(optional($staff)->workpoint)->work_name ?? (optional($payroll->workpoint)->work_name ?? '-') }}</small><br>
                                <small>Payroll Scope: {{ $payroll->scope_type ?? 'All' }}</small><br>
                                <small>Payslip Date: {{ \Carbon\Carbon::now()->toDateString() }}</small><br>
                                <small>Period: {{ $payroll->period ?? '-' }}</small><br>
                                <small>Status: {{ $payroll->status ?? '-' }}</small>
                            </div>
                        </div>

                        <h6 class="payslip-section-title">MONTH / ATTENDANCE DETAILS</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td style="width:50%">Calendar Days In Month</td>
                                    <td style="width:50%" class="text-right">{{ $calendarDays ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Absent Days</td>
                                    <td class="text-right">{{ number_format($absentDays, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Paid Days</td>
                                    <td class="text-right">{{ number_format($paidDays, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Daily Rate</td>
                                    <td class="text-right">{{ number_format($dailyRate, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="payslip-section-title">EARNINGS</h6>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:50%">Description</th>
                                    <th style="width:50%" class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Basic Salary / Wages</td>
                                    <td class="text-right">{{ number_format($basicSalary, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Allowances</td>
                                    <td class="text-right">{{ number_format($allowances, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Bonus</td>
                                    <td class="text-right">{{ number_format($bonus, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Overtime</td>
                                    <td class="text-right">{{ number_format($overtimePayment, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Less: Absence Deduction</td>
                                    <td class="text-right">{{ number_format($absenceDeduction, 2) }}</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Total Gross</td>
                                    <td class="text-right">{{ number_format($gross, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="payslip-section-title">EMPLOYEE DEDUCTIONS</h6>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:50%">Description</th>
                                    <th style="width:50%" class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>PAYE</td>
                                    <td class="text-right">{{ number_format($paye, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>NSSF Employee 10%</td>
                                    <td class="text-right">{{ number_format($nssfEmployee, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>PSSSF</td>
                                    <td class="text-right">{{ number_format($psssf, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Loan / Advance Deduction</td>
                                    <td class="text-right">{{ number_format($loanDeduction, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>HESLB Deduction</td>
                                    <td class="text-right">{{ number_format($heslbDeduction, 2) }}</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Total Deductions</td>
                                    <td class="text-right">{{ number_format($totalDeductions, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="payslip-section-title">HESLB OUTSTANDING TRACKING</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td style="width:50%">Outstanding Before Payroll</td>
                                    <td style="width:50%" class="text-right">
                                        {{ number_format($line->heslb_balance_before ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>This Month HESLB Deduction</td>
                                    <td class="text-right">{{ number_format($heslbDeduction, 2) }}</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Outstanding After Payroll</td>
                                    <td class="text-right">{{ number_format($line->heslb_balance_after ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="payslip-section-title">EMPLOYER CONTRIBUTIONS / COMPANY COST</h6>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:50%">Description</th>
                                    <th style="width:50%" class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>NSSF Employer 10%</td>
                                    <td class="text-right">{{ number_format($nssfEmployer, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>SDL 3.5%</td>
                                    <td class="text-right">{{ number_format($sdl, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>WCF 0.5%</td>
                                    <td class="text-right">{{ number_format($wcf, 2) }}</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Total Employer Cost</td>
                                    <td class="text-right">{{ number_format($line->employer_cost ?? $employerCosts, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="payslip-section-title">PAY SUMMARY</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr class="font-weight-bold">
                                    <td style="width:50%">NET PAY</td>
                                    <td style="width:50%" class="text-right">{{ number_format($line->net_pay ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>TOTAL PAYROLL COST</td>
                                    <td class="text-right">
                                        {{ number_format($line->total_payroll_cost ?? $gross + $employerCosts, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="payslip-section-title">PREVIOUS MONTH VARIATION</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td style="width:50%">Previous Month Net Pay</td>
                                    <td style="width:50%" class="text-right">{{ number_format($previousNetPay, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Current Month Net Pay</td>
                                    <td class="text-right">{{ number_format($line->net_pay ?? 0, 2) }}</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Net Pay Variation</td>
                                    <td class="text-right">
                                        {{ number_format($netVariation, 2) }}
                                        @if ($netVariation > 0)
                                            <small>(Increase)</small>
                                        @elseif($netVariation < 0)
                                            <small>(Decrease)</small>
                                        @else
                                            <small>(No Change)</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Gross Pay Variation</td>
                                    <td class="text-right">
                                        {{ number_format($grossVariation, 2) }}
                                        @if ($grossVariation > 0)
                                            <small>(Increase)</small>
                                        @elseif($grossVariation < 0)
                                            <small>(Decrease)</small>
                                        @else
                                            <small>(No Change)</small>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        @if (!empty($line->note))
                            <h6 class="payslip-section-title">NOTE</h6>
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <td>{{ $line->note }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <strong>Bank / Account</strong>
                                <p>{{ optional($staff)->accName ?? 'N/A' }} / {{ optional($staff)->accNo ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <div style="margin-top:30px;">__________________________</div>
                                <div>Authorized Signature</div>
                            </div>
                        </div>

                        <div class="mt-3 small text-muted">
                            Prepared by: {{ optional($payroll->preparer)->name ?? optional(auth()->user())->name }} |
                            Approved by: {{ optional($payroll->approver)->name ?? '-' }} |
                            Paid by: {{ optional($payroll->payer)->name ?? '-' }} |
                            Period: {{ $payroll->period ?? '-' }} |
                            Printed at: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
