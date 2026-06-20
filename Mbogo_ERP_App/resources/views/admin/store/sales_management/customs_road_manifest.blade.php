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
        $manifestNumber = $delivery->customs_manifest_no ?: $delivery->delivery_no;
        $transporter = $delivery->transporter_name ?: $companyName;
        $invoiceNo =
            optional($delivery->invoice)->invoice_no ?:
            optional($delivery->proforma)->proforma_no ?:
            $delivery->delivery_no;
        $itemCount = max(1, $delivery->items->count());
        $densityClass = $itemCount <= 4 ? 'density-few' : ($itemCount <= 10 ? 'density-medium' : 'density-many');

        $consignor = trim(
            $companyName .
                "\n" .
                (optional($company)->address ?? 'P.O BOX 6369') .
                "\n" .
                (optional($company)->city ?? 'MWANZA') .
                "\nTANZANIA",
        );
        $consignee = trim(
            (optional($customer)->customer_name ?? '-') .
                "\n" .
                (optional($customer)->address ?? '-') .
                (optional($customer)->phone ? "\nTel; " . optional($customer)->phone : ''),
        );

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
    @endphp

    <style>
        .manifest-page {
            width: 100%;
            display: block;
            background: #f3f3f4;
            padding: 14px 0 24px 0;
            box-sizing: border-box;
            overflow-x: auto;
        }

        .manifest-paper {
            width: 198mm;
            max-width: 198mm;
            min-height: 287mm;
            margin: 0 auto;
            background: #fff;
            color: #000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9.2px;
            line-height: 1.15;
            padding: 5mm;
            box-sizing: border-box;
            box-shadow: 0 0 8px rgba(0, 0, 0, .12);
            overflow: visible;
        }

        .manifest-paper.density-few {
            font-size: 9.8px;
            line-height: 1.18;
        }

        .manifest-paper.density-medium {
            font-size: 8px;
            line-height: 1.08;
        }

        .manifest-paper.density-many {
            font-size: 6.3px;
            line-height: 1.0;
        }

        .doc-actions {
            text-align: right;
            margin-bottom: 8px;
        }

        .company-header-box {
            width: 100%;
            height: 34mm;
            margin: 0 0 4px 0;
            overflow: hidden;
        }

        .company-header-img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }

        .manifest-paper.density-many .company-header-box {
            height: 22mm;
        }

        .doc-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 3px 0 5px 0;
            color: #000;
        }

        .manifest-paper.density-few .doc-title {
            font-size: 15px;
        }

        .manifest-paper.density-many .doc-title {
            font-size: 10px;
            margin: 1px 0 2px 0;
        }

        .mini-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .mini-table th,
        .mini-table td {
            border: 1px solid #000;
            padding: 2.4px 3px;
            vertical-align: top;
            color: #000;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .manifest-paper.density-few .mini-table th,
        .manifest-paper.density-few .mini-table td {
            padding: 3px 3.5px;
        }

        .manifest-paper.density-medium .mini-table th,
        .manifest-paper.density-medium .mini-table td {
            padding: 1.8px 2px;
        }

        .manifest-paper.density-many .mini-table th,
        .manifest-paper.density-many .mini-table td {
            padding: 1px 1.2px;
        }

        .header-bar {
            background: #0b1a78 !important;
            color: #fff !important;
            font-weight: bold;
        }

        .header-bar th,
        .header-bar td {
            color: #fff !important;
            background: #0b1a78 !important;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .manifest-items td,
        .manifest-items th {
            word-break: break-word;
            color: #000;
        }

        .customs-box {
            height: 24mm;
            text-align: center;
            font-weight: bold;
            vertical-align: top;
        }

        .signature-img,
        .stamp-img {
            max-height: 48px;
            max-width: 125px;
            object-fit: contain;
        }

        .footer-note {
            text-align: center;
            font-size: 8.5px;
            margin-top: 5px;
            padding-top: 3px;
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

            .manifest-page {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                width: 100% !important;
                overflow: visible !important;
            }

            #printArea {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 200mm !important;
                max-width: 200mm !important;
                min-height: 287mm !important;
                margin: 0 auto !important;
                padding: 2.5mm !important;
                box-shadow: none !important;
                border: none !important;
                background: #fff !important;
            }

            .manifest-paper {
                font-size: 8.3px !important;
                line-height: 1.08 !important;
            }

            .manifest-paper.density-few {
                font-size: 8.8px !important;
                line-height: 1.1 !important;
            }

            .manifest-paper.density-medium {
                font-size: 7.1px !important;
                line-height: 1.0 !important;
            }

            .manifest-paper.density-many {
                font-size: 5.4px !important;
                line-height: 0.92 !important;
            }

            .company-header-box {
                width: 100% !important;
                height: 28mm !important;
                margin-bottom: 3px !important;
            }

            .company-header-img {
                width: 100% !important;
                height: 100% !important;
                object-fit: fill !important;
            }

            .manifest-paper.density-many .company-header-box {
                height: 18mm !important;
            }

            .doc-title {
                font-size: 12px !important;
                margin: 1px 0 3px 0 !important;
            }

            .manifest-paper.density-many .doc-title {
                font-size: 9px !important;
                margin: 1px 0 2px 0 !important;
            }

            .mini-table th,
            .mini-table td {
                padding: 1.8px 2px !important;
            }

            .manifest-paper.density-many .mini-table th,
            .manifest-paper.density-many .mini-table td {
                padding: 0.7px 0.8px !important;
            }

            .signature-img,
            .stamp-img {
                max-height: 36px !important;
                max-width: 100px !important;
            }

            .customs-box {
                height: 18mm !important;
            }

            .footer-note {
                font-size: 6.8px !important;
                margin-top: 2px !important;
                padding-top: 2px !important;
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

    <div class="manifest-page wrapper wrapper-content">
        <div class="manifest-paper {{ $densityClass }}" id="printArea">
            <div class="doc-actions no-print">
                <a href="{{ route('sales.deliveries') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('sales.delivery.note', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-info" target="_blank">Delivery Note</a>
                <a href="{{ route('sales.delivery.packing.list', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-warning" target="_blank">Packing List</a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>

            <div class="company-header-box">
                <img src="{{ asset('img/header.png') }}" alt="Company Header" class="company-header-img">
            </div>

            <div class="doc-title">CUSTOMS ROAD MANIFEST <span style="font-size:10px;">Form No. 1</span></div>

            <table class="mini-table" style="margin-bottom:3px;">
                <tr>
                    <td style="width:16%; font-weight:bold;">MANIFEST NUMBER:</td>
                    <td style="width:24%;">{{ $manifestNumber }}</td>
                    <td style="width:14%; font-weight:bold;">TRANSPORTER</td>
                    <td style="width:46%;">{{ $transporter }}</td>
                </tr>
            </table>

            <table class="mini-table" style="margin-bottom:3px;">
                <tr class="header-bar text-center">
                    <th style="width:18%;"></th>
                    <th>TRUCK 1</th>
                    <th>TRUCK 2</th>
                    <th>TRAILER 2</th>
                </tr>
                <tr>
                    <td style="font-weight:bold;">REGISTRATION NUMBER(S)</td>
                    <td>{{ $delivery->vehicle_no ?? 'N/A' }}</td>
                    <td>{{ $delivery->truck2_registration_no ?? 'N/A' }}</td>
                    <td>{{ $delivery->trailer_registration_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">CONTAINER NUMBER(S)</td>
                    <td>{{ $delivery->container_no ?? 'N/A' }}</td>
                    <td>{{ $delivery->container2_no ?? 'N/A' }}</td>
                    <td>{{ $delivery->container3_no ?? 'N/A' }}</td>
                </tr>
            </table>

            <table class="mini-table manifest-items">
                <thead>
                    <tr class="header-bar text-center">
                        <th style="width:4%;">LINE<br>NO.</th>
                        <th style="width:10%;">COMMERCIAL<br>INV. NO</th>
                        <th style="width:8%;">PKGS NO<br>& TYPE</th>
                        <th style="width:7%;">WEIGHT/<br>MASS</th>
                        <th style="width:17%;">DESCRIPTION OF GOODS</th>
                        <th style="width:14%;">CONSIGNOR</th>
                        <th style="width:14%;">CONSIGNEE</th>
                        <th style="width:8%;">CLEARING<br>AGENT</th>
                        <th style="width:8%;">BILL OF<br>ENTRY NO.</th>
                        <th style="width:10%;">EXIT<br>ENTRY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($delivery->items as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $invoiceNo }}</td>
                            <td>{{ $item->packages_no_type ?: $fmtQty($item->quantity) . ' ' . ($item->unit ?? '') }}
                            </td>
                            <td>{{ $item->gross_weight ?: '-' }}</td>
                            <td>{{ $item->item_name ?? (optional($item->product)->product_name ?? '-') }}</td>
                            <td>{!! nl2br(e($consignor)) !!}</td>
                            <td>{!! nl2br(e($consignee)) !!}</td>
                            <td>{{ $delivery->clearing_agent ?? '-' }}</td>
                            <td>{{ $delivery->bill_of_entry_no ?? '-' }}</td>
                            <td>{{ $delivery->exit_entry_no ?? '-' }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" class="text-right"><strong>TOTAL WEIGHT</strong></td>
                        <td><strong>{{ $grossWeightText }}</strong></td>
                        <td colspan="6"></td>
                    </tr>
                </tbody>
            </table>

            <table class="mini-table" style="margin-top:3px;">
                <tr>
                    <td style="width:48%; height:24mm;">
                        I hereby certify that the particulars shown on this manifest are true reflection of all the goods
                        carried on the above mentioned vehicle(s).<br>

                        @if ($signaturePath)
                            <img src="{{ $signaturePath }}" class="signature-img" alt="Signature"><br>
                        @else
                            ........................................<br>
                        @endif

                        <strong>{{ $delivery->driver_name ?: optional($creator)->name ?? '-' }}</strong><br>
                        Name of Driver / Transporter<br>
                        Tel No. {{ $delivery->driver_phone ?? '-' }}
                    </td>
                    <td style="width:26%;" class="customs-box">
                        FOR CUSTOMS USE<br><br>
                        CUSTOMS STAMP - EXIT<br><br>
                        REPORT NUMBER:
                    </td>
                    <td style="width:26%;" class="customs-box">
                        CUSTOMS STAMP - ENTRY<br><br><br>
                        REPORT NUMBER:
                    </td>
                </tr>
            </table>

            <table class="mini-table" style="margin-top:3px;">
                <tr>
                    <td style="width:50%;">
                        <strong>Prepared By:</strong>
                        {{ optional($creator)->name ?? (optional(auth()->user())->name ?? '-') }}<br>
                        @if ($delivery->approved_at)
                            <strong>Approved By:</strong> {{ optional($approver)->name ?? '-' }}<br>
                            <strong>Approved Date:</strong>
                            {{ \Carbon\Carbon::parse($delivery->approved_at)->format('Y-m-d H:i') }}
                        @endif
                    </td>
                    <td style="width:50%; text-align:center;">
                        @if ($stampPath)
                            <img src="{{ $stampPath }}" class="stamp-img" alt="Company Stamp"><br>
                        @endif
                        <strong>Company Stamp / Approval</strong>
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
