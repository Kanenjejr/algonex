@extends('layouts.AdminMaster')
@section('content')
    <style>
        .summary-card th {
            width: 55%;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Payroll Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li><a href="{{ route('hr') }}">Human Resource</a></li><span
                    style="font-size:25px"class="fa fa-angle-double-right "></span>
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
    @php
        $lines = $payroll->lines ?? collect();
        $grossTotal = ($payroll->gross_total ?? 0) > 0 ? $payroll->gross_total : $lines->sum('gross');
        $payeTotal = ($payroll->paye_total ?? 0) > 0 ? $payroll->paye_total : $lines->sum('paye');
        $nssfEmployeeTotal = $lines->sum('nssf_employee');
        $nssfEmployerTotal = $lines->sum('nssf_employer');
        $sdlTotal = $lines->sum('sdl');
        $wcfTotal = $lines->sum('wcf');
        $loanTotal = $lines->sum('loan_deduction');
        $heslbTotal = ($payroll->heslb_total ?? 0) > 0 ? $payroll->heslb_total : $lines->sum('heslb_deduction');
        $absenceTotal = $lines->sum('absence_deduction');
        $deductionTotal = $lines->sum('total_deductions');
        $netTotal = ($payroll->net_total ?? 0) > 0 ? $payroll->net_total : $lines->sum('net_pay');
        $employerCostTotal =
            ($payroll->employer_cost_total ?? 0) > 0 ? $payroll->employer_cost_total : $lines->sum('employer_cost');
        if ($employerCostTotal <= 0) {
            $employerCostTotal = $nssfEmployerTotal + $sdlTotal + $wcfTotal;
        }
        $payrollCostTotal =
            ($payroll->payroll_cost_total ?? 0) > 0 ? $payroll->payroll_cost_total : $lines->sum('total_payroll_cost');
        if ($payrollCostTotal <= 0) {
            $payrollCostTotal = $grossTotal + $employerCostTotal;
        }
        $staffCount = $lines->count();
    @endphp
    <div class="col-12">
        <h3 class="mb-2 page-title">Payroll Sheets - {{ $payroll->period }}</h3>
        <div style="position:absolute; top:4.5%; right:1.7%;"><a
                href="{{ route('hr.payrolls.show', encrypt($payroll->id)) }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Available Sheets</h5>
                    </div>
                    <div class="ibox-content">
                        <ul class="list-group">
                            <li class="list-group-item"><a target="_blank"
                                    href="{{ route('hr.payrolls.sheet.net', encrypt($payroll->id)) }}">Net Paysheet / Salary
                                    & Wages Sheet</a></li>
                            <li class="list-group-item"><a target="_blank"
                                    href="{{ route('hr.payrolls.sheet.nssf', encrypt($payroll->id)) }}">NSSF / PSSSF
                                    Contribution Sheet</a></li>
                            <li class="list-group-item"><a target="_blank"
                                    href="{{ route('hr.payrolls.sheet.wcf', encrypt($payroll->id)) }}">WCF Sheet</a></li>
                            <li class="list-group-item"><a target="_blank"
                                    href="{{ route('hr.payrolls.sheet.sdl', encrypt($payroll->id)) }}">SDL Sheet</a></li>
                            <li class="list-group-item"><a target="_blank"
                                    href="{{ route('hr.payrolls.sheet.loans', encrypt($payroll->id)) }}">Loans / Advances
                                    Deduction Sheet</a></li>
                            <li class="list-group-item"><a target="_blank"
                                    href="{{ route('hr.payrolls.sheet.heslb', encrypt($payroll->id)) }}">HESLB Deduction
                                    Sheet</a></li>
                            <li class="list-group-item"><a
                                    href="{{ route('hr.payrolls.show', encrypt($payroll->id)) }}">Full Payroll Details By
                                    Company & Business Unit</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Payroll Summary</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-bordered summary-card">
                            <tr>
                                <th>Period</th>
                                <td>{{ $payroll->period }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $payroll->status }}</td>
                            </tr>
                            <tr>
                                <th>Staff Count</th>
                                <td class="text-right">{{ number_format($staffCount) }}</td>
                            </tr>
                            <tr>
                                <th>Gross Total</th>
                                <td class="text-right">{{ number_format($grossTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>PAYE Total</th>
                                <td class="text-right">{{ number_format($payeTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>NSSF Employee Total</th>
                                <td class="text-right">{{ number_format($nssfEmployeeTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Loans / Advances Total</th>
                                <td class="text-right">{{ number_format($loanTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>HESLB Total</th>
                                <td class="text-right">{{ number_format($heslbTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Absence Deduction Total</th>
                                <td class="text-right">{{ number_format($absenceTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Employee Deductions</th>
                                <td class="text-right">{{ number_format($deductionTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Net Total</th>
                                <td class="text-right">{{ number_format($netTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>NSSF Employer Total</th>
                                <td class="text-right">{{ number_format($nssfEmployerTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>SDL Total</th>
                                <td class="text-right">{{ number_format($sdlTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>WCF Total</th>
                                <td class="text-right">{{ number_format($wcfTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Employer Cost Total</th>
                                <td class="text-right">{{ number_format($employerCostTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Payroll Cost</th>
                                <td class="text-right font-weight-bold">{{ number_format($payrollCostTotal, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
