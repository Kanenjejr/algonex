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
                        <strong>Followups</strong>
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

        {{-- ================= PAGE TITLE ================= --}}
        <div class="ibox mt-3">
            <div class="ibox-title bg-primary">
                <h5>
                    <i class="fa fa-calendar-check-o"></i>
                    Customer Followups
                </h5>
            </div>

            <div class="ibox-content">
                <p class="text-muted" style="margin-bottom:0;">
                    Manage customer followups, priorities and completion status.
                </p>
            </div>
        </div>

        {{-- ================= CARDS ================= --}}
        <div class="row">

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5 class="text-muted">
                            Total Followups
                        </h5>

                        <h2 class="font-bold text-primary">
                            {{ $followups->count() }}
                        </h2>

                        <small>
                            All followup records
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5 class="text-muted">
                            Pending
                        </h5>

                        <h2 class="font-bold text-warning">
                            {{ $pendingFollowups }}
                        </h2>

                        <small>
                            Waiting followups
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5 class="text-muted">
                            Completed
                        </h5>

                        <h2 class="font-bold text-success">
                            {{ $completedFollowups }}
                        </h2>

                        <small>
                            Completed followups
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5 class="text-muted">
                            High Priority
                        </h5>

                        <h2 class="font-bold text-danger">
                            {{ $highPriorityFollowups }}
                        </h2>

                        <small>
                            Urgent followups
                        </small>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= FOLLOWUP FORM ================= --}}
        @can('Create-Followups')
            <div class="ibox" id="followupForm">

                <div class="ibox-title bg-primary">
                    <h5>
                        <i class="fa fa-plus"></i>
                        Schedule Followup
                    </h5>
                </div>

                <div class="ibox-content">

                    <form method="POST" action="{{ route('sales.followups.store') }}">
                        @csrf

                        <div class="row">

                            {{-- CUSTOMER --}}
                            <div class="col-md-4">
                                <label>Customer *</label>

                                <select name="customer_id" class="form-control select2_demo_2" required>

                                    <option value="">
                                        Select Customer
                                    </option>

                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->customer_code ?? '' }} - {{ $customer->customer_name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            {{-- DATE --}}
                            <div class="col-md-4">
                                <label>Followup Date *</label>

                                <input type="date" name="followup_date" value="{{ old('followup_date') }}"
                                    class="form-control" required>
                            </div>

                            {{-- PRIORITY --}}
                            <div class="col-md-4">
                                <label>Priority *</label>

                                <select name="priority" class="form-control select2_demo_2" required>

                                    <option value="">
                                        Select Priority
                                    </option>

                                    <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>
                                        Low
                                    </option>

                                    <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>
                                        Medium
                                    </option>

                                    <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>
                                        High
                                    </option>

                                </select>
                            </div>

                            {{-- STATUS --}}
                            <div class="col-md-4">
                                <label>Status</label>

                                <select name="status" class="form-control select2_demo_2">

                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>

                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>

                                </select>
                            </div>

                            {{-- NOTES --}}
                            <div class="col-md-8">
                                <label>Notes</label>

                                <textarea name="notes" rows="3" class="form-control" placeholder="Write notes here...">{{ old('notes') }}</textarea>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-success mt-3">
                            <i class="fa fa-save"></i>
                            Save Followup
                        </button>

                        <button type="reset" class="btn btn-default mt-3">
                            <i class="fa fa-refresh"></i>
                            Reset
                        </button>

                    </form>

                </div>
            </div>
        @endcan

        {{-- ================= FOLLOWUPS TABLE ================= --}}
        @can('View-Followups')
            <div class="ibox">

                <div class="ibox-title">
                    <h5>
                        Followups List
                    </h5>
                </div>

                <div class="ibox-content">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover dataTables-example">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($followups as $k => $followup)
                                    <tr>
                                        <td>
                                            {{ $k + 1 }}
                                        </td>

                                        <td>
                                            {{ $followup->customer->customer_name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $followup->followup_date ?? '-' }}
                                        </td>

                                        <td>
                                            @if ($followup->priority == 'High')
                                                <span class="label label-danger">
                                                    High
                                                </span>
                                            @elseif ($followup->priority == 'Medium')
                                                <span class="label label-warning">
                                                    Medium
                                                </span>
                                            @else
                                                <span class="label label-primary">
                                                    Low
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($followup->status == 'completed')
                                                <span class="label label-success">
                                                    Completed
                                                </span>
                                            @else
                                                <span class="label label-warning">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $followup->notes ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $followup->user->name ?? '-' }}
                                        </td>

                                        <td>
                                            <div class="btn-group">

                                                @can('Edit-Followups')
                                                    <a href="{{ route('sales.followups.edit', ['id' => Crypt::encryptString($followup->id)]) }}"
                                                        class="btn btn-xs btn-primary">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('Delete-Followups')
                                                    <form
                                                        action="{{ route('sales.followups.delete', ['id' => Crypt::encryptString($followup->id)]) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="btn btn-xs btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this followup?')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan

                                            </div>
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            No followups found
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        @endcan

    </div>

@endsection
