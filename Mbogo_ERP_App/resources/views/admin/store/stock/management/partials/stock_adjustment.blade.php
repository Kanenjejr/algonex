@extends('layouts.AdminMaster')

@section('content')

<div class="wrapper wrapper-content">

    {{--  PAGE HEADER --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8">
            <h2>Stock Adjustment Management</h2>
            <ol class="breadcrumb">
                <li><a href="#">Stock</a></li>
                <li class="active"><strong>Adjustment</strong></li>
            </ol>
        </div>
    </div>

    {{--  ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif


    {{--  ADJUSTMENT FORM --}}
    <div class="row mt-3">
        <div class="col-md-8 offset-md-2">

            <div class="ibox">
                <div class="ibox-title bg-warning">
                    <h5>Adjust Stock (Loss / Gain)</h5>
                </div>

                <div class="ibox-content">

                    <form method="POST" action="{{ route('stock.adjust.save') }}">
                        @csrf

                        <div class="row">

                            {{-- PRODUCT --}}
                            <div class="col-md-6">
                                <label>Product</label>
                                <select name="product_id" class="form-control" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- TYPE --}}
                            <div class="col-md-6">
                                <label>Adjustment Type</label>
                                <select name="type" class="form-control" required>
                                    <option value="loss">Loss (Damaged / Missing)</option>
                                    <option value="gain">Gain (Extra / Found)</option>
                                </select>
                            </div>

                        </div>

                        <div class="row mt-3">

                            {{-- QTY --}}
                            <div class="col-md-6">
                                <label>Quantity</label>
                                <input type="number" name="qty" class="form-control" required>
                            </div>

                            {{-- REASON --}}
                            <div class="col-md-6">
                                <label>Reason</label>
                                <input type="text" name="reason" class="form-control" placeholder="Optional reason">
                            </div>

                        </div>

                        <div class="mt-3">
                            <button class="btn btn-warning btn-block">
                                <i class="fa fa-exchange"></i> Adjust Stock
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>


    {{-- ADJUSTMENT HISTORY (VERY IMPORTANT ERP FEATURE) --}}
    <div class="row">
        <div class="col-md-12">

            <div class="ibox">
                <div class="ibox-title">
                    <h5>Adjustment History</h5>
                </div>

                <div class="ibox-content">

            <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Date</th>
                                <th>Reason</th>
                            </tr>
                        </thead>

                    <tbody>
                        @foreach($adjustments as $key => $adj)
                        <tr>
                            <td>{{ $key+1 }}</td>

                            <td>{{ $adj->product->product_name ?? '' }}</td>

                            <td>
                                <span class="label label-{{ $adj->type == 'loss' ? 'danger' : 'success' }}">
                                    {{ ucfirst($adj->type) }}
                                </span>
                            </td>

                            <td>{{ number_format($adj->qty,2) }}</td>

                            <td>{{ $adj->created_at }}</td>

                            <td>{{ $adj->reason ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>

                    </table>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection