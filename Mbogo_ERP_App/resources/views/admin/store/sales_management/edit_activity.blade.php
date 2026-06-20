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
                        <a href="{{ route('sales.activities') }}">Activities</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Activity</strong>
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
                <h5>Edit Activity</h5>
            </div>

            <div class="ibox-content">
                <form method="POST"
                    action="{{ route('activities.update', ['id' => Crypt::encryptString($activity->id)]) }}">
                    @csrf

                    <div class="row">

                        {{-- SUBJECT --}}
                        <div class="col-md-4">
                            <label>Subject *</label>

                            <input type="text" name="subject" value="{{ old('subject', $activity->subject) }}"
                                class="form-control" required>
                        </div>

                        {{-- TYPE --}}
                        <div class="col-md-4">
                            <label>Type</label>

                            <select name="type" class="form-control select2_demo_2">
                                <option value="">Select Type</option>
                                <option value="call" {{ old('type', $activity->type) == 'call' ? 'selected' : '' }}>Call
                                </option>
                                <option value="email" {{ old('type', $activity->type) == 'email' ? 'selected' : '' }}>Email
                                </option>
                                <option value="meeting" {{ old('type', $activity->type) == 'meeting' ? 'selected' : '' }}>
                                    Meeting</option>
                                <option value="requisition"
                                    {{ old('type', $activity->type) == 'requisition' ? 'selected' : '' }}>Requisition
                                </option>
                                <option value="purchase" {{ old('type', $activity->type) == 'purchase' ? 'selected' : '' }}>
                                    Purchase</option>
                                <option value="stock" {{ old('type', $activity->type) == 'stock' ? 'selected' : '' }}>Stock
                                </option>
                                <option value="invoice" {{ old('type', $activity->type) == 'invoice' ? 'selected' : '' }}>
                                    Invoice</option>
                            </select>
                        </div>

                        {{-- MODULE --}}
                        <div class="col-md-4">
                            <label>Module</label>

                            <select name="module" class="form-control select2_demo_2">
                                <option value="">Select Module</option>
                                <option value="sales" {{ old('module', $activity->module) == 'sales' ? 'selected' : '' }}>
                                    Sales</option>
                                <option value="purchasing"
                                    {{ old('module', $activity->module) == 'purchasing' ? 'selected' : '' }}>Purchasing
                                </option>
                                <option value="stock" {{ old('module', $activity->module) == 'stock' ? 'selected' : '' }}>
                                    Stock</option>
                                <option value="finance"
                                    {{ old('module', $activity->module) == 'finance' ? 'selected' : '' }}>Finance</option>
                            </select>
                        </div>

                        {{-- COMPANY --}}
                        <div class="col-md-4">
                            <label>Company</label>

                            <select name="company_id" id="company" class="form-control select2_demo_2">
                                <option value="">Select Company</option>

                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id', $activity->company_id) == $company->id ? 'selected' : '' }}>
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

                        {{-- ASSIGNED TO --}}
                        <div class="col-md-4">
                            <label>Assigned To</label>

                            <select name="assigned_to" class="form-control select2_demo_2">
                                <option value="">Select User</option>

                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('assigned_to', $activity->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ACTIVITY DATE --}}
                        <div class="col-md-4">
                            <label>Activity Date</label>

                            <input type="datetime-local" name="activity_date"
                                value="{{ old('activity_date', $activity->activity_date ? \Carbon\Carbon::parse($activity->activity_date)->format('Y-m-d\TH:i') : '') }}"
                                class="form-control">
                        </div>

                        {{-- DUE DATE --}}
                        <div class="col-md-4">
                            <label>Due Date</label>

                            <input type="datetime-local" name="due_at"
                                value="{{ old('due_at', $activity->due_at ? \Carbon\Carbon::parse($activity->due_at)->format('Y-m-d\TH:i') : '') }}"
                                class="form-control">
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-4">
                            <label>Status *</label>

                            <select name="status" class="form-control select2_demo_2" required>
                                <option value="Pending"
                                    {{ old('status', $activity->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved"
                                    {{ old('status', $activity->status) == 'Approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="Done" {{ old('status', $activity->status) == 'Done' ? 'selected' : '' }}>
                                    Done</option>
                                <option value="Cancelled"
                                    {{ old('status', $activity->status) == 'Cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                                <option value="Deleted"
                                    {{ old('status', $activity->status) == 'Deleted' ? 'selected' : '' }}>Deleted</option>
                            </select>
                        </div>

                        {{-- CREATED BY --}}
                        <div class="col-md-4">
                            <label>Created By</label>

                            <input type="text" value="{{ $activity->user->name ?? '-' }}" class="form-control" readonly>
                        </div>

                        {{-- CREATED DATE --}}
                        <div class="col-md-4">
                            <label>Created Date</label>

                            <input type="text"
                                value="{{ $activity->created_at ? $activity->created_at->format('Y-m-d H:i') : '-' }}"
                                class="form-control" readonly>
                        </div>

                        {{-- BODY --}}
                        <div class="col-md-12">
                            <label>Body / Notes</label>

                            <textarea name="body" rows="5" class="form-control">{{ old('body', $activity->body) }}</textarea>
                        </div>

                    </div>

                    <button class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Update Activity
                    </button>

                    <a href="{{ route('sales.activities') }}" class="btn btn-default mt-3">
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

            const selectedCompany = "{{ old('company_id', $activity->company_id) }}";
            const selectedWorkPoint = "{{ old('work_point_id', $activity->work_point_id) }}";

            let selectedBusiness = "";

            function loadBusinessUnits(companyId) {

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

                            $('#business').append(
                                $('<option>', {
                                    value: unit.id,
                                    text: `${unit.unit_code} - ${unit.unit_name}`
                                })
                            );

                        });

                        $('#business').trigger('change.select2');

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
                loadBusinessUnits($(this).val());
            });

            $('#business').on('change', function() {
                loadWorkPoints($(this).val(), null);
            });

            if (selectedCompany) {
                loadBusinessUnits(selectedCompany);
            }

        });
    </script>
@endsection
