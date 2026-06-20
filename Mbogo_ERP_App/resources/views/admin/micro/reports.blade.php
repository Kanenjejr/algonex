@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Microfinance Reports</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Reports</strong></li>
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
                            </tr>
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

    <style>
        .smart-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .smart-card .ibox-title {
            color: #fff !important;
            border: 0 !important;
        }

        .smart-card .ibox-title h5 {
            color: #fff !important;
            font-weight: 700;
        }

        .card-count {
            font-size: 28px;
            font-weight: 800;
        }

        .bg-r1 {
            background: linear-gradient(135deg, #173a7a, #214f9c);
        }

        .bg-r2 {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
        }

        .bg-r3 {
            background: linear-gradient(135deg, #b45309, #f59e0b);
        }

        .bg-r4 {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        }

        .bg-r5 {
            background: linear-gradient(135deg, #be123c, #f43f5e);
        }

        .bg-r6 {
            background: linear-gradient(135deg, #166534, #22c55e);
        }
    </style>

    <div class="col-12">
        <h3 class="mb-2 page-title">Microfinance Reports</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r1">
                        <h5>Total Loan Amount</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['loan_amount'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r2">
                        <h5>Returned Amount</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['returned_amount'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r3">
                        <h5>Overdue Loans</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['overdue_loans'] ?? 0) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r4">
                        <h5>Penalty Amount</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['penalty_amount'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r5">
                        <h5>Office Cost</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['office_cost'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r6">
                        <h5>Recoverable Cost</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['recoverable_cost'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r2">
                        <h5>Other Income</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['other_income'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r1">
                        <h5>Reminder Sent</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['reminder_sent'] ?? 0) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r3">
                        <h5>Reminder Cost</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['reminder_cost'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r4">
                        <h5>Total Income</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['income_total'] ?? 0, 2) }}</strong></div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-r5">
                        <h5>Loss Unreturned</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($report['loss_unreturned'] ?? 0, 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endsection
