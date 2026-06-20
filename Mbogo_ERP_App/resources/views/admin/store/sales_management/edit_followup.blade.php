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
                        <a href="{{ route('sales.followups') }}">Followups</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Followup</strong>
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

        {{-- ================= EDIT FOLLOWUP FORM ================= --}}
        <div class="ibox mt-3">

            <div class="ibox-title bg-primary">
                <h5>
                    <i class="fa fa-edit"></i>
                    Edit Followup
                </h5>
            </div>

            <div class="ibox-content">

                <form method="POST"
                    action="{{ route('sales.followups.update', ['id' => Crypt::encryptString($followup->id)]) }}">

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
                                        {{ old('customer_id', $followup->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->customer_code ?? '' }} - {{ $customer->customer_name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- DATE --}}
                        <div class="col-md-4">
                            <label>Followup Date *</label>

                            <input type="date" name="followup_date"
                                value="{{ old('followup_date', \Carbon\Carbon::parse($followup->followup_date)->format('Y-m-d')) }}"
                                class="form-control" required>
                        </div>

                        {{-- PRIORITY --}}
                        <div class="col-md-4">
                            <label>Priority *</label>

                            <select name="priority" class="form-control select2_demo_2" required>

                                <option value="">
                                    Select Priority
                                </option>

                                <option value="Low"
                                    {{ old('priority', $followup->priority) == 'Low' ? 'selected' : '' }}>
                                    Low
                                </option>

                                <option value="Medium"
                                    {{ old('priority', $followup->priority) == 'Medium' ? 'selected' : '' }}>
                                    Medium
                                </option>

                                <option value="High"
                                    {{ old('priority', $followup->priority) == 'High' ? 'selected' : '' }}>
                                    High
                                </option>

                            </select>
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-4">
                            <label>Status *</label>

                            <select name="status" class="form-control select2_demo_2" required>

                                <option value="pending"
                                    {{ old('status', $followup->status) == 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>

                                <option value="completed"
                                    {{ old('status', $followup->status) == 'completed' ? 'selected' : '' }}>
                                    Completed
                                </option>

                            </select>
                        </div>

                        {{-- CREATED BY --}}
                        <div class="col-md-4">
                            <label>Created By</label>

                            <input type="text" class="form-control" value="{{ $followup->user->name ?? '-' }}" readonly>
                        </div>

                        {{-- CREATED DATE --}}
                        <div class="col-md-4">
                            <label>Created Date</label>

                            <input type="text" class="form-control"
                                value="{{ $followup->created_at ? $followup->created_at->format('Y-m-d H:i') : '-' }}"
                                readonly>
                        </div>

                        {{-- NOTES --}}
                        <div class="col-md-12">
                            <label>Notes</label>

                            <textarea name="notes" rows="5" class="form-control" placeholder="Write notes here...">{{ old('notes', $followup->notes) }}</textarea>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-success mt-3">
                        <i class="fa fa-save"></i>
                        Update Followup
                    </button>

                    <a href="{{ route('sales.followups') }}" class="btn btn-default mt-3">
                        <i class="fa fa-arrow-left"></i>
                        Back
                    </a>

                </form>

            </div>

        </div>

    </div>
@endsection
