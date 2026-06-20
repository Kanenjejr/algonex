@extends('layouts.salesMaster')

@section('content')

    <style>
        .select2-container {
            width: 100% !important;
        }

        select.select2-hidden-accessible {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            position: absolute !important;
        }

        #items_table thead th {
            color: #000 !important;
            background: #f3f3f4 !important;
            font-weight: bold !important;
            border: 1px solid #ddd !important;
            vertical-align: middle !important;
        }

        #items_table td {
            vertical-align: top !important;
        }

        #items_table .select2-container {
            width: 100% !important;
        }

        #items_table .select2-selection--single {
            min-height: 38px !important;
        }

        #items_table .select2-selection__rendered {
            line-height: 36px !important;
        }

        #items_table .select2-selection__arrow {
            height: 36px !important;
        }


        /* ================= SCROLL FIX =================
                                                            Some salesMaster layouts/fixed footers can stop this page from scrolling.
                                                            This forces the main page area to scroll normally and gives enough bottom space.
                                                          */
        html,
        body {
            height: auto !important;
            min-height: 100% !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        body.fixed-sidebar,
        body.fixed-nav,
        body.mini-navbar,
        #wrapper,
        #page-wrapper,
        .wrapper-content {
            height: auto !important;
            min-height: 100vh !important;
            overflow: visible !important;
        }

        .wrapper-content {
            padding-bottom: 120px !important;
        }

        .ibox-content {
            overflow: visible !important;
        }

        .footer,
        .fixed-footer {
            z-index: 1000;
        }
    </style>

    <div class="wrapper wrapper-content">

        {{-- ================= HEADER ================= --}}
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-8">
                <h2 class="dashboard-title">Sales Management Module</h2>
                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li><a href="{{ route('sales.dashboard') }}">Sales Management</a></li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li class="breadcrumb-item active"><strong>Proformas</strong></li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Current Date</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>{{ now()->format('l') }} , {{ now()->toDateString() }}</strong>
                    </li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Time</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>
                            <table>
                                <tr>
                                    <td id="Hour" style="color:green;"></td>
                                    <td id="Minut" style="color:green;"></td>
                                    <td id="Second" style="color:red;"></td>
                                </tr>
                            </table>
                        </strong>
                    </li>
                </ol>
            </div>
        </div>

        <script>
            function timedMsg() {
                setInterval(change_time, 1000);
            }

            function change_time() {
                const d = new Date();
                document.getElementById('Hour').innerHTML = String(d.getHours()).padStart(2, '0') + ':';
                document.getElementById('Minut').innerHTML = String(d.getMinutes()).padStart(2, '0') + ':';
                document.getElementById('Second').innerHTML = String(d.getSeconds()).padStart(2, '0');
            }
            timedMsg();
        </script>

        {{-- ================= CREATE PROFORMA ================= --}}
        @can('Create-Proforma')
            <div class="ibox mt-3">
                <div class="ibox-title bg-primary">
                    <h5><i class="fa fa-plus"></i> Create Proforma</h5>
                </div>

                <div class="ibox-content">
                    <form method="POST" action="{{ route('proforma.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-3">
                                <label>Invoice Type</label>
                                <select name="invoice_type" id="invoice_type" class="form-control select2_demo_2">
                                    <option value="local" {{ old('invoice_type') == 'local' ? 'selected' : '' }}>Proforma
                                        Invoice (Local)</option>
                                    <option value="export" {{ old('invoice_type') == 'export' ? 'selected' : '' }}>Commercial
                                        Invoice (Export)</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Currency *</label>
                                <select name="currency" id="currency" class="form-control select2_demo_2" required>
                                    @foreach (['TZS', 'USD', 'EUR', 'GBP', 'KES', 'UGX', 'RWF', 'ZAR', 'AED', 'CNY', 'INR'] as $cur)
                                        <option value="{{ $cur }}"
                                            {{ old('currency', old('invoice_type') == 'export' ? 'USD' : 'TZS') == $cur ? 'selected' : '' }}>
                                            {{ $cur }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Exchange Rate <small>(optional)</small></label>
                                <input type="number" step="0.0001" min="0" name="exchange_rate" id="exchange_rate" class="form-control"
                                    value="{{ old('exchange_rate', $proforma->exchange_rate ?? '') }}">
                                <small class="text-muted">Used to show TZS and USD amounts together.</small>
                            </div>

                            <div class="col-md-3">
                                <label>Item Type</label>
                                <select id="item_type" class="form-control select2_demo_2">
                                    <option value="product">Product</option>
                                    <option value="service">Service Charge</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Company *</label>
                                <select name="company_id" id="company" class="form-control select2_demo_2" required>
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->id }}"
                                            {{ old('company_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->company_code }} - {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Business Unit *</label>
                                <select name="business_unit_id" id="business" class="form-control select2_demo_2" required>
                                    <option value="">Select Business Unit</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Work Point *</label>
                                <select name="work_point_id" id="work_point" class="form-control select2_demo_2" required>
                                    <option value="">Select Work Point</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Customer *</label>
                                <select name="customer_id" id="customer_id" class="form-control select2_demo_2" required>
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" data-tin="{{ $c->tin_number }}"
                                            data-country="{{ $c->country }}"
                                            {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->customer_code ?? '' }} - {{ $c->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= ITEMS ================= --}}
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items_table">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>Description</th>
                                        <th style="width:210px;">Qty</th>
                                        <th style="width:120px;">Units</th>
                                        <th style="width:160px;">Unit Price<br><small>(Incl. VAT)</small></th>
                                        <th style="width:160px;">Actual Price<br><small>(Excl. VAT)</small></th>
                                        <th style="width:160px;">VAT Amount</th>
                                        <th style="width:160px;">Total Amount</th>
                                        <th style="width:70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <select name="items[0][product_id]"
                                                class="form-control product_select select2_demo_2">
                                                <option value="">-- Select Item --</option>
                                            </select>

                                            <input type="hidden" name="items[0][item_type]" class="item_type_input"
                                                value="product">
                                            <input type="hidden" name="items[0][product_name]" class="product_name">

                                            <input type="text" name="items[0][description]"
                                                class="form-control mt-1 description_input" placeholder="Enter description">
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:5px;">
                                                <button type="button" class="btn btn-sm btn-secondary qty_minus">-</button>
                                                <input type="number" name="items[0][qty]" class="form-control qty"
                                                    value="1" min="1" step="0.000001">
                                                <button type="button" class="btn btn-sm btn-secondary qty_plus">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="items[0][unit]" class="form-control unit_select select2_demo_2">
                                                <option value="PCS">PCS</option>
                                                <option value="BAGS">BAGS</option>
                                                <option value="CASES">CASES</option>
                                                <option value="REELS">REELS</option>
                                                <option value="UNITS">UNITS</option>
                                                <option value="service">SERVICES</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[0][price]" class="form-control price"
                                                step="0.0001"></td>
                                        <td><input type="number" class="form-control actual_price" readonly step="0.0001">
                                        </td>
                                        <td><input type="number" class="form-control row_vat" readonly step="0.0001"></td>
                                        <td><input type="number" name="items[0][total]" class="form-control total" readonly
                                                step="0.000001">
                                        </td>
                                        <td><button type="button" class="btn btn-danger remove">X</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                        <button type="button" id="addRow" class="btn btn-primary">+ Add Item</button>

                        <hr>
                        {{-- ================= TOTAL ================= --}}
                        <div class="row">
                            <div class="col-md-3">
                                <label>Subtotal (Excl. VAT)</label>
                                <input type="number" name="subtotal" id="subtotal" class="form-control" readonly
                                    step="0.000001">
                            </div>
                            <div class="col-md-3">
                                <label>VAT Amount</label>
                                <input type="number" name="vat" id="vat" class="form-control" readonly
                                    step="0.000001">
                            </div>
                            <div class="col-md-3">
                                <label>Total Amount</label>
                                <input type="number" name="total" id="grand_total" class="form-control" readonly
                                    step="0.000001">
                            </div>
                        </div>

                        <hr>

                        {{-- ================= BANK ================= --}}
                        <div class="row">
                            <div class="col-md-4">
                                <label>Select Bank</label>
                                <select name="bank_id" class="form-control select2_demo_2">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id }}" {{ old('bank_id') == $b->id ? 'selected' : '' }}>
                                            {{ $b->SubCode }} - {{ $b->SubDescription }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Account Number</label>
                                <input type="text" name="account_number" value="{{ old('account_number') }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Swift Code</label>
                                <input type="text" name="swift_code" value="{{ old('swift_code') }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-4 mt-2">
                                <label>Branch</label>
                                <input type="text" name="branch" value="{{ old('branch') }}" class="form-control">
                            </div>
                        </div>

                        <hr>

                        <button class="btn btn-success"><i class="fa fa-save"></i> Save Proforma</button>
                        <button type="reset" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</button>
                    </form>
                </div>
            </div>
        @endcan

        {{-- ================= PROFORMA LIST ================= --}}
        @can('View-Proforma')
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Proforma List</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped dataTables-example">
                            <thead>
                                <tr>
                                    <th>P.Invoice #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Company</th>
                                    <th>Business Unit</th>
                                    <th>Location</th>
                                    <th>Description</th>
                                    <th>Subtotal</th>
                                    <th>VAT</th>
                                    <th>Total</th>
                                    <th>Currency</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $p)
                                    @php
                                        $status = strtolower($p->status ?? 'draft');
                                        $encryptedId = \Illuminate\Support\Facades\Crypt::encryptString($p->id);
                                    @endphp
                                    <tr>
                                        <td>{{ $p->proforma_no }}</td>
                                        <td>{{ $p->created_at ? $p->created_at->format('d M Y') : '-' }}</td>
                                        <td>{{ $p->customer->customer_name ?? '-' }}</td>
                                        <td>{{ $p->company->company_name ?? '-' }}</td>
                                        <td>{{ $p->businessUnit->unit_name ?? '-' }}</td>
                                        <td>{{ $p->workPoint->work_name ?? '-' }}</td>
                                        <td>
                                            @foreach ($p->items as $item)
                                                <div>{{ $item->description }}</div>
                                            @endforeach
                                        </td>
                                        <td>{{ number_format($p->subtotal ?? 0, 4) }}</td>
                                        <td>{{ number_format($p->vat ?? 0, 4) }}</td>
                                        <td><strong>{{ number_format($p->total ?? 0, 4) }}</strong></td>
                                        <td>{{ $p->currency ?? (($p->invoice_type ?? 'local') == 'export' ? 'USD' : 'TZS') }}
                                        </td>
                                        <td>
                                            @if ($status == 'draft')
                                                <span class="label label-warning">Draft</span>
                                            @elseif($status == 'approved')
                                                <span class="label label-success">Approved</span>
                                            @elseif($status == 'rejected')
                                                <span class="label label-danger">Rejected</span>
                                            @else
                                                <span class="label label-info">{{ ucfirst($p->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $paymentStatus = strtolower($p->payment_status ?? 'unpaid'); @endphp
                                            @if ($paymentStatus == 'paid')
                                                <span class="label label-success">PAID</span>
                                            @elseif($paymentStatus == 'partial')
                                                <span class="label label-warning">PARTIAL</span>
                                            @else
                                                <span class="label label-danger">UNPAID</span>
                                            @endif
                                            <br>
                                            <small>{{ number_format($p->paid_amount ?? 0, 6) }}</small>
                                        </td>
                                        <td>
                                            @can('View-Proforma')
                                                <a href="{{ route('proforma.view', $encryptedId) }}"
                                                    class="btn btn-warning btn-sm">View</a>
                                            @endcan

                                            @can('Edit-Proforma')
                                                @if (in_array($status, ['draft', 'rejected']))
                                                    <a href="{{ route('proforma.edit', $encryptedId) }}"
                                                        class="btn btn-info btn-sm">Edit</a>
                                                @endif
                                            @endcan

                                            @can('Approve-Proforma')
                                                @if ($status == 'draft')
                                                    <form action="{{ route('proforma.approve', $encryptedId) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="approval_action" value="approve">
                                                        <button class="btn btn-success btn-sm"
                                                            onclick="return confirm('Approve this proforma and post accounting transaction?')">Approve</button>
                                                    </form>
                                                    <form action="{{ route('proforma.approve', $encryptedId) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="approval_action" value="reject">
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Reject this proforma? It will remain editable for correction and resubmission.')">Reject</button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('Delete-Proforma')
                                                @if (in_array($status, ['draft', 'rejected']))
                                                    <form action="{{ route('proforma.delete', $encryptedId) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Delete this proforma?')">Delete</button>
                                                    </form>
                                                @endif
                                            @endcan

                                            <a href="{{ route('proforma.print', $encryptedId) }}"
                                                class="btn btn-primary btn-sm" target="_blank">Print PDF</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">No Proforma Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let index = document.querySelectorAll('#items_table tbody tr').length;
            let currentType = document.getElementById('item_type') ? document.getElementById('item_type').value :
                'product';

            const companySelect = document.getElementById('company');
            const businessSelect = document.getElementById('business');
            const workPointSelect = document.getElementById('work_point');
            const customerSelect = document.getElementById('customer_id');
            const currencySelect = document.getElementById('currency');

            /*
            |--------------------------------------------------------------------------
            | KEEP YOUR EXISTING COMPANY / BUSINESS UNIT / WORK POINT LOGIC
            |--------------------------------------------------------------------------
            | These fields are still used for saving Proforma.
            | They are NOT used for filtering products/services anymore.
            |--------------------------------------------------------------------------
            */
            const businessUnitsUrl = "{{ url('/admin/get-business-units') }}/__COMPANY_ID__";
            const workPointsUrl = "{{ url('/admin/get-work-points') }}/__UNIT_ID__";

            /*
            |--------------------------------------------------------------------------
            | NEW ALL PRODUCTS / SERVICES URLS
            |--------------------------------------------------------------------------
            | Products and services now load from the whole system.
            |--------------------------------------------------------------------------
            */
            const allProductsUrl = "{{ route('proforma.all.products') }}";
            const allServicesUrl = "{{ route('proforma.all.services') }}";

            const selectedCompany = "{{ old('company_id', isset($proforma) ? $proforma->company_id : '') }}";
            const selectedBusiness =
                "{{ old('business_unit_id', isset($proforma) ? $proforma->business_unit_id : '') }}";
            const selectedWorkPoint =
                "{{ old('work_point_id', isset($proforma) ? $proforma->work_point_id : '') }}";

            function initDynamicSelect2() {
                if (!window.$ || !$.fn.select2) {
                    return;
                }

                $('.product_select.select2_demo_2, .unit_select.select2_demo_2, #item_type, #invoice_type, #currency, #company, #business, #work_point, #customer_id')
                    .each(function() {
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2('destroy');
                        }

                        $(this).select2({
                            theme: 'bootstrap4',
                            width: '100%'
                        });
                    });
            }

            function destroySelect2(select) {
                if (window.$ && $.fn.select2 && $(select).hasClass('select2-hidden-accessible')) {
                    $(select).select2('destroy');
                }
            }

            function getRowState(row) {
                const select = row.querySelector('.product_select');
                const selectedOption = select ? select.options[select.selectedIndex] : null;

                return {
                    selectedId: select ? select.value : '',
                    productName: row.querySelector('.product_name')?.value || selectedOption?.getAttribute(
                        'data-name') || '',
                    description: row.querySelector('.description_input')?.value || '',
                    price: row.querySelector('.price')?.value || selectedOption?.getAttribute('data-price') || '',
                    unit: row.querySelector('.unit_select')?.value || selectedOption?.getAttribute('data-unit') ||
                        'pcs',
                    qty: row.querySelector('.qty')?.value || 1,
                    itemType: row.querySelector('.item_type_input')?.value || currentType
                };
            }

            function setSelectedOption(select, state) {
                select.innerHTML = '';

                let defaultText = currentType === 'service' ?
                    '-- Select Service --' :
                    '-- Select Product --';

                let first = document.createElement('option');
                first.value = state.selectedId || '';
                first.text = state.productName || defaultText;
                first.selected = true;

                if (state.productName) {
                    first.setAttribute('data-name', state.productName);
                    first.setAttribute('data-price', state.price || 0);
                    first.setAttribute('data-unit', state.unit || 'pcs');
                }

                select.appendChild(first);
            }

            function clearProductDropdowns(clearNames = false) {
                document.querySelectorAll('.product_select').forEach(select => {
                    let row = select.closest('tr');
                    let state = getRowState(row);

                    destroySelect2(select);

                    if (clearNames && row) {
                        state = {
                            selectedId: '',
                            productName: '',
                            description: '',
                            price: '',
                            unit: currentType === 'service' ? 'service' : 'pcs',
                            qty: row.querySelector('.qty')?.value || 1,
                            itemType: currentType
                        };

                        if (row.querySelector('.product_name')) {
                            row.querySelector('.product_name').value = '';
                        }

                        if (row.querySelector('.description_input')) {
                            row.querySelector('.description_input').value = '';
                        }

                        if (row.querySelector('.price')) {
                            row.querySelector('.price').value = '';
                        }

                        if (row.querySelector('.total')) {
                            row.querySelector('.total').value = '';
                        }

                        if (row.querySelector('.actual_price')) {
                            row.querySelector('.actual_price').value = '';
                        }

                        if (row.querySelector('.row_vat')) {
                            row.querySelector('.row_vat').value = '';
                        }

                        if (row.querySelector('.item_type_input')) {
                            row.querySelector('.item_type_input').value = currentType;
                        }

                        if (row.querySelector('.unit_select')) {
                            row.querySelector('.unit_select').value = currentType === 'service' ?
                                'service' : 'pcs';
                        }
                    }

                    setSelectedOption(select, state);
                });

                initDynamicSelect2();
                calculate();
            }

            function loadBusinessUnits(companyId, selectedUnit = null, selectedPoint = null) {
                $('#business')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                $('#work_point')
                    .empty()
                    .append('<option value="">Select Work Point</option>')
                    .trigger('change.select2');

                if (!companyId) {
                    $('#business')
                        .empty()
                        .append('<option value="">Select Business Unit</option>')
                        .trigger('change.select2');
                    return;
                }

                let url = businessUnitsUrl.replace('__COMPANY_ID__', encodeURIComponent(companyId));

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        $('#business')
                            .empty()
                            .append('<option value="">Select Business Unit</option>');

                        data.forEach(unit => {
                            $('#business').append(
                                $('<option>', {
                                    value: unit.id,
                                    text: `${unit.unit_code} - ${unit.unit_name}`,
                                    selected: String(unit.id) === String(selectedUnit)
                                })
                            );
                        });

                        $('#business').trigger('change.select2');

                        if (selectedUnit) {
                            loadWorkPoints(selectedUnit, selectedPoint);
                        }
                    });
            }

            function loadWorkPoints(unitId, selectedPoint = null) {
                $('#work_point')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                if (!unitId) {
                    $('#work_point')
                        .empty()
                        .append('<option value="">Select Work Point</option>')
                        .trigger('change.select2');
                    return;
                }

                let url = workPointsUrl.replace('__UNIT_ID__', encodeURIComponent(unitId));

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        $('#work_point')
                            .empty()
                            .append('<option value="">Select Work Point</option>');

                        data.forEach(work => {
                            $('#work_point').append(
                                $('<option>', {
                                    value: work.id,
                                    text: `${work.work_code} - ${work.work_name}`,
                                    selected: String(work.id) === String(selectedPoint)
                                })
                            );
                        });

                        $('#work_point').trigger('change.select2');
                    });
            }

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT CHANGE
            |--------------------------------------------------------------------------
            | Products/services no longer depend on company, business unit, or work point.
            | This function now loads all products/services directly.
            |--------------------------------------------------------------------------
            */
            function loadItems(targetRow = null) {
                let url = currentType === 'product' ? allProductsUrl : allServicesUrl;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        let rows = targetRow ? [targetRow] :
                            Array.from(document.querySelectorAll('#items_table tbody tr'));

                        rows.forEach(row => {
                            let select = row.querySelector('.product_select');

                            if (!select) return;

                            let state = getRowState(row);

                            destroySelect2(select);
                            setSelectedOption(select, state);

                            if (!data || data.length <= 0) {
                                let empty = document.createElement('option');
                                empty.value = '';
                                empty.text = 'No Data Found';
                                empty.disabled = true;
                                select.appendChild(empty);
                                return;
                            }

                            data.forEach(item => {
                                let option = document.createElement('option');

                                if (currentType === 'service') {
                                    option.value = item.id;
                                    option.text = item.service_name + ' | Price: ' + parseFloat(
                                        item.price || 0).toFixed(4);

                                    option.setAttribute('data-name', item.service_name);
                                    option.setAttribute('data-price', item.price || 0);
                                    option.setAttribute('data-unit', item.unit || 'service');
                                } else {
                                    option.value = item.id;
                                    option.text = item.product_name + ' | Stock: ' +
                                        parseFloat(item.current_stock || 0).toFixed(4) +
                                        ' | Price: ' + parseFloat(item.price || 0).toFixed(4);

                                    option.setAttribute('data-name', item.product_name);
                                    option.setAttribute('data-price', item.price || 0);
                                    option.setAttribute('data-stock', item.current_stock || 0);
                                    option.setAttribute('data-unit', item.unit || 'pcs');
                                }

                                select.appendChild(option);
                            });

                            if (state.selectedId) {
                                select.value = state.selectedId;
                            }
                        });

                        initDynamicSelect2();
                        calculate();
                    })
                    .catch(error => {
                        console.error('Failed to load products/services:', error);
                    });
            }


            let syncingCurrency = false;

            function syncCurrencyWithInvoiceType() {
                if (!currencySelect || syncingCurrency) {
                    return;
                }

                let invoiceType = document.getElementById('invoice_type')?.value || 'local';
                syncingCurrency = true;

                if (invoiceType === 'local') {
                    currencySelect.value = 'TZS';
                } else if (!currencySelect.value || currencySelect.value === 'TZS') {
                    currencySelect.value = 'USD';
                }

                if (window.$ && $.fn.select2) {
                    $(currencySelect).trigger('change.select2');
                }

                syncingCurrency = false;
            }

            function selectedCurrency() {
                return currencySelect ? (currencySelect.value || 'TZS') : 'TZS';
            }

            function totalDecimals() {
                return selectedCurrency() === 'TZS' ? 0 : 2;
            }

            
            function calculate() {
                let subtotal = 0;
                let vat = 0;
                let grandTotal = 0;

                let invoiceType = document.getElementById('invoice_type')?.value || 'local';
                let customerOption = customerSelect ? customerSelect.options[customerSelect.selectedIndex] : null;
                let tin = customerOption ? customerOption.getAttribute('data-tin') : '';
                let vatRate = 0;

                if (invoiceType === 'local' && tin && tin.trim() !== '') {
                    vatRate = 0.18;
                }

                document.querySelectorAll('#items_table tbody tr').forEach(row => {
                    let qty = parseFloat(row.querySelector('.qty')?.value || 0);
                    let grossUnitPrice = parseFloat(row.querySelector('.price')?.value || 0);

                    // User enters VAT-inclusive unit price. Actual price below excludes VAT.
                    let actualUnitPrice = grossUnitPrice;
                    if (vatRate > 0) {
                        actualUnitPrice = grossUnitPrice / (1 + vatRate);
                    }

                    let rowSubtotal = qty * actualUnitPrice;
                    let rowVat = vatRate > 0 ? (qty * grossUnitPrice - rowSubtotal) : 0;
                    let rowTotalAmount = qty * grossUnitPrice;

                    if (row.querySelector('.actual_price')) {
                        row.querySelector('.actual_price').value = actualUnitPrice.toFixed(4);
                    }

                    if (row.querySelector('.row_vat')) {
                        row.querySelector('.row_vat').value = rowVat.toFixed(4);
                    }

                    if (row.querySelector('.total')) {
                        row.querySelector('.total').value = rowTotalAmount.toFixed(4);
                    }

                    subtotal += rowSubtotal;
                    vat += rowVat;
                    grandTotal += rowTotalAmount;
                });

                const decimals = 2;

                subtotal = Math.round(subtotal * 100) / 100;
                vat = Math.round(vat * 100) / 100;
                grandTotal = Math.round(grandTotal * 100) / 100;

                if (vatRate <= 0) {
                    vat = 0;
                    grandTotal = subtotal;
                }

                if (document.getElementById('subtotal')) {
                    document.getElementById('subtotal').value = subtotal.toFixed(decimals);
                }

                if (document.getElementById('vat')) {
                    document.getElementById('vat').value = vat.toFixed(decimals);
                }

                if (document.getElementById('grand_total')) {
                    document.getElementById('grand_total').value = grandTotal.toFixed(decimals);
                }

                if (document.getElementById('subtotal_view')) {
                    document.getElementById('subtotal_view').value = subtotal.toFixed(decimals);
                }

                if (document.getElementById('vat_view')) {
                    document.getElementById('vat_view').value = vat.toFixed(decimals);
                }

                if (document.getElementById('grand_total_view')) {
                    document.getElementById('grand_total_view').value = grandTotal.toFixed(decimals);
                }
            }


        $('#company').on('change', function() {
            loadBusinessUnits($(this).val(), null, null);
        });

        $('#business').on('change', function() {
            loadWorkPoints($(this).val(), null);
        });

        $('#work_point').on('change', function() {
            /*
             | Do not reload or filter products by work point anymore.
             | Work point remains only for saving the Proforma.
             */
        });

        $('#item_type').on('change', function() {
            currentType = this.value;
            clearProductDropdowns(true);
            loadItems();
        });


        $('#invoice_type').on('change', function() {
            syncCurrencyWithInvoiceType();
            calculate();
        });

        $('#customer_id, #currency').on('change', function() {
            calculate();
        });

        syncCurrencyWithInvoiceType();

        const initialCompany = selectedCompany || (companySelect ? companySelect.value : '');
        if (initialCompany) {
            loadBusinessUnits(initialCompany, selectedBusiness, selectedWorkPoint);
        }

        document.getElementById('addRow').addEventListener('click', function() {
            let tbody = document.querySelector('#items_table tbody');

            let rowHtml = `
                <tr>
                    <td>${index + 1}</td>

                    <td>
                        <select name="items[${index}][product_id]" class="form-control product_select select2_demo_2">
                            <option value="">-- Select Item --</option>
                        </select>

                        <input type="hidden" name="items[${index}][item_type]" class="item_type_input" value="${currentType}">
                        <input type="hidden" name="items[${index}][product_name]" class="product_name">

                        <input type="text"
                            name="items[${index}][description]"
                            class="form-control mt-1 description_input"
                            placeholder="Enter description">
                    </td>

                    <td>
                        <div style="display:flex; gap:5px;">
                            <button type="button" class="btn btn-sm btn-secondary qty_minus">-</button>
                            <input type="number" name="items[${index}][qty]" class="form-control qty" value="1" min="1" step="0.000001">
                            <button type="button" class="btn btn-sm btn-secondary qty_plus">+</button>
                        </div>
                    </td>

                    <td>
                        <select name="items[${index}][unit]" class="form-control unit_select select2_demo_2">
                            <option value="pcs">pcs</option>
                            <option value="bags">bags</option>
                            <option value="case">case</option>
                            <option value="reels">reels</option>
                            <option value="units">units</option>
                            <option value="service">service</option>
                        </select>
                    </td>

                    <td>
                        <input type="number" name="items[${index}][price]" class="form-control price" step="0.000001">
                    </td>

                    <td>
                        <input type="number" class="form-control actual_price" readonly step="0.0001">
                    </td>

                    <td>
                        <input type="number" class="form-control row_vat" readonly step="0.0001">
                    </td>

                    <td>
                        <input type="number" name="items[${index}][total]" class="form-control total" readonly step="0.000001">
                    </td>

                    <td>
                        <button type="button" class="btn btn-danger remove">X</button>
                    </td>
                </tr>
            `;

            tbody.insertAdjacentHTML('beforeend', rowHtml);

            let newRow = tbody.lastElementChild;

            if (newRow.querySelector('.item_type_input')) {
                newRow.querySelector('.item_type_input').value = currentType;
            }

            if (newRow.querySelector('.unit_select')) {
                newRow.querySelector('.unit_select').value = currentType === 'service' ? 'service' :
                    'pcs';
            }

            index++;

            initDynamicSelect2();
            loadItems(newRow);
            calculate();
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product_select')) {
                let row = e.target.closest('tr');
                let option = e.target.options[e.target.selectedIndex];

                if (!row || !option) return;

                let itemName = option.getAttribute('data-name') || '';
                let price = option.getAttribute('data-price') || 0;
                let unit = option.getAttribute('data-unit') || (currentType === 'service' ? 'service' :
                    'pcs');

                if (row.querySelector('.product_name')) {
                    row.querySelector('.product_name').value = itemName;
                }

                if (row.querySelector('.description_input')) {
                    row.querySelector('.description_input').value = itemName;
                }

                if (row.querySelector('.price')) {
                    row.querySelector('.price').value = price;
                }

                if (row.querySelector('.unit_select')) {
                    row.querySelector('.unit_select').value = unit;
                    $(row.querySelector('.unit_select')).trigger('change.select2');
                }

                if (row.querySelector('.item_type_input')) {
                    row.querySelector('.item_type_input').value = currentType;
                }

                calculate();
            }

            if (
                e.target.classList.contains('qty') ||
                e.target.classList.contains('price') ||
                e.target.classList.contains('unit_select')
            ) {
                calculate();
            }
        });

        document.addEventListener('input', function(e) {
            if (
                e.target.classList.contains('qty') ||
                e.target.classList.contains('price')
            ) {
                calculate();
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove')) {
                let rows = document.querySelectorAll('#items_table tbody tr');

                if (rows.length <= 1) {
                    alert('At least one item is required');
                    return;
                }

                e.target.closest('tr').remove();

                document.querySelectorAll('#items_table tbody tr').forEach((row, i) => {
                    row.children[0].innerText = i + 1;
                });

                calculate();
            }

            if (e.target.classList.contains('qty_plus')) {
                let row = e.target.closest('tr');
                let qtyInput = row.querySelector('.qty');
                qtyInput.value = parseFloat(qtyInput.value || 0) + 1;
                calculate();
            }

            if (e.target.classList.contains('qty_minus')) {
                let row = e.target.closest('tr');
                let qtyInput = row.querySelector('.qty');
                let currentQty = parseFloat(qtyInput.value || 0);

                if (currentQty > 1) {
                    qtyInput.value = currentQty - 1;
                    calculate();
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | INITIALIZE PAGE
        |--------------------------------------------------------------------------
        | Load all products immediately.
        | No need to wait for company/business/work point.
        |--------------------------------------------------------------------------
        */
        initDynamicSelect2();
        loadItems();
        calculate();
        });
    </script>
@endsection
