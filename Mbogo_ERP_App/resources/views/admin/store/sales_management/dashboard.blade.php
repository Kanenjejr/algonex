{{-- resources/views/sales_management/dashboard.blade.php --}}
@extends('layouts.salesMaster')

@section('content')
    @php
        $stats = $stats ?? [];
        $recentInvoices = $recentInvoices ?? [];
        $recentDeliveries = $recentDeliveries ?? [];
        $recentPayments = $recentPayments ?? [];
        $topCustomers = $topCustomers ?? [];
        $topProducts = $topProducts ?? [];
        $lowStockProducts = $lowStockProducts ?? [];
    @endphp

    <style>
        .dashboard-title {
            font-weight: 800;
            margin-bottom: 5px;
            color: #1f2d3d;
            letter-spacing: .5px;
        }

        .dashboard-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .summary-card {
            border-radius: 16px;
            color: #fff;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: 0.25s ease-in-out;
            overflow: hidden;
            min-height: 140px;
        }

        .summary-card:hover {
            transform: translateY(-4px);
        }

        .summary-card .ibox-title {
            border: none;
            padding: 14px 18px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.12);
        }

        .summary-card .ibox-content {
            background: transparent;
            padding: 18px;
        }

        .summary-card h2 {
            margin: 0;
            font-weight: 800;
            font-size: 30px;
        }

        .summary-card small {
            opacity: .92;
            font-size: 13px;
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #0d6efd, #4dabf7);
        }

        .bg-gradient-success {
            background: linear-gradient(45deg, #198754, #5dd39e);
        }

        .bg-gradient-warning {
            background: linear-gradient(45deg, #fd7e14, #ffb347);
            color: #111;
        }

        .bg-gradient-danger {
            background: linear-gradient(45deg, #dc3545, #ff6b6b);
        }

        .bg-gradient-dark {
            background: linear-gradient(45deg, #212529, #495057);
        }

        .bg-gradient-info {
            background: linear-gradient(45deg, #0dcaf0, #67e8f9);
            color: #111;
        }

        .ibox {
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
            border: 1px solid #eef1f5;
            overflow: hidden;
        }

        .ibox-title {
            font-weight: 700;
            border-bottom: 1px solid #eef1f5;
        }

        .section-title {
            font-weight: 800;
            color: #1f2d3d;
            margin-bottom: 14px;
        }

        .metric-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .metric-badge.success {
            background: #d1fae5;
            color: #065f46;
        }

        .metric-badge.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .metric-badge.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .metric-badge.info {
            background: #dbeafe;
            color: #1e40af;
        }

        table thead th {
            background: #f8fafc;
            font-weight: 700;
            color: #334155;
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8">
            <h2 class="dashboard-title">Sales Management Dashboard</h2>
            <p class="dashboard-subtitle">
                General summary only. Operational modules remain inside Sales & Marketing.
            </p>
            <ol class="breadcrumb" style="font-size:16px;color:#000">
                <li><a href="{{ route('sales.dashboard') }}">Sales Management</a></li>
                <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Master Dashboard</strong></li>
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
            document.getElementById('Hour').innerHTML = String(d.getHours()).padStart(2, '0') + ':';
            document.getElementById('Minut').innerHTML = String(d.getMinutes()).padStart(2, '0') + ':';
            document.getElementById('Second').innerHTML = String(d.getSeconds()).padStart(2, '0');
        }
        timedMsg();
    </script>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- TOP SUMMARY CARDS --}}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-primary">
                    <div class="ibox-title">Sales Today</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['sales_today'] ?? 0, 2) }}</h2>
                        <small>Confirmed sales for today</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-success">
                    <div class="ibox-title">Sales This Month</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['sales_month'] ?? 0, 2) }}</h2>
                        <small>Monthly sales performance</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-info">
                    <div class="ibox-title">Payments Received</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['payments'] ?? 0, 2) }}</h2>
                        <small>Cash, bank, and mobile payments</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-warning">
                    <div class="ibox-title">Pending Invoices</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['unpaid_invoices'] ?? 0) }}</h2>
                        <small>Invoices awaiting settlement</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECOND SUMMARY ROW --}}
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="ibox summary-card bg-gradient-dark">
                    <div class="ibox-title">Proformas</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['proformas'] ?? 0) }}</h2>
                        <small>Issued proforma documents</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="ibox summary-card bg-gradient-success">
                    <div class="ibox-title">Deliveries</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['deliveries'] ?? 0) }}</h2>
                        <small>Delivery records created</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="ibox summary-card bg-gradient-danger">
                    <div class="ibox-title">POS Sales</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['pos_sales'] ?? 0) }}</h2>
                        <small>Point of sale transactions</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- THIRD SUMMARY ROW --}}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-info">
                    <div class="ibox-title">Invoices</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['invoices'] ?? 0) }}</h2>
                        <small>All generated invoices</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-warning">
                    <div class="ibox-title">Pending Deliveries</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['pending_deliveries'] ?? 0) }}</h2>
                        <small>Orders awaiting delivery</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-success">
                    <div class="ibox-title">Customers</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['customers'] ?? 0) }}</h2>
                        <small>Registered active customers</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox summary-card bg-gradient-danger">
                    <div class="ibox-title">Overdue Invoices</div>
                    <div class="ibox-content">
                        <h2>{{ number_format($stats['overdue_invoices'] ?? 0) }}</h2>
                        <small>Need urgent follow-up</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- QUICK INSIGHTS --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 class="section-title">Quick Insights</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <span class="metric-badge success">Strong</span>
                                <div class="mt-2">
                                    <strong>Sales Performance:</strong>
                                    <div>{{ number_format($stats['sales_month'] ?? 0, 2) }} this month</div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <span class="metric-badge warning">Attention</span>
                                <div class="mt-2">
                                    <strong>Collections:</strong>
                                    <div>{{ number_format($stats['unpaid_invoices'] ?? 0) }} unpaid invoices</div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <span class="metric-badge info">Delivery</span>
                                <div class="mt-2">
                                    <strong>Fulfilment:</strong>
                                    <div>{{ number_format($stats['pending_deliveries'] ?? 0) }} pending deliveries</div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <span class="metric-badge danger">POS</span>
                                <div class="mt-2">
                                    <strong>POS Activity:</strong>
                                    <div>{{ number_format($stats['pos_sales'] ?? 0) }} transactions</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RECENT INVOICES AND PAYMENTS --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 class="section-title">Recent Invoices</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_no ?? ($invoice->no ?? 'N/A') }}</td>
                                        <td>{{ $invoice->customer_name ?? (optional($invoice->customer)->customer_name ?? 'N/A') }}
                                        </td>
                                        <td>{{ number_format($invoice->total_amount ?? ($invoice->amount ?? 0), 2) }}</td>
                                        <td>
                                            @if (($invoice->status ?? '') === 'paid')
                                                <span class="label label-success">Paid</span>
                                            @elseif(($invoice->status ?? '') === 'partial')
                                                <span class="label label-warning">Partial</span>
                                            @else
                                                <span class="label label-danger">Unpaid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent invoices found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- DELIVERY AND CUSTOMER INSIGHTS --}}
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 class="section-title">Pending / Recent Deliveries</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Delivery No</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDeliveries as $delivery)
                                    <tr>
                                        <td>{{ $delivery->delivery_no ?? 'N/A' }}</td>
                                        <td>{{ $delivery->customer_name ?? (optional($delivery->order->customer)->customer_name ?? 'N/A') }}
                                        </td>
                                        <td>
                                            @php
                                                $status = strtolower($delivery->delivery_status ?? 'pending');
                                            @endphp

                                            @if ($status === 'pending')
                                                <span class="label label-warning">Pending</span>
                                            @elseif($status === 'in_transit')
                                                <span class="label label-primary">In Transit</span>
                                            @else
                                                <span class="label label-success">Delivered</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No recent deliveries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 class="section-title">Top Customers</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $customer)
                                    <tr>
                                        <td>{{ $customer->customer_name ?? ($customer->name ?? 'N/A') }}</td>
                                        <td>{{ number_format($customer->total_sales ?? ($customer->sales_total ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No top customer data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOP PRODUCTS AND LOW STOCK --}}
        <div class="row">
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 class="section-title">Top Selling Products</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $product)
                                    <tr>
                                        <td>{{ $product->product_name ?? ($product->name ?? 'N/A') }}</td>
                                        <td>{{ number_format($product->qty_sold ?? ($product->quantity_sold ?? 0)) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No product ranking data
                                            available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 class="section-title">Low Stock Alerts</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Reorder Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts as $product)
                                    <tr>
                                        <td>{{ $product->product_name ?? ($product->name ?? 'N/A') }}</td>
                                        <td class="text-danger font-bold">
                                            {{ $product->total_qty ?? ($product->qty ?? 0) }}
                                        </td>
                                        <td>{{ $product->reorder_level ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No low stock items found.</td>
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
