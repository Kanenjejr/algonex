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

                    <li class="breadcrumb-item active">
                        <strong>Sales Pipeline</strong>
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

        {{-- ================= TOP SUMMARY ================= --}}
        <div class="row mt-3">

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-warning text-white text-center">
                        <h5>Leads</h5>
                        <h2>{{ number_format($leads ?? 0) }}</h2>
                        <small>Total leads</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-primary text-white text-center">
                        <h5>Opportunities</h5>
                        <h2>{{ number_format($opportunities ?? 0) }}</h2>
                        <small>Total opportunities</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-success text-white text-center">
                        <h5>Invoices</h5>
                        <h2>{{ number_format($invoices ?? 0) }}</h2>
                        <small>Total invoices</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-danger text-white text-center">
                        <h5>Payments</h5>
                        <h2>{{ number_format($payments ?? 0) }}</h2>
                        <small>Total payments</small>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= PIPELINE KPI ================= --}}
        <div class="row">

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content text-center">
                        <h5>Total Pipelines</h5>
                        <h2 class="text-primary">{{ $totalPipelines ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content text-center">
                        <h5>Open</h5>
                        <h2 class="text-warning">{{ $openPipelines ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content text-center">
                        <h5>Pipeline Value</h5>
                        <h2 class="text-info">{{ number_format($pipelineValue ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content text-center">
                        <h5>Actual Value</h5>
                        <h2 class="text-success">{{ number_format($actualValue ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= CREATE PIPELINE ================= --}}
        @can('Create-Pipelines')
            <div class="ibox mt-3">
                <div class="ibox-title bg-primary">
                    <h5>Create Sales Pipeline</h5>
                </div>

                <div class="ibox-content">

                    <form method="POST" action="{{ route('sales.pipeline.store') }}">
                        @csrf

                        <div class="row">

                            {{-- TITLE --}}
                            <div class="col-md-4">
                                <label>Pipeline Title *</label>

                                <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                            </div>

                            {{-- CUSTOMER --}}
                            <div class="col-md-4">
                                <label>Customer</label>

                                <select name="customer_id" class="form-control select2_demo_2">
                                    <option value="">Select Customer</option>

                                    @foreach ($customersList as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->customer_code ?? '' }} - {{ $customer->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- LEAD --}}
                            <div class="col-md-4">
                                <label>Lead</label>

                                <select name="lead_id" class="form-control select2_demo_2">
                                    <option value="">Select Lead</option>

                                    @foreach ($leadsList as $lead)
                                        <option value="{{ $lead->id }}"
                                            {{ old('lead_id') == $lead->id ? 'selected' : '' }}>
                                            {{ $lead->customer_name ?? '' }} - {{ $lead->phone ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- OPPORTUNITY --}}
                            <div class="col-md-4">
                                <label>Opportunity</label>

                                <select name="opportunity_id" class="form-control select2_demo_2">
                                    <option value="">Select Opportunity</option>

                                    @foreach ($opportunitiesList as $opportunity)
                                        <option value="{{ $opportunity->id }}"
                                            {{ old('opportunity_id') == $opportunity->id ? 'selected' : '' }}>
                                            {{ $opportunity->opportunity_name ?? '' }}
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
                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
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

                            {{-- STAGE --}}
                            <div class="col-md-4">
                                <label>Stage *</label>

                                <select name="stage" class="form-control select2_demo_2" required>
                                    <option value="Lead" {{ old('stage', 'Lead') == 'Lead' ? 'selected' : '' }}>Lead</option>
                                    <option value="Opportunity" {{ old('stage') == 'Opportunity' ? 'selected' : '' }}>
                                        Opportunity</option>
                                    <option value="Proposal" {{ old('stage') == 'Proposal' ? 'selected' : '' }}>Proposal
                                    </option>
                                    <option value="Negotiation" {{ old('stage') == 'Negotiation' ? 'selected' : '' }}>
                                        Negotiation</option>
                                    <option value="Invoice" {{ old('stage') == 'Invoice' ? 'selected' : '' }}>Invoice</option>
                                    <option value="Payment" {{ old('stage') == 'Payment' ? 'selected' : '' }}>Payment</option>
                                    <option value="Won" {{ old('stage') == 'Won' ? 'selected' : '' }}>Won</option>
                                    <option value="Lost" {{ old('stage') == 'Lost' ? 'selected' : '' }}>Lost</option>
                                    <option value="On Hold" {{ old('stage') == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                </select>
                            </div>

                            {{-- STATUS --}}
                            <div class="col-md-4">
                                <label>Status *</label>

                                <select name="status" class="form-control select2_demo_2" required>
                                    <option value="Open" {{ old('status', 'Open') == 'Open' ? 'selected' : '' }}>Open
                                    </option>
                                    <option value="In Progress" {{ old('status') == 'In Progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed
                                    </option>
                                    <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled
                                    </option>
                                    <option value="Lost" {{ old('status') == 'Lost' ? 'selected' : '' }}>Lost</option>
                                </select>
                            </div>

                            {{-- ASSIGNED --}}
                            <div class="col-md-4">
                                <label>Assigned To</label>

                                <select name="assigned_to" class="form-control select2_demo_2">
                                    <option value="">Select User</option>

                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- EXPECTED VALUE --}}
                            <div class="col-md-4">
                                <label>Expected Value</label>

                                <input type="number" name="expected_value" value="{{ old('expected_value', 0) }}"
                                    step="0.01" class="form-control">
                            </div>

                            {{-- ACTUAL VALUE --}}
                            <div class="col-md-4">
                                <label>Actual Value</label>

                                <input type="number" name="actual_value" value="{{ old('actual_value', 0) }}"
                                    step="0.01" class="form-control">
                            </div>

                            {{-- PROBABILITY --}}
                            <div class="col-md-4">
                                <label>Probability (%)</label>

                                <input type="number" name="probability" value="{{ old('probability', 0) }}" min="0"
                                    max="100" class="form-control">
                            </div>

                            {{-- EXPECTED CLOSE --}}
                            <div class="col-md-4">
                                <label>Expected Close Date</label>

                                <input type="date" name="expected_close_date" value="{{ old('expected_close_date') }}"
                                    class="form-control">
                            </div>

                            {{-- CLOSED DATE --}}
                            <div class="col-md-4">
                                <label>Closed Date</label>

                                <input type="date" name="closed_date" value="{{ old('closed_date') }}"
                                    class="form-control">
                            </div>

                            {{-- DESCRIPTION --}}
                            <div class="col-md-12">
                                <label>Description</label>

                                <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
                            </div>

                        </div>

                        <button class="btn btn-success mt-3">
                            <i class="fa fa-save"></i>
                            Save Pipeline
                        </button>

                        <button type="reset" class="btn btn-default mt-3">
                            <i class="fa fa-refresh"></i>
                            Reset
                        </button>

                    </form>
                </div>
            </div>
        @endcan

        {{-- ================= PIPELINE TABLE ================= --}}
        <div class="ibox">
            <div class="ibox-title">
                <h5>Sales Pipeline List</h5>
            </div>

            <div class="ibox-content">

                <div class="table-responsive">

                    <table class="table table-striped table-bordered dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Customer</th>
                                <th>Stage</th>
                                <th>Status</th>
                                <th>Expected</th>
                                <th>Actual</th>
                                <th>Probability</th>
                                <th>Assigned To</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pipelines as $k => $pipeline)
                                <tr>
                                    <td>{{ $k + 1 }}</td>

                                    <td>{{ $pipeline->pipeline_code }}</td>

                                    <td>{{ $pipeline->title }}</td>

                                    <td>{{ $pipeline->customer->customer_name ?? '-' }}</td>

                                    <td>
                                        <span class="badge badge-info">
                                            {{ $pipeline->stage }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($pipeline->status == 'Completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif ($pipeline->status == 'Lost' || $pipeline->status == 'Cancelled')
                                            <span class="badge badge-danger">{{ $pipeline->status }}</span>
                                        @elseif ($pipeline->status == 'In Progress')
                                            <span class="badge badge-primary">In Progress</span>
                                        @else
                                            <span class="badge badge-warning">Open</span>
                                        @endif
                                    </td>

                                    <td>{{ number_format($pipeline->expected_value, 2) }}</td>

                                    <td>{{ number_format($pipeline->actual_value, 2) }}</td>

                                    <td>{{ $pipeline->probability }}%</td>

                                    <td>{{ $pipeline->assignedUser->name ?? '-' }}</td>

                                    <td>
                                        <div class="btn-group">

                                            @can('Edit-Pipelines')
                                                <a href="{{ route('sales.pipeline.edit', ['id' => Crypt::encryptString($pipeline->id)]) }}"
                                                    class="btn btn-xs btn-warning">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('Delete-Pipelines')
                                                <form
                                                    action="{{ route('sales.pipeline.delete', ['id' => Crypt::encryptString($pipeline->id)]) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Delete this pipeline?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-danger">
                                        No Pipeline Records Found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const businessUnitsUrl = "{{ route('sales.ajax.business.units', ':companyId') }}";
            const workPointsUrl = "{{ route('sales.ajax.work.points', ':unitId') }}";

            $('#company').on('change', function() {

                let companyId = $(this).val();

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
            });

            $('#business').on('change', function() {

                let unitId = $(this).val();

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
                            $('#work_point').append(
                                $('<option>', {
                                    value: work.id,
                                    text: `${work.work_code} - ${work.work_name}`
                                })
                            );
                        });

                        $('#work_point').trigger('change.select2');
                    });
            });

        });
    </script>

@endsection
