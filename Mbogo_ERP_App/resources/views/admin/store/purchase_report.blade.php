@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Store Management Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Purchase Report</strong></li>
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
                <h5>Purchase / Received Report</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Expiry</th>
                                <th>Work Point</th>
                                <th>Section</th>
                                <th>Scope</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Received Qty</th>
                                <th>Damaged Qty</th>
                                <th>Good Qty</th>
                                <th>Purchase Price</th>
                                <th>Total Amount</th>
                                <th>Supplier</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $k => $row)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->receive_date }}</td>
                                    <td>{{ $row->expiry_date ?? '-' }}</td>
                                    <td>{{ optional($row->workpoint)->work_name }}</td>
                                    <td>{{ optional($row->section)->secName ?? '-' }}</td>
                                    <td>{{ $row->stock_scope }}</td>
                                    <td>{{ optional($row->item)->item_name }}</td>
                                    <td>{{ optional($row->description)->description_name }}
                                        ({{ optional($row->description)->unit_name }})
                                    </td>
                                    <td>{{ number_format($row->received_qty, 2) }}</td>
                                    <td>{{ number_format($row->damaged_qty, 2) }}</td>
                                    <td>{{ number_format($row->good_qty, 2) }}</td>
                                    <td>{{ number_format($row->purchase_price, 2) }}</td>
                                    <td>{{ number_format($row->total_amount, 2) }}</td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ $row->invoice_no }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <hr>
                <h4>Expired Items Still In Stock</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Section</th>
                                <th>Expiry Date</th>
                                <th>Balance</th>
                                <th>Purchase Price</th>
                                <th>Estimated Loss</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expiredRows as $k => $row)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ optional($row->item)->item_name }}</td>
                                    <td>{{ optional($row->description)->description_name }}
                                        ({{ optional($row->description)->unit_name }})
                                    </td>
                                    <td>{{ optional($row->section)->secName ?? 'ALL' }}</td>
                                    <td>{{ $row->expiry_date }}</td>
                                    <td>{{ number_format($row->balance, 2) }}</td>
                                    <td>{{ number_format($row->purchase_price, 2) }}</td>
                                    <td>{{ number_format($row->balance * $row->purchase_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
