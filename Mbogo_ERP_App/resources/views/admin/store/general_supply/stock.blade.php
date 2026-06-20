@extends('layouts.AdminMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="ibox">
        <div class="ibox-title bg-primary">
            <h5>General Supply Stock</h5>
        </div>

        <div class="ibox-content">

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Total In</th>
                        <th>Total Out</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($itemStocks as $k => $s)
                    <tr>
                        <td>{{ $k+1 }}</td>
                        <td>{{ $s->item_name ?? 'N/A' }}</td>
                        <td>{{ $s->total_in }}</td>
                        <td>{{ $s->total_out }}</td>
                        <td>{{ $s->available }}</td>
                        <td>
                            @if($s->available > 0)
                                <span class="label label-primary">In Stock</span>
                            @else
                                <span class="label label-danger">Out of Stock</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No Stock Data</td>
                    </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection