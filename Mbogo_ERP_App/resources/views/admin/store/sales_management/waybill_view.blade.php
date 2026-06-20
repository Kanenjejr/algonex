@extends('layouts.salesMaster')

@section('content')
    @php
        $company =
            $delivery->company ?: optional($delivery->invoice)->company ?: optional($delivery->proforma)->company;
        $customer =
            $delivery->customer ?: optional($delivery->invoice)->customer ?: optional($delivery->proforma)->customer;
        $isApproved =
            strtolower($delivery->approval_status ?? '') === 'approved' ||
            strtolower($delivery->status ?? '') === 'closed';

        $tin = optional($customer)->tin_number ?? optional($customer)->tin;
        $vrn = optional($customer)->vrn;

        $explosiveWords = [
            'explosive',
            'baruti',
            'detonator',
            'detonating',
            'detonating cord',
            'ied',
            'anfo',
            'blasting',
            'super power',
        ];
        $hasExplosive = false;

        foreach ($delivery->items as $item) {
            $name = strtolower(($item->item_name ?? '') . ' ' . optional($item->product)->product_name);
            foreach ($explosiveWords as $word) {
                if (str_contains($name, $word)) {
                    $hasExplosive = true;
                    break 2;
                }
            }
        }
    @endphp

    <style>
        .waybill-page .paper {
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

        .waybill-page .actions {
            text-align: right;
            margin-bottom: 10px;
        }

        .waybill-page .title {
            text-align: center;
            font-size: 21px;
            font-weight: 800;
            margin: 10px 0;
            color: #0b1a78;
        }

        .waybill-page .header-bar {
            background: #0b1a78;
            color: #fff;
            font-weight: 700;
            padding: 6px;
        }

        .waybill-page table {
            width: 100%;
            border-collapse: collapse;
        }

        .waybill-page td,
        .waybill-page th {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .waybill-page .no-border td {
            border: none;
        }

        .waybill-page .text-right {
            text-align: right;
        }

        .waybill-page .text-center {
            text-align: center;
        }

        .waybill-page .warning-box {
            border: 1px solid #000;
            padding: 8px;
            margin-top: 10px;
            font-size: 12px;
        }

        .waybill-page .warning-title {
            font-weight: bold;
            color: #0b1a78;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .waybill-page .sign-img {
            max-height: 60px;
            max-width: 150px;
            object-fit: contain;
        }

        .waybill-page .stamp-img {
            max-height: 78px;
            max-width: 170px;
            object-fit: contain;
        }

        .waybill-page .footer-note {
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
                overflow: visible !important;
                background: #fff !important;
            }

            .waybill-page {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .waybill-page .paper {
                width: 190mm !important;
                max-width: 190mm !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding: 0 !important;
                border: none !important;
                font-size: 10.5px !important;
            }

            .waybill-page td,
            .waybill-page th {
                padding: 3.5px !important;
            }
        }
    </style>

    <div class="waybill-page wrapper wrapper-content">
        <div class="paper">
            <div class="actions no-print">
                <a href="{{ route('sales.deliveries') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <a href="{{ route('sales.delivery.note', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-info" target="_blank">
                    Delivery Note
                </a>

                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>

            <img src="{{ asset('img/header.png') }}" style="width:100%;max-height:150px;object-fit:contain;">

            <div class="title">WAYBILL</div>

            <table class="no-border">
                <tr>
                    <td width="60%">
                        <div class="header-bar">CONSIGNEE / CUSTOMER DETAILS</div>
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
                                <td><strong>WAYBILL #</strong></td>
                                <td>{{ $delivery->waybill_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>DELIVERY #</strong></td>
                                <td>{{ $delivery->delivery_no }}</td>
                            </tr>
                            <tr>
                                <td><strong>DATE</strong></td>
                                <td>{{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('F d, Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>STATUS</strong></td>
                                <td>{{ strtoupper($delivery->approval_status ?? 'pending') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <table>
                <tr class="header-bar">
                    <th colspan="4">TRANSPORT / ROUTE DETAILS</th>
                </tr>
                <tr>
                    <td><strong>Transport Owner</strong></td>
                    <td>{{ ucfirst($delivery->transport_owner ?? '-') }}</td>
                    <td><strong>Transport Mode</strong></td>
                    <td>{{ $delivery->transport_mode ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Vehicle No</strong></td>
                    <td>{{ $delivery->vehicle_no ?? '-' }}</td>
                    <td><strong>Container No</strong></td>
                    <td>{{ $delivery->container_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Driver</strong></td>
                    <td>{{ $delivery->driver_name ?? '-' }}</td>
                    <td><strong>Driver Phone</strong></td>
                    <td>{{ $delivery->driver_phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Origin</strong></td>
                    <td>{{ $delivery->origin ?? '-' }}</td>
                    <td><strong>Destination</strong></td>
                    <td>{{ $delivery->destination ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Dispatch Date</strong></td>
                    <td>{{ $delivery->dispatch_date ? \Carbon\Carbon::parse($delivery->dispatch_date)->format('Y-m-d') : '-' }}
                    </td>
                    <td><strong>Expected Delivery</strong></td>
                    <td>{{ $delivery->expected_delivery_date ? \Carbon\Carbon::parse($delivery->expected_delivery_date)->format('Y-m-d') : '-' }}
                    </td>
                </tr>
            </table>

            <br>

            <table>
                <thead>
                    <tr class="header-bar">
                        <th style="width:40px;">#</th>
                        <th>Description</th>
                        <th style="width:90px;">Qty</th>
                        <th style="width:90px;">Unit</th>
                        <th style="width:120px;">Unit Price</th>
                        <th style="width:130px;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($delivery->items as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item->item_name ?? optional($item->product)->product_name }}</td>
                            <td class="text-right">{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="text-right">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <br>

            <table class="no-border">
                <tr>
                    <td width="58%">
                        <table>
                            <tr>
                                <th colspan="2" class="header-bar">WAYBILL CHARGE / ACCOUNTING</th>
                            </tr>
                            <tr>
                                <td><strong>Amount</strong></td>
                                <td>{{ $delivery->delivery_income_currency ?? 'TZS' }}
                                    {{ number_format((float) $delivery->delivery_income_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method</strong></td>
                                <td>{{ ucfirst($delivery->delivery_payment_method ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Payment Account</strong></td>
                                <td>{{ optional($delivery->paymentAccount)->SubCode }}
                                    {{ optional($delivery->paymentAccount)->SubDescription }}</td>
                            </tr>
                            <tr>
                                <td><strong>Service Income</strong></td>
                                <td>{{ optional($delivery->serviceIncomeAccount)->SubCode }}
                                    {{ optional($delivery->serviceIncomeAccount)->SubDescription }}</td>
                            </tr>
                        </table>
                    </td>

                    <td width="42%">
                        <table>
                            <tr>
                                <th colspan="2" class="header-bar">CONTROLLED GOODS INFO</th>
                            </tr>
                            <tr>
                                <td><strong>Permit No</strong></td>
                                <td>{{ $delivery->permit_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Storage Type</strong></td>
                                <td>{{ $delivery->storage_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Safety Officer</strong></td>
                                <td>{{ $delivery->safety_officer ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Escort Officer</strong></td>
                                <td>{{ $delivery->escort_officer ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            @if ($hasExplosive)
                <div class="warning-box">
                    <div class="warning-title">WARNING / ONYO KWA USAFIRISHAJI WA BARUTI AU BIDHAA HATARISHI</div>
                    <ol style="margin-bottom:0;padding-left:20px;">
                        <li>Hairuhusiwi kusafirisha baruti kwenye vyombo vya abiria.</li>
                        <li>Hairuhusiwi kusafirisha baruti bila kibali cha serikali.</li>
                        <li>Hairuhusiwi kusafirisha baruti bila waybill ya kampuni.</li>
                        <li>Hairuhusiwi kusafirisha baruti bila blasting certificate pale inapohitajika.</li>
                        <li>Ukikamatwa bila vielelezo tajwa hapo juu, kampuni haitahusika na sharia itachukua mkondo wake.
                        </li>
                    </ol>
                </div>
            @endif

            <br>

            <table class="no-border">
                <tr>
                    <td width="33%">
                        <strong>Security Officer</strong><br><br>
                        Name: ______________________<br><br>
                        ID: ________________________<br><br>
                        Signature: _________________<br><br>
                        Date: ______________________
                    </td>

                    <td width="34%">
                        <strong>Customer / Receiver</strong><br><br>
                        Name: {{ $delivery->receiver_name ?? '______________________' }}<br><br>
                        Signature: _________________<br><br>
                        Date: ______________________
                    </td>

                    <td width="33%" class="text-center">
                        <strong>Authorized By</strong><br>
                        @if ($isApproved)
                            {{ optional($delivery->approver)->name ?? '-' }}<br>
                            {{ $delivery->approved_at ? \Carbon\Carbon::parse($delivery->approved_at)->format('Y-m-d H:i') : '-' }}<br>
                            @if (optional($company)->signature)
                                <img src="{{ asset(optional($company)->signature) }}" class="sign-img" alt="Signature">
                            @endif
                            <br>
                            @if (optional($company)->stamp)
                                <img src="{{ asset(optional($company)->stamp) }}" class="stamp-img" alt="Stamp">
                            @endif
                        @else
                            <br><span style="font-size:12px;color:#9a6700;">Signature and stamp will appear after waybill
                                approval.</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="footer-note">
                <strong>Thank you for your business!</strong><br>
                {{ optional($company)->district ?? 'GROUND FLOOR, NILE PLAZA BUILDING' }}
                {{ optional($company)->city ?? 'Shinyanga Road Opposite Nyashishi Min Bus Stand' }}<br>
                Tel: {{ optional($company)->phone_No ?? '+255756263287' }} |
                Email: info@mbogomining.co.tz |
                Web: www.mbogomining.co.tz
            </div>
        </div>
    </div>
@endsection
