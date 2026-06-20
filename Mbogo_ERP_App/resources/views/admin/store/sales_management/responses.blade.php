@extends('layouts.salesMaster')

@section('content')

<div class="wrapper wrapper-content">

    {{-- HEADER --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-12">
            <h2>Campaign Responses</h2>
        </div>
    </div>

    {{-- ================= SUMMARY ================= --}}
    <div class="row mt-3">

        <div class="col-md-4">
            <div class="card bg-primary text-white text-center p-3 shadow">
                <h5>Total Responses</h5>
                <h3>{{ count($data) }}</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white text-center p-3 shadow">
                <h5>Positive Responses</h5>
                <h3>{{ $data->where('response','like','%yes%')->count() }}</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-danger text-white text-center p-3 shadow">
                <h5>Negative Responses</h5>
                <h3>{{ $data->where('response','like','%no%')->count() }}</h3>
            </div>
        </div>

    </div>

    {{-- ================= TABLE ================= --}}
    <div class="ibox mt-4">
        <div class="ibox-title bg-warning">
            <h5>Campaign Responses List</h5>
        </div>

        <div class="ibox-content">

            <table class="table table-striped table-bordered dataTables-example">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Campaign</th>
                        <th>Customer</th>
                        <th>Response</th>
                        <th>Date</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($data as $k => $r)
                    <tr>
                        <td>{{ $k+1 }}</td>

                        <td>{{ $r->campaign->name ?? '-' }}</td>

                        <td>{{ $r->customer->name ?? '-' }}</td>

                        <td>
                            @if(strtolower($r->response) == 'yes')
                                <span class="badge badge-success">Yes</span>
                            @elseif(strtolower($r->response) == 'no')
                                <span class="badge badge-danger">No</span>
                            @else
                                <span class="badge badge-secondary">{{ $r->response }}</span>
                            @endif
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}
                        </td>

                        <td>
                            <a href="{{ route('responses.show', $r->id) }}"
                               class="btn btn-xs btn-info">
                               <i class="fa fa-eye"></i>
                            </a>

                            <a href="{{ route('responses.delete', $r->id) }}"
                               onclick="return confirm('Delete this response?')"
                               class="btn btn-xs btn-danger">
                               <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            No Responses Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection