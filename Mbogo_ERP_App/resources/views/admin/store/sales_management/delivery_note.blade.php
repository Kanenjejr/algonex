<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
        }

        .container {
            width: 100%;
            margin: auto;
        }

        .header img {
            width: 100%;
            max-height: 120px;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            background: #0b1a78;
            color: #fff;
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid #000;
            padding: 5px;
        }

        .no-border td {
            border: none;
        }

        .section-title {
            background: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .signatures td {
            height: 70px;
            vertical-align: bottom;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="container">

    {{-- HEADER --}}
    <div class="header">
        <img src="{{ public_path('img/header.png') }}">
    </div>

    <div class="title">
        DELIVERY NOTE
    </div>

    {{-- CUSTOMER + DELIVERY --}}
    <table class="no-border">
        <tr>
            <td width="60%">
                <strong>Customer:</strong><br>
                {{ optional($delivery->order->customer)->customer_name }}
            </td>

            <td width="40%">
                <strong>Delivery No:</strong> {{ $delivery->delivery_no }}<br>
                <strong>Date:</strong> {{ $delivery->delivery_date }}<br>
                <strong>Invoice:</strong> {{ optional($delivery->order)->invoice_no ?? '-' }}
            </td>
        </tr>
    </table>

    <br>

    {{-- ITEMS --}}
    <table>
        <tr class="section-title">
            <th>#</th>
            <th>Product</th>
            <th>Qty</th>
        </tr>

        @foreach($delivery->items as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->product->name ?? 'Product' }}</td>
            <td class="text-right">{{ number_format($item->quantity,2) }}</td>
        </tr>
        @endforeach
    </table>

    <br>

    {{-- TRANSPORT DETAILS --}}
    <table>
        <tr class="section-title">
            <td colspan="2">Transport Details</td>
        </tr>

        <tr>
            <td>Driver</td>
            <td>{{ $delivery->driver_name }}</td>
        </tr>

        <tr>
            <td>Vehicle</td>
            <td>{{ $delivery->vehicle_no }}</td>
        </tr>

        <tr>
            <td>Route</td>
            <td>{{ $delivery->origin }} → {{ $delivery->destination }}</td>
        </tr>

        <tr>
            <td>Transport Mode</td>
            <td>{{ $delivery->transport_mode }}</td>
        </tr>
    </table>

      {{-- POD SECTION --}}
        <br>

        <table>
            <tr class="section-title">
                <td colspan="2">Proof of Delivery</td>
            </tr>

            <tr>
                <td>Receiver Name</td>
                <td>{{ $delivery->receiver_name ?? '-' }}</td>
            </tr>

            <tr>
                <td>Delivered At</td>
                <td>{{ $delivery->delivered_at ?? '-' }}</td>
            </tr>
            <tr>
                <td>Signature</td>
                <td>
                    @if($delivery->receiver_signature)
                        <img src="{{ $delivery->receiver_signature }}" width="120">
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>
     <br>
    {{--  EXPLOSIVES COMPLIANCE --}}
    <table>
        <tr class="section-title">
            <td colspan="2">Explosives Compliance</td>
        </tr>

        <tr>
            <td>Permit No</td>
            <td>{{ $delivery->permit_no ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td>Storage Type</td>
            <td>{{ $delivery->storage_type ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td>Approved Qty</td>
            <td>{{ $delivery->approved_qty ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td>Safety Officer</td>
            <td>{{ $delivery->safety_officer ?? 'N/A' }}</td>
        </tr>
    </table>

    <br><br>

    {{-- SIGNATURES --}}
    <table class="signatures">
        <tr>
            <td>Prepared By</td>
            <td>Delivered By</td>
            <td>Received By</td>
        </tr>

        <tr>
            <td>{{ optional($delivery->creator)->name }}</td>
            <td>{{ $delivery->driver_name }}</td>
            <td>________________</td>
        </tr>
    </table>

</div>

</body>
</html>