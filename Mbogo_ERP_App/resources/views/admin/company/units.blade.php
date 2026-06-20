@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Business Unit Information</h2>
        <ol class="breadcrumb" style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('business-admin') }}">Business Administration</a>
            </li>
            <li class="active">
                <strong>Business Unit Registration</strong>
            </li>
        </ol>
    </div>

    <div class="col-lg-2">
        <h2>Current Date</h2>
        <ol class="breadcrumb">
            <li class="active">
                <strong>{{ \Carbon\Carbon::now()->format('l, Y-m-d') }}</strong>
            </li>
        </ol>
    </div>

    <div class="col-lg-2">
        <h2>Time</h2>
        <ol class="breadcrumb">
            <li class="active">
                <strong>
                    <span id="liveTime" style="color:green;"></span>
                </strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    {{-- top actions --}}
    <div class="row mb-3">
        <div class="col-lg-6">
            <h3>Business Units Information</h3>
        </div>
        <div class="col-lg-6 text-right">
            @can('Register-Company-Unit')
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#unitAddModal">
                    <i class="fa fa-plus"></i> Add Company Unit
                </button>
            @endcan
        </div>
    </div>

    {{-- table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title bg-success">
                    <h5>Business Units Table</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>

                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Business Name</th>
                                    <th>Business Code</th>
                                    <th>City</th>
                                    <th>District</th>
                                    <th>Street/Location</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($units as $key => $u)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $u->unit_name }}</td>
                                        <td>{{ $u->unit_code }}</td>
                                        <td>{{ $u->city }}</td>
                                        <td>{{ $u->district }}</td>
                                        <td>{{ $u->location }}</td>
                                        <td>{{ $u->phone_No }}</td>
                                        <td>
                                            <span class="label label-{{ $u->status == 'Active' ? 'primary' : 'danger' }}">
                                                {{ $u->status ?? 'Active' }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('Edit-Company-Unit')
                                                <button
                                                    class="btn btn-warning btn-sm btn-edit-unit"
                                                    data-toggle="modal"
                                                    data-target="#unitEditModal"
                                                    data-id="{{ encrypt($u->id) }}"
                                                    data-company_id="{{ $u->company_id }}"
                                                    data-unit_code="{{ $u->unit_code }}"
                                                    data-unit_name="{{ $u->unit_name }}"
                                                    data-city="{{ $u->city }}"
                                                    data-district="{{ $u->district }}"
                                                    data-location="{{ $u->location }}"
                                                    data-phone="{{ $u->phone_No }}"
                                                    data-status="{{ $u->status }}">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>
                                            @endcan

                                            @can('Delete-Business-Unit')
                                                <a href="{{ url('/admin/company-units/remove/'.encrypt($u->id)) }}"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Remove this business unit?')">
                                                    <i class="fa fa-trash"></i> Remove
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="unitAddModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('companyunit.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Add Business Unit</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Company Name</label>
                        <select name="company_id" class="form-control" required>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Business Code</label>
                        <input type="text" name="unit_code" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Business Name</label>
                        <input type="text" name="unit_name" class="form-control" required>
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
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone_No" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="unitEditModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="unitEditForm" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-content">
                <div class="modal-header">
                    <h4>Edit Business Unit</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Company Name</label>
                        <select id="unit_company_id" name="company_id" class="form-control">
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Business Code</label>
                        <input id="unit_code" type="text" name="unit_code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Business Name</label>
                        <input id="unit_name" type="text" name="unit_name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>City</label>
                        <input id="unit_city" type="text" name="city" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>District</label>
                        <input id="unit_district" type="text" name="district" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Location</label>
                        <input id="unit_location" type="text" name="location" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input id="unit_phone" type="text" name="phone_No" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select id="unit_status" name="status" class="form-control">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
setInterval(function () {
    document.getElementById('liveTime').innerHTML = new Date().toLocaleTimeString();
}, 1000);

document.querySelectorAll('.btn-edit-unit').forEach(btn => {
    btn.addEventListener('click', function () {
        let id = this.dataset.id;

        document.getElementById('unit_company_id').value = this.dataset.company_id;
        document.getElementById('unit_code').value = this.dataset.unit_code;
        document.getElementById('unit_name').value = this.dataset.unit_name;
        document.getElementById('unit_city').value = this.dataset.city;
        document.getElementById('unit_district').value = this.dataset.district;
        document.getElementById('unit_location').value = this.dataset.location;
        document.getElementById('unit_phone').value = this.dataset.phone;
        document.getElementById('unit_status').value = this.dataset.status;

        document.getElementById('unitEditForm').action =
            "{{ url('/admin/company-units') }}/" + encodeURIComponent(id);
    });
});
</script>

@endsection