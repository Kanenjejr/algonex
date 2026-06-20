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
                        <strong>Activities</strong>
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
                        <h5>Total Activities</h5>
                        <h2>{{ $totalActivities ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-warning text-white text-center">
                        <h5>Pending</h5>
                        <h2>{{ $pendingActivities ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-success text-white text-center">
                        <h5>Done</h5>
                        <h2>{{ $doneActivities ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-info text-white text-center">
                        <h5>Approved</h5>
                        <h2>{{ $approvedActivities ?? 0 }}</h2>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= CREATE ACTIVITY ================= --}}
        <div class="ibox mt-3">
            <div class="ibox-title bg-primary">
                <h5>Create Activity</h5>
            </div>

            <div class="ibox-content">
                <form method="POST" action="{{ route('activities.store') }}">
                    @csrf

                    <div class="row">

                        {{-- SUBJECT --}}
                        <div class="col-md-4">
                            <label>Subject *</label>

                            <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" required>
                        </div>

                        {{-- TYPE --}}
                        <div class="col-md-4">
                            <label>Type</label>

                            <select name="type" class="form-control select2_demo_2">
                                <option value="">Select Type</option>
                                <option value="call" {{ old('type') == 'call' ? 'selected' : '' }}>Call</option>
                                <option value="email" {{ old('type') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="meeting" {{ old('type') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                <option value="requisition" {{ old('type') == 'requisition' ? 'selected' : '' }}>
                                    Requisition</option>
                                <option value="purchase" {{ old('type') == 'purchase' ? 'selected' : '' }}>Purchase
                                </option>
                                <option value="stock" {{ old('type') == 'stock' ? 'selected' : '' }}>Stock</option>
                                <option value="invoice" {{ old('type') == 'invoice' ? 'selected' : '' }}>Invoice</option>
                            </select>
                        </div>

                        {{-- MODULE --}}
                        <div class="col-md-4">
                            <label>Module</label>

                            <select name="module" class="form-control select2_demo_2">
                                <option value="">Select Module</option>
                                <option value="sales" {{ old('module') == 'sales' ? 'selected' : '' }}>Sales</option>
                                <option value="purchasing" {{ old('module') == 'purchasing' ? 'selected' : '' }}>Purchasing
                                </option>
                                <option value="stock" {{ old('module') == 'stock' ? 'selected' : '' }}>Stock</option>
                                <option value="finance" {{ old('module') == 'finance' ? 'selected' : '' }}>Finance</option>
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

                        {{-- ACTIVITY DATE --}}
                        <div class="col-md-4">
                            <label>Activity Date</label>

                            <input type="datetime-local" name="activity_date" value="{{ old('activity_date') }}"
                                class="form-control">
                        </div>

                        {{-- DUE DATE --}}
                        <div class="col-md-4">
                            <label>Due Date</label>

                            <input type="datetime-local" name="due_at" value="{{ old('due_at') }}" class="form-control">
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-4">
                            <label>Status *</label>

                            <select name="status" class="form-control select2_demo_2" required>
                                <option value="Pending" {{ old('status', 'Pending') == 'Pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="Approved" {{ old('status') == 'Approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="Done" {{ old('status') == 'Done' ? 'selected' : '' }}>Done</option>
                                <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                                <option value="Deleted" {{ old('status') == 'Deleted' ? 'selected' : '' }}>Deleted
                                </option>
                            </select>
                        </div>

                        {{-- BODY --}}
                        <div class="col-md-12">
                            <label>Body / Notes</label>

                            <textarea name="body" rows="4" class="form-control">{{ old('body') }}</textarea>
                        </div>

                    </div>

                    <button class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Save Activity
                    </button>

                    <button type="reset" class="btn btn-default mt-3">
                        <i class="fa fa-refresh"></i>
                        Reset
                    </button>
                </form>
            </div>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="ibox">
            <div class="ibox-title">
                <h5>Activities List</h5>
            </div>

            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Module</th>
                                <th>User</th>
                                <th>Assigned To</th>
                                <th>Company</th>
                                <th>Work Point</th>
                                <th>Activity Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($data as $k => $a)
                                <tr>
                                    <td>{{ $data->firstItem() + $k }}</td>

                                    <td>{{ $a->subject ?? '-' }}</td>

                                    <td>{{ ucfirst($a->type ?? '-') }}</td>

                                    <td>{{ ucfirst($a->module ?? '-') }}</td>

                                    <td>{{ $a->user->name ?? '-' }}</td>

                                    <td>{{ $a->assignedTo->name ?? '-' }}</td>

                                    <td>{{ $a->company->company_name ?? '-' }}</td>

                                    <td>{{ $a->workPoint->work_name ?? '-' }}</td>

                                    <td>
                                        {{ $a->activity_date ? \Carbon\Carbon::parse($a->activity_date)->format('d M Y H:i') : '-' }}
                                    </td>

                                    <td>
                                        {{ $a->due_at ? \Carbon\Carbon::parse($a->due_at)->format('d M Y H:i') : '-' }}
                                    </td>

                                    <td>
                                        @if ($a->status == 'Done')
                                            <span class="badge badge-success">Done</span>
                                        @elseif($a->status == 'Approved')
                                            <span class="badge badge-info">Approved</span>
                                        @elseif($a->status == 'Cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                        @elseif($a->status == 'Deleted')
                                            <span class="badge badge-danger">Deleted</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="btn-group">
                                            @can('Edit-Activities')
                                                <a href="{{ route('activities.edit', ['id' => Crypt::encryptString($a->id)]) }}"
                                                    class="btn btn-xs btn-warning">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('Delete-Activities')
                                                <form
                                                    action="{{ route('activities.delete', ['id' => Crypt::encryptString($a->id)]) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Delete this activity?')">
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
                                        No Activities Found
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

                    })
                    .catch(error => {

                        console.error(error);

                        $('#business')
                            .empty()
                            .append('<option value="">Error loading business units</option>')
                            .trigger('change.select2');

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

                    })
                    .catch(error => {

                        console.error(error);

                        $('#work_point')
                            .empty()
                            .append('<option value="">Error loading work points</option>')
                            .trigger('change.select2');

                    });

            });

        });
    </script>
@endsection
