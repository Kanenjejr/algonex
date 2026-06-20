@extends('layouts.SalesMaster')
@section('content')
@php
    $pageTitle = $pageTitle ?? 'Transport Costing & Analysis';

    $formatDate = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    };
@endphp

<style>
    .cost-heading {
        margin-bottom: 15px;
        border-bottom: 1px solid #e7eaec;
        background: #fff;
        border-radius: 8px;
        padding: 18px 20px;
    }

    .cost-card {
        color: #fff;
        border-radius: 16px;
        padding: 20px;
        min-height: 135px;
        box-shadow: 0 6px 18px rgba(0,0,0,.10);
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .cost-card::after {
        content: "";
        position: absolute;
        right: -20px;
        top: -20px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
    }

    .cost-card .icon {
        font-size: 30px;
        margin-bottom: 14px;
        opacity: .95;
    }

    .cost-card .value {
        font-size: 34px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .cost-card .label {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .card-orders {
        background: linear-gradient(135deg, #1abc9c, #16a085);
    }

    .card-totalcost {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .card-profit {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
    }

    .card-hired {
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

    .table thead th {
        background: #f7f9fb;
        font-weight: 700;
        color: #2f4050;
        white-space: nowrap;
    }
</style>

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row cost-heading no-print">
        <div class="col-lg-8">
            <h2 style="margin-top:0;margin-bottom:5px;">{{ $pageTitle }}</h2>
            <ol class="breadcrumb" style="font-size:15px;background:transparent;padding-left:0;margin-bottom:0;">
                <li><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                <li class="active"><strong>{{ $pageTitle }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-4 text-right" style="padding-top:18px;">
            <a href="{{ route('logistics.dashboard') }}" class="btn btn-default">
                <i class="fa fa-dashboard"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="cost-card card-orders">
                <div class="icon"><i class="fa fa-file-text"></i></div>
                <div class="value">{{ $records->count() }}</div>
                <div class="label">Orders</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="cost-card card-totalcost">
                <div class="icon"><i class="fa fa-money"></i></div>
                <div class="value">{{ number_format($records->sum('total_cost'), 2) }}</div>
                <div class="label">Total Cost</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="cost-card card-profit">
                <div class="icon"><i class="fa fa-line-chart"></i></div>
                <div class="value">{{ number_format($records->sum('profit'), 2) }}</div>
                <div class="label">Total Profit</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="cost-card card-hired">
                <div class="icon"><i class="fa fa-truck"></i></div>
                <div class="value">{{ $records->where('vehicle_source', 'hired')->count() }}</div>
                <div class="label">Hired Jobs</div>
            </div>
        </div>
    </div>

    <div class="info-box">
        <div class="ibox-title">
            <h5 style="margin:0;">Cost Sheets</h5>
        </div>
        <div class="ibox-content table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cost No</th>
                        <th>Order No</th>
                        <th>Cost Date</th>
                        <th>Hire</th>
                        <th>Fuel</th>
                        <th>Total</th>
                        <th>Profit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->cost_no ?? '-' }}</td>
                            <td>{{ optional($row->order)->order_no ?? '-' }}</td>
                            <td>{{ $formatDate($row->cost_date ?? null) }}</td>
                            <td>{{ number_format((float) ($row->hire_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($row->fuel_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($row->total_cost ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($row->profit ?? 0), 2) }}</td>
                            <td>
                                @can('Edit-Transport-Costing')
                                    <form method="POST" action="{{ route('logistics.costing.recalculate', encrypt(optional($row->order)->id)) }}" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-primary">
                                            Recalculate
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No Cost Sheets Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection