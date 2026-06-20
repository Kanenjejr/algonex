<!DOCTYPE html>
<html>

<head>

    <title>
        Customer Ledger
    </title>

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <style>

        body{
            font-family:Arial, Helvetica, sans-serif;
            font-size:12px;
            color:#222;
            padding:20px;
        }

        .header-image{
            width:100%;
            margin-bottom:20px;
        }

        .header-image img{
            width:100%;
            height:auto;
        }

        .report-title{
            margin-bottom:25px;
        }

        .report-title h2{
            margin:0;
            font-weight:700;
            color:#1c84c6;
        }

        .report-title p{
            margin-top:5px;
            color:#666;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th{
            background:#1c84c6 !important;
            color:white !important;
            padding:10px;
            border:1px solid #ddd;
            font-size:12px;
            text-align:center;
        }

        table td{
            padding:8px;
            border:1px solid #ddd;
        }

        .text-right{
            text-align:right;
        }

        .text-center{
            text-align:center;
        }

        .paid{
            color:green;
            font-weight:700;
        }

        .partial{
            color:orange;
            font-weight:700;
        }

        .unpaid{
            color:red;
            font-weight:700;
        }

        .footer{
            margin-top:30px;
            text-align:right;
            color:#777;
            font-size:11px;
        }

        .print-btn{
            margin-bottom:20px;
        }

        @media print {

            .no-print{
                display:none;
            }

            body{
                padding:0;
            }

        }

    </style>

</head>

<body>

    {{-- COMPANY HEADER IMAGE --}}
    <div class="header-image">

        <img src="{{ asset('img/header.png') }}"
             alt="Company Header">

    </div>

    {{-- REPORT TITLE --}}
    <div class="report-title">

        <h2>
            Customer Ledger Report
        </h2>

        <p>

            Printed On:
            {{ now()->format('d M Y H:i A') }}

        </p>

    </div>

    {{-- PRINT BUTTON --}}
    <div class="no-print print-btn">

        <button onclick="window.print()"
                class="btn btn-primary">

            <i class="fa fa-print"></i>

            Print Report

        </button>

    </div>

    {{-- TABLE --}}
    <table>

        <thead>

            <tr>

                <th>#</th>

                <th>
                    Customer Code
                </th>

                <th>
                    Customer Name
                </th>

                <th>
                    Invoice Amount
                </th>

                <th>
                    Paid Amount
                </th>

                <th>
                    Balance
                </th>

                <th>
                    Status
                </th>

                <th>
                    Date
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($ledgers as $k => $ledger)

            <tr>

                <td class="text-center">
                    {{ $k + 1 }}
                </td>

                <td>
                    {{ $ledger->customer->customer_code ?? '-' }}
                </td>

                <td>
                    {{ $ledger->customer->customer_name ?? '-' }}
                </td>

                <td class="text-right">

                    {{ number_format($ledger->invoice_amount ?? 0,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($ledger->paid_amount ?? 0,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($ledger->balance ?? 0,2) }}

                </td>

                <td class="text-center">

                    @if(($ledger->balance ?? 0) <= 0)

                        <span class="paid">
                            Paid
                        </span>

                    @elseif(($ledger->paid_amount ?? 0) > 0)

                        <span class="partial">
                            Partial
                        </span>

                    @else

                        <span class="unpaid">
                            Unpaid
                        </span>

                    @endif

                </td>

                <td class="text-center">

                    {{ optional($ledger->created_at)->format('d M Y') }}

                </td>

            </tr>

            @empty

            <tr>

                <td colspan="8"
                    class="text-center">

                    No customer ledger records found

                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

    {{-- FOOTER --}}
    <div class="footer">

        Generated by Mbogo Mining and general Supply limited

    </div>

</body>

</html>