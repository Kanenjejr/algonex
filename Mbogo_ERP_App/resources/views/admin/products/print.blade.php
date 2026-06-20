<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Information Sheet</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #eef1f5;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }

        .screen-controls {
            max-width: 210mm;
            margin: 16px auto 8px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 0 4px;
        }

        .btn {
            border: 0;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: #163d7a;
            color: #fff;
        }

        .btn-back {
            background: #e5e7eb;
            color: #111827;
        }

        .print-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 0 0 18px;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            border: 1px solid #d9dde3;
            overflow: hidden;
            box-shadow: 0 6px 22px rgba(0,0,0,.08);
            display: flex;
            flex-direction: column;
        }

        .header-image {
            width: 100%;
            line-height: 0;
            border-bottom: 3px solid #163d7a;
        }

        .header-image img {
            width: 100%;
            display: block;
        }

        .title-bar {
            text-align: center;
            padding: 10px 18px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .title-bar h2 {
            margin: 0;
            font-size: 18px;
            color: #163d7a;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .title-bar p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #6b7280;
        }

        .body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 14px 16px 12px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 14px;
            align-items: start;
        }

        .box {
            border: 1px solid #d9dde3;
            border-radius: 8px;
            padding: 12px;
            background: #fff;
        }

        .box-title {
            font-weight: 700;
            color: #0f3b7a;
            font-size: 12px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.details th,
        table.details td {
            border: 1px solid #d9dde3;
            padding: 8px 10px;
            font-size: 12px;
            vertical-align: top;
            word-wrap: break-word;
            color: #111827;
        }

        table.details th {
            width: 34%;
            background: #f8fafc;
            font-weight: 700;
        }

        .summary {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .summary li {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px dashed #e5e7eb;
            padding: 7px 0;
            font-size: 12px;
        }

        .summary li span:first-child {
            color: #4b5563;
            font-weight: 600;
        }

        .summary li span:last-child {
            color: #111827;
            font-weight: 700;
            text-align: right;
        }

        .badge-active,
        .badge-inactive {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 11px;
        }

        .badge-active {
            background: #e8fff2;
            color: #0a7a3d;
        }

        .badge-inactive {
            background: #fff1f2;
            color: #b42318;
        }

        .section-bottom {
            margin-top: 14px;
        }

        .section-bottom table.details th {
            width: 28%;
        }

        .workpoint-block {
            margin-top: 14px;
            page-break-inside: avoid;
        }

        .workpoint-title {
            background: #163d7a;
            color: #fff;
            padding: 10px 12px;
            border-radius: 8px 8px 0 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .group-box {
            border: 1px solid #d9dde3;
            border-top: 0;
            margin-bottom: 12px;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
        }

        .group-head {
            background: #f8fafc;
            padding: 9px 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #0f3b7a;
            font-weight: 700;
            font-size: 12px;
        }

        .table-list {
            width: 100%;
            border-collapse: collapse;
        }

        .table-list th,
        .table-list td {
            border: 1px solid #e5e7eb;
            padding: 8px 9px;
            font-size: 11px;
            color: #111827;
        }

        .table-list thead th {
            background: #0d6efd;
            color: #fff;
            font-weight: 700;
        }

        .table-list tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .no-products {
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
        }

        @media print {
            html, body {
                background: #fff !important;
                margin: 0 !important;
            }

            .screen-controls {
                display: none !important;
            }

            .print-wrap {
                padding: 0 !important;
                margin: 0 !important;
            }

            .print-page {
                width: 100% !important;
                min-height: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

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

<div class="screen-controls no-print">
    <button class="btn btn-print" onclick="window.print()">Print</button>
    <a href="{{ route('products.index') }}" class="btn btn-back">Back</a>
</div>

<div class="print-wrap">
    <div class="print-page">

        <div class="header-image">
            <img src="{{ asset('img/header.png') }}" alt="Company Header">
        </div>

        <div class="title-bar">
            <h2>PRODUCT INFORMATION SHEET</h2>
            <p>{{ $productCode }} | {{ now()->format('d M Y H:i') }}</p>
        </div>

        <div class="body">

            <div class="grid">
                <div class="box">
                    <div class="box-title">Product Details</div>

                    <table class="details">
                        <tr>
                            <th>Product Name</th>
                            <td><strong>{{ $product->product_name }}</strong></td>
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
                    </table>
                </div>

                <div class="box">
                    <div class="box-title">Stock & Pricing Summary</div>

                    <ul class="summary">
                        <li><span>Average Cost</span><span>{{ number_format((float) ($product->avg_cost ?? 0), 2) }}</span></li>
                        <li><span>Selling Price</span><span>{{ number_format((float) ($product->selling_price ?? 0), 2) }}</span></li>
                        <li><span>Opening Stock</span><span>{{ number_format((float) ($product->opening_stock ?? 0), 2) }}</span></li>
                        <li><span>Stock In</span><span>{{ number_format((float) ($product->stock_in ?? 0), 2) }}</span></li>
                        <li><span>Stock Out</span><span>{{ number_format((float) ($product->stock_out ?? 0), 2) }}</span></li>
                        <li><span>Current Stock</span><span>{{ number_format((float) ($product->current_stock ?? 0), 2) }}</span></li>
                        <li><span>Reorder Level</span><span>{{ number_format((float) ($product->reorder_level ?? 10), 2) }}</span></li>
                        <li>
                            <span>Status</span>
                            <span>
                                @if (($product->status ?? '') === 'Active')
                                    <span class="badge-active">Active</span>
                                @else
                                    <span class="badge-inactive">{{ $product->status ?? '-' }}</span>
                                @endif
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="section-bottom box">
                <div class="box-title">Financial Account Mapping</div>

                <table class="details">
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

            <div class="workpoint-block">
                <div class="workpoint-title">
                    PRODUCTS IN THIS WORK POINT: {{ strtoupper($currentWorkPointName) }}
                </div>

                @forelse($groupedProducts as $groupName => $groupItems)
                    <div class="group-box">
                        <div class="group-head">{{ $groupName }}</div>

                        <table class="table-list">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Product Name</th>
                                    <th style="width:110px;">Size</th>
                                    <th style="width:110px;">Current Stock</th>
                                    <th style="width:90px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->product_size ?? '-' }}</td>
                                        <td>{{ number_format((float) ($item->current_stock ?? 0), 2) }}</td>
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
                @empty
                    <div class="no-products">
                        No products found for this work point.
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

@if(isset($product))
<script>
    window.onload = function () {
        window.print();
    };
</script>
@endif

</body>
</html>