@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Loan Penalties</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Penalties</strong></li>
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
        <h3 class="mb-2 page-title">Loan Penalties</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-4">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Register Penalty</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.penalties.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Loan Application</label>
                                <select name="loan_application_id" class="form-control select2_demo_2" required>
                                    <option value="">-- Select Application --</option>
                                    @foreach ($applications as $app)
                                        <option value="{{ $app->id }}">{{ $app->application_no }} -
                                            {{ optional($app->applicant)->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Penalty Date</label>
                                <input type="date" name="penalty_date" value="{{ date('Y-m-d') }}" class="form-control"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>Days Overdue</label>
                                <input type="number" name="days_overdue" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"></textarea>
                            </div>

                            <button class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Penalties Table</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-bordered dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Application</th>
                                    <th>Date</th>
                                    <th>Days</th>
                                    <th>Base Amount</th>
                                    <th>Rate</th>
                                    <th>Penalty</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ optional($row->application)->application_no }}</td>
                                        <td>{{ $row->penalty_date }}</td>
                                        <td>{{ $row->days_overdue }}</td>
                                        <td>{{ number_format($row->base_amount ?? 0, 2) }}</td>
                                        <td>{{ number_format($row->penalty_percent_per_day ?? 0, 2) }}%</td>
                                        <td>{{ number_format($row->penalty_amount ?? 0, 2) }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            @can('Edit-Loan-Penalties')
                                                <a href="javascript:void(0)" class="btn btn-warning btn-sm"
                                                    onclick="document.getElementById('edit-{{ $row->id }}').style.display='block'">Edit</a>
                                            @endcan
                                            @can('Delete-Loan-Penalties')
                                                <a href="{{ route('micro.penalties.remove', encrypt($row->id)) }}"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Remove penalty?')">Remove</a>
                                            @endcan
                                        </td>
                                    </tr>

                                    <tr id="edit-{{ $row->id }}" style="display:none;background:#f9f9f9;">
                                        <td colspan="9">
                                            <form action="{{ route('micro.penalties.update', encrypt($row->id)) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Penalty Date</label><input
                                                                type="date" name="penalty_date"
                                                                value="{{ $row->penalty_date }}" class="form-control"
                                                                required></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Days Overdue</label><input
                                                                type="number" name="days_overdue"
                                                                value="{{ $row->days_overdue }}" class="form-control"
                                                                required></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Remarks</label><input type="text"
                                                                name="remarks" value="{{ $row->remarks }}"
                                                                class="form-control"></div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group"><label>Status</label><select name="status"
                                                                class="form-control select2_demo_2">
                                                                <option value="Active"
                                                                    {{ $row->status == 'Active' ? 'selected' : '' }}>Active
                                                                </option>
                                                                <option value="Deleted"
                                                                    {{ $row->status == 'Deleted' ? 'selected' : '' }}>
                                                                    Deleted
                                                                </option>
                                                            </select></div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="form-group"><label>&nbsp;</label><button
                                                                class="btn btn-primary btn-sm btn-block">Update</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No penalties found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
