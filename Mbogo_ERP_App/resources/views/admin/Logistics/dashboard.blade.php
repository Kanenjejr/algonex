@extends('layouts.SalesMaster')

@section('content')
@php
    $pageTitle = $pageTitle ?? 'Logistics Dashboard';
@endphp

<style>
    .logistics-page-heading {
        margin-bottom: 15px;
        border-bottom: 1px solid #e7eaec;
        background: #fff;
        border-radius: 8px;
        padding: 18px 20px;
    }

    .logistics-card {
        color: #fff;
        border-radius: 16px;
        padding: 20px;
        min-height: 150px;
        box-shadow: 0 6px 18px rgba(0,0,0,.10);
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .logistics-card::after {
        content: "";
        position: absolute;
        right: -20px;
        top: -20px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
    }

    .logistics-card .icon {
        font-size: 30px;
        margin-bottom: 14px;
        opacity: .95;
    }

    .logistics-card .value {
        font-size: 36px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .logistics-card .label {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .card-orders {
        background: linear-gradient(135deg, #1abc9c, #16a085);
    }

    .card-vehicles {
        background: linear-gradient(135deg, #3498db, #2980b9);
    }

    .card-drivers {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
    }

    .card-costs {
        background: linear-gradient(135deg, #f39c12, #e67e22);
    }

    .info-box {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e7eaec;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }

    .info-box .ibox-title {
        padding: 14px 18px;
        border-bottom: 1px solid #e7eaec;
        font-weight: 700;
        color: #2f4050;
        background: #fafafa;
        border-radius: 12px 12px 0 0;
    }

    .info-box .ibox-content {
        padding: 18px;
    }

    .status-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
    }

    .status-super {
        background: #1ab394;
    }

    .status-scoped {
        background: #f8ac59;
    }

    .table thead th {
        background: #f7f9fb;
        font-weight: 700;
        color: #2f4050;
        white-space: nowrap;
    }
</style>

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row logistics-page-heading">
        <div class="col-lg-8 col-md-8 col-sm-12">
            <h2 style="margin-top:0;margin-bottom:5px;">Logistics Dashboard</h2>
            <ol class="breadcrumb" style="font-size:15px;background:transparent;padding-left:0;margin-bottom:0;">
                <li><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                <li class="active"><strong>Dashboard</strong></li>
            </ol>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 text-right" style="padding-top:18px;">
            @can('Create-Transport-Orders')
                <a href="{{ route('logistics.orders.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> New Order
                </a>
            @endcan

            @can('View-Fleet-Management')
                <a href="{{ route('logistics.fleet') }}" class="btn btn-success">
                    <i class="fa fa-truck"></i> Fleet
                </a>
            @endcan
        </div>
    </div>

    @if(isset($companies) && $companies->count())
        <div class="info-box">
            <div class="ibox-content">
                <form method="GET" action="{{ route('logistics.dashboard') }}">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <label style="font-weight:700;">Select Company</label>
                            <select name="company_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Select company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ optional($currentCompany)->id == $company->id ? 'selected' : '' }}>
                                        {{ $company->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-orders">
                <div class="icon"><i class="fa fa-file-text"></i></div>
                <div class="value">{{ $totals['orders'] ?? 0 }}</div>
                <div class="label">Transport Orders</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-vehicles">
                <div class="icon"><i class="fa fa-truck"></i></div>
                <div class="value">{{ $totals['vehicles'] ?? 0 }}</div>
                <div class="label">Vehicles</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-drivers">
                <div class="icon"><i class="fa fa-users"></i></div>
                <div class="value">{{ $totals['drivers'] ?? 0 }}</div>
                <div class="label">Drivers</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-costs">
                <div class="icon"><i class="fa fa-money"></i></div>
                <div class="value">{{ $totals['costs'] ?? 0 }}</div>
                <div class="label">Cost Sheets</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="info-box">
                <div class="ibox-title">
                    <h5 style="margin:0;">Company Information</h5>
                </div>
                <div class="ibox-content">
                    <h3 style="margin-top:0;">
                        {{ optional($currentCompany)->company_name ?? 'N/A' }}
                    </h3>

                    <p><strong>Company Code:</strong> {{ optional($currentCompany)->company_code ?? 'N/A' }}</p>
                    <p><strong>Business Unit:</strong> {{ optional($currentUnit)->unit_name ?? 'N/A' }}</p>
                    <p><strong>Work Point:</strong> {{ optional($currentWorkPoint)->work_name ?? 'N/A' }}</p>
                    <p><strong>Location:</strong> {{ optional($currentWorkPoint)->location ?? optional($currentUnit)->location ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="info-box">
                <div class="ibox-title">
                    <h5 style="margin:0;">Quick Summary</h5>
                </div>
                <div class="ibox-content">
                    <p><strong>Transport Orders</strong> - all movement control.</p>
                    <p><strong>Fleet Management</strong> - vehicles, drivers, escorts.</p>
                    <p><strong>Transport Costing</strong> - cost and profit analysis.</p>
                    <hr>
                    <p style="margin-bottom:0;">
                        <strong>Access:</strong>
                        @if($isSuper ?? false)
                            <span class="status-pill status-super">Super User</span>
                        @else
                            <span class="status-pill status-scoped">Scoped User</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="info-box">
        <div class="ibox-title">
            <h5 style="margin:0;">Latest Orders</h5>
        </div>
        <div class="ibox-content table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Route</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($latestOrders ?? [] as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->order_no ?? '-' }}</td>
                        <td>
                            @if(!empty($row->order_date))
                                {{ date('Y-m-d', strtotime($row->order_date)) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $row->customer_name ?? '-' }}</td>
                        <td>
                            {{ $row->origin ?? '-' }} → {{ $row->destination ?? '-' }}
                        </td>
                        <td>{{ $row->status ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No Orders Found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection