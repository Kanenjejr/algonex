@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Application Details</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li><a href="{{ route('micro.applications.index') }}">Loan Applications</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Details</strong></li>
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
        .section-box {
            border: 1px solid #d9e2f2;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 18px;
            background: #fafcff;
        }

        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: #173a7a;
            margin-bottom: 15px;
            border-bottom: 2px solid #e5edf8;
            padding-bottom: 8px;
        }

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
        <h3 class="mb-2 page-title">Application Details - {{ $item->application_no }}</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Main Details</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="section-box">
                            <h2 class="section-title">1. Application Information</h2>
                            <div class="row">
                                <div class="col-md-3">
                                    <p><strong>Application No:</strong><br>{{ $item->application_no }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Application Date:</strong><br>{{ $item->application_date }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Verification:</strong><br>{{ $item->verification_status }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Approval:</strong><br>{{ $item->approval_status }}</p>
                                </div>

                                <div class="col-md-3">
                                    <p><strong>Loan Status:</strong><br>{{ $item->loan_status }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Disbursement:</strong><br>{{ $item->disbursement_status }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Approved
                                            Amount:</strong><br>{{ number_format($item->approved_amount ?? 0, 2) }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Monthly
                                            Repayment:</strong><br>{{ number_format($item->monthly_repayment ?? 0, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="section-box">
                            <h2 class="section-title">2. Applicant Information</h2>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Name:</strong><br>{{ optional($item->applicant)->full_name }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Mobile:</strong><br>{{ optional($item->applicant)->mobile_no }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Email:</strong><br>{{ optional($item->applicant)->personal_email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="section-box">
                            <h2 class="section-title">3. Loan Particulars</h2>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Category:</strong><br>{{ optional($item->category)->category_name }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Product:</strong><br>{{ optional($item->product)->product_name }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Amount
                                            Applied:</strong><br>{{ number_format($item->amount_applied ?? 0, 2) }}</p>
                                </div>

                                <div class="col-md-4">
                                    <p><strong>Project Cost:</strong><br>{{ number_format($item->project_cost ?? 0, 2) }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Own
                                            Contribution:</strong><br>{{ number_format($item->own_contribution ?? 0, 2) }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Loan Period:</strong><br>{{ $item->loan_period_months }} Month(s)</p>
                                </div>

                                <div class="col-md-4">
                                    <p><strong>Interest Rate:</strong><br>{{ $item->interest_rate }}%</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Interest Method:</strong><br>{{ $item->interest_method }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Reminder
                                            Charge:</strong><br>{{ number_format($item->reminder_charge ?? 0, 2) }}</p>
                                </div>

                                <div class="col-md-6">
                                    <p><strong>Penalty % Per Day:</strong><br>{{ $item->penalty_percent_per_day }}%</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Penalty Basis:</strong><br>{{ $item->penalty_basis }}</p>
                                </div>

                                <div class="col-md-12">
                                    <p><strong>Purpose:</strong><br>{{ $item->purpose }}</p>
                                </div>
                                <div class="col-md-12">
                                    <p><strong>Notes:</strong><br>{{ $item->notes }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="section-box">
                            <h2 class="section-title">4. Verification / Approval Remarks</h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Verification Remarks:</strong><br>{{ $item->verification_remarks }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Approval Remarks:</strong><br>{{ $item->approval_remarks }}</p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('micro.applications.index') }}" class="btn btn-secondary">Back</a>
                        @if ($item->approval_status != 'Approved' && $item->disbursement_status != 'Cashed-Out')
                            @can('Edit-Loan-Applications')
                                <a href="{{ route('micro.applications.edit', encrypt($item->id)) }}"
                                    class="btn btn-warning">Edit Application</a>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Verification / Approval / Cashout --}}
        <div class="row">
            <div class="col-md-4">
                <div class="ibox ibox-custom">
                    <div class="ibox-title bg-info">
                        <h5>Verify / Decline</h5>
                    </div>
                    <div class="ibox-content">
                        @can('Verify-Loan-Applications')
                            <form action="{{ route('micro.applications.verify', encrypt($item->id)) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Verification Remarks</label>
                                    <textarea name="verification_remarks" class="form-control" rows="3"></textarea>
                                </div>
                                <button class="btn btn-primary btn-sm">Verify</button>
                            </form>
                        @endcan

                        <hr>

                        @can('Decline-Loan-Applications')
                            <form action="{{ route('micro.applications.decline', encrypt($item->id)) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Decline Remarks</label>
                                    <textarea name="verification_remarks" class="form-control" rows="3" required></textarea>
                                </div>
                                <button class="btn btn-danger btn-sm">Decline</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox ibox-custom">
                    <div class="ibox-title bg-success">
                        <h5>Approve / Reject</h5>
                    </div>
                    <div class="ibox-content">
                        @can('Approve-Loan-Applications')
                            <form action="{{ route('micro.applications.approve', encrypt($item->id)) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Approved Amount</label>
                                    <input type="number" step="0.01" name="approved_amount" class="form-control"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label>Approval Remarks</label>
                                    <textarea name="approval_remarks" class="form-control" rows="3"></textarea>
                                </div>
                                <button class="btn btn-primary btn-sm">Approve</button>
                            </form>
                        @endcan

                        <hr>

                        @can('Reject-Loan-Applications')
                            <form action="{{ route('micro.applications.reject', encrypt($item->id)) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Reject Remarks</label>
                                    <textarea name="approval_remarks" class="form-control" rows="3" required></textarea>
                                </div>
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ibox ibox-custom">
                    <div class="ibox-title bg-warning">
                        <h5>Cashout Loan</h5>
                    </div>
                    <div class="ibox-content">
                        @can('Cashout-Loan-Applications')
                            <form action="{{ route('micro.applications.cashout', encrypt($item->id)) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Disbursement Date</label>
                                    <input type="date" name="disbursement_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Amount Disbursed</label>
                                    <input type="number" step="0.01" name="amount_disbursed" class="form-control"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label>Channel</label>
                                    <input type="text" name="channel" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Reference No</label>
                                    <input type="text" name="reference_no" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Bank / Network</label>
                                    <input type="text" name="bank_or_network" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                </div>
                                <button class="btn btn-success btn-sm">Cash Out</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- Guarantors / Referees --}}
        <div class="row">
            <div class="col-md-6">
                <div class="ibox ibox-custom">
                    <div class="ibox-title bg-info">
                        <h5>Guarantors / Referees</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.applications.guarantors.store', encrypt($item->id)) }}"
                            method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>Relation Type</label><select name="relation_type"
                                            class="form-control select2_demo_2">
                                            <option value="NextOfKin">Next Of Kin</option>
                                            <option value="Referral">Referral</option>
                                            <option value="Guarantor">Guarantor</option>
                                            <option value="Referee">Referee</option>
                                        </select></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Full Name</label><input type="text"
                                            name="full_name" class="form-control" required></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Phone No</label><input type="text" name="phone_no"
                                            class="form-control phone255" maxlength="12"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Relationship</label><input type="text"
                                            name="relationship" class="form-control"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Email</label><input type="email" name="email"
                                            class="form-control"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Work Email</label><input type="email"
                                            name="work_email" class="form-control"></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group"><label>Branch</label><input type="text" name="branch"
                                            class="form-control"></div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm">Add Guarantor / Referee</button>
                        </form>

                        <hr>

                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->guarantors as $k => $g)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $g->relation_type }}</td>
                                        <td>{{ $g->full_name }}</td>
                                        <td>{{ $g->phone_no }}</td>
                                        <td>
                                            <a href="{{ route('micro.guarantors.remove', encrypt($g->id)) }}"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Remove record?')">Remove</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No record found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Collateral --}}
            <div class="col-md-6">
                <div class="ibox ibox-custom">
                    <div class="ibox-title bg-success">
                        <h5>Collateral / Security</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.applications.collaterals.store', encrypt($item->id)) }}"
                            method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>Collateral Type</label><select name="collateral_type"
                                            class="form-control select2_demo_2">
                                            <option value="BusinessShare">Business Share</option>
                                            <option value="Asset">Asset</option>
                                            <option value="Vehicle">Vehicle</option>
                                            <option value="Plot">Plot</option>
                                            <option value="House">House</option>
                                            <option value="LogBook">LogBook</option>
                                            <option value="Other">Other</option>
                                        </select></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Item Name</label><input type="text"
                                            name="item_name" class="form-control" required></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>No. of Items</label><input type="number"
                                            name="no_of_items" class="form-control" value="1"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Serial Number</label><input type="text"
                                            name="serial_number" class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Color</label><input type="text" name="color"
                                            class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Original Cost</label><input type="number"
                                            step="0.01" name="original_cost" class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Estimated Value</label><input type="number"
                                            step="0.01" name="estimated_value" class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Discounted Value</label><input type="number"
                                            step="0.01" name="discounted_value" class="form-control"></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group"><label>Ownership Notes</label>
                                        <textarea name="ownership_notes" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm">Add Collateral</button>
                        </form>

                        <hr>

                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th>Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->collaterals as $k => $c)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $c->collateral_type }}</td>
                                        <td>{{ $c->item_name }}</td>
                                        <td>{{ number_format($c->discounted_value ?? 0, 2) }}</td>
                                        <td>
                                            <a href="{{ route('micro.collaterals.remove', encrypt($c->id)) }}"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Remove record?')">Remove</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No collateral found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attachments --}}
        <div class="row">
            <div class="col-md-12">
                <div class="ibox ibox-custom">
                    <div class="ibox-title bg-warning">
                        <h5>Attachments</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.applications.attachments.store', encrypt($item->id)) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Attachment Type</label>
                                        <select name="attachment_type" class="form-control select2_demo_2" required>
                                            <option value="ApplicationLetter">Application Letter</option>
                                            <option value="Contract">Contract</option>
                                            <option value="AssetDocument">Asset Document</option>
                                            <option value="BorrowerImage">Borrower Image</option>
                                            <option value="RefereeImage">Referee Image</option>
                                            <option value="NationalID">National ID</option>
                                            <option value="Passport">Passport</option>
                                            <option value="BusinessCertificate">Business Certificate</option>
                                            <option value="TCC">TCC</option>
                                            <option value="LogBook">LogBook</option>
                                            <option value="CourtOrder">Court Order</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>File</label>
                                        <input type="file" name="file" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button class="btn btn-primary btn-sm btn-block">Upload</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>File Name</th>
                                        <th>View</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($item->attachments as $k => $a)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $a->attachment_type }}</td>
                                            <td>{{ $a->file_name }}</td>
                                            <td><a href="{{ asset($a->file_path) }}" target="_blank"
                                                    class="btn btn-info btn-sm">View File</a></td>
                                            <td><a href="{{ route('micro.attachments.remove', encrypt($a->id)) }}"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Remove attachment?')">Remove</a></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No attachments uploaded.</td>
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

    <script>
        $(document).ready(function() {

            function formatPhone255(value) {
                value = value.replace(/\D/g, '');
                if (value.length === 0) return '255';
                if (value.substring(0, 3) !== '255') value = '255' + value.replace(/^0+/, '');
                return value.substring(0, 12);
            }

            $(document).on('focus', '.phone255', function() {
                if ($(this).val().trim() === '') $(this).val('255');
            });

            $(document).on('input blur', '.phone255', function() {
                $(this).val(formatPhone255($(this).val()));
            });
        });
    </script>
@endsection
