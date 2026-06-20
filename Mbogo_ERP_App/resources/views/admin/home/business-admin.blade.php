@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Business-Administration Dashboard</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('business-admin') }}">Business Administration</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
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
            if (curr_hour > 24)
                curr_hour = curr_hour - 24;
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
            margin-bottom: 0;
        }

        .smart-card .ibox-content {
            background: #fff;
        }

        .card-count {
            font-size: 28px;
            font-weight: 800;
        }

        .bg-erp-1 {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
        }

        .bg-erp-2 {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .bg-erp-3 {
            background: linear-gradient(135deg, #d97706, #f59e0b);
        }

        .bg-erp-4 {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        }

        .bg-erp-5 {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .bg-erp-6 {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
        }

        .chart-box {
            height: 320px;
            position: relative;
        }

        .chart-box-sm {
            height: 300px;
            position: relative;
        }

        .chart-toolbar {
            margin-bottom: 15px;
            text-align: right;
        }

        .chart-toolbar select {
            width: 140px;
            display: inline-block;
        }

        .page-title {
            font-weight: 800;
            margin-bottom: 18px;
        }
    </style>

    @can('Administration-Modules')
        <div class="col-12 mb-3">
            <h3 class="page-title">Administration Dashboard</h3>
        </div>

        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-erp-1">
                        <h5>Total Companies</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count"
                            title="{{ \Illuminate\Support\Str::limit(implode(', ', $companyNames ?? []), 800) }}">
                            {{ $totalCompanies ?? 0 }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-erp-5">
                        <h5>Business Units</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count"
                            title="{{ \Illuminate\Support\Str::limit(implode(', ', $businessUnitNames ?? []), 800) }}">
                            {{ $totalBusinessUnits ?? 0 }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-erp-2">
                        <h5>Work Points</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count"
                            title="{{ \Illuminate\Support\Str::limit(implode(', ', $workPointNames ?? []), 800) }}">
                            {{ $totalWorkPoints ?? 0 }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-erp-3">
                        <h5>Total Users</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count"
                            title="{{ \Illuminate\Support\Str::limit(implode(', ', $userNames ?? []), 800) }}">
                            {{ $totalUsers ?? 0 }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-erp-4">
                        <h5>Departments</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count"
                            title="{{ \Illuminate\Support\Str::limit(implode(', ', $departmentNames ?? []), 800) }}">
                            {{ $totalDepartments ?? 0 }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-erp-6">
                        <h5>Sections</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count"
                            title="{{ \Illuminate\Support\Str::limit(implode(', ', $sectionNames ?? []), 800) }}">
                            {{ $totalSections ?? 0 }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-erp-1">
                        <h5>Companies vs Work Points</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="chart-toolbar">
                            <select id="adminChartType" class="form-control">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                                <option value="doughnut">Doughnut</option>
                            </select>
                        </div>
                        <div class="chart-box">
                            <canvas id="adminChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan


    @can('Accounting-Modules')
        <div class="col-12 mb-3">
            <h3 class="page-title">Accounting Dashboard</h3>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-warning">
                        <h5>Pending Requests</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count">{{ $totalPendingRequests ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-info">
                        <h5>Verified Only</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count">{{ $totalVerifiedRequests ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-success">
                        <h5>Approved Requests</h5>
                    </div>
                    <div class="ibox-content">
                        <strong class="card-count">{{ $totalApprovedRequests ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-warning">
                        <h5>Pending vs Company</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="chart-toolbar">
                            <select id="pendingChartType" class="form-control">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                                <option value="doughnut">Doughnut</option>
                            </select>
                        </div>
                        <div class="chart-box-sm">
                            <canvas id="pendingCompanyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-info">
                        <h5>Verified vs Company</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="chart-toolbar">
                            <select id="verifiedChartType" class="form-control">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                                <option value="doughnut">Doughnut</option>
                            </select>
                        </div>
                        <div class="chart-box-sm">
                            <canvas id="verifiedCompanyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-success">
                        <h5>Approved vs Company</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="chart-toolbar">
                            <select id="approvedChartType" class="form-control">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                                <option value="doughnut">Doughnut</option>
                            </select>
                        </div>
                        <div class="chart-box-sm">
                            <canvas id="approvedCompanyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endcan

    @if (!$canViewAdministration && !$canViewAccounting)
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="ibox smart-card">
                    <div class="ibox-title bg-danger">
                        <h5>No Dashboard Access</h5>
                    </div>
                    <div class="ibox-content">
                        <strong>You do not have permission to view this dashboard.</strong>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function buildSmartChart(canvasId, type, labels, data, labelText) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) return null;

            var ctx = canvas.getContext('2d');

            return new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelText,
                        data: data,
                        backgroundColor: [
                            '#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444',
                            '#14b8a6', '#f97316', '#84cc16', '#ec4899', '#0ea5e9'
                        ],
                        borderColor: [
                            '#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444',
                            '#14b8a6', '#f97316', '#84cc16', '#ec4899', '#0ea5e9'
                        ],
                        borderWidth: 2,
                        fill: false,
                        tension: 0.25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: (type === 'pie' || type === 'doughnut') ? {} : {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function enableChartSwitcher(selectId, canvasId, labels, data, labelText, defaultType) {
            var instance = buildSmartChart(canvasId, defaultType, labels, data, labelText);
            var selector = document.getElementById(selectId);

            if (selector) {
                selector.addEventListener('change', function() {
                    if (instance) {
                        instance.destroy();
                    }
                    instance = buildSmartChart(canvasId, this.value, labels, data, labelText);
                });
            }
        }

        @can('Administration-Modules')
            enableChartSwitcher(
                'adminChartType',
                'adminChart',
                @json($adminChartLabels ?? []),
                @json($adminChartData ?? []),
                'Work Points',
                'bar'
            );
        @endcan

        @can('Accounting-Modules')
            enableChartSwitcher(
                'approvedChartType',
                'approvedCompanyChart',
                @json($accountingCompanyLabels ?? []),
                @json($approvedByCompany ?? []),
                'Approved Requests',
                'bar'
            );

            enableChartSwitcher(
                'pendingChartType',
                'pendingCompanyChart',
                @json($accountingCompanyLabels ?? []),
                @json($pendingByCompany ?? []),
                'Pending Requests',
                'bar'
            );

            enableChartSwitcher(
                'verifiedChartType',
                'verifiedCompanyChart',
                @json($accountingCompanyLabels ?? []),
                @json($verifiedByCompany ?? []),
                'Verified Only Requests',
                'bar'
            );
        @endcan
    </script>
@endsection
