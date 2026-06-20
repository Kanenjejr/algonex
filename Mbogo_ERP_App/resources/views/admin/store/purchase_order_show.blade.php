@extends('layouts.salesMaster')

@section('content')
    @php
        $company = optional($order->company);
        $unit = optional($order->businessUnit);
        $workPoint = optional($order->workPoint);
        $vendor = optional($order->vendor);
        $creator = optional($order->creator);
        $approver = optional($order->approver);

        $signature = $company->signature ?? null;
        $stamp = $company->stamp ?? null;

        $signaturePath = null;
        $stampPath = null;
        $signatureFallbackPath = null;
        $stampFallbackPath = null;

        if ($signature) {
            // Same primary path style as Proforma. Fallback supports old records saved as filename only.
            $signaturePath = asset($signature);
            $signatureFallbackPath = str_contains($signature, '/') ? null : asset('img/' . $signature);
        }

        if ($stamp) {
            // Same primary path style as Proforma. Fallback supports old records saved as filename only.
            $stampPath = asset($stamp);
            $stampFallbackPath = str_contains($stamp, '/') ? null : asset('img/' . $stamp);
        }

        $poFmt4 = function ($value) {
            return number_format(round((float) $value, 2), 2);
        };

        $poFmtTotal = function ($value) {
            return number_format(round((float) $value, 0), 2);
        };

        $poGrossUnitPrice = function ($item) {
            $qty = (float) ($item->qty ?? 0);

            if ($qty > 0) {
                return ((float) ($item->total_price ?? 0)) / $qty;
            }

            return (float) ($item->unit_price ?? 0);
        };

        $poActualUnitPrice = function ($item) {
            $qty = (float) ($item->qty ?? 0);

            if ($qty > 0) {
                return ((float) ($item->sub_total ?? 0)) / $qty;
            }

            return (float) ($item->unit_price ?? 0);
        };
    @endphp

    <style>
        .po-page-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background: #f3f3f4;
            padding: 18px 0 35px 0;
            box-sizing: border-box;
        }

        .po-container {
            width: 194mm;
            max-width: 194mm;
            margin: 0 auto;
            background: #fff;
            color: #000;
            font-family: Arial, sans-serif;
            font-size: 10.6px;
            line-height: 1.22;
            padding: 8mm;
            box-sizing: border-box;
            box-shadow: 0 0 8px rgba(0, 0, 0, .12);
            overflow: visible;
        }

        .po-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .po-table th,
        .po-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .no-border,
        .no-border td,
        .no-border th {
            border: none !important;
        }

        .blue-head {
            background: #000080 !important;
            color: #fff !important;
            font-weight: bold;
        }

        .dark-head {
            background: #37465a !important;
            color: #fff !important;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .doc-title {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            margin: 6px 0 8px 0;
            color: #000;
        }

        .signature-box {
            min-height: 72px;
            border: 1px solid #d9e2f2;
            border-radius: 7px;
            padding: 6px;
            background: #fbfcff;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .signature-img,
        .stamp-img {
            max-height: 42px;
            max-width: 125px;
            object-fit: contain;
        }

        .po-footer {
            text-align: center;
            font-size: 8.8px;
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px solid #ddd;
            color: #000;
            page-break-inside: avoid;
        }

        .terms-box {
            font-size: 9.5px;
            font-weight: bold;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                background: #fff !important;
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
            .pace,
            .pace-activity,
            .theme-config,
            .right-sidebar,
            #right-sidebar,
            .footer.fixed,
            .fixed-footer,
            #footer {
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
                min-height: 0 !important;
                height: auto !important;
                overflow: visible !important;
                background: #fff !important;
            }

            .po-page-wrap {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: visible !important;
            }

            #printArea {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 194mm !important;
                max-width: 194mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                background: #fff !important;
                box-shadow: none !important;
                overflow: visible !important;
                transform: none !important;
                zoom: 1 !important;
            }

            .po-container {
                width: 194mm !important;
                max-width: 194mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
                font-size: 10.2px !important;
                line-height: 1.18 !important;
                page-break-inside: avoid !important;
                overflow: visible !important;
                transform: none !important;
                zoom: 1 !important;
            }

            .po-table th,
            .po-table td {
                padding: 3px 4px !important;
            }

            .doc-title {
                font-size: 15px !important;
                margin: 5px 0 7px 0 !important;
            }

            .signature-box,
            .po-footer,
            table,
            tr,
            td,
            th {
                page-break-inside: avoid !important;
            }

            .signature-img,
            .stamp-img {
                max-height: 38px !important;
                max-width: 115px !important;
            }
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading no-print">
        <div class="col-lg-8">
            <h2>General Supply Dashboard</h2>
            <ol class="breadcrumb" style="font-size:16px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                <li><a href="{{ route('sales.po.index') }}">Purchase Orders</a></li>
                <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>{{ $order->po_no }}</strong></li>
            </ol>
        </div>

        <div class="col-lg-4 text-right" style="padding-top:25px;">
            <button onclick="window.print();" class="btn btn-primary">
                <i class="fa fa-print"></i> Print
            </button>

            <a href="{{ route('sales.po.documents', encrypt($order->id)) }}" class="btn btn-info">
                <i class="fa fa-file"></i> Documents
            </a>

            <a href="{{ route('sales.po.index') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="po-page-wrap">
        <div class="po-container" id="printArea">

            {{-- HEADER IMAGE --}}
            <div style="width:100%; margin-bottom:6px; text-align:center;">
                <img src="{{ asset('img/header.png') }}" alt="Company Header"
                    style="width:100%; max-height:100%; object-fit:contain;">
            </div>

            <div class="doc-title">PURCHASE ORDER</div>

            {{-- TOP INFO --}}
            <table class="po-table" style="margin-bottom:6px;">
                <tr class="blue-head">
                    <td colspan="2" style="width:50%;">COMPANY DETAILS</td>
                    <td colspan="2" style="width:50%;">PURCHASE ORDER DETAILS</td>
                </tr>
                <tr>
                    <td style="width:18%; font-weight:bold;">Company</td>
                    <td style="width:32%;">{{ $company->company_name ?? '-' }}</td>
                    <td style="width:18%; font-weight:bold;">Date</td>
                    <td style="width:32%;">
                        {{ $order->po_date ? \Carbon\Carbon::parse($order->po_date)->format('M d, Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Business Unit</td>
                    <td>{{ $unit->unit_code ?? '-' }} - {{ $unit->unit_name ?? '-' }}</td>
                    <td style="font-weight:bold;">PO #</td>
                    <td>{{ $order->po_no }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Location</td>
                    <td>{{ $workPoint->work_code ?? '-' }} - {{ $workPoint->work_name ?? '-' }}</td>
                    <td style="font-weight:bold;">P.I #</td>
                    <td>{{ $order->pi_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Purchase Type</td>
                    <td>{{ $order->purchase_type ?? '-' }}</td>
                    <td style="font-weight:bold;">Currency</td>
                    <td>{{ $order->currency ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Exchange Rate</td>
                    <td>{{ $poFmt4($order->exchange_rate ?? 1) }}</td>
                    <td style="font-weight:bold;">Expected Delivery</td>
                    <td>
                        {{ $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('M d, Y') : '-' }}
                    </td>
                </tr>
            </table>

            {{-- SHIP TO / FROM --}}
            <table class="po-table">
                <tr class="blue-head">
                    <td style="width:40%;">SHIP TO:</td>
                    <td style="width:60%;">FROM</td>
                </tr>
                <tr>
                    <td style="height:78px;">
                        {!! nl2br(e($order->ship_to)) !!}
                    </td>
                    <td>
                        {!! nl2br(e($order->vendor_from)) !!}
                        <br>
                        <strong>TIN:</strong> {{ $vendor->tin_no ?? '-' }}<br>
                        <strong>Account:</strong> {{ $order->account_code ?? '-' }} - {{ $order->account_name ?? '-' }}
                    </td>
                </tr>
            </table>

            {{-- SHIPPING --}}
            <table class="po-table" style="margin-top:0;">
                <tr class="dark-head">
                    <td>SHIPPING METHOD</td>
                    <td>SHIPPING TERMS</td>
                    <td>DELIVERY POINT</td>
                </tr>
                <tr>
                    <td>{{ $order->shipping_method ?? '-' }}</td>
                    <td>{{ $order->shipping_terms ?? '-' }}</td>
                    <td>{{ $order->delivery_point ?? '-' }}</td>
                </tr>
            </table>

            <div class="text-center" style="font-size:10.5px; margin:3px 0;">
                PLEASE SUPPLY THE FOLLOWING
            </div>

            {{-- ITEMS --}}
            <table class="po-table">
                <thead>
                    <tr class="blue-head">
                        <th style="width:6%;">SR</th>
                        <th style="width:36%;">DESCRIPTION</th>
                        <th style="width:9%;">UNIT</th>
                        <th style="width:10%;">QTY</th>
                        <th style="width:15%;">UNIT PRICE<br>{{ $order->currency }}</th>
                        <th style="width:9%;">TAX</th>
                        <th style="width:15%;">TOTAL AMOUNT<br>{{ $order->currency }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($order->items as $k => $item)
                        <tr>
                            <td class="text-center">{{ $k + 1 }}</td>
                            <td>{{ $item->description ?? $item->item_name }}</td>
                            <td class="text-center">{{ $item->unit ?? '-' }}</td>
                            <td class="text-right">{{ $poFmt4($item->qty) }}</td>
                            <td class="text-right">{{ $poFmt4($item->unit_price) }}</td>
                            <td class="text-center">
                                {{ (float) ($item->vat_amount ?? 0) > 0 || (float) ($order->vat_amount ?? 0) > 0 ? $poFmt4($order->vat_rate ?? 18) . '%' : '-' }}
                            </td>
                            <td class="text-right">{{ $poFmtTotal($item->sub_total) }}</td>
                        </tr>
                    @endforeach

                    @for ($i = $order->items->count(); $i < 3; $i++)
                        <tr>
                            <td style="height:30px;"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-right">-</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- TERMS + TOTALS --}}
            <table class="po-table" style="margin-top:8px;">
                <tr>
                    <td style="width:55%; vertical-align:top;" rowspan="9">
                        <div class="blue-head" style="padding:4px;">OTHER TERMS & CONDITIONS</div>
                        <div class="terms-box">
                            {!! nl2br(e($order->terms_conditions ?? '-')) !!}
                        </div>
                    </td>
                    <td style="width:25%; font-weight:bold;" class="text-right">Subtotal</td>
                    <td style="width:20%;" class="text-right">
                        {{ $order->currency }} {{ $poFmt4($order->sub_total) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="font-weight:bold;">VAT Rate %</td>
                    <td class="text-right">{{ $poFmt4($order->vat_rate) }}</td>
                </tr>
                <tr>
                    <td class="text-right" style="font-weight:bold;">VAT</td>
                    <td class="text-right">{{ $order->currency }} {{ $poFmt4($order->vat_amount) }}</td>
                </tr>
                <tr>
                    <td class="text-right" style="font-weight:bold;">Discount</td>
                    <td class="text-right">{{ $order->currency }} {{ $poFmt4($order->discount) }}</td>
                </tr>
                <tr>
                    <td class="text-right" style="font-weight:bold;">Total</td>
                    <td class="text-right">
                        <strong>{{ $order->currency }} {{ $poFmtTotal($order->total_amount) }}</strong>
                    </td>
                </tr>
                @if ((float) ($order->exchange_rate ?? 1) != 1)
                    <tr>
                        <td class="text-right" style="font-weight:bold;">Exchange Rate</td>
                        <td class="text-right">{{ $poFmt4($order->exchange_rate) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right" style="font-weight:bold;">Total TZS</td>
                        <td class="text-right"><strong>TZS {{ $poFmtTotal($order->total_tzs) }}</strong></td>
                    </tr>
                @endif
                <tr>
                    <td class="text-right" style="font-weight:bold;">Paid</td>
                    <td class="text-right">{{ $order->currency }} {{ $poFmtTotal($order->amount_paid) }}</td>
                </tr>
                <tr>
                    <td class="text-right" style="font-weight:bold;">Balance</td>
                    <td class="text-right">{{ $order->currency }} {{ $poFmtTotal($order->balance) }}</td>
                </tr>
            </table>

            {{-- SIGNATURES --}}
            <table class="po-table no-border" style="margin-top:10px;">
                <tr>
                    <td width="50%">
                        <div class="signature-box">
                            <strong>Prepared By</strong><br>
                            {{ $creator->name ?? 'System' }}<br>

                            @if ($signaturePath)
                                <img src="{{ $signaturePath }}" class="signature-img" alt="Signature"
                                    @if ($signatureFallbackPath) onerror="this.onerror=null;this.src='{{ $signatureFallbackPath }}';" @endif><br>
                            @endif

                            <strong>TIN:</strong> {{ $company->TIN ?? '119-505-887' }}<br>
                            <strong>VRN:</strong> {{ $company->vrn ?? '40-020836-I' }}
                        </div>
                    </td>

                    <td width="50%">
                        <div class="signature-box text-center">
                            <strong>Company Stamp / Approval</strong><br>

                            @if ($stampPath)
                                <img src="{{ $stampPath }}" class="stamp-img" alt="Stamp"
                                    @if ($stampFallbackPath) onerror="this.onerror=null;this.src='{{ $stampFallbackPath }}';" @endif><br>
                            @endif

                            @if ($order->status === 'Approved' || $order->approved_at)
                                Approved By: {{ $approver->name ?? '-' }}<br>
                                Approved Date:
                                {{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('Y-m-d H:i') : '-' }}<br>
                                Serial No: {{ $order->accounting_transaction_group ?? '-' }}
                            @else
                                Pending approval
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            @if ($order->remarks)
                <div style="margin-top:8px;">
                    <strong>Remarks:</strong><br>
                    {!! nl2br(e($order->remarks)) !!}
                </div>
            @endif

            {{-- FOOTER --}}
            <div class="po-footer">
                <strong>Thank you for your business!</strong><br>
                Should you have any enquiries concerning this purchase order, please contact Managing Director<br>
                GROUND FLOOR, NILE PLAZA BUILDING, Shinyanga Road Opposite Nyashishi Min Bus Stand<br>
                Tel: {{ $company->phone_No ?? '+255756263287' }} |
                Email: {{ $company->email ?? 'info@mbogomining.co.tz' }} |
                Web: {{ $company->website ?? 'www.mbogomining.co.tz' }}
            </div>
        </div>
    </div>
@endsection
