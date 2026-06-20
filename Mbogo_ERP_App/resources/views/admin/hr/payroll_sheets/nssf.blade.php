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

        .payroll-sheet-table {
            width: 100%;
            font-size: 12px;
        }

        .payroll-sheet-table th,
        .payroll-sheet-table td {
            padding: 4px 6px !important;
            vertical-align: middle !important;
        }

        .company-row {
            background: #d9edf7;
            font-weight: 800;
        }

        .workpoint-row {
            background: #f7f7f7;
            font-weight: 800;
        }

        .subtotal-row {
            background: #f5f5f5;
            font-weight: 800;
        }

        .grand-total-row {
            background: yellow;
            font-weight: 900;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
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
            .ibox-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .payroll-sheet-table {
                font-size: 9px !important;
            }

            .payroll-sheet-table th,
            .payroll-sheet-table td {
                padding: 2px 3px !important;
            }
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Payroll Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('hr') }}">Human Resource</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Staff/User Payroll Sheets</strong></li>
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
        <h3 class="mb-2 page-title">NSSF / PSSSF Contribution Sheet — {{ $payroll->period }}</h3>
        <button onclick="printReceipt('form1')" class="btn btn-sm btn-primary float-right mr-2"><i class="fa fa-print"></i>
            Print Report</button>
        <a href="{{ route('hr.payrolls.reports', encrypt($payroll->id)) }}" class="btn btn-secondary">Back</a>
    </div>

    @php
        $sheetLines = $lines ?? collect();
        $companiesGrouped = $sheetLines->groupBy(function ($line) {
            $companyName = optional(optional($line->user)->company)->company_name ?: 'NO COMPANY';
            $companyCode = optional(optional($line->user)->company)->company_code;
            return trim($companyName . ($companyCode ? ' (' . $companyCode . ')' : ''));
        });
    @endphp

    <div id="form1" class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox-content payroll-print-area">
            <img src="{{ asset('img/header.png') }}" alt="Company Header" class="payroll-header-img">
            <div class="text-center mb-3">
                <h3 style="font-weight:bold;">NSSF / PSSSF CONTRIBUTION SHEET</h3>
                <h4>{{ strtoupper(\Carbon\Carbon::parse($payroll->period . '-01')->format('F Y')) }}</h4>
                <p>Status: <strong>{{ $payroll->status }}</strong> | Scope:
                    <strong>{{ $payroll->scope_type ?? 'All' }}</strong> | Prepared By:
                    <strong>{{ optional($payroll->preparer)->name ?? '-' }}</strong>
                </p>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered payroll-sheet-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff Name</th>
                            <th>NSSF No</th>
                            <th class="text-right">Gross Salary</th>
                            <th class="text-right">NSSF Employee</th>
                            <th class="text-right">NSSF Employer</th>
                            <th class="text-right">PSSSF</th>
                            <th class="text-right">Total Contribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandGross=$grandEmployee=$grandEmployer=$grandPsssf=$grandTotal=0; @endphp
                        @foreach ($companiesGrouped as $companyName => $companyLines)
                            <tr class="company-row">
                                <td colspan="8">COMPANY SITE: {{ strtoupper($companyName) }}</td>
                            </tr>
                            @php
                                $workPointsGrouped = $companyLines->groupBy(function ($line) {
                                    return optional(optional($line->user)->workpoint)->work_name ?? 'NO WORK POINT';
                                });
                            @endphp
                            @foreach ($workPointsGrouped as $workPointName => $workPointLines)
                                <tr class="workpoint-row">
                                    <td colspan="8">WORK POINT: {{ strtoupper($workPointName) }}</td>
                                </tr>
                                @php $subGross=$subEmployee=$subEmployer=$subPsssf=$subTotal=0; @endphp
                                @foreach ($workPointLines as $k => $l)
                                    @php
                                        $employee = (float) ($l->nssf_employee ?? 0);
                                        $employer = (float) ($l->nssf_employer ?? 0);
                                        $psssf = (float) ($l->psssf ?? 0);
                                        $rowTotal = $employee + $employer + $psssf;
                                        $subGross += (float) ($l->gross ?? 0);
                                        $subEmployee += $employee;
                                        $subEmployer += $employer;
                                        $subPsssf += $psssf;
                                        $subTotal += $rowTotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ optional($l->user)->name ?? '-' }}</td>
                                        <td>{{ optional($l->user)->nssfNo ?? (optional($l->user)->nssf_no ?? '-') }}</td>
                                        <td class="text-right">{{ number_format($l->gross ?? 0, 2) }}</td>
                                        <td class="text-right">{{ number_format($employee, 2) }}</td>
                                        <td class="text-right">{{ number_format($employer, 2) }}</td>
                                        <td class="text-right">{{ number_format($psssf, 2) }}</td>
                                        <td class="text-right">{{ number_format($rowTotal, 2) }}</td>
                                    </tr>
                                @endforeach
                                @php
                                    $grandGross += $subGross;
                                    $grandEmployee += $subEmployee;
                                    $grandEmployer += $subEmployer;
                                    $grandPsssf += $subPsssf;
                                    $grandTotal += $subTotal;
                                @endphp
                                <tr class="subtotal-row">
                                    <td colspan="3" class="text-right">TOTAL FOR {{ strtoupper($workPointName) }}</td>
                                    <td class="text-right">{{ number_format($subGross, 2) }}</td>
                                    <td class="text-right">{{ number_format($subEmployee, 2) }}</td>
                                    <td class="text-right">{{ number_format($subEmployer, 2) }}</td>
                                    <td class="text-right">{{ number_format($subPsssf, 2) }}</td>
                                    <td class="text-right">{{ number_format($subTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        <tr class="grand-total-row">
                            <td colspan="3" class="text-right">GENERAL TOTAL</td>
                            <td class="text-right">{{ number_format($grandGross, 2) }}</td>
                            <td class="text-right">{{ number_format($grandEmployee, 2) }}</td>
                            <td class="text-right">{{ number_format($grandEmployer, 2) }}</td>
                            <td class="text-right">{{ number_format($grandPsssf, 2) }}</td>
                            <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
