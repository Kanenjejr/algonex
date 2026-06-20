@extends('layouts.salesMaster')

@section('content')

@php
    $productCode = $product->product_code ?? 'PRD-' . str_pad($product->id, 5, '0', STR_PAD_LEFT);

    $currentWorkPointId = $product->work_point_id ?? null;
    $currentWorkPointName = optional($product->workPoint)->work_name ?? 'NO LOCATION';

    $workPointProducts = isset($products)
        ? $products->where('work_point_id', $currentWorkPointId)->values()
        : collect([$product]);

    $groupedProducts = $workPointProducts->sortBy('product_name')->groupBy(function ($p) {
        $name = strtoupper(trim($p->product_name ?? ''));

        if (strpos($name, 'LP 3M') !== false) return 'LP 3M';
        if (strpos($name, 'LP 4.2M') !== false) return 'LP 4.2M';
        if (strpos($name, 'LP 5') !== false) return 'LP 5M';

        if (strpos($name, 'LDS 3') !== false) return 'LDS 3 MTR';
        if (strpos($name, 'LDS 4.2') !== false) return 'LDS 4.2 MTR';
        if (strpos($name, 'LDS 5') !== false) return 'LDS 5 MTR';
        if (strpos($name, 'LDS 7') !== false) return 'LDS 7 MTR';

        if (strpos($name, 'DTH 15') !== false) return 'DTH 15 MTR';
        if (strpos($name, 'DTH 18') !== false) return 'DTH 18 MTR';
        if (strpos($name, 'DTH 21') !== false) return 'DTH 21 MTR';

        if (strpos($name, 'STL 4') !== false) return 'STL 4 MTR';
        if (strpos($name, 'STL 5') !== false) return 'STL 5 MTR';
        if (strpos($name, 'STL 6') !== false) return 'STL 6 MTR';

        if (strpos($name, 'COMBIDET') !== false) return 'COMBIDET';
        if (strpos($name, 'IED') !== false && strpos($name, 'WIRE') !== false) return 'IED GI WIRE';
        if (strpos($name, 'BULK EMULSION') !== false) return 'BULK EMULSION';
        if (strpos($name, 'ANFO') !== false) return 'ANFO';
        if (strpos($name, 'POROUS PRILLED') !== false) return 'POROUS PRILLED AMMONIUM NITRATE';
        if (strpos($name, 'DETONATING CORD') !== false) return 'DETONATING CORD';
        if (strpos($name, 'CAST BOOSTER') !== false) return 'CAST BOOSTER';
        if (strpos($name, 'SOLAR SAFETY FUSE') !== false || strpos($name, 'SOLAR CAPPED FUSE') !== false) return 'SOLAR FUSE';
        if (strpos($name, 'LEAD-IN LINE') !== false) return 'LEAD-IN LINE';
        if (strpos($name, 'SUPERPOWER 90') !== false) return 'SUPERPOWER 90 CARTRIDGE';
        if (strpos($name, 'MBOGO MEGA FUSE') !== false) return 'MBOGO MEGA FUSE';

        return $p->product_name ?? 'OTHER PRODUCTS';
    });
@endphp

<style>
    .product-view-page{
        background:#f4f6f9;
        padding:18px 0 30px 0;
        min-height:100vh;
    }

    .product-wrapper{
        max-width:1280px;
        margin:0 auto;
        padding:0 15px;
    }

    .action-buttons{
        margin-bottom:14px;
        text-align:right;
    }

    .action-buttons .btn{
        margin-left:6px;
        border-radius:6px;
        box-shadow:0 1px 3px rgba(0,0,0,.08);
    }

    .product-card{
        background:#fff;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 2px 14px rgba(0,0,0,.08);
    }

    .header-image img{
        width:100%;
        display:block;
    }

    .title-area{
        text-align:center;
        padding:14px 18px 12px 18px;
        border-bottom:1px solid #e5e7eb;
        background:#fff;
    }

    .title-area h3{
        margin:0;
        font-weight:800;
        color:#0f3b7a;
        letter-spacing:.3px;
    }

    .title-area small{
        color:#6b7280;
        display:block;
        margin-top:4px;
    }

    .section-title{
        background:#0f3b7a;
        color:#fff;
        padding:11px 15px;
        font-weight:700;
        margin-top:15px;
        border-radius:8px 8px 0 0;
        letter-spacing:.2px;
    }

    .table-details{
        width:100%;
        margin-bottom:0;
        background:#fff;
    }

    .table-details th{
        width:32%;
        background:#f8fafc !important;
        color:#111827 !important;
        font-weight:700;
    }

    .table-details th,
    .table-details td{
        padding:11px 12px;
        border:1px solid #e5e7eb;
        vertical-align:top;
        font-size:13px;
        color:#111827 !important;
        background:#fff;
    }

    .badge-active{
        background:#16a34a;
        color:#fff;
        padding:5px 12px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        display:inline-block;
    }

    .badge-inactive{
        background:#dc2626;
        color:#fff;
        padding:5px 12px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        display:inline-block;
    }

    .summary-box{
        background:#fcfcfd;
        border:1px solid #e5e7eb;
        border-radius:10px;
        padding:15px;
        margin-bottom:15px;
    }

    .summary-box h4{
        margin:0 0 12px 0;
        color:#0f3b7a;
        font-weight:800;
        font-size:15px;
    }

    .summary-row{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:15px;
        border-bottom:1px dashed #e5e7eb;
        padding:8px 0;
        font-size:13px;
    }

    .summary-row:last-child{
        border-bottom:none;
    }

    .summary-row span{
        color:#4b5563;
        font-weight:600;
    }

    .summary-row strong{
        color:#111827;
        font-weight:800;
    }

    .panel-wrap{
        margin-top:15px;
        border:1px solid #e5e7eb;
        border-radius:10px;
        overflow:hidden;
        background:#fff;
    }

    .panel-head{
        background:#0f3b7a;
        color:#fff;
        padding:10px 15px;
    }

    .panel-head h4{
        margin:0;
        font-weight:800;
        font-size:15px;
    }

    .panel-body{
        padding:15px;
        background:#fff;
    }

    .ibox{
        margin-bottom:16px;
        border:1px solid #e5e7eb;
        border-radius:10px;
        overflow:hidden;
        background:#fff;
    }

    .ibox-title{
        background:#f8fafc;
        padding:10px 14px;
        border-bottom:1px solid #e5e7eb;
    }

    .ibox-title h4{
        margin:0;
        color:#0f3b7a;
        font-weight:800;
        font-size:14px;
    }

    .table-products{
        margin-bottom:0;
        width:100%;
        background:#fff;
    }

    .table-products thead th{
        background:#0d6efd !important;
        color:#fff !important;
        text-align:center;
        vertical-align:middle;
        font-weight:700;
        font-size:13px;
        border-color:#0b5ed7 !important;
    }

    .table-products tbody td{
        vertical-align:middle;
        font-size:13px;
        color:#111827 !important;
        background:#fff !important;
    }

    .table-products tbody tr:nth-child(even) td{
        background:#f8fafc !important;
    }

    .table-products td,
    .table-products th{
        border-color:#e5e7eb !important;
    }

    .empty-box{
        padding:14px;
        border:1px dashed #cbd5e1;
        border-radius:8px;
        background:#f8fafc;
        color:#475569;
    }

    @media (max-width: 991px){
        .action-buttons{
            text-align:left;
        }
    }
</style>

<div class="product-view-page">
    <div class="product-wrapper">

        <div class="action-buttons">
            <a href="{{ route('products.index') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>

            <a href="{{ route('products.print', $product->id) }}" class="btn btn-primary">
                <i class="fa fa-print"></i> Print
            </a>
        </div>

        <div class="product-card">

            <div class="header-image">
                <img src="{{ asset('img/header.png') }}" alt="Header">
            </div>

            <div class="title-area">
                <h3>PRODUCT INFORMATION SHEET</h3>
                <small>
                    Product Code: <strong>{{ $productCode }}</strong>
                </small>
            </div>

            <div class="row" style="padding:15px;">

                <div class="col-md-8">
                    <div class="section-title">Product Details</div>

                    <table class="table table-details">
                        <tr>
                            <th>Product Name</th>
                            <td>{{ $product->product_name }}</td>
                        </tr>
                        <tr>
                            <th>Product Code</th>
                            <td>{{ $productCode }}</td>
                        </tr>
                        <tr>
                            <th>Details / Unit / Size</th>
                            <td>{{ $product->product_size ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <td>{{ optional($product->company)->company_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Company Code</th>
                            <td>{{ optional($product->company)->company_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Company Unit</th>
                            <td>{{ optional($product->businessUnit)->unit_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Work Point</th>
                            <td>{{ optional($product->workPoint)->work_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Work Code</th>
                            <td>{{ optional($product->workPoint)->work_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if(($product->status ?? '') === 'Active')
                                    <span class="badge-active">Active</span>
                                @else
                                    <span class="badge-inactive">{{ $product->status ?? '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-4">
                    <div class="summary-box">
                        <h4>Stock Summary</h4>

                        <div class="summary-row">
                            <span>Opening Stock</span>
                            <strong>{{ number_format($product->opening_stock ?? 0, 2) }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Stock In</span>
                            <strong>{{ number_format($product->stock_in ?? 0, 2) }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Stock Out</span>
                            <strong>{{ number_format($product->stock_out ?? 0, 2) }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Current Stock</span>
                            <strong>{{ number_format($product->current_stock ?? 0, 2) }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Reorder Level</span>
                            <strong>{{ number_format($product->reorder_level ?? 0, 2) }}</strong>
                        </div>
                    </div>

                    <div class="summary-box">
                        <h4>Pricing</h4>

                        <div class="summary-row">
                            <span>Average Cost</span>
                            <strong>{{ number_format($product->avg_cost ?? 0, 2) }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Selling Price</span>
                            <strong>{{ number_format($product->selling_price ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>

            </div>

            <div style="padding:15px;">
                <div class="section-title">Financial Account Mapping</div>

                <table class="table table-details">
                    <tr>
                        <th>Inventory Account</th>
                        <td>{{ $product->inventory_account_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>COGS Account</th>
                        <td>{{ $product->cogs_account_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Revenue Account</th>
                        <td>{{ $product->revenue_account_code ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <div style="padding:15px;">
                <div class="section-title">PRODUCTS IN THIS WORK POINT</div>

                <div class="panel-wrap">
                    <div class="panel-head">
                        <h4>{{ strtoupper($currentWorkPointName) }}</h4>
                    </div>

                    <div class="panel-body">

                        @forelse($groupedProducts as $groupName => $groupItems)

                            <div class="ibox">
                                <div class="ibox-title">
                                    <h4>{{ $groupName }}</h4>
                                </div>

                                <div class="ibox-content" style="padding:0;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-products">
                                            <thead>
                                                <tr>
                                                    <th width="60">#</th>
                                                    <th>Product Name</th>
                                                    <th>Size / Details</th>
                                                    <th width="150">Current Stock</th>
                                                    <th width="120">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($groupItems as $index => $item)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $item->product_name }}</td>
                                                        <td>{{ $item->product_size ?? '-' }}</td>
                                                        <td>{{ number_format($item->current_stock ?? 0, 2) }}</td>
                                                        <td>
                                                            @if(($item->status ?? '') === 'Active')
                                                                <span class="badge-active">Active</span>
                                                            @else
                                                                <span class="badge-inactive">{{ $item->status ?? '-' }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <div class="empty-box">
                                No products found for this work point.
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection