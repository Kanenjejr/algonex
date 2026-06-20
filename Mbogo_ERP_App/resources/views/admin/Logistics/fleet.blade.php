@extends('layouts.SalesMaster')

@section('content')
@php
    $pageTitle = $pageTitle ?? 'Fleet Management';

    $formatDate = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $vehicleCount = isset($vehicles) && is_countable($vehicles) ? count($vehicles) : 0;
    $driverCount  = isset($drivers) && is_countable($drivers) ? count($drivers) : 0;
    $escortCount  = isset($escorts) && is_countable($escorts) ? count($escorts) : 0;
@endphp

<style>
    .fleet-heading {
        margin-bottom: 15px;
        border-bottom: 1px solid #e7eaec;
        background: #fff;
        border-radius: 8px;
        padding: 18px 20px;
    }

    .fleet-card {
        color: #fff;
        border-radius: 16px;
        padding: 20px;
        min-height: 135px;
        box-shadow: 0 6px 18px rgba(0,0,0,.10);
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .fleet-card::after {
        content: "";
        position: absolute;
        right: -20px;
        top: -20px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
    }

    .fleet-card .icon {
        font-size: 30px;
        margin-bottom: 14px;
        opacity: .95;
    }

    .fleet-card .value {
        font-size: 34px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .fleet-card .label {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .card-vehicles { background: linear-gradient(135deg, #1abc9c, #16a085); }
    .card-drivers  { background: linear-gradient(135deg, #3498db, #2980b9); }
    .card-escorts  { background: linear-gradient(135deg, #2ecc71, #27ae60); }

    .info-box {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e7eaec;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }

    .info-box .ibox-title {
        padding: 14px 18px;
        border-bottom: 1px solid #e7eaec;
        font-weight: 700;
        color: #2f4050;
        background: #fafafa;
        border-radius: 12px 12px 0 0;
    }

    .info-box .ibox-content {
        padding: 18px;
    }

    .table thead th {
        background: #f7f9fb;
        font-weight: 700;
        color: #2f4050;
        white-space: nowrap;
    }

    .small-muted {
        color: #6c757d;
        font-size: 13px;
    }

    .modal-body .form-group {
        margin-bottom: 12px;
    }
</style>

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row fleet-heading no-print">
        <div class="col-lg-8">
            <h2 style="margin-top:0;margin-bottom:5px;">{{ $pageTitle }}</h2>
            <ol class="breadcrumb" style="font-size:15px;background:transparent;padding-left:0;margin-bottom:0;">
                <li><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                <li class="active"><strong>{{ $pageTitle }}</strong></li>
            </ol>
        </div>

        <div class="col-lg-4 text-right" style="padding-top:18px;">
            <a href="{{ route('logistics.dashboard') }}" class="btn btn-default">
                <i class="fa fa-dashboard"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="fleet-card card-vehicles">
                <div class="icon"><i class="fa fa-truck"></i></div>
                <div class="value">{{ $vehicleCount }}</div>
                <div class="label">Vehicles</div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="fleet-card card-drivers">
                <div class="icon"><i class="fa fa-users"></i></div>
                <div class="value">{{ $driverCount }}</div>
                <div class="label">Drivers</div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="fleet-card card-escorts">
                <div class="icon"><i class="fa fa-shield"></i></div>
                <div class="value">{{ $escortCount }}</div>
                <div class="label">Escorts</div>
            </div>
        </div>
    </div>

    <div class="info-box">
        <div class="ibox-title"><h5 style="margin:0;">Add Vehicle</h5></div>
        <div class="ibox-content">
            <form method="POST" action="{{ route('logistics.fleet.vehicles.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Vehicle Code</label>
                        <input class="form-control" name="vehicle_code" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Plate Number</label>
                        <input class="form-control" name="plate_number" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Vehicle Type</label>
                        <input class="form-control" name="vehicle_type" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Ownership</label>
                        <select class="form-control" name="ownership">
                            <option value="company">Company</option>
                            <option value="hired">Hired</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Fuel Type</label>
                        <input class="form-control" name="fuel_type">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Fuel Rate/Liter</label>
                        <input class="form-control" name="fuel_rate_per_liter" type="number" step="0.01">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Hire Rate/Day</label>
                        <input class="form-control" name="hire_rate_per_day" type="number" step="0.01">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Company</label>
                        <select name="company_id" id="fleet_company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Business Unit</label>
                        <select name="comp_unit_id" id="fleet_comp_unit_id" class="form-control" required>
                            <option value="">Select Business Unit</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Work Point</label>
                        <select name="work_point_id" id="fleet_work_point_id" class="form-control" required>
                            <option value="">Select Work Point</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Status</label>
                        <select class="form-control" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Capacity</label>
                        <input class="form-control" name="capacity" type="number" step="0.01">
                    </div>

                    <div class="col-md-12 form-group">
                        <label>Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>

                <button class="btn btn-success">
                    <i class="fa fa-save"></i> Save Vehicle
                </button>
            </form>
        </div>
    </div>

    <div class="info-box">
        <div class="ibox-title"><h5 style="margin:0;">Vehicles</h5></div>
        <div class="ibox-content table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Plate</th>
                        <th>Type</th>
                        <th>Ownership</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($vehicles as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->vehicle_code }}</td>
                        <td>{{ $row->plate_number }}</td>
                        <td>{{ $row->vehicle_type }}</td>
                        <td>{{ ucfirst($row->ownership) }}</td>
                        <td>{{ $row->status }}</td>
                        <td>
                            @can('Edit-Fleet-Management')
                                <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#editVehicle{{ $row->id }}">
                                    Edit
                                </button>
                            @endcan

                            @can('Delete-Fleet-Management')
                                <form action="{{ route('logistics.fleet.vehicles.destroy', encrypt($row->id)) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete vehicle?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>

                    <div class="modal fade" id="editVehicle{{ $row->id }}">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Edit Vehicle</h4>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="{{ route('logistics.fleet.vehicles.update', encrypt($row->id)) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="row">
                                            <div class="col-md-3 form-group">
                                                <label>Vehicle Code</label>
                                                <input class="form-control" name="vehicle_code" value="{{ $row->vehicle_code }}" required>
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Plate Number</label>
                                                <input class="form-control" name="plate_number" value="{{ $row->plate_number }}" required>
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Vehicle Type</label>
                                                <input class="form-control" name="vehicle_type" value="{{ $row->vehicle_type }}" required>
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Ownership</label>
                                                <select class="form-control" name="ownership">
                                                    <option value="company" @if($row->ownership=='company') selected @endif>Company</option>
                                                    <option value="hired" @if($row->ownership=='hired') selected @endif>Hired</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Fuel Type</label>
                                                <input class="form-control" name="fuel_type" value="{{ $row->fuel_type }}">
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Fuel Rate/Liter</label>
                                                <input class="form-control" name="fuel_rate_per_liter" type="number" step="0.01" value="{{ $row->fuel_rate_per_liter }}">
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Hire Rate/Day</label>
                                                <input class="form-control" name="hire_rate_per_day" type="number" step="0.01" value="{{ $row->hire_rate_per_day }}">
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="status">
                                                    <option value="Active" @if($row->status=='Active') selected @endif>Active</option>
                                                    <option value="Inactive" @if($row->status=='Inactive') selected @endif>Inactive</option>
                                                </select>
                                            </div>

                                            <div class="col-md-12 form-group">
                                                <label>Remarks</label>
                                                <textarea class="form-control" name="remarks">{{ $row->remarks }}</textarea>
                                            </div>
                                        </div>

                                        <button class="btn btn-success">Update</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No Vehicles Found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="info-box">
                <div class="ibox-title"><h5 style="margin:0;">Drivers</h5></div>
                <div class="ibox-content">
                    <form method="POST" action="{{ route('logistics.fleet.drivers.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Code</label>
                                <input class="form-control" name="driver_code" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>First Name</label>
                                <input class="form-control" name="first_name" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Last Name</label>
                                <input class="form-control" name="last_name" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Phone</label>
                                <input class="form-control" name="phone">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>License No</label>
                                <input class="form-control" name="license_no">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Allowance</label>
                                <input class="form-control" name="allowance_rate" type="number" step="0.01">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-8 form-group">
                                <label>Remarks</label>
                                <input class="form-control" name="remarks">
                            </div>
                        </div>

                        <button class="btn btn-success">Save Driver</button>
                    </form>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($drivers as $row)
                                <tr>
                                    <td>{{ $row->driver_code }}</td>
                                    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td>
                                        @can('Edit-Fleet-Management')
                                            <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#editDriver{{ $row->id }}">
                                                Edit
                                            </button>
                                        @endcan

                                        @can('Delete-Fleet-Management')
                                            <form action="{{ route('logistics.fleet.drivers.destroy', encrypt($row->id)) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete driver?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>

                                <div class="modal fade" id="editDriver{{ $row->id }}">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Edit Driver</h4>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="{{ route('logistics.fleet.drivers.update', encrypt($row->id)) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="row">
                                                        <div class="col-md-4 form-group">
                                                            <label>Code</label>
                                                            <input class="form-control" name="driver_code" value="{{ $row->driver_code }}" required>
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>First Name</label>
                                                            <input class="form-control" name="first_name" value="{{ $row->first_name }}" required>
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Last Name</label>
                                                            <input class="form-control" name="last_name" value="{{ $row->last_name }}" required>
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Phone</label>
                                                            <input class="form-control" name="phone" value="{{ $row->phone }}">
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>License No</label>
                                                            <input class="form-control" name="license_no" value="{{ $row->license_no }}">
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Allowance</label>
                                                            <input class="form-control" name="allowance_rate" type="number" step="0.01" value="{{ $row->allowance_rate }}">
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Status</label>
                                                            <select name="status" class="form-control">
                                                                <option value="Active" @if($row->status=='Active') selected @endif>Active</option>
                                                                <option value="Inactive" @if($row->status=='Inactive') selected @endif>Inactive</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-8 form-group">
                                                            <label>Remarks</label>
                                                            <input class="form-control" name="remarks" value="{{ $row->remarks }}">
                                                        </div>
                                                    </div>

                                                    <button class="btn btn-success">Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No Drivers</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="info-box">
                <div class="ibox-title"><h5 style="margin:0;">Escorts</h5></div>
                <div class="ibox-content">
                    <form method="POST" action="{{ route('logistics.fleet.escorts.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Code</label>
                                <input class="form-control" name="escort_code" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Full Name</label>
                                <input class="form-control" name="full_name" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Phone</label>
                                <input class="form-control" name="phone">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Allowance</label>
                                <input class="form-control" name="allowance_rate" type="number" step="0.01">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Remarks</label>
                                <input class="form-control" name="remarks">
                            </div>
                        </div>

                        <button class="btn btn-success">Save Escort</button>
                    </form>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($escorts as $row)
                                <tr>
                                    <td>{{ $row->escort_code }}</td>
                                    <td>{{ $row->full_name }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td>
                                        @can('Edit-Fleet-Management')
                                            <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#editEscort{{ $row->id }}">
                                                Edit
                                            </button>
                                        @endcan

                                        @can('Delete-Fleet-Management')
                                            <form action="{{ route('logistics.fleet.escorts.destroy', encrypt($row->id)) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete escort?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>

                                <div class="modal fade" id="editEscort{{ $row->id }}">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Edit Escort</h4>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="{{ route('logistics.fleet.escorts.update', encrypt($row->id)) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="row">
                                                        <div class="col-md-4 form-group">
                                                            <label>Code</label>
                                                            <input class="form-control" name="escort_code" value="{{ $row->escort_code }}" required>
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Full Name</label>
                                                            <input class="form-control" name="full_name" value="{{ $row->full_name }}" required>
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Phone</label>
                                                            <input class="form-control" name="phone" value="{{ $row->phone }}">
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Allowance</label>
                                                            <input class="form-control" name="allowance_rate" type="number" step="0.01" value="{{ $row->allowance_rate }}">
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Status</label>
                                                            <select name="status" class="form-control">
                                                                <option value="Active" @if($row->status=='Active') selected @endif>Active</option>
                                                                <option value="Inactive" @if($row->status=='Inactive') selected @endif>Inactive</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4 form-group">
                                                            <label>Remarks</label>
                                                            <input class="form-control" name="remarks" value="{{ $row->remarks }}">
                                                        </div>
                                                    </div>

                                                    <button class="btn btn-success">Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No Escorts</td>
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

<script>
(function () {
    var company = document.getElementById('fleet_company_id');
    var unit = document.getElementById('fleet_comp_unit_id');
    var workPoint = document.getElementById('fleet_work_point_id');

    function loadUnits(companyId) {
        if (!companyId || !unit) return;

        fetch("{{ url('/logistics/ajax/company-units') }}/" + companyId)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                unit.innerHTML = '<option value="">Select Business Unit</option>';
                data.forEach(function (item) {
                    unit.insertAdjacentHTML('beforeend', '<option value="' + item.id + '">' + item.unit_name + '</option>');
                });
            });
    }

    function loadWorkPoints(companyId, unitId) {
        if (!companyId || !unitId || !workPoint) return;

        fetch("{{ url('/logistics/ajax/work-points') }}/" + companyId + "/" + unitId)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                workPoint.innerHTML = '<option value="">Select Work Point</option>';
                data.forEach(function (item) {
                    var extra = item.location ? ' - ' + item.location : '';
                    workPoint.insertAdjacentHTML('beforeend', '<option value="' + item.id + '">' + item.work_name + extra + '</option>');
                });
            });
    }

    if (company) {
        company.addEventListener('change', function () {
            loadUnits(this.value);
        });
    }

    if (unit) {
        unit.addEventListener('change', function () {
            if (company) {
                loadWorkPoints(company.value, this.value);
            }
        });
    }
})();
</script>
@endsection