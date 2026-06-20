@extends('layouts.salesMaster')

@section('content')

@can('View-Customer-Ledger')

<div class="wrapper wrapper-content animated fadeInRight">

    {{-- PAGE HEADER --}}
    <div class="row wrapper border-bottom white-bg page-heading">

        <div class="col-lg-10">

            <h2 style="
                font-weight:700;
                color:#1f2937;
            ">
                Customer Ledger
            </h2>

            <ol class="breadcrumb">

                <li>
                    <a href="#">
                        Sales Management
                    </a>
                </li>

                <li class="active">
                    <strong>
                        Customer Ledger
                    </strong>
                </li>

            </ol>

        </div>

    </div>

    {{-- SUMMARY CARDS --}}
    <div class="row mt-4">

        {{-- TOTAL INVOICE --}}
        <div class="col-lg-3">

            <div class="ibox"
                 style="
                    border-radius:14px;
                    overflow:hidden;
                    box-shadow:0 2px 12px rgba(0,0,0,.08);
                 ">

                <div class="ibox-content"
                     style="
                        background:linear-gradient(135deg,#1c84c6,#23c6c8);
                        color:white;
                        padding:25px;
                     ">

                    <div class="row">

                        <div class="col-xs-8">

                            <h5 style="
                                color:white;
                                font-weight:600;
                            ">
                                Total Invoice
                            </h5>

                            <h2 style="
                                font-weight:700;
                                margin-top:10px;
                                color:white;
                            ">

                                {{ number_format($totalInvoice ?? 0,2) }}

                            </h2>

                        </div>

                        <div class="col-xs-4 text-right">

                            <i class="fa fa-file-text-o"
                               style="
                                    font-size:45px;
                                    opacity:.25;
                               "></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- TOTAL PAID --}}
        <div class="col-lg-3">

            <div class="ibox"
                 style="
                    border-radius:14px;
                    overflow:hidden;
                    box-shadow:0 2px 12px rgba(0,0,0,.08);
                 ">

                <div class="ibox-content"
                     style="
                        background:linear-gradient(135deg,#1ab394,#18a689);
                        color:white;
                        padding:25px;
                     ">

                    <div class="row">

                        <div class="col-xs-8">

                            <h5 style="
                                color:white;
                                font-weight:600;
                            ">
                                Total Paid
                            </h5>

                            <h2 style="
                                font-weight:700;
                                margin-top:10px;
                                color:white;
                            ">

                                {{ number_format($totalPaid ?? 0,2) }}

                            </h2>

                        </div>

                        <div class="col-xs-4 text-right">

                            <i class="fa fa-money"
                               style="
                                    font-size:45px;
                                    opacity:.25;
                               "></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- TOTAL BALANCE --}}
        <div class="col-lg-3">

            <div class="ibox"
                 style="
                    border-radius:14px;
                    overflow:hidden;
                    box-shadow:0 2px 12px rgba(0,0,0,.08);
                 ">

                <div class="ibox-content"
                     style="
                        background:linear-gradient(135deg,#ed5565,#dc3545);
                        color:white;
                        padding:25px;
                     ">

                    <div class="row">

                        <div class="col-xs-8">

                            <h5 style="
                                color:white;
                                font-weight:600;
                            ">
                                Outstanding Balance
                            </h5>

                            <h2 style="
                                font-weight:700;
                                margin-top:10px;
                                color:white;
                            ">

                                {{ number_format($totalBalance ?? 0,2) }}

                            </h2>

                        </div>

                        <div class="col-xs-4 text-right">

                            <i class="fa fa-warning"
                               style="
                                    font-size:45px;
                                    opacity:.25;
                               "></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- UNPAID CUSTOMERS --}}
        <div class="col-lg-3">

            <div class="ibox"
                 style="
                    border-radius:14px;
                    overflow:hidden;
                    box-shadow:0 2px 12px rgba(0,0,0,.08);
                 ">

                <div class="ibox-content"
                     style="
                        background:linear-gradient(135deg,#f8ac59,#f39c12);
                        color:white;
                        padding:25px;
                     ">

                    <div class="row">

                        <div class="col-xs-8">

                            <h5 style="
                                color:white;
                                font-weight:600;
                            ">
                                Unpaid Customers
                            </h5>

                            <h2 style="
                                font-weight:700;
                                margin-top:10px;
                                color:white;
                            ">

                                {{ $unpaidCustomers ?? 0 }}

                            </h2>

                        </div>

                        <div class="col-xs-4 text-right">

                            <i class="fa fa-users"
                               style="
                                    font-size:45px;
                                    opacity:.25;
                               "></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- LEDGER TABLE --}}
    <div class="ibox mt-4"
         style="
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,.08);
         ">

        {{-- HEADER --}}
        <div class="ibox-title"
             style="
                background:#ffffff;
                border-bottom:1px solid #e5e7eb;
                padding:18px 20px;
             ">

            <div class="row">

                <div class="col-md-6">

                    <h4 style="
                        color:#1f2937;
                        font-weight:700;
                        margin-top:5px;
                    ">
                        Customer Financial Transactions
                    </h4>

                </div>

                <div class="col-md-6 text-right">

                    @can('Export-Customer-Ledger')

                    <a href="{{ route('sales.customer.ledger.export.excel') }}"
                       class="btn btn-success btn-sm"
                       style="
                            border-radius:8px;
                            font-weight:600;
                            padding:8px 14px;
                       ">

                        <i class="fa fa-file-excel-o"></i>

                        Export Excel

                    </a>

                    @endcan

                    @can('Print-Customer-Ledger')

                    <a href="{{ route('sales.customer.ledger.print') }}"
                       target="_blank"
                       class="btn btn-primary btn-sm"
                       style="
                            border-radius:8px;
                            font-weight:600;
                            padding:8px 14px;
                       ">

                        <i class="fa fa-print"></i>

                        Print

                    </a>

                    @endcan

                </div>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="ibox-content"
             style="
                background:white;
                padding:20px;
             ">

            <div class="table-responsive">

                <table class="table table-hover table-bordered dataTables-example"
                       style="
                            width:100%;
                            margin-bottom:0;
                       ">

                    <thead>

                        <tr
                            style="
                                background:#1c84c6 !important;
                            ">

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                #
                            </th>

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                Customer Code
                            </th>

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                Customer Name
                            </th>

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                Invoice Amount
                            </th>

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                Paid Amount
                            </th>

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                Balance
                            </th>

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                Status
                            </th>

                            <th style="
                                color:#ffffff !important;
                                background:#1c84c6 !important;
                                font-weight:700 !important;
                                border-color:#1976b2 !important;
                                vertical-align:middle;
                            ">
                                Date
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($ledgers as $k => $ledger)

                        <tr>

                            <td>
                                {{ $k + 1 }}
                            </td>

                            <td>

                                <span class="label label-info"
                                      style="
                                            font-size:12px;
                                            padding:6px 10px;
                                      ">

                                    {{ $ledger->customer->customer_code ?? '-' }}

                                </span>

                            </td>

                            <td style="
                                font-weight:600;
                                color:#1f2937;
                            ">

                                {{ $ledger->customer->customer_name ?? '-' }}

                            </td>

                            <td class="text-right"
                                style="
                                    color:#1c84c6;
                                    font-weight:700;
                                ">

                                {{ number_format($ledger->invoice_amount ?? 0,2) }}

                            </td>

                            <td class="text-right"
                                style="
                                    color:#1ab394;
                                    font-weight:700;
                                ">

                                {{ number_format($ledger->paid_amount ?? 0,2) }}

                            </td>

                            <td class="text-right"
                                style="
                                    color:#ed5565;
                                    font-weight:700;
                                ">

                                {{ number_format($ledger->balance ?? 0,2) }}

                            </td>

                            <td>

                                @if(($ledger->balance ?? 0) <= 0)

                                    <span class="label label-primary">
                                        Paid
                                    </span>

                                @elseif(($ledger->paid_amount ?? 0) > 0)

                                    <span class="label label-warning">
                                        Partial
                                    </span>

                                @else

                                    <span class="label label-danger">
                                        Unpaid
                                    </span>

                                @endif

                            </td>

                            <td style="
                                color:#6b7280;
                                font-weight:600;
                            ">

                                {{ optional($ledger->created_at)->format('d M Y') }}

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="8"
                                class="text-center text-muted"
                                style="
                                    padding:25px;
                                    font-size:15px;
                                ">

                                No customer ledger transactions found

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endcan

@endsection