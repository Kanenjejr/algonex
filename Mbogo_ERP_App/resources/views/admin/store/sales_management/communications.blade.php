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
                        <strong>Communications</strong>
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
                    <i class="fa fa-comments"></i>
                    Customer Communications
                </h5>
            </div>

            <div class="ibox-content">
                <p class="text-muted" style="margin-bottom:0;">
                    Manage customer messages, calls, emails and communication records.
                </p>
            </div>
        </div>

        {{-- ================= COMMUNICATION FORM ================= --}}
        @can('Create-Communications')
            <div class="ibox" id="communicationForm">

                <div class="ibox-title bg-primary">
                    <h5>
                        <i class="fa fa-paper-plane"></i>
                        Send Communication
                    </h5>
                </div>

                <div class="ibox-content">

                    <form method="POST" action="{{ route('sales.communications.store') }}">
                        @csrf

                        <div class="row">

                            {{-- CUSTOMER --}}
                            <div class="col-md-4">
                                <label>Customer *</label>

                                <select name="customer_id" class="form-control select2_demo_2" required>

                                    <option value="">Select Customer</option>

                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
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

                                    <option value="Email" {{ old('type') == 'Email' ? 'selected' : '' }}>
                                        Email
                                    </option>

                                    <option value="SMS" {{ old('type') == 'SMS' ? 'selected' : '' }}>
                                        SMS
                                    </option>

                                    <option value="Phone Call" {{ old('type') == 'Phone Call' ? 'selected' : '' }}>
                                        Phone Call
                                    </option>

                                    <option value="WhatsApp" {{ old('type') == 'WhatsApp' ? 'selected' : '' }}>
                                        WhatsApp
                                    </option>

                                </select>
                            </div>

                            {{-- SUBJECT --}}
                            <div class="col-md-4">
                                <label>Subject *</label>

                                <input type="text" name="subject" value="{{ old('subject') }}" class="form-control"
                                    placeholder="Enter subject" required>
                            </div>

                            {{-- MESSAGE --}}
                            <div class="col-md-12">
                                <label>Message *</label>

                                <textarea name="message" rows="5" class="form-control" placeholder="Write your communication message here..."
                                    required>{{ old('message') }}</textarea>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-success mt-3">
                            <i class="fa fa-send"></i>
                            Send Communication
                        </button>

                        <button type="reset" class="btn btn-default mt-3">
                            <i class="fa fa-refresh"></i>
                            Reset
                        </button>

                    </form>

                </div>

            </div>
        @endcan

        {{-- ================= COMMUNICATIONS TABLE ================= --}}
        @can('View-Communications')
            <div class="ibox">

                <div class="ibox-title">
                    <h5 class="text-primary font-bold">
                        Recent Communications
                    </h5>
                </div>

                <div class="ibox-content">

                    <div class="table-responsive">

                        <table class="table table-striped table-hover table-bordered dataTables-example">

                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($communications as $k => $communication)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>

                                        <td>
                                            {{ $communication->customer->customer_name ?? '-' }}
                                        </td>

                                        <td>
                                            @if ($communication->type == 'Email')
                                                <span class="label label-primary">Email</span>
                                            @elseif ($communication->type == 'SMS')
                                                <span class="label label-success">SMS</span>
                                            @elseif ($communication->type == 'Phone Call')
                                                <span class="label label-warning">Phone Call</span>
                                            @else
                                                <span class="label label-info">WhatsApp</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $communication->subject ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $communication->created_at ? $communication->created_at->format('d M Y') : '-' }}
                                        </td>

                                        <td>
                                            <span class="label label-success">
                                                {{ ucfirst($communication->status ?? 'Sent') }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $communication->user->name ?? '-' }}
                                        </td>

                                        <td>
                                            <div class="btn-group">

                                                @can('Edit-Communications')
                                                    <a href="{{ route('sales.communications.edit', ['id' => Crypt::encryptString($communication->id)]) }}"
                                                        class="btn btn-xs btn-primary">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('Delete-Communications')
                                                    <form
                                                        action="{{ route('sales.communications.delete', ['id' => Crypt::encryptString($communication->id)]) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="btn btn-xs btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this communication?')">
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
                                            No communication records found
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
