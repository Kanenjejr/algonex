@extends('layouts.salesMaster')

@section('content')

    <div class="wrapper wrapper-content">

        <div class="row">
            <div class="col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1">

                <div class="ibox">
                    <div class="ibox-title bg-primary">
                        <h5>Create Product</h5>
                    </div>

                    <div class="ibox-content">

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

                        <form method="POST" action="{{ route('products.store') }}">
                            @csrf

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
                                            value="{{ old('product_size') }}" placeholder="Example: PCS, BOX, 90/25 MM">
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

                            </div>

                            <hr>

                            <h4 class="text-info">
                                <i class="fa fa-bank"></i> Financial Account Mapping
                            </h4>

                            <div class="row">

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

                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>
                                        Active</option>
                                    <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>

                            <button class="btn btn-success btn-block">
                                <i class="fa fa-save"></i> Save Product
                            </button>

                            <a href="{{ route('products.index') }}" class="btn btn-default btn-block"
                                style="margin-top:8px;">
                                Back to List
                            </a>

                        </form>

                    </div>
                </div>

            </div>
        </div>

    </div>
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2-product').select2({
                    width: '100%'
                });
            }
        });
    </script>
@endsection
