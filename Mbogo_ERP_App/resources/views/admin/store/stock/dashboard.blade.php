@extends('layouts.salesMaster')

@section('content')
    @php
        $products = collect($products ?? []);
        $workPoints = collect($workPoints ?? []);
        $companies = collect($companies ?? []);
        $rows = collect($rows ?? []);
        $adjustments = collect($adjustments ?? []);
        $lowStockProducts = collect($lowStockProducts ?? []);
        $itemStocks = collect($itemStocks ?? []);
        $movement = collect($movement ?? []);
        $stats = $stats ?? [];
        $chartData = collect($chartData ?? []);

        $openingStock = (float) ($stats['opening_stock'] ?? 0);
        $receivedStock = (float) ($stats['received'] ?? 0);
        $issuedStock = (float) ($stats['issued'] ?? 0);
        $closingStock = isset($stats['closing_stock'])
            ? (float) $stats['closing_stock']
            : $openingStock + $receivedStock - $issuedStock;

        $lowStockCount = (int) ($stats['low_stock'] ?? 0);
        $totalProducts = count($products);
        $totalItems = count($itemStocks);
        $totalStock = (float) ($stats['total_stock'] ?? ($stats['inventory_qty'] ?? 0));

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

        $routeUrl = function ($name, $fallback = '#') {
            try {
                return \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fallback;
            } catch (\Throwable $e) {
                return $fallback;
            }
        };

        $receivePostRoute = \Illuminate\Support\Facades\Route::has('stock.management.receive.store')
            ? route('stock.management.receive.store')
            : (\Illuminate\Support\Facades\Route::has('stock.management.receive')
                ? route('stock.management.receive')
                : url()->current());

        $adjustPostRoute = \Illuminate\Support\Facades\Route::has('stock.management.adjust.store')
            ? route('stock.management.adjust.store')
            : (\Illuminate\Support\Facades\Route::has('stock.management.adjust.save')
                ? route('stock.management.adjust.save')
                : url()->current());

        $transferPostRoute = \Illuminate\Support\Facades\Route::has('stock.management.transfer.store')
            ? route('stock.management.transfer.store')
            : (\Illuminate\Support\Facades\Route::has('stock.management.transfer')
                ? route('stock.management.transfer')
                : url()->current());

        $movementPostRoute = \Illuminate\Support\Facades\Route::has('stock.management.movement.store')
            ? route('stock.management.movement.store')
            : url()->current();

        $companyIds = $products->pluck('company_id')->merge($rows->pluck('company_id'))->filter()->unique()->values();

        $unitIds = $products
            ->pluck('comp_unit_id')
            ->merge($products->pluck('company_unit_id'))
            ->merge($rows->pluck('company_unit_id'))
            ->merge($rows->pluck('comp_unit_id'))
            ->filter()
            ->unique()
            ->values();

        try {
            $companySitesMap = \App\Models\CompanySite::whereIn('id', $companyIds)->get()->keyBy('id');
        } catch (\Throwable $e) {
            $companySitesMap = collect();
        }

        try {
            $companyUnitsMap = \App\Models\Company_unit::whereIn('id', $unitIds)->get()->keyBy('id');
        } catch (\Throwable $e) {
            $companyUnitsMap = collect();
        }

        $printAsAtDate = request('to') ?: now()->toDateString();

        try {
            $printAsAtDateFormatted = strtoupper(\Carbon\Carbon::parse($printAsAtDate)->format('d F Y'));
        } catch (\Throwable $e) {
            $printAsAtDateFormatted = strtoupper(now()->format('d F Y'));
        }

        $stockNum = function ($value, $dashZero = false) {
            $value = (float) $value;

            if ($dashZero && abs($value) < 0.00001) {
                return '-';
            }

            if (floor($value) == $value) {
                return number_format($value, 0);
            }

            return number_format($value, 2);
        };

        $physicalStockRows = $products->map(function ($product) use ($rows, $companySitesMap, $companyUnitsMap) {
            $productLedgers = $rows->where('product_id', $product->id);

            $companyId = $product->company_id ?? optional($productLedgers->first())->company_id;
            $unitId =
                $product->comp_unit_id ??
                ($product->company_unit_id ?? optional($productLedgers->first())->company_unit_id);

            $opening = (float) ($product->opening_stock ?? 0);
            $received = (float) $productLedgers->sum('qty_in');
            $issued = (float) $productLedgers->sum('qty_out');
            $totalStock = $opening + $received;
            $closing = $totalStock - $issued;
            $price = (float) ($product->selling_price ?? 0);
            $value = $closing * $price;

            $company = $companySitesMap->get($companyId);
            $unit = $companyUnitsMap->get($unitId);

            /*
            |--------------------------------------------------------------------------
            | DETAILS / UM FIX
            |--------------------------------------------------------------------------
            | Product size/details must not repeat the unit of measure.
            | Example: if product_size = PCS and UM = PCS, DETAILS should show "-".
            */
            $unitMeasure = $product->unit_name ?? ($product->um ?? ($product->unit ?? 'PCS'));
            $rawDetails = trim((string) ($product->product_size ?? ''));
            $rawUnitMeasure = trim((string) ($unitMeasure ?? ''));
            $cleanDetails = '-';

            if ($rawDetails !== '' && strtoupper($rawDetails) !== strtoupper($rawUnitMeasure)) {
                $cleanDetails = $rawDetails;
            }

            return [
                'company_id' => $companyId,
                'company_name' => optional($company)->company_name ?? 'NO COMPANY SITE',
                'company_code' => optional($company)->company_code ?? '',
                'unit_id' => $unitId,
                'unit_name' => optional($unit)->unit_name ?? 'NO COMPANY UNIT',
                'unit_code' => optional($unit)->unit_code ?? '',
                'product' => $product->product_name ?? '-',
                'details' => $cleanDetails,
                'um' => $rawUnitMeasure !== '' ? $rawUnitMeasure : 'PCS',
                'opening' => $opening,
                'received' => $received,
                'total_stock' => $totalStock,
                'issued' => $issued,
                'closing' => $closing,
                'price' => $price,
                'value' => $value,
            ];
        });

        $physicalStockGroups = $physicalStockRows
            ->sortBy([['company_name', 'asc'], ['unit_name', 'asc'], ['product', 'asc']])
            ->groupBy(function ($row) {
                return ($row['company_id'] ?? '0') . '|' . ($row['unit_id'] ?? '0');
            });

        $physicalGrandTotals = [
            'opening' => $physicalStockRows->sum('opening'),
            'received' => $physicalStockRows->sum('received'),
            'total_stock' => $physicalStockRows->sum('total_stock'),
            'issued' => $physicalStockRows->sum('issued'),
            'closing' => $physicalStockRows->sum('closing'),
            'value' => $physicalStockRows->sum('value'),
        ];

        $receiveRows = $rows->filter(function ($row) {
            return ($row->reference_type ?? null) === 'purchase' ||
                ($row->type ?? null) === 'IN' ||
                (float) ($row->qty_in ?? 0) > 0;
        });
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
            box-shadow: 0 10px 25px rgba(0, 0, 0, .10);
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
            background: rgba(255, 255, 255, .08);
        }

        .erp-card .icon-wrap {
            width: 75px;
            height: 75px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .15);
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
            color: rgba(255, 255, 255, .82);
        }

        .card-blue {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
        }

        .card-teal {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
        }

        .card-amber {
            background: linear-gradient(135deg, #b45309, #f59e0b);
        }

        .card-rose {
            background: linear-gradient(135deg, #be123c, #e11d48);
        }

        .erp-box {
            border-radius: 18px;
            border: 1px solid #e7eaec;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            margin-bottom: 20px;
            overflow: hidden;
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

        .erp-section-title {
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .erp-subtitle-2 {
            color: #6b7280;
            margin-top: 0;
            margin-bottom: 14px;
        }

        .nav-tabs>li>a {
            font-weight: 700;
        }

        .table>thead>tr>th {
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

        .inventory-note {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 14px 16px;
            color: #1e3a8a;
            font-weight: 700;
            margin-bottom: 20px;
        }

        select.select2_demo_1,
        select.select3,
        select.select3_demo_1 {
            width: 100% !important;
            display: block;
        }

        .select2-container,
        .select2-container--default,
        .select2-container--bootstrap4,
        .select2-container--bootstrap {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
        }

        .select2-container .select2-selection--single,
        .select2-container--default .select2-selection--single,
        .select2-container--bootstrap4 .select2-selection--single,
        .select2-container--bootstrap .select2-selection--single {
            width: 100% !important;
            min-height: 44px !important;
            height: 44px !important;
            border-radius: 12px !important;
            border: 1px solid #dbe3ee !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 42px !important;
            padding-left: 12px !important;
            padding-right: 30px !important;
            width: 100% !important;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border-color: #dbe3ee !important;
            border-radius: 12px !important;
            overflow: hidden;
            z-index: 999999 !important;
        }

        .select2-search__field {
            width: 100% !important;
            border-radius: 8px !important;
            outline: none !important;
        }

        .physical-stock-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 10px;
        }

        .physical-stock-print-area {
            background: #fff;
            color: #000;
            padding: 10px;
            overflow-x: auto;
        }

        .physical-stock-header-img {
            width: 100%;
            height: 130px;
            margin-bottom: 8px;
            text-align: center;
        }

        .physical-stock-header-img img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }

        .physical-stock-group {
            margin-bottom: 22px;
        }

        .physical-stock-title {
            text-align: center;
            font-weight: 900;
            color: #000;
            font-size: 18px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .physical-stock-subtitle {
            text-align: center;
            font-weight: 800;
            color: #000;
            font-size: 13px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .physical-stock-table {
            width: 100%;
            border-collapse: collapse;
            color: #000;
            background: #fff;
            font-family: Arial, sans-serif;
            font-size: 12px;
            table-layout: fixed;
        }

        .physical-stock-table th,
        .physical-stock-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            line-height: 1.15;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .physical-stock-table th {
            background: #fff !important;
            color: #000 !important;
            text-align: left;
            font-weight: 700;
        }

        .physical-stock-table td.num,
        .physical-stock-table th.num {
            text-align: right;
        }

        .physical-stock-table .highlight-cell {
            background: #00aeef !important;
            color: #000 !important;
        }

        .physical-stock-table tfoot td {
            font-weight: 800;
            background: #fff;
        }

        .physical-grand-title {
            text-align: right;
            font-weight: 900;
            color: #000;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .physical-stock-print-area {
                overflow: visible !important;
                padding: 0 !important;
            }

            .physical-stock-header-img {
                width: 100% !important;
                height: 100px !important;
                margin-bottom: 5px !important;
            }

            .physical-stock-header-img img {
                width: 100% !important;
                height: 100% !important;
                object-fit: fill !important;
                display: block !important;
            }

            .physical-stock-title {
                font-size: 12px !important;
                margin-bottom: 2px !important;
            }

            .physical-stock-subtitle {
                font-size: 10px !important;
                margin-bottom: 3px !important;
            }

            .physical-stock-table {
                width: 100% !important;
                font-size: 7.5px !important;
                table-layout: fixed !important;
                page-break-inside: auto !important;
            }

            .physical-stock-table thead {
                display: table-header-group !important;
            }

            .physical-stock-table tfoot {
                display: table-footer-group !important;
            }

            .physical-stock-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .physical-stock-table th,
            .physical-stock-table td {
                border: 1px solid #000 !important;
                padding: 2px 3px !important;
                line-height: 1.05 !important;
            }

            .physical-stock-group {
                page-break-after: always;
                break-after: page;
                margin-bottom: 8px !important;
            }

            .physical-stock-group:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            @page {
                size: A4 landscape;
                margin: 5mm;
            }
        }
    </style>

    <div class="wrapper wrapper-content animated fadeInRight erp-shell">

        <div class="row no-print">
            <div class="col-lg-8">
                <div class="erp-hero">
                    <h2 class="erp-title">
                        <i class="fa fa-database text-primary"></i>
                        Inventory & Warehouse
                    </h2>
                    <p class="erp-subtitle">
                        Real stock movement dashboard: opening stock, goods received, stock issued, closing balance,
                        low stock alerts, ledger and reports.
                    </p>

                    <ol class="breadcrumb" style="margin-top:12px;margin-bottom:0;background:transparent;padding-left:0;">
                        <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                        <li class="active"><strong>Stock Management</strong></li>
                    </ol>

                    <div class="inventory-note" style="margin-top:15px;margin-bottom:0;">
                        Stock Formula: Opening Stock + Received Stock - Issued Stock = Closing Stock
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="erp-hero" style="min-height:126px;">
                    <div class="erp-date-chip">
                        <i class="fa fa-calendar"></i>
                        {{ \Carbon\Carbon::now()->format('l, Y-m-d') }}
                    </div>

                    <div style="margin-top:10px;color:#64748b;font-weight:700;">
                        Current Inventory Dashboard Session
                    </div>

                    <div class="erp-actions" style="justify-content:flex-start;">
                        <a href="#receiveTab" data-toggle="tab" class="btn btn-success erp-action-btn">
                            <i class="fa fa-download"></i> Receive Stock
                        </a>

                        <a href="{{ $routeUrl('stock.management.stock.out', '#ledgerTab') }}"
                            class="btn btn-danger erp-action-btn">
                            <i class="fa fa-minus-circle"></i> Stock Out
                        </a>

                        <a href="#adjustTab" data-toggle="tab" class="btn btn-warning erp-action-btn">
                            <i class="fa fa-exchange"></i> Adjust Stock
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="no-print">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-top:8px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="row no-print">
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
                            <h5>OPENING STOCK</h5>
                            <h1>{{ number_format($openingStock, 2) }}</h1>
                            <small>Warehouse opening balance</small>
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
                            <small>Items received into store</small>
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
                            <small>Items issued / sold</small>
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
                            <h5>CLOSING STOCK</h5>
                            <h1>{{ number_format($closingStock, 2) }}</h1>
                            <small>Balance available now</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-mini no-print">
            <div class="row">
                <div class="col-md-3">
                    <h4>{{ number_format($totalProducts) }}</h4>
                    <p>Total Products</p>
                </div>
                <div class="col-md-3">
                    <h4>{{ number_format($totalStock, 2) }}</h4>
                    <p>Total Stock</p>
                </div>
                <div class="col-md-3">
                    <h4 class="text-danger">{{ number_format($lowStockCount) }}</h4>
                    <p>Low Stock Items</p>
                </div>
                <div class="col-md-3">
                    <h4>{{ number_format($totalItems) }}</h4>
                    <p>Stock Summary Rows</p>
                </div>
            </div>
        </div>

        <div class="erp-filter no-print">
            <div class="erp-filter-head">
                <i class="fa fa-filter"></i> Inventory Filters
            </div>
            <div class="erp-filter-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="erp-label">From Date</label>
                            <input type="date" name="from" class="form-control erp-input"
                                value="{{ request('from') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="erp-label">To Date</label>
                            <input type="date" name="to" class="form-control erp-input"
                                value="{{ request('to') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="erp-label">Product</label>
                            <select name="product_id" class="form-control select2_demo_1">
                                <option value="">All Products</option>
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}"
                                        {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="erp-label">&nbsp;</label>
                            <button class="btn btn-primary btn-block erp-btn">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="erp-box">
            <div class="ibox-title no-print" style="padding:0 18px;">
                <ul class="nav nav-tabs" style="border-bottom:none;">
                    <li>
                        <a data-toggle="tab" href="#overview">
                            <i class="fa fa-home"></i> Inventory Report
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#receiveTab">
                            <i class="fa fa-download"></i> Receive
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#adjustTab">
                            <i class="fa fa-exchange"></i> Adjust
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#transferTab">
                            <i class="fa fa-random"></i> Transfer
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#movementTab">
                            <i class="fa fa-exchange"></i> Movement
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#ledgerTab">
                            <i class="fa fa-book"></i> Ledger
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#lowStockTab">
                            <i class="fa fa-warning"></i> Low Stock
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#adjustmentsTab">
                            <i class="fa fa-history"></i> Adjustments
                        </a>
                    </li>
                </ul>
            </div>

            <div class="ibox-content">
                <div class="tab-content">

                    <div id="overview" class="tab-pane">
                        <div class="physical-stock-toolbar no-print">
                            <button type="button" onclick="printPhysicalStockOverview()"
                                class="btn btn-primary erp-btn">
                                <i class="fa fa-print"></i> Print Overview
                            </button>
                        </div>

                        <div id="physicalStockPrintArea" class="physical-stock-print-area">
                            <div class="physical-stock-header-img">
                                <img src="{{ asset('img/header.png') }}" alt="Company Header">
                            </div>

                            @forelse($physicalStockGroups as $groupRows)
                                @php
                                    $firstRow = $groupRows->first();

                                    $groupCompanyTitle = trim(
                                        ($firstRow['company_name'] ?? 'NO COMPANY SITE') .
                                            ' ' .
                                            ($firstRow['company_code'] ?? ''
                                                ? '(' . $firstRow['company_code'] . ')'
                                                : ''),
                                    );
                                    $groupUnitTitle = trim(
                                        ($firstRow['unit_name'] ?? 'NO COMPANY UNIT') .
                                            ' ' .
                                            ($firstRow['unit_code'] ?? '' ? '(' . $firstRow['unit_code'] . ')' : ''),
                                    );

                                    $groupTotals = [
                                        'opening' => $groupRows->sum('opening'),
                                        'received' => $groupRows->sum('received'),
                                        'total_stock' => $groupRows->sum('total_stock'),
                                        'issued' => $groupRows->sum('issued'),
                                        'closing' => $groupRows->sum('closing'),
                                        'value' => $groupRows->sum('value'),
                                    ];
                                @endphp

                                <div class="physical-stock-group">
                                    <div class="physical-stock-title">
                                        {{ strtoupper($groupCompanyTitle) }} PHYSICAL STOCK AS AT
                                        {{ $printAsAtDateFormatted }}
                                    </div>

                                    <div class="physical-stock-subtitle">
                                        COMPANY UNIT: {{ strtoupper($groupUnitTitle) }}
                                    </div>

                                    <table class="physical-stock-table">
                                        <thead>
                                            <tr>
                                                <th style="width:4%;">S/NO</th>
                                                <th style="width:15%;">PRODUCT</th>
                                                <th style="width:12%;">DETAILS</th>
                                                <th style="width:6%;">UM</th>
                                                <th class="num" style="width:8%;">OPN -STK</th>
                                                <th class="num" style="width:9%;">RECEIVED<br>STOCK</th>
                                                <th class="num" style="width:9%;">TOTAL STOCK</th>
                                                <th class="num" style="width:9%;">ISSUED STOCK</th>
                                                <th class="num" style="width:9%;">CLOSING<br>STOCK</th>
                                                <th class="num" style="width:8%;">PRICE</th>
                                                <th class="num" style="width:11%;">VALUE/OWING</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($groupRows as $index => $row)
                                                <tr>
                                                    <td class="num">{{ $index + 1 }}</td>
                                                    <td>{{ strtoupper($row['product']) }}</td>
                                                    <td>{{ strtoupper(trim((string) ($row['details'] ?? '')) !== '' ? $row['details'] : '-') }}
                                                    </td>
                                                    <td>{{ strtoupper(trim((string) ($row['um'] ?? '')) !== '' ? $row['um'] : 'PCS') }}
                                                    </td>
                                                    <td class="num">{{ $stockNum($row['opening']) }}</td>
                                                    <td class="num">{{ $stockNum($row['received']) }}</td>
                                                    <td class="num">{{ $stockNum($row['total_stock']) }}</td>
                                                    <td class="num">{{ $stockNum($row['issued'], true) }}</td>
                                                    <td
                                                        class="num {{ (float) $row['closing'] > 0 && (float) $row['closing'] < 100 ? 'highlight-cell' : '' }}">
                                                        {{ $stockNum($row['closing']) }}
                                                    </td>
                                                    <td class="num">{{ $stockNum($row['price']) }}</td>
                                                    <td class="num">{{ number_format((float) $row['value'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td class="num">{{ $stockNum($groupTotals['opening']) }}</td>
                                                <td class="num">{{ $stockNum($groupTotals['received']) }}</td>
                                                <td class="num">{{ $stockNum($groupTotals['total_stock']) }}</td>
                                                <td class="num">{{ $stockNum($groupTotals['issued'], true) }}</td>
                                                <td class="num">{{ $stockNum($groupTotals['closing']) }}</td>
                                                <td class="num"></td>
                                                <td class="num">{{ number_format((float) $groupTotals['value'], 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @empty
                                <div class="physical-stock-title">
                                    PHYSICAL STOCK AS AT {{ $printAsAtDateFormatted }}
                                </div>

                                <table class="physical-stock-table">
                                    <tbody>
                                        <tr>
                                            <td style="text-align:center;font-weight:800;color:#b91c1c;">
                                                NO PHYSICAL STOCK DATA FOUND
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endforelse

                            @if ($physicalStockRows->count() > 0)
                                <div class="physical-grand-title">GRAND TOTAL</div>
                                <table class="physical-stock-table">
                                    <tfoot>
                                        <tr>
                                            <td colspan="4">GRAND TOTAL</td>
                                            <td class="num">{{ $stockNum($physicalGrandTotals['opening']) }}</td>
                                            <td class="num">{{ $stockNum($physicalGrandTotals['received']) }}</td>
                                            <td class="num">{{ $stockNum($physicalGrandTotals['total_stock']) }}</td>
                                            <td class="num">{{ $stockNum($physicalGrandTotals['issued'], true) }}</td>
                                            <td class="num">{{ $stockNum($physicalGrandTotals['closing']) }}</td>
                                            <td class="num"></td>
                                            <td class="num">
                                                {{ number_format((float) $physicalGrandTotals['value'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @endif
                        </div>
                    </div>

                    <div id="receiveTab" class="tab-pane no-print">
                        <div class="erp-filter">
                            <div class="erp-filter-head">
                                <i class="fa fa-download"></i> Receive Stock
                            </div>
                            <div class="erp-filter-body">
                                <form method="POST" action="{{ $receivePostRoute }}">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="erp-label">Product</label>
                                            <select name="product_id" class="form-control select2_demo_1 erp-input"
                                                required>
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ encrypt($product->id) }}"
                                                        {{ old('product_id') == encrypt($product->id) ? 'selected' : '' }}>
                                                        {{ $product->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="erp-label">Quantity</label>
                                            <input type="number" step="0.01" min="0.01" name="qty"
                                                class="form-control erp-input" value="{{ old('qty') }}" required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="erp-label">Unit Cost</label>
                                            <input type="number" step="0.01" min="0" name="unit_cost"
                                                class="form-control erp-input" value="{{ old('unit_cost') }}" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="erp-label">Description</label>
                                            <input type="text" name="description" class="form-control erp-input"
                                                value="{{ old('description') }}" placeholder="Stock receipt description">
                                        </div>
                                    </div>

                                    <div class="row" style="margin-top:10px;">
                                        <div class="col-md-12 text-right">
                                            <button class="btn btn-success erp-btn">
                                                <i class="fa fa-download"></i> Receive Stock
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="receiveTable"
                                class="table table-striped table-bordered table-hover erp-data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Qty In</th>
                                        <th>Unit Cost</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($receiveRows as $row)
                                        <tr>
                                            <td>{{ $fmt($row->date ?? ($row->created_at ?? null)) }}</td>
                                            <td>{{ optional($row->product)->product_name ?? ($row->product_name ?? 'N/A') }}
                                            </td>
                                            <td>{{ number_format((float) ($row->qty_in ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($row->unit_cost ?? 0), 2) }}</td>
                                            <td>{{ $row->description ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="erp-empty">No Receive Data Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="adjustTab" class="tab-pane no-print">
                        <div class="erp-filter">
                            <div class="erp-filter-head">
                                <i class="fa fa-exchange"></i> Stock Adjustment
                            </div>
                            <div class="erp-filter-body">
                                <form method="POST" action="{{ $adjustPostRoute }}">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="erp-label">Product</label>
                                            <select name="product_id" class="form-control select2_demo_1 erp-input"
                                                required>
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ encrypt($product->id) }}"
                                                        {{ old('product_id') == encrypt($product->id) ? 'selected' : '' }}>
                                                        {{ $product->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="erp-label">Type</label>
                                            <select name="type" class="form-control erp-input" required>
                                                <option value="increase"
                                                    {{ old('type') === 'increase' ? 'selected' : '' }}>Increase</option>
                                                <option value="decrease"
                                                    {{ old('type') === 'decrease' ? 'selected' : '' }}>Decrease</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="erp-label">Quantity</label>
                                            <input type="number" step="0.01" min="0.01" name="qty"
                                                class="form-control erp-input" value="{{ old('qty') }}" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="erp-label">Reason</label>
                                            <input type="text" name="reason" class="form-control erp-input"
                                                value="{{ old('reason') }}" placeholder="Enter adjustment reason"
                                                required>
                                        </div>
                                    </div>

                                    <div class="row" style="margin-top:10px;">
                                        <div class="col-md-12 text-right">
                                            <button class="btn btn-warning erp-btn">
                                                <i class="fa fa-save"></i> Save Adjustment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="transferTab" class="tab-pane no-print">
                        <div class="erp-filter">
                            <div class="erp-filter-head">
                                <i class="fa fa-random"></i> Transfer Stock
                            </div>
                            <div class="erp-filter-body">
                                <form method="POST" action="{{ $transferPostRoute }}">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="erp-label">Product</label>
                                            <select name="product_id" class="form-control select2_demo_1 erp-input"
                                                required>
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ encrypt($product->id) }}">
                                                        {{ $product->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="erp-label">From Work Point</label>
                                            <select name="from_work_point" class="form-control select2_demo_1 erp-input"
                                                required>
                                                <option value="">Select Source</option>
                                                @foreach ($workPoints as $wp)
                                                    <option value="{{ encrypt($wp->id) }}">
                                                        {{ $wp->work_code ? $wp->work_code . ' - ' : '' }}{{ $wp->work_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="erp-label">To Work Point</label>
                                            <select name="to_work_point" class="form-control select2_demo_1 erp-input"
                                                required>
                                                <option value="">Select Destination</option>
                                                @foreach ($workPoints as $wp)
                                                    <option value="{{ encrypt($wp->id) }}">
                                                        {{ $wp->work_code ? $wp->work_code . ' - ' : '' }}{{ $wp->work_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="erp-label">Quantity</label>
                                            <input type="number" step="0.01" min="0.01" name="qty"
                                                class="form-control erp-input" required>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="erp-label">&nbsp;</label>
                                            <button class="btn btn-info btn-block erp-btn">
                                                <i class="fa fa-save"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="movementTab" class="tab-pane no-print">
                        <div class="erp-filter">
                            <div class="erp-filter-head">
                                <i class="fa fa-exchange"></i> Manual Stock Movement
                            </div>
                            <div class="erp-filter-body">
                                <form method="POST" action="{{ $movementPostRoute }}">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="erp-label">Product</label>
                                            <select name="product_id" class="form-control select2_demo_1 erp-input"
                                                required>
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ encrypt($product->id) }}">
                                                        {{ $product->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="erp-label">Type</label>
                                            <select name="type" class="form-control erp-input" required>
                                                <option value="in">In</option>
                                                <option value="out">Out</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="erp-label">Quantity</label>
                                            <input type="number" step="0.01" min="0.01" name="quantity"
                                                class="form-control erp-input" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="erp-label">Description</label>
                                            <input type="text" name="description" class="form-control erp-input">
                                        </div>
                                    </div>

                                    <div class="row" style="margin-top:10px;">
                                        <div class="col-md-12 text-right">
                                            <button class="btn btn-primary erp-btn">
                                                <i class="fa fa-save"></i> Save Movement
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="ledgerTab" class="tab-pane no-print">
                        <h4 class="erp-section-title">Stock Ledger</h4>
                        <p class="erp-subtitle-2">All stock ledger movements.</p>

                        <div class="table-responsive">
                            <table id="ledgerTable" class="table table-striped table-bordered table-hover erp-data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Qty In</th>
                                        <th>Qty Out</th>
                                        <th>Balance</th>
                                        <th>Reference</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rows as $row)
                                        <tr>
                                            <td>{{ $fmt($row->date ?? ($row->created_at ?? null)) }}</td>
                                            <td>{{ optional($row->product)->product_name ?? ($row->product_name ?? 'N/A') }}
                                            </td>
                                            <td>{{ $row->type ?? '-' }}</td>
                                            <td>{{ number_format((float) ($row->qty_in ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($row->qty_out ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($row->balance ?? 0), 2) }}</td>
                                            <td>{{ $row->reference_type ?? '-' }}</td>
                                            <td>{{ $row->description ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="erp-empty">No Ledger Data Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="lowStockTab" class="tab-pane no-print">
                        <h4 class="erp-section-title">Low Stock Products</h4>
                        <p class="erp-subtitle-2">Products at or below reorder level.</p>

                        <div class="table-responsive">
                            <table id="lowStockTable"
                                class="table table-striped table-bordered table-hover erp-data-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Current Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lowStockProducts as $product)
                                        @php
                                            $current =
                                                (float) (optional($product->stock)->current_stock ??
                                                    ($product->current_stock ?? ($product->total_qty ?? 0)));
                                            $reorder = (float) ($product->reorder_level ?? 10);
                                        @endphp
                                        <tr>
                                            <td>{{ $product->product_name ?? 'N/A' }}</td>
                                            <td>{{ number_format($current, 2) }}</td>
                                            <td>{{ number_format($reorder, 2) }}</td>
                                            <td>
                                                <span class="badge-bad">Low Stock</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="erp-empty">No Low Stock Products Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="adjustmentsTab" class="tab-pane no-print">
                        <h4 class="erp-section-title">Adjustment History</h4>
                        <p class="erp-subtitle-2">Stock increase and decrease records.</p>

                        <div class="table-responsive">
                            <table id="adjustmentsTable"
                                class="table table-striped table-bordered table-hover erp-data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($adjustments as $adj)
                                        <tr>
                                            <td>{{ $fmt($adj->created_at ?? null) }}</td>
                                            <td>{{ optional($adj->product)->product_name ?? 'N/A' }}</td>
                                            <td>
                                                @if (($adj->type ?? '') === 'increase')
                                                    <span class="badge-good">Increase</span>
                                                @else
                                                    <span class="badge-bad">Decrease</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format((float) ($adj->qty ?? 0), 2) }}</td>
                                            <td>{{ $adj->reason ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="erp-empty">No Adjustment Data Found</td>
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
        function printPhysicalStockOverview() {
            var printArea = document.getElementById('physicalStockPrintArea');

            if (!printArea) {
                alert('Nothing to print');
                return;
            }

            var printWindow = window.open('', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');

            if (!printWindow) {
                alert('Please allow popups to print this report.');
                return;
            }

            var printContent = printArea.cloneNode(true);

            var printStyles = `
            <style>
                @page {
                    size: A4 landscape;
                    margin: 5mm;
                }

                html,
                body {
                    margin: 0;
                    padding: 0;
                    background: #fff;
                    color: #000;
                    font-family: Arial, sans-serif;
                    overflow: auto;
                }

                * {
                    box-sizing: border-box;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                .physical-stock-print-area {
                    width: 100%;
                    background: #fff;
                    color: #000;
                    padding: 0;
                    margin: 0;
                    overflow: visible;
                }

                .physical-stock-header-img {
                    width: 100%;
                    height: 100px;
                    margin-bottom: 5px;
                    text-align: center;
                }

                .physical-stock-header-img img {
                    width: 100%;
                    height: 100%;
                    object-fit: fill;
                    display: block;
                }

                .physical-stock-group {
                    width: 100%;
                    margin-bottom: 8px;
                    page-break-after: always;
                    break-after: page;
                }

                .physical-stock-group:last-child {
                    page-break-after: auto;
                    break-after: auto;
                }

                .physical-stock-title {
                    text-align: center;
                    font-weight: 900;
                    color: #000;
                    font-size: 12px;
                    margin-bottom: 2px;
                    text-transform: uppercase;
                }

                .physical-stock-subtitle {
                    text-align: center;
                    font-weight: 800;
                    color: #000;
                    font-size: 10px;
                    margin-bottom: 3px;
                    text-transform: uppercase;
                }

                .physical-stock-table {
                    width: 100%;
                    border-collapse: collapse;
                    color: #000;
                    background: #fff;
                    font-family: Arial, sans-serif;
                    font-size: 7.5px;
                    table-layout: fixed;
                    page-break-inside: auto;
                }

                .physical-stock-table thead {
                    display: table-header-group;
                }

                .physical-stock-table tfoot {
                    display: table-footer-group;
                }

                .physical-stock-table tr {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                .physical-stock-table th,
                .physical-stock-table td {
                    border: 1px solid #000;
                    padding: 2px 3px;
                    line-height: 1.05;
                    vertical-align: middle;
                    word-wrap: break-word;
                }

                .physical-stock-table th {
                    background: #fff;
                    color: #000;
                    text-align: left;
                    font-weight: 700;
                }

                .physical-stock-table td.num,
                .physical-stock-table th.num {
                    text-align: right;
                }

                .physical-stock-table .highlight-cell {
                    background: #00aeef !important;
                    color: #000 !important;
                }

                .physical-stock-table tfoot td {
                    font-weight: 800;
                    background: #fff;
                }

                .physical-grand-title {
                    text-align: right;
                    font-weight: 900;
                    color: #000;
                    margin-top: 10px;
                    margin-bottom: 5px;
                    font-size: 10px;
                }

                .no-print,
                .physical-stock-toolbar {
                    display: none !important;
                }
            </style>
        `;

            printWindow.document.open();
            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Physical Stock Report</title>
                    ${printStyles}
                </head>
                <body>
                    ${printContent.outerHTML}
                </body>
            </html>
        `);
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();

                setTimeout(function() {
                    printWindow.print();
                }, 700);
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('.select2_demo_1, .select3, .select3_demo_1').each(function() {
                    var $select = window.jQuery(this);

                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }

                    $select.select2({
                        width: '100%',
                        dropdownAutoWidth: false
                    });
                });

                window.jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function() {
                    window.jQuery('.select2_demo_1, .select3, .select3_demo_1').each(function() {
                        window.jQuery(this).next('.select2-container').css('width', '100%');
                    });

                    if (window.jQuery.fn.DataTable) {
                        window.jQuery.fn.dataTable.tables({
                            visible: true,
                            api: true
                        }).columns.adjust();
                    }
                });
            }

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                window.jQuery('.erp-data-table').each(function() {
                    if (window.jQuery.fn.DataTable.isDataTable(this)) {
                        window.jQuery(this).DataTable().destroy();
                    }

                    window.jQuery(this).DataTable({
                        pageLength: 25,
                        autoWidth: false,
                        responsive: true,
                        retrieve: true
                    });
                });
            }
        });
    </script>
@endsection
