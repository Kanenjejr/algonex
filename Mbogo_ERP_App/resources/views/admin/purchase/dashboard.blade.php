@extends('layouts.salesMaster')

@section('content')
    <style>
        .dashboard-wrapper {
            padding: 5px 10px 25px;
        }

        /* EXECUTIVE HEADER */

        .executive-header {
            background: #fff;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
        }

        .executive-title {
            font-size: 32px;
            font-weight: 800;
            color: #173A7A;
            margin-bottom: 10px;
        }

        .executive-subtitle {
            color: #777;
            font-size: 15px;
        }

        .executive-metrics {
            text-align: right;
        }

        .executive-metrics h2 {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            color: #173A7A;
        }

        .executive-metrics span {
            color: #888;
        }

        /* KPI CARDS */

        .kpi-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 28px;
            color: #fff;
            margin-bottom: 25px;
            min-height: 170px;
            transition: .3s;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.10);
        }

        .kpi-card:hover {
            transform: translateY(-4px);
        }

        .kpi-title {
            font-size: 15px;
            font-weight: 600;
            opacity: 0.95;
        }

        .kpi-value {
            font-size: 42px;
            font-weight: 800;
            margin-top: 18px;
        }

        .kpi-trend {
            margin-top: 12px;
            font-size: 13px;
            opacity: 0.9;
        }

        .kpi-icon {
            position: absolute;
            right: 20px;
            bottom: 15px;
            font-size: 70px;
            opacity: 0.13;
        }

        /* COLORS */

        .bg-blue {
            background: linear-gradient(135deg, #396afc, #2948ff);
        }

        .bg-green {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .bg-orange {
            background: linear-gradient(135deg, #f7971e, #ffd200);
        }

        .bg-red {
            background: linear-gradient(135deg, #cb2d3e, #ef473a);
        }

        .bg-dark {
            background: linear-gradient(135deg, #232526, #414345);
        }

        .bg-purple {
            background: linear-gradient(135deg, #7f00ff, #e100ff);
        }

        .bg-teal {
            background: linear-gradient(135deg, #134e5e, #71b280);
        }

        .bg-cyan {
            background: linear-gradient(135deg, #5b86e5, #36d1dc);
        }

        /* ERP BOX */

        .erp-box {
            background: #fff;
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
        }

        .erp-box-title {
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #173A7A;
        }

        .erp-box-title i {
            margin-right: 8px;
        }

        /* QUICK ACTION */

        .quick-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f7f9fc;
            padding: 16px 18px;
            border-radius: 14px;
            margin-bottom: 14px;
            transition: .3s;
            text-decoration: none !important;
        }

        .quick-action:hover {
            transform: translateX(4px);
        }

        .quick-action-left {
            display: flex;
            align-items: center;
        }

        .quick-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 15px;
            font-size: 18px;
        }

        .quick-text {
            font-weight: 700;
            color: #173A7A;
        }

        /* SUMMARY */

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        /* ACTIVITIES */

        .activity-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 15px;
        }

        .activity-content strong {
            display: block;
            color: #173A7A;
        }

        .activity-content small {
            color: #888;
        }

        /* APPROVALS */

        .approval-widget {
            padding: 18px;
            border-radius: 15px;
            margin-bottom: 15px;
            color: #fff;
        }

        .approval-widget h3 {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
        }

        .approval-widget p {
            margin: 0;
            margin-top: 6px;
        }
    </style>

    <div class="container-fluid dashboard-wrapper animated fadeInRight">

        {{-- EXECUTIVE HEADER --}}

        <div class="executive-header">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <div class="executive-title">

                        <i class="fa fa-shopping-cart"></i>

                        Purchase & Procurement Executive Dashboard

                    </div>

                    <div class="executive-subtitle">

                        Monitor procurement operations, supplier performance,
                        requisitions, purchase orders, goods receipts and purchasing analytics.

                    </div>

                </div>

                <div class="col-lg-4 executive-metrics">

                    <h2>
                        {{ number_format($stats['monthly_purchases'] ?? 0, 2) }}
                    </h2>

                    <span>
                        Monthly Procurement Spend
                    </span>

                </div>

            </div>

        </div>

        {{-- KPI ROW --}}

        <div class="row">

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-cyan">

                    <div class="kpi-title">
                        Total Suppliers
                    </div>

                    <div class="kpi-value">
                        {{ $flow['suppliers'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Active supplier network
                    </div>

                    <i class="fa fa-users kpi-icon"></i>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-green">

                    <div class="kpi-title">
                        Purchase Requisitions
                    </div>

                    <div class="kpi-value">
                        {{ $flow['requisitions'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Procurement requests
                    </div>

                    <i class="fa fa-file-text-o kpi-icon"></i>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-orange">

                    <div class="kpi-title">
                        Purchase Orders
                    </div>

                    <div class="kpi-value">
                        {{ $flow['orders'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Orders processed
                    </div>

                    <i class="fa fa-shopping-basket kpi-icon"></i>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-blue">

                    <div class="kpi-title">
                        Goods Receipts
                    </div>

                    <div class="kpi-value">
                        {{ $flow['receipts'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Inventory received
                    </div>

                    <i class="fa fa-truck kpi-icon"></i>

                </div>

            </div>

        </div>

        {{-- SECOND KPI ROW --}}

        <div class="row">

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-red">

                    <div class="kpi-title">
                        Paid Orders
                    </div>

                    <div class="kpi-value">
                        {{ $flow['paid_orders'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Purchase orders fully paid
                    </div>

                    <i class="fa fa-money kpi-icon"></i>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-dark">

                    <div class="kpi-title">
                        Draft Orders
                    </div>

                    <div class="kpi-value">
                        {{ $flow['draft_orders'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Orders in draft stage
                    </div>

                    <i class="fa fa-pencil-square-o kpi-icon"></i>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-purple">

                    <div class="kpi-title">
                        Debit Notes
                    </div>

                    <div class="kpi-value">
                        {{ $flow['debits'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Supplier debit records
                    </div>

                    <i class="fa fa-refresh kpi-icon"></i>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="kpi-card bg-teal">

                    <div class="kpi-title">
                        Received Orders
                    </div>

                    <div class="kpi-value">
                        {{ $flow['received_orders'] ?? 0 }}
                    </div>

                    <div class="kpi-trend">
                        Fully received orders
                    </div>

                    <i class="fa fa-check-circle kpi-icon"></i>

                </div>

            </div>

        </div>

        {{-- ANALYTICS ROW --}}

        <div class="row">

            {{-- QUICK ACTIONS --}}

            <div class="col-lg-4">

                <div class="erp-box">

                    <div class="erp-box-title">

                        <i class="fa fa-bolt"></i>

                        Quick Actions

                    </div>

                    <a href="{{ route('sales.vendors.index') }}" class="quick-action">

                        <div class="quick-action-left">

                            <div class="quick-icon bg-primary">
                                <i class="fa fa-users"></i>
                            </div>

                            <div class="quick-text">
                                Manage Suppliers
                            </div>

                        </div>

                        <i class="fa fa-angle-right"></i>

                    </a>

                    <a href="{{ url('/admin/reqsts/general-supply/requisition') }}" class="quick-action">

                        <div class="quick-action-left">

                            <div class="quick-icon bg-warning">
                                <i class="fa fa-file-text"></i>
                            </div>

                            <div class="quick-text">
                                Purchase Requisitions
                            </div>

                        </div>

                        <i class="fa fa-angle-right"></i>

                    </a>

                    <a href="{{ route('sales.po.index') }}" class="quick-action">

                        <div class="quick-action-left">

                            <div class="quick-icon bg-success">
                                <i class="fa fa-shopping-cart"></i>
                            </div>

                            <div class="quick-text">
                                Purchase Orders
                            </div>

                        </div>

                        <i class="fa fa-angle-right"></i>

                    </a>

                    <a href="{{ route('sales.po.index') }}" class="quick-action">

                        <div class="quick-action-left">

                            <div class="quick-icon bg-danger">
                                <i class="fa fa-money"></i>
                            </div>

                            <div class="quick-text">
                                Paid / Unpaid Orders
                            </div>

                        </div>

                        <i class="fa fa-angle-right"></i>

                    </a>

                </div>

            </div>

            {{-- PROCUREMENT SUMMARY --}}

            <div class="col-lg-4">

                <div class="erp-box">

                    <div class="erp-box-title">

                        <i class="fa fa-pie-chart"></i>

                        Procurement Summary

                    </div>

                    <div class="summary-item">
                        <strong>Total Purchase Orders</strong>
                        <span>{{ $stats['purchase_orders'] ?? 0 }}</span>
                    </div>

                    <div class="summary-item">
                        <strong>Draft Orders</strong>
                        <span class="text-warning">
                            {{ $stats['draft_orders'] ?? 0 }}
                        </span>
                    </div>

                    <div class="summary-item">
                        <strong>Approved Orders</strong>
                        <span class="text-success">
                            {{ $stats['approved_orders'] ?? 0 }}
                        </span>
                    </div>

                    <div class="summary-item">
                        <strong>Pending Receipts</strong>
                        <span class="text-danger">
                            {{ $stats['pending_orders'] ?? 0 }}
                        </span>
                    </div>

                    <div class="summary-item">
                        <strong>Received Orders</strong>
                        <span class="text-primary">
                            {{ $stats['received_orders'] ?? 0 }}
                        </span>
                    </div>

                    <div class="summary-item">
                        <strong>Paid Orders</strong>
                        <span class="text-success">
                            {{ $stats['paid_orders'] ?? 0 }}
                        </span>
                    </div>

                    <div class="summary-item">
                        <strong>Unpaid Orders</strong>
                        <span class="text-danger">
                            {{ $stats['unpaid_orders'] ?? 0 }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- APPROVAL WORKFLOW --}}

            <div class="col-lg-4">

                <div class="erp-box">

                    <div class="erp-box-title">

                        <i class="fa fa-check-circle"></i>

                        Approval Workflow

                    </div>

                    <div class="approval-widget bg-warning">

                        <h3>
                            {{ $stats['draft_orders'] ?? 0 }}
                        </h3>

                        <p>
                            Draft Purchase Orders
                        </p>

                    </div>

                    <div class="approval-widget bg-primary">

                        <h3>
                            {{ $stats['approved_orders'] ?? 0 }}
                        </h3>

                        <p>
                            Approved Purchase Orders
                        </p>

                    </div>

                    <div class="approval-widget bg-success">

                        <h3>
                            {{ $stats['received_orders'] ?? 0 }}
                        </h3>

                        <p>
                            Completed Procurement
                        </p>

                    </div>

                </div>

            </div>

        </div>

        {{-- BOTTOM ROW --}}

        <div class="row">

            {{-- RECENT ACTIVITIES --}}

            <div class="col-lg-6">

                <div class="erp-box">

                    <div class="erp-box-title">

                        <i class="fa fa-history"></i>

                        Procurement Activities

                    </div>

                    @forelse($recentOrders as $order)
                        <div class="activity-item">

                            <div class="activity-icon bg-primary">

                                <i class="fa fa-shopping-cart"></i>

                            </div>

                            <div class="activity-content">

                                <strong>
                                    Purchase Order:
                                    {{ $order->po_no ?? 'PO' }}
                                </strong>

                                <small>
                                    {{ optional($order->created_at)->diffForHumans() }}
                                </small>

                            </div>

                        </div>

                    @empty

                        <div class="text-center text-muted">

                            No recent procurement activities

                        </div>
                    @endforelse

                </div>

            </div>

            {{-- RECENT PURCHASE ORDERS --}}

            <div class="col-lg-6">

                <div class="erp-box">

                    <div class="erp-box-title">

                        <i class="fa fa-list"></i>

                        Recent Purchase Orders

                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover">

                            <thead>

                                <tr>

                                    <th>PO Number</th>
                                    <th>Date</th>
                                    <th>Status</th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($recentOrders as $order)
                                    <tr>

                                        <td>
                                            {{ $order->po_no ?? 'PO' }}
                                        </td>

                                        <td>
                                            @if (!empty($order->po_date))
                                                {{ \Carbon\Carbon::parse($order->po_date)->format('d M Y') }}
                                            @else
                                                {{ optional($order->created_at)->format('d M Y') }}
                                            @endif
                                        </td>

                                        <td>

                                            @php
                                                $status = $order->status ?? 'Draft';

                                                $badgeClass = 'badge-primary';

                                                if ($status == 'Draft') {
                                                    $badgeClass = 'badge-warning';
                                                } elseif ($status == 'Approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($status == 'Ordered') {
                                                    $badgeClass = 'badge-primary';
                                                } elseif ($status == 'PartiallyReceived') {
                                                    $badgeClass = 'badge-info';
                                                } elseif ($status == 'Received') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($status == 'Closed') {
                                                    $badgeClass = 'badge-dark';
                                                } elseif ($status == 'Cancelled') {
                                                    $badgeClass = 'badge-danger';
                                                }
                                            @endphp

                                            <span class="badge {{ $badgeClass }}">
                                                {{ $status }}
                                            </span>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="3" class="text-center text-muted">

                                            No purchase orders found

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
