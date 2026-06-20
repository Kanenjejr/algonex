@extends('layouts.SalesMaster')

@section('content')
@php
    $pageTitle = $pageTitle ?? 'Transport Orders';
    $record = $record ?? null;
    $currentCompany = $currentCompany ?? null;
    $currentUnit = $currentUnit ?? null;
    $currentWorkPoint = $currentWorkPoint ?? null;

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
@endphp

<style>
    .logistics-heading {
        margin-bottom: 15px;
        border-bottom: 1px solid #e7eaec;
        background: #fff;
        border-radius: 8px;
        padding: 18px 20px;
    }

    .logistics-card {
        color: #fff;
        border-radius: 16px;
        padding: 20px;
        min-height: 150px;
        box-shadow: 0 6px 18px rgba(0,0,0,.10);
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .logistics-card::after {
        content: "";
        position: absolute;
        right: -20px;
        top: -20px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
    }

    .logistics-card .icon {
        font-size: 30px;
        margin-bottom: 14px;
        opacity: .95;
    }

    .logistics-card .value {
        font-size: 36px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .logistics-card .label {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .card-orders {
        background: linear-gradient(135deg, #1abc9c, #16a085);
    }

    .card-vehicles {
        background: linear-gradient(135deg, #3498db, #2980b9);
    }

    .card-drivers {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
    }

    .card-costs {
        background: linear-gradient(135deg, #f39c12, #e67e22);
    }

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

    .status-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
    }

    .status-draft { background: #777; }
    .status-approved { background: #1ab394; }
    .status-dispatched { background: #23c6c8; }
    .status-intransit { background: #f8ac59; }
    .status-completed { background: #1c84c6; }
    .status-closed { background: #0f9d58; }
    .status-cancelled { background: #ed5565; }

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

    .form-section-title {
        margin: 0 0 15px 0;
        font-weight: 700;
        color: #2f4050;
    }
</style>

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row logistics-heading">
        <div class="col-lg-8 col-md-8 col-sm-12">
            <h2 style="margin-top:0;margin-bottom:5px;">{{ $pageTitle }}</h2>
            <ol class="breadcrumb" style="font-size:15px;background:transparent;padding-left:0;margin-bottom:0;">
                <li><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                <li class="active"><strong>{{ $pageTitle }}</strong></li>
            </ol>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 text-right" style="padding-top:18px;">
            <a href="{{ route('logistics.dashboard') }}" class="btn btn-default">
                <i class="fa fa-dashboard"></i> Dashboard
            </a>

            @can('Create-Transport-Orders')
                <a href="{{ route('logistics.orders.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> New Order
                </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-orders">
                <div class="icon"><i class="fa fa-file-text"></i></div>
                <div class="value">{{ $totals['orders'] ?? (isset($records) && is_countable($records) ? count($records) : 0) }}</div>
                <div class="label">Transport Orders</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-vehicles">
                <div class="icon"><i class="fa fa-truck"></i></div>
                <div class="value">{{ $totals['vehicles'] ?? 0 }}</div>
                <div class="label">Vehicles</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-drivers">
                <div class="icon"><i class="fa fa-users"></i></div>
                <div class="value">{{ $totals['drivers'] ?? 0 }}</div>
                <div class="label">Drivers</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="logistics-card card-costs">
                <div class="icon"><i class="fa fa-money"></i></div>
                <div class="value">{{ $totals['costs'] ?? 0 }}</div>
                <div class="label">Cost Sheets</div>
            </div>
        </div>
    </div>

    @if(isset($companies) && $companies->count())
        <div class="info-box">
            <div class="ibox-content">
                <form method="GET" action="{{ route('logistics.dashboard') }}">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <label style="font-weight:700;">Select Company</label>
                            <select name="company_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Select company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ optional($currentCompany)->id == $company->id ? 'selected' : '' }}>
                                        {{ $company->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="info-box">
                <div class="ibox-title">
                    <h5 style="margin:0;">Company Information</h5>
                </div>
                <div class="ibox-content">
                    <h3 style="margin-top:0;">
                        {{ optional($currentCompany)->company_name ?? 'N/A' }}
                    </h3>

                    <p><strong>Company Code:</strong> {{ optional($currentCompany)->company_code ?? 'N/A' }}</p>
                    <p><strong>Business Unit:</strong> {{ optional($currentUnit)->unit_name ?? 'N/A' }}</p>
                    <p><strong>Work Point:</strong> {{ optional($currentWorkPoint)->work_name ?? 'N/A' }}</p>
                    <p><strong>Location:</strong> {{ optional($currentWorkPoint)->location ?? optional($currentUnit)->location ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="info-box">
                <div class="ibox-title">
                    <h5 style="margin:0;">Quick Summary</h5>
                </div>
                <div class="ibox-content">
                    <p><strong>Transport Orders</strong> - all movement control.</p>
                    <p><strong>Fleet Management</strong> - vehicles, drivers, escorts.</p>
                    <p><strong>Transport Costing</strong> - cost and profit analysis.</p>
                    <hr>
                    <p style="margin-bottom:0;">
                        <strong>Access:</strong>
                        @if($isSuper ?? false)
                            <span class="status-pill status-approved">Super User</span>
                        @else
                            <span class="status-pill status-intransit">Scoped User</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="info-box">
        <div class="ibox-title">
            <h5 style="margin:0;">Create / Edit Transport Order</h5>
        </div>
        <div class="ibox-content">
            <form method="POST" action="{{ $record ? route('logistics.orders.update', encrypt($record->id)) : route('logistics.orders.store') }}" id="orderForm">
                @csrf
                @if($record)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Order No *</label>
                        <input type="text" name="order_no" class="form-control" value="{{ old('order_no', $record->order_no ?? '') }}" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Order Date *</label>
                        <input type="date" name="order_date" class="form-control"
                               value="{{ old('order_date', !empty($record->order_date) ? \Carbon\Carbon::parse($record->order_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Company *</label>
                        <select name="company_id" id="company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ (int) old('company_id', $record->company_id ?? optional($currentCompany)->id) === (int) $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Business Unit *</label>
                        <select name="comp_unit_id" id="comp_unit_id" class="form-control" required>
                            <option value="">Select Business Unit</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Work Point *</label>
                        <select name="work_point_id" id="work_point_id" class="form-control" required>
                            <option value="">Select Work Point</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Customer *</label>
                        <select name="customer_id" class="form-control">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (int) old('customer_id', $record->customer_id ?? 0) === (int) $customer->id ? 'selected' : '' }}>
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Customer Name *</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $record->customer_name ?? '') }}" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Cargo Description *</label>
                        <input type="text" name="cargo_description" class="form-control" value="{{ old('cargo_description', $record->cargo_description ?? '') }}" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Origin *</label>
                        <input type="text" name="origin" class="form-control" value="{{ old('origin', $record->origin ?? '') }}" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Destination *</label>
                        <input type="text" name="destination" class="form-control" value="{{ old('destination', $record->destination ?? '') }}" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Vehicle Source *</label>
                        <select name="vehicle_source" id="vehicle_source" class="form-control" required>
                            <option value="company" {{ old('vehicle_source', $record->vehicle_source ?? 'company') == 'company' ? 'selected' : '' }}>Company Vehicle</option>
                            <option value="hired" {{ old('vehicle_source', $record->vehicle_source ?? '') == 'hired' ? 'selected' : '' }}>Hired Vehicle</option>
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Company Vehicle</label>
                        <select name="company_vehicle_id" id="company_vehicle_id" class="form-control">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (int) old('company_vehicle_id', $record->company_vehicle_id ?? 0) === (int) $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate_number }} - {{ $vehicle->vehicle_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Hired Vehicle Name</label>
                        <input type="text" name="hired_vehicle_name" class="form-control" value="{{ old('hired_vehicle_name', $record->hired_vehicle_name ?? '') }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Hired Vehicle Plate</label>
                        <input type="text" name="hired_vehicle_plate" class="form-control" value="{{ old('hired_vehicle_plate', $record->hired_vehicle_plate ?? '') }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Hired Cost</label>
                        <input type="number" step="0.01" name="hired_vehicle_cost" id="hired_vehicle_cost" class="form-control" value="{{ old('hired_vehicle_cost', $record->hired_vehicle_cost ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Driver</label>
                        <select name="driver_id" id="driver_id" class="form-control">
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ (int) old('driver_id', $record->driver_id ?? 0) === (int) $driver->id ? 'selected' : '' }}>
                                    {{ $driver->first_name }} {{ $driver->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Escort Name</label>
                        <input type="text" name="escort_name" class="form-control" value="{{ old('escort_name', $record->escort_name ?? '') }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Escort Allowance</label>
                        <input type="number" step="0.01" name="escort_allowance" id="escort_allowance" class="form-control" value="{{ old('escort_allowance', $record->escort_allowance ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Driver Allowance</label>
                        <input type="number" step="0.01" name="driver_allowance" id="driver_allowance" class="form-control" value="{{ old('driver_allowance', $record->driver_allowance ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Expected Fuel Liters</label>
                        <input type="number" step="0.01" name="expected_fuel_liters" id="expected_fuel_liters" class="form-control" value="{{ old('expected_fuel_liters', $record->expected_fuel_liters ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Fuel Rate</label>
                        <input type="number" step="0.01" name="fuel_rate" id="fuel_rate" class="form-control" value="{{ old('fuel_rate', $record->fuel_rate ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Loading Cost</label>
                        <input type="number" step="0.01" name="loading_cost" id="loading_cost" class="form-control" value="{{ old('loading_cost', $record->loading_cost ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Other Cost</label>
                        <input type="number" step="0.01" name="other_cost" id="other_cost" class="form-control" value="{{ old('other_cost', $record->other_cost ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Revenue Amount *</label>
                        <input type="number" step="0.01" name="revenue_amount" id="revenue_amount" class="form-control" value="{{ old('revenue_amount', $record->revenue_amount ?? 0) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Status *</label>
                        <select name="status" id="status" class="form-control" required>
                            @foreach(['Draft','Approved','Dispatched','In Transit','Completed','Closed','Cancelled'] as $status)
                                <option value="{{ $status }}" {{ old('status', $record->status ?? 'Draft') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $record->remarks ?? '') }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <div class="alert alert-info" style="margin-bottom:0;">
                            <strong>Estimated Fuel Cost:</strong> <span id="calc_fuel_cost">0.00</span> |
                            <strong>Estimated Total Cost:</strong> <span id="calc_total_cost">0.00</span> |
                            <strong>Estimated Profit:</strong> <span id="calc_profit">0.00</span>
                        </div>
                    </div>
                </div>

                <div class="text-right" style="margin-top:15px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="info-box">
        <div class="ibox-title"><h5 style="margin:0;">Transport Orders</h5></div>
        <div class="ibox-content table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Route</th>
                        <th>Vehicle Source</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->order_no ?? '-' }}</td>
                            <td>{{ $formatDate($row->order_date ?? null) }}</td>
                            <td>{{ $row->customer_name ?? '-' }}</td>
                            <td>{{ ($row->origin ?? '-') }} → {{ ($row->destination ?? '-') }}</td>
                            <td>{{ ucfirst($row->vehicle_source ?? '-') }}</td>
                            <td>{{ $row->status ?? '-' }}</td>
                            <td>
                                @can('View-Transport-Orders')
                                    <a href="{{ route('logistics.orders.show', encrypt($row->id)) }}" class="btn btn-xs btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endcan

                                @can('Edit-Transport-Orders')
                                    <a href="{{ route('logistics.orders.edit', encrypt($row->id)) }}" class="btn btn-xs btn-success">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                @endcan

                                @can('Delete-Transport-Orders')
                                    <form action="{{ route('logistics.orders.destroy', encrypt($row->id)) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No Records Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    const companyId = document.getElementById('company_id');
    const unitId = document.getElementById('comp_unit_id');
    const workPointId = document.getElementById('work_point_id');

    const expectedFuel = document.getElementById('expected_fuel_liters');
    const fuelRate = document.getElementById('fuel_rate');
    const hiredCost = document.getElementById('hired_vehicle_cost');
    const driverAllowance = document.getElementById('driver_allowance');
    const escortAllowance = document.getElementById('escort_allowance');
    const loadingCost = document.getElementById('loading_cost');
    const otherCost = document.getElementById('other_cost');
    const revenueAmount = document.getElementById('revenue_amount');

    const calcFuelCost = document.getElementById('calc_fuel_cost');
    const calcTotalCost = document.getElementById('calc_total_cost');
    const calcProfit = document.getElementById('calc_profit');
    const vehicleSource = document.getElementById('vehicle_source');

    function num(v) {
        const n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function calculate() {
        const fuelCost = num(expectedFuel ? expectedFuel.value : 0) * num(fuelRate ? fuelRate.value : 0);
        const hire = vehicleSource && vehicleSource.value === 'hired' ? num(hiredCost ? hiredCost.value : 0) : 0;
        const total = hire + fuelCost + num(driverAllowance ? driverAllowance.value : 0) + num(escortAllowance ? escortAllowance.value : 0) + num(loadingCost ? loadingCost.value : 0) + num(otherCost ? otherCost.value : 0);
        const profit = num(revenueAmount ? revenueAmount.value : 0) - total;

        if (calcFuelCost) calcFuelCost.textContent = fuelCost.toFixed(2);
        if (calcTotalCost) calcTotalCost.textContent = total.toFixed(2);
        if (calcProfit) calcProfit.textContent = profit.toFixed(2);
    }

    function loadUnits(companyIdValue, selectedUnit = null) {
        if (!companyIdValue || !unitId) return;

        fetch(`{{ url('/logistics/ajax/company-units') }}/${companyIdValue}`)
            .then(r => r.json())
            .then(data => {
                unitId.innerHTML = '<option value="">Select Business Unit</option>';
                data.forEach(item => {
                    const selected = selectedUnit && String(selectedUnit) === String(item.id) ? 'selected' : '';
                    unitId.insertAdjacentHTML('beforeend', `<option value="${item.id}" ${selected}>${item.unit_name}</option>`);
                });

                unitId.dispatchEvent(new Event('change'));
            });
    }

    function loadWorkPoints(companyIdValue, unitIdValue, selectedWorkPoint = null) {
        if (!companyIdValue || !unitIdValue || !workPointId) return;

        fetch(`{{ url('/logistics/ajax/work-points') }}/${companyIdValue}/${unitIdValue}`)
            .then(r => r.json())
            .then(data => {
                workPointId.innerHTML = '<option value="">Select Work Point</option>';
                data.forEach(item => {
                    const selected = selectedWorkPoint && String(selectedWorkPoint) === String(item.id) ? 'selected' : '';
                    const extra = item.location ? ' - ' + item.location : '';
                    workPointId.insertAdjacentHTML('beforeend', `<option value="${item.id}" ${selected}>${item.work_name}${extra}</option>`);
                });
            });
    }

    if (companyId) {
        companyId.addEventListener('change', function () {
            loadUnits(this.value);
        });
    }

    if (unitId) {
        unitId.addEventListener('change', function () {
            if (companyId) {
                loadWorkPoints(companyId.value, this.value);
            }
        });
    }

    [expectedFuel, fuelRate, hiredCost, driverAllowance, escortAllowance, loadingCost, otherCost, revenueAmount, vehicleSource].forEach(el => {
        if (el) el.addEventListener('input', calculate);
    });

    const selectedCompany = companyId ? companyId.value : '';
    if (selectedCompany) {
        loadUnits(selectedCompany, @json(old('comp_unit_id', $record->comp_unit_id ?? null)));
        setTimeout(function () {
            loadWorkPoints(
                selectedCompany,
                @json(old('comp_unit_id', $record->comp_unit_id ?? null)),
                @json(old('work_point_id', $record->work_point_id ?? null))
            );
        }, 300);
    }

    calculate();
})();
</script>
@endsection