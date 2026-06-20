@extends('layouts.salesMaster')

@section('content')

<div class="wrapper wrapper-content">

    {{-- HEADER --}}
    <div class="ibox">
        <div class="ibox-title bg-danger">
            <h5>Credit Notes (Returns & Adjustments)</h5>
        </div>

        <div class="ibox-content">

            {{-- FILTERS --}}
            <form method="GET">
                <div class="row">

                    <div class="col-md-3">
                        <input type="date" name="from" class="form-control" placeholder="From Date">
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="to" class="form-control" placeholder="To Date">
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="posted">Posted</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-danger btn-block">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="row mb-3">

        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-content text-center">
                    <h4>{{ number_format($totalCredit ?? 0,2) }}</h4>
                    <small>Total Credit Issued</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-content text-center">
                    <h4>{{ $count ?? 0 }}</h4>
                    <small>Total Credit Notes</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-content text-center">
                    <h4>{{ $pending ?? 0 }}</h4>
                    <small>Pending Approval</small>
                </div>
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="ibox">
        <div class="ibox-content">

            <div class="text-right mb-3">
                <a href="#" class="btn btn-danger">
                    <i class="fa fa-plus"></i> Create Credit Note
                </a>
            </div>

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Credit No</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($data ?? [] as $k => $c)
                    <tr>

                        <td>{{ $k+1 }}</td>

                        <td>
                            <strong>{{ $c->credit_no }}</strong>
                        </td>

                        <td>{{ $c->invoice->invoice_no ?? '-' }}</td>

                        <td>{{ $c->customer->name ?? '-' }}</td>

                        <td>{{ $c->reason }}</td>

                        <td class="text-danger">
                            {{ number_format($c->amount,2) }}
                        </td>

                        <td>
                            <span class="label label-{{ 
                                $c->status == 'posted' ? 'primary' : 
                                ($c->status == 'approved' ? 'success' : 'warning') 
                            }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>

                        <td>{{ $c->created_at }}</td>

                        <td>
                            <button class="btn btn-xs btn-info">View</button>

                            @if($c->status == 'draft')
                                <button class="btn btn-xs btn-success">Approve</button>
                            @endif

                            @if($c->status == 'approved')
                                <button class="btn btn-xs btn-primary">Post</button>
                            @endif
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="9" class="text-center">No Credit Notes Found</td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection