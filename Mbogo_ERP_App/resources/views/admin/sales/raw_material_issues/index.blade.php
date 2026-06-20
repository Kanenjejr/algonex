@extends('layouts.AdminMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-title bg-danger">
                    <h5>Raw Material Issues</h5>
                </div>

                <div class="ibox-content">

                    <a class="btn btn-primary mb-3">
                        <i class="fa fa-plus"></i> Issue Raw Material
                    </a>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Issue No</th>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Issued To</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($data as $k => $r)
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $r->issue_no ?? 'N/A' }}</td>
                                <td>{{ $r->product->product_name ?? '' }}</td>
                                <td>{{ $r->qty ?? 0 }}</td>
                                <td>{{ $r->issued_to ?? '' }}</td>
                                <td>{{ $r->created_at ?? '' }}</td>
                                <td>
                                    <span class="label label-primary">Issued</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No Raw Material Issues Found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection