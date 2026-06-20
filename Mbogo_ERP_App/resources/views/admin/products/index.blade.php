@extends('layouts.salesMaster')

@section('content')

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8">
            <h2>
                <i class="fa fa-cubes"></i>
                Product Dashboard
            </h2>

            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active"><strong>Products</strong></li>
            </ol>
        </div>

        <div class="col-lg-4 text-right" style="padding-top:25px;">
            @can('Create-Product')
                <button class="btn btn-primary" data-toggle="modal" data-target="#productModal">
                    <i class="fa fa-plus"></i> Create Product
                </button>
            @endcan

            @can('Export-Product')
                <a href="{{ route('products.export') }}" class="btn btn-success">
                    <i class="fa fa-download"></i> Export
                </a>
            @endcan
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

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

        {{-- ================= SUMMARY ================= --}}
        <div class="row">

            <div class="col-lg-3">
                <div class="ibox"
                    style="background: linear-gradient(135deg,#1e3c72,#2a5298); color:white; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                    <div class="ibox-title" style="background:transparent;border:none;color:white;">
                        <h5><i class="fa fa-cubes"></i> Total Products</h5>
                    </div>
                    <div class="ibox-content text-center" style="background:transparent;border:none;">
                        <h1 style="font-size:40px;font-weight:bold;">{{ $products->count() }}</h1>
                        <small>All Registered Products</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="ibox"
                    style="background: linear-gradient(135deg,#11998e,#38ef7d); color:white; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                    <div class="ibox-title" style="background:transparent;border:none;color:white;">
                        <h5><i class="fa fa-check-circle"></i> Active</h5>
                    </div>
                    <div class="ibox-content text-center" style="background:transparent;border:none;">
                        <h1 style="font-size:40px;font-weight:bold;">{{ $products->where('status', 'Active')->count() }}</h1>
                        <small>Active Products</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="ibox"
                    style="background: linear-gradient(135deg,#f7971e,#ffd200); color:white; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                    <div class="ibox-title" style="background:transparent;border:none;color:white;">
                        <h5><i class="fa fa-warning"></i> Low Stock</h5>
                    </div>
                    <div class="ibox-content text-center" style="background:transparent;border:none;">
                        <h1 style="font-size:40px;font-weight:bold;">
                            {{ $products->filter(function ($p) { return (float) ($p->current_stock ?? 0) > 0 && (float) ($p->current_stock ?? 0) <= (float) ($p->reorder_level ?? 10); })->count() }}
                        </h1>
                        <small>Reorder Needed</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="ibox"
                    style="background: linear-gradient(135deg,#cb2d3e,#ef473a); color:white; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                    <div class="ibox-title" style="background:transparent;border:none;color:white;">
                        <h5><i class="fa fa-times-circle"></i> Out Of Stock</h5>
                    </div>
                    <div class="ibox-content text-center" style="background:transparent;border:none;">
                        <h1 style="font-size:40px;font-weight:bold;">
                            {{ $products->filter(function ($p) { return (float) ($p->current_stock ?? 0) <= 0; })->count() }}
                        </h1>
                        <small>Stock Finished</small>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= PRODUCT TABLE ================= --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">

                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fa fa-list"></i> Product List</h5>
                            </div>

                            <div class="col-md-6 text-right">
                                @can('Create-Product')
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#productModal">
                                        <i class="fa fa-plus"></i> Create Product
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row m-b-md">
                            <form method="GET" action="{{ route('products.index') }}">
                            <div class="input-group">
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search product..."
                                    value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </form>
                        </div>

                        <div class="table-responsive">
                            <table id="productsTable"
                                   class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Company</th>
                                        <th>Company Code</th>
                                        <th>Company Unit</th>
                                        <th>Work Point</th>
                                        <th>Work Code</th>
                                        <th>Product</th>
                                        <th>Product Code</th>
                                        <th>Details / UM</th>
                                        <th>Average Cost</th>
                                        <th>Selling Price</th>
                                        <th>Opening Stock</th>
                                        <th>Stock In</th>
                                        <th>Stock Out</th>
                                        <th>Current Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                        <th width="140">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($products->sortBy(function ($product) {
                                        return strtolower(trim($product->product_name ?? '')) . '|' . strtolower(trim($product->product_code ?? ''));
                                    })->values() as $key => $product)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>

                                            <td>{{ optional($product->company)->company_name ?? '-' }}</td>
                                            <td>{{ optional($product->company)->company_code ?? '-' }}</td>

                                            <td>{{ optional($product->businessUnit)->unit_name ?? '-' }}</td>

                                            <td>{{ optional($product->workPoint)->work_name ?? '-' }}</td>
                                            <td>{{ optional($product->workPoint)->work_code ?? '-' }}</td>

                                            <td><strong>{{ $product->product_name }}</strong></td>
                                            <td>{{ $product->product_code ?? 'PRD-' . str_pad($product->id, 5, '0', STR_PAD_LEFT) }}</td>

                                            <td>{{ $product->product_size ?? '-' }}</td>

                                            <td>{{ number_format((float) ($product->avg_cost ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($product->selling_price ?? 0), 2) }}</td>

                                            <td>
                                                <span class="label label-default">
                                                    {{ number_format((float) ($product->opening_stock ?? 0), 2) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="label label-info">
                                                    {{ number_format((float) ($product->stock_in ?? 0), 2) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="label label-warning">
                                                    {{ number_format((float) ($product->stock_out ?? 0), 2) }}
                                                </span>
                                            </td>

                                            <td>
                                                @if ((float) ($product->current_stock ?? 0) <= 0)
                                                    <span class="label label-danger">Out</span>
                                                @elseif((float) ($product->current_stock ?? 0) <= (float) ($product->reorder_level ?? 10))
                                                    <span class="label label-warning">
                                                        {{ number_format((float) $product->current_stock, 2) }}
                                                    </span>
                                                @else
                                                    <span class="label label-primary">
                                                        {{ number_format((float) $product->current_stock, 2) }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td>{{ number_format((float) ($product->reorder_level ?? 10), 2) }}</td>

                                            <td>
                                                @if ($product->status === 'Active')
                                                    <span class="label label-primary">Active</span>
                                                @else
                                                    <span class="label label-default">{{ $product->status }}</span>
                                                @endif
                                            </td>

                                            <td>
                                                @can('View-Product')
                                                    <a href="{{ route('products.show', $product->id) }}"
                                                    class="btn btn-xs btn-success"
                                                    title="View Product">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('Manage-Product')
                                                    <button type="button" class="btn btn-xs btn-warning btn-edit-product"
                                                        data-toggle="modal" data-target="#editProductModal"
                                                        data-action="{{ route('products.update', $product->id) }}"
                                                        data-product-name="{{ e($product->product_name) }}"
                                                        data-product-size="{{ e($product->product_size) }}"
                                                        data-company-id="{{ $product->company_id }}"
                                                        data-comp-unit-id="{{ $product->comp_unit_id }}"
                                                        data-work-point-id="{{ $product->work_point_id }}"
                                                        data-opening-stock="{{ $product->opening_stock }}"
                                                        data-avg-cost="{{ $product->avg_cost }}"
                                                        data-selling-price="{{ $product->selling_price }}"
                                                        data-reorder-level="{{ $product->reorder_level }}"
                                                        data-inventory-account-code="{{ $product->inventory_account_code }}"
                                                        data-cogs-account-code="{{ $product->cogs_account_code }}"
                                                        data-revenue-account-code="{{ $product->revenue_account_code }}"
                                                        data-status="{{ $product->status }}">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                @endcan

                                                @can('Print-Product')
                                                    <a href="{{ route('products.print', $product->id) }}"
                                                    class="btn btn-xs btn-info">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                @endcan
                                                @can('Delete-Product')
                                                    <form method="POST" action="{{ route('products.delete', $product->id) }}"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Delete this product?');">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button class="btn btn-xs btn-danger">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
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

    {{-- ================= CREATE PRODUCT MODAL ================= --}}
    @can('Create-Product')
        <div class="modal fade" id="productModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">
                            <i class="fa fa-plus"></i> Create Product
                        </h4>
                    </div>

                    <form method="POST" action="{{ route('products.store') }}">
                        @csrf

                        <div class="modal-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name</label>
                                        <input type="text" name="product_name" class="form-control"
                                            value="{{ old('product_name') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Details / Unit / Size</label>
                                        <input type="text" name="product_size" class="form-control"
                                            value="{{ old('product_size') }}"
                                            placeholder="Example: PCS, BOX, 90/25 MM, 10 GM">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Company Site</label>
                                        <select name="company_id" class="form-control select2-product">
                                            <option value="">Select Company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}"
                                                    {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->company_code }} - {{ $company->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Company Unit</label>
                                        <select name="comp_unit_id" class="form-control select2-product">
                                            <option value="">Select Unit</option>
                                            @foreach ($businessUnits as $unit)
                                                <option value="{{ $unit->id }}"
                                                    {{ old('comp_unit_id') == $unit->id ? 'selected' : '' }}>
                                                    {{ optional($unit->company)->company_code ? optional($unit->company)->company_code . ' - ' : '' }}{{ $unit->unit_code }}
                                                    - {{ $unit->unit_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Work Point</label>
                                        <select name="work_point_id" class="form-control select2-product">
                                            <option value="">Select Work Point</option>
                                            @foreach ($workPoints as $wp)
                                                <option value="{{ $wp->id }}"
                                                    {{ old('work_point_id') == $wp->id ? 'selected' : '' }}>
                                                    {{ $wp->work_code ? $wp->work_code . ' - ' : '' }}{{ $wp->work_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Opening Stock</label>
                                        <input type="number" step="0.0001" min="0" name="opening_stock"
                                            class="form-control" value="{{ old('opening_stock', 0) }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Average Cost</label>
                                        <input type="number" step="0.01" min="0" name="avg_cost"
                                            class="form-control" value="{{ old('avg_cost', 0) }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Selling Price</label>
                                        <input type="number" step="0.01" min="0" name="selling_price"
                                            class="form-control" value="{{ old('selling_price', 0) }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Reorder Level</label>
                                        <input type="number" step="0.0001" min="0" name="reorder_level"
                                            class="form-control" value="{{ old('reorder_level', 10) }}">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <hr>
                                    <h4 class="text-info">
                                        <i class="fa fa-bank"></i> Financial Account Mapping
                                    </h4>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Inventory Account</label>
                                        <select name="inventory_account_code" class="form-control select2-product">
                                            <option value="">Select Inventory Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->SubCode }}"
                                                    {{ old('inventory_account_code') == $acc->SubCode ? 'selected' : '' }}>
                                                    {{ $acc->SubCode }} - {{ $acc->SubDescription }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>COGS Account</label>
                                        <select name="cogs_account_code" class="form-control select2-product">
                                            <option value="">Select COGS Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->SubCode }}"
                                                    {{ old('cogs_account_code') == $acc->SubCode ? 'selected' : '' }}>
                                                    {{ $acc->SubCode }} - {{ $acc->SubDescription }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Revenue Account</label>
                                        <select name="revenue_account_code" class="form-control select2-product">
                                            <option value="">Select Revenue Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->SubCode }}"
                                                    {{ old('revenue_account_code') == $acc->SubCode ? 'selected' : '' }}>
                                                    {{ $acc->SubCode }} - {{ $acc->SubDescription }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="Active"
                                                {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>
                                                Inactive</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Save Product
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endcan

    {{-- ================= SINGLE EDIT PRODUCT MODAL ================= --}}
    @can('Manage-Product')
        <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-warning">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">
                            <i class="fa fa-edit"></i> Edit Product
                        </h4>
                    </div>

                    <form method="POST" id="editProductForm" action="">
                        @csrf
                        @method('PUT')

                        <div class="modal-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name</label>
                                        <input type="text" name="product_name" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Details / Unit / Size</label>
                                        <input type="text" name="product_size" class="form-control"
                                            placeholder="Example: PCS, BOX, 90/25 MM">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Company Site</label>
                                        <select name="company_id" class="form-control select2-product">
                                            <option value="">Select Company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}">
                                                    {{ $company->company_code }} - {{ $company->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Company Unit</label>
                                        <select name="comp_unit_id" class="form-control select2-product">
                                            <option value="">Select Unit</option>
                                            @foreach ($businessUnits as $unit)
                                                <option value="{{ $unit->id }}">
                                                    {{ optional($unit->company)->company_code ? optional($unit->company)->company_code . ' - ' : '' }}{{ $unit->unit_code }}
                                                    - {{ $unit->unit_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Work Point</label>
                                        <select name="work_point_id" class="form-control select2-product">
                                            <option value="">Select Work Point</option>
                                            @foreach ($workPoints as $wp)
                                                <option value="{{ $wp->id }}">
                                                    {{ $wp->work_code ? $wp->work_code . ' - ' : '' }}{{ $wp->work_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Opening Stock</label>
                                        <input type="number" step="0.0001" min="0" name="opening_stock"
                                            class="form-control" readonly>
                                        <small class="text-muted">Opening stock is not edited here to protect ledger balance.</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Average Cost</label>
                                        <input type="number" step="0.01" min="0" name="avg_cost"
                                            class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Selling Price</label>
                                        <input type="number" step="0.01" min="0" name="selling_price"
                                            class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Reorder Level</label>
                                        <input type="number" step="0.0001" min="0" name="reorder_level"
                                            class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <hr>
                                    <h4 class="text-info">
                                        <i class="fa fa-bank"></i> Financial Account Mapping
                                    </h4>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Inventory Account</label>
                                        <select name="inventory_account_code" class="form-control select2-product">
                                            <option value="">Select Inventory Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->SubCode }}">
                                                    {{ $acc->SubCode }} - {{ $acc->SubDescription }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>COGS Account</label>
                                        <select name="cogs_account_code" class="form-control select2-product">
                                            <option value="">Select COGS Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->SubCode }}">
                                                    {{ $acc->SubCode }} - {{ $acc->SubDescription }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Revenue Account</label>
                                        <select name="revenue_account_code" class="form-control select2-product">
                                            <option value="">Select Revenue Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->SubCode }}">
                                                    {{ $acc->SubCode }} - {{ $acc->SubDescription }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-save"></i> Update Product
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endcan

@endsection

@section('scripts')
<script>
$(document).ready(function () {

    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#productsTable')) {
        $('#productsTable').DataTable().destroy();
    }

    var table = $('#productsTable').DataTable({
        pageLength: 25,
        responsive: true,
        ordering: true,
        info: true,
        lengthChange: true,
        autoWidth: false,
        searching: true,
        dom: 'rtip',
        order: [[6, 'asc'], [7, 'asc']]
    });

    $(document).on('click', '.btn-edit-product', function () {
        var btn = $(this);
        var modal = $('#editProductModal');

        $('#editProductForm').attr('action', btn.data('action'));

        modal.find('input[name="product_name"]').val(btn.data('product-name'));
        modal.find('input[name="product_size"]').val(btn.data('product-size'));
        modal.find('select[name="company_id"]').val(btn.data('company-id'));
        modal.find('select[name="comp_unit_id"]').val(btn.data('comp-unit-id'));
        modal.find('select[name="work_point_id"]').val(btn.data('work-point-id'));
        modal.find('input[name="opening_stock"]').val(btn.data('opening-stock'));
        modal.find('input[name="avg_cost"]').val(btn.data('avg-cost'));
        modal.find('input[name="selling_price"]').val(btn.data('selling-price'));
        modal.find('input[name="reorder_level"]').val(btn.data('reorder-level'));
        modal.find('select[name="inventory_account_code"]').val(btn.data('inventory-account-code'));
        modal.find('select[name="cogs_account_code"]').val(btn.data('cogs-account-code'));
        modal.find('select[name="revenue_account_code"]').val(btn.data('revenue-account-code'));
        modal.find('select[name="status"]').val(btn.data('status'));

        if ($.fn.select2) {
            modal.find('.select2-product').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    width: '100%',
                    dropdownParent: modal
                });
            });

            modal.find('select[name="company_id"]').trigger('change');
            modal.find('select[name="comp_unit_id"]').trigger('change');
            modal.find('select[name="work_point_id"]').trigger('change');
            modal.find('select[name="inventory_account_code"]').trigger('change');
            modal.find('select[name="cogs_account_code"]').trigger('change');
            modal.find('select[name="revenue_account_code"]').trigger('change');
            modal.find('select[name="status"]').trigger('change');
        }
    });

    if ($.fn.select2) {
        $('.select2-product').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }

            $(this).select2({
                width: '100%',
                dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : $(document.body)
            });
        });
    }
});
</script>
@endsection