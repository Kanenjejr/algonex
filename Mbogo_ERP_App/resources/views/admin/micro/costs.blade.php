@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Microfinance Costs</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Costs</strong></li>
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
        <h3 class="mb-2 page-title">Microfinance Costs</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Register Cost</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.costs.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group"><label>Company</label><select name="company_id"
                                            class="form-control select2_demo_2">
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Business Unit</label><select name="comp_unit_id"
                                            class="form-control select2_demo_2">
                                            <option value="">-- Select --</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Work Point</label><select name="work_point_id"
                                            class="form-control select2_demo_2">
                                            <option value="">-- Select --</option>
                                            @foreach ($workPoints as $wp)
                                                <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group"><label>Loan Application (Optional)</label><select
                                            name="loan_application_id" class="form-control select2_demo_2">
                                            <option value="">-- Select Application --</option>
                                            @foreach ($applications as $app)
                                                <option value="{{ $app->id }}">{{ $app->application_no }} -
                                                    {{ optional($app->applicant)->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group"><label>Cost Type</label><select name="cost_type"
                                            class="form-control select2_demo_2" required>
                                            <option value="Office">Office</option>
                                            <option value="ApplicantRecoverable">Applicant Recoverable</option>
                                        </select></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Cost Date</label><input type="date" name="cost_date"
                                            value="{{ date('Y-m-d') }}" class="form-control" required></div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group"><label>Cost Name</label><input type="text" name="cost_name"
                                            class="form-control" required></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group"><label>Amount</label><input type="number" step="0.01"
                                            name="amount" class="form-control" required></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group"><label>Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Costs Table</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-bordered dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cost Date</th>
                                    <th>Type</th>
                                    <th>Cost Name</th>
                                    <th>Application</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $row->cost_date }}</td>
                                        <td>{{ $row->cost_type }}</td>
                                        <td>{{ $row->cost_name }}</td>
                                        <td>{{ optional($row->application)->application_no }}</td>
                                        <td>{{ number_format($row->amount ?? 0, 2) }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            @can('Edit-Microfinance-Costs')
                                                <a href="javascript:void(0)" class="btn btn-warning btn-sm"
                                                    onclick="document.getElementById('edit-{{ $row->id }}').style.display='block'">Edit</a>
                                            @endcan
                                            @can('Delete-Microfinance-Costs')
                                                <a href="{{ route('micro.costs.remove', encrypt($row->id)) }}"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Remove cost?')">Remove</a>
                                            @endcan
                                        </td>
                                    </tr>

                                    <tr id="edit-{{ $row->id }}" style="display:none;background:#f9f9f9;">
                                        <td colspan="8">
                                            <form action="{{ route('micro.costs.update', encrypt($row->id)) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Company</label><select
                                                                name="company_id" class="form-control select2_demo_2">
                                                                @foreach ($companies as $company)
                                                                    <option value="{{ $company->id }}"
                                                                        {{ $row->company_id == $company->id ? 'selected' : '' }}>
                                                                        {{ $company->company_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Unit</label><select
                                                                name="comp_unit_id" class="form-control select2_demo_2">
                                                                <option value="">-- Select --</option>
                                                                @foreach ($units as $unit)
                                                                    <option value="{{ $unit->id }}"
                                                                        {{ $row->comp_unit_id == $unit->id ? 'selected' : '' }}>
                                                                        {{ $unit->unit_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Work Point</label><select
                                                                name="work_point_id" class="form-control select2_demo_2">
                                                                <option value="">-- Select --</option>
                                                                @foreach ($workPoints as $wp)
                                                                    <option value="{{ $wp->id }}"
                                                                        {{ $row->work_point_id == $wp->id ? 'selected' : '' }}>
                                                                        {{ $wp->work_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Application</label><select
                                                                name="loan_application_id"
                                                                class="form-control select2_demo_2">
                                                                <option value="">-- Select Application --</option>
                                                                @foreach ($applications as $app)
                                                                    <option value="{{ $app->id }}"
                                                                        {{ $row->loan_application_id == $app->id ? 'selected' : '' }}>
                                                                        {{ $app->application_no }}</option>
                                                                @endforeach
                                                            </select></div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Cost Type</label><select
                                                                name="cost_type" class="form-control select2_demo_2">
                                                                <option value="Office"
                                                                    {{ $row->cost_type == 'Office' ? 'selected' : '' }}>
                                                                    Office
                                                                </option>
                                                                <option value="ApplicantRecoverable"
                                                                    {{ $row->cost_type == 'ApplicantRecoverable' ? 'selected' : '' }}>
                                                                    Applicant Recoverable</option>
                                                            </select></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Cost Date</label><input
                                                                type="date" name="cost_date"
                                                                value="{{ $row->cost_date }}" class="form-control"
                                                                required></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Cost Name</label><input
                                                                type="text" name="cost_name"
                                                                value="{{ $row->cost_name }}" class="form-control"
                                                                required></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Amount</label><input type="number"
                                                                step="0.01" name="amount"
                                                                value="{{ $row->amount }}" class="form-control"
                                                                required></div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Remarks</label><input
                                                                type="text" name="remarks"
                                                                value="{{ $row->remarks }}" class="form-control"></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Status</label><select
                                                                name="status" class="form-control select2_demo_2">
                                                                <option value="Active"
                                                                    {{ $row->status == 'Active' ? 'selected' : '' }}>Active
                                                                </option>
                                                                <option value="Deleted"
                                                                    {{ $row->status == 'Deleted' ? 'selected' : '' }}>
                                                                    Deleted
                                                                </option>
                                                            </select></div>
                                                    </div>
                                                    <div class="col-md-2">
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
                                        <td colspan="8" class="text-center text-muted">No costs found.</td>
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
