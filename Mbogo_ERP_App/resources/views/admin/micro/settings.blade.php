@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Microfinance Settings</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Settings</strong></li>
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
                        echo $carbon1; ?>
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

    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-5">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Register Settings</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.settings.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <select name="company_id" class="form-control select2_demo_2">
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Business Unit</label>
                                        <select name="comp_unit_id" class="form-control select2_demo_2">
                                            <option value="">-- Select --</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Work Point</label>
                                        <select name="work_point_id" class="form-control select2_demo_2">
                                            <option value="">-- Select --</option>
                                            @foreach ($workPoints as $wp)
                                                <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>SMS Token Cost</label>
                                        <input type="number" step="0.01" name="sms_token_cost" class="form-control"
                                            value="25" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default Reminder Charge</label>
                                        <input type="number" step="0.01" name="default_reminder_charge"
                                            class="form-control" value="0" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default Penalty Basis</label>
                                        <select name="default_penalty_basis" class="form-control select2_demo_2" required>
                                            <option value="remaining_balance">Remaining Balance</option>
                                            <option value="full_loan">Full Loan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control select2_demo_2">
                                            <option value="Active">Active</option>
                                            <option value="Deleted">Deleted</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Settings Table</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-bordered dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Company</th>
                                    <th>Unit</th>
                                    <th>Work Point</th>
                                    <th>SMS</th>
                                    <th>Reminder</th>
                                    <th>Penalty Basis</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ optional($row->company)->company_name }}</td>
                                        <td>{{ optional($row->companyUnit)->unit_name }}</td>
                                        <td>{{ optional($row->workPoint)->work_name }}</td>
                                        <td>{{ number_format($row->sms_token_cost, 2) }}</td>
                                        <td>{{ number_format($row->default_reminder_charge, 2) }}</td>
                                        <td>{{ $row->default_penalty_basis }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            @can('Edit-Microfinance-Settings')
                                                <a href="javascript:void(0)" class="btn btn-warning btn-sm"
                                                    onclick="document.getElementById('edit-box-{{ $row->id }}').style.display='block'">Edit</a>
                                            @endcan
                                        </td>
                                    </tr>
                                    <tr id="edit-box-{{ $row->id }}" style="display:none;background:#f9f9f9;">
                                        <td colspan="9">
                                            <form action="{{ route('micro.settings.update', encrypt($row->id)) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Company</label>
                                                            <select name="company_id" class="form-control select2_demo_2">
                                                                @foreach ($companies as $company)
                                                                    <option value="{{ $company->id }}"
                                                                        {{ $row->company_id == $company->id ? 'selected' : '' }}>
                                                                        {{ $company->company_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Business Unit</label>
                                                            <select name="comp_unit_id" class="form-control select2_demo_2">
                                                                <option value="">-- Select --</option>
                                                                @foreach ($units as $unit)
                                                                    <option value="{{ $unit->id }}"
                                                                        {{ $row->comp_unit_id == $unit->id ? 'selected' : '' }}>
                                                                        {{ $unit->unit_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Work Point</label>
                                                            <select name="work_point_id" class="form-control select2_demo_2">
                                                                <option value="">-- Select --</option>
                                                                @foreach ($workPoints as $wp)
                                                                    <option value="{{ $wp->id }}"
                                                                        {{ $row->work_point_id == $wp->id ? 'selected' : '' }}>
                                                                        {{ $wp->work_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>SMS Token Cost</label>
                                                            <input type="number" step="0.01" name="sms_token_cost"
                                                                class="form-control" value="{{ $row->sms_token_cost }}"
                                                                required>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Default Reminder Charge</label>
                                                            <input type="number" step="0.01"
                                                                name="default_reminder_charge" class="form-control"
                                                                value="{{ $row->default_reminder_charge }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Default Penalty Basis</label>
                                                            <select name="default_penalty_basis"
                                                                class="form-control select2_demo_2">
                                                                <option value="remaining_balance"
                                                                    {{ $row->default_penalty_basis == 'remaining_balance' ? 'selected' : '' }}>
                                                                    Remaining Balance</option>
                                                                <option value="full_loan"
                                                                    {{ $row->default_penalty_basis == 'full_loan' ? 'selected' : '' }}>
                                                                    Full Loan</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Status</label>
                                                            <select name="status" class="form-control select2_demo_2">
                                                                <option value="Active"
                                                                    {{ $row->status == 'Active' ? 'selected' : '' }}>Active
                                                                </option>
                                                                <option value="Deleted"
                                                                    {{ $row->status == 'Deleted' ? 'selected' : '' }}>
                                                                    Deleted
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="btn btn-primary btn-sm">Update</button>
                                            </form>
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
@endsection
