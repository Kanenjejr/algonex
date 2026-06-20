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
                        <a href="{{ route('sales.campaigns.index') }}">Campaigns</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Campaign</strong>
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

        {{-- ================= EDIT CAMPAIGN ================= --}}
        <div class="ibox mt-3">
            <div class="ibox-title bg-primary">
                <h5>Edit Campaign</h5>
            </div>

            <div class="ibox-content">

                <form method="POST"
                    action="{{ route('campaigns.update', ['id' => Crypt::encryptString($campaign->id)]) }}">
                    @csrf

                    <div class="row">

                        {{-- COMPANY --}}
                        <div class="col-md-4">
                            <label>Company *</label>

                            <select name="company_id" id="company" class="form-control select2_demo_2" required>

                                <option value="">Select Company</option>

                                @foreach ($companies as $c)
                                    <option value="{{ $c->id }}" data-code="{{ $c->company_code }}"
                                        data-name="{{ $c->company_name }}"
                                        {{ old('company_id', $campaign->company_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->company_code }} - {{ $c->company_name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- BUSINESS --}}
                        <div class="col-md-4">
                            <label>Business *</label>

                            <select name="business_unit_id" id="business" class="form-control select2_demo_2" required>

                                <option value="">Select Business</option>

                            </select>
                        </div>

                        {{-- WORKPOINT --}}
                        <div class="col-md-4">
                            <label>Location *</label>

                            <select name="work_point_id" id="work_point" class="form-control select2_demo_2" required>

                                <option value="">Select Location</option>

                            </select>
                        </div>

                        {{-- NAME --}}
                        <div class="col-md-4">
                            <label>Name *</label>

                            <input type="text" name="name" value="{{ old('name', $campaign->name) }}"
                                class="form-control" required>
                        </div>

                        {{-- TYPE --}}
                        <div class="col-md-4">
                            <label>Type *</label>

                            <select name="type" class="form-control select2_demo_2" required>

                                <option value="">Select Type</option>

                                <option value="discount"
                                    {{ old('type', $campaign->type) == 'discount' ? 'selected' : '' }}>
                                    Discount
                                </option>

                                <option value="promotion"
                                    {{ old('type', $campaign->type) == 'promotion' ? 'selected' : '' }}>
                                    Promotion
                                </option>

                                <option value="awareness"
                                    {{ old('type', $campaign->type) == 'awareness' ? 'selected' : '' }}>
                                    Awareness
                                </option>

                                <option value="seasonal"
                                    {{ old('type', $campaign->type) == 'seasonal' ? 'selected' : '' }}>
                                    Seasonal
                                </option>

                            </select>
                        </div>

                        {{-- CUSTOMER TYPE --}}
                        <div class="col-md-4">
                            <label>Customer Type</label>

                            <select name="customer_type" class="form-control select2_demo_2">

                                <option value="all"
                                    {{ old('customer_type', $campaign->customer_type) == 'all' ? 'selected' : '' }}>
                                    All Customers
                                </option>

                                <option value="new"
                                    {{ old('customer_type', $campaign->customer_type) == 'new' ? 'selected' : '' }}>
                                    New Customers
                                </option>

                                <option value="existing"
                                    {{ old('customer_type', $campaign->customer_type) == 'existing' ? 'selected' : '' }}>
                                    Existing Customers
                                </option>

                            </select>
                        </div>

                        {{-- DISCOUNT --}}
                        <div class="col-md-4">
                            <label>Discount</label>

                            <input type="number" name="discount" value="{{ old('discount', $campaign->discount) }}"
                                class="form-control" step="0.01">
                        </div>

                        {{-- BUDGET --}}
                        <div class="col-md-4">
                            <label>Budget</label>

                            <input type="number" name="budget" value="{{ old('budget', $campaign->budget) }}"
                                class="form-control" step="0.01">
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-4">
                            <label>Status</label>

                            <select name="status" class="form-control select2_demo_2">

                                <option value="active"
                                    {{ old('status', $campaign->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>

                                <option value="inactive"
                                    {{ old('status', $campaign->status) == 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>

                            </select>
                        </div>

                        {{-- START --}}
                        <div class="col-md-4">
                            <label>Start Date *</label>

                            <input type="date" name="start_date"
                                value="{{ old('start_date', $campaign->start_date ? \Carbon\Carbon::parse($campaign->start_date)->format('Y-m-d') : '') }}"
                                class="form-control" required>
                        </div>

                        {{-- END --}}
                        <div class="col-md-4">
                            <label>End Date *</label>

                            <input type="date" name="end_date"
                                value="{{ old('end_date', $campaign->end_date ? \Carbon\Carbon::parse($campaign->end_date)->format('Y-m-d') : '') }}"
                                class="form-control" required>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="col-md-12">
                            <label>Description</label>

                            <textarea name="description" class="form-control">{{ old('description', $campaign->description) }}</textarea>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Update Campaign
                    </button>

                    <a href="{{ route('sales.campaigns.index') }}" class="btn btn-default mt-3">
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

            const selectedCompany = "{{ old('company_id', $campaign->company_id) }}";
            const selectedBusiness = "{{ old('business_unit_id', $campaign->business_unit_id) }}";
            const selectedWorkPoint = "{{ old('work_point_id', $campaign->work_point_id) }}";

            // ================= COMPANY → BUSINESS UNITS =================
            function loadBusinessUnits(companyId, selectedUnit = null) {

                $('#business')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                $('#work_point')
                    .empty()
                    .append('<option value="">Select Location</option>')
                    .trigger('change.select2');

                if (!companyId) {

                    $('#business')
                        .empty()
                        .append('<option value="">Select Business</option>')
                        .trigger('change.select2');

                    return;
                }

                let url = businessUnitsUrl.replace(':companyId', companyId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {

                        $('#business')
                            .empty()
                            .append('<option value="">Select Business</option>');

                        if (data.length > 0) {

                            data.forEach(b => {

                                let isSelected = String(b.id) === String(selectedUnit);

                                $('#business').append(
                                    $('<option>', {
                                        value: b.id,
                                        text: `${b.unit_code} - ${b.unit_name}`,
                                        selected: isSelected
                                    })
                                    .attr('data-code', b.unit_code)
                                    .attr('data-name', b.unit_name)
                                );

                            });

                        } else {

                            $('#business')
                                .empty()
                                .append('<option value="">No Business Unit Found</option>');
                        }

                        $('#business').trigger('change.select2');

                        if (selectedUnit) {
                            loadWorkPoints(selectedUnit, selectedWorkPoint);
                        }

                    })
                    .catch(error => {

                        console.error(error);

                        $('#business')
                            .empty()
                            .append('<option value="">Error loading business units</option>')
                            .trigger('change.select2');
                    });
            }

            // ================= BUSINESS UNIT → WORK POINTS =================
            function loadWorkPoints(unitId, selectedPoint = null) {

                $('#work_point')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                if (!unitId) {

                    $('#work_point')
                        .empty()
                        .append('<option value="">Select Location</option>')
                        .trigger('change.select2');

                    return;
                }

                let url = workPointsUrl.replace(':unitId', unitId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {

                        $('#work_point')
                            .empty()
                            .append('<option value="">Select Location</option>');

                        if (data.length > 0) {

                            data.forEach(w => {

                                let isSelected = String(w.id) === String(selectedPoint);

                                $('#work_point').append(
                                    $('<option>', {
                                        value: w.id,
                                        text: `${w.work_code} - ${w.work_name}`,
                                        selected: isSelected
                                    })
                                    .attr('data-code', w.work_code)
                                    .attr('data-name', w.work_name)
                                );

                            });

                        } else {

                            $('#work_point')
                                .empty()
                                .append('<option value="">No Location Found</option>');
                        }

                        $('#work_point').trigger('change.select2');

                    })
                    .catch(error => {

                        console.error(error);

                        $('#work_point')
                            .empty()
                            .append('<option value="">Error loading locations</option>')
                            .trigger('change.select2');
                    });
            }

            // ================= CHANGE EVENTS =================
            $('#company').on('change', function() {

                let companyId = $(this).val();

                loadBusinessUnits(companyId, null);

            });

            $('#business').on('change', function() {

                let unitId = $(this).val();

                loadWorkPoints(unitId, null);

            });

            // ================= AUTO LOAD CURRENT CAMPAIGN DATA =================
            if (selectedCompany) {
                loadBusinessUnits(selectedCompany, selectedBusiness);
            }

        });
    </script>
@endsection
