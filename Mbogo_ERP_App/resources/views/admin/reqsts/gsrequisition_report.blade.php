@extends('layouts.ReqstMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Requisition & Approvals Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('requisition') }}">Requisition & Approvals</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Requisition Report</strong></li>
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
            document.getElementById('Hour').innerHTML = d.getHours() + ':';
            document.getElementById('Minut').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second').innerHTML = d.getSeconds();
        }
        timedMsg();
    </script>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Requisition Report</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request No</th>
                                <th>Date</th>
                                <th>location</th>
                                <th>Section</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Requested Qty</th>
                                <th>Issued Qty</th>
                                <th>Received Qty</th>
                                <th>Received Date</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Received Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $k => $row)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->request_no }}</td>
                                    <td>{{ $row->request_date }}</td>
                                    <td>{{ optional($row->workpoint)->work_name }}</td>
                                    <td>{{ optional($row->section)->secName ?? '-' }}</td>
                                    <td>{{ optional($row->item)->item_name }}</td>
                                    <td>{{ optional($row->description)->description_name }}
                                        ({{ optional($row->description)->unit_name }})</td>
                                    <td>{{ number_format($row->requested_qty, 2) }}</td>
                                    <td>{{ number_format($row->issued_qty, 2) }}</td>
                                    <td>{{ number_format($row->received_qty, 2) }}</td>
                                    <td>{{ $row->received_date ?? '-' }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td>{{ $row->reason }}</td>
                                    <td>{{ $row->received_remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
