@extends('layouts.salesMaster')

@section('content')
    @php
        $enc = fn($id) => $id ? \Illuminate\Support\Facades\Crypt::encryptString((string) $id) : '';

        $company = $invoice->company;
        $customer = $invoice->customer;
        $approvedPayment = $invoice->payments ? $invoice->payments->where('status', 'approved')->last() : null;

        // Accounting is posted only after payment approval, therefore PAID must mean approved payment exists.
        $isApprovedPaid = $approvedPayment && strtolower($invoice->payment_status ?? '') === 'paid';

        $tin = optional($customer)->tin_number ?? optional($customer)->tin;
        $vrn = optional($customer)->vrn;
    @endphp

    <style>
        .invoice-print-page {
            padding-bottom: 120px;
        }

        .invoice-print-page .paper {
            background: #fff;
            width: 210mm;
            max-width: 980px;
            margin: 20px auto;
            padding: 18px;
            border: 1px solid #ddd;
            color: #000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }

        .invoice-print-page .actions {
            text-align: right;
            margin-bottom: 10px;
        }

        .invoice-print-page .title {
            text-align: center;
            font-size: 21px;
            font-weight: 800;
            margin: 10px 0;
            color: #0b1a78;
            letter-spacing: .5px;
        }

        .invoice-print-page .header-bar {
            background: #0b1a78;
            color: #fff;
            font-weight: 700;
            padding: 6px;
        }

        .invoice-print-page table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-print-page td,
        .invoice-print-page th {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .invoice-print-page .no-border td {
            border: none;
        }

        .invoice-print-page .text-right {
            text-align: right;
        }

        .invoice-print-page .text-center {
            text-align: center;
        }

        .invoice-print-page .paid-stamp {
            color: green;
            font-size: 20px;
            font-weight: 800;
            text-align: center;
        }

        .invoice-print-page .sign-img {
            max-height: 60px;
            max-width: 150px;
            object-fit: contain;
        }

        .invoice-print-page .stamp-img {
            max-height: 78px;
            max-width: 170px;
            object-fit: contain;
        }

        .invoice-print-page .footer-note {
            text-align: center;
            font-size: 11px;
            margin-top: 16px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            .no-print,
            .navbar,
            .footer,
            .page-heading,
            .minimalize-styl-2 {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            #wrapper,
            #page-wrapper,
            .wrapper,
            .wrapper-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                overflow: visible !important;
                background: #fff !important;
            }

            .invoice-print-page {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }

            .invoice-print-page .paper {
                width: 190mm !important;
                max-width: 190mm !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding: 0 !important;
                border: none !important;
                font-size: 10.5px !important;
                box-shadow: none !important;
                transform: none !important;
            }

            .invoice-print-page td,
            .invoice-print-page th {
                padding: 3.5px !important;
            }

            .invoice-print-page img {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

    <div class="invoice-print-page wrapper wrapper-content">
        <div class="paper">
            <div class="actions no-print">
                <a href="{{ route('sales.invoice.view', $enc($invoice->id)) }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>

            <img src="{{ asset('img/header.png') }}" style="width:100%;max-height:150px;object-fit:contain;">

            <div class="title">TAX INVOICE</div>

            <table class="no-border">
                <tr>
                    <td width="60%">
                        <div class="header-bar">LESSEE / CUSTOMER DETAILS</div>
                        <strong>{{ optional($customer)->customer_name ?? '-' }}</strong><br>
                        {{ optional($customer)->address ?? '-' }}<br>
                        PHONE NO: {{ optional($customer)->phone ?? '-' }}<br>
                        @if ($tin)
                            TIN: {{ $tin }}<br>
                        @endif
                        @if ($vrn)
                            VRN: {{ $vrn }}<br>
                        @endif
                    </td>
                    <td width="40%">
                        <table>
                            <tr>
                                <td><strong>DATE</strong></td>
                                <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('F d, Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>TAX INVOICE #</strong></td>
                                <td>{{ $invoice->invoice_no }}</td>
                            </tr>
                            <tr>
                                <td><strong>PROFORMA #</strong></td>
                                <td>{{ optional($invoice->proforma)->proforma_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>STATUS</strong></td>
                                <td>{{ $isApprovedPaid ? 'PAID - APPROVED' : strtoupper($invoice->payment_status ?? 'UNPAID') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <table>
                <thead>
                    <tr class="header-bar">
                        <th style="width:40px;">Item #</th>
                        <th>Description</th>
                        <th style="width:75px;">Qty.</th>
                        <th style="width:75px;">Units</th>
                        <th style="width:130px;">Unit Price ({{ $invoice->currency }})</th>
                        <th style="width:80px;">VAT</th>
                        <th style="width:140px;">Line Total ({{ $invoice->currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item->description ?: $item->product_name }}</td>
                            <td class="text-right">{{ number_format((float) $item->qty, 2) }}</td>
                            <td>{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-right">{{ number_format((float) $item->price, 2) }}</td>
                            <td class="text-center">
                                {{ (float) $invoice->tax > 0 ? number_format((float) $invoice->vat_rate, 0) . '%' : 'x' }}
                            </td>
                            <td class="text-right">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach

                    @for ($i = $invoice->items->count(); $i < 8; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <br>

            <table class="no-border">
                <tr>
                    <td width="58%">
                        <table>
                            <tr>
                                <th colspan="2" class="header-bar">BANK DETAILS</th>
                            </tr>
                            <tr>
                                <td><strong>NAME</strong></td>
                                <td>{{ optional($company)->company_name ?? 'MBOGO MINING AND GENERAL SUPPLY LTD' }}</td>
                            </tr>
                            <tr>
                                <td><strong>BANK NAME</strong></td>
                                <td>{{ $invoice->bank_name ?? (optional($invoice->bank)->SubDescription ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td><strong>ACCOUNT NUMBER</strong></td>
                                <td>{{ $invoice->account_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>SWIFT CODE</strong></td>
                                <td>{{ $invoice->swift_code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>BRANCH</strong></td>
                                <td>{{ $invoice->branch ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="42%">
                        <table>
                            <tr>
                                <td>Subtotal</td>
                                <td>{{ $invoice->currency }}</td>
                                <td class="text-right">{{ number_format((float) $invoice->sub_total, 2) }}</td>
                            </tr>
                            <tr>
                                <td>VAT</td>
                                <td>{{ $invoice->currency }}</td>
                                <td class="text-right">{{ number_format((float) $invoice->tax, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>{{ $invoice->currency }}</strong></td>
                                <td class="text-right"><strong>{{ number_format((float) $invoice->total, 2) }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td>Approved Paid</td>
                                <td>{{ $invoice->currency }}</td>
                                <td class="text-right">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Balance</td>
                                <td>{{ $invoice->currency }}</td>
                                <td class="text-right">{{ number_format((float) $invoice->balance, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <table class="no-border">
                <tr>
                   <td width="50%">
                        SALES TECHNICIAN<br>
                        Authorized Signatory<br>
                        {{ optional($approvedPayment)->approver ? optional($approvedPayment->approver)->name : (auth()->user()->name ?? 'System') }}
                        <br><br>

                        <strong>TIN:</strong>
                        {{ $company->tin ?? $company->TIN ?? $company->tin_no ?? '119-505-887' }}
                        <br>

                        <strong>VRN:</strong>
                        {{ $company->vrn ?? $company->VRN ?? '40-020836-I' }}
                    </td>
                    <td width="50%" class="text-center">
                        @if ($isApprovedPaid)
                            <div class="paid-stamp">PAID</div>
                            <div style="font-size:11px;">
                                Approved By: {{ optional(optional($approvedPayment)->approver)->name ?? '-' }}<br>
                                {{ optional($approvedPayment)->approved_at ? \Carbon\Carbon::parse($approvedPayment->approved_at)->format('Y-m-d H:i') : '' }}
                            </div>

                            @if (optional($company)->signature)
                                <img src="{{ asset(optional($company)->signature) }}" class="sign-img" alt="Signature">
                            @endif

                            <br>

                            @if (optional($company)->stamp)
                                <img src="{{ asset(optional($company)->stamp) }}" class="stamp-img" alt="Stamp">
                            @endif
                        @else
                            <span style="font-size:12px;color:#9a6700;">
                                Signature and stamp will appear after payment approval.
                            </span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="footer-note">
                <strong>Thank you for your business!</strong><br>
                {{ optional($company)->district ?? 'GROUND FLOOR, NILE PLAZA BUILDING' }}<br>
                {{ optional($company)->city ?? 'Shinyanga Road Opposite Nyashishi Min Bus Stand' }}<br>
                Tel: {{ optional($company)->phone_No ?? '+255756263287' }}<br>
                Email: info@mbogomining.co.tz<br>
                Web: www.mbogomining.co.tz
            </div>
        </div>
    </div>
@endsection
