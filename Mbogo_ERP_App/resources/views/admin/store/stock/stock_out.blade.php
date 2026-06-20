@extends('layouts.salesMaster')

@section('content')
@php
    $products = $products ?? [];
    $workPoints = $workPoints ?? [];
    $rows = $rows ?? [];
    $adjustments = $adjustments ?? [];
    $lowStockProducts = $lowStockProducts ?? [];
    $itemStocks = $itemStocks ?? [];
    $movement = $movement ?? [];
    $stats = $stats ?? [];

    $totalProducts   = count($products);
    $totalStock      = (float) ($stats['total_stock'] ?? $stats['inventory_qty'] ?? 0);
    $receivedStock   = (float) ($stats['received'] ?? 0);
    $issuedStock     = (float) ($stats['issued'] ?? 0);
    $lowStockCount   = (int) ($stats['low_stock'] ?? 0);
    $adjustCount     = count($adjustments);

    $fmt = function ($value) {
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
    .erp-shell {
        background: #f8fafc;
        min-height: 100vh;
    }

    .erp-hero {
        background: #fff;
        border: 1px solid #e7eaec;
        border-radius: 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
        padding: 20px 22px;
        margin-bottom: 20px;
    }

    .erp-title {
        font-size: 28px;
        font-weight: 900;
        color: #111827;
        margin-bottom: 6px;
    }

    .erp-subtitle {
        color: #6b7280;
        margin: 0;
        line-height: 1.6;
    }

    .erp-date-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 800;
        font-size: 13px;
        margin-top: 8px;
    }

    .erp-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-start;
        margin-top: 12px;
    }

    .erp-action-btn {
        border-radius: 12px !important;
        font-weight: 800 !important;
        padding: 10px 14px !important;
    }

    .erp-card {
        border: none;
        border-radius: 22px;
        padding: 28px;
        color: #fff;
        min-height: 180px;
        box-shadow: 0 10px 25px rgba(0,0,0,.10);
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .erp-card .card-glow {
        position: absolute;
        right: -20px;
        top: -20px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }

    .erp-card .icon-wrap {
        width: 75px;
        height: 75px;
        border-radius: 20px;
        background: rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .erp-card h5 {
        margin: 0;
        font-weight: 700;
        letter-spacing: .5px;
        color: #fff;
        opacity: .95;
    }

    .erp-card h1 {
        color: #fff;
        font-size: 38px;
        font-weight: 800;
        margin-top: 15px;
        margin-bottom: 0;
    }

    .erp-card small {
        color: rgba(255,255,255,.82);
    }

    .card-blue   { background: linear-gradient(135deg,#1e3a8a,#2563eb); }
    .card-teal   { background: linear-gradient(135deg,#0f766e,#14b8a6); }
    .card-amber  { background: linear-gradient(135deg,#b45309,#f59e0b); }
    .card-rose   { background: linear-gradient(135deg,#be123c,#e11d48); }

    .erp-box {
        border-radius: 18px;
        border: 1px solid #e7eaec;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,.05);
        margin-bottom: 20px;
    }

    .erp-box .ibox-title {
        border-bottom: 1px solid #e7eaec;
        border-radius: 18px 18px 0 0;
        background: #fff;
        padding: 18px;
    }

    .erp-box .ibox-content {
        padding: 18px;
    }

    .table > thead > tr > th {
        background: #f8fafc;
        color: #1f2937;
        font-weight: 800;
        white-space: nowrap;
    }

    .erp-empty {
        text-align: center;
        color: #ef4444;
        font-weight: 800;
        padding: 18px !important;
    }

    .badge-good {
        background: #16a34a;
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
    }

    .badge-bad {
        background: #dc2626;
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
    }

    .erp-filter {
        background: #fff;
        border: 1px solid #e7eaec;
        border-radius: 18px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .erp-filter-head {
        background: #0f172a;
        color: #fff;
        padding: 14px 18px;
        font-weight: 800;
    }

    .erp-filter-body {
        padding: 18px;
    }

    .erp-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #334155;
    }

    .erp-input {
        height: 44px;
        border-radius: 12px;
    }

    .erp-btn {
        height: 44px;
        border-radius: 12px;
        font-weight: 800;
    }

    .select2-container--bootstrap4 .select2-selection {
        height: 44px !important;
        border-radius: 12px !important;
        border: 1px solid #dbe3ee !important;
        padding-top: 6px;
    }

    .summary-mini {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        background: #fff;
        padding: 14px 16px;
        margin-bottom: 15px;
    }

    .summary-mini h4 {
        margin: 0 0 6px 0;
        font-weight: 900;
        color: #0f172a;
    }

    .summary-mini p {
        margin: 0;
        color: #6b7280;
    }
</style>

<div class="wrapper wrapper-content animated fadeInRight erp-shell">

    <div class="row">
        <div class="col-lg-8">
            <div class="erp-hero">
                <h2 class="erp-title">
                    <i class="fa fa-minus-circle text-danger"></i>
                    Stock Out
                </h2>
                <p class="erp-subtitle">
                    Issue products from inventory, reduce balance, and post every movement to the database.
                </p>
                <ol class="breadcrumb" style="margin-top:12px;margin-bottom:0;background:transparent;padding-left:0;">
                    <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('stock.management.dashboard') }}">Stock Management</a></li>
                    <li class="active"><strong>Stock Out</strong></li>
                </ol>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="erp-hero" style="min-height:126px;">
                <div class="erp-date-chip">
                    <i class="fa fa-calendar"></i>
                    {{ \Carbon\Carbon::now()->format('l, Y-m-d') }}
                </div>

                <div style="margin-top:10px;color:#64748b;font-weight:700;">
                    Company-based stock issue control
                </div>

                <div class="erp-actions">
                    <a href="{{ route('stock.management.dashboard') }}" class="btn btn-primary erp-action-btn">
                        <i class="fa fa-home"></i> Dashboard
                    </a>

                    <a href="{{ route('stock.management.adjust') }}" class="btn btn-warning erp-action-btn">
                        <i class="fa fa-exchange"></i> Adjust Stock
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="erp-card card-blue">
                <div class="card-glow"></div>
                <div class="row">
                    <div class="col-xs-4">
                        <div class="icon-wrap">
                            <i class="fa fa-cubes fa-3x"></i>
                        </div>
                    </div>
                    <div class="col-xs-8 text-right">
                        <h5>TOTAL PRODUCTS</h5>
                        <h1>{{ number_format($totalProducts, 0) }}</h1>
                        <small>Products available</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="erp-card card-teal">
                <div class="card-glow"></div>
                <div class="row">
                    <div class="col-xs-4">
                        <div class="icon-wrap">
                            <i class="fa fa-arrow-circle-down fa-3x"></i>
                        </div>
                    </div>
                    <div class="col-xs-8 text-right">
                        <h5>STOCK RECEIVED</h5>
                        <h1>{{ number_format($receivedStock, 2) }}</h1>
                        <small>Total in stock</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="erp-card card-amber">
                <div class="card-glow"></div>
                <div class="row">
                    <div class="col-xs-4">
                        <div class="icon-wrap">
                            <i class="fa fa-arrow-circle-up fa-3x"></i>
                        </div>
                    </div>
                    <div class="col-xs-8 text-right">
                        <h5>STOCK ISSUED</h5>
                        <h1>{{ number_format($issuedStock, 2) }}</h1>
                        <small>Already issued</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="erp-card card-rose">
                <div class="card-glow"></div>
                <div class="row">
                    <div class="col-xs-4">
                        <div class="icon-wrap">
                            <i class="fa fa-warning fa-3x"></i>
                        </div>
                    </div>
                    <div class="col-xs-8 text-right">
                        <h5>LOW STOCK</h5>
                        <h1>{{ number_format($lowStockCount, 0) }}</h1>
                        <small>Reorder items</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-mini">
        <div class="row">
            <div class="col-md-3">
                <h4>{{ number_format($totalStock, 2) }}</h4>
                <p>Total Stock</p>
            </div>
            <div class="col-md-3">
                <h4>{{ number_format($adjustCount) }}</h4>
                <p>Adjustments</p>
            </div>
            <div class="col-md-3">
                <h4>{{ number_format(count($movement)) }}</h4>
                <p>Movement Rows</p>
            </div>
            <div class="col-md-3">
                <h4>{{ number_format(count($itemStocks)) }}</h4>
                <p>Stock Summary Rows</p>
            </div>
        </div>
    </div>

    <div class="erp-box">
        <div class="ibox-title">
            <h5 style="margin:0;font-weight:800;">
                <i class="fa fa-minus-circle"></i> Issue Product / Stock Out
            </h5>
        </div>

        <div class="ibox-content">
            <div class="erp-filter">
                <div class="erp-filter-head">
                    <i class="fa fa-edit"></i> Stock Out Form
                </div>
                <div class="erp-filter-body">
                    <form method="POST" action="{{ route('stock.management.stock.out.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-5">
                                <label class="erp-label">Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-control select2_demo_1 erp-input" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ encrypt($product->id) }}" {{ old('product_id') == encrypt($product->id) ? 'selected' : '' }}>
                                            {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="erp-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number"
                                       step="0.01"
                                       min="0.01"
                                       name="qty"
                                       class="form-control erp-input"
                                       value="{{ old('qty') }}"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="erp-label">Description / Notes</label>
                                <input type="text"
                                       name="description"
                                       class="form-control erp-input"
                                       value="{{ old('description') }}"
                                       placeholder="Reason for stock out">
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-12 text-right">
                                <button class="btn btn-danger erp-btn">
                                    <i class="fa fa-save"></i> Save Stock Out
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover dataTables-example">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Qty Out</th>
                            <th>Balance</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $stockOutRows = collect($rows)->filter(function ($row) {
                                $type = strtoupper((string) ($row->type ?? ''));
                                $ref = strtolower((string) ($row->reference_type ?? ''));
                                return $type === 'OUT' || in_array($ref, ['stock_out', 'issue', 'sale'], true);
                            });
                        @endphp

                        @forelse($stockOutRows as $row)
                            <tr>
                                <td>{{ $fmt($row->date ?? $row->created_at ?? null) }}</td>
                                <td>{{ optional($row->product)->product_name ?? $row->product_name ?? 'N/A' }}</td>
                                <td>{{ number_format((float) ($row->qty_out ?? 0), 2) }}</td>
                                <td>{{ number_format((float) ($row->balance ?? 0), 2) }}</td>
                                <td>{{ $row->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">No Stock Out Data Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="erp-box">
                <div class="ibox-title">
                    <h5 style="margin:0;font-weight:800;">
                        <i class="fa fa-line-chart"></i> Stock Movement Summary
                    </h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Opening</th>
                                    <th>Received</th>
                                    <th>Issued</th>
                                    <th>Closing</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movement as $row)
                                    <tr>
                                        <td>{{ $row['item_name'] ?? 'N/A' }}</td>
                                        <td>{{ number_format((float) ($row['opening'] ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($row['received'] ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($row['issued'] ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($row['closing'] ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="erp-empty">No Movement Data Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="erp-box">
                <div class="ibox-title">
                    <h5 style="margin:0;font-weight:800;">
                        <i class="fa fa-warning"></i> Low Stock Items
                    </h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts as $p)
                                    @php
                                        $currentQty = (float) (optional($p->stock)->current_stock ?? $p->current_stock ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $p->product_name ?? 'N/A' }}</td>
                                        <td class="text-danger">{{ number_format($currentQty, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="erp-empty">No Low Stock Data Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    function initTables() {
        if (window.jQuery && $.fn.select2) {
            $('.select2_demo_1').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        if (window.jQuery && $.fn.DataTable) {
            $('.dataTables-example').each(function () {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }

                $(this).DataTable({
                    pageLength: 25,
                    autoWidth: false,
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initTables);
})();
</script>
@endsection
