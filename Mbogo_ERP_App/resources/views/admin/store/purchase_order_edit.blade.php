@extends('layouts.salesMaster')

@section('content')
    @php
        $encryptedId = encrypt($order->id);

        $vendorOptions = $vendors
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'vendor_name' => $v->vendor_name ?? '',
                    'vendor_code' => $v->vendor_code ?? '',
                    'phone_no' => $v->phone_no ?? '',
                    'email' => $v->email ?? '',
                    'address' => $v->address ?? '',
                    'tin_no' => $v->tin_no ?? '',
                ];
            })
            ->values();

        $companyOptions = $companies
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'company_code' => $c->company_code ?? '',
                    'company_name' => $c->company_name ?? '',
                    'district' => $c->district ?? '',
                    'city' => $c->city ?? '',
                    'phone' => $c->phone_No ?? ($c->phone_no ?? ''),
                    'email' => $c->email ?? '',
                ];
            })
            ->values();

        $generalItems = $items
            ->map(function ($it) {
                return [
                    'id' => $it->id,
                    'name' => $it->product_name ?? '',
                    'unit' => $it->unit_name ?? ($it->unit ?? 'PCS'),
                    'price' => $it->buying_price ?? ($it->purchase_price ?? ($it->avg_cost ?? 0)),
                    'account_code' => $it->account_code ?? ($it->inventory_account_code ?? ''),
                    'account_name' => $it->account_name ?? '',
                ];
            })
            ->values();

        $rawMaterialItems = $rawMaterials
            ->map(function ($rm) {
                return [
                    'id' => $rm->id,
                    'name' => $rm->material_name ?? '',
                    'unit' => $rm->unit_name ?? ($rm->unit ?? 'PCS'),
                    'price' => $rm->buying_price ?? ($rm->purchase_price ?? ($rm->avg_cost ?? 0)),
                    'account_code' => $rm->account_code ?? ($rm->inventory_account_code ?? ''),
                    'account_name' => $rm->account_name ?? '',
                ];
            })
            ->values();

        $existingItems = $order->items
            ->map(function ($it) {
                return [
                    'item_id' => $it->item_id,
                    'description' => $it->description,
                    'qty' => $it->qty,
                    'unit' => $it->unit,
                    'unit_price' =>
                        ((float) $it->qty) > 0
                            ? round(((float) $it->total_price) / ((float) $it->qty), 6)
                            : (float) $it->unit_price,
                    'actual_unit_price' =>
                        ((float) $it->qty) > 0
                            ? round(((float) $it->sub_total) / ((float) $it->qty), 6)
                            : (float) $it->unit_price,
                    'vat_amount' => $it->vat_amount,
                    'total_price' => $it->total_price,
                    'account_code' => $it->account_code,
                    'account_name' => $it->account_name,
                ];
            })
            ->values();
    @endphp

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

        #po_items_table thead th {
            color: #000 !important;
            background: #f3f3f4 !important;
            font-weight: bold !important;
            border: 1px solid #ddd !important;
            vertical-align: middle !important;
        }

        #po_items_table td {
            vertical-align: top !important;
        }

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
    </style>

    <div class="wrapper wrapper-content">

        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-8">
                <h2>General Supply Dashboard</h2>
                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li><a href="{{ route('sales.po.index') }}">Purchase Orders</a></li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li class="breadcrumb-item active"><strong>Edit Purchase Order</strong></li>
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

        @can('Edit-Purchase-Orders')
            <div class="ibox mt-3">
                <div class="ibox-title bg-primary">
                    <h5><i class="fa fa-edit"></i> Edit Purchase Order - {{ $order->po_no }}</h5>
                </div>

                <div class="ibox-content">
                    <form method="POST" action="{{ route('sales.po.update', $encryptedId) }}" enctype="multipart/form-data"
                        id="poEditForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-3">
                                <label>Company *</label>
                                <select name="company_id" id="company_id" class="form-control select2_demo_2" required>
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->id }}"
                                            {{ $order->company_id == $c->id ? 'selected' : '' }}>
                                            {{ $c->company_code }} - {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Business Unit *</label>
                                <select name="business_unit_id" id="business_unit_id" class="form-control select2_demo_2"
                                    required>
                                    <option value="{{ $order->business_unit_id }}" selected>
                                        {{ optional($order->businessUnit)->unit_code }} -
                                        {{ optional($order->businessUnit)->unit_name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Work Point / Location *</label>
                                <select name="work_point_id" id="work_point_id" class="form-control select2_demo_2" required>
                                    <option value="{{ $order->work_point_id }}" selected>
                                        {{ optional($order->workPoint)->work_code }} -
                                        {{ optional($order->workPoint)->work_name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Vendor / Supplier *</label>
                                <select name="vendor_id" id="vendor_id" class="form-control select2_demo_2" required>
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}"
                                            {{ $order->vendor_id == $v->id ? 'selected' : '' }}>
                                            {{ $v->vendor_code }} - {{ $v->vendor_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>PO Date *</label>
                                <input type="date" name="po_date" class="form-control"
                                    value="{{ $order->po_date ? $order->po_date->format('Y-m-d') : now()->toDateString() }}"
                                    required>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" class="form-control"
                                    value="{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : '' }}">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Supplier PI No</label>
                                <input type="text" name="pi_no" class="form-control" value="{{ $order->pi_no }}">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Purchase Type *</label>
                                <select name="purchase_type" id="purchase_type" class="form-control select2_demo_2" required>
                                    <option value="GeneralSupply"
                                        {{ $order->purchase_type == 'GeneralSupply' ? 'selected' : '' }}>General Supply /
                                        Product</option>
                                    <option value="RawMaterial" {{ $order->purchase_type == 'RawMaterial' ? 'selected' : '' }}>
                                        Raw Material</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Currency *</label>
                                <select name="currency" id="currency" class="form-control select2_demo_2" required>
                                    <option value="TZS" {{ $order->currency == 'TZS' ? 'selected' : '' }}>TZS</option>
                                    <option value="USD" {{ $order->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="KES" {{ $order->currency == 'KES' ? 'selected' : '' }}>KES</option>
                                    <option value="UGX" {{ $order->currency == 'UGX' ? 'selected' : '' }}>UGX</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Exchange Rate *</label>
                                <input type="number" name="exchange_rate" id="exchange_rate" class="form-control"
                                    value="{{ $order->exchange_rate }}" step="0.0001" min="0.0001" required>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>VAT Rate (%)</label>
                                <input type="number" name="vat_rate" id="vat_rate" class="form-control"
                                    value="{{ $order->vat_rate }}" step="0.0001" min="0">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Discount</label>
                                <input type="number" name="discount" id="discount" class="form-control"
                                    value="{{ $order->discount }}" step="0.0001" min="0">
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Ship To</label>
                                <textarea name="ship_to" id="ship_to" class="form-control" rows="4">{{ $order->ship_to }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label>From Vendor</label>
                                <textarea name="vendor_from" id="vendor_from" class="form-control" rows="4">{{ $order->vendor_from }}</textarea>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Shipping Method</label>
                                <input type="text" name="shipping_method" class="form-control"
                                    value="{{ $order->shipping_method }}">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Shipping Terms</label>
                                <input type="text" name="shipping_terms" class="form-control"
                                    value="{{ $order->shipping_terms }}">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Delivery Point</label>
                                <input type="text" name="delivery_point" id="delivery_point" class="form-control"
                                    value="{{ $order->delivery_point }}">
                            </div>
                        </div>

                        <hr>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="po_items_table">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>Description / Item</th>
                                        <th style="width:150px;">Qty</th>
                                        <th style="width:120px;">Unit</th>
                                        <th style="width:160px;">Unit Price<br><small>(Incl. VAT)</small></th>
                                        <th style="width:160px;">Actual Price<br><small>(Excl. VAT)</small></th>
                                        <th style="width:160px;">VAT Amount</th>
                                        <th style="width:160px;">Total Amount</th>
                                        <th style="width:70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="poItemsBody"></tbody>
                            </table>
                        </div>

                        <button type="button" id="addPoRow" class="btn btn-primary">+ Add Item</button>

                        <hr>

                        <div class="row">
                            <div class="col-md-3">
                                <label>Subtotal</label>
                                <input type="text" id="subtotal_view" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>VAT</label>
                                <input type="text" id="vat_view" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Discount</label>
                                <input type="text" id="discount_view" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Total</label>
                                <input type="text" id="grand_total_view" class="form-control" readonly>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Supplier Proforma / Quotation</label>
                                <input type="file" name="supplier_proforma_attachment" class="form-control">

                                @if ($order->supplier_proforma_attachment)
                                    <small>
                                        Current:
                                        <a href="{{ asset('storage/' . $order->supplier_proforma_attachment) }}"
                                            target="_blank">
                                            Open file
                                        </a>
                                    </small>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Terms & Conditions</label>
                                <textarea name="terms_conditions" class="form-control" rows="4">{{ $order->terms_conditions }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="4">{{ $order->remarks }}</textarea>
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Update Purchase Order
                        </button>

                        <a href="{{ route('sales.po.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </form>
                </div>
            </div>
        @endcan
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.vendorOptions = @json($vendorOptions);
            window.companyOptions = @json($companyOptions);
            window.generalItems = @json($generalItems);
            window.rawMaterialItems = @json($rawMaterialItems);
            window.existingItems = @json($existingItems);

            const businessUnitsUrl =
                "{{ route('sales.po.ajax.business.units', ['company_id' => '__COMPANY_ID__']) }}";
            const workPointsUrl = "{{ route('sales.po.ajax.work.points', ['business_id' => '__UNIT_ID__']) }}";

            function initSelect2() {
                if (!window.$ || !$.fn.select2) return;

                $('.select2_demo_2').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }

                    $(this).select2({
                        width: '100%',
                        theme: 'bootstrap4'
                    });
                });
            }

            initSelect2();

            $('#company_id').on('change', function() {
                loadBusinessUnits(this.value);
            });

            $('#business_unit_id').on('change', function() {
                loadWorkPoints(this.value);
            });

            $('#vendor_id').on('change', function() {
                buildVendorFromText();
                calculateTotals();
            });

            $('#purchase_type').on('change', function() {
                $('#poItemsBody').html('');
                addPoRow();
            });

            $('#vat_rate, #discount').on('input', calculateTotals);

            $('#addPoRow').on('click', function() {
                addPoRow();
            });

            function escapeAttr(value) {
                if (value === null || value === undefined) return '';

                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function roundAmount(value, decimals = 6) {
                let factor = Math.pow(10, decimals);
                return Math.round((parseFloat(value) || 0) * factor) / factor;
            }

            function roundTotalAmount(value) {
                return Math.round(parseFloat(value) || 0);
            }

            function formatView(value) {
                return roundAmount(value, 4).toFixed(4);
            }

            function formatTotalView(value) {
                return roundTotalAmount(value).toFixed(2);
            }

            function selectedVendorHasTIN() {
                let vendorId = $('#vendor_id').val();
                let vendor = vendorOptions.find(x => String(x.id) === String(vendorId));

                if (!vendor) return false;

                return !!String(vendor.tin_no || vendor.tin_number || vendor.tin || '').trim();
            }

            function loadBusinessUnits(companyId) {
                $('#business_unit_id').html('<option value="">Loading...</option>').trigger('change');
                $('#work_point_id').html('<option value="">Select Work Point</option>').trigger('change');

                if (!companyId) {
                    $('#business_unit_id').html('<option value="">Select Business Unit</option>').trigger('change');
                    return;
                }

                fetch(businessUnitsUrl.replace('__COMPANY_ID__', encodeURIComponent(companyId)))
                    .then(response => response.json())
                    .then(data => {
                        let html = '<option value="">Select Business Unit</option>';

                        data.forEach(function(unit) {
                            html += '<option value="' + escapeAttr(unit.id) + '">' +
                                escapeAttr((unit.unit_code ? unit.unit_code + ' - ' : '') + unit
                                    .unit_name) +
                                '</option>';
                        });

                        $('#business_unit_id').html(html).trigger('change');
                    });
            }

            function loadWorkPoints(unitId) {
                $('#work_point_id').html('<option value="">Loading...</option>').trigger('change');

                if (!unitId) {
                    $('#work_point_id').html('<option value="">Select Work Point</option>').trigger('change');
                    return;
                }

                fetch(workPointsUrl.replace('__UNIT_ID__', encodeURIComponent(unitId)))
                    .then(response => response.json())
                    .then(data => {
                        let html = '<option value="">Select Work Point</option>';

                        data.forEach(function(wp) {
                            html += '<option value="' + escapeAttr(wp.id) + '" ' +
                                'data-work-name="' + escapeAttr(wp.work_name || '') + '" ' +
                                'data-location="' + escapeAttr(wp.location || '') + '" ' +
                                'data-district="' + escapeAttr(wp.district || '') + '" ' +
                                'data-city="' + escapeAttr(wp.city || '') + '">' +
                                escapeAttr((wp.work_code ? wp.work_code + ' - ' : '') + wp.work_name) +
                                '</option>';
                        });

                        $('#work_point_id').html(html).trigger('change');
                    });
            }

            function buildVendorFromText() {
                let vendorId = $('#vendor_id').val();
                let vendor = vendorOptions.find(x => String(x.id) === String(vendorId));

                if (!vendor) {
                    $('#vendor_from').val('');
                    return;
                }

                $('#vendor_from').val([
                    vendor.vendor_name,
                    vendor.address,
                    vendor.phone_no,
                    vendor.email,
                    vendor.tin_no ? 'TIN: ' + vendor.tin_no : ''
                ].filter(Boolean).join("\n"));
            }

            function addPoRow(item = null) {
                let index = $('#poItemsBody tr').length;
                let purchaseType = $('#purchase_type').val() || 'GeneralSupply';
                let source = purchaseType === 'RawMaterial' ? rawMaterialItems : generalItems;

                let options = '<option value="">-- Select Item --</option>';

                source.forEach(function(x) {
                    let selected = item && String(item.item_id) === String(x.id) ? 'selected' : '';

                    options += '<option value="' + escapeAttr(x.id) + '" ' + selected + ' ' +
                        'data-name="' + escapeAttr(x.name) + '" ' +
                        'data-unit="' + escapeAttr(x.unit) + '" ' +
                        'data-price="' + escapeAttr(x.price) + '" ' +
                        'data-account-code="' + escapeAttr(x.account_code) + '" ' +
                        'data-account-name="' + escapeAttr(x.account_name) + '">' +
                        escapeAttr(x.name) +
                        '</option>';
                });

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <select name="items[${index}][item_id]" class="form-control item_select select2_demo_2" required>
                                ${options}
                            </select>

                            <input type="hidden" name="items[${index}][account_code]" class="account_code" value="${item ? escapeAttr(item.account_code) : ''}">
                            <input type="hidden" name="items[${index}][account_name]" class="account_name" value="${item ? escapeAttr(item.account_name) : ''}">

                            <input type="text" name="items[${index}][description]" class="form-control mt-1 description"
                                value="${item ? escapeAttr(item.description) : ''}" placeholder="Enter description">
                        </td>
                        <td>
                            <input type="number" name="items[${index}][qty]" class="form-control qty"
                                value="${item ? item.qty : 1}" step="0.0001" min="0.0001" required>
                        </td>
                        <td>
                            <input type="text" name="items[${index}][unit]" class="form-control unit"
                                value="${item ? escapeAttr(item.unit) : ''}">
                        </td>
                        <td>
                            <input type="number" name="items[${index}][unit_price]" class="form-control price"
                                value="${item ? item.unit_price : 0}" step="0.0001" min="0" required>
                        </td>
                        <td>
                            <input type="number" class="form-control actual_price"
                                value="${item ? item.actual_unit_price : 0}" readonly step="0.0001">
                        </td>
                        <td>
                            <input type="number" class="form-control row_vat"
                                value="${item ? item.vat_amount : 0}" readonly step="0.0001">
                        </td>
                        <td>
                            <input type="number" class="form-control line_total" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove_row">X</button>
                        </td>
                    </tr>
                `;

                $('#poItemsBody').append(row);
                initSelect2();
                calculateTotals();
            }

            $(document).on('change', '.item_select', function() {
                let option = $(this).find('option:selected');
                let row = $(this).closest('tr');

                row.find('.description').val(option.data('name') || '');
                row.find('.unit').val(option.data('unit') || '');
                row.find('.price').val(option.data('price') || 0);
                row.find('.account_code').val(option.data('account-code') || '');
                row.find('.account_name').val(option.data('account-name') || '');

                calculateTotals();
            });

            $(document).on('input', '.qty, .price', calculateTotals);

            $(document).on('click', '.remove_row', function() {
                $(this).closest('tr').remove();
                renumberRows();
                calculateTotals();
            });

            function renumberRows() {
                $('#poItemsBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function calculateTotals() {
                let grossTotal = 0;
                let subtotal = 0;
                let vat = 0;
                let vatRate = parseFloat($('#vat_rate').val() || 0);
                let discount = roundAmount($('#discount').val() || 0, 6);
                let vatInclusive = selectedVendorHasTIN() && vatRate > 0;
                let rate = vatRate / 100;

                $('#poItemsBody tr').each(function() {
                    let qty = roundAmount($(this).find('.qty').val() || 0, 6);
                    let grossUnitPrice = roundAmount($(this).find('.price').val() || 0, 6);
                    let actualUnitPrice = grossUnitPrice;

                    if (vatInclusive) {
                        actualUnitPrice = roundAmount(grossUnitPrice / (1 + rate), 6);
                    }

                    let lineSubtotal = roundAmount(qty * actualUnitPrice, 6);
                    let lineVat = vatInclusive ? roundAmount(lineSubtotal * rate, 6) : 0;
                    let grossLineTotal = roundAmount(lineSubtotal + lineVat, 6);

                    $(this).find('.actual_price').val(formatView(actualUnitPrice));
                    $(this).find('.row_vat').val(formatView(lineVat));
                    $(this).find('.line_total').val(formatTotalView(grossLineTotal));

                    grossTotal += grossLineTotal;
                    subtotal += lineSubtotal;
                    vat += lineVat;
                });

                let grandTotal = roundTotalAmount(grossTotal - discount);

                $('#subtotal_view').val(formatView(subtotal));
                $('#vat_view').val(formatView(vat));
                $('#discount_view').val(formatView(discount));
                $('#grand_total_view').val(formatTotalView(grandTotal));
            }

            existingItems.forEach(function(item) {
                addPoRow(item);
            });

            if (existingItems.length < 1) {
                addPoRow();
            }
        });
    </script>
@endsection
