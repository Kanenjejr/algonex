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
                        <a href="{{ route('sales.communications') }}">Communications</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Communication</strong>
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

        {{-- ================= EDIT COMMUNICATION FORM ================= --}}
        <div class="ibox mt-3">

            <div class="ibox-title bg-primary">
                <h5>
                    <i class="fa fa-edit"></i>
                    Edit Communication
                </h5>
            </div>

            <div class="ibox-content">

                <form method="POST"
                    action="{{ route('sales.communications.update', ['id' => Crypt::encryptString($communication->id)]) }}">

                    @csrf

                    <div class="row">

                        {{-- CUSTOMER --}}
                        <div class="col-md-4">
                            <label>Customer *</label>

                            <select name="customer_id" class="form-control select2_demo_2" required>

                                <option value="">Select Customer</option>

                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ old('customer_id', $communication->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->customer_code ?? '' }} - {{ $customer->customer_name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- TYPE --}}
                        <div class="col-md-4">
                            <label>Communication Type *</label>

                            <select name="type" class="form-control select2_demo_2" required>

                                <option value="">Select Type</option>

                                <option value="Email" {{ old('type', $communication->type) == 'Email' ? 'selected' : '' }}>
                                    Email
                                </option>

                                <option value="SMS" {{ old('type', $communication->type) == 'SMS' ? 'selected' : '' }}>
                                    SMS
                                </option>

                                <option value="Phone Call"
                                    {{ old('type', $communication->type) == 'Phone Call' ? 'selected' : '' }}>
                                    Phone Call
                                </option>

                                <option value="WhatsApp"
                                    {{ old('type', $communication->type) == 'WhatsApp' ? 'selected' : '' }}>
                                    WhatsApp
                                </option>

                            </select>
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-4">
                            <label>Status *</label>

                            <select name="status" class="form-control select2_demo_2" required>

                                <option value="Sent"
                                    {{ old('status', $communication->status) == 'Sent' ? 'selected' : '' }}>
                                    Sent
                                </option>

                                <option value="Pending"
                                    {{ old('status', $communication->status) == 'Pending' ? 'selected' : '' }}>
                                    Pending
                                </option>

                                <option value="Failed"
                                    {{ old('status', $communication->status) == 'Failed' ? 'selected' : '' }}>
                                    Failed
                                </option>

                            </select>
                        </div>

                        {{-- SUBJECT --}}
                        <div class="col-md-12">
                            <label>Subject *</label>

                            <input type="text" name="subject" value="{{ old('subject', $communication->subject) }}"
                                class="form-control" placeholder="Enter subject" required>
                        </div>

                        {{-- MESSAGE --}}
                        <div class="col-md-12">
                            <label>Message *</label>

                            <textarea name="message" rows="6" class="form-control" placeholder="Write your communication message here..."
                                required>{{ old('message', $communication->message) }}</textarea>
                        </div>

                        {{-- CREATED BY --}}
                        <div class="col-md-4">
                            <label>Created By</label>

                            <input type="text" class="form-control" value="{{ $communication->user->name ?? '-' }}"
                                readonly>
                        </div>

                        {{-- CREATED DATE --}}
                        <div class="col-md-4">
                            <label>Created Date</label>

                            <input type="text" class="form-control"
                                value="{{ $communication->created_at ? $communication->created_at->format('Y-m-d H:i') : '-' }}"
                                readonly>
                        </div>

                        {{-- UPDATED DATE --}}
                        <div class="col-md-4">
                            <label>Last Updated</label>

                            <input type="text" class="form-control"
                                value="{{ $communication->updated_at ? $communication->updated_at->format('Y-m-d H:i') : '-' }}"
                                readonly>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Update Communication
                    </button>

                    <a href="{{ route('sales.communications') }}" class="btn btn-default mt-3">
                        <i class="fa fa-arrow-left"></i>
                        Back
                    </a>

                </form>

            </div>

        </div>

    </div>
@endsection
