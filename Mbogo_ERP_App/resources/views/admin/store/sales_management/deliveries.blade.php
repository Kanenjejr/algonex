@extends('layouts.salesMaster')
@section('content')
    @php
        $enc = function ($id) {
            return $id ? \Illuminate\Support\Facades\Crypt::encryptString((string) $id) : '';
        };

        $deliveryProductsJson = $products
            ->map(function ($p) use ($enc) {
                return [
                    'id' => $enc($p->id),
                    'name' => $p->product_name,
                    'unit' => $p->unit ?? ($p->product_size ?? 'pcs'),
                    'price' => $p->selling_price ?? ($p->avg_cost ?? 0),
                ];
            })
            ->values();
    @endphp

    <style>
        .deliveries-page .select2-container {
            width: 100% !important;
        }

        .deliveries-page .ibox-title h5 {
            color: inherit;
        }

        .deliveries-page #delivery_items th {
            background: #f3f3f4;
            color: #000 !important;
        }

        .deliveries-page .readonly-note {
            font-size: 12px;
            color: #777;
            margin-top: 4px;
        }

        .deliveries-page .summary-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            border-radius: 4px;
        }

        .deliveries-page .summary-box h4 {
            margin: 0;
            color: #1c84c6;
            font-weight: bold;
        }

        .deliveries-page .locked-control+.select2-container .select2-selection {
            background: #e9ecef !important;
            cursor: not-allowed !important;
        }

        @media print {

            .no-print,
            .navbar,
            .footer,
            .page-heading {
                display: none !important;
            }
        }
    </style>

    <div class="deliveries-page wrapper wrapper-content">
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-8">
                <h2 class="dashboard-title">Sales Management Module</h2>
                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li><a href="{{ route('sales.dashboard') }}">Sales Management</a></li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li class="active"><strong>Deliveries / Waybills</strong></li>
                </ol>
            </div>
            <div class="col-lg-2">
                <h4>Current Date</h4><strong>{{ now()->format('l, Y-m-d') }}</strong>
            </div>
            <div class="col-lg-2">
                <h4>Time</h4><strong><span id="Hour" style="color:green"></span><span id="Minut"
                        style="color:green"></span><span id="Second" style="color:red"></span></strong>
            </div>
        </div>

        @can('Create-Delivery')
            <div class="ibox mt-3">
                <div class="ibox-title bg-warning">
                    <h5><i class="fa fa-truck"></i> Create Delivery Note / Waybill</h5>
                </div>
                <div class="ibox-content">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Delivery was not saved:</strong>
                            <ul style="margin-bottom:0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sales.deliveries.store') }}" id="deliveryForm">
                        @csrf
                        <div class="alert alert-info">
                            Select an invoice/proforma to auto-populate customer, company, unit, location and items. Leave both
                            blank for manual delivery.
                            Waybill is generated from entered data, not uploaded as a document.
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label>Source Invoice</label>
                                <select name="invoice_id" id="invoice_id" class="form-control select2_demo_2">
                                    <option value="">-- No Invoice --</option>
                                    @foreach ($invoices as $i)
                                        <option value="{{ $enc($i->id) }}">{{ $i->invoice_no }} -
                                            {{ optional($i->customer)->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Source Proforma</label>
                                <select name="proforma_id" id="proforma_id" class="form-control select2_demo_2">
                                    <option value="">-- No Proforma --</option>
                                    @foreach ($proformas as $p)
                                        <option value="{{ $enc($p->id) }}">{{ $p->proforma_no }} -
                                            {{ optional($p->customer)->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Customer *</label>
                                <select name="customer_id" id="customer_id" class="form-control select2_demo_2">
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $enc($c->id) }}">{{ $c->customer_code ?? '' }} -
                                            {{ $c->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Delivery Date *</label>
                                <input type="date" name="delivery_date" value="{{ now()->toDateString() }}"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Company *</label>
                                <select name="company_id" id="company_id" class="form-control select2_demo_2">
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $enc($c->id) }}">{{ $c->company_code }} - {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Business Unit</label>
                                <select name="business_unit_id" id="business_unit_id" class="form-control select2_demo_2">
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Location *</label>
                                <select name="work_point_id" id="work_point_id" class="form-control select2_demo_2">
                                    <option value="">Select Location</option>
                                </select>
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Transport Owner</label>
                                <select name="transport_owner" id="transport_owner" class="form-control select2_demo_2">
                                    <option value="company">Our Company Transport</option>
                                    <option value="customer">Customer Transport</option>
                                </select>
                                <div class="readonly-note">If customer transport, waybill amount can be 0.</div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            {{-- <div class="col-md-6">
                                <div class="alert alert-warning" style="margin-bottom:0;">
                                    <strong>Document numbers are automatic.</strong><br>
                                    Waybill format: <strong>WBL-YYYYMMDD-0001</strong> and Delivery Note format:
                                    <strong>DLN-YYYYMMDD-0001</strong>. Numbering restarts every month.
                                </div>
                            </div> --}}
                            <div class="col-md-4"><label>Transport Mode</label><input type="text" name="transport_mode"
                                    class="form-control" placeholder="Truck / Pickup / Customer vehicle"></div>
                            <div class="col-md-4"><label>Tracking No</label><input type="text" name="tracking_no"
                                    class="form-control"></div>
                            <div class="col-md-4 mt-2"><label>Driver</label><input type="text" name="driver_name"
                                    class="form-control"></div>
                            <div class="col-md-4 mt-2"><label>Driver Phone</label><input type="text" name="driver_phone"
                                    class="form-control"></div>
                            <div class="col-md-4 mt-2"><label>Vehicle No</label><input type="text" name="vehicle_no"
                                    class="form-control"></div>
                            <div class="col-md-4 mt-2"><label>Container No</label><input type="text" name="container_no"
                                    class="form-control"></div>
                            <div class="col-md-3 mt-2"><label>Origin</label><input type="text" name="origin"
                                    id="origin" class="form-control"></div>
                            <div class="col-md-3 mt-2"><label>Destination</label><input type="text" name="destination"
                                    id="destination" class="form-control"></div>
                            <div class="col-md-3 mt-2"><label>Dispatch Date</label><input type="date" name="dispatch_date"
                                    class="form-control"></div>
                            <div class="col-md-3 mt-2"><label>Expected Delivery</label><input type="date"
                                    name="expected_delivery_date" class="form-control"></div>
                        </div>

                        <hr>
                        <h4>Packing List / Customs Road Manifest Details</h4>
                        <div class="row">
                            <div class="col-md-3 mt-2">
                                <label>Export Reference No</label>
                                <input type="text" name="export_reference_no" class="form-control"
                                    placeholder="e.g. MMGS-SOTB-001-2024">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Transporter</label>
                                <input type="text" name="transporter_name" class="form-control"
                                    placeholder="Transporter name">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Total Gross Weight</label>
                                <input type="text" name="total_gross_weight" class="form-control"
                                    placeholder="e.g. 662.995 KG">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Truck 2 Registration</label>
                                <input type="text" name="truck2_registration_no" class="form-control" placeholder="N/A">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Trailer Registration</label>
                                <input type="text" name="trailer_registration_no" class="form-control" placeholder="N/A">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Container 2 No</label>
                                <input type="text" name="container2_no" class="form-control" placeholder="N/A">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Container 3 No</label>
                                <input type="text" name="container3_no" class="form-control" placeholder="N/A">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Clearing Agent</label>
                                <input type="text" name="clearing_agent" class="form-control">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Bill of Entry No</label>
                                <input type="text" name="bill_of_entry_no" class="form-control">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label>Exit / Entry No</label>
                                <input type="text" name="exit_entry_no" class="form-control">
                            </div>
                        </div>

                        <hr>
                        <h4>Delivery Items</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="delivery_items">
                                <thead>
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Product / Item Name</th>
                                        <th style="width:100px">Qty</th>
                                        <th style="width:90px">Unit</th>
                                        <th style="width:125px">Unit Price</th>
                                        <th style="width:125px">Total</th>
                                        <th style="width:140px">Packages No & Type</th>
                                        <th style="width:120px">Gross Weight</th>
                                        <th style="width:70px">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary" id="addDeliveryRow">+ Add Product/Item</button>

                        <hr>
                        <h4>Waybill / Transport Service Income</h4>
                        <div class="row" id="waybill_income_section">
                            <div class="col-md-3">
                                <label>Waybill Amount</label>
                                <input type="number" step="0.01" name="waybill_amount" id="waybill_amount"
                                    value="0" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Currency</label>
                                <select name="waybill_currency" class="form-control select2_demo_2">
                                    <option value="TZS">TZS</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Exchange Rate</label>
                                <input type="number" step="0.000001" name="waybill_exchange_rate" value="1"
                                    class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Payment Method</label>
                                <select name="waybill_payment_method" id="waybill_payment_method"
                                    class="form-control select2_demo_2">
                                    <option value="bank">Bank</option>
                                    <option value="cash">Cash</option>
                                    <option value="mobile">Mobile Network</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Bank / Cash / Mobile Account</label>
                                <select name="waybill_payment_account_id" id="waybill_payment_account_id"
                                    class="form-control select2_demo_2">
                                    <option value="">-- Select Account --</option>
                                    @foreach ($paymentAccounts as $a)
                                        <option value="{{ $enc($a->id) }}">{{ $a->SubCode }} - {{ $a->SubDescription }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label>Service Income Account</label>
                                <select name="waybill_service_income_account_id" id="waybill_service_income_account_id"
                                    class="form-control select2_demo_2">
                                    <option value="">-- Select Service Income --</option>
                                    @foreach ($serviceIncomeAccounts as $a)
                                        <option value="{{ $enc($a->id) }}">{{ $a->SubCode }} - {{ $a->SubDescription }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mt-2">
                                <label>Notes</label>
                                <input type="text" name="notes" class="form-control">
                            </div>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Delivery /
                            Waybill</button>
                    </form>
                </div>
            </div>
        @endcan

        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Deliveries List</h5>
            </div>
            <div class="ibox-content table-responsive">
                <table class="table table-striped table-bordered dataTables-example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Delivery No</th>
                            <th>Waybill</th>
                            <th>Note</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Waybill Amount</th>
                            <th>Packing List</th>
                            <th>Manifest</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveries as $d)
                            @php $customer = $d->customer ?: optional($d->invoice)->customer ?: optional($d->proforma)->customer; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $d->delivery_no }}</strong></td>
                                <td>{{ $d->waybill_no }}</td>
                                <td>{{ $d->delivery_note_no }}</td>
                                <td>{{ optional($d->delivery_date)->format('Y-m-d') }}</td>
                                <td>{{ optional($customer)->customer_name }}</td>
                                <td>{{ optional($d->company)->company_name }}</td>
                                <td>{{ optional($d->businessUnit)->unit_name }}</td>
                                <td>{{ optional($d->workPoint)->work_name }}</td>
                                <td>{{ $d->delivery_income_currency ?? 'TZS' }}
                                    {{ number_format((float) $d->delivery_income_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('sales.delivery.packing.list', $enc($d->id)) }}" target="_blank"
                                        class="btn btn-xs btn-warning">Packing List</a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('sales.delivery.customs.manifest', $enc($d->id)) }}"
                                        target="_blank" class="btn btn-xs btn-primary">Manifest</a>
                                </td>
                                <td><span
                                        class="label label-{{ $d->approval_status == 'approved' ? 'success' : 'warning' }}">{{ strtoupper($d->delivery_status ?? $d->approval_status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('sales.delivery.note', $enc($d->id)) }}" target="_blank"
                                        class="btn btn-xs btn-primary">Delivery Note</a>
                                    <a href="{{ route('sales.delivery.waybill', $enc($d->id)) }}" target="_blank"
                                        class="btn btn-xs btn-info">Waybill</a>
                                    @if ($d->approval_status != 'approved' && !$d->isClosed())
                                        @can('Approve-Delivery')
                                            <form action="{{ route('sales.deliveries.approve', $enc($d->id)) }}"
                                                method="POST" style="display:inline">@csrf
                                                <button class="btn btn-xs btn-success"
                                                    onclick="return confirm('Approve delivery and post waybill income if any?')">Approve</button>
                                            </form>
                                        @endcan
                                        @can('Delete-Delivery')
                                            <form action="{{ route('sales.deliveries.delete', $enc($d->id)) }}"
                                                method="POST" style="display:inline">@csrf @method('DELETE')
                                                <button class="btn btn-xs btn-danger"
                                                    onclick="return confirm('Delete delivery?')">Delete</button>
                                            </form>
                                        @endcan
                                    @endif
                                    @if ($d->approval_status == 'approved' && !$d->isClosed())
                                        @can('Approve-Delivery')
                                            <form action="{{ route('sales.deliveries.accept', $enc($d->id)) }}"
                                                method="POST" style="display:inline">@csrf
                                                <button class="btn btn-xs btn-warning"
                                                    onclick="return confirm('Close delivery after customer acceptance?')">Accept/Close</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function timedMsg() {
            setInterval(change_time, 1000);
        }

        function change_time() {
            const d = new Date();
            Hour.innerHTML = String(d.getHours()).padStart(2, '0') + ':';
            Minut.innerHTML = String(d.getMinutes()).padStart(2, '0') + ':';
            Second.innerHTML = String(d.getSeconds()).padStart(2, '0');
        }
        timedMsg();

        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = 0;
            let sourceMode = 'manual';
            const encProducts = @json($deliveryProductsJson);

            function initSelect2() {
                if (window.$ && $.fn.select2) {
                    $('.select2_demo_2').select2({
                        theme: 'bootstrap4',
                        width: '100%'
                    });
                }
            }
            initSelect2();

            function toggleManualItems() {
                if (sourceMode === 'manual') {
                    $('#addDeliveryRow').show();
                    $('#delivery_items .remove-row').show();
                } else {
                    $('#addDeliveryRow').hide();
                    $('#delivery_items .remove-row').hide();
                }
            }

            function toggleWaybillIncome() {
                const owner = $('#transport_owner').val();
                if (owner === 'customer') {
                    $('#waybill_income_section').hide();
                    $('#waybill_amount').val(0);
                    $('#waybill_payment_account_id').val('').trigger('change.select2');
                    $('#waybill_service_income_account_id').val('').trigger('change.select2');
                } else {
                    $('#waybill_income_section').show();
                }
            }

            $('#transport_owner').on('change', toggleWaybillIncome);
            toggleManualItems();
            toggleWaybillIncome();

            function setSelectWithText(selector, value, text) {
                const el = $(selector);
                if (!value) {
                    el.val('').trigger('change.select2');
                    return;
                }
                if (el.find('option[value="' + value + '"]').length === 0) {
                    el.append(new Option(text || value, value, true, true));
                }
                el.val(value).trigger('change.select2');
            }

            function lockSourceControls(lock) {
                ['#customer_id', '#company_id', '#business_unit_id', '#work_point_id'].forEach(id => {
                    const el = $(id);
                    el.prop('disabled', false);
                    if (lock) {
                        el.addClass('locked-control');
                    } else {
                        el.removeClass('locked-control');
                    }
                });
                $('.locked-control').on('select2:opening', function(e) {
                    e.preventDefault();
                });
            }

            function productOptions(selected = '') {
                let html = '<option value="">-- Select Product --</option>';
                encProducts.forEach(p => {
                    html +=
                        `<option value="${p.id}" data-name="${escapeHtml(p.name)}" data-unit="${escapeHtml(p.unit)}" data-price="${p.price}" ${selected===p.id?'selected':''}>${escapeHtml(p.name)}</option>`;
                });
                return html;
            }

            function addRow(item = {}) {
                const i = rowIndex++;
                const locked = item.locked === true || sourceMode !== 'manual';
                const disabledAttr = locked ? 'disabled' : '';
                const readonlyAttr = locked ? 'readonly' : '';
                const actionCell = locked ? '-' :
                    '<button type="button" class="btn btn-danger btn-sm remove-row">X</button>';
                const hiddenProduct = locked && item.product_id ?
                    `<input type="hidden" name="items[${i}][product_id]" value="${item.product_id}">` : '';
                const html = `<tr>
                    <td>${i+1}</td>
                    <td>
                        ${hiddenProduct}
                        <select name="items[${i}][product_id]" class="form-control item_product select2_demo_2" ${disabledAttr}>${productOptions(item.product_id || '')}</select>
                        <input type="text" name="items[${i}][item_name]" class="form-control mt-1 item_name" value="${escapeAttr(item.item_name || '')}" placeholder="Manual item name if no product" ${readonlyAttr}>
                    </td>
                    <td><input type="number" step="0.0001" name="items[${i}][quantity]" class="form-control qty" value="${item.quantity || 1}" ${readonlyAttr}></td>
                    <td><input type="text" name="items[${i}][unit]" class="form-control unit" value="${escapeAttr(item.unit || 'pcs')}" ${readonlyAttr}></td>
                    <td><input type="number" step="0.01" name="items[${i}][unit_price]" class="form-control price" value="${item.unit_price || 0}" ${readonlyAttr}></td>
                    <td><input type="number" step="0.01" name="items[${i}][total]" class="form-control total" value="${item.total || 0}" readonly></td>
                    <td><input type="text" name="items[${i}][packages_no_type]" class="form-control packages_no_type" value="${escapeAttr(item.packages_no_type || '')}" placeholder="e.g. 25 Cases"></td>
                    <td><input type="text" name="items[${i}][gross_weight]" class="form-control gross_weight" value="${escapeAttr(item.gross_weight || '')}" placeholder="e.g. 625 Kg"></td>
                    <td>${actionCell}</td>
                </tr>`;
                document.querySelector('#delivery_items tbody').insertAdjacentHTML('beforeend', html);
                initSelect2();
                calculateRows();
            }

            $('#addDeliveryRow').on('click', () => addRow());
            addRow();

            $(document).on('change', '.item_product', function() {
                const opt = this.options[this.selectedIndex];
                const row = this.closest('tr');
                if (opt && opt.value) {
                    row.querySelector('.item_name').value = opt.getAttribute('data-name') || '';
                    row.querySelector('.unit').value = opt.getAttribute('data-unit') || 'pcs';
                    row.querySelector('.price').value = opt.getAttribute('data-price') || 0;
                }
                calculateRows();
            });
            $(document).on('input', '.qty,.price', calculateRows);
            $(document).on('click', '.remove-row', function() {
                this.closest('tr').remove();
                renumber();
                calculateRows();
            });

            function calculateRows() {
                document.querySelectorAll('#delivery_items tbody tr').forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty').value) || 0;
                    const price = parseFloat(row.querySelector('.price').value) || 0;
                    row.querySelector('.total').value = (qty * price).toFixed(2);
                });
            }

            function renumber() {
                document.querySelectorAll('#delivery_items tbody tr').forEach((r, i) => r.querySelector(
                    'td:first-child').innerText = i + 1);
            }


            $('#company_id').on('change', function() {
                loadUnits(this.value);
            });
            $('#business_unit_id').on('change', function() {
                loadWorkPoints(this.value);
            });

            function loadUnits(companyId, selectedUnit = '', selectedUnitText = '') {
                $('#business_unit_id').empty().append('<option value="">Loading...</option>').trigger(
                    'change.select2');
                $('#work_point_id').empty().append('<option value="">Select Location</option>').trigger(
                    'change.select2');
                if (!companyId) {
                    $('#business_unit_id').empty().append('<option value="">Select Unit</option>').trigger(
                        'change.select2');
                    return;
                }
                fetch(`{{ url('/admin/ajax/company-units') }}/${companyId}`).then(r => r.json()).then(data => {
                    $('#business_unit_id').empty().append('<option value="">Select Unit</option>');
                    data.forEach(u => $('#business_unit_id').append(new Option(u.text, u.id, false,
                        selectedUnit && selectedUnit === u.id)));
                    if (selectedUnit && $('#business_unit_id option[value="' + selectedUnit + '"]')
                        .length === 0) {
                        $('#business_unit_id').append(new Option(selectedUnitText || selectedUnit,
                            selectedUnit, true, true));
                    }
                    $('#business_unit_id').val(selectedUnit || '').trigger('change.select2');
                });
            }

            function loadWorkPoints(unitId, selectedPoint = '', selectedPointText = '') {
                $('#work_point_id').empty().append('<option value="">Loading...</option>').trigger(
                    'change.select2');
                if (!unitId) {
                    $('#work_point_id').empty().append('<option value="">Select Location</option>').trigger(
                        'change.select2');
                    return;
                }
                fetch(`{{ url('/admin/ajax/unit-workpoints') }}/${unitId}`).then(r => r.json()).then(data => {
                    $('#work_point_id').empty().append('<option value="">Select Location</option>');
                    data.forEach(w => $('#work_point_id').append(new Option(w.text, w.id, false,
                        selectedPoint && selectedPoint === w.id)));
                    if (selectedPoint && $('#work_point_id option[value="' + selectedPoint + '"]')
                        .length === 0) {
                        $('#work_point_id').append(new Option(selectedPointText || selectedPoint,
                            selectedPoint, true, true));
                    }
                    $('#work_point_id').val(selectedPoint || '').trigger('change.select2');
                });
            }

            $('#invoice_id').on('change', function() {
                const id = this.value;
                if (!id) {
                    sourceMode = $('#proforma_id').val() ? 'proforma' : 'manual';
                    lockSourceControls(false);
                    toggleManualItems();
                    return;
                }
                sourceMode = 'invoice';
                $('#proforma_id').val('').trigger('change.select2');
                fetch(`{{ url('/admin/ajax/delivery-invoice') }}/${id}`).then(r => r.json()).then(data => {
                    const inv = data.invoice;
                    setSelectWithText('#customer_id', inv.customer_id, inv.customer_name);
                    setSelectWithText('#company_id', inv.company_id, inv.company_text);
                    loadUnits(inv.company_id, inv.business_unit_id, inv.business_unit_text);
                    loadWorkPoints(inv.business_unit_id, inv.work_point_id, inv.work_point_text);
                    $('#origin').val(inv.origin || '');
                    $('#destination').val(inv.destination || '');
                    replaceItems(data.items || []);
                    lockSourceControls(true);
                    toggleManualItems();
                });
            });
            $('#proforma_id').on('change', function() {
                const id = this.value;
                if (!id) {
                    sourceMode = $('#invoice_id').val() ? 'invoice' : 'manual';
                    lockSourceControls(false);
                    toggleManualItems();
                    return;
                }
                sourceMode = 'proforma';
                $('#invoice_id').val('').trigger('change.select2');
                fetch(`{{ url('/admin/ajax/delivery-proforma') }}/${id}`).then(r => r.json()).then(
                    data => {
                        const p = data.proforma;
                        setSelectWithText('#customer_id', p.customer_id, p.customer_name);
                        setSelectWithText('#company_id', p.company_id, p.company_text);
                        loadUnits(p.company_id, p.business_unit_id, p.business_unit_text);
                        loadWorkPoints(p.business_unit_id, p.work_point_id, p.work_point_text);
                        $('#origin').val(p.origin || '');
                        $('#destination').val(p.destination || '');
                        replaceItems(data.items || []);
                        lockSourceControls(true);
                        toggleManualItems();
                    });
            });

            $('#waybill_payment_method').on('change', function() {
                let companyId = $('#company_id').val();
                let workId = $('#work_point_id').val();
                $('#waybill_payment_account_id').empty().append('<option value="">Loading...</option>')
                    .trigger('change.select2');
                let url =
                    `{{ url('/admin/payment-accounts') }}/${this.value}?company_id=${encodeURIComponent(companyId || '')}&work_point_id=${encodeURIComponent(workId || '')}`;
                fetch(url).then(r => r.json()).then(data => {
                    $('#waybill_payment_account_id').empty().append(
                        '<option value="">-- Select Account --</option>');
                    data.forEach(a => $('#waybill_payment_account_id').append(new Option(a.label, a
                        .id)));
                    $('#waybill_payment_account_id').trigger('change.select2');
                });
            });

            function replaceItems(items) {
                rowIndex = 0;
                $('#delivery_items tbody').empty();
                items.forEach(item => {
                    item.locked = sourceMode !== 'manual';
                    addRow(item);
                });
                toggleManualItems();
            }

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>'"]/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#039;',
                    '"': '&quot;'
                } [s]));
            }

            function escapeAttr(str) {
                return escapeHtml(str);
            }
        });
    </script>
@endsection
