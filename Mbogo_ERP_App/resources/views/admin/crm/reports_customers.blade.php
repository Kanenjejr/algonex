@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Customers, Supplies & Interactions Dashboard</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                     <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Customer / Supplier Reports</strong>
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
    <div class="col-12">
        <h3 class="mb-2 page-title">Customer / Supplier Reports</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-primary">
                        <h5>Customers & Suppliers</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Phone</th>
                                        <th>Work Point</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customers as $k => $c)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $c->customer_name }}</td>
                                            <td>{{ $c->category }}</td>
                                            <td>{{ $c->phone_no ?? '-' }}</td>
                                            <td>{{ optional($c->workpoint)->work_name ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('crm.reports.customer.detail', encrypt($c->id)) }}"
                                                    class="btn btn-sm btn-info">View Report</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
