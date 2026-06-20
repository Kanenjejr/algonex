@extends('layouts.salesMaster')

@section('content')
    <div class="wrapper wrapper-content">

        {{-- ================= PAGE HEADER - NOT PRINTED ================= --}}
        <div class="row wrapper border-bottom white-bg page-heading no-print">
            <div class="col-lg-8">
                <h2 class="dashboard-title">Sales Management Module</h2>
                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li><a href="{{ route('sales.dashboard') }}">Sales Management</a></li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li><a href="{{ route('sales.proformas') }}">Proformas</a></li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li class="breadcrumb-item active"><strong>View Proforma</strong></li>
                </ol>
            </div>

            <div class="col-lg-4 text-right" style="padding-top:25px;">
                @php
                    $encryptedId = \Illuminate\Support\Facades\Crypt::encryptString($proforma->id);
                @endphp
                <a href="{{ route('proforma.print', $encryptedId) }}" target="_blank" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print
                </a>
                <a href="{{ route('sales.proformas') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>


        @php
            $company = optional($proforma->company);
            $customer = optional($proforma->customer);
            $creator = optional($proforma->creator);
            $approver = optional($proforma->approver);

            $stamp = $company->stamp ?? null;
            $signature = $company->signature ?? null;

            $status = strtolower($proforma->status ?? 'draft');

            $docCurrency = strtoupper(
                $proforma->currency ?? (($proforma->invoice_type ?? 'local') === 'export' ? 'USD' : 'TZS'),
            );

            $exchangeRate = (float) ($proforma->exchange_rate ?? 0);
            $hasExchangeRate = $exchangeRate > 0 && (float) $exchangeRate != 1.0;

            $docSubtotal = (float) ($proforma->subtotal ?? 0);
            $docVat = (float) ($proforma->vat ?? 0);
            $docTotal = (float) ($proforma->total ?? $docSubtotal + $docVat);

            $secondaryCurrency = $docCurrency === 'USD' ? 'TZS' : 'USD';

            $toUsd = function ($value) use ($docCurrency, $exchangeRate, $hasExchangeRate) {
                if ($docCurrency === 'USD' || !$hasExchangeRate) {
                    return (float) $value;
                }

                return $exchangeRate > 0 ? (float) $value / $exchangeRate : (float) $value;
            };

            $toTzs = function ($value) use ($docCurrency, $exchangeRate, $hasExchangeRate) {
                if ($docCurrency === 'TZS' || !$hasExchangeRate) {
                    return (float) $value;
                }

                return (float) $value * $exchangeRate;
            };

            $fmtView = function ($value) {
                $value = round((float) $value, 6);
                $formatted = number_format($value, 6, '.', ',');
                $formatted = rtrim(rtrim($formatted, '0'), '.');
                return $formatted === '' ? '0' : $formatted;
            };

            $fmtTotal = function ($value) {
                $value = round((float) $value, 6);
                $formatted = number_format($value, 6, '.', ',');
                $formatted = rtrim(rtrim($formatted, '0'), '.');
                return $formatted === '' ? '0' : $formatted;
            };

            $fmtFx = function ($value) {
                return number_format((float) $value, 4, '.', ',');
            };

            if (
                $status !== 'approved' &&
                (!empty($proforma->approved_at) || !empty($proforma->accounting_transaction_group))
            ) {
                $status = 'approved';
            }
        @endphp
        <style>
            /* ================= SCREEN VIEW ================= */
            .proforma-page-wrap {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                background: #f3f3f4;
                padding: 18px 0 35px 0;
                box-sizing: border-box;
            }

            .proforma-container {
                width: 190mm;
                max-width: 190mm;
                margin: 0 auto;
                background: #fff;
                color: #000;
                font-family: Arial, sans-serif;
                font-size: 11.5px;
                line-height: 1.28;
                padding: 8mm;
                box-sizing: border-box;
                box-shadow: 0 0 8px rgba(0, 0, 0, 0.12);
                overflow: visible;
            }

            .proforma-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .proforma-table th,
            .proforma-table td {
                border: 1px solid #000;
                padding: 4px 5px;
                vertical-align: top;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .no-border,
            .no-border td,
            .no-border th {
                border: none !important;
            }

            .header-bar {
                background: #0b1a78 !important;
                color: #fff !important;
                font-weight: bold;
                padding: 5px 6px;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .doc-title {
                text-align: center;
                font-weight: bold;
                font-size: 17px;
                margin: 8px 0 10px 0;
                color: #000;
            }

            .customer-box {
                border: 1px solid #000;
                border-top: none;
                padding: 6px;
                min-height: 68px;
                box-sizing: border-box;
            }

            .status-pill {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 999px;
                font-weight: bold;
                font-size: 10px;
                border: 1px solid #ccc;
                white-space: nowrap;
            }

            .status-approved {
                color: #173a7a;
                background: #eef4ff;
                border-color: #d8e4fb;
            }

            .status-rejected {
                color: #a10000;
                background: #fff0f0;
                border-color: #ffd0d0;
            }

            .status-draft {
                color: #9a6700;
                background: #fff4e5;
                border-color: #ffe1b8;
            }

            .signature-box {
                min-height: 82px;
                border: 1px solid #d9e2f2;
                border-radius: 7px;
                padding: 7px;
                background: #fbfcff;
                box-sizing: border-box;
                page-break-inside: avoid;
            }

            .signature-img,
            .stamp-img {
                max-height: 48px;
                max-width: 140px;
                object-fit: contain;
            }

            .proforma-footer {
                text-align: center;
                font-size: 9.5px;
                margin-top: 12px;
                padding-top: 6px;
                border-top: 1px solid #ddd;
                color: #000;
                page-break-inside: avoid;
            }

            .items-table th {
                background-color: #0b1a78 !important;
                color: #ffffff !important;
            }

            .currency-summary-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .currency-summary-table td,
            .currency-summary-table th {
                border: 1px solid #000;
                padding: 4px 5px;
                vertical-align: top;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .fx-rate-label {
                text-align: right;
                font-weight: bold;
                margin: 8px 0 4px 0;
                font-size: 10px;
            }





            /* ================= INVOICE DETAILS TABLE LINES ONLY =================
                               Scoped to DATE / P.INVOICE / COMPANY / LOCATION area only.
                            */
            .invoice-meta-section .invoice-details-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .invoice-meta-section .invoice-details-table td,
            .invoice-meta-section .invoice-details-table th {
                border: 1px solid #000 !important;
                padding: 4px 5px;
                vertical-align: top;
                color: #000;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .invoice-meta-section .invoice-details-label {
                width: 38%;
                font-weight: bold;
            }

            /* ================= PAYMENT BLOCK TABLE LINES ONLY =================
                               Scoped to bank/amount section so existing page CSS and print logic stay unchanged.
                            */
            .bank-total-section .bank-details-table,
            .bank-total-section .amount-summary-table,
            .bank-total-section .exchange-summary-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .bank-total-section .bank-details-table th,
            .bank-total-section .bank-details-table td,
            .bank-total-section .amount-summary-table td,
            .bank-total-section .exchange-summary-table td {
                border: 1px solid #000 !important;
                padding: 4px 5px;
                vertical-align: top;
                color: #000;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .bank-total-section .bank-details-table th {
                background: #0b1a78 !important;
                color: #fff !important;
                text-align: left;
                font-weight: bold;
            }

            .bank-total-section .bank-label {
                width: 35%;
                font-weight: bold;
            }

            .bank-total-section .currency-col {
                width: 50px;
                text-align: center;
                font-weight: bold;
            }

            .bank-total-section .amount-label {
                text-align: right;
            }

            .bank-total-section .amount-value {
                text-align: right;
            }

            .bank-total-section .exchange-rate-title {
                margin-top: 8px;
                margin-bottom: 3px;
                text-align: center;
                font-weight: bold;
                font-size: 11px;
            }

            /* ================= PRINT FIX =================
                                                           Important:
                                                           1. Do not use display:none on wrapper parents.
                                                           2. Do not use zoom/scale. It made the document very small.
                                                           3. Print only #printArea using visibility.
                                                        */
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

                .proforma-page-wrap {
                    display: block !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #fff !important;
                    overflow: visible !important;
                }

                .items-table td {
                    color: #000 !important;
                    font-weight: 500 !important;
                    font-size: 10.5px !important;
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

                .proforma-container {
                    width: 194mm !important;
                    max-width: 194mm !important;
                    margin: 0 auto !important;
                    padding: 0 !important;
                    box-shadow: none !important;
                    background: #fff !important;

                    font-size: 11.5px !important;
                    line-height: 1.35 !important;

                    color: #000 !important;
                    font-weight: 500 !important;

                    font-family: Arial, Helvetica, sans-serif !important;
                }

                .proforma-table th,
                .proforma-table td {
                    padding: 3px 3px !important;
                }

                .doc-title {
                    font-size: 15px !important;
                    margin: 5px 0 7px 0 !important;
                }

                .header-bar {
                    padding: 4px 5px !important;
                }

                .customer-box {
                    min-height: 56px !important;
                    padding: 5px !important;
                }

                .signature-box {
                    min-height: 72px !important;
                    padding: 5px !important;
                    page-break-inside: avoid !important;
                }

                .items-table td:nth-child(2) {
                    color: #000 !important;
                    font-weight: bold !important;
                    font-size: 11px !important;
                }

                .signature-img,
                .stamp-img {
                    max-height: 38px !important;
                    max-width: 115px !important;
                }

                .proforma-footer {
                    font-size: 8.8px !important;
                    margin-top: 8px !important;
                    padding-top: 4px !important;
                    page-break-inside: avoid !important;
                }

                .signature-box,
                .proforma-footer {
                    page-break-inside: avoid !important;
                }

                table,
                tr,
                td,
                th {
                    page-break-inside: auto !important;
                }
            }
        </style>

        <div class="proforma-page-wrap">
            <div class="proforma-container" id="printArea">

                {{-- ================= HEADER IMAGE ================= --}}
                <div style="width:100%; margin-bottom:8px; text-align:center;">
                    <img src="{{ asset('img/header.png') }}" alt="Company Header"
                        style="width:100%; max-height:100%; object-fit:contain;">
                </div>

                <div class="doc-title">
                    @if ($proforma->invoice_type === 'export')
                        COMMERCIAL INVOICE
                    @else
                        PROFORMA INVOICE
                    @endif
                </div>

                {{-- ================= CUSTOMER + INVOICE DETAILS ================= --}}
                <table class="proforma-table no-border invoice-meta-section" style="margin-bottom:10px;">
                    <tr>
                        <td width="62%">
                            <div class="header-bar">LESSEE DETAILS</div>
                            <div class="customer-box">
                                <strong>{{ $customer->customer_name ?? '-' }}</strong><br>
                                {{ $customer->address ?? '-' }}<br>
                                PHONE NO: {{ $customer->phone ?? '-' }}<br>
                                @if (!empty($customer->tin_number))
                                    TIN: {{ $customer->tin_number }}<br>
                                @endif
                                @if (!empty($customer->vrn))
                                    VRN: {{ $customer->vrn }}<br>
                                @endif
                            </div>
                        </td>

                        <td width="38%">
                            <table class="proforma-table invoice-details-table">
                                <tr>
                                    <td class="invoice-details-label"><strong>DATE</strong></td>
                                    <td>{{ $proforma->created_at ? \Carbon\Carbon::parse($proforma->created_at)->format('M d, Y') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="invoice-details-label"><strong>P.INVOICE #</strong></td>
                                    <td>{{ $proforma->proforma_no }}</td>
                                </tr>
                                <tr>
                                    <td class="invoice-details-label"><strong>COMPANY</strong></td>
                                    <td>{{ $company->company_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="invoice-details-label"><strong>LOCATION</strong></td>
                                    <td>{{ optional($proforma->workPoint)->work_code ?? '-' }} -
                                        {{ optional($proforma->workPoint)->work_name ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- ================= ITEMS ================= --}}
                <table class="proforma-table items-table">
                    <thead>
                        <tr>
                            <th class="col-sr">SR</th>
                            <th class="col-desc">DESCRIPTION</th>
                            <th class="col-unit">UNIT</th>
                            <th class="col-qty">QTY</th>
                            <th class="col-price">UNIT PRICE</th>
                            <th class="col-tax">TAX</th>
                            <th class="col-total">TOTAL AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hasVat = ($proforma->invoice_type ?? 'local') === 'local' && ($proforma->vat ?? 0) > 0;
                            $currency =
                                $proforma->currency ??
                                (($proforma->invoice_type ?? 'local') === 'export' ? 'USD' : 'TZS');
                        @endphp

                        @foreach ($proforma->items as $i => $item)
                            @php
                                $displayUnitPrice = (float) $item->price;
                                $displayLineTotal = (float) $item->total;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>{{ $item->item_name ?? '-' }}</td>
                                <td class="text-center">{{ $item->unit ?? '-' }}</td>
                                <td class="text-center">{{ $fmtView($item->qty) }}</td>
                                <td class="text-right">{{ $fmtView($displayUnitPrice) }}</td>
                                <td class="text-center">{{ ($proforma->vat ?? 0) > 0 ? '18%' : '-' }}</td>
                                <td class="text-right">{{ $fmtView($displayLineTotal) }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <br>

                {{-- ================= BANK + TOTALS ================= --}}
                <table class="proforma-table no-border bank-total-section">
                    <tr>
                        <td width="60%" style="vertical-align:top; padding-right:8px;">
                            <table class="bank-details-table">
                                <tr>
                                    <th colspan="2">BANK DETAILS</th>
                                </tr>
                                <tr>
                                    <td class="bank-label">NAME</td>
                                    <td>{{ $company->company_name ?? 'MBOGO MINING AND GENERAL SUPPLY LTD' }}</td>
                                </tr>
                                <tr>
                                    <td class="bank-label">BANK NAME</td>
                                    <td>{{ optional($proforma->bank)->SubDescription ?? 'CRDB BANK PLC' }}</td>
                                </tr>
                                <tr>
                                    <td class="bank-label">ACCOUNT NUMBER</td>
                                    <td>{{ $proforma->account_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="bank-label">BRANCH</td>
                                    <td>{{ $proforma->branch ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="bank-label">SWIFT CODE</td>
                                    <td>{{ $proforma->swift_code ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>

                        <td width="40%" style="vertical-align:top; padding-left:8px;">
                            <table class="amount-summary-table">
                                <tr>
                                    <td class="amount-label">Subtotal</td>
                                    <td class="currency-col">{{ $docCurrency }}</td>
                                    <td class="amount-value">{{ $fmtView($docSubtotal) }}</td>
                                </tr>
                                <tr>
                                    <td class="amount-label">VAT 18%</td>
                                    <td class="currency-col">{{ $docCurrency }}</td>
                                    <td class="amount-value">{{ $fmtView($docVat) }}</td>
                                </tr>
                                <tr>
                                    <td class="amount-label"><strong>Total</strong></td>
                                    <td class="currency-col">{{ $docCurrency }}</td>
                                    <td class="amount-value"><strong>{{ $fmtTotal($docTotal) }}</strong></td>
                                </tr>
                            </table>

                            @if ($hasExchangeRate)
                                <div class="exchange-rate-title">
                                    Exchange Rate {{ $fmtFx($exchangeRate) }}
                                </div>

                                <table class="exchange-summary-table">
                                    @if ($docCurrency === 'USD')
                                        @php
                                            $secondarySubtotal = $toTzs($docSubtotal);
                                            $secondaryVat = $toTzs($docVat);
                                            $secondaryTotal = $toTzs($docTotal);
                                        @endphp
                                        <tr>
                                            <td class="amount-label">Subtotal</td>
                                            <td class="currency-col">TZS</td>
                                            <td class="amount-value">{{ $fmtView($secondarySubtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="amount-label">VAT 18%</td>
                                            <td class="currency-col">TZS</td>
                                            <td class="amount-value">{{ $fmtView($secondaryVat) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="amount-label"><strong>Total</strong></td>
                                            <td class="currency-col">TZS</td>
                                            <td class="amount-value"><strong>{{ $fmtTotal($secondaryTotal) }}</strong></td>
                                        </tr>
                                    @else
                                        @php
                                            $secondarySubtotal = $toUsd($docSubtotal);
                                            $secondaryVat = $toUsd($docVat);
                                            $secondaryTotal = $toUsd($docTotal);
                                        @endphp
                                        <tr>
                                            <td class="amount-label">Subtotal</td>
                                            <td class="currency-col">USD</td>
                                            <td class="amount-value">{{ $fmtView($secondarySubtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="amount-label">VAT 18%</td>
                                            <td class="currency-col">USD</td>
                                            <td class="amount-value">{{ $fmtView($secondaryVat) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="amount-label"><strong>Total</strong></td>
                                            <td class="currency-col">USD</td>
                                            <td class="amount-value"><strong>{{ $fmtTotal($secondaryTotal) }}</strong></td>
                                        </tr>
                                    @endif
                                </table>
                            @endif
                        </td>
                    </tr>
                </table>

                <br>
                {{-- ================= SIGNATURES ================= --}}
                <table class="proforma-table no-border">
                    <tr>
                        <td width="50%">
                            <div class="signature-box">
                                <strong>Prepared By / Sales Technician</strong><br>
                                {{ $creator->name ?? 'System' }}<br>

                                <strong>Company TIN:</strong>
                                {{ $company->tin ?? ($company->TIN ?? ($company->tin_no ?? '119-505-887')) }}<br>

                                <strong>Company VRN:</strong>
                                {{ $company->vrn ?? '40-020836-I' }}
                            </div>
                        </td>

                        <td width="50%">
                            <div class="signature-box text-center">
                                <strong>Company Stamp / Approval</strong><br>

                                @if ($signature)
                                    <img src="{{ asset($signature) }}" class="signature-img" alt="Signature"><br>
                                @endif
                                @if ($stamp)
                                    <img src="{{ asset($stamp) }}" class="stamp-img" alt="Stamp"><br>
                                @endif

                                @if ($status === 'approved')
                                    Approved By: {{ $approver->name ?? '-' }}<br>
                                    Approved Date:
                                    {{ $proforma->approved_at ? \Carbon\Carbon::parse($proforma->approved_at)->format('Y-m-d H:i') : '-' }}<br>
                                    Serial No: {{ $proforma->accounting_transaction_group ?? '-' }}
                                @elseif ($status === 'rejected')
                                    Rejected / Needs correction<br>
                                    Edit and resubmit again
                                @else
                                    Pending approval
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                {{-- ================= INVOICE FOOTER ================= --}}
                <div class="proforma-footer">
                    <strong>Thank you for your business!</strong><br>
                    Should you have any enquiries concerning this invoice, please contact Managing Director
                    +255757851332<br>
                    GROUND FLOOR, NILE PLAZA BUILDING, Shinyanga Road Opposite Nyashishi Min Bus Stand<br>
                    Tel: {{ $company->phone_No ?? '+255756263287' }} | Email: info@mbogomining.co.tz | Web:
                    www.mbogomining.co.tz
                </div>
            </div>
        </div>

    </div>
@endsection
