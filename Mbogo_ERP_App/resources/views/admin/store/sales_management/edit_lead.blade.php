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
                        <a href="{{ route('sales.leads') }}">Leads</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Lead</strong>
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

        {{-- ================= EDIT LEAD FORM ================= --}}
        <div class="ibox mt-3">

            <div class="ibox-title bg-primary">
                <h5>
                    <i class="fa fa-edit"></i>
                    Edit Lead
                </h5>
            </div>

            <div class="ibox-content">

                <form method="POST" action="{{ route('sales.leads.update', ['id' => Crypt::encryptString($lead->id)]) }}">

                    @csrf

                    <div class="row">

                        {{-- ================= ROW 1 ================= --}}

                        <div class="col-md-4">
                            <label>Customer Name *</label>

                            <input type="text" name="customer_name"
                                value="{{ old('customer_name', $lead->customer_name) }}" class="form-control"
                                placeholder="Enter customer name" required>
                        </div>

                        <div class="col-md-4">
                            <label>Phone Number</label>

                            <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}"
                                class="form-control" placeholder="Enter phone number">
                        </div>

                        <div class="col-md-4">
                            <label>Email</label>

                            <input type="email" name="email" value="{{ old('email', $lead->email) }}"
                                class="form-control" placeholder="Enter email address">
                        </div>

                        {{-- ================= ROW 2 ================= --}}

                        <div class="col-md-4">
                            <label>Business Type</label>

                            <select name="business_type" class="form-control select2_demo_2">

                                <option value="">Select Business Type</option>

                                <option value="Individual"
                                    {{ old('business_type', $lead->business_type) == 'Individual' ? 'selected' : '' }}>
                                    Individual
                                </option>

                                <option value="Company"
                                    {{ old('business_type', $lead->business_type) == 'Company' ? 'selected' : '' }}>
                                    Company
                                </option>

                                <option value="Government"
                                    {{ old('business_type', $lead->business_type) == 'Government' ? 'selected' : '' }}>
                                    Government
                                </option>

                                <option value="NGO"
                                    {{ old('business_type', $lead->business_type) == 'NGO' ? 'selected' : '' }}>
                                    NGO
                                </option>

                                <option value="Contractor"
                                    {{ old('business_type', $lead->business_type) == 'Contractor' ? 'selected' : '' }}>
                                    Contractor
                                </option>

                                <option value="Supplier"
                                    {{ old('business_type', $lead->business_type) == 'Supplier' ? 'selected' : '' }}>
                                    Supplier
                                </option>

                                <option value="Other"
                                    {{ old('business_type', $lead->business_type) == 'Other' ? 'selected' : '' }}>
                                    Other
                                </option>

                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Lead Status *</label>

                            <select name="status" class="form-control select2_demo_2" required>

                                <option value="">Select Status</option>

                                <option value="pending" {{ old('status', $lead->status) == 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>

                                <option value="converted"
                                    {{ old('status', $lead->status) == 'converted' ? 'selected' : '' }}>
                                    Converted
                                </option>

                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Lead Source</label>

                            <select name="source" class="form-control select2_demo_2">

                                <option value="">Select Source</option>

                                <option value="Website" {{ old('source', $lead->source) == 'Website' ? 'selected' : '' }}>
                                    Website
                                </option>

                                <option value="WhatsApp"
                                    {{ old('source', $lead->source) == 'WhatsApp' ? 'selected' : '' }}>
                                    WhatsApp
                                </option>

                                <option value="Phone Call"
                                    {{ old('source', $lead->source) == 'Phone Call' ? 'selected' : '' }}>
                                    Phone Call
                                </option>

                                <option value="Email" {{ old('source', $lead->source) == 'Email' ? 'selected' : '' }}>
                                    Email
                                </option>

                                <option value="Walk In" {{ old('source', $lead->source) == 'Walk In' ? 'selected' : '' }}>
                                    Walk In
                                </option>

                                <option value="Referral"
                                    {{ old('source', $lead->source) == 'Referral' ? 'selected' : '' }}>
                                    Referral
                                </option>

                                <option value="Social Media"
                                    {{ old('source', $lead->source) == 'Social Media' ? 'selected' : '' }}>
                                    Social Media
                                </option>

                                <option value="Campaign"
                                    {{ old('source', $lead->source) == 'Campaign' ? 'selected' : '' }}>
                                    Campaign
                                </option>

                                <option value="Other" {{ old('source', $lead->source) == 'Other' ? 'selected' : '' }}>
                                    Other
                                </option>

                            </select>
                        </div>

                        {{-- ================= ROW 3 ================= --}}

                        <div class="col-md-4">
                            <label>Assigned Staff</label>

                            <input type="text" class="form-control"
                                value="{{ $lead->user->name ?? auth()->user()->name }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Created Date</label>

                            <input type="text" class="form-control"
                                value="{{ $lead->created_at ? $lead->created_at->format('Y-m-d H:i') : '-' }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Last Updated</label>

                            <input type="text" class="form-control"
                                value="{{ $lead->updated_at ? $lead->updated_at->format('Y-m-d H:i') : '-' }}" readonly>
                        </div>

                        {{-- ================= ROW 4 ================= --}}

                        <div class="col-md-12">
                            <label>Lead Description</label>

                            <textarea name="description" rows="5" class="form-control" placeholder="Write lead details here...">{{ old('description', $lead->description) }}</textarea>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Update Lead
                    </button>

                    <a href="{{ route('sales.leads') }}" class="btn btn-default mt-3">
                        <i class="fa fa-arrow-left"></i>
                        Back
                    </a>

                </form>

            </div>

        </div>

    </div>
@endsection
