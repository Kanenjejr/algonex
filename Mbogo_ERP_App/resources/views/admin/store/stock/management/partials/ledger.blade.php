@extends('layouts.salesMaster')

@section('content')

<style>

    .ledger-card{
        border-radius:16px;
        border:none;
        color:#fff;
        padding:22px;
        margin-bottom:20px;
        box-shadow:0 4px 14px rgba(0,0,0,.08);
    }

    .ledger-blue{
        background:#1e3a8a;
    }

    .ledger-green{
        background:#047857;
    }

    .ledger-orange{
        background:#b45309;
    }

    .ledger-red{
        background:#be123c;
    }

    .ledger-card h2{
        font-size:34px;
        font-weight:800;
        margin-top:10px;
        margin-bottom:0;
        color:#fff;
    }

    .ledger-card h4{
        color:#fff;
        font-weight:700;
        margin:0;
    }

    .ibox{
        border-radius:16px;
        overflow:hidden;
        border:none;
        box-shadow:0 2px 10px rgba(0,0,0,.05);
    }

    .ibox-title{
        background:#fff;
        border-bottom:1px solid #edf2f7;
        padding:18px 20px;
    }

    .ibox-title h5{
        font-size:15px;
        font-weight:800;
        margin:0;
    }

    .ibox-content{
        padding:20px;
    }

    .erp-btn{
        border-radius:10px !important;
        font-weight:700 !important;
    }

    .table > thead > tr > th{
        background:#f8fafc;
        font-weight:800;
        color:#0f172a;
        border-bottom:1px solid #e5e7eb;
    }

    .low-stock-alert{
        border-radius:12px;
        padding:16px;
        background:#fff5f5;
        border-left:4px solid #ef4444;
        margin-bottom:15px;
    }

    .filter-box{
        background:#fff;
        border-radius:16px;
        padding:20px;
    }

</style>

<div class="wrapper wrapper-content animated fadeInRight">

    {{-- HEADER --}}
    <div class="row m-b-lg">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-content">

                    <div class="row">

                        <div class="col-md-8">
                            <h2 style="font-weight:800;">
                                <i class="fa fa-book text-primary"></i>
                                Stock Ledger Management
                            </h2>

                            <p class="text-muted" style="margin-top:10px;">
                                Inventory movement tracking, stock monitoring,
                                warehouse balance analysis and reporting system.
                            </p>
                        </div>

                        <div class="col-md-4 text-right">

                            <a href="{{ route('stock.export.excel') }}"
                               class="btn btn-success erp-btn">

                                <i class="fa fa-file-excel-o"></i>
                                Export Excel

                            </a>

                            <a href="{{ route('stock.export.pdf') }}"
                               class="btn btn-danger erp-btn">

                                <i class="fa fa-file-pdf-o"></i>
                                Export PDF

                            </a>

                            <button onclick="window.print()"
                                    class="btn btn-primary erp-btn">

                                <i class="fa fa-print"></i>
                                Print

                            </button>

                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- FILTER --}}
    <div class="ibox">

        <div class="ibox-title">
            <h5>
                <i class="fa fa-filter"></i>
                Filters
            </h5>
        </div>

        <div class="ibox-content">

            <form method="GET">

                <div class="row">

                    <div class="col-md-3">

                        <label>From Date</label>

                        <input type="date"
                               name="from"
                               class="form-control"
                               value="{{ request('from') }}">

                    </div>

                    <div class="col-md-3">

                        <label>To Date</label>

                        <input type="date"
                               name="to"
                               class="form-control"
                               value="{{ request('to') }}">

                    </div>

                    <div class="col-md-3">

                        <label>Product</label>

                        <select name="product_id"
                                class="form-control select2_demo_1">

                            <option value="">All Products</option>

                            @foreach($products as $p)

                                <option value="{{ $p->id }}"
                                    {{ request('product_id') == $p->id ? 'selected' : '' }}>

                                    {{ $p->product_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>Movement Type</label>

                        <select name="type"
                                class="form-control">

                            <option value="">All</option>

                            <option value="IN">
                                Stock In
                            </option>

                            <option value="OUT">
                                Stock Out
                            </option>

                            <option value="SALE">
                                Sales
                            </option>

                        </select>

                    </div>

                </div>

                <br>

                <button class="btn btn-primary erp-btn">

                    <i class="fa fa-search"></i>
                    Filter Report

                </button>

            </form>

        </div>

    </div>

    {{-- SUMMARY --}}
    <div class="row">

        <div class="col-lg-3">

            <div class="ledger-card ledger-blue">

                <h4>Total Stock In</h4>

                <h2>
                    {{ number_format($stats['total_in'] ?? 0,2) }}
                </h2>

            </div>

        </div>

        <div class="col-lg-3">

            <div class="ledger-card ledger-green">

                <h4>Total Stock Out</h4>

                <h2>
                    {{ number_format($stats['total_out'] ?? 0,2) }}
                </h2>

            </div>

        </div>

        <div class="col-lg-3">

            <div class="ledger-card ledger-orange">

                <h4>Available Balance</h4>

                <h2>
                    {{ number_format($stats['balance'] ?? 0,2) }}
                </h2>

            </div>

        </div>

        <div class="col-lg-3">

            <div class="ledger-card ledger-red">

                <h4>Low Stock Items</h4>

                <h2>
                    {{ collect($itemStocks)->where('total_available','<',10)->count() }}
                </h2>

            </div>

        </div>

    </div>

    {{-- LOW STOCK --}}
    <div class="row">

        @foreach($itemStocks as $item)

            @if($item->total_available < 10)

                <div class="col-md-3">

                    <div class="low-stock-alert">

                        <strong>
                            ⚠ Low Stock
                        </strong>

                        <br><br>

                        <strong>
                            {{ $item->product->product_name ?? '' }}
                        </strong>

                        <br>

                        Available:
                        {{ number_format($item->total_available,2) }}

                    </div>

                </div>

            @endif

        @endforeach

    </div>

    {{-- PRODUCT SUMMARY --}}
    <div class="ibox">

        <div class="ibox-title">

            <h5>
                Product Stock Summary
            </h5>

        </div>

        <div class="ibox-content">

            <div class="table-responsive">

                <table class="table table-bordered dataTables-example">

                    <thead>

                        <tr>

                            <th>Product</th>

                            <th>Received</th>

                            <th>Issued</th>

                            <th>Available</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($itemStocks as $item)

                            <tr>

                                <td>
                                    {{ $item->product->product_name ?? '' }}
                                </td>

                                <td class="text-success">

                                    {{ number_format($item->total_received,2) }}

                                </td>

                                <td class="text-danger">

                                    {{ number_format($item->total_used,2) }}

                                </td>

                                <td>

                                    <strong>

                                        {{ number_format($item->total_available,2) }}

                                    </strong>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- LEDGER HISTORY --}}
    <div class="ibox">

        <div class="ibox-title">

            <h5>
                Ledger History
            </h5>

        </div>

        <div class="ibox-content">

            <div class="table-responsive">

                <table class="table table-striped table-bordered dataTables-example">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Product</th>

                            <th>Stock In</th>

                            <th>Stock Out</th>

                            <th>Balance</th>

                            <th>Reference</th>

                            <th>Date</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($rows as $key => $row)

                            <tr>

                                <td>
                                    {{ $key + 1 }}
                                </td>

                                <td>
                                    {{ $row->product->product_name ?? '' }}
                                </td>

                                <td class="text-success">

                                    {{ number_format($row->qty_in,2) }}

                                </td>

                                <td class="text-danger">

                                    {{ number_format($row->qty_out,2) }}

                                </td>

                                <td>

                                    <strong>

                                        {{ number_format($row->balance,2) }}

                                    </strong>

                                </td>

                                <td>
                                    {{ $row->reference_type }}
                                </td>

                                <td>

                                    {{ $row->created_at->format('Y-m-d H:i') }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

    $(document).ready(function(){

        $('.select2_demo_1').select2({
            width:'100%'
        });

        $('.dataTables-example').DataTable({

            pageLength:25,

            responsive:true,

            dom: '<"html5buttons"B>lTfgitp',

            buttons: [

                {
                    extend:'copy'
                },

                {
                    extend:'csv'
                },

                {
                    extend:'excel',
                    title:'Stock Ledger'
                },

                {
                    extend:'pdf',
                    title:'Stock Ledger'
                },

                {
                    extend:'print'
                }

            ]

        });

    });

</script>

@endpush