@extends('layouts.salesMaster')

@section('content')
    @php
        $rows = $rows ?? collect();
        $workPoints = $workPoints ?? collect();
        $generalItems = $generalItems ?? collect();
        $rawMaterials = $rawMaterials ?? collect();
        $products = $products ?? collect();

        $generalItemMap = collect($generalItems)->keyBy('id');
        $rawMaterialMap = collect($rawMaterials)->keyBy('id');
        $productMap = collect($products)->keyBy('id');

        $generalItemsJson = collect($generalItems)
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->item_name ?? 'N/A',
                    'code' => $item->item_code ?? '',
                    'unit' => '',
                ];
            })
            ->values();

        $rawMaterialsJson = collect($rawMaterials)
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->material_name ?? 'N/A',
                    'code' => $item->material_code ?? '',
                    'unit' => $item->unit_name ?? '',
                ];
            })
            ->values();

        $productsJson = collect($products)
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product_name ?? 'N/A',
                    'code' => '',
                    'unit' => $item->product_size ?? '',
                ];
            })
            ->values();

        $totalAudits = collect($rows)->count();
        $openAudits = collect($rows)->where('status', 'Open')->count();
        $approvedAudits = collect($rows)->where('status', 'Approved')->count();
        $closedAudits = collect($rows)->where('status', 'Closed')->count();

        $totalVariance = collect($rows)->sum(function ($audit) {
            return collect($audit->items ?? [])->sum('variance_qty');
        });

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

        $formatDateTime = function ($value) {
            if (empty($value)) {
                return '-';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d H:i');
            } catch (\Throwable $e) {
                return $value;
            }
        };

        $auditTypeLabel = function ($type) {
            if ($type === 'RawMaterial') {
                return 'Raw Material';
            }

            if ($type === 'Product') {
                return 'Product';
            }

            return 'General Supply';
        };

        $itemName = function ($type, $id) use ($generalItemMap, $rawMaterialMap, $productMap) {
            if ($type === 'RawMaterial') {
                return optional($rawMaterialMap->get($id))->material_name ?? 'N/A';
            }

            if ($type === 'Product') {
                return optional($productMap->get($id))->product_name ?? 'N/A';
            }

            return optional($generalItemMap->get($id))->item_name ?? 'N/A';
        };

        $itemCode = function ($type, $id) use ($generalItemMap, $rawMaterialMap) {
            if ($type === 'RawMaterial') {
                return optional($rawMaterialMap->get($id))->material_code ?? '';
            }

            if ($type === 'Product') {
                return '';
            }

            return optional($generalItemMap->get($id))->item_code ?? '';
        };

        $itemUnit = function ($type, $id) use ($rawMaterialMap, $productMap) {
            if ($type === 'RawMaterial') {
                return optional($rawMaterialMap->get($id))->unit_name ?? '';
            }

            if ($type === 'Product') {
                return optional($productMap->get($id))->product_size ?? '';
            }

            return '';
        };
    @endphp

    <style>
        .audit-shell {
            background: #f8fafc;
            min-height: 100vh;
        }

        .audit-hero {
            background: #fff;
            border: 1px solid #e7eaec;
            border-radius: 18px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
            padding: 20px 22px;
            margin-bottom: 20px;
        }

        .audit-title {
            font-size: 28px;
            font-weight: 900;
            color: #111827;
            margin-bottom: 6px;
        }

        .audit-subtitle {
            color: #6b7280;
            margin: 0;
            line-height: 1.6;
        }

        .audit-date-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 800;
            font-size: 13px;
            margin-top: 8px;
        }

        .audit-card {
            border: none;
            border-radius: 22px;
            padding: 24px;
            color: #fff;
            min-height: 150px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .10);
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .audit-card .card-glow {
            position: absolute;
            right: -20px;
            top: -20px;
            width: 115px;
            height: 115px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
        }

        .audit-card h5 {
            margin: 0;
            font-weight: 700;
            letter-spacing: .5px;
            color: #fff;
            opacity: .95;
        }

        .audit-card h1 {
            color: #fff;
            font-size: 34px;
            font-weight: 900;
            margin-top: 15px;
            margin-bottom: 0;
        }

        .audit-card small {
            color: rgba(255, 255, 255, .85);
        }

        .card-blue {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
        }

        .card-teal {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
        }

        .card-amber {
            background: linear-gradient(135deg, #b45309, #f59e0b);
        }

        .card-rose {
            background: linear-gradient(135deg, #be123c, #e11d48);
        }

        .audit-box {
            border-radius: 18px;
            border: 1px solid #e7eaec;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .audit-box .ibox-title {
            border-bottom: 1px solid #e7eaec;
            background: #fff;
            padding: 18px;
        }

        .audit-box .ibox-content {
            padding: 18px;
        }

        .audit-box-dark-title {
            background: #0f172a !important;
            color: #fff !important;
            padding: 14px 18px !important;
            font-weight: 800;
        }

        .audit-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
            color: #334155;
        }

        .audit-input,
        .audit-work-point-select,
        .audit-normal-select {
            height: 44px !important;
            border-radius: 12px !important;
            border: 1px solid #dbe3ee !important;
            width: 100% !important;
            background: #fff !important;
            display: block !important;
        }

        .audit-textarea {
            border-radius: 12px !important;
            border: 1px solid #dbe3ee;
            min-height: 85px;
        }

        .audit-btn {
            height: 44px;
            border-radius: 12px !important;
            font-weight: 800 !important;
        }

        .table>thead>tr>th {
            background: #f8fafc;
            color: #1f2937;
            font-weight: 800;
            white-space: nowrap;
        }

        .audit-empty {
            text-align: center;
            color: #ef4444;
            font-weight: 800;
            padding: 18px !important;
        }

        .badge-open {
            background: #16a34a;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 800;
        }

        .badge-approved {
            background: #2563eb;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 800;
        }

        .badge-closed {
            background: #64748b;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 800;
        }

        .badge-gain {
            background: #16a34a;
            color: #fff;
            padding: 5px 9px;
            border-radius: 6px;
            font-weight: 800;
        }

        .badge-loss {
            background: #dc2626;
            color: #fff;
            padding: 5px 9px;
            border-radius: 6px;
            font-weight: 800;
        }

        .badge-match {
            background: #2563eb;
            color: #fff;
            padding: 5px 9px;
            border-radius: 6px;
            font-weight: 800;
        }

        .audit-item-row {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 12px;
        }

        .audit-mini-title {
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .modal-content {
            border-radius: 18px;
            overflow: visible !important;
        }

        .modal-header {
            background: #0f172a;
            color: #fff;
        }

        .modal-title {
            font-weight: 900;
        }

        .modal-header .close {
            color: #fff;
            opacity: 1;
        }

        .modal,
        .modal-dialog,
        .modal-body {
            overflow: visible !important;
        }

        select.audit-item-select {
            width: 100% !important;
            display: block !important;
        }

        .select2-container,
        .select2-container--default,
        .select2-container--bootstrap4,
        .select2-container--bootstrap {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            z-index: 999999 !important;
        }

        .select2-container .select2-selection--single,
        .select2-container--bootstrap4 .select2-selection,
        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border-radius: 12px !important;
            border: 1px solid #dbe3ee !important;
            padding-top: 6px !important;
        }

        .select2-dropdown {
            z-index: 999999 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .audit-box,
            .audit-hero {
                box-shadow: none !important;
            }
        }
    </style>

    <div class="wrapper wrapper-content animated fadeInRight audit-shell">

        <div class="row">
            <div class="col-lg-8">
                <div class="audit-hero">
                    <h2 class="audit-title">
                        <i class="fa fa-search text-primary"></i>
                        Stock Audits
                    </h2>
                    <p class="audit-subtitle">
                        Register stock count audits for General Supply, Raw Materials and Products. Approve and close audits
                        after verification.
                    </p>

                    <ol class="breadcrumb" style="margin-top:12px;margin-bottom:0;background:transparent;padding-left:0;">
                        <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                        <li class="active"><strong>Stock Audits</strong></li>
                    </ol>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="audit-hero" style="min-height:126px;">
                    <div class="audit-date-chip">
                        <i class="fa fa-calendar"></i>
                        {{ \Carbon\Carbon::now()->format('l, Y-m-d') }}
                    </div>

                    <div style="margin-top:10px;color:#64748b;font-weight:700;">
                        Physical stock count control
                    </div>

                    <div style="margin-top:12px;">
                        @can('Register-Stock-Audits')
                            <button type="button" class="btn btn-primary audit-btn" data-toggle="modal"
                                data-target="#createAuditModal">
                                <i class="fa fa-plus"></i> New Stock Audit
                            </button>
                        @endcan

                        <button type="button" onclick="printReceipt('printArea')" class="btn btn-success audit-btn">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top:8px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="audit-card card-blue">
                    <div class="card-glow"></div>
                    <h5>TOTAL AUDITS</h5>
                    <h1>{{ number_format($totalAudits) }}</h1>
                    <small>All audit records</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="audit-card card-teal">
                    <div class="card-glow"></div>
                    <h5>OPEN AUDITS</h5>
                    <h1>{{ number_format($openAudits) }}</h1>
                    <small>Pending verification</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="audit-card card-amber">
                    <div class="card-glow"></div>
                    <h5>APPROVED</h5>
                    <h1>{{ number_format($approvedAudits) }}</h1>
                    <small>Ready for closure</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="audit-card card-rose">
                    <div class="card-glow"></div>
                    <h5>CLOSED</h5>
                    <h1>{{ number_format($closedAudits) }}</h1>
                    <small>Completed audits</small>
                </div>
            </div>
        </div>

        <div class="audit-box">
            <div class="ibox-title audit-box-dark-title">
                <i class="fa fa-list"></i> Stock Audit Register
            </div>

            <div class="ibox-content">
                <div id="printArea">
                    <div class="table-responsive">
                        <table id="stockAuditTable" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Audit Date</th>
                                    <th>Type</th>
                                    <th>Work Point</th>
                                    <th>Items</th>
                                    <th>System Qty</th>
                                    <th>Physical Qty</th>
                                    <th>Variance</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th class="no-print">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $key => $row)
                                    @php
                                        $systemTotal = collect($row->items ?? [])->sum('system_qty');
                                        $physicalTotal = collect($row->items ?? [])->sum('physical_qty');
                                        $varianceTotal = collect($row->items ?? [])->sum('variance_qty');
                                        $wp = $row->workpoint;
                                        $companyName = optional(optional($wp)->company)->company_name ?? '';
                                        $companyCode = optional(optional($wp)->company)->company_code ?? '';
                                    @endphp

                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $formatDate($row->audit_date ?? null) }}</td>
                                        <td>{{ $auditTypeLabel($row->audit_type) }}</td>
                                        <td>
                                            @if ($wp)
                                                <strong>{{ $wp->work_code ?? 'No Code' }}</strong> -
                                                {{ $wp->work_name }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $companyName }}{{ $companyCode ? ' (' . $companyCode . ')' : '' }}
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ collect($row->items ?? [])->count() }}</td>
                                        <td>{{ number_format($systemTotal, 2) }}</td>
                                        <td>{{ number_format($physicalTotal, 2) }}</td>
                                        <td>
                                            @if ($varianceTotal > 0)
                                                <span class="badge-gain">{{ number_format($varianceTotal, 2) }}</span>
                                            @elseif($varianceTotal < 0)
                                                <span class="badge-loss">{{ number_format($varianceTotal, 2) }}</span>
                                            @else
                                                <span class="badge-match">{{ number_format($varianceTotal, 2) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status === 'Closed')
                                                <span class="badge-closed">Closed</span>
                                            @elseif($row->status === 'Approved')
                                                <span class="badge-approved">Approved</span>
                                            @else
                                                <span class="badge-open">Open</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($row->creator)->name ?? (optional($row->creator)->username ?? 'N/A') }}
                                        </td>
                                        <td class="no-print">
                                            <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                                data-target="#viewAuditModal{{ $row->id }}">
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            @if ($row->status !== 'Closed')
                                                @can('Edit-Stock-Audit')
                                                    <button type="button" class="btn btn-warning btn-xs" data-toggle="modal"
                                                        data-target="#editAuditModal{{ $row->id }}">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                @endcan
                                            @endif

                                            @if ($row->status === 'Open')
                                                @can('Approve-Stock-Audit')
                                                    <a href="{{ route('sales.stock.audit.approve', encrypt($row->id)) }}"
                                                        onclick="return confirm('Approve this stock audit?')"
                                                        class="btn btn-primary btn-xs">
                                                        <i class="fa fa-check"></i>
                                                    </a>
                                                @endcan
                                            @endif

                                            @if ($row->status === 'Approved')
                                                @can('Approve-Stock-Audit')
                                                    <a href="{{ route('sales.stock.audit.close', encrypt($row->id)) }}"
                                                        onclick="return confirm('Close this stock audit? This cannot be edited after closing.')"
                                                        class="btn btn-success btn-xs">
                                                        <i class="fa fa-lock"></i>
                                                    </a>
                                                @endcan
                                            @endif

                                            @if ($row->status !== 'Closed')
                                                @can('Delete-Stock-Audit')
                                                    <a href="{{ route('sales.stock.audit.remove', encrypt($row->id)) }}"
                                                        onclick="return confirm('Are you sure you want to remove this stock audit?')"
                                                        class="btn btn-danger btn-xs">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="audit-empty">No Stock Audit Data Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- CREATE MODAL --}}
        @can('Register-Stock-Audits')
            <div class="modal fade" id="createAuditModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" style="width:95%; max-width:1200px;">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('sales.stock.audit.store') }}" class="audit-form">
                            @csrf

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">
                                    <i class="fa fa-plus"></i> Register Stock Audit
                                </h4>
                            </div>

                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="audit-label">Audit Date</label>
                                        <input type="date" name="audit_date" class="form-control audit-input"
                                            value="{{ old('audit_date', now()->toDateString()) }}" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="audit-label">Work Point</label>
                                        <select name="work_point_id" class="form-control audit-work-point-select" required>
                                            <option value="">Select Work Point</option>
                                            @foreach ($workPoints as $wp)
                                                @php
                                                    $companyName = optional($wp->company)->company_name ?? 'No Company';
                                                    $companyCode = optional($wp->company)->company_code ?? '';
                                                    $workCode = $wp->work_code ?? 'No Code';
                                                @endphp

                                                <option value="{{ $wp->id }}"
                                                    {{ old('work_point_id') == $wp->id ? 'selected' : '' }}>
                                                    {{ $workCode }} - {{ $wp->work_name }} |
                                                    {{ $companyName }}{{ $companyCode ? ' (' . $companyCode . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="audit-label">Audit Type</label>
                                        <select name="audit_type" class="form-control audit-normal-select audit-type"
                                            required>
                                            <option value="GeneralSupply"
                                                {{ old('audit_type') === 'GeneralSupply' ? 'selected' : '' }}>General Supply
                                            </option>
                                            <option value="RawMaterial"
                                                {{ old('audit_type') === 'RawMaterial' ? 'selected' : '' }}>Raw Material
                                            </option>
                                            <option value="Product" {{ old('audit_type') === 'Product' ? 'selected' : '' }}>
                                                Product</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="audit-label">Status</label>
                                        <select name="status" class="form-control audit-normal-select" required>
                                            <option value="Open" {{ old('status') === 'Open' ? 'selected' : '' }}>Open
                                            </option>
                                            <option value="Approved" {{ old('status') === 'Approved' ? 'selected' : '' }}>
                                                Approved</option>
                                            <option value="Closed" {{ old('status') === 'Closed' ? 'selected' : '' }}>Closed
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row" style="margin-top:14px;">
                                    <div class="col-md-12">
                                        <label class="audit-label">Remarks</label>
                                        <textarea name="remarks" class="form-control audit-textarea" placeholder="Enter audit remarks if any">{{ old('remarks') }}</textarea>
                                    </div>
                                </div>

                                <hr>

                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                                    <h4 style="font-weight:900;margin:0;">
                                        <i class="fa fa-cubes"></i> Audit Items
                                    </h4>

                                    <button type="button" class="btn btn-success add-audit-item">
                                        <i class="fa fa-plus"></i> Add Item
                                    </button>
                                </div>

                                <div class="audit-items-wrap" style="margin-top:14px;"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default audit-btn" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary audit-btn">
                                    <i class="fa fa-save"></i> Save Audit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        {{-- VIEW AND EDIT MODALS --}}
        @foreach ($rows as $row)
            <div class="modal fade" id="viewAuditModal{{ $row->id }}" tabindex="-1" role="dialog"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" style="width:95%; max-width:1200px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">
                                <i class="fa fa-eye"></i> Stock Audit Details
                            </h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Audit Date:</strong><br>
                                    {{ $formatDate($row->audit_date ?? null) }}
                                </div>

                                <div class="col-md-3">
                                    <strong>Work Point:</strong><br>
                                    {{ optional($row->workpoint)->work_name ?? '-' }}
                                </div>

                                <div class="col-md-3">
                                    <strong>Audit Type:</strong><br>
                                    {{ $auditTypeLabel($row->audit_type) }}
                                </div>

                                <div class="col-md-3">
                                    <strong>Status:</strong><br>
                                    {{ $row->status }}
                                </div>
                            </div>

                            <div class="row" style="margin-top:14px;">
                                <div class="col-md-4">
                                    <strong>Approved By:</strong><br>
                                    {{ optional($row->approver)->name ?? (optional($row->approver)->username ?? '-') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Approved At:</strong><br>
                                    {{ $formatDateTime($row->approved_at ?? null) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Closed At:</strong><br>
                                    {{ $formatDateTime($row->closed_at ?? null) }}
                                </div>
                            </div>

                            <div style="margin-top:14px;">
                                <strong>Remarks:</strong><br>
                                {{ $row->remarks ?? '-' }}
                            </div>

                            <hr>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Code</th>
                                            <th>Unit / Size</th>
                                            <th>System Qty</th>
                                            <th>Physical Qty</th>
                                            <th>Variance</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($row->items as $k => $item)
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ $itemName($item->item_type, $item->item_id) }}</td>
                                                <td>{{ $itemCode($item->item_type, $item->item_id) ?: '-' }}</td>
                                                <td>{{ $itemUnit($item->item_type, $item->item_id) ?: '-' }}</td>
                                                <td>{{ number_format((float) $item->system_qty, 2) }}</td>
                                                <td>{{ number_format((float) $item->physical_qty, 2) }}</td>
                                                <td>
                                                    @if ((float) $item->variance_qty > 0)
                                                        <span
                                                            class="badge-gain">{{ number_format((float) $item->variance_qty, 2) }}</span>
                                                    @elseif((float) $item->variance_qty < 0)
                                                        <span
                                                            class="badge-loss">{{ number_format((float) $item->variance_qty, 2) }}</span>
                                                    @else
                                                        <span
                                                            class="badge-match">{{ number_format((float) $item->variance_qty, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->remarks ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="audit-empty">No Items Found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default audit-btn" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($row->status !== 'Closed')
                @can('Edit-Stock-Audits')
                    <div class="modal fade edit-audit-modal" id="editAuditModal{{ $row->id }}" tabindex="-1"
                        role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" style="width:95%; max-width:1200px;">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('sales.stock.audit.update', encrypt($row->id)) }}"
                                    class="audit-form edit-audit-form">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title">
                                            <i class="fa fa-edit"></i> Edit Stock Audit
                                        </h4>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="audit-label">Audit Date</label>
                                                <input type="date" name="audit_date" class="form-control audit-input"
                                                    value="{{ $formatDate($row->audit_date ?? null) }}" required>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="audit-label">Work Point</label>
                                                <select name="work_point_id" class="form-control audit-work-point-select"
                                                    required>
                                                    <option value="">Select Work Point</option>
                                                    @foreach ($workPoints as $wp)
                                                        @php
                                                            $companyName =
                                                                optional($wp->company)->company_name ?? 'No Company';
                                                            $companyCode = optional($wp->company)->company_code ?? '';
                                                            $workCode = $wp->work_code ?? 'No Code';
                                                        @endphp

                                                        <option value="{{ $wp->id }}"
                                                            {{ (int) $row->work_point_id === (int) $wp->id ? 'selected' : '' }}>
                                                            {{ $workCode }} - {{ $wp->work_name }} |
                                                            {{ $companyName }}{{ $companyCode ? ' (' . $companyCode . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="audit-label">Audit Type</label>
                                                <select name="audit_type" class="form-control audit-normal-select audit-type"
                                                    required>
                                                    <option value="GeneralSupply"
                                                        {{ $row->audit_type === 'GeneralSupply' ? 'selected' : '' }}>General
                                                        Supply</option>
                                                    <option value="RawMaterial"
                                                        {{ $row->audit_type === 'RawMaterial' ? 'selected' : '' }}>Raw Material
                                                    </option>
                                                    <option value="Product"
                                                        {{ $row->audit_type === 'Product' ? 'selected' : '' }}>Product</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="audit-label">Status</label>
                                                <select name="status" class="form-control audit-normal-select" required>
                                                    <option value="Open" {{ $row->status === 'Open' ? 'selected' : '' }}>
                                                        Open</option>
                                                    <option value="Approved"
                                                        {{ $row->status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="Closed" {{ $row->status === 'Closed' ? 'selected' : '' }}>
                                                        Closed</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row" style="margin-top:14px;">
                                            <div class="col-md-12">
                                                <label class="audit-label">Remarks</label>
                                                <textarea name="remarks" class="form-control audit-textarea">{{ $row->remarks }}</textarea>
                                            </div>
                                        </div>

                                        <hr>

                                        <div
                                            style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                                            <h4 style="font-weight:900;margin:0;">
                                                <i class="fa fa-cubes"></i> Audit Items
                                            </h4>

                                            <button type="button" class="btn btn-success add-audit-item">
                                                <i class="fa fa-plus"></i> Add Item
                                            </button>
                                        </div>

                                        <div class="audit-items-wrap" style="margin-top:14px;">
                                            @foreach ($row->items as $i => $item)
                                                <div class="audit-item-row">
                                                    <div class="audit-mini-title">
                                                        Item Row
                                                        <button type="button"
                                                            class="btn btn-danger btn-xs pull-right remove-audit-item">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label class="audit-label">Item</label>
                                                            <select name="items[{{ $i }}][item_id]"
                                                                class="form-control audit-item-select"
                                                                data-selected="{{ $item->item_id }}" required>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="audit-label">System Qty</label>
                                                            <input type="number" step="0.0001" min="0"
                                                                name="items[{{ $i }}][system_qty]"
                                                                class="form-control audit-input system-qty"
                                                                value="{{ $item->system_qty }}" required>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="audit-label">Physical Qty</label>
                                                            <input type="number" step="0.0001" min="0"
                                                                name="items[{{ $i }}][physical_qty]"
                                                                class="form-control audit-input physical-qty"
                                                                value="{{ $item->physical_qty }}" required>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="audit-label">Variance</label>
                                                            <input type="text"
                                                                class="form-control audit-input variance-display"
                                                                value="{{ number_format((float) $item->variance_qty, 4, '.', '') }}"
                                                                readonly>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="audit-label">Remarks</label>
                                                            <input type="text" name="items[{{ $i }}][remarks]"
                                                                class="form-control audit-input" value="{{ $item->remarks }}"
                                                                placeholder="Optional">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default audit-btn"
                                            data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-warning audit-btn">
                                            <i class="fa fa-save"></i> Update Audit
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan
            @endif
        @endforeach

    </div>

    <script>
        window.auditItemsData = {
            GeneralSupply: @json($generalItemsJson),
            RawMaterial: @json($rawMaterialsJson),
            Product: @json($productsJson)
        };

        function auditOptionText(item) {
            var text = item.name || 'N/A';

            if (item.code) {
                text += ' | Code: ' + item.code;
            }

            if (item.unit) {
                text += ' | Unit/Size: ' + item.unit;
            }

            return text;
        }

        function safeInitItemSelect2(scope) {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
                return;
            }

            var $ = window.jQuery;
            var container = scope || document;

            $(container).find('select.audit-item-select').each(function() {
                var $select = $(this);

                $select.css('width', '100%');

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownAutoWidth: false,
                    dropdownParent: $select.closest('.modal').length ? $select.closest('.modal') : $(
                        document.body)
                });
            });
        }

        function fillAuditItemSelect(select, auditType, selectedId) {
            var items = window.auditItemsData[auditType] || [];

            select.innerHTML = '';

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select Item';
            select.appendChild(placeholder);

            items.forEach(function(item) {
                var option = document.createElement('option');
                option.value = item.id;
                option.textContent = auditOptionText(item);

                if (String(selectedId || '') === String(item.id)) {
                    option.selected = true;
                }

                select.appendChild(option);
            });
        }

        function getAuditType(form) {
            var typeSelect = form.querySelector('.audit-type');
            return typeSelect ? typeSelect.value : 'GeneralSupply';
        }

        function reindexRows(form) {
            var rows = form.querySelectorAll('.audit-item-row');

            rows.forEach(function(row, index) {
                row.querySelectorAll('[name]').forEach(function(field) {
                    field.name = field.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
            });
        }

        function calculateRowVariance(row) {
            var systemInput = row.querySelector('.system-qty');
            var physicalInput = row.querySelector('.physical-qty');
            var varianceInput = row.querySelector('.variance-display');

            var systemQty = parseFloat(systemInput ? systemInput.value : 0) || 0;
            var physicalQty = parseFloat(physicalInput ? physicalInput.value : 0) || 0;
            var variance = physicalQty - systemQty;

            if (varianceInput) {
                varianceInput.value = variance.toFixed(4);
            }
        }

        function refreshFormItems(form) {
            var auditType = getAuditType(form);
            var selects = form.querySelectorAll('.audit-item-select');

            selects.forEach(function(select) {
                var selectedId = select.getAttribute('data-selected') || select.value;

                fillAuditItemSelect(select, auditType, selectedId);
                select.setAttribute('data-selected', '');
            });

            safeInitItemSelect2(form);
        }

        function addAuditItemRow(form) {
            var wrap = form.querySelector('.audit-items-wrap');

            if (!wrap) {
                return;
            }

            var index = wrap.querySelectorAll('.audit-item-row').length;

            var row = document.createElement('div');
            row.className = 'audit-item-row';

            row.innerHTML =
                '<div class="audit-mini-title">' +
                'Item Row' +
                '<button type="button" class="btn btn-danger btn-xs pull-right remove-audit-item">' +
                '<i class="fa fa-times"></i>' +
                '</button>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-md-4">' +
                '<label class="audit-label">Item</label>' +
                '<select name="items[' + index + '][item_id]" class="form-control audit-item-select" required></select>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label class="audit-label">System Qty</label>' +
                '<input type="number" step="0.0001" min="0" name="items[' + index +
                '][system_qty]" class="form-control audit-input system-qty" value="0" required>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label class="audit-label">Physical Qty</label>' +
                '<input type="number" step="0.0001" min="0" name="items[' + index +
                '][physical_qty]" class="form-control audit-input physical-qty" value="0" required>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label class="audit-label">Variance</label>' +
                '<input type="text" class="form-control audit-input variance-display" value="0.0000" readonly>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label class="audit-label">Remarks</label>' +
                '<input type="text" name="items[' + index +
                '][remarks]" class="form-control audit-input" placeholder="Optional">' +
                '</div>' +
                '</div>';

            wrap.appendChild(row);

            var select = row.querySelector('.audit-item-select');
            fillAuditItemSelect(select, getAuditType(form), null);

            calculateRowVariance(row);
            reindexRows(form);
            safeInitItemSelect2(row);
        }

        function initializeAuditForms() {
            document.querySelectorAll('.audit-form').forEach(function(form) {
                var rows = form.querySelectorAll('.audit-item-row');

                if (rows.length === 0) {
                    addAuditItemRow(form);
                } else {
                    refreshFormItems(form);

                    rows.forEach(function(row) {
                        calculateRowVariance(row);
                    });
                }
            });
        }

        function printReceipt(ele) {
            var content = document.getElementById(ele);

            if (!content) {
                alert('Nothing to print');
                return;
            }

            var pri = window.open('', '_blank', 'height=842,width=595');
            var doc = pri.document.open();

            var style = '<style>' +
                '@page{size:A4 landscape;margin:12mm;}' +
                '*{box-sizing:border-box;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
                'html,body{margin:0;padding:0;background:#fff;color:#000;font-family:Arial,sans-serif;font-size:12px;}' +
                'table{width:100%;border-collapse:collapse;}' +
                'th,td{border:1px solid #d9e2f2;padding:8px;}' +
                '.no-print{display:none!important;}' +
                '</style>';

            doc.write('<html><head><title>Stock Audit Report</title>' + style + '</head><body>');
            doc.write(content.innerHTML);
            doc.write('</body></html>');
            doc.close();

            pri.focus();

            setTimeout(function() {
                pri.print();
            }, 400);
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeAuditForms();
            safeInitItemSelect2(document);

            document.addEventListener('click', function(event) {
                var addButton = event.target.closest('.add-audit-item');

                if (addButton) {
                    event.preventDefault();

                    var form = addButton.closest('form');

                    if (form) {
                        addAuditItemRow(form);
                    }

                    return;
                }

                var removeButton = event.target.closest('.remove-audit-item');

                if (removeButton) {
                    event.preventDefault();

                    var form = removeButton.closest('form');
                    var row = removeButton.closest('.audit-item-row');

                    if (!form || !row) {
                        return;
                    }

                    var rows = form.querySelectorAll('.audit-item-row');

                    if (rows.length <= 1) {
                        alert('At least one item is required.');
                        return;
                    }

                    row.remove();
                    reindexRows(form);
                }
            });

            document.addEventListener('change', function(event) {
                if (event.target.classList.contains('audit-type')) {
                    var form = event.target.closest('form');

                    if (form) {
                        form.querySelectorAll('.audit-item-select').forEach(function(select) {
                            select.setAttribute('data-selected', '');
                        });

                        refreshFormItems(form);
                    }
                }

                if (event.target.classList.contains('system-qty') || event.target.classList.contains(
                        'physical-qty')) {
                    var row = event.target.closest('.audit-item-row');

                    if (row) {
                        calculateRowVariance(row);
                    }
                }
            });

            document.addEventListener('keyup', function(event) {
                if (event.target.classList.contains('system-qty') || event.target.classList.contains(
                        'physical-qty')) {
                    var row = event.target.closest('.audit-item-row');

                    if (row) {
                        calculateRowVariance(row);
                    }
                }
            });

            if (window.jQuery) {
                window.jQuery('.modal').on('shown.bs.modal', function() {
                    initializeAuditForms();
                    safeInitItemSelect2(this);
                });

                if (window.jQuery.fn && window.jQuery.fn.DataTable) {
                    var auditTable = window.jQuery('#stockAuditTable');

                    if (auditTable.length) {
                        if (window.jQuery.fn.DataTable.isDataTable('#stockAuditTable')) {
                            auditTable.DataTable().destroy();
                        }

                        auditTable.DataTable({
                            pageLength: 25,
                            autoWidth: false,
                            responsive: true,
                            paging: true,
                            searching: true,
                            ordering: true,
                            retrieve: true
                        });
                    }
                }
            }
        });
    </script>
@endsection
