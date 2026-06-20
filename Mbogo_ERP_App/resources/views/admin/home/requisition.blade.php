@extends('layouts.ReqstMaster')
@section('content')

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Requisition & Approvals Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('requisition') }}">Requisition & Approvals </a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Dashboard</strong></li>
            </ol>
        </div>

        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        @php
                            $carbon = \Carbon\Carbon::now();
                            $carbon1 = \Carbon\Carbon::now()->toDateString();
                        @endphp
                        {{ $carbon->format('l') }}, {{ $carbon1 }}
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
            setInterval(change_time, 1000);
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

    @can('Requisition-Modules')
        <div class="col-12 mb-3">
            <h3 class="page-title">Requisition Dashboard</h3>
        </div>

        <div class="row">

            <div class="col-lg-3">
                <a href="{{ route('reports.requisition.book') }}" style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-success">
                            <h5>Total Money Requests</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ $money_total ?? 0 }}</strong>
                            <div><small>All requests except deleted</small></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3">
                <a href="{{ route('reports.requisition.book', ['status' => 'approved']) }}"
                    style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-primary">
                            <h5>Approved</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ $money_approved_like ?? 0 }}</strong>
                            <div><small>Approved / Cashed-out / Retired</small></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3">
                <a href="{{ route('moneyrequest.approved') }}" style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-warning">
                            <h5>Cashed Out</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ $money_cashed_out ?? 0 }}</strong>
                            <div><small>Waiting retirement</small></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3">
                <a href="{{ route('reports.requisition.book') }}" style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-info">
                            <h5>Total Amount</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ number_format($money_total_amount ?? 0, 2) }}</strong>
                            <div><small>Rejected / Declined excluded</small></div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="row">

            <div class="col-lg-3">
                <a href="{{ route('moneyrequest.pending') }}" style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-warning">
                            <h5>Pending Verification</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ $money_pending ?? 0 }}</strong>
                            <div><small>{{ number_format($money_pending_amount ?? 0, 2) }}</small></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3">
                <a href="{{ route('moneyrequest.verified') }}" style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-info">
                            <h5>Need Approval</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ $money_verified ?? 0 }}</strong>
                            <div><small>{{ number_format($money_verified_amount ?? 0, 2) }}</small></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3">
                <a href="{{ route('reports.requisition.book', ['status' => 'approved']) }}"
                    style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-primary">
                            <h5>Approved Amount</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ number_format($money_approved_amount ?? 0, 2) }}</strong>
                            <div><small>Click opens approved book only</small></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3">
                <a href="{{ route('moneyrequest.rejected') }}" style="text-decoration:none;color:inherit;">
                    <div class="ibox">
                        <div class="ibox-title bg-danger">
                            <h5>Rejected / Declined</h5>
                        </div>
                        <div class="ibox-content">
                            <strong style="font-size:28px">{{ $money_rejected ?? 0 }}</strong>
                            <div><small>{{ number_format($money_rejected_amount ?? 0, 2) }} not included in total</small></div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="row mt-3">

            <div class="col-md-4">
                <div class="ibox">
                    <div class="ibox-title bg-success">
                        <h5>Money Request Status</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row text-center">
                            <div class="col-4">
                                <a href="{{ route('moneyrequest.pending') }}" style="text-decoration:none;color:inherit;">
                                    <div>Pending</div><strong>{{ $money_pending ?? 0 }}</strong>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('moneyrequest.verified') }}" style="text-decoration:none;color:inherit;">
                                    <div>Verified</div><strong>{{ $money_verified ?? 0 }}</strong>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('reports.requisition.book', ['status' => 'approved']) }}"
                                    style="text-decoration:none;color:inherit;">
                                    <div>Approved</div><strong>{{ $money_approved ?? 0 }}</strong>
                                </a>
                            </div>
                        </div>

                        <div class="row text-center mt-3">
                            <div class="col-4">
                                <a href="{{ route('moneyrequest.approved') }}" style="text-decoration:none;color:inherit;">
                                    <div>Cashed Out</div><strong>{{ $money_cashed_out ?? 0 }}</strong>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('reports.requisition.book', ['status' => 'approved']) }}"
                                    style="text-decoration:none;color:inherit;">
                                    <div>Retired</div><strong>{{ $money_retired ?? 0 }}</strong>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('moneyrequest.rejected') }}" style="text-decoration:none;color:inherit;">
                                    <div>Rejected</div><strong>{{ $money_rejected ?? 0 }}</strong>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox">
                    <div class="ibox-title bg-primary">
                        <h5>Approved vs Unapproved</h5>
                    </div>
                    <div class="ibox-content">
                        <div style="height:260px;">
                            <canvas id="approvedVsUnapprovedChart"></canvas>
                        </div>
                        <div class="mt-3 text-center">
                            <div><strong>Approved:</strong> {{ $money_approved_like ?? 0 }}</div>
                            <div><strong>Unapproved:</strong> {{ $money_unapproved ?? 0 }}</div>
                            <div><small>Rejected / Declined excluded from unapproved</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Monthly Money Requests</h5>
                    </div>
                    <div class="ibox-content">
                        <div style="height:260px;">
                            <canvas id="moneyMonthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        @if ($showCompanyChart ?? false)
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="ibox">
                        <div class="ibox-title bg-warning">
                            <h5>Money Requests Per Company</h5>
                        </div>
                        <div class="ibox-content">
                            <div style="height:320px;">
                                <canvas id="companyMoneyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="ibox">
                    <div class="ibox-title bg-success">
                        <h5>Recent Money Requests</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ref No</th>
                                        <th>Date</th>
                                        <th>Details</th>
                                        <th>Purpose</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($recentMoney ?? [] as $k => $r)
                                        @php
                                            $statusClass = 'badge badge-secondary';

                                            switch ($r->Status) {
                                                case 'Pending':
                                                    $statusClass = 'badge badge-warning';
                                                    break;
                                                case 'Verified':
                                                    $statusClass = 'badge badge-info';
                                                    break;
                                                case 'Approved':
                                                    $statusClass = 'badge badge-primary';
                                                    break;
                                                case 'Cashed-out':
                                                    $statusClass = 'badge badge-success';
                                                    break;
                                                case 'Retired':
                                                    $statusClass = 'badge badge-dark';
                                                    break;
                                                case 'Declined':
                                                case 'Rejected':
                                                    $statusClass = 'badge badge-danger';
                                                    break;
                                            }
                                        @endphp

                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $r->RequestNo ?? '-' }}</td>
                                            <td>{{ $r->RequestDate ? \Carbon\Carbon::parse($r->RequestDate)->format('Y-m-d') : '-' }}
                                            </td>
                                            <td>
                                                {{ $r->PayeeName }}<br>
                                                <small>{{ $r->Description }}</small>
                                            </td>
                                            <td>{{ $r->remarks ?? '-' }}</td>
                                            <td class="text-right">
                                                {{ number_format($r->total_amount ?? 0, 2) }}
                                            </td>
                                            <td>
                                                <span class="{{ $statusClass }}">
                                                    {{ $r->Status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No recent money requests.</td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            const chartLabels = @json($chartLabels ?? []);
            const moneyData = @json($moneyChartData ?? []);

            const approvedVsUnapprovedCtx = document.getElementById('approvedVsUnapprovedChart');

            new Chart(approvedVsUnapprovedCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Unapproved'],
                    datasets: [{
                        data: [{{ $money_approved_like ?? 0 }}, {{ $money_unapproved ?? 0 }}]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            const moneyMonthlyCtx = document.getElementById('moneyMonthlyChart');

            new Chart(moneyMonthlyCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Money Requests',
                        data: moneyData,
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

            @if ($showCompanyChart ?? false)
                const companyMoneyCtx = document.getElementById('companyMoneyChart');

                new Chart(companyMoneyCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($companyChartLabels ?? []),
                        datasets: [{
                            label: 'Requests per Company',
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
            @endif
        </script>
    @endcan
@endsection
