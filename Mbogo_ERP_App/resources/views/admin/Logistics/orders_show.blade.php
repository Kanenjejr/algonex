@extends('layouts.SalesMaster')
@section('content')
@php
    $approved = in_array($record->status, ['Approved', 'Dispatched', 'In Transit', 'Completed', 'Closed'], true);
    $stampPath = asset('Stamp%20Mbogo%20Mining.png');
@endphp

<div class="wrapper wrapper-content animated fadeInRight no-print">
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8">
            <h2>{{ $pageTitle ?? 'Transport Order Details' }}</h2>
            <ol class="breadcrumb" style="font-size:15px;background:transparent;padding-left:0;">
                <li><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                <li class="active"><strong>Transport Orders</strong></li>
            </ol>
        </div>
        <div class="col-lg-4 text-right" style="padding-top:18px;">
            <a href="{{ route('logistics.orders') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
            @can('Edit-Transport-Orders')
                <a href="{{ route('logistics.orders.edit', encrypt($record->id)) }}" class="btn btn-success"><i class="fa fa-pencil"></i> Edit</a>
            @endcan
            <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
@extends('layouts.SalesMaster')
@section('content')
@php
    $record = $record ?? null;
    $approved = in_array(optional($record)->status, ['Approved', 'Dispatched', 'In Transit', 'Completed', 'Closed'], true);
    $stampPath = asset('Stamp%20Mbogo%20Mining.png');

    $formatDate = function ($value) {
        if (empty($value)) {
            return 'N/A';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $driverName = trim(
        (optional($record->driver)->first_name ?? '') . ' ' . (optional($record->driver)->last_name ?? '')
    );
@endphp

<style>
    .order-detail-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e7eaec;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }

    .order-detail-card .ibox-title {
        padding: 14px 18px;
        border-bottom: 1px solid #e7eaec;
        font-weight: 700;
        color: #2f4050;
        background: #fafafa;
        border-radius: 12px 12px 0 0;
    }

    .order-detail-card .ibox-content {
        padding: 18px;
    }

    .detail-table th {
        width: 25%;
        white-space: nowrap;
        background: #f7f9fb;
    }

    .status-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
    }

    .status-approved { background: #1ab394; }
    .status-dispatched { background: #23c6c8; }
    .status-intransit { background: #f8ac59; }
    .status-completed { background: #1c84c6; }
    .status-closed { background: #0f9d58; }
    .status-draft { background: #777; }
    .status-cancelled { background: #ed5565; }

    .print-header {
        width: 100%;
        margin-bottom: 20px;
    }

    .print-header img {
        width: 100%;
        height: auto;
        display: block;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            background: #fff !important;
        }

        .order-detail-card {
            box-shadow: none !important;
            border: 1px solid #000;
        }
    }
</style>

<div class="wrapper wrapper-content animated fadeInRight no-print">
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8">
            <h2>{{ $pageTitle ?? 'Transport Order Details' }}</h2>
            <ol class="breadcrumb" style="font-size:15px;background:transparent;padding-left:0;margin-bottom:0;">
                <li><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                <li class="active"><strong>Transport Orders</strong></li>
            </ol>
        </div>

        <div class="col-lg-4 text-right" style="padding-top:18px;">
            <a href="{{ route('logistics.orders') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>

            @can('Edit-Transport-Orders')
                <a href="{{ route('logistics.orders.edit', encrypt($record->id)) }}" class="btn btn-success">
                    <i class="fa fa-pencil"></i> Edit
                </a>
            @endcan

            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="order-detail-card">
        <div class="ibox-content">
            <img src="{{ asset('img/header.png') }}" alt="Header" style="width:100%;height:auto;display:block;margin-bottom:20px;">

            <div class="row">
                <div class="col-md-8">
                    <h3 style="margin-top:0;">Transport Order #{{ optional($record)->order_no ?? 'N/A' }}</h3>
                    <p><strong>Customer:</strong> {{ optional($record)->customer_name ?? 'N/A' }}</p>
                    <p><strong>Route:</strong> {{ optional($record)->origin ?? 'N/A' }} → {{ optional($record)->destination ?? 'N/A' }}</p>
                    <p><strong>Cargo:</strong> {{ optional($record)->cargo_description ?? 'N/A' }}</p>
                    <p>
                        <strong>Status:</strong>
                        @php
                            $status = optional($record)->status ?? 'Draft';
                            $pillClass = 'status-draft';
                            if ($status === 'Approved') $pillClass = 'status-approved';
                            elseif ($status === 'Dispatched') $pillClass = 'status-dispatched';
                            elseif ($status === 'In Transit') $pillClass = 'status-intransit';
                            elseif ($status === 'Completed') $pillClass = 'status-completed';
                            elseif ($status === 'Closed') $pillClass = 'status-closed';
                            elseif ($status === 'Cancelled') $pillClass = 'status-cancelled';
                        @endphp
                        <span class="status-pill {{ $pillClass }}">{{ $status }}</span>
                    </p>
                </div>

                <div class="col-md-4 text-right">
                    @if($approved)
                        <img src="{{ $stampPath }}" alt="Approved Stamp" style="max-width:220px;width:100%;opacity:0.95;">
                    @endif
                </div>
            </div>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered table-striped detail-table">
                    <tbody>
                        <tr>
                            <th>Order No</th>
                            <td>{{ optional($record)->order_no ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Order Date</th>
                            <td>{{ $formatDate(optional($record)->order_date) }}</td>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <td>{{ optional(optional($record)->company)->company_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Business Unit</th>
                            <td>{{ optional(optional($record)->compUnit)->unit_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Work Point</th>
                            <td>{{ optional(optional($record)->workPoint)->work_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Vehicle Source</th>
                            <td>{{ ucfirst(optional($record)->vehicle_source ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <th>Company Vehicle</th>
                            <td>{{ optional(optional($record)->vehicle)->plate_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Hired Vehicle</th>
                            <td>
                                @php
                                    $hiredVehicleText = trim(
                                        (optional($record)->hired_vehicle_name ?? '') .
                                        ' ' .
                                        (optional($record)->hired_vehicle_plate ? '(' . optional($record)->hired_vehicle_plate . ')' : '')
                                    );
                                @endphp
                                {{ $hiredVehicleText !== '' ? $hiredVehicleText : 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Driver</th>
                            <td>{{ $driverName !== '' ? $driverName : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Escort</th>
                            <td>{{ optional($record)->escort_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Revenue Amount</th>
                            <td>{{ number_format((float) (optional($record)->revenue_amount ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <th>Remarks</th>
                            <td>{{ optional($record)->remarks ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive" style="margin-top:20px;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Cost Sheet</th>
                            <th>Hire</th>
                            <th>Fuel</th>
                            <th>Driver Allowance</th>
                            <th>Escort Allowance</th>
                            <th>Loading</th>
                            <th>Other</th>
                            <th>Total</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ optional(optional($record)->costing)->cost_no ?? 'N/A' }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->hire_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->fuel_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->driver_allowance ?? 0), 2) }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->escort_allowance ?? 0), 2) }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->loading_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->other_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->total_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) (optional(optional($record)->costing)->profit ?? 0), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($approved)
                <div class="text-right" style="margin-top:30px;">
                    <p style="margin-bottom:10px;">
                        <strong>Approved By:</strong> {{ optional(optional($record)->updater)->name ?? 'System' }}
                    </p>
                    <img src="{{ $stampPath }}" alt="Approved Stamp" style="max-width:180px;">
                </div>
            @endif
        </div>
    </div>
</div>

<div class="visible-print-block" style="display:none;">
    <img src="{{ asset('img/header.png') }}" alt="Header" style="width:100%;">
</div>
@endsection
    <div class="ibox">
        <div class="ibox-content">
            <img src="{{ asset('img/header.png') }}" alt="Header" style="width:100%;height:auto;display:block;margin-bottom:20px;">
            <div class="row">
                <div class="col-md-8">
                    <h3 style="margin-top:0;">Transport Order #{{ $record->order_no }}</h3>
                    <p><strong>Customer:</strong> {{ $record->customer_name }}</p>
                    <p><strong>Route:</strong> {{ $record->origin }} → {{ $record->destination }}</p>
                    <p><strong>Cargo:</strong> {{ $record->cargo_description }}</p>
                    <p><strong>Status:</strong> {{ $record->status }}</p>
                </div>
                <div class="col-md-4 text-right">
                    @if($approved)
                        <img src="{{ $stampPath }}" alt="Approved Stamp" style="max-width:220px;width:100%;opacity:0.95;">
                    @endif
                </div>
            </div>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr><th style="width:25%;">Order No</th><td>{{ $record->order_no }}</td></tr>
                        <tr><th>Order Date</th><td>{{ $record->order_date?->format('Y-m-d') ?? $record->order_date }}</td></tr>
                        <tr><th>Company</th><td>{{ optional($record->company)->company_name ?? 'N/A' }}</td></tr>
                        <tr><th>Business Unit</th><td>{{ optional($record->compUnit)->unit_name ?? 'N/A' }}</td></tr>
                        <tr><th>Work Point</th><td>{{ optional($record->workPoint)->work_name ?? 'N/A' }}</td></tr>
                        <tr><th>Vehicle Source</th><td>{{ ucfirst($record->vehicle_source) }}</td></tr>
                        <tr><th>Company Vehicle</th><td>{{ optional($record->vehicle)->plate_number ?? 'N/A' }}</td></tr>
                        <tr><th>Hired Vehicle</th><td>{{ trim(($record->hired_vehicle_name ?? '') . ' ' . ($record->hired_vehicle_plate ? '(' . $record->hired_vehicle_plate . ')' : '')) ?: 'N/A' }}</td></tr>
                        <tr><th>Driver</th><td>{{ optional($record->driver)->first_name ?? '' }} {{ optional($record->driver)->last_name ?? '' }}</td></tr>
                        <tr><th>Escort</th><td>{{ $record->escort_name ?? 'N/A' }}</td></tr>
                        <tr><th>Revenue Amount</th><td>{{ number_format((float) $record->revenue_amount, 2) }}</td></tr>
                        <tr><th>Remarks</th><td>{{ $record->remarks ?? 'N/A' }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive" style="margin-top:20px;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Cost Sheet</th>
                            <th>Hire</th>
                            <th>Fuel</th>
                            <th>Driver Allowance</th>
                            <th>Escort Allowance</th>
                            <th>Loading</th>
                            <th>Other</th>
                            <th>Total</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ optional($record->costing)->cost_no ?? 'N/A' }}</td>
                            <td>{{ number_format((float) optional($record->costing)->hire_cost, 2) }}</td>
                            <td>{{ number_format((float) optional($record->costing)->fuel_cost, 2) }}</td>
                            <td>{{ number_format((float) optional($record->costing)->driver_allowance, 2) }}</td>
                            <td>{{ number_format((float) optional($record->costing)->escort_allowance, 2) }}</td>
                            <td>{{ number_format((float) optional($record->costing)->loading_cost, 2) }}</td>
                            <td>{{ number_format((float) optional($record->costing)->other_cost, 2) }}</td>
                            <td>{{ number_format((float) optional($record->costing)->total_cost, 2) }}</td>
                            <td>{{ number_format((float) optional($record->costing)->profit, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($approved)
                <div class="text-right" style="margin-top:30px;">
                    <p style="margin-bottom:10px;"><strong>Approved By:</strong> {{ optional($record->updater)->name ?? 'System' }}</p>
                    <img src="{{ $stampPath }}" alt="Approved Stamp" style="max-width:180px;">
                </div>
            @endif
        </div>
    </div>
</div>

<div class="visible-print-block" style="display:none;">
    <img src="{{ asset('img/header.png') }}" alt="Header" style="width:100%;">
</div>
@endsection
