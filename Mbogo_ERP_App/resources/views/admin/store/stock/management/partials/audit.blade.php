@extends('layouts.AdminMaster')

@section('content')

<div class="wrapper wrapper-content">

    {{-- HEADER --}}
    <div class="row">
        <div class="col-lg-12">
            <h2>Stock Audit Management</h2>
        </div>
    </div>

    {{--  ALERTS --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{--  SUMMARY CARDS (MODERN UI) --}}
<div class="row">

    <div class="col-md-3">
        <div class="ibox shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="ibox-content text-center" style="background: linear-gradient(45deg,#1ab394,#18a689); color:white;">
                <h5>Total Audits</h5>
                <h2 class="font-bold">{{ $audits->count() }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="ibox shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="ibox-content text-center" style="background: linear-gradient(45deg,#f8ac59,#f39c12); color:white;">
                <h5>Pending</h5>
                <h2 class="font-bold">{{ $audits->where('status','pending')->count() }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="ibox shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="ibox-content text-center" style="background: linear-gradient(45deg,#1c84c6,#23c6c8); color:white;">
                <h5>Approved</h5>
                <h2 class="font-bold">{{ $audits->where('status','approved')->count() }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="ibox shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="ibox-content text-center" style="background: linear-gradient(45deg,#ed5565,#e74c3c); color:white;">
                <h5>Total Variance</h5>
                <h2 class="font-bold">
                    {{ number_format($audits->sum(fn($a) => $a->variance())) }}
                </h2>
            </div>
        </div>
    </div>
 </div>
    {{--  MAIN TABLE --}}
    <div class="ibox">
        <div class="ibox-title">
            <h5>Audit List</h5>
        </div>
        <div class="ibox-content">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Audit No</th>
                        <th>Date</th>
                        <th>Company</th>
                        <th>location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Total System</th>
                        <th>Total Counted</th>
                        <th>Variance</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $key => $audit)
                        <tr>

                            <td>{{ $key + 1 }}</td>

                            <td><strong>#{{ $audit->id }}</strong></td>

                            <td>{{ $audit->audit_date }}</td>

                            <td>{{ $audit->company->name ?? '' }}</td>

                            <td>{{ $audit->wlocation->name ?? '' }}</td>

                            <td>{{ ucfirst($audit->audit_type) }}</td>

                            <td>
                                <span class="label label-{{ 
                                    $audit->status == 'approved' ? 'success' :
                                    ($audit->status == 'pending' ? 'warning' : 'danger')
                                }}">
                                    {{ $audit->status_label }}
                                </span>
                            </td>

                            <td>{{ number_format($audit->totalSystem()) }}</td>

                            <td>{{ number_format($audit->totalCounted()) }}</td>

                            <td>
                                <strong class="{{ $audit->variance() < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($audit->variance()) }}
                                </strong>
                            </td>

                            <td>
                                <button class="btn btn-info btn-sm"
                                        data-toggle="collapse"
                                        data-target="#audit{{ $audit->id }}">
                                    View
                                </button>
                            </td>

                        </tr>

                        {{--  DETAILS (ERP LEVEL) --}}
                        <tr id="audit{{ $audit->id }}" class="collapse">
                            <td colspan="11">

                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Account Code</th>
                                            <th>System Qty</th>
                                            <th>Counted Qty</th>
                                            <th>Variance</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($audit->items as $item)
                                            <tr>

                                                <td>{{ $item->product->product_name ?? '' }}</td>

                                                {{--  ACCOUNT CODE --}}
                                                <td>
                                                    <span class="label label-primary">
                                                        {{ $item->product->account_code ?? 'N/A' }}
                                                    </span>
                                                </td>

                                                <td>{{ $item->system_qty }}</td>
                                                <td>{{ $item->counted_qty }}</td>

                                                <td>
                                                    <strong class="{{ ($item->counted_qty - $item->system_qty) < 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ $item->counted_qty - $item->system_qty }}
                                                    </strong>
                                                </td>

                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>

                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="11" class="text-center">No Audit Records Found</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>

@endsection