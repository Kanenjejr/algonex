@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Loan Repayments</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Repayments</strong></li>
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
        <h3 class="mb-2 page-title">Loan Repayments</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Register Repayment</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.repayments.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Loan Application</label>
                                        <select name="loan_application_id" class="form-control select2_demo_2" required>
                                            <option value="">-- Select Application --</option>
                                            @foreach ($applications as $app)
                                                <option value="{{ $app->id }}">
                                                    {{ $app->application_no }} - {{ optional($app->applicant)->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group"><label>Repayment Date</label><input type="date"
                                            name="repayment_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Amount Paid</label><input type="number" step="0.01"
                                            name="amount_paid" class="form-control" required></div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group"><label>Principal Paid</label><input type="number"
                                            step="0.01" name="principal_paid" class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Interest Paid</label><input type="number" step="0.01"
                                            name="interest_paid" class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Penalty Paid</label><input type="number" step="0.01"
                                            name="penalty_paid" class="form-control"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group"><label>Reminder Charge Paid</label><input type="number"
                                            step="0.01" name="reminder_charge_paid" class="form-control"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Recoverable Cost Paid</label><input type="number"
                                            step="0.01" name="recoverable_cost_paid" class="form-control"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group"><label>Payment Method</label><input type="text"
                                            name="payment_method" class="form-control"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Reference No</label><input type="text"
                                            name="reference_no" class="form-control"></div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group"><label>Repayment Slip / Document</label><input type="file"
                                            name="repayment_slip" class="form-control"></div>
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
                        <h5>Repayments Table</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-bordered dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Application</th>
                                    <th>Date</th>
                                    <th>Amount Paid</th>
                                    <th>Method</th>
                                    <th>Slip</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ optional($row->application)->application_no }}</td>
                                        <td>{{ $row->repayment_date }}</td>
                                        <td>{{ number_format($row->amount_paid ?? 0, 2) }}</td>
                                        <td>{{ $row->payment_method }}</td>
                                        <td>
                                            @php
                                                $slip = optional($row->attachments)->first();
                                            @endphp
                                            @if ($slip)
                                                <a href="{{ asset($slip->file_path) }}" target="_blank"
                                                    class="btn btn-info btn-sm">View Slip</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            @can('Edit-Loan-Repayments')
                                                <a href="javascript:void(0)" class="btn btn-warning btn-sm"
                                                    onclick="document.getElementById('edit-{{ $row->id }}').style.display='block'">Edit</a>
                                            @endcan
                                            @can('Delete-Loan-Repayments')
                                                <a href="{{ route('micro.repayments.remove', encrypt($row->id)) }}"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Remove repayment?')">Remove</a>
                                            @endcan
                                        </td>
                                    </tr>

                                    <tr id="edit-{{ $row->id }}" style="display:none;background:#f9f9f9;">
                                        <td colspan="8">
                                            <form action="{{ route('micro.repayments.update', encrypt($row->id)) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Repayment Date</label><input
                                                                type="date" name="repayment_date"
                                                                value="{{ $row->repayment_date }}" class="form-control"
                                                                required></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Amount Paid</label><input
                                                                type="number" step="0.01" name="amount_paid"
                                                                value="{{ $row->amount_paid }}" class="form-control"
                                                                required></div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group"><label>Principal</label><input
                                                                type="number" step="0.01" name="principal_paid"
                                                                value="{{ $row->principal_paid }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group"><label>Interest</label><input
                                                                type="number" step="0.01" name="interest_paid"
                                                                value="{{ $row->interest_paid }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group"><label>Penalty</label><input
                                                                type="number" step="0.01" name="penalty_paid"
                                                                value="{{ $row->penalty_paid }}" class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Reminder Charge</label><input
                                                                type="number" step="0.01" name="reminder_charge_paid"
                                                                value="{{ $row->reminder_charge_paid }}"
                                                                class="form-control"></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Recoverable Cost</label><input
                                                                type="number" step="0.01"
                                                                name="recoverable_cost_paid"
                                                                value="{{ $row->recoverable_cost_paid }}"
                                                                class="form-control"></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Payment Method</label><input
                                                                type="text" name="payment_method"
                                                                value="{{ $row->payment_method }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Reference No</label><input
                                                                type="text" name="reference_no"
                                                                value="{{ $row->reference_no }}" class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Replace Slip /
                                                                Document</label><input type="file"
                                                                name="repayment_slip" class="form-control"></div>
                                                    </div>
                                                    <div class="col-md-4">
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
                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Remarks</label><input
                                                                type="text" name="remarks"
                                                                value="{{ $row->remarks }}" class="form-control"></div>
                                                    </div>

                                                    <div class="col-md-12"><button
                                                            class="btn btn-primary btn-sm">Update</button></div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No repayments found.</td>
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
