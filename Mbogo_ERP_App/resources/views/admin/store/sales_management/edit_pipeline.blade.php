@php
    use Illuminate\Support\Facades\Crypt;
@endphp

@extends('layouts.salesMaster')

@section('content')
    <style>
        .select2-container {
            width: 100% !important;
        }

        select.select2-hidden-accessible {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            position: absolute !important;
        }
    </style>

    <div class="wrapper wrapper-content">

        {{-- ================= HEADER ================= --}}
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-8">
                <h2 class="dashboard-title">Sales Management Module</h2>

                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li>
                        <a href="{{ route('sales.dashboard') }}">Sales Management</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li>
                        <a href="{{ route('sales.pipeline') }}">Sales Pipeline</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Pipeline</strong>
                    </li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Current Date</h4>

                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>
                            @php
                                $carbon = \Carbon\Carbon::now();
                                echo $carbon->format('l') . ' , ' . $carbon->toDateString();
                            @endphp
                        </strong>
                    </li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Time</h4>

                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>
                            <table>
                                <tr>
                                    <td id="Hour" style="color:green;"></td>
                                    <td id="Minut" style="color:green;"></td>
                                    <td id="Second" style="color:red;"></td>
                                </tr>
                            </table>
                        </strong>
                    </li>
                </ol>
            </div>
        </div>

        <script>
            function timedMsg() {
                setInterval(change_time, 1000);
            }

            function change_time() {
                const d = new Date();

                document.getElementById('Hour').innerHTML =
                    String(d.getHours()).padStart(2, '0') + ':';

                document.getElementById('Minut').innerHTML =
                    String(d.getMinutes()).padStart(2, '0') + ':';

                document.getElementById('Second').innerHTML =
                    String(d.getSeconds()).padStart(2, '0');
            }

            timedMsg();
        </script>

        {{-- ================= EDIT PIPELINE ================= --}}
        <div class="ibox mt-3">
            <div class="ibox-title bg-primary">
                <h5>Edit Sales Pipeline</h5>
            </div>

            <div class="ibox-content">

                <form method="POST"
                    action="{{ route('sales.pipeline.update', ['id' => Crypt::encryptString($pipeline->id)]) }}">
                    @csrf

                    <div class="row">

                        <div class="col-md-4">
                            <label>Pipeline Code</label>

                            <input type="text" value="{{ $pipeline->pipeline_code }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Pipeline Title *</label>

                            <input type="text" name="title" value="{{ old('title', $pipeline->title) }}"
                                class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label>Customer</label>

                            <select name="customer_id" class="form-control select2_demo_2">
                                <option value="">Select Customer</option>

                                @foreach ($customersList as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ old('customer_id', $pipeline->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->customer_code ?? '' }} - {{ $customer->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Lead</label>

                            <select name="lead_id" class="form-control select2_demo_2">
                                <option value="">Select Lead</option>

                                @foreach ($leadsList as $lead)
                                    <option value="{{ $lead->id }}"
                                        {{ old('lead_id', $pipeline->lead_id) == $lead->id ? 'selected' : '' }}>
                                        {{ $lead->customer_name ?? '' }} - {{ $lead->phone ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Opportunity</label>

                            <select name="opportunity_id" class="form-control select2_demo_2">
                                <option value="">Select Opportunity</option>

                                @foreach ($opportunitiesList as $opportunity)
                                    <option value="{{ $opportunity->id }}"
                                        {{ old('opportunity_id', $pipeline->opportunity_id) == $opportunity->id ? 'selected' : '' }}>
                                        {{ $opportunity->opportunity_name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Assigned To</label>

                            <select name="assigned_to" class="form-control select2_demo_2">
                                <option value="">Select User</option>

                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('assigned_to', $pipeline->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- COMPANY --}}
                        <div class="col-md-4">
                            <label>Company</label>

                            <select name="company_id" id="company" class="form-control select2_demo_2">
                                <option value="">Select Company</option>

                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id', $pipeline->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->company_code ?? '' }} - {{ $company->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- BUSINESS UNIT --}}
                        <div class="col-md-4">
                            <label>Business Unit</label>

                            <select name="business_unit_id" id="business" class="form-control select2_demo_2">
                                <option value="">Select Business Unit</option>
                            </select>
                        </div>

                        {{-- WORK POINT --}}
                        <div class="col-md-4">
                            <label>Work Point / Location</label>

                            <select name="work_point_id" id="work_point" class="form-control select2_demo_2">
                                <option value="">Select Work Point</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Stage *</label>

                            <select name="stage" class="form-control select2_demo_2" required>
                                <option value="Lead" {{ old('stage', $pipeline->stage) == 'Lead' ? 'selected' : '' }}>
                                    Lead</option>
                                <option value="Opportunity"
                                    {{ old('stage', $pipeline->stage) == 'Opportunity' ? 'selected' : '' }}>Opportunity
                                </option>
                                <option value="Proposal"
                                    {{ old('stage', $pipeline->stage) == 'Proposal' ? 'selected' : '' }}>Proposal</option>
                                <option value="Negotiation"
                                    {{ old('stage', $pipeline->stage) == 'Negotiation' ? 'selected' : '' }}>Negotiation
                                </option>
                                <option value="Invoice"
                                    {{ old('stage', $pipeline->stage) == 'Invoice' ? 'selected' : '' }}>Invoice</option>
                                <option value="Payment"
                                    {{ old('stage', $pipeline->stage) == 'Payment' ? 'selected' : '' }}>Payment</option>
                                <option value="Won" {{ old('stage', $pipeline->stage) == 'Won' ? 'selected' : '' }}>Won
                                </option>
                                <option value="Lost" {{ old('stage', $pipeline->stage) == 'Lost' ? 'selected' : '' }}>
                                    Lost</option>
                                <option value="On Hold"
                                    {{ old('stage', $pipeline->stage) == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Status *</label>

                            <select name="status" class="form-control select2_demo_2" required>
                                <option value="Open" {{ old('status', $pipeline->status) == 'Open' ? 'selected' : '' }}>
                                    Open</option>
                                <option value="In Progress"
                                    {{ old('status', $pipeline->status) == 'In Progress' ? 'selected' : '' }}>In Progress
                                </option>
                                <option value="Completed"
                                    {{ old('status', $pipeline->status) == 'Completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="Cancelled"
                                    {{ old('status', $pipeline->status) == 'Cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                                <option value="Lost" {{ old('status', $pipeline->status) == 'Lost' ? 'selected' : '' }}>
                                    Lost</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Expected Value</label>

                            <input type="number" name="expected_value"
                                value="{{ old('expected_value', $pipeline->expected_value) }}" step="0.01"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Actual Value</label>

                            <input type="number" name="actual_value"
                                value="{{ old('actual_value', $pipeline->actual_value) }}" step="0.01"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Probability (%)</label>

                            <input type="number" name="probability"
                                value="{{ old('probability', $pipeline->probability) }}" min="0" max="100"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Expected Close Date</label>

                            <input type="date" name="expected_close_date"
                                value="{{ old('expected_close_date', $pipeline->expected_close_date ? \Carbon\Carbon::parse($pipeline->expected_close_date)->format('Y-m-d') : '') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Closed Date</label>

                            <input type="date" name="closed_date"
                                value="{{ old('closed_date', $pipeline->closed_date ? \Carbon\Carbon::parse($pipeline->closed_date)->format('Y-m-d') : '') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label>Description</label>

                            <textarea name="description" rows="5" class="form-control">{{ old('description', $pipeline->description) }}</textarea>
                        </div>

                    </div>

                    <button class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Update Pipeline
                    </button>

                    <a href="{{ route('sales.pipeline') }}" class="btn btn-default mt-3">
                        <i class="fa fa-arrow-left"></i>
                        Back
                    </a>

                </form>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const businessUnitsUrl = "{{ route('sales.ajax.business.units', ':companyId') }}";
            const workPointsUrl = "{{ route('sales.ajax.work.points', ':unitId') }}";

            const selectedCompany = "{{ old('company_id', $pipeline->company_id) }}";
            const selectedBusiness = "{{ old('business_unit_id', $pipeline->business_unit_id) }}";
            const selectedWorkPoint = "{{ old('work_point_id', $pipeline->work_point_id) }}";

            function loadBusinessUnits(companyId, selectedUnit = null) {

                $('#business')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                $('#work_point')
                    .empty()
                    .append('<option value="">Select Work Point</option>')
                    .trigger('change.select2');

                if (!companyId) {
                    $('#business')
                        .empty()
                        .append('<option value="">Select Business Unit</option>')
                        .trigger('change.select2');

                    return;
                }

                let url = businessUnitsUrl.replace(':companyId', companyId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {

                        $('#business')
                            .empty()
                            .append('<option value="">Select Business Unit</option>');

                        data.forEach(unit => {

                            let isSelected = String(unit.id) === String(selectedUnit);

                            $('#business').append(
                                $('<option>', {
                                    value: unit.id,
                                    text: `${unit.unit_code} - ${unit.unit_name}`,
                                    selected: isSelected
                                })
                            );

                        });

                        $('#business').trigger('change.select2');

                        if (selectedUnit) {
                            loadWorkPoints(selectedUnit, selectedWorkPoint);
                        }

                    });
            }

            function loadWorkPoints(unitId, selectedPoint = null) {

                $('#work_point')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                if (!unitId) {
                    $('#work_point')
                        .empty()
                        .append('<option value="">Select Work Point</option>')
                        .trigger('change.select2');

                    return;
                }

                let url = workPointsUrl.replace(':unitId', unitId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {

                        $('#work_point')
                            .empty()
                            .append('<option value="">Select Work Point</option>');

                        data.forEach(work => {

                            let isSelected = String(work.id) === String(selectedPoint);

                            $('#work_point').append(
                                $('<option>', {
                                    value: work.id,
                                    text: `${work.work_code} - ${work.work_name}`,
                                    selected: isSelected
                                })
                            );

                        });

                        $('#work_point').trigger('change.select2');

                    });
            }

            $('#company').on('change', function() {
                loadBusinessUnits($(this).val(), null);
            });

            $('#business').on('change', function() {
                loadWorkPoints($(this).val(), null);
            });

            if (selectedCompany) {
                loadBusinessUnits(selectedCompany, selectedBusiness);
            }

        });
    </script>
@endsection
