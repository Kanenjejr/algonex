@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Microfinance Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('micro.dashboard') }}">Microfinance</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Dashboard</strong>
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

        .bg-m1 {
            background: linear-gradient(135deg, #173a7a, #214f9c);
        }

        .bg-m2 {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
        }

        .bg-m3 {
            background: linear-gradient(135deg, #b45309, #f59e0b);
        }

        .bg-m4 {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        }

        .bg-m5 {
            background: linear-gradient(135deg, #be123c, #f43f5e);
        }

        .bg-m6 {
            background: linear-gradient(135deg, #166534, #22c55e);
        }

        .chart-box {
            height: 320px;
        }
    </style>

    <div class="col-12">
        <h3 class="mb-2 page-title">Microfinance Dashboard</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m1">
                        <h5>Total Applications</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($totalApplications ?? 0) }}</strong></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m2">
                        <h5>Total Approved</h5>
                    </div>
                    <div class="ibox-content"><strong class="card-count">{{ number_format($totalApproved ?? 0) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m3">
                        <h5>Active Loans</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($totalActiveLoans ?? 0) }}</strong></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m5">
                        <h5>Overdue Loans</h5>
                    </div>
                    <div class="ibox-content"><strong class="card-count">{{ number_format($overdueLoans ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m4">
                        <h5>Loan Amount</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($totalLoanAmount ?? 0, 2) }}</strong></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m6">
                        <h5>Returned Amount</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($returnedAmount ?? 0, 2) }}</strong></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m3">
                        <h5>Penalty Amount</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($penaltyAmount ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m1">
                        <h5>Reminder Charges</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($reminderCharges ?? 0, 2) }}</strong></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m5">
                        <h5>Office Cost</h5>
                    </div>
                    <div class="ibox-content"><strong class="card-count">{{ number_format($officeCost ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m2">
                        <h5>Applicant Recoverable Cost</h5>
                    </div>
                    <div class="ibox-content"><strong
                            class="card-count">{{ number_format($recoverableCost ?? 0, 2) }}</strong></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m4">
                        <h5>Other Income</h5>
                    </div>
                    <div class="ibox-content"><strong class="card-count">{{ number_format($otherIncome ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m1">
                        <h5>Applications By Status</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="chart-box"><canvas id="statusChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m2">
                        <h5>Monthly Applications</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-m3">
                        <h5>Approved Amount vs Company</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="chart-box"><canvas id="companyChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: @json($statusLabels ?? []),
                datasets: [{
                    data: @json($statusData ?? [])
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: @json($monthlyLabels ?? []),
                datasets: [{
                    label: 'Applications',
                    data: @json($monthlyData ?? []),
                    fill: false,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(document.getElementById('companyChart'), {
            type: 'bar',
            data: {
                labels: @json($companyChartLabels ?? []),
                datasets: [{
                    label: 'Approved Amount',
                    data: @json($companyChartData ?? [])
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
