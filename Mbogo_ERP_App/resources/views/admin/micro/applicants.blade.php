@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Loan Applicants</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('micro.dashboard') }}">Microfinance</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Applicants</strong>
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
        <h3 class="mb-2 page-title">Loan Applicants</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Register Applicant</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.applicants.store') }}" method="POST">
                            @csrf

                            <div class="section-box">
                                <h2 class="section-title">1. Branch / Company Details</h2>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Company</label>
                                            <select name="company_id" class="form-control select2_demo_2">
                                                @foreach ($companies as $company)
                                                    <option value="{{ $company->id }}">{{ $company->company_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
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

                                    <div class="col-md-4">
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
                                </div>
                            </div>

                            <div class="section-box">
                                <h2 class="section-title">2. Applicant Main Details</h2>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Applicant Type</label>
                                            <select name="applicant_type" class="form-control select2_demo_2">
                                                <option value="Individual">Individual</option>
                                                <option value="Group">Group</option>
                                                <option value="Business">Business</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="full_name" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Trading As</label>
                                            <input type="text" name="trading_as" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>National ID</label>
                                            <input type="text" name="national_id_no" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Passport No</label>
                                            <input type="text" name="passport_no" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Marital Status</label>
                                            <input type="text" name="marital_status" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date of Birth</label>
                                            <input type="date" name="date_of_birth" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Age</label>
                                            <input type="number" name="age" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-box">
                                <h2 class="section-title">3. Contact & Residential Details</h2>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Mobile No (255XXXXXXXXX)</label>
                                            <input type="text" name="mobile_no" class="form-control phone255"
                                                maxlength="12" required>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Office Phone</label>
                                            <input type="text" name="office_phone" class="form-control phone255"
                                                maxlength="12">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Personal Email</label>
                                            <input type="email" name="personal_email" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Work Email</label>
                                            <input type="email" name="work_email" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Postal Address</label>
                                            <input type="text" name="postal_address" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Permanent Address</label>
                                            <input type="text" name="permanent_address" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Residence Town</label>
                                            <input type="text" name="residence_town" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Residence Estate</label>
                                            <input type="text" name="residence_estate" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Residence Street</label>
                                            <input type="text" name="residence_street" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>House No</label>
                                            <input type="text" name="house_no" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Residence Type</label>
                                            <input type="text" name="residence_type" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Building Name</label>
                                            <input type="text" name="building_name" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Landmark</label>
                                            <input type="text" name="landmark" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-box">
                                <h2 class="section-title">4. Referral Details</h2>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Referred By</label>
                                            <input type="text" name="referred_by" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Referred Phone (255XXXXXXXXX)</label>
                                            <input type="text" name="referred_phone" class="form-control phone255"
                                                maxlength="12">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-box">
                                <h2 class="section-title">5. Employment Details</h2>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Employer</label><input type="text"
                                                name="employer" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Employment Terms</label><input type="text"
                                                name="employment_terms" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Contract Duration (Months)</label><input
                                                type="number" name="contract_duration_months" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group"><label>Employment Date</label><input type="date"
                                                name="employment_date" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Designation</label><input type="text"
                                                name="designation" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Payroll No</label><input type="text"
                                                name="payroll_no" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Salary Pay Date</label><input type="text"
                                                name="salary_pay_date" class="form-control"></div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group"><label>Gross Salary</label><input type="number"
                                                step="0.01" name="gross_salary" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Net Salary</label><input type="number"
                                                step="0.01" name="net_salary" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Department</label><input type="text"
                                                name="department" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Workstation</label><input type="text"
                                                name="workstation" class="form-control"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group"><label>Branch Name</label><input type="text"
                                                name="branch_name" class="form-control"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-box">
                                <h2 class="section-title">6. Business Details</h2>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business Name</label><input type="text"
                                                name="business_name" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business Type</label><input type="text"
                                                name="business_type" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>KRA PIN</label><input type="text"
                                                name="kra_pin" class="form-control"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business TIN</label><input type="text"
                                                name="business_tin" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business Physical Address</label><input
                                                type="text" name="business_physical_address" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business Town</label><input type="text"
                                                name="business_town" class="form-control"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business Building</label><input type="text"
                                                name="business_building" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Nature Of Business</label><input type="text"
                                                name="nature_of_business" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business Premise</label><input type="text"
                                                name="business_premise" class="form-control"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group"><label>Business Landmark</label><input type="text"
                                                name="business_landmark" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Annual Turnover</label><input type="number"
                                                step="0.01" name="annual_turnover" class="form-control"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Years In Business</label><input type="number"
                                                name="years_in_business" class="form-control"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-box">
                                <h2 class="section-title">7. Status Details</h2>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control select2_demo_2">
                                                <option value="Active">Active</option>
                                                <option value="Deleted">Deleted</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Applicants Table</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-bordered dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Employer/Business</th>
                                    <th>Work Point</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $row->full_name }}</td>
                                        <td>{{ $row->mobile_no }}</td>
                                        <td>{{ $row->employer ?: $row->business_name }}</td>
                                        <td>{{ optional($row->workPoint)->work_name }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            @can('Edit-Loan-Applicants')
                                                <a href="javascript:void(0)" class="btn btn-warning btn-sm"
                                                    onclick="document.getElementById('edit-{{ $row->id }}').style.display='block'">Edit</a>
                                            @endcan
                                            @can('Delete-Loan-Applicants')
                                                <a href="{{ route('micro.applicants.remove', encrypt($row->id)) }}"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Remove applicant?')">Remove</a>
                                            @endcan
                                        </td>
                                    </tr>

                                    <tr id="edit-{{ $row->id }}" style="display:none;background:#f9f9f9;">
                                        <td colspan="7">
                                            <form action="{{ route('micro.applicants.update', encrypt($row->id)) }}"
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
                                                        <div class="form-group"><label>Business Unit</label><select
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
                                                            </select></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group"><label>Applicant Type</label><select
                                                                name="applicant_type" class="form-control select2_demo_2">
                                                                <option value="Individual"
                                                                    {{ $row->applicant_type == 'Individual' ? 'selected' : '' }}>
                                                                    Individual</option>
                                                                <option value="Business"
                                                                    {{ $row->applicant_type == 'Business' ? 'selected' : '' }}>
                                                                    Business</option>
                                                            </select></div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Full Name</label><input
                                                                type="text" name="full_name"
                                                                value="{{ $row->full_name }}" class="form-control"
                                                                required></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Mobile No</label><input
                                                                type="text" name="mobile_no"
                                                                value="{{ $row->mobile_no }}"
                                                                class="form-control phone255" maxlength="12" required>
                                                        </div>
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
                                                        <div class="form-group"><label>Personal Email</label><input
                                                                type="email" name="personal_email"
                                                                value="{{ $row->personal_email }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Work Email</label><input
                                                                type="email" name="work_email"
                                                                value="{{ $row->work_email }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Referred Phone</label><input
                                                                type="text" name="referred_phone"
                                                                value="{{ $row->referred_phone }}"
                                                                class="form-control phone255" maxlength="12"></div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Employer</label><input
                                                                type="text" name="employer"
                                                                value="{{ $row->employer }}" class="form-control"></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Business Name</label><input
                                                                type="text" name="business_name"
                                                                value="{{ $row->business_name }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group"><label>Trading As</label><input
                                                                type="text" name="trading_as"
                                                                value="{{ $row->trading_as }}" class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <button class="btn btn-primary btn-sm">Update</button>
                                                    </div>
                                                </div>
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

    <script>
        $(document).ready(function() {
            function formatPhone255(value) {
                value = value.replace(/\D/g, '');

                if (value.length === 0) {
                    return '255';
                }

                if (value.substring(0, 3) !== '255') {
                    value = '255' + value.replace(/^0+/, '');
                }

                value = value.substring(0, 12);
                return value;
            }

            $(document).on('focus', '.phone255', function() {
                if ($(this).val().trim() === '') {
                    $(this).val('255');
                }
            });

            $(document).on('input', '.phone255', function() {
                $(this).val(formatPhone255($(this).val()));
            });

            $(document).on('blur', '.phone255', function() {
                $(this).val(formatPhone255($(this).val()));
            });
        });
    </script>
@endsection
