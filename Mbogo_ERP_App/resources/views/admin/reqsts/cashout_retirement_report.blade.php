@extends('layouts.ReqstMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Requisition & Approvals Dashboard</h2>
        <ol class="breadcrumb" style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('requisition') }}">Requisition & Approvals</a>
            </li>
            <span style="font-size:25px" class="fa fa-angle-double-right"></span>
            <li class="breadcrumb-item active">
                <strong>Cashed-Out & Retirement Report</strong>
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
                        echo $carbon->format('l'); echo " , "; echo $carbon1;
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
@php
    $totalActual = (float) $actualSpendings;
@endphp
<div class="wrapper wrapper-content animated fadeInRight" style="padding:15px;">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox" style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">
                <div class="ibox-title" style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                        <h5 style="margin:0; font-weight:800; color:#fff;">Cashed-Out & Retirement Money Requests</h5>

                        <button type="button" class="btn btn-primary text-white" onclick="printReceipt('reportArea')" style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#1f4fa3;">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <div class="ibox-content">
                    <form method="GET" action="{{ route('reports.money.cashout_retirement') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Start Date</label>
                                <input type="date" name="start_date" value="{{ $start }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>End Date</label>
                                <input type="date" name="end_date" value="{{ $end }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Company</label>
                                <select name="company_id" class="form-control select2_demo_3">
                                    <option value="">All</option>
                                    @foreach($companies as $c)
                                        <option value="{{ $c->id }}" {{ (string)$companyId === (string)$c->id ? 'selected' : '' }}>
                                            {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Working Point</label>
                                <select name="work_point_id" class="form-control select2_demo_3">
                                    <option value="">All</option>
                                    @foreach($workPoints as $wp)
                                        <option value="{{ $wp->id }}" {{ (string)$workPointId === (string)$wp->id ? 'selected' : '' }}>
                                            {{ $wp->work_code }} - {{ $wp->work_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div style="margin-top:12px;">
                            <button class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="reportArea" style="margin-top:16px; background:#fff; padding:22px; border:1px solid #d9e2f2; border-radius:14px; box-shadow:0 8px 24px rgba(23,58,122,.08);">
    {{-- COMPANY HEADER --}}
    <div style="width:100%; margin-bottom:20px;">
        <img src="{{ asset('img/header.png') }}"
             alt="Company Header"
             style="width:100%; display:block;">
    </div>
    <div style="max-width:1000px; margin:0 auto;">
        <div style="max-width:1000px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:16px;">
                <div>
                    <div style="font-size:22px; font-weight:800; color:#173a7a;">Cashed-Out & Retirement Report</div>
                    <div style="font-size:12px; color:#475569;">All requests that have been cashed out or retired</div>
                </div>
                <div style="text-align:right;">
                    <div><strong>Printed:</strong> {{ now()->format('Y-m-d H:i') }}</div>
                    <div><strong>Records:</strong> {{ $moneyRequests->count() }}</div>
                </div>
            </div>
            <div class="row" style="margin-bottom:14px;">
                <div class="col-md-4">
                    <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
                        <strong>Total Approved Amount to Cash Out</strong>
                        <div style="font-size:24px; color:#173a7a; font-weight:800;">{{ number_format($totalApprovedAmount, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
                        <strong>Total Returned Amount</strong>
                        <div style="font-size:24px; color:#173a7a; font-weight:800;">{{ number_format($totalReturnedAmount, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
                        <strong>Actual Spendings</strong>
                        <div style="font-size:24px; color:#173a7a; font-weight:800;">{{ number_format($totalActual, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference No</th>
                            <th>Request Date</th>
                            <th>Details</th>
                            <th>Working Point</th>
                            <th>Requested By</th>
                            <th>Approved Amount to Cash Out</th>
                            <th>Status</th>
                            <th>Returned Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($moneyRequests as $k => $m)
                            <tr>
                                <td>{{ $k + 1 }}</td>
                                <td>{{ $m->RequestNo }}</td>
                                <td>{{ $m->RequestDate ? \Carbon\Carbon::parse($m->RequestDate)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $m->Description ?? '-' }}</td>
                                <td>{{ optional($m->workpoint)->work_code }} - {{ optional($m->workpoint)->work_name }}</td>
                                <td>{{ optional($m->requester)->name ?? '-' }}</td>
                                <td>{{ number_format((float)($m->approved_amount ?? $m->total_amount ?? 0), 2) }}</td>
                                <td>{{ $m->Status }}</td>
                                <td>{{ number_format((float)($m->returned_amount ?? 0), 2) }}</td>
                                <td>
                                    <a href="{{ route('reports.money.cashout_retirement.show', encrypt($m->id)) }}" class="btn btn-sm btn-info">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No cashed-out or retired requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Totals</th>
                            <th>{{ number_format($totalApprovedAmount, 2) }}</th>
                            <th></th>
                            <th>{{ number_format($totalReturnedAmount, 2) }}</th>
                            <th>{{ number_format($totalActual, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
function printReceipt(ele) {
    var content = document.getElementById(ele);
    if (!content) return alert('Nothing to print');

    var pri = window.open('', '_blank', 'height=842,width=595');
    var doc = pri.document.open();

    var style = `<style>
        @page{margin:18mm}
        *{box-sizing:border-box;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            color:#000;
            margin:0;
            padding:0;
            font-size:12.5px
        }
        table{border-collapse:collapse;width:100%}
        table th, table td{border:1px solid #d9e2f2;padding:7px;vertical-align:top}
        img{max-width:100%;height:auto}
    </style>`;

    doc.write('<html><head><title>Cashed-Out & Retirement Report</title>' + style + '</head><body>');
    doc.write(content.innerHTML);
    doc.write('</body></html>');
    doc.close();

    pri.focus();
    setTimeout(function() { pri.print(); }, 400);
}
</script>

@endsection