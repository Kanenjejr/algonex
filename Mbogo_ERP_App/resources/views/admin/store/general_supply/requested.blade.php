@extends('layouts.AdminMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-title bg-warning">
                    <h5>Requested Items</h5>
                </div>

                <div class="ibox-content">

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($data as $k => $r)
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $r->item_name ?? 'N/A' }}</td>
                                <td>{{ $r->qty ?? 0 }}</td>
                                <td>
                                    <span class="label label-primary">
                                        Pending
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    No Requested Items Found
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