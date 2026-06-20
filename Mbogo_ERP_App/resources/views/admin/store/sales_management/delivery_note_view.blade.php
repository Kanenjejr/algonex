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
        $isClosed = $delivery->isClosed();
        $tin = optional($customer)->tin_number ?? optional($customer)->tin;
        $vrn = optional($customer)->vrn;
    @endphp

    <style>
        .delivery-note-page .paper {
            background: #fff;
            width: 210mm;
            max-width: 980px;
            margin: 20px auto;
            padding: 18px;
            border: 1px solid #ddd;
            color: #000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .delivery-note-page .actions {
            text-align: right;
            margin-bottom: 10px;
        }

        .delivery-note-page .title {
            text-align: center;
            font-size: 21px;
            font-weight: 800;
            margin: 10px 0;
            color: #0b1a78;
        }

        .delivery-note-page .header-bar {
            background: #0b1a78;
            color: #fff;
            font-weight: 700;
            padding: 6px;
        }

        .delivery-note-page table {
            width: 100%;
            border-collapse: collapse;
        }

        .delivery-note-page td,
        .delivery-note-page th {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
            word-break: break-word;
        }

        .delivery-note-page .no-border td {
            border: none;
        }

        .delivery-note-page .text-right {
            text-align: right;
        }

        .delivery-note-page .text-center {
            text-align: center;
        }

        /* Signature */
        .delivery-note-page .sign-img {
            width: 120px;
            max-width: 120px;
            height: 45px;
            max-height: 45px;
            object-fit: contain;
        }

        /* Company Stamp */
        .delivery-note-page .stamp-img {
            width: 70px;
            max-width: 70px;
            height: 70px;
            max-height: 70px;
            object-fit: contain;
        }

        .delivery-note-page .footer-note {
            text-align: center;
            font-size: 11px;
            line-height: 1.6;
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #d9d9d9;
            word-break: break-word;
        }

        @media print {

            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            .no-print,
            .navbar,
            .navbar-static-top,
            .navbar-static-side,
            .footer,
            .footer.fixed,
            .fixed-footer,
            .page-footer,
            #footer,
            .page-heading,
            .minimalize-styl-2 {
                display: none !important;
                visibility: hidden !important;
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

            .delivery-note-page {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .delivery-note-page .paper {
                width: 190mm !important;
                max-width: 190mm !important;
                margin: 0 auto !important;
                padding: 5mm 5mm 18mm 5mm !important;
                border: none !important;
                box-shadow: none !important;
                font-size: 10.5px !important;
                line-height: 1.35 !important;
            }

            .delivery-note-page td,
            .delivery-note-page th {
                padding: 3.5px !important;
            }

            .delivery-note-page .sign-img {
                width: 100px !important;
                max-width: 100px !important;
                height: 35px !important;
                max-height: 35px !important;
                object-fit: contain !important;
            }

            .delivery-note-page .stamp-img {
                width: 60px !important;
                max-width: 60px !important;
                height: 60px !important;
                max-height: 60px !important;
                object-fit: contain !important;
            }

            .delivery-note-page .footer-note {
                font-size: 9px !important;
                line-height: 1.6 !important;
                margin-top: 12px !important;
                padding-top: 6px !important;
                page-break-inside: avoid !important;
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

    <div class="delivery-note-page wrapper wrapper-content">
        <div class="paper">
            <div class="actions no-print">
                <a href="{{ route('sales.deliveries') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <a href="{{ route('sales.delivery.waybill', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-info" target="_blank">
                    Waybill
                </a>

                <a href="{{ route('sales.delivery.packing.list', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-warning" target="_blank">
                    Packing List
                </a>

                <a href="{{ route('sales.delivery.customs.manifest', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-success" target="_blank">
                    Customs Manifest
                </a>

                @if ($isApproved || $isClosed)
                    <a href="{{ route('sales.delivery.document.pack', \Illuminate\Support\Facades\Crypt::encryptString((string) $delivery->id)) }}"
                        class="btn btn-warning" target="_blank">
                        View Documents
                    </a>
                @endif

                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>

            <img src="{{ asset('img/header.png') }}" style="width:100%;max-height:150px;object-fit:contain;">

            <div class="title">DELIVERY NOTE</div>

            <table class="no-border">
                <tr>
                    <td width="60%">
                        <div class="header-bar">CUSTOMER DETAILS</div>
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
                                <td>{{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('F d, Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>DELIVERY #</strong></td>
                                <td>{{ $delivery->delivery_no }}</td>
                            </tr>
                            <tr>
                                <td><strong>NOTE #</strong></td>
                                <td>{{ $delivery->delivery_note_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>INVOICE #</strong></td>
                                <td>{{ optional($delivery->invoice)->invoice_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>PROFORMA #</strong></td>
                                <td>{{ optional($delivery->proforma)->proforma_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>STATUS</strong></td>
                                <td>{{ strtoupper($delivery->delivery_status ?? $delivery->approval_status) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <table>
                <thead>
                    <tr class="header-bar">
                        <th style="width:40px;">#</th>
                        <th>Product / Description</th>
                        <th style="width:90px;">Qty</th>
                        <th style="width:90px;">Unit</th>
                        <th style="width:100px;">Issued Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($delivery->items as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item->item_name ?? optional($item->product)->product_name }}</td>
                            <td class="text-right">{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-right">{{ number_format((float) $item->issued_qty, 2) }}</td>
                        </tr>
                    @endforeach

                    @for ($i = $delivery->items->count(); $i < 8; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <br>

            <table>
                <tr class="header-bar">
                    <th colspan="4">TRANSPORT DETAILS</th>
                </tr>
                <tr>
                    <td><strong>Transport Owner</strong></td>
                    <td>{{ ucfirst($delivery->transport_owner ?? '-') }}</td>
                    <td><strong>Transport Mode</strong></td>
                    <td>{{ $delivery->transport_mode ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Driver</strong></td>
                    <td>{{ $delivery->driver_name ?? '-' }}</td>
                    <td><strong>Driver Phone</strong></td>
                    <td>{{ $delivery->driver_phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Vehicle No</strong></td>
                    <td>{{ $delivery->vehicle_no ?? '-' }}</td>
                    <td><strong>Container No</strong></td>
                    <td>{{ $delivery->container_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Origin</strong></td>
                    <td>{{ $delivery->origin ?? '-' }}</td>
                    <td><strong>Destination</strong></td>
                    <td>{{ $delivery->destination ?? '-' }}</td>
                </tr>
            </table>

            <br>

            <table class="no-border">
                <tr>
                    <td width="33%">
                        <strong>Prepared By</strong><br><br>
                        Name: {{ auth()->user()->name ?? 'System' }}<br><br>
                        Signature: ______________________
                    </td>

                    <td width="34%" class="text-center">
                        <strong>Approved / Authorized</strong><br>
                        @if ($isApproved)
                            Name: {{ optional($delivery->approver)->name ?? '-' }}<br>
                            Date:
                            {{ $delivery->approved_at ? \Carbon\Carbon::parse($delivery->approved_at)->format('Y-m-d H:i') : '-' }}<br>
                            @if (optional($company)->signature)
                                <img src="{{ asset(optional($company)->signature) }}" class="sign-img" alt="Signature">
                            @endif
                            <br>
                            @if (optional($company)->stamp)
                                <img src="{{ asset(optional($company)->stamp) }}" class="stamp-img" alt="Stamp">
                            @endif
                        @else
                            <br><span style="font-size:12px;color:#9a6700;">Signature and stamp will appear after delivery
                                approval.</span>
                        @endif
                    </td>

                    <td width="33%">
                        <strong>Customer / Receiver</strong><br><br>
                        Name: {{ $delivery->receiver_name ?? '______________________' }}<br><br>
                        Signature: ______________________<br><br>
                        Date: ___________________________
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
