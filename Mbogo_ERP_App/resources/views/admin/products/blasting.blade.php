@extends('layouts.ManftrMaster')

@section('content')
@php
    $user = auth()->user();
    $isSuper = in_array(optional($user)->role, ['Admin', 'CEO', 'Admin-Developer', 'Managing Director (MD)'], true);
@endphp

<div class="wrapper wrapper-content">
    <div class="row wrapper border-bottom white-bg page-heading no-print">
        <div class="col-lg-8">
            <h2 class="dashboard-title">Drilling & Blasting</h2>
            <ol class="breadcrumb" style="font-size:16px;color:#000">
                <li><a href="#">Production Department</a></li>
                <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Blasting Activities & Explosives Sales Summary</strong></li>
            </ol>
        </div>
        <div class="col-lg-4 text-right" style="padding-top:25px;">
            @can('Create-Drilling-Blasting')
                <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                    <i class="fa fa-plus"></i> New Record
                </button>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <style>
        .db-page { background:#f4f6fb; min-height:100vh; padding:18px 12px 28px; }
        .db-card { background:#fff; border:0; border-radius:18px; box-shadow:0 10px 30px rgba(16,24,40,.08); }
        .db-title { font-weight:900; color:#1f2937; }
        .summary-card { border-radius:16px; color:#fff; min-height:118px; box-shadow:0 10px 30px rgba(16,24,40,.08); }
        .summary-card h6 { font-size:11px; letter-spacing:.45px; font-weight:800; opacity:.95; }
        .summary-card h2 { margin:0; font-weight:900; }
        .db-table thead th { background:#0b1a78 !important; color:#fff !important; font-weight:800; white-space:nowrap; }
        .db-table tbody td { vertical-align:middle; }
        .form-control, .custom-select { border-radius:10px; min-height:44px; }
        .small-label { font-size:12px; font-weight:800; color:#4b5563; margin-bottom:7px; letter-spacing:.3px; }
        .soft-pill { display:inline-block; padding:3px 8px; border-radius:999px; background:#eef4ff; color:#173a7a; border:1px solid #d8e4fb; font-size:11px; font-weight:800; }
        .action-wrap .btn { min-width:36px; }
        .readonly-box {
            background: #f8fafc;
        }
    </style>

    <div class="db-page">
        <div class="container-fluid">

            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card bg-primary text-white summary-card">
                        <div class="card-body">
                            <h6>TOTAL RECORDS</h6>
                            <h2>{{ number_format($totals['records']) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card bg-success text-white summary-card">
                        <div class="card-body">
                            <h6>BLASTS</h6>
                            <h2>{{ number_format($totals['blasts']) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card bg-warning text-dark summary-card">
                        <div class="card-body">
                            <h6>EXPLOSIVE QTY</h6>
                            <h2>{{ number_format($totals['explosive_qty'], 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card bg-info text-white summary-card">
                        <div class="card-body">
                            <h6>DETONATORS</h6>
                            <h2>{{ number_format($totals['detonators_qty'], 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card bg-danger text-white summary-card">
                        <div class="card-body">
                            <h6>CORD (M)</h6>
                            <h2>{{ number_format($totals['cord_m'], 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card bg-dark text-white summary-card">
                        <div class="card-body">
                            <h6>ROCK BLASTED</h6>
                            <h2>{{ number_format($totals['rock'], 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card db-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="db-title mb-1">DRILLING AND BLASTING SUMMARY</h4>
                            <div class="text-muted">Track blasting operations, sales and summary by site.</div>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <span class="soft-pill">Production Department</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card db-card mb-4">
                <div class="card-header bg-white py-3">
                    <strong>RECENT BLASTING RECORDS</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover db-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>RECORD NO</th>
                                    <th>DATE</th>
                                    <th>COMPANY</th>
                                    <th>BUSINESS UNIT</th>
                                    <th>WORK POINT</th>
                                    <th>UNIT LOCATION</th>
                                    <th>ACTIVITY LOCATION</th>
                                    <th>BLASTS</th>
                                    <th>EXPLOSIVE TYPE</th>
                                    <th>QTY USED/SOLD</th>
                                    <th>AUTHORIZED BLASTER</th>
                                    <th>STATUS</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row->record_no }}</td>
                                        <td>{{ optional($row->record_date)->format('Y-m-d') ?? $row->record_date ?? '-' }}</td>
                                        <td>{{ $row->company->company_name ?? '-' }}</td>
                                        <td>{{ $row->companyUnit->unit_name ?? '-' }}</td>
                                        <td>{{ $row->workPoint->work_name ?? '-' }}</td>
                                        <td>{{ $row->companyUnit->location ?? '-' }}</td>
                                        <td>{{ $row->project_site ?? '-' }}</td>
                                        <td>{{ number_format($row->blasts_conducted) }}</td>
                                        <td>{{ $row->explosive_type ?? '-' }}</td>
                                        <td>{{ number_format($row->explosive_qty, 2) }}</td>
                                        <td>{{ $row->authorized_blaster ?? '-' }}</td>
                                        <td>
                                            @if($row->status === 'Active')
                                                <span class="badge badge-success">ACTIVE</span>
                                            @elseif($row->status === 'Inactive')
                                                <span class="badge badge-warning">INACTIVE</span>
                                            @else
                                                <span class="badge badge-secondary">DELETED</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group action-wrap">
                                                @can('View-Drilling-Blasting')
                                                    <a href="{{ route('production.drilling-blasting.show', Crypt::encryptString($row->id)) }}"
                                                       class="btn btn-sm btn-info"
                                                       title="View">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('Edit-Drilling-Blasting')
                                                    <button class="btn btn-sm btn-primary js-edit"
                                                        data-id="{{ Crypt::encryptString($row->id) }}"
                                                        data-company_id="{{ $row->company_id }}"
                                                        data-comp_unit_id="{{ $row->comp_unit_id }}"
                                                        data-work_point_id="{{ $row->work_point_id }}"
                                                        data-record_date="{{ optional($row->record_date)->format('Y-m-d') ?? $row->record_date }}"
                                                        data-customer_name="{{ $row->customer_name }}"
                                                        data-project_site="{{ $row->project_site }}"
                                                        data-period_from="{{ $row->period_from }}"
                                                        data-period_to="{{ $row->period_to }}"
                                                        data-blasts_conducted="{{ $row->blasts_conducted }}"
                                                        data-total_holes_charged="{{ $row->total_holes_charged }}"
                                                        data-explosive_type="{{ $row->explosive_type }}"
                                                        data-explosive_qty="{{ $row->explosive_qty }}"
                                                        data-detonators_qty="{{ $row->detonators_qty }}"
                                                        data-detonating_cord_m="{{ $row->detonating_cord_m }}"
                                                        data-booster_qty="{{ $row->booster_qty }}"
                                                        data-total_rock_blasted="{{ $row->total_rock_blasted }}"
                                                        data-rock_unit="{{ $row->rock_unit }}"
                                                        data-authorized_blaster="{{ $row->authorized_blaster }}"
                                                        data-remarks="{{ $row->remarks }}"
                                                        data-status="{{ $row->status }}"
                                                        data-toggle="modal"
                                                        data-target="#editModal"
                                                        title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                @endcan

                                                @can('Delete-Drilling-Blasting')
                                                    <form action="{{ route('production.drilling-blasting.destroy', Crypt::encryptString($row->id)) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Remove this blasting record?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center text-muted">No drilling and blasting record found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= CREATE MODAL ================= --}}
    @can('Create-Drilling-Blasting')
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('production.drilling-blasting.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Create Blasting Record</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            @if($isSuper)
                                <div class="col-md-4">
                                    <div class="small-label">Company</div>
                                    <select name="company_id" id="create_company_id" class="form-control" required>
                                        <option value="">Select company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Unit</div>
                                    <select name="comp_unit_id" id="create_comp_unit_id" class="form-control" required>
                                        <option value="">Select unit</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Work Point</div>
                                    <select name="work_point_id" id="create_work_point_id" class="form-control" required>
                                        <option value="">Select work point</option>
                                    </select>
                                </div> 
                                 @else
                                <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                                <input type="hidden" name="comp_unit_id" value="{{ auth()->user()->comp_unit_id }}">
                                <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">

                                <div class="col-md-4">
                                    <div class="small-label">Company</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentCompany)->company_name }}" readonly>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Unit</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentUnit)->unit_name }}" readonly>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Work Point</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentWorkPoint)->work_name }}" readonly>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <div class="small-label">Unit Location</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentUnit)->location }}" readonly>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <div class="small-label">Work Point Location</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentWorkPoint)->location }}" readonly>
                                </div>
                            @endif

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Record Date</div>
                                <input type="date" name="record_date" class="form-control" value="{{ old('record_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Customer / Company Name</div>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Activity Location</div>
                                <input type="text" name="project_site" class="form-control" value="{{ old('project_site') }}" placeholder="Write activity location" required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Blasting Period From</div>
                                <input type="date" name="period_from" class="form-control" value="{{ old('period_from') }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Blasting Period To</div>
                                <input type="date" name="period_to" class="form-control" value="{{ old('period_to') }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Number of Blasts Conducted</div>
                                <input type="number" step="1" min="0" name="blasts_conducted" class="form-control" value="{{ old('blasts_conducted', 0) }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Total Holes Charged</div>
                                <input type="number" step="1" min="0" name="total_holes_charged" class="form-control" value="{{ old('total_holes_charged', 0) }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Explosive Type</div>
                                <input type="text" name="explosive_type" class="form-control" value="{{ old('explosive_type') }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Qty of Explosives (kg)</div>
                                <input type="number" step="0.01" min="0" name="explosive_qty" class="form-control" value="{{ old('explosive_qty', 0) }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Detonators (pcs)</div>
                                <input type="number" step="0.01" min="0" name="detonators_qty" class="form-control" value="{{ old('detonators_qty', 0) }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Detonating Cord (m)</div>
                                <input type="number" step="0.01" min="0" name="detonating_cord_m" class="form-control" value="{{ old('detonating_cord_m', 0) }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Booster Qty (pcs)</div>
                                <input type="number" step="0.01" min="0" name="booster_qty" class="form-control" value="{{ old('booster_qty', 0) }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Total Rock Blasted</div>
                                <input type="number" step="0.01" min="0" name="total_rock_blasted" class="form-control" value="{{ old('total_rock_blasted', 0) }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Rock Unit</div>
                                <select name="rock_unit" class="form-control">
                                    <option value="BCM">BCM</option>
                                    <option value="Tonnes">Tonnes</option>
                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Authorized Blaster</div>
                                <input type="text" name="authorized_blaster" class="form-control" value="{{ old('authorized_blaster') }}">
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="small-label">Remarks</div>
                                <textarea name="remarks" rows="3" class="form-control">{{ old('remarks') }}</textarea>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Status</div>
                                <select name="status" class="form-control" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Record</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- ================= EDIT MODAL ================= --}}
    @can('Edit-Drilling-Blasting')
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form id="editForm" method="POST" action="">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Blasting Record</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            @if($isSuper)
                                <div class="col-md-4">
                                    <div class="small-label">Company</div>
                                    <select name="company_id" id="edit_company_id" class="form-control" required>
                                        <option value="">Select company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Unit</div>
                                    <select name="comp_unit_id" id="edit_comp_unit_id" class="form-control" required>
                                        <option value="">Select unit</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Work Point</div>
                                    <select name="work_point_id" id="edit_work_point_id" class="form-control" required>
                                        <option value="">Select work point</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <div class="small-label">Unit Location</div>
                                    <input type="text" id="edit_unit_location" class="form-control readonly-box" readonly>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <div class="small-label">Work Point Location</div>
                                    <input type="text" id="edit_work_point_location" class="form-control readonly-box" readonly>
                                </div>
                            @else
                                <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                                <input type="hidden" name="comp_unit_id" value="{{ auth()->user()->comp_unit_id }}">
                                <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">

                                <div class="col-md-4">
                                    <div class="small-label">Company</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentCompany)->company_name }}" readonly>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Unit</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentUnit)->unit_name }}" readonly>
                                </div>

                                <div class="col-md-4">
                                    <div class="small-label">Work Point</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentWorkPoint)->work_name }}" readonly>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <div class="small-label">Unit Location</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentUnit)->location }}" readonly>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <div class="small-label">Work Point Location</div>
                                    <input type="text" class="form-control readonly-box" value="{{ optional($currentWorkPoint)->location }}" readonly>
                                </div>
                            @endif

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Record Date</div>
                                <input type="date" name="record_date" id="edit_record_date" class="form-control" required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Customer / Company Name</div>
                                <input type="text" name="customer_name" id="edit_customer_name" class="form-control" required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Activity Location</div>
                                <input type="text" name="project_site" id="edit_project_site" class="form-control" required>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Blasting Period From</div>
                                <input type="date" name="period_from" id="edit_period_from" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Blasting Period To</div>
                                <input type="date" name="period_to" id="edit_period_to" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Number of Blasts Conducted</div>
                                <input type="number" step="1" min="0" name="blasts_conducted" id="edit_blasts_conducted" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Total Holes Charged</div>
                                <input type="number" step="1" min="0" name="total_holes_charged" id="edit_total_holes_charged" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Explosive Type</div>
                                <input type="text" name="explosive_type" id="edit_explosive_type" class="form-control">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Qty of Explosives (kg)</div>
                                <input type="number" step="0.01" min="0" name="explosive_qty" id="edit_explosive_qty" class="form-control">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Detonators (pcs)</div>
                                <input type="number" step="0.01" min="0" name="detonators_qty" id="edit_detonators_qty" class="form-control">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Detonating Cord (m)</div>
                                <input type="number" step="0.01" min="0" name="detonating_cord_m" id="edit_detonating_cord_m" class="form-control">
                            </div>

                            <div class="col-md-3 mt-3">
                                <div class="small-label">Booster Qty (pcs)</div>
                                <input type="number" step="0.01" min="0" name="booster_qty" id="edit_booster_qty" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Total Rock Blasted</div>
                                <input type="number" step="0.01" min="0" name="total_rock_blasted" id="edit_total_rock_blasted" class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Rock Unit</div>
                                <select name="rock_unit" id="edit_rock_unit" class="form-control">
                                    <option value="BCM">BCM</option>
                                    <option value="Tonnes">Tonnes</option>
                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Authorized Blaster</div>
                                <input type="text" name="authorized_blaster" id="edit_authorized_blaster" class="form-control">
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="small-label">Remarks</div>
                                <textarea name="remarks" id="edit_remarks" rows="3" class="form-control"></textarea>
                            </div>

                            <div class="col-md-4 mt-3">
                                <div class="small-label">Status</div>
                                <select name="status" id="edit_status" class="form-control" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Deleted">Deleted</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Record</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isSuper = @json($isSuper);

    const createCompany = document.getElementById('create_company_id');
    const createUnit = document.getElementById('create_comp_unit_id');
    const createWorkPoint = document.getElementById('create_work_point_id');
    const createUnitLocation = document.getElementById('create_unit_location');
    const createWorkPointLocation = document.getElementById('create_work_point_location');

    const editCompany = document.getElementById('edit_company_id');
    const editUnit = document.getElementById('edit_comp_unit_id');
    const editWorkPoint = document.getElementById('edit_work_point_id');
    const editUnitLocation = document.getElementById('edit_unit_location');
    const editWorkPointLocation = document.getElementById('edit_work_point_location');

    const unitUrl = "{{ url('/production/drilling-blasting/ajax/company-units') }}";
    const workPointUrl = "{{ url('/production/drilling-blasting/ajax/work-points') }}";

    function setOptions(select, placeholder) {
        if (!select) return;
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    function setLocation(input, value) {
        if (input) input.value = value || '';
    }

    async function loadUnits(companyId, unitSelect, workPointSelect, locationInput, selectedUnitId = '', selectedWorkPointId = '') {
        if (!unitSelect) return;

        setOptions(unitSelect, 'Select unit');
        if (workPointSelect) setOptions(workPointSelect, 'Select work point');
        setLocation(locationInput, '');

        if (!companyId) return;

        try {
            const response = await fetch(`${unitUrl}/${companyId}`);
            const units = await response.json();

            units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = unit.unit_name;
                option.dataset.location = unit.location || '';

                if (selectedUnitId && String(selectedUnitId) === String(unit.id)) {
                    option.selected = true;
                    setLocation(locationInput, unit.location || '');
                }

                unitSelect.appendChild(option);
            });

            if (selectedUnitId && workPointSelect) {
                await loadWorkPoints(companyId, selectedUnitId, workPointSelect, selectedWorkPointId);
            }
        } catch (error) {
            console.error(error);
        }
    }

    async function loadWorkPoints(companyId, unitId, workPointSelect, locationInput, selectedWorkPointId = '') {
        if (!workPointSelect) return;

        setOptions(workPointSelect, 'Select work point');
        setLocation(locationInput, '');

        if (!companyId || !unitId) return;

        try {
            const response = await fetch(`${workPointUrl}/${companyId}/${unitId}`);
            const workPoints = await response.json();

            workPoints.forEach(wp => {
                const option = document.createElement('option');
                option.value = wp.id;
                option.textContent = wp.work_name;
                option.dataset.location = wp.location || '';

                if (selectedWorkPointId && String(selectedWorkPointId) === String(wp.id)) {
                    option.selected = true;
                    setLocation(locationInput, wp.location || '');
                }

                workPointSelect.appendChild(option);
            });
        } catch (error) {
            console.error(error);
        }
    }

    if (createCompany) {
        createCompany.addEventListener('change', function () {
            loadUnits(this.value, createUnit, createWorkPoint, createUnitLocation);
        });

        // preload on open if company is already selected
        if (createCompany.value) {
            loadUnits(createCompany.value, createUnit, createWorkPoint, createUnitLocation);
        }
    }

    if (createUnit) {
        createUnit.addEventListener('change', function () {
            const companyId = createCompany ? createCompany.value : '';
            const selected = this.options[this.selectedIndex];
            setLocation(createUnitLocation, selected ? selected.dataset.location : '');
            loadWorkPoints(companyId, this.value, createWorkPoint, createWorkPointLocation);
        });
    }

    if (createWorkPoint) {
        createWorkPoint.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            setLocation(createWorkPointLocation, selected ? selected.dataset.location : '');
        });
    }

    if (editCompany) {
        editCompany.addEventListener('change', function () {
            loadUnits(this.value, editUnit, editWorkPoint, editUnitLocation);
        });
    }

    if (editUnit) {
        editUnit.addEventListener('change', function () {
            const companyId = editCompany ? editCompany.value : '';
            const selected = this.options[this.selectedIndex];
            setLocation(editUnitLocation, selected ? selected.dataset.location : '');
            loadWorkPoints(companyId, this.value, editWorkPoint, editWorkPointLocation);
        });
    }

    if (editWorkPoint) {
        editWorkPoint.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            setLocation(editWorkPointLocation, selected ? selected.dataset.location : '');
        });
    }

    document.querySelectorAll('.js-edit').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const encryptedId = this.getAttribute('data-id');
            document.getElementById('editForm').action = "{{ url('/production/drilling-blasting') }}/" + encodeURIComponent(encryptedId) + "/update";

            const setValue = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.value = value ?? '';
            };

            setValue('edit_record_date', this.dataset.record_date);
            setValue('edit_customer_name', this.dataset.customer_name);
            setValue('edit_project_site', this.dataset.project_site);
            setValue('edit_period_from', this.dataset.period_from);
            setValue('edit_period_to', this.dataset.period_to);
            setValue('edit_blasts_conducted', this.dataset.blasts_conducted || 0);
            setValue('edit_total_holes_charged', this.dataset.total_holes_charged || 0);
            setValue('edit_explosive_type', this.dataset.explosive_type);
            setValue('edit_explosive_qty', this.dataset.explosive_qty || 0);
            setValue('edit_detonators_qty', this.dataset.detonators_qty || 0);
            setValue('edit_detonating_cord_m', this.dataset.detonating_cord_m || 0);
            setValue('edit_booster_qty', this.dataset.booster_qty || 0);
            setValue('edit_total_rock_blasted', this.dataset.total_rock_blasted || 0);
            setValue('edit_rock_unit', this.dataset.rock_unit || 'BCM');
            setValue('edit_authorized_blaster', this.dataset.authorized_blaster);
            setValue('edit_remarks', this.dataset.remarks);
            setValue('edit_status', this.dataset.status || 'Active');

            if (isSuper) {
                const companyId = this.dataset.company_id || '';
                const unitId = this.dataset.comp_unit_id || '';
                const workPointId = this.dataset.work_point_id || '';

                if (editCompany) editCompany.value = companyId;

                await loadUnits(companyId, editUnit, editWorkPoint, editUnitLocation, unitId, workPointId);
            }
        });
    });
});
</script>
@endsection