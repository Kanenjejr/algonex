@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Loan Applications</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('micro.dashboard') }}">Microfinance</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Loan Applications</strong>
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
        .ibox-custom {
            border: 1px solid #d9e2f2;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(23, 58, 122, .08);
            background: #fff;
        }

        .ibox-title-custom {
            background: linear-gradient(135deg, #173a7a 0%, #214f9c 55%, #244f96 100%);
            color: #fff;
        }

        .ibox-title-custom h5 {
            color: #fff !important;
            font-weight: 800;
        }
    </style>

    <div class="col-12">
        <h3 class="mb-2 page-title">Loan Applications</h3>
        @can('Register-Loan-Applications')
            <a href="{{ route('micro.applications.create') }}" style="position:absolute; top:4.5%; right:1.7%;"
                class="btn mb-2 btn-primary">
                Add Application
            </a>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Applications Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Application No</th>
                                        <th>Applicant</th>
                                        <th>Category</th>
                                        <th>Applied Amount</th>
                                        <th>Approved Amount</th>
                                        <th>Verification</th>
                                        <th>Approval</th>
                                        <th>Loan Status</th>
                                        <th>Cashout</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $k => $row)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $row->application_no }}</td>
                                            <td>{{ optional($row->applicant)->full_name }}</td>
                                            <td>{{ optional($row->category)->category_name }}</td>
                                            <td>{{ number_format($row->amount_applied ?? 0, 2) }}</td>
                                            <td>{{ number_format($row->approved_amount ?? 0, 2) }}</td>
                                            <td>{{ $row->verification_status }}</td>
                                            <td>{{ $row->approval_status }}</td>
                                            <td>{{ $row->loan_status }}</td>
                                            <td>{{ $row->disbursement_status }}</td>
                                            <td style="white-space:nowrap;">
                                                <a href="{{ route('micro.applications.show', encrypt($row->id)) }}"
                                                    class="btn btn-info btn-sm">View</a>

                                                @can('Edit-Loan-Applications')
                                                    @if ($row->approval_status != 'Approved' && $row->disbursement_status != 'Cashed-Out')
                                                        <a href="{{ route('micro.applications.edit', encrypt($row->id)) }}"
                                                            class="btn btn-warning btn-sm">Edit</a>
                                                    @endif
                                                @endcan

                                                @can('Delete-Loan-Applications')
                                                    @if ($row->approval_status != 'Approved' && $row->disbursement_status != 'Cashed-Out')
                                                        <a href="{{ route('micro.applications.remove', encrypt($row->id)) }}"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Remove this application?')">
                                                            Remove
                                                        </a>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">No applications found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
