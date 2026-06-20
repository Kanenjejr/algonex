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
                    <strong>Requisition Book</strong>
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
                            <tr>
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
        $statusBadge = function ($status) {
            $class = 'badge badge-secondary';

            switch ($status) {
                case 'Pending':
                    $class = 'badge badge-warning';
                    break;
                case 'Verified':
                    $class = 'badge badge-info';
                    break;
                case 'Approved':
                    $class = 'badge badge-primary';
                    break;
                case 'Cashed-out':
                    $class = 'badge badge-success';
                    break;
                case 'Retired':
                    $class = 'badge badge-dark';
                    break;
                case 'Declined':
                    $class = 'badge badge-danger-light';
                    break;
                case 'Rejected':
                    $class = 'badge badge-danger';
                    break;
            }

            return $class;
        };
    @endphp

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox"
                    style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">
                    <div class="ibox-title"
                        style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <h5 style="margin:0; font-weight:800; color:#fff;">Requisition Book</h5>
                            <div style="display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;">
                                <button type="button" class="btn btn-primary text-white"
                                    onclick="printReceipt('reportArea')"
                                    style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#1f4fa3;">
                                    <i class="fa fa-print"></i> Print
                                </button>

                                <a href="{{ route('reports.requisition.book.excel', ['work_point_id' => $workPointId, 'start_date' => $start, 'end_date' => $end]) }}"
                                    class="btn btn-success text-white"
                                    style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12);">
                                    <i class="fa fa-file-excel-o"></i> Export Excel
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="ibox-content">
                        <form method="GET" action="{{ route('reports.requisition.book') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Working Point</label>
                                    <select name="work_point_id" class="form-control select2_demo_3">
                                        <option value="">All</option>
                                        @foreach ($workPoints as $wp)
                                            <option value="{{ $wp->id }}"
                                                {{ (string) $workPointId === (string) $wp->id ? 'selected' : '' }}>
                                                {{ $wp->work_code }} - {{ $wp->work_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" value="{{ $start }}"
                                        class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" value="{{ $end }}" class="form-control">
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

        <div id="reportArea" style="margin-top:16px; background:#fff; padding:18px; border:1px solid #d9e2f2; border-radius:14px; box-shadow:0 8px 24px rgba(23,58,122,.08);">
            {{-- COMPANY HEADER --}}
        <div style=" width:100%;
            margin:0;
            padding:0;
            overflow:hidden;
        ">
            <img src="{{ asset('img/header.png') }}"
                alt="Company Header"
                style="
                    display:block;
                    width:100%;
                    height:auto;
                    margin:0;
                    padding:0;
                ">
        </div>

          <div style="max-width:100%; margin:0 auto;">
                <div
                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:14px;">
                    <div>
                        <div style="font-size:22px; font-weight:800; color:#173a7a;">Requisition Book</div>
                        <div style="font-size:12px; color:#475569;">All money requisitions with full accounting details
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div><strong>Printed:</strong> {{ now()->format('Y-m-d H:i') }}</div>
                        <div><strong>Records:</strong> {{ $moneyRequests->count() }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" style="font-size:11px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ref No.</th>
                                <th>Payee</th>
                                <th>ACCOUNT CODE</th>
                                <th>ACCOUNT DESCRIPTION</th>
                                <th>SUB ACCOUNT CODE</th>
                                <th>SUB ACCOUNT DESCRIPTION</th>
                                <th>COMPANY CODE</th>
                                <th>COMPANY DESCRIPTION</th>
                                <th>BUSINESS CODE</th>
                                <th>BUSINESS DESCRIPTION</th>
                                <th>DEPARTMENT CODE</th>
                                <th>DEPARTMENT DESCRIPTION</th>
                                <th>SECTION CODE</th>
                                <th>SECTION DESCRIPTION</th>
                                <th>CODE</th>
                                <th>LOCATION</th>
                                <th>MEMO</th>
                                <th>AMOUNT</th>
                                <th>Raised By</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($moneyRequests as $m)
                                <tr>
                                    <td>{{ $m->RequestDate ? \Carbon\Carbon::parse($m->RequestDate)->format('d.m.Y') : '-' }}
                                    </td>
                                    <td>{{ $m->RequestNo }}</td>
                                    <td>{{ $m->PayeeName }}</td>

                                    <td>{{ $m->accounting_code_6 ?? '-' }}</td>
                                    <td>{{ $m->accounting_name_6 ?? '-' }}</td>

                                    <td>{{ $m->sub_accounting_code_8 ?? '-' }}</td>
                                    <td>{{ $m->sub_accounting_name_8 ?? '-' }}</td>

                                    <td>{{ optional($m->company)->company_code ?? '-' }}</td>
                                    <td>{{ optional($m->company)->company_name ?? '-' }}</td>

                                    <td>{{ optional($m->unit)->unit_code ?? '-' }}</td>
                                    <td>{{ optional($m->unit)->unit_name ?? '-' }}</td>

                                    <td>{{ optional($m->department)->depCode ?? '-' }}</td>
                                    <td>{{ optional($m->department)->depName ?? '-' }}</td>

                                    <td>{{ optional($m->section)->secCode ?? '-' }}</td>
                                    <td>{{ optional($m->section)->secName ?? '-' }}</td>

                                    <td>{{ optional($m->workpoint)->work_code ?? '-' }}</td>
                                    <td>{{ optional($m->workpoint)->work_name ?? '-' }}</td>

                                    <td>{{ $m->remarks ?? '-' }}</td>
                                    <td style="text-align:right;">{{ number_format((float) $m->total_amount, 2) }}</td>
                                    <td>{{ optional($m->requester)->name ?? '-' }}</td>
                                    <td>
                                        <span class="{{ $statusBadge($m->Status) }}">
                                            {{ $m->Status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="21" class="text-center text-muted">No requisitions found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="18" style="text-align:right;">Grand Total</th>
                                <th style="text-align:right;">{{ number_format((float) $grandTotal, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            if ($('.select2_demo_3').length) {
                $('.select2_demo_3').select2({
                    width: '100%',
                    theme: 'bootstrap4'
                });
            }
        });

        function printReceipt(ele) {
            var content = document.getElementById(ele);
            if (!content) return alert('Nothing to print');

            var pri = window.open('', '_blank', 'height=842,width=595');
            var doc = pri.document.open();

            var style = `<style>
        @page{
            size:A4 landscape;
            margin:10mm;
        }
        *{
            box-sizing:border-box;
            -webkit-print-color-adjust:exact;
            print-color-adjust:exact;
        }
        html, body{
            width:100%;
            margin:0;
            padding:0;
            background:#fff;
        }
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            color:#000;
            font-size:10px;
            background:#fff;
        }
        #printWrap{
            width:100%;
            margin:0 auto;
        }
        table{
            border-collapse:collapse;
            width:100%;
        }
        table th, table td{
            border:1px solid #d9e2f2;
            padding:4px;
            vertical-align:top;
            word-break:break-word;
        }
        img{
            max-width:100%;
            height:auto;
            display:block;
        }
    </style>`;

            doc.write('<html><head><title>Requisition Book</title>' + style + '</head><body><div id="printWrap">');
            doc.write(content.innerHTML);
            doc.write('</div></body></html>');
            doc.close();

            pri.focus();
            setTimeout(function() {
                pri.print();
            }, 400);
        }
    </script>
@endsection
