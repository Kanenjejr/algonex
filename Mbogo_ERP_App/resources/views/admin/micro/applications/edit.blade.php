@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Create Loan Application</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li><a href="{{ route('micro.applications.index') }}">Loan Applications</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Create</strong></li>
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
        <h3 class="mb-2 page-title">Create Loan Application</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Loan Application Form</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('micro.applications.store') }}" method="POST">
                            @csrf

                            <div class="section-box">
                                <h2 class="section-title">1. Company / Branch Details</h2>
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
                                <h2 class="section-title">2. Applicant & Product Details</h2>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Applicant</label>
                                            <select name="applicant_id" class="form-control select2_demo_2" required>
                                                <option value="">-- Select Applicant --</option>
                                                @foreach ($applicants as $applicant)
                                                    <option value="{{ $applicant->id }}">{{ $applicant->full_name }} -
                                                        {{ $applicant->mobile_no }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Loan Category</label>
                                            <select name="loan_category_id" class="form-control select2_demo_2" required>
                                                <option value="">-- Select Category --</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->category_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Loan Product</label>
                                            <select name="loan_product_id" class="form-control select2_demo_2">
                                                <option value="">-- Select Product --</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Application Date</label>
                                            <input type="date" name="application_date" class="form-control"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-box">
                                <h2 class="section-title">3. Loan Particulars</h2>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Amount Applied</label><input type="number"
                                                step="0.01" name="amount_applied" class="form-control" required></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Project Cost</label><input type="number"
                                                step="0.01" name="project_cost" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Own Contribution</label><input type="number"
                                                step="0.01" name="own_contribution" class="form-control"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"><label>Loan Period (Months)</label><input type="number"
                                                name="loan_period_months" class="form-control" required></div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group"><label>Interest Rate (%)</label><input type="number"
                                                step="0.01" name="interest_rate" class="form-control" required></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Interest Method</label><select
                                                name="interest_method" class="form-control select2_demo_2" required>
                                                <option value="flat">Flat</option>
                                                <option value="reducing">Reducing</option>
                                            </select></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Reminder Charge</label><input type="number"
                                                step="0.01" name="reminder_charge" class="form-control"
                                                value="{{ optional($settings)->default_reminder_charge ?? 0 }}"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group"><label>Penalty % Per Day</label><input type="number"
                                                step="0.01" name="penalty_percent_per_day" class="form-control"
                                                required></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label>Penalty Basis</label><select name="penalty_basis"
                                                class="form-control select2_demo_2" required>
                                                <option value="remaining_balance">Remaining Balance</option>
                                                <option value="full_loan">Full Loan</option>
                                            </select></div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group"><label>Purpose</label>
                                            <textarea name="purpose" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group"><label>Notes</label>
                                            <textarea name="notes" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary">Submit Application</button>
                            <a href="{{ route('micro.applications.index') }}" class="btn btn-secondary">Back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
