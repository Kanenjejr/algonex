@extends('layouts.AdminMaster')
@section('content')

<div class="wrapper wrapper-content animated fadeInRight">

    {{-- PAGE HEADER --}}
    <div class="row border-bottom white-bg page-heading mb-4" style="padding:20px;">
        <div class="col-lg-6">
            <h2>Location Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('business-admin') }}">Business Administration</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Location Registration</strong>
                </li>
            </ol>
        </div>

        <div class="col-lg-6 text-right" style="padding-top:20px;">
            @can('Register-WorkPoint')
                <button type="button"
                        class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#varyModal">
                    <i class="fa fa-plus"></i> Add Sales Point
                </button>
            @endcan
        </div>
    </div>

    {{-- TABLE --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title bg-primary text-white">
                    <h5>Location Table</h5>
                </div>

                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Company Name</th>
                                    <th>Business Unit</th>
                                    <th>Location Code</th>
                                    <th>Location Name</th>
                                    <th>City</th>
                                    <th>District</th>
                                    <th>Sales Point</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workPoints as $key => $w)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ optional($w->company)->company_name ?? '-' }}</td>
                                    <td>{{ optional($w->comp_unit)->unit_name ?? '-' }}</td>
                                    <td>{{ $w->work_code }}</td>
                                    <td>{{ $w->work_name }}</td>
                                    <td>{{ $w->city }}</td>
                                    <td>{{ $w->district }}</td>
                                    <td>{{ $w->location }}</td>
                                    <td>{{ $w->phone_No }}</td>
                                    <td>
                                        <span class="badge badge-{{ $w->status == 'Active' ? 'success' : 'danger' }}">
                                            {{ $w->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @can('Edit-Location')
                                            <button class="btn btn-warning btn-sm"
                                                data-toggle="modal"
                                                data-target="#wpEditModal">
                                                Edit
                                            </button>
                                        @endcan

                                        @can('Delete-Location')
                                            <a href="javascript:void(0)"
                                               class="btn btn-danger btn-sm">
                                                Remove
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">No data available in table</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ADD SALES POINT MODAL --}}
<div class="modal fade" id="varyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('workpoint.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title">Add Sales Point</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Company Name</label>
                        <select name="company_id" class="form-control" required>
                            <option value="">-- Select Company --</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Business Unit</label>
                        <select name="comp_unit_id" class="form-control" required>
                            <option value="">-- Select Unit --</option>
                            @foreach($unities as $u)
                                <option value="{{ $u->id }}">{{ $u->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Location Code</label>
                        <input type="text" name="work_code" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Location Name</label>
                        <input type="text" name="work_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>District</label>
                        <input type="text" name="district" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>SalesPoint/Location</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone_No" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Save Sales Point
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection