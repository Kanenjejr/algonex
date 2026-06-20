@extends('layouts.ReqstMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Requisition & Approvals Dashboard</h2>
        <ol class="breadcrumb" style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('requisition') }}">Requisition & Approvals</a>
            </li>
            <span style="font-size:25px" class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Requisition Report</strong>
            </li>
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

@can('View-Requisition-Reports')
<div class="col-12 mb-3">
    <h3 class="mb-2 page-title">Requisition Report</h3>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchModal">
        <i class="fa fa-search"></i> Search
    </button>

    <button onclick="printReceipt('printArea')" class="btn btn-sm btn-primary float-right">
        <i class="fa fa-print"></i> Print
    </button>
</div>

<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="GET" action="{{ route('reports.requisitions') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">Search Filters</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="{{ $start ?? '' }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="{{ $end ?? '' }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Company</label>
                        <select name="company_id" class="form-control">
                            <option value="">-- All --</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" @selected((string)request('company_id') === (string)$c->id)>{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <select name="work_point_id" class="form-control">
                            <option value="">-- All --</option>
                            @foreach($workPoints as $wp)
                                <option value="{{ $wp->id }}" @selected((string)request('work_point_id') === (string)$wp->id)>{{ $wp->work_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="printArea" class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-md-3">
            <div class="ibox">
                <div class="ibox-title bg-success"><h5>Total Requests</h5></div>
                <div class="ibox-content"><strong style="font-size:28px">{{ $moneyRequests->count() ?? 0 }}</strong></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ibox">
                <div class="ibox-title bg-primary"><h5>Approved</h5></div>
                <div class="ibox-content"><strong style="font-size:28px">{{ $moneyRequestsApprovedTotal ?? 0 }}</strong></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ibox">
                <div class="ibox-title bg-warning"><h5>Cashed Out</h5></div>
                <div class="ibox-content"><strong style="font-size:28px">{{ $moneyRequestsCashedOutTotal ?? 0 }}</strong></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ibox">
                <div class="ibox-title bg-info"><h5>Total Amount</h5></div>
                <div class="ibox-content"><strong style="font-size:28px">{{ number_format($moneyRequestsGrandTotal ?? 0, 2) }}</strong></div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title bg-success">
                    <h5>Approved vs Unapproved</h5>
                </div>
                <div class="ibox-content">
                    <div style="height:260px;">
                        <canvas id="approvedVsUnapprovedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title bg-warning">
                    <h5>Money Requests (last 6 months)</h5>
                </div>
                <div class="ibox-content">
                    <div style="height:260px;">
                        <canvas id="moneyMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(($companies->count() ?? 0) > 1)
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title bg-info">
                    <h5>Money Requests Per Company</h5>
                </div>
                <div class="ibox-content">
                    <div style="height:300px;">
                        <canvas id="companyMoneyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="ibox mt-4">
        <div class="ibox-title bg-success">
            <h5>Money Requisitions</h5>
        </div>
        <div class="ibox-content table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reference No</th>
                        <th>Request Date</th>
                        <th>Company</th>
                        <th>Unit</th>
                        <th>Site</th>
                        <th>Details</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($moneyRequests as $k => $r)
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
                                $statusClass = 'badge badge-danger-light';
                                break;
                            case 'Rejected':
                                $statusClass = 'badge badge-danger';
                                break;
                        }
                    @endphp
                    <tr>
                        <td>{{ $k + 1 }}</td>
                        <td>{{ $r->RequestNo ?? '-' }}</td>
                        <td>{{ $r->RequestDate ? \Carbon\Carbon::parse($r->RequestDate)->format('Y-m-d') : '-' }}</td>
                        <td>{{ optional($r->company)->company_name ?? '-' }}</td>
                        <td>{{ optional($r->unit)->unit_name ?? '-' }}</td>
                        <td>{{ optional($r->workpoint)->work_name ?? '-' }}</td>
                        <td>
                           {{ $r->PayeeName }}<br>
                           <small>{{ $r->Description }}</small>
                        </td>
                        <td>{{ $r->remarks ?? '-' }}</td>
                        <td class="text-right">{{ number_format($r->total_amount ?? 0, 2) }}</td>
                        <td><span class="{{ $statusClass }}">{{ $r->Status }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No money requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function printReceipt(id) {
        let content = document.getElementById(id).innerHTML;
        let win = window.open('', '', 'height=900,width=1200');
        win.document.write('<html><head><title>Requisition Report</title>');
        win.document.write('<style>table{width:100%;border-collapse:collapse;} th,td{border:1px solid #000;padding:5px;}</style>');
        win.document.write('</head><body>');
        win.document.write(content);
        win.document.write('</body></html>');
        win.document.close();
        win.print();
    }

    const labels = @json($chartLabels ?? []);
    const moneyData = @json($moneyChartData ?? []);

    new Chart(document.getElementById('approvedVsUnapprovedChart'), {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Unapproved'],
            datasets: [{
                data: [
                    {{ $moneyApprovedLike ?? 0 }},
                    {{ $moneyUnapproved ?? 0 }}
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    new Chart(document.getElementById('moneyMonthlyChart'), {
        type: 'line',
        data: {
            labels: labels,
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

    @if(($companies->count() ?? 0) > 1)
    new Chart(document.getElementById('companyMoneyChart'), {
        type: 'bar',
        data: {
            labels: @json($companies->map(fn($c) => $c->company_name)->values()->all() ?? []),
            datasets: [{
                label: 'Requests per Company',
                data: @json($moneyRequests->groupBy('company_id')->map->count()->values()->all() ?? [])
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