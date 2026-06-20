@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Store Management Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('company.dashboard') }}">Dashboard</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Raw Material Stock</strong>
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
                                <td id="Hour5" style="color:green;font-size:large;"></td>
                                <td id="Minut5" style="color:green;font-size:large;"></td>
                                <td id="Second5" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg5() {
            setInterval("change_time5();", 1000);
        }

        function change_time5() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            if (curr_hour > 24) curr_hour = curr_hour - 24;
            document.getElementById('Hour5').innerHTML = curr_hour + ':';
            document.getElementById('Minut5').innerHTML = curr_min + ':';
            document.getElementById('Second5').innerHTML = curr_sec;
        }
        timedMsg5();
    </script>

    <div class="col-12">
        <h3 class="mb-2 page-title">Raw Material Stock</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Raw Material Stock Table</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company</th>
                                <th>Unit</th>
                                <th>Work Point</th>
                                <th>Material</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Balance</th>
                                <th>Unit Price</th>
                                <th>Stock Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $k => $row)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ optional($row->company)->company_name ?? '-' }}</td>
                                    <td>{{ optional($row->unit)->unit_name ?? '-' }}</td>
                                    <td>
                                        {{ optional($row->workpoint)->work_code ?? '' }}
                                        {{ optional($row->workpoint)->work_code ? ' - ' : '' }}
                                        {{ optional($row->workpoint)->work_name ?? '-' }}
                                    </td>
                                    <td>{{ optional($row->material)->material_name ?? '-' }}</td>
                                    <td>{{ number_format($row->qty_in, 2) }}</td>
                                    <td>{{ number_format($row->qty_out, 2) }}</td>
                                    <td>{{ number_format($row->balance, 2) }}</td>
                                    <td>{{ number_format($row->unit_price, 2) }}</td>
                                    <td>{{ number_format($row->balance * $row->unit_price, 2) }}</td>
                                    <td>{{ $row->status }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No stock found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="9" style="text-align:right">Total Stock Value:</th>
                                <th colspan="2">
                                    {{ number_format(collect($rows)->sum(function ($r) {return $r->balance * $r->unit_price;}),2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
