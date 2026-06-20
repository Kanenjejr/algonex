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
                        <strong>Opportunities</strong>
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

        {{-- ================= SUMMARY ================= --}}
        <div class="row mt-3">

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-primary text-white text-center">
                        <h5>Total Opportunities</h5>
                        <h2>{{ $totalOpportunities ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-info text-white text-center">
                        <h5>Open</h5>
                        <h2>{{ $openOpportunities ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-success text-white text-center">
                        <h5>Won</h5>
                        <h2>{{ $wonOpportunities ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-warning text-white text-center">
                        <h5>Expected Revenue</h5>
                        <h2>{{ number_format($expectedRevenue ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= FORM ================= --}}
        @can('Register-Opportunities')
            <div class="ibox mt-3">
                <div class="ibox-title bg-primary">
                    <h5>Add Opportunity</h5>
                </div>

                <div class="ibox-content">

                    <form method="POST" action="{{ route('opportunities.store') }}">
                        @csrf

                        <div class="row">

                            {{-- OPPORTUNITY NAME --}}
                            <div class="col-md-4">
                                <label>Opportunity Name *</label>

                                <input type="text" name="opportunity_name" value="{{ old('opportunity_name') }}"
                                    class="form-control" required>
                            </div>

                            {{-- CUSTOMER --}}
                            <div class="col-md-4">
                                <label>Customer *</label>

                                <select name="cstm_id" class="form-control select2_demo_2" required>
                                    <option value="">Select Customer</option>

                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" {{ old('cstm_id') == $c->id ? 'selected' : '' }}>
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
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
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

                            {{-- ESTIMATED VALUE --}}
                            <div class="col-md-4">
                                <label>Estimated Value</label>

                                <input type="number" name="estimated_value" value="{{ old('estimated_value', 0) }}"
                                    class="form-control" step="0.01">
                            </div>

                            {{-- CLOSE EXPECTED --}}
                            <div class="col-md-4">
                                <label>Expected Close Date</label>

                                <input type="date" name="close_expected" value="{{ old('close_expected') }}"
                                    class="form-control">
                            </div>

                            {{-- STAGE --}}
                            <div class="col-md-4">
                                <label>Stage *</label>

                                <select name="stage" class="form-control select2_demo_2" required>
                                    <option value="Prospecting"
                                        {{ old('stage', 'Prospecting') == 'Prospecting' ? 'selected' : '' }}>Prospecting
                                    </option>
                                    <option value="Qualification" {{ old('stage') == 'Qualification' ? 'selected' : '' }}>
                                        Qualification</option>
                                    <option value="Proposal" {{ old('stage') == 'Proposal' ? 'selected' : '' }}>Proposal
                                    </option>
                                    <option value="Negotiation" {{ old('stage') == 'Negotiation' ? 'selected' : '' }}>
                                        Negotiation</option>
                                    <option value="Closed Won" {{ old('stage') == 'Closed Won' ? 'selected' : '' }}>Closed Won
                                    </option>
                                    <option value="Closed Lost" {{ old('stage') == 'Closed Lost' ? 'selected' : '' }}>Closed
                                        Lost</option>
                                    <option value="On Hold" {{ old('stage') == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                </select>
                            </div>

                            {{-- STATUS --}}
                            <div class="col-md-4">
                                <label>Status *</label>

                                <select name="status" class="form-control select2_demo_2" required>
                                    <option value="Open" {{ old('status', 'Open') == 'Open' ? 'selected' : '' }}>Open
                                    </option>
                                    <option value="Won" {{ old('status') == 'Won' ? 'selected' : '' }}>Won</option>
                                    <option value="Lost" {{ old('status') == 'Lost' ? 'selected' : '' }}>Lost</option>
                                    <option value="Deleted" {{ old('status') == 'Deleted' ? 'selected' : '' }}>Deleted</option>
                                </select>
                            </div>

                            {{-- DESCRIPTION --}}
                            <div class="col-md-12">
                                <label>Description</label>

                                <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-md-12 mt-3">
                                <button class="btn btn-success">
                                    <i class="fa fa-save"></i>
                                    Save Opportunity
                                </button>

                                <button type="reset" class="btn btn-default">
                                    <i class="fa fa-refresh"></i>
                                    Reset
                                </button>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        @endcan

        {{-- ================= TABLE ================= --}}
        <div class="ibox">
            <div class="ibox-title">
                <h5>Opportunities List</h5>
            </div>

            <div class="ibox-content">

                <div class="table-responsive">

                    <table class="table table-striped table-bordered dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Opportunity</th>
                                <th>Customer</th>
                                <th>Company</th>
                                <th>Business Unit</th>
                                <th>Location</th>
                                <th>Estimated Value</th>
                                <th>Close Expected</th>
                                <th>Stage</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($data as $k => $o)
                                <tr>
                                    <td>{{ $data->firstItem() + $k }}</td>

                                    <td>{{ $o->opportunity_name ?? '-' }}</td>

                                    <td>{{ $o->customer->customer_name ?? '-' }}</td>

                                    <td>{{ $o->company->company_name ?? '-' }}</td>

                                    <td>{{ $o->businessUnit->unit_name ?? '-' }}</td>

                                    <td>{{ $o->workPoint->work_name ?? '-' }}</td>

                                    <td>{{ number_format($o->estimated_value ?? 0, 2) }}</td>

                                    <td>
                                        {{ $o->close_expected ? \Carbon\Carbon::parse($o->close_expected)->format('d M Y') : '-' }}
                                    </td>

                                    <td>
                                        <span class="badge badge-info">
                                            {{ $o->stage }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($o->status == 'Won')
                                            <span class="badge badge-success">Won</span>
                                        @elseif($o->status == 'Lost')
                                            <span class="badge badge-danger">Lost</span>
                                        @elseif($o->status == 'Deleted')
                                            <span class="badge badge-danger">Deleted</span>
                                        @else
                                            <span class="badge badge-warning">Open</span>
                                        @endif
                                    </td>

                                    <td>{{ $o->assignedUser->name ?? '-' }}</td>

                                    <td>
                                        <div class="btn-group">

                                            @can('Edit-Opportunities')
                                                <a href="{{ route('opportunities.edit', ['id' => Crypt::encryptString($o->id)]) }}"
                                                    class="btn btn-xs btn-warning">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('Delete-Opportunities')
                                                <form
                                                    action="{{ route('opportunities.delete', ['id' => Crypt::encryptString($o->id)]) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Delete this opportunity?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-danger">
                                        No Opportunities Found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>

                {{ $data->links() }}

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

                        if (data.length > 0) {
                            data.forEach(unit => {
                                $('#business').append(
                                    $('<option>', {
                                        value: unit.id,
                                        text: `${unit.unit_code} - ${unit.unit_name}`
                                    })
                                );
                            });
                        } else {
                            $('#business')
                                .empty()
                                .append('<option value="">No Business Unit Found</option>');
                        }

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

                        if (data.length > 0) {
                            data.forEach(work => {
                                $('#work_point').append(
                                    $('<option>', {
                                        value: work.id,
                                        text: `${work.work_code} - ${work.work_name}`
                                    })
                                );
                            });
                        } else {
                            $('#work_point')
                                .empty()
                                .append('<option value="">No Work Point Found</option>');
                        }

                        $('#work_point').trigger('change.select2');
                    });
            });

        });
    </script>

@endsection
