@extends('layouts.salesMaster')

@section('content')
    @php
        $company = $delivery->company ?: optional($delivery->invoice)->company ?: optional($delivery->proforma)->company;
        $customer = $delivery->customer ?: optional($delivery->invoice)->customer ?: optional($delivery->proforma)->customer;
        $invoice = $delivery->invoice ?? null;
        $proforma = $delivery->proforma ?? null;

        $isApproved =
            strtolower($delivery->approval_status ?? '') === 'approved' ||
            strtolower($delivery->status ?? '') === 'closed' ||
            strtolower(optional($invoice)->status ?? '') === 'approved' ||
            strtolower(optional($proforma)->status ?? '') === 'approved';

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

        $payments = collect(optional($invoice)->payments ?? []);
        $receiptPayments = $payments->filter(function ($p) {
            return !empty($p->receipt_attachment);
        })->values();

        $receiptTitle = function ($index) {
            return 'RECEIPT ' . ($index + 1);
        };

        $formatDate = function ($value, $format = 'Y-m-d H:i') {
            return $value ? \Carbon\Carbon::parse($value)->format($format) : '-';
        };

        $safeAsset = function ($path) {
            if (!$path) {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/', 'storage/'])) {
                return asset(ltrim($path, '/'));
            }

            return asset($path);
        };
    @endphp
<style>
    .doc-pack-page{
        width:100%;
        display:block;
        background:#f3f3f4;
        padding:15px 0 25px;
        box-sizing:border-box;
    }

    .doc-pack-paper{
        width:190mm;
        max-width:190mm;
        margin:0 auto 18px auto;
        background:#fff;
        color:#000;
        font-family:DejaVu Sans, Arial, sans-serif;
        font-size:11px;
        line-height:1.25;
        padding:8mm;
        box-shadow:0 0 8px rgba(0,0,0,.12);
        box-sizing:border-box;
        page-break-after:always;
    }

    .doc-pack-paper:last-child{
        page-break-after:auto;
    }

    .doc-actions{
        text-align:right;
        margin-bottom:8px;
    }

    .doc-title{
        text-align:center;
        font-weight:bold;
        font-size:17px;
        margin:8px 0 10px;
        color:#000;
    }

    .header-bar{
        background:#0b1a78 !important;
        color:#fff !important;
        font-weight:bold;
        padding:5px 6px;
    }

    .mini-table{
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
    }

    .mini-table th,
    .mini-table td{
        border:1px solid #000;
        padding:4px 5px;
        vertical-align:top;
        word-wrap:break-word;
        overflow-wrap:break-word;
    }

    .no-border,
    .no-border td,
    .no-border th{
        border:none !important;
    }

    .text-right{
        text-align:right;
    }

    .text-center{
        text-align:center;
    }

    .status-pill{
        display:inline-block;
        padding:3px 8px;
        border-radius:999px;
        font-weight:bold;
        font-size:10px;
        border:1px solid #ccc;
        white-space:nowrap;
    }

    .status-approved{
        color:#173a7a;
        background:#eef4ff;
        border-color:#d8e4fb;
    }

    .status-rejected{
        color:#a10000;
        background:#fff0f0;
        border-color:#ffd0d0;
    }

    .status-draft{
        color:#9a6700;
        background:#fff4e5;
        border-color:#ffe1b8;
    }

    .customer-box{
        border:1px solid #000;
        border-top:none;
        padding:6px;
        min-height:68px;
        box-sizing:border-box;
    }

    .section-space{
        margin-top:10px;
    }

    .signature-box{
        min-height:82px;
        border:1px solid #d9e2f2;
        border-radius:7px;
        padding:7px;
        background:#fbfcff;
        box-sizing:border-box;
        page-break-inside:avoid;
    }

    /* SIGNATURE */
    .signature-img,
    .sign-img{
        width:120px;
        max-width:120px;
        height:45px;
        max-height:45px;
        object-fit:contain;
    }

    /* STAMP */
    .stamp-img{
        width:70px;
        max-width:70px;
        height:70px;
        max-height:70px;
        object-fit:contain;
    }

    .footer-note{
        text-align:center;
        font-size:11px;
        line-height:1.6;
        margin-top:15px;
        padding-top:8px;
        border-top:1px solid #cfcfcf;
        color:#000;
        word-wrap:break-word;
        overflow-wrap:break-word;
        page-break-inside:avoid;
    }

    .warning-box{
        border:1px solid #000;
        padding:8px;
        margin-top:10px;
        font-size:12px;
    }

    .warning-title{
        font-weight:bold;
        color:#0b1a78;
        font-size:14px;
        margin-bottom:5px;
    }

    .receipt-card{
        border:1px solid #d9e2f2;
        border-radius:8px;
        padding:10px;
        background:#fbfcff;
        margin-bottom:12px;
        page-break-inside:avoid;
    }

    .receipt-image{
        width:100%;
        max-width:100%;
        border:1px solid #e3e3e3;
        margin-top:10px;
    }

    @media print{

        @page{
            size:A4 portrait;
            margin:8mm;
        }

        html,
        body{
            margin:0 !important;
            padding:0 !important;
            background:#fff !important;
            overflow:visible !important;
            -webkit-print-color-adjust:exact !important;
            print-color-adjust:exact !important;
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
        .footer,
        .footer.fixed,
        .fixed-footer,
        .footer-content,
        .main-footer,
        #footer,
        .page-footer{
            display:none !important;
            visibility:hidden !important;
        }

        #wrapper,
        #page-wrapper,
        .wrapper,
        .wrapper-content,
        .gray-bg,
        .content-wrapper{
            margin:0 !important;
            padding:0 !important;
            width:100% !important;
            background:#fff !important;
            overflow:visible !important;
        }

        .doc-pack-page{
            background:#fff !important;
            margin:0 !important;
            padding:0 !important;
        }

        .doc-pack-paper{
            width:190mm !important;
            max-width:190mm !important;
            margin:0 auto !important;
            padding:5mm !important;
            box-shadow:none !important;
            border:none !important;
            background:#fff !important;
            page-break-after:always;
        }

        .doc-pack-paper:last-child{
            page-break-after:auto;
        }

        .mini-table th,
        .mini-table td{
            padding:3px 4px !important;
        }

        .signature-img,
        .sign-img{
            width:100px !important;
            max-width:100px !important;
            height:35px !important;
            max-height:35px !important;
            object-fit:contain !important;
        }

        .stamp-img{
            width:60px !important;
            max-width:60px !important;
            height:60px !important;
            max-height:60px !important;
            object-fit:contain !important;
        }

        .footer-note{
            font-size:9px !important;
            line-height:1.4 !important;
            margin-top:8px !important;
            padding-top:5px !important;
        }

        table,
        tr,
        td,
        th,
        .signature-box,
        .receipt-card,
        .footer-note{
            page-break-inside:avoid !important;
        }

        .receipt-image {
            width: 100%;
            max-width: 100%;
            border: 1px solid #e3e3e3;
            margin-top: 10px;
        }

        .receipt-card embed{
        width:100%;
        min-height:900px;
        border:1px solid #ddd;
    }
    }
</style>

    <div class="doc-pack-page wrapper wrapper-content">
        <div class="doc-pack-paper" id="packArea">

            <div class="doc-actions no-print">
                <a href="{{ route('sales.deliveries') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <a href="{{ route('sales.delivery.note', Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-info" target="_blank">
                    Delivery Note
                </a>

                <a href="{{ route('sales.delivery.waybill', Crypt::encryptString((string) $delivery->id)) }}"
                    class="btn btn-primary" target="_blank">
                    Waybill
                </a>

                <button onclick="window.print()" class="btn btn-success">
                    <i class="fa fa-print"></i> Print Pack
                </button>
            </div>

            <img src="{{ asset('img/header.png') }}" alt="Company Header"
                style="width:100%;max-height:150px;object-fit:contain;">

            <div class="doc-title">
                @if(optional($proforma)->invoice_type === 'export')
                    COMMERCIAL INVOICE
                @else
                    PROFORMA INVOICE
                @endif
            </div>

            <table class="mini-table no-border" style="margin-bottom:10px;">
                <tr>
                    <td width="62%">
                        <div class="header-bar">LESSEE DETAILS</div>
                        <div class="customer-box">
                            <strong>{{ optional($customer)->customer_name ?? '-' }}</strong><br>
                            {{ optional($customer)->address ?? '-' }}<br>
                            PHONE NO: {{ optional($customer)->phone ?? '-' }}<br>
                            @if (!empty($tin))
                                TIN: {{ $tin }}<br>
                            @endif
                            @if (!empty($vrn))
                                VRN: {{ $vrn }}<br>
                            @endif
                        </div>
                    </td>
                    <td width="38%">
                        <table class="mini-table">
                            <tr>
                                <td style="width:38%;"><strong>DATE</strong></td>
                                <td>{{ optional($proforma)->created_at ? \Carbon\Carbon::parse($proforma->created_at)->format('M d, Y') : optional($delivery->created_at)->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>P.INVOICE #</strong></td>
                                <td>{{ optional($proforma)->proforma_no ?? optional($invoice)->invoice_no ?? optional($delivery)->delivery_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>STATUS</strong></td>
                                <td>
                                    @php
                                        $status = strtolower(trim(optional($proforma)->status ?? optional($delivery)->approval_status ?? 'draft'));
                                        if ($status !== 'approved' && (!empty(optional($proforma)->approved_at) || !empty(optional($proforma)->accounting_transaction_group))) {
                                            $status = 'approved';
                                        }
                                    @endphp

                                    @if ($status === 'approved')
                                        <span class="status-pill status-approved">APPROVED</span>
                                    @elseif ($status === 'rejected')
                                        <span class="status-pill status-rejected">REJECTED</span>
                                    @else
                                        <span class="status-pill status-draft">DRAFT</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>COMPANY</strong></td>
                                <td>{{ optional($company)->company_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>LOCATION</strong></td>
                                <td>{{ optional(optional($proforma)->workPoint ?? optional($delivery)->workPoint ?? null)->work_code ?? '-' }} -
                                    {{ optional(optional($proforma)->workPoint ?? optional($delivery)->workPoint ?? null)->work_name ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="mini-table">
                <thead>
                    <tr class="header-bar">
                        <th style="width:32px;">#</th>
                        <th>Description</th>
                        <th style="width:60px;">Qty</th>
                        <th style="width:65px;">Units</th>
                        <th style="width:95px;">Unit Price</th>
                        <th style="width:65px;">VAT</th>
                        <th style="width:105px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delivery->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->item_name ?? optional($item->product)->product_name ?? '-' }}</td>
                            <td class="text-center">{{ number_format((float) ($item->qty ?? $item->quantity ?? 0), 2) }}</td>
                            <td class="text-center">{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-right">{{ number_format((float) ($item->price ?? $item->unit_price ?? 0), 2) }}</td>
                            <td class="text-center">
                                @if(optional($proforma)->invoice_type === 'local' && (float) optional($proforma)->vat > 0)
                                    x
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">{{ number_format((float) ($item->total ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <br>

            <table class="mini-table no-border">
                <tr>
                    <td width="60%">
                        <table class="mini-table">
                            <tr class="header-bar">
                                <th colspan="2">BANK DETAILS</th>
                            </tr>
                            <tr>
                                <td style="width:34%;">Name</td>
                                <td>{{ optional($company)->company_name ?? 'MBOGO MINING AND GENERAL SUPPLY LTD' }}</td>
                            </tr>
                            <tr>
                                <td>Bank Name</td>
                                <td>{{ optional($proforma->bank ?? $delivery->paymentAccount ?? null)->SubDescription ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Account Number</td>
                                <td>{{ optional($proforma)->account_number ?? optional($delivery)->account_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>SWIFT Code</td>
                                <td>{{ optional($proforma)->swift_code ?? optional($delivery)->swift_code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Branch</td>
                                <td>{{ optional($proforma)->branch ?? optional($delivery)->branch ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>

                    <td width="40%">
                        <table class="mini-table">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-right">{{ number_format((float) (optional($proforma)->subtotal ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td>VAT</td>
                                <td class="text-right">{{ number_format((float) (optional($proforma)->vat ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td class="text-right"><strong>{{ number_format((float) (optional($proforma)->total ?? 0), 2) }}</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <table class="mini-table no-border">
                <tr>
                    <td width="50%">
                        <div class="signature-box">
                            <strong>Prepared By / Sales Technician</strong><br>
                            {{ optional($delivery->creator)->name ?? optional(auth()->user())->name ?? 'System' }}<br>

                            <strong>Company TIN:</strong>
                            {{ optional($company)->tin ?? optional($company)->TIN ?? optional($company)->tin_no ?? '119-505-887' }}<br>

                            <strong>Company VRN:</strong>
                            {{ optional($company)->vrn ?? '40-020836-I' }}
                        </div>
                    </td>

                    <td width="50%">
                        <div class="signature-box text-center">
                            <strong>Company Stamp / Approval</strong><br>

                            @if(optional($company)->signature)
                                <img src="{{ $safeAsset(optional($company)->signature) }}" class="signature-img" alt="Signature"><br>
                            @endif
                            @if(optional($company)->stamp)
                                <img src="{{ $safeAsset(optional($company)->stamp) }}" class="stamp-img" alt="Stamp"><br>
                            @endif

                            @if ($isApproved)
                                Approved By: {{ optional($delivery->approver)->name ?? optional($proforma->approver)->name ?? '-' }}<br>
                                Approved Date:
                                {{ optional($delivery->approved_at) ? \Carbon\Carbon::parse($delivery->approved_at)->format('Y-m-d H:i') : (optional($proforma->approved_at) ? \Carbon\Carbon::parse($proforma->approved_at)->format('Y-m-d H:i') : '-') }}<br>
                                Serial No: {{ optional($proforma)->accounting_transaction_group ?? optional($delivery)->accounting_transaction_group ?? '-' }}
                            @else
                                Pending approval
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

           <div class="footer-note">
                <strong>Thank you for your business!</strong><br>

                Should you have any enquiries concerning this invoice,
                please contact Managing Director on +255757851332, {{ optional($company)->district ?? 
              'GROUND FLOOR, NILE PLAZA BUILDING' }}<br>{{ optional($company)->city ?? 'Shinyanga Road Opposite Nyashishi Min Bus Stand' }}
                Tel: {{ optional($company)->phone_No ?? '+255756263287' }}
                Email: info@mbogomining.co.tz ,Web: www.mbogomining.co.tz
            </div>
        </div>

        <div class="doc-pack-paper">
            <img src="{{ asset('img/header.png') }}" alt="Company Header"
                style="width:100%;max-height:150px;object-fit:contain;">

            <div class="doc-title">DELIVERY NOTE</div>

            <table class="mini-table no-border">
                <tr>
                    <td width="60%">
                        <div class="header-bar">CUSTOMER DETAILS</div>
                        <div class="customer-box">
                            <strong>{{ optional($customer)->customer_name ?? '-' }}</strong><br>
                            {{ optional($customer)->address ?? '-' }}<br>
                            PHONE NO: {{ optional($customer)->phone ?? '-' }}<br>
                            @if ($tin)
                                TIN: {{ $tin }}<br>
                            @endif
                            @if ($vrn)
                                VRN: {{ $vrn }}<br>
                            @endif
                        </div>
                    </td>
                    <td width="40%">
                        <table class="mini-table">
                            <tr>
                                <td><strong>DATE</strong></td>
                                <td>{{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('F d, Y') : '-' }}</td>
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
                                <td>{{ optional($invoice)->invoice_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>PROFORMA #</strong></td>
                                <td>{{ optional($proforma)->proforma_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>STATUS</strong></td>
                                <td>{{ strtoupper($delivery->delivery_status ?? $delivery->approval_status ?? '-') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <table class="mini-table">
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
                            <td class="text-right">{{ number_format((float) ($item->quantity ?? $item->qty ?? 0), 2) }}</td>
                            <td>{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-right">{{ number_format((float) ($item->issued_qty ?? $item->quantity ?? 0), 2) }}</td>
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

            <table class="mini-table">
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

            <table class="mini-table no-border">
                <tr>
                    <td width="33%">
                        <strong>Prepared By</strong><br><br>
                        Name: {{ optional(auth()->user())->name ?? 'System' }}<br><br>
                        Signature: ______________________
                    </td>

                    <td width="34%" class="text-center">
                        <strong>Approved / Authorized</strong><br>
                        @if ($isApproved)
                            Name: {{ optional($delivery->approver)->name ?? '-' }}<br>
                            Date: {{ $delivery->approved_at ? \Carbon\Carbon::parse($delivery->approved_at)->format('Y-m-d H:i') : '-' }}<br>
                            @if (optional($company)->signature)
                                <img src="{{ $safeAsset(optional($company)->signature) }}" class="sign-img" alt="Signature">
                            @endif
                            <br>
                            @if (optional($company)->stamp)
                                <img src="{{ $safeAsset(optional($company)->stamp) }}" class="stamp-img" alt="Stamp">
                            @endif
                        @else
                            <br><span style="font-size:12px;color:#9a6700;">Signature and stamp will appear after delivery approval.</span>
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
                Tel: {{ optional($company)->phone_No ?? '+255756263287' }} | Email: info@mbogomining.co.tz | Web: www.mbogomining.co.tz
            </div>
        </div>

        <div class="doc-pack-paper">
            <img src="{{ asset('img/header.png') }}" alt="Company Header"
                style="width:100%;max-height:150px;object-fit:contain;">

            <div class="doc-title">WAYBILL</div>

            <table class="mini-table no-border">
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
                        <table class="mini-table">
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
                                <td>{{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('F d, Y') : '-' }}</td>
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

            <table class="mini-table">
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
                    <td>{{ $delivery->dispatch_date ? \Carbon\Carbon::parse($delivery->dispatch_date)->format('Y-m-d') : '-' }}</td>
                    <td><strong>Expected Delivery</strong></td>
                    <td>{{ $delivery->expected_delivery_date ? \Carbon\Carbon::parse($delivery->expected_delivery_date)->format('Y-m-d') : '-' }}</td>
                </tr>
            </table>

            <br>

            <table class="mini-table">
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
                            <td class="text-right">{{ number_format((float) ($item->quantity ?? $item->qty ?? 0), 2) }}</td>
                            <td>{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-right">{{ number_format((float) ($item->unit_price ?? $item->price ?? 0), 2) }}</td>
                            <td class="text-right">{{ number_format((float) ($item->total ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <br>

            <table class="mini-table no-border">
                <tr>
                    <td width="33%">
                        <strong>Security Officer</strong><br><br>
                        Name: ______________________<br><br>
                        ID: ________________________<br><br>
                        Signature: _________________<br><br>
                        Date: ______________________
                    </td>

                    <td width="34%" class="text-center">
                        <strong>Authorized By</strong><br>
                        @if ($isApproved)
                            {{ optional($delivery->approver)->name ?? '-' }}<br>
                            {{ $delivery->approved_at ? \Carbon\Carbon::parse($delivery->approved_at)->format('Y-m-d H:i') : '-' }}<br>
                            @if (optional($company)->signature)
                                <img src="{{ $safeAsset(optional($company)->signature) }}" class="sign-img" alt="Signature">
                            @endif
                            <br>
                            @if (optional($company)->stamp)
                                <img src="{{ $safeAsset(optional($company)->stamp) }}" class="stamp-img" alt="Stamp">
                            @endif
                        @else
                            <br><span style="font-size:12px;color:#9a6700;">Signature and stamp will appear after waybill approval.</span>
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

            @if ($hasExplosive)
                <div class="warning-box">
                    <div class="warning-title">WARNING / ONYO KWA USAFIRISHAJI WA BARUTI AU BIDHAA HATARISHI</div>
                    <ol style="margin-bottom:0;padding-left:20px;">
                        <li>Hairuhusiwi kusafirisha baruti kwenye vyombo vya abiria.</li>
                        <li>Hairuhusiwi kusafirisha baruti bila kibali cha serikali.</li>
                        <li>Hairuhusiwi kusafirisha baruti bila waybill ya kampuni.</li>
                        <li>Hairuhusiwi kusafirisha baruti bila blasting certificate pale inapohitajika.</li>
                        <li>Ukikamatwa bila vielelezo tajwa hapo juu, kampuni haitahusika na sheria itachukua mkondo wake.</li>
                    </ol>
                </div>
            @endif

            <div class="footer-note">
                <strong>Thank you for your business!</strong><br>
                {{ optional($company)->district ?? 'GROUND FLOOR, NILE PLAZA BUILDING' }}
                {{ optional($company)->city ?? 'Shinyanga Road Opposite Nyashishi Min Bus Stand' }}<br>
                Tel: {{ optional($company)->phone_No ?? '+255756263287' }} | Email: info@mbogomining.co.tz | Web: www.mbogomining.co.tz
            </div>
        </div>

        @foreach($receiptPayments as $index => $receipt)
            @php
                $receiptPath = $safeAsset($receipt->receipt_attachment);
            @endphp
            <div class="doc-pack-paper">
                <img src="{{ asset('img/header.png') }}" alt="Company Header"
                    style="width:100%;max-height:150px;object-fit:contain;">

                <div class="doc-title">{{ $receiptTitle($index) }}</div>

                <table class="mini-table no-border">
                    <tr>
                        <td width="60%">
                            <div class="header-bar">PAYMENT / RECEIPT DETAILS</div>
                            <div class="customer-box">
                                <strong>{{ optional($customer)->customer_name ?? '-' }}</strong><br>
                                {{ optional($customer)->address ?? '-' }}<br>
                                PHONE NO: {{ optional($customer)->phone ?? '-' }}<br>
                                @if ($tin)
                                    TIN: {{ $tin }}<br>
                                @endif
                                @if ($vrn)
                                    VRN: {{ $vrn }}<br>
                                @endif
                            </div>
                        </td>
                        <td width="40%">
                            <table class="mini-table">
                                <tr>
                                    <td><strong>DATE</strong></td>
                                    <td>{{ $formatDate($receipt->payment_date ?? $receipt->created_at, 'M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>RECEIPT #</strong></td>
                                    <td>{{ $receipt->receipt_no ?? $receipt->payment_no ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>METHOD</strong></td>
                                    <td>{{ ucfirst($receipt->payment_method ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ACCOUNT</strong></td>
                                    <td>{{ optional($receipt->paymentAccount)->SubCode ?? '' }}
                                        {{ optional($receipt->paymentAccount)->SubDescription ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>STATUS</strong></td>
                                    <td>{{ strtoupper($receipt->status ?? 'pending') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>AMOUNT</strong></td>
                                    <td>{{ $receipt->currency ?? 'TZS' }} {{ number_format((float) ($receipt->amount ?? 0), 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <div class="section-space"></div>
                    @if ($receiptPath)
                        <div class="receipt-card">
                            <strong>Receipt Attachment</strong><br><br>

                            @php
                                $extension = strtolower(pathinfo($receipt->receipt_attachment, PATHINFO_EXTENSION));
                            @endphp
                            @if(in_array($extension, ['jpg','jpeg','png','gif','webp']))
                                <img src="{{ $receiptPath }}"
                                    class="receipt-image"
                                    alt="Receipt Attachment">
                            @elseif($extension == 'pdf')
                                <embed
                                    src="{{ $receiptPath }}"
                                    type="application/pdf"
                                    width="100%"
                                    height="900px">
                            @else

                                <a href="{{ $receiptPath }}"
                                target="_blank"
                                class="btn btn-primary">
                                    Open Attached Receipt
                                </a>

                            @endif
                        </div>
                    @endif
                <table class="mini-table no-border">
                    <tr>
                        <td width="33%">
                            <strong>Prepared By</strong><br><br>
                            Name: {{ optional(auth()->user())->name ?? 'System' }}<br><br>
                            Signature: ______________________
                        </td>

                        <td width="34%" class="text-center">
                            <strong>Authorized By</strong><br>
                            @if ($receipt->status == 'approved')
                                {{ optional($receipt->approver)->name ?? '-' }}<br>
                                {{ $receipt->approved_at ? \Carbon\Carbon::parse($receipt->approved_at)->format('Y-m-d H:i') : '-' }}<br>
                                @if (optional($company)->signature)
                                    <img src="{{ $safeAsset(optional($company)->signature) }}" class="sign-img" alt="Signature">
                                @endif
                                <br>
                                @if (optional($company)->stamp)
                                    <img src="{{ $safeAsset(optional($company)->stamp) }}" class="stamp-img" alt="Stamp">
                                @endif
                            @else
                                <br><span style="font-size:12px;color:#9a6700;">Pending approval</span>
                            @endif
                        </td>

                        <td width="33%">
                            <strong>Verified / Received By</strong><br><br>
                            Name: ________________________<br><br>
                            Signature: ______________________<br><br>
                            Date: ___________________________
                        </td>
                    </tr>
                </table>

                <div class="footer-note">
                    <strong>Thank you for your business!</strong><br>
                    {{ optional($company)->district ?? 'GROUND FLOOR, NILE PLAZA BUILDING' }}
                    {{ optional($company)->city ?? 'Shinyanga Road Opposite Nyashishi Min Bus Stand' }}<br>
                    Tel: {{ optional($company)->phone_No ?? '+255756263287' }} | Email: info@mbogomining.co.tz | Web: www.mbogomining.co.tz
                </div>
            </div>

            
        @endforeach

    </div>
@endsection
