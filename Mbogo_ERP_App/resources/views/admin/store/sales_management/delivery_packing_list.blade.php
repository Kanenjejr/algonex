@extends('layouts.salesMaster')

@section('content')
    @php
        $company =
            $delivery->company ?: optional($delivery->invoice)->company ?: optional($delivery->proforma)->company;
        $customer =
            $delivery->customer ?: optional($delivery->invoice)->customer ?: optional($delivery->proforma)->customer;
        $creator = optional($delivery->creator);
        $approver = optional($delivery->approver);

        $companyName = optional($company)->company_name ?? 'MBOGO MINING AND GENERAL SUPPLY LTD';
        $transporter = $delivery->transporter_name ?: $companyName;
        $pointOfLoad = $delivery->origin ?: '-';
        $pointOfDelivery = $delivery->destination ?: '-';
        $exportReferenceNo = $delivery->export_reference_no ?: $delivery->delivery_no;
        $itemCount = max(1, $delivery->items->count());
        $densityClass = $itemCount <= 5 ? 'density-few' : ($itemCount <= 12 ? 'density-medium' : 'density-many');

        $signature = optional($company)->signature;
        $stamp = optional($company)->stamp;

        $safeAsset = function ($path) {
            if (!$path) {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            if (\Illuminate\Support\Str::startsWith($path, ['/'])) {
                return asset(ltrim($path, '/'));
            }

            if (\Illuminate\Support\Str::startsWith($path, ['storage/', 'img/'])) {
                return asset($path);
            }

            return asset($path);
        };

        $signaturePath = $safeAsset($signature);
        $stampPath = $safeAsset($stamp);

        $fmtQty = function ($value) {
            $formatted = number_format((float) $value, 4, '.', ',');
            return rtrim(rtrim($formatted, '0'), '.') ?: '0';
        };

        $grossWeightText = $delivery->total_gross_weight;
        if (!$grossWeightText) {
            $grossWeightText = $delivery->items->pluck('gross_weight')->filter()->implode(' + ');
        }
        if (!$grossWeightText) {
            $grossWeightText = '-';
        }

        $exporterAddress = trim(
            ($companyName ?: 'MBOGO MINING AND GENERAL SUPPLY LTD') .
                "\n" .
                (optional($company)->address ?: 'P.O BOX 6369') .
                "\n" .
                trim((optional($company)->district ?: '') . ' ' . (optional($company)->city ?: 'MWANZA')) .
                "\n" .
                'TANZANIA.',
        );

        $consigneeAddress = trim(
            (optional($customer)->customer_name ?: '-') .
                "\n" .
                (optional($customer)->address ?: '-') .
                (optional($customer)->phone ? "\nTel; " . optional($customer)->phone : ''),
        );
    @endphp

    <style>
        .packing-list-page {
            width: 100%;
            display: block;
            background: #f3f3f4;
            padding: 14px 0 24px 0;
            box-sizing: border-box;
        }

        .packing-paper {
            width: 198mm;
            max-width: 198mm;
            min-height: 287mm;
            margin: 0 auto;
            background: #fff;
            color: #000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.28;
            padding: 7mm;
            box-sizing: border-box;
            box-shadow: 0 0 8px rgba(0, 0, 0, .12);
            overflow: visible;
        }

        .packing-paper.density-few {
            font-size: 13px;
            line-height: 1.35;
        }

        .packing-paper.density-medium {
            font-size: 11.5px;
            line-height: 1.22;
        }

        .packing-paper.density-many {
            font-size: 9.2px;
            line-height: 1.08;
        }

        .doc-actions {
            text-align: right;
            margin-bottom: 8px;
        }

        .company-header-box {
            width: 100%;
            height: 36mm;
            margin: 0 0 5px 0;
            overflow: hidden;
        }

        .company-header-img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }

        .doc-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            margin: 6px 0 12px 0;
            color: #000;
        }

        .packing-paper.density-many .doc-title {
            font-size: 16px;
            margin: 4px 0 7px 0;
        }

        .mini-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .mini-table th,
        .mini-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .no-border td,
        .no-border th {
            border: none !important;
        }

        .party-table td {
            vertical-align: top;
        }

        .party-title {
            font-weight: bold;
            text-decoration: underline;
            font-size: 13px;
            margin-bottom: 5px;
            color: #000;
        }

        .party-text {
            white-space: pre-line;
            font-size: 13px;
            line-height: 1.34;
            color: #000;
        }

        .packing-paper.density-medium .party-text,
        .packing-paper.density-medium .party-title {
            font-size: 11px;
            line-height: 1.18;
        }

        .packing-paper.density-many .party-text,
        .packing-paper.density-many .party-title {
            font-size: 9px;
            line-height: 1.06;
        }

        .item-table th,
        .item-table td {
            padding: 7px 8px;
            color: #000;
        }

        .packing-paper.density-few .item-table td {
            height: 15mm;
            font-size: 13px;
        }

        .packing-paper.density-medium .item-table th,
        .packing-paper.density-medium .item-table td {
            padding: 4px 5px;
        }

        .packing-paper.density-many .item-table th,
        .packing-paper.density-many .item-table td {
            padding: 2px 3px;
            font-size: 8.5px;
            line-height: 1.05;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .transport-table td {
            padding: 4px 6px;
            border: none !important;
            color: #000;
        }

        .transport-label {
            width: 36%;
            font-weight: bold;
            white-space: nowrap;
        }

        .transport-value {
            width: 64%;
        }

        .signature-img,
        .stamp-img {
            max-height: 60px;
            max-width: 145px;
            object-fit: contain;
        }

        .footer-note {
            text-align: center;
            font-size: 9px;
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #d9d9d9;
            page-break-inside: avoid;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm;
            }

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                background: #fff !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body * {
                visibility: hidden !important;
            }

            #printArea,
            #printArea * {
                visibility: visible !important;
            }

            .no-print,
            .navbar,
            .navbar-static-top,
            .navbar-static-side,
            .minimalize-styl-2,
            .footer,
            .footer.fixed,
            .fixed-footer,
            #footer,
            .page-heading {
                display: none !important;
                visibility: hidden !important;
            }

            #wrapper,
            #page-wrapper,
            .wrapper,
            .wrapper-content,
            .gray-bg,
            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                overflow: visible !important;
                background: #fff !important;
            }

            .packing-list-page {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                width: 100% !important;
            }

            #printArea {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 200mm !important;
                max-width: 200mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                background: #fff !important;
            }

            .packing-paper {
                width: 200mm !important;
                max-width: 200mm !important;
                min-height: 287mm !important;
                padding: 3.5mm !important;
                font-size: 10.5px !important;
                line-height: 1.15 !important;
            }

            .packing-paper.density-few {
                font-size: 11px !important;
                line-height: 1.22 !important;
            }

            .packing-paper.density-many {
                font-size: 7.7px !important;
                line-height: 1.02 !important;
            }

            .company-header-box {
                width: 100% !important;
                height: 30mm !important;
                margin-bottom: 4px !important;
            }

            .company-header-img {
                width: 100% !important;
                height: 100% !important;
                object-fit: fill !important;
            }

            .packing-paper.density-many .company-header-box {
                height: 22mm !important;
            }

            .doc-title {
                font-size: 17px !important;
                margin: 4px 0 8px 0 !important;
            }

            .packing-paper.density-many .doc-title {
                font-size: 14px !important;
                margin: 2px 0 4px 0 !important;
            }

            .mini-table th,
            .mini-table td {
                padding: 3px 4px !important;
            }

            .packing-paper.density-many .mini-table th,
            .packing-paper.density-many .mini-table td {
                padding: 1.6px 2px !important;
            }

            .packing-paper.density-many .party-text,
            .packing-paper.density-many .party-title {
                font-size: 7.8px !important;
                line-height: 1.0 !important;
            }

            table,
            tr,
            td,
            th,
            .footer-note {
                page-break-inside: avoid !important;
            }
        }
    </style>

    <div class="packing-list-page wrapper wrapper-content">
        <div class="packing-paper {{ $densityClass }}" id="printArea">
            <div class="doc-actions no-print">
                <a href="{{ route('sales.deliveries') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('sales.delivery.note', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-info" target="_blank">Delivery Note</a>
                <a href="{{ route('sales.delivery.customs.manifest', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-warning" target="_blank">Customs Manifest</a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>

            <div class="company-header-box">
                <img src="{{ asset('img/header.png') }}" alt="Company Header" class="company-header-img">
            </div>

            <div class="doc-title">PACKING LIST</div>

            <table class="mini-table no-border" style="margin-bottom:8px;">
                <tr>
                    <td style="width:70%;"></td>
                    <td style="width:30%; text-align:right; font-size:13px;">
                        <strong>Date:</strong>
                        {{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('d. m.Y') : now()->format('d. m.Y') }}
                    </td>
                </tr>
            </table>

            <table class="mini-table no-border party-table" style="margin-bottom:14px;">
                <tr>
                    <td style="width:50%; padding-right:18px;">
                        <div class="party-title">EXPORTER:</div>
                        <div class="party-text">{{ $exporterAddress }}</div>
                    </td>
                    <td style="width:50%; padding-left:18px;">
                        <div class="party-title text-center">CONSIGNEE</div>
                        <div class="party-text" style="width:78%; margin:0 auto; text-align:left;">{{ $consigneeAddress }}
                        </div>
                    </td>
                </tr>
            </table>

            <div class="text-center" style="font-weight:bold; font-size:15px; margin:12px 0 12px 0;">
                Export Reference No: {{ $exportReferenceNo }}
            </div>

            <table class="mini-table item-table">
                <thead>
                    <tr>
                        <th style="width:38%;">Product Description</th>
                        <th style="width:17%;">Quantity</th>
                        <th style="width:23%;">Number of boxes</th>
                        <th style="width:22%;">Gross Product<br>Weight</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($delivery->items as $item)
                        <tr>
                            <td>{{ $item->item_name ?? (optional($item->product)->product_name ?? '-') }}</td>
                            <td class="text-center">{{ $fmtQty($item->quantity) }} {{ $item->unit ?? '' }}</td>
                            <td class="text-center">
                                {{ $item->packages_no_type ?: $fmtQty($item->quantity) . ' ' . ($item->unit ?? '') }}
                            </td>
                            <td class="text-center">{{ $item->gross_weight ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="mini-table no-border transport-table" style="margin-top:12px;">
                <tr>
                    <td class="transport-label">TOTAL GROSS PRODUCT WEIGHT</td>
                    <td class="transport-value">: {{ $grossWeightText }}</td>
                </tr>
                <tr>
                    <td class="transport-label">MODE OF TRANSPORT</td>
                    <td class="transport-value">: {{ strtoupper($delivery->transport_mode ?? 'ROAD') }}</td>
                </tr>
                <tr>
                    <td class="transport-label">TRANSPORTER</td>
                    <td class="transport-value">: {{ $transporter }}</td>
                </tr>
                <tr>
                    <td class="transport-label">POINT OF LOAD</td>
                    <td class="transport-value">: {{ $pointOfLoad }}</td>
                </tr>
                <tr>
                    <td class="transport-label">POINT OF DELIVERY</td>
                    <td class="transport-value">: {{ $pointOfDelivery }}</td>
                </tr>
                <tr>
                    <td class="transport-label">TRUCK REGISTRATION NUMBER</td>
                    <td class="transport-value">: TRUCK; {{ $delivery->vehicle_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="transport-label">DRIVERS NAME</td>
                    <td class="transport-value">: {{ $delivery->driver_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="transport-label">DRIVERS MOBILE.</td>
                    <td class="transport-value">: {{ $delivery->driver_phone ?? '-' }}</td>
                </tr>
            </table>

            <table class="mini-table no-border" style="margin-top:16px;">
                <tr>
                    <td style="width:65%;">
                        On behalf of <strong>{{ $companyName }}</strong><br><br>

                        @if ($signaturePath)
                            <img src="{{ $signaturePath }}" class="signature-img" alt="Signature"><br>
                        @else
                            ........................................<br>
                        @endif

                        <strong>{{ optional($creator)->name ?? (optional(auth()->user())->name ?? '-') }}</strong><br>
                        <strong>{{ optional($creator)->position ?? 'SALES MINING ENGINEER' }}</strong>
                    </td>
                    <td style="width:35%; text-align:center; vertical-align:bottom;">
                        @if ($stampPath)
                            <img src="{{ $stampPath }}" class="stamp-img" alt="Company Stamp"><br>
                        @endif
                        <strong>Company Stamp</strong><br>
                        @if ($delivery->approved_at)
                            Approved By: {{ optional($approver)->name ?? '-' }}<br>
                            Approved Date: {{ \Carbon\Carbon::parse($delivery->approved_at)->format('Y-m-d H:i') }}
                        @endif
                    </td>
                </tr>
            </table>

            <div class="footer-note">
                {{ $companyName }} | {{ optional($company)->phone_No ?? '+255756263287' }} |
                {{ optional($company)->email ?? 'info@mbogomining.co.tz' }} |
                {{ optional($company)->website ?? 'www.mbogomining.co.tz' }}
            </div>
        </div>
    </div>
@endsection
