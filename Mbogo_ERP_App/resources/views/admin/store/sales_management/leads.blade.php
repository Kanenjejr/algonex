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
                        <strong>Leads</strong>
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
        <div class="row mt-3">
            <div class="col-lg-12">

                <div class="ibox">
                    <div class="ibox-title bg-primary">
                        <h5>
                            <i class="fa fa-users"></i>
                            Leads Management
                        </h5>
                    </div>

                    <div class="ibox-content">
                        <p class="text-muted" style="margin-bottom:0;">
                            Manage customer leads, opportunities and conversion tracking.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================= STATISTICS CARDS ================= --}}
        <div class="row">

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content bg-primary text-white text-center">
                        <div class="mb-3">
                            <i class="fa fa-user-plus fa-3x"></i>
                        </div>

                        <h5>Total Leads</h5>

                        <h2 class="font-bold">
                            {{ count($leads) }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content bg-warning text-white text-center">
                        <div class="mb-3">
                            <i class="fa fa-clock-o fa-3x"></i>
                        </div>

                        <h5>Pending Leads</h5>

                        <h2 class="font-bold">
                            {{ $leads->where('status', 'pending')->count() }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content bg-success text-white text-center">
                        <div class="mb-3">
                            <i class="fa fa-check-circle fa-3x"></i>
                        </div>

                        <h5>Converted</h5>

                        <h2 class="font-bold">
                            {{ $leads->where('status', 'converted')->count() }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox">
                    <div class="ibox-content bg-danger text-white text-center">
                        <div class="mb-3">
                            <i class="fa fa-line-chart fa-3x"></i>
                        </div>

                        <h5>Business Sources</h5>

                        <h2 class="font-bold">
                            {{ $leads->groupBy('source')->count() }}
                        </h2>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= CREATE LEAD FORM ================= --}}
        @can('Create-Leads')
            <div class="ibox mt-3" id="lead-registration-form">
                <div class="ibox-title bg-primary">
                    <h5>
                        <i class="fa fa-edit"></i>
                        Lead Registration Form
                    </h5>
                </div>

                <div class="ibox-content">

                    <form method="POST" action="{{ route('sales.leads.store') }}">
                        @csrf

                        <div class="row">

                            {{-- ================= ROW 1 ================= --}}

                            <div class="col-md-4">
                                <label>Customer Name *</label>

                                <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                    class="form-control" placeholder="Enter customer name" required>
                            </div>

                            <div class="col-md-4">
                                <label>Phone Number</label>

                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control"
                                    placeholder="Enter phone number">
                            </div>

                            <div class="col-md-4">
                                <label>Email</label>

                                <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                    placeholder="Enter email address">
                            </div>

                            {{-- ================= ROW 2 ================= --}}

                            <div class="col-md-4">
                                <label>Business Type</label>

                                <select name="business_type" class="form-control select2_demo_2">

                                    <option value="">Select Business Type</option>

                                    <option value="Individual" {{ old('business_type') == 'Individual' ? 'selected' : '' }}>
                                        Individual
                                    </option>

                                    <option value="Company" {{ old('business_type') == 'Company' ? 'selected' : '' }}>
                                        Company
                                    </option>

                                    <option value="Government" {{ old('business_type') == 'Government' ? 'selected' : '' }}>
                                        Government
                                    </option>

                                    <option value="NGO" {{ old('business_type') == 'NGO' ? 'selected' : '' }}>
                                        NGO
                                    </option>

                                    <option value="Contractor" {{ old('business_type') == 'Contractor' ? 'selected' : '' }}>
                                        Contractor
                                    </option>

                                    <option value="Supplier" {{ old('business_type') == 'Supplier' ? 'selected' : '' }}>
                                        Supplier
                                    </option>

                                    <option value="Other" {{ old('business_type') == 'Other' ? 'selected' : '' }}>
                                        Other
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Lead Status *</label>

                                <select name="status" class="form-control select2_demo_2" required>

                                    <option value="">Select Status</option>

                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>

                                    <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>
                                        Converted
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Lead Source</label>

                                <select name="source" class="form-control select2_demo_2">

                                    <option value="">Select Source</option>

                                    <option value="Website" {{ old('source') == 'Website' ? 'selected' : '' }}>
                                        Website
                                    </option>

                                    <option value="WhatsApp" {{ old('source') == 'WhatsApp' ? 'selected' : '' }}>
                                        WhatsApp
                                    </option>

                                    <option value="Phone Call" {{ old('source') == 'Phone Call' ? 'selected' : '' }}>
                                        Phone Call
                                    </option>

                                    <option value="Email" {{ old('source') == 'Email' ? 'selected' : '' }}>
                                        Email
                                    </option>

                                    <option value="Walk In" {{ old('source') == 'Walk In' ? 'selected' : '' }}>
                                        Walk In
                                    </option>

                                    <option value="Referral" {{ old('source') == 'Referral' ? 'selected' : '' }}>
                                        Referral
                                    </option>

                                    <option value="Social Media" {{ old('source') == 'Social Media' ? 'selected' : '' }}>
                                        Social Media
                                    </option>

                                    <option value="Campaign" {{ old('source') == 'Campaign' ? 'selected' : '' }}>
                                        Campaign
                                    </option>

                                    <option value="Other" {{ old('source') == 'Other' ? 'selected' : '' }}>
                                        Other
                                    </option>

                                </select>
                            </div>

                            {{-- ================= ROW 3 ================= --}}

                            <div class="col-md-4">
                                <label>Assigned Staff</label>

                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            </div>

                            <div class="col-md-8">
                                <label>Lead Description</label>

                                <textarea name="description" rows="3" class="form-control" placeholder="Write lead details here...">{{ old('description') }}</textarea>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-success mt-3">
                            <i class="fa fa-save"></i>
                            Save Lead
                        </button>

                        <button type="reset" class="btn btn-default mt-3">
                            <i class="fa fa-refresh"></i>
                            Reset
                        </button>

                    </form>

                </div>
            </div>
        @endcan

        {{-- ================= LEADS TABLE ================= --}}
        @can('View-Leads')
            <div class="ibox">
                <div class="ibox-title">
                    <h5 class="font-bold text-primary">
                        Leads List
                    </h5>
                </div>

                <div class="ibox-content">

                    <div class="table-responsive">

                        <table class="table table-hover table-bordered dataTables-example">

                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Business</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($leads as $k => $lead)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>

                                        <td>{{ $lead->customer_name ?? '-' }}</td>

                                        <td>{{ $lead->phone ?? '-' }}</td>

                                        <td>{{ $lead->email ?? '-' }}</td>

                                        <td>{{ $lead->business_type ?? '-' }}</td>

                                        <td>
                                            @if ($lead->status == 'converted')
                                                <span class="badge badge-success">
                                                    Converted
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>

                                        <td>{{ $lead->source ?? '-' }}</td>

                                        <td>{{ $lead->user->name ?? '-' }}</td>

                                        <td>
                                            <div class="btn-group">

                                                @can('Edit-Leads')
                                                    <a href="{{ route('sales.leads.edit', ['id' => Crypt::encryptString($lead->id)]) }}"
                                                        class="btn btn-xs btn-primary">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('Delete-Leads')
                                                    <form
                                                        action="{{ route('sales.leads.delete', ['id' => Crypt::encryptString($lead->id)]) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="btn btn-xs btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this lead?')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            No leads available
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
