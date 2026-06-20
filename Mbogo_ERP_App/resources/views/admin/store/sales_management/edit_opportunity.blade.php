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
                        <a href="{{ route('sales.opportunities') }}">Opportunities</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Opportunity</strong>
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

        {{-- ================= EDIT FORM ================= --}}
        <div class="ibox mt-3">
            <div class="ibox-title bg-primary">
                <h5>Edit Opportunity</h5>
            </div>

            <div class="ibox-content">

                <form method="POST"
                    action="{{ route('opportunities.update', ['id' => Crypt::encryptString($opportunity->id)]) }}">
                    @csrf

                    <div class="row">

                        {{-- OPPORTUNITY NAME --}}
                        <div class="col-md-4">
                            <label>Opportunity Name *</label>

                            <input type="text" name="opportunity_name"
                                value="{{ old('opportunity_name', $opportunity->opportunity_name) }}" class="form-control"
                                required>
                        </div>

                        {{-- CUSTOMER --}}
                        <div class="col-md-4">
                            <label>Customer *</label>

                            <select name="cstm_id" class="form-control select2_demo_2" required>
                                <option value="">Select Customer</option>

                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('cstm_id', $opportunity->cstm_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->customer_code ?? '' }} - {{ $c->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ASSIGNED TO --}}
                        <div class="col-md-4">
                            <label>Assigned To</label>

                            <select name="assigned_to" class="form-control select2_demo_2">
                                <option value="">Select User</option>

                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('assigned_to', $opportunity->assigned_to) == $user->id ? 'selected' : '' }}>
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
                                        {{ old('company_id', $opportunity->company_id) == $company->id ? 'selected' : '' }}>
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

                        {{-- ESTIMATED VALUE --}}
                        <div class="col-md-4">
                            <label>Estimated Value</label>

                            <input type="number" name="estimated_value"
                                value="{{ old('estimated_value', $opportunity->estimated_value) }}" class="form-control"
                                step="0.01">
                        </div>

                        {{-- CLOSE EXPECTED --}}
                        <div class="col-md-4">
                            <label>Expected Close Date</label>

                            <input type="date" name="close_expected"
                                value="{{ old('close_expected', $opportunity->close_expected ? \Carbon\Carbon::parse($opportunity->close_expected)->format('Y-m-d') : '') }}"
                                class="form-control">
                        </div>

                        {{-- STAGE --}}
                        <div class="col-md-4">
                            <label>Stage *</label>

                            <select name="stage" class="form-control select2_demo_2" required>
                                <option value="Prospecting"
                                    {{ old('stage', $opportunity->stage) == 'Prospecting' ? 'selected' : '' }}>Prospecting
                                </option>
                                <option value="Qualification"
                                    {{ old('stage', $opportunity->stage) == 'Qualification' ? 'selected' : '' }}>
                                    Qualification</option>
                                <option value="Proposal"
                                    {{ old('stage', $opportunity->stage) == 'Proposal' ? 'selected' : '' }}>Proposal
                                </option>
                                <option value="Negotiation"
                                    {{ old('stage', $opportunity->stage) == 'Negotiation' ? 'selected' : '' }}>Negotiation
                                </option>
                                <option value="Closed Won"
                                    {{ old('stage', $opportunity->stage) == 'Closed Won' ? 'selected' : '' }}>Closed Won
                                </option>
                                <option value="Closed Lost"
                                    {{ old('stage', $opportunity->stage) == 'Closed Lost' ? 'selected' : '' }}>Closed Lost
                                </option>
                                <option value="On Hold"
                                    {{ old('stage', $opportunity->stage) == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-4">
                            <label>Status *</label>

                            <select name="status" class="form-control select2_demo_2" required>
                                <option value="Open"
                                    {{ old('status', $opportunity->status) == 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="Won"
                                    {{ old('status', $opportunity->status) == 'Won' ? 'selected' : '' }}>Won</option>
                                <option value="Lost"
                                    {{ old('status', $opportunity->status) == 'Lost' ? 'selected' : '' }}>Lost</option>
                                <option value="Deleted"
                                    {{ old('status', $opportunity->status) == 'Deleted' ? 'selected' : '' }}>Deleted
                                </option>
                            </select>
                        </div>

                        {{-- CREATED BY --}}
                        <div class="col-md-4">
                            <label>Created By</label>

                            <input type="text" value="{{ $opportunity->user->name ?? '-' }}" class="form-control"
                                readonly>
                        </div>

                        {{-- CREATED DATE --}}
                        <div class="col-md-4">
                            <label>Created Date</label>

                            <input type="text"
                                value="{{ $opportunity->created_at ? $opportunity->created_at->format('Y-m-d H:i') : '-' }}"
                                class="form-control" readonly>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="col-md-12">
                            <label>Description</label>

                            <textarea name="description" rows="5" class="form-control">{{ old('description', $opportunity->description) }}</textarea>
                        </div>

                    </div>

                    <button class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Update Opportunity
                    </button>

                    <a href="{{ route('sales.opportunities') }}" class="btn btn-default mt-3">
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

            const selectedCompany = "{{ old('company_id', $opportunity->company_id) }}";
            const selectedBusiness = "{{ old('business_unit_id', $opportunity->business_unit_id) }}";
            const selectedWorkPoint = "{{ old('work_point_id', $opportunity->work_point_id) }}";

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
