@extends('layouts.salesMaster')

@section('content')
    @php
        $enc = fn($id) => $id ? \Illuminate\Support\Facades\Crypt::encryptString((string) $id) : '';
    @endphp

    <style>
        /* ================= SCROLL FIX =================
                       Do not set .wrapper to min-height:100vh because the page heading also uses
                       class="row wrapper" in this layout. That creates a big blank white area.
                    */
        html,
        body {
            height: auto !important;
            min-height: 100% !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        #wrapper,
        #page-wrapper {
            height: auto !important;
            min-height: 100vh !important;
            overflow-y: visible !important;
            overflow-x: hidden !important;
        }

        .wrapper-content {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            padding-bottom: 180px !important;
        }

        .page-heading {
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            margin-bottom: 15px !important;
        }

        .ibox,
        .ibox-content,
        .erp-card,
        .erp-card-body,
        .table-responsive {
            overflow: visible !important;
        }

        .footer {
            z-index: 1000 !important;
        }

        /* ================= SELECT2 DISPLAY FIX ================= */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap4 .select2-selection--single,
        .select2-container .select2-selection--single {
            min-height: 38px !important;
            height: 38px !important;
            padding: 4px 8px !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered,
        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 0 !important;
            padding-right: 22px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow,
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            top: 1px !important;
        }

        .select2-container--disabled .select2-selection,
        .select2-container--bootstrap4.select2-container--disabled .select2-selection {
            background-color: #e9ecef !important;
            opacity: 1 !important;
            cursor: not-allowed !important;
        }

        /* These values were being shown below select boxes after choosing proforma.
                       Keep the selected values inside the select boxes only. */
        #customer_info,
        #company_info,
        #unit_info,
        #workpoint_info {
            display: none !important;
        }

        .erp-card {
            border: 1px solid #e7eaec;
            background: #fff;
            margin-bottom: 18px;
        }

        .erp-card-title {
            background: #1c84c6;
            color: #fff;
            padding: 10px 15px;
            font-weight: bold;
        }

        .erp-card-body {
            padding: 15px;
        }

        .summary-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            border-radius: 4px;
        }

        .summary-box h4 {
            margin: 0;
            color: #1c84c6;
            font-weight: bold;
        }

        #invoice_items th {
            background: #f3f3f4;
            color: #000 !important;
            vertical-align: middle !important;
        }

        #invoice_items td {
            vertical-align: top !important;
        }

        .readonly-note {
            font-size: 12px;
            color: #777;
            margin-top: 4px;
        }

        .hidden {
            display: none !important;
        }

        @media print {

            .no-print,
            .navbar,
            .footer,
            .page-heading,
            .create-box {
                display: none !important;
            }

            #printArea {
                display: block !important;
            }
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
                    <li class="active"><strong>Invoices / Sales Record</strong></li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Current Date</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>{{ now()->format('l, Y-m-d') }}</strong>
                    </li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Time</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>
                            <span id="Hour" style="color:green"></span>
                            <span id="Minut" style="color:green"></span>
                            <span id="Second" style="color:red"></span>
                        </strong>
                    </li>
                </ol>
            </div>
        </div>

        @can('Register-Invoices')
            <div class="erp-card mt-3 create-box">
                <div class="erp-card-title">
                    <i class="fa fa-plus"></i> Create Invoice / Record Pending Payment
                </div>

                <div class="erp-card-body">
                    <div class="alert alert-info">
                        Payment will be posted to accounting and stock deducted only after payment approval.
                        When proforma is selected, all proforma details and VAT are copied exactly.
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Invoice was not saved. Please fix the following:</strong>
                            <ul style="margin-bottom:0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('sales.invoice.store') }}" enctype="multipart/form-data"
                        id="invoiceForm">
                        @csrf

                        {{-- ================= SOURCE / BASIC DETAILS ================= --}}
                        <div class="row">

                            <div class="col-md-3">
                                <label>Select Approved Proforma</label>
                                <select id="proforma_id" name="proforma_id" class="form-control select2_demo_2">
                                    <option value="">-- Normal Sale / No Proforma --</option>
                                    @foreach ($proformas as $p)
                                        @php
                                            $balance = (float) $p->total - (float) $p->paid_amount;
                                        @endphp
                                        @if (
                                            $balance > 0 &&
                                                in_array(strtolower($p->status), ['approved', 'converted']) &&
                                                strtolower($p->payment_status ?? 'unpaid') != 'paid')
                                            <option value="{{ $enc($p->id) }}">
                                                {{ $p->proforma_no }} -
                                                {{ optional($p->customer)->customer_name }} -
                                                Balance {{ number_format($balance, 2) }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="readonly-note">Only approved proformas with unpaid/partial balance appear.</div>
                            </div>

                            <div class="col-md-3">
                                <label>Invoice Type</label>
                                <select name="invoice_type" id="invoice_type" class="form-control select2_demo_2">
                                    <option value="local">Local</option>
                                    <option value="export">Export</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>VAT For Normal Sale</label>
                                <select name="vat_option" id="vat_option" class="form-control select2_demo_2">
                                    <option value="no">No VAT</option>
                                    <option value="yes">Yes - Amount is VAT Inclusive</option>
                                </select>
                                <div class="readonly-note" id="vat_note">
                                    If Yes and total is 20,000, system calculates 20,000 / 1.18 as sales value.
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>Currency</label>
                                <select name="currency" id="currency" class="form-control select2_demo_2">
                                    <option value="TZS">TZS</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Exchange Rate</label>
                                <input type="number" step="0.000001" name="exchange_rate" id="exchange_rate" value="1"
                                    class="form-control">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Customer *</label>
                                <select name="customer_id" id="customer_id" class="form-control select2_demo_2">
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $enc($c->id) }}" data-name="{{ $c->customer_name }}"
                                            data-code="{{ $c->customer_code ?? '' }}">
                                            {{ $c->customer_code ?? '' }} - {{ $c->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="readonly-note" id="customer_info"></div>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Company *</label>
                                <select name="company_id" id="company_id" class="form-control select2_demo_2">
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $enc($c->id) }}" data-name="{{ $c->company_name }}"
                                            data-code="{{ $c->company_code }}">
                                            {{ $c->company_code }} - {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="readonly-note" id="company_info"></div>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Business Unit</label>
                                <select name="business_unit_id" id="business_unit_id" class="form-control select2_demo_2">
                                    <option value="">Select Unit</option>
                                    @foreach ($companyUnits as $u)
                                        <option value="{{ $enc($u->id) }}" data-company="{{ $enc($u->company_id) }}"
                                            data-name="{{ $u->unit_name }}" data-code="{{ $u->unit_code }}">
                                            {{ $u->unit_code }} - {{ $u->unit_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="readonly-note" id="unit_info"></div>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Location *</label>
                                <select name="work_point_id" id="work_point_id" class="form-control select2_demo_2">
                                    <option value="">Select Work Point</option>
                                    @foreach ($workPoints as $w)
                                        <option value="{{ $enc($w->id) }}" data-unit="{{ $enc($w->comp_unit_id) }}"
                                            data-name="{{ $w->work_name }}" data-code="{{ $w->work_code }}">
                                            {{ $w->work_code }} - {{ $w->work_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="readonly-note" id="workpoint_info"></div>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Customer Ref / PO</label>
                                <input type="text" name="reference_no" class="form-control">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>

                        </div>

                        <hr>

                        {{-- ================= PROFORMA BANK DETAILS ================= --}}
                        <div class="row" id="proforma_bank_box" style="display:none;">
                            <div class="col-md-3">
                                <label>Proforma Bank</label>
                                <input type="text" id="proforma_bank_name" class="form-control" readonly>
                                <input type="hidden" name="bank_id" id="bank_id">
                                <input type="hidden" name="bank_name" id="bank_name">
                            </div>

                            <div class="col-md-3">
                                <label>Account Number</label>
                                <input type="text" name="account_number" id="account_number" class="form-control"
                                    readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Swift Code</label>
                                <input type="text" name="swift_code" id="swift_code" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Branch</label>
                                <input type="text" name="branch" id="branch" class="form-control" readonly>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= ITEMS ================= --}}
                        <div class="table-responsive">
                            <table class="table table-bordered" id="invoice_items">
                                <thead>
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Description / Product / Service</th>
                                        <th style="width:110px">Qty</th>
                                        <th style="width:120px">Unit</th>
                                        <th style="width:150px">Price</th>
                                        <th style="width:150px">Line Total</th>
                                        <th style="width:70px">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-primary" id="addRow">
                            + Add Product/Service
                        </button>

                        <hr>

                        {{-- ================= TOTALS ================= --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="summary-box">
                                    <h5>Subtotal / Sales Value</h5>
                                    <h4 id="subtotal_text">0.00</h4>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="summary-box">
                                    <h5>VAT</h5>
                                    <h4 id="vat_text">0.00</h4>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="summary-box">
                                    <h5>Total Invoice</h5>
                                    <h4 id="total_text">0.00</h4>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="summary-box">
                                    <h5>Pending Payment</h5>
                                    <h4 id="paid_text">0.00</h4>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= PAYMENT ================= --}}
                        <div class="row">
                            <div class="col-md-3">
                                <label>Payment Type</label>
                                <select name="payment_type" id="payment_type" class="form-control select2_demo_2">
                                    <option value="full">Full Payment</option>
                                    <option value="partial">Partial Payment</option>
                                    <option value="credit">Credit / No Payment Now</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Amount Paid</label>
                                <input type="number" name="payment_amount" id="payment_amount" step="0.01"
                                    class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-control select2_demo_2">
                                    <option value="bank">Bank</option>
                                    <option value="cash">Cash</option>
                                    <option value="mobile">Mobile Network</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Bank / Cash / Mobile Account</label>
                                <select name="payment_account_id" id="payment_account_id"
                                    class="form-control select2_demo_2">
                                    <option value="">-- Select Account --</option>
                                    @foreach ($paymentAccounts as $a)
                                        <option value="{{ $enc($a->id) }}">
                                            {{ $a->SubCode }} - {{ $a->SubDescription }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="readonly-note">
                                    Bank pulls account class 56, Cash pulls 57, Mobile pulls mobile subaccounts.
                                </div>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Receipt No</label>
                                <input type="text" name="receipt_no" class="form-control">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Receipt Attachment</label>
                                <input type="file" name="receipt_attachment" class="form-control">
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Payment Notes</label>
                                <input type="text" name="payment_notes" class="form-control">
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-success" id="saveInvoiceBtn">
                            <i class="fa fa-save"></i> Create Invoice
                        </button>
                    </form>
                </div>
            </div>
        @endcan

        {{-- ================= INVOICE LIST ================= --}}
        <div class="erp-card">
            <div class="erp-card-title" style="background:#23c6c8">Created Invoices</div>
            <div class="erp-card-body table-responsive">
                <table class="table table-striped table-bordered dataTables-example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Proforma</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid Approved</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $inv->invoice_no }}</strong></td>
                                <td>{{ optional($inv->proforma)->proforma_no ?? '-' }}</td>
                                <td>{{ optional($inv->customer)->customer_name }}</td>
                                <td>{{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                                <td>{{ $inv->currency }} {{ number_format($inv->paid_amount, 2) }}</td>
                                <td>{{ $inv->currency }} {{ number_format($inv->balance, 2) }}</td>
                                <td>
                                    <span
                                        class="label label-{{ $inv->payment_status == 'paid' ? 'success' : ($inv->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ strtoupper($inv->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('sales.invoice.view', $enc($inv->id)) }}"
                                        class="btn btn-xs btn-warning">View</a>

                                    <a href="{{ route('sales.invoice.print', $enc($inv->id)) }}" target="_blank"
                                        class="btn btn-xs btn-primary">Print</a>

                                    @if (!$inv->isLocked())
                                        <form action="{{ route('sales.invoice.delete', $enc($inv->id)) }}" method="POST"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-xs btn-danger"
                                                onclick="return confirm('Delete invoice?')">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No invoices found</td>
                            </tr>
                        @endforelse
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
            document.getElementById('Hour').innerHTML = String(d.getHours()).padStart(2, '0') + ':';
            document.getElementById('Minut').innerHTML = String(d.getMinutes()).padStart(2, '0') + ':';
            document.getElementById('Second').innerHTML = String(d.getSeconds()).padStart(2, '0');
        }

        timedMsg();

        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = 0;
            let products = [];
            let services = [];
            let selectedProforma = null;

            const productUrlBase = "{{ url('/admin/invoice-products') }}";
            const serviceUrlBase = "{{ url('/admin/invoice-services') }}";
            const paymentAccountUrlBase = "{{ url('/admin/payment-accounts') }}";
            const proformaDetailsUrlBase = "{{ url('/admin/proformas/details') }}";
            const companyUnitsUrlBase = "{{ url('/admin/ajax/company-units') }}";
            const unitWorkPointsUrlBase = "{{ url('/admin/ajax/unit-workpoints') }}";

            function initSelect2() {
                if (window.$ && $.fn.select2) {
                    $('.select2_demo_2').select2({
                        theme: 'bootstrap4',
                        width: '100%'
                    });
                }
            }

            initSelect2();

            function money(n) {
                return (parseFloat(n) || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function setSelectValue(selector, value) {
                setSelectValueWithText(selector, value, null);
            }

            function setSelectValueWithText(selector, value, text) {
                const el = $(selector);

                if (!value) {
                    el.val('').trigger('change.select2');
                    return;
                }

                // If the encrypted value is not already in the select options, add it.
                // This is important for proforma-selected records because Select2 displays blank
                // when value exists but no matching <option> is present.
                if (el.find('option[value="' + value + '"]').length === 0) {
                    el.append(new Option(text || value, value, true, true));
                }

                el.val(value).trigger('change.select2');
            }

            function makeLabel(code, name) {
                code = code || '';
                name = name || '';

                if (code && name) {
                    return code + ' - ' + name;
                }

                return name || code || '';
            }

            function setReadonlyFromProforma(isReadonly) {
                const ids = [
                    '#customer_id',
                    '#company_id',
                    '#business_unit_id',
                    '#work_point_id',
                    '#invoice_type',
                    '#vat_option'
                ];

                ids.forEach(id => {
                    const el = $(id);

                    // Do not disable these selects, because disabled Select2 sometimes displays blank
                    // and disabled fields are not submitted. Instead, lock the UI with CSS/pointer events
                    // and submit hidden mirrors.
                    el.prop('disabled', false);

                    if (isReadonly) {
                        el.addClass('proforma-locked-control proforma-locked-select');
                        el.next('.select2-container').addClass('proforma-select2-locked');
                    } else {
                        el.removeClass('proforma-locked-control proforma-locked-select');
                        el.next('.select2-container').removeClass('proforma-select2-locked');
                    }

                    el.trigger('change.select2');
                });

                removeHiddenMirrors();

                if (isReadonly) {
                    mirrorSelect('customer_id');
                    mirrorSelect('company_id');
                    mirrorSelect('business_unit_id');
                    mirrorSelect('work_point_id');
                    mirrorSelect('invoice_type');
                    mirrorSelect('vat_option');
                }
            }

            $(document).on('select2:opening', '.proforma-locked-select', function(e) {
                e.preventDefault();
            });

            function mirrorSelect(id) {
                const el = document.getElementById(id);

                if (!el || !el.name) {
                    return;
                }

                let hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = el.name;
                hidden.value = el.value;
                hidden.className = 'proforma-hidden-mirror';

                document.getElementById('invoiceForm').appendChild(hidden);
            }

            function removeHiddenMirrors() {
                document.querySelectorAll('.proforma-hidden-mirror').forEach(el => el.remove());
            }

            function productServiceOptions(item = {}) {
                let selectedId = item.product_id || item.service_id || '';

                let html = '<option value="">-- Select Item --</option>';

                if (item.product_name && selectedId) {
                    html += `<option value="${selectedId}" selected
                                data-name="${escapeHtml(item.product_name)}"
                                data-price="${item.price || 0}"
                                data-unit="${item.unit || 'pcs'}">
                                ${escapeHtml(item.product_name)}
                             </option>`;
                }

                let list = item.item_type === 'service' ? services : products;

                list.forEach(p => {
                    if (item.item_type === 'service') {
                        html += `<option value="${p.id}"
                                    data-name="${escapeHtml(p.service_name)}"
                                    data-price="${p.price || 0}"
                                    data-unit="${p.unit || 'service'}">
                                    ${escapeHtml(p.service_name)} | Price: ${money(p.price || 0)}
                                 </option>`;
                    } else {
                        html += `<option value="${p.id}"
                                    data-name="${escapeHtml(p.product_name)}"
                                    data-price="${p.selling_price || p.avg_cost || 0}"
                                    data-unit="${p.unit || 'pcs'}">
                                    ${escapeHtml(p.product_name)} | Stock: ${money(p.total_qty || 0)} | Price: ${money(p.selling_price || p.avg_cost || 0)}
                                 </option>`;
                    }
                });

                return html;
            }

            function rowHtml(item = {}) {
                let i = rowIndex++;

                let itemType = item.item_type || 'product';
                let itemIdField = itemType === 'service' ? 'service_id' : 'product_id';
                let itemValue = itemType === 'service' ? (item.service_id || '') : (item.product_id || '');

                return `
                    <tr>
                        <td>${i + 1}</td>

                        <td>
                            <select name="items[${i}][item_type]" class="form-control item_type select2_demo_2" ${selectedProforma ? 'disabled' : ''}>
                                <option value="product" ${itemType === 'product' ? 'selected' : ''}>Product</option>
                                <option value="service" ${itemType === 'service' ? 'selected' : ''}>Service</option>
                            </select>

                            ${selectedProforma ? `<input type="hidden" name="items[${i}][item_type]" value="${itemType}">` : ''}

                            <select name="items[${i}][${itemIdField}]"
                                    class="form-control item_select select2_demo_2 mt-1"
                                    ${selectedProforma ? 'disabled' : ''}>
                                ${productServiceOptions(item)}
                            </select>

                            ${selectedProforma && itemValue ? `<input type="hidden" name="items[${i}][${itemIdField}]" value="${itemValue}">` : ''}

                            <input type="hidden" name="items[${i}][product_name]"
                                   class="product_name"
                                   value="${escapeAttr(item.product_name || '')}">

                            <input type="text"
                                   name="items[${i}][description]"
                                   class="form-control mt-1 description_input"
                                   value="${escapeAttr(item.description || item.product_name || '')}"
                                   placeholder="Description"
                                   ${selectedProforma ? 'readonly' : ''}>
                        </td>

                        <td>
                            <input type="number"
                                   step="0.01"
                                   name="items[${i}][qty]"
                                   class="form-control qty"
                                   value="${item.qty || 1}"
                                   ${selectedProforma ? 'readonly' : ''}>
                        </td>

                        <td>
                            <input type="text"
                                   name="items[${i}][unit]"
                                   class="form-control unit"
                                   value="${escapeAttr(item.unit || (itemType === 'service' ? 'service' : 'pcs'))}"
                                   ${selectedProforma ? 'readonly' : ''}>
                        </td>

                        <td>
                            <input type="number"
                                   step="0.01"
                                   name="items[${i}][price]"
                                   class="form-control price"
                                   value="${item.price || 0}"
                                   ${selectedProforma ? 'readonly' : ''}>
                        </td>

                        <td>
                            <input type="number"
                                   name="items[${i}][total]"
                                   class="form-control total"
                                   readonly
                                   value="${item.total || 0}">
                        </td>

                        <td>
                            ${selectedProforma ? '-' : '<button type="button" class="btn btn-danger remove">X</button>'}
                        </td>
                    </tr>
                `;
            }

            function addRow(item = {}) {
                document.querySelector('#invoice_items tbody').insertAdjacentHTML('beforeend', rowHtml(item));
                initSelect2();
                calculate();
            }

            function clearRows() {
                document.querySelector('#invoice_items tbody').innerHTML = '';
                rowIndex = 0;
            }

            function clearItemsForNewLocation() {
                products = [];
                services = [];
                if (!selectedProforma) {
                    document.querySelectorAll('#invoice_items tbody tr').forEach(row => {
                        const select = row.querySelector('.item_select');
                        if (select) {
                            if ($(select).hasClass('select2-hidden-accessible')) {
                                $(select).select2('destroy');
                            }
                            select.innerHTML = '<option value="">-- Select Item --</option>';
                        }
                    });
                    initSelect2();
                }
            }

            function loadCompanyUnits(companyId, selectedUnit = '', selectedUnitText = '') {
                $('#business_unit_id')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                $('#work_point_id')
                    .empty()
                    .append('<option value="">Select Work Point</option>')
                    .trigger('change.select2');

                clearItemsForNewLocation();

                if (!companyId) {
                    $('#business_unit_id')
                        .empty()
                        .append('<option value="">Select Unit</option>')
                        .trigger('change.select2');
                    return;
                }

                fetch(`${companyUnitsUrlBase}/${companyId}`)
                    .then(response => response.json())
                    .then(data => {
                        $('#business_unit_id')
                            .empty()
                            .append('<option value="">Select Unit</option>');

                        data.forEach(unit => {
                            $('#business_unit_id').append(
                                new Option(unit.text, unit.id, false, selectedUnit && String(
                                    selectedUnit) === String(unit.id))
                            );
                        });

                        if (selectedUnit && $('#business_unit_id option[value="' + selectedUnit + '"]')
                            .length === 0) {
                            $('#business_unit_id').append(new Option(selectedUnitText || selectedUnit,
                                selectedUnit, true, true));
                        }

                        $('#business_unit_id').val(selectedUnit || '').trigger('change.select2');

                        if (selectedUnit) {
                            loadUnitWorkPoints(selectedUnit, $('#work_point_id').val(), '');
                        }
                    })
                    .catch(() => {
                        $('#business_unit_id')
                            .empty()
                            .append('<option value="">Select Unit</option>')
                            .trigger('change.select2');
                    });
            }

            function loadUnitWorkPoints(unitId, selectedPoint = '', selectedPointText = '') {
                $('#work_point_id')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                clearItemsForNewLocation();

                if (!unitId) {
                    $('#work_point_id')
                        .empty()
                        .append('<option value="">Select Work Point</option>')
                        .trigger('change.select2');
                    return;
                }

                fetch(`${unitWorkPointsUrlBase}/${unitId}`)
                    .then(response => response.json())
                    .then(data => {
                        $('#work_point_id')
                            .empty()
                            .append('<option value="">Select Work Point</option>');

                        data.forEach(work => {
                            $('#work_point_id').append(
                                new Option(work.text, work.id, false, selectedPoint && String(
                                    selectedPoint) === String(work.id))
                            );
                        });

                        if (selectedPoint && $('#work_point_id option[value="' + selectedPoint + '"]')
                            .length === 0) {
                            $('#work_point_id').append(new Option(selectedPointText || selectedPoint,
                                selectedPoint, true, true));
                        }

                        $('#work_point_id').val(selectedPoint || '').trigger('change.select2');

                        if ($('#work_point_id').val()) {
                            refreshManualItemOptions();
                        }
                    })
                    .catch(() => {
                        $('#work_point_id')
                            .empty()
                            .append('<option value="">Select Work Point</option>')
                            .trigger('change.select2');
                    });
            }

            function refreshManualItemOptions() {
                if (selectedProforma) {
                    return;
                }

                loadProductsAndServices(function() {
                    document.querySelectorAll('#invoice_items tbody tr').forEach(row => {
                        let itemType = row.querySelector('.item_type')?.value || 'product';
                        let select = row.querySelector('.item_select');
                        let currentValue = select ? select.value : '';
                        let currentName = row.querySelector('.product_name')?.value || '';
                        let currentPrice = row.querySelector('.price')?.value || 0;
                        let currentUnit = row.querySelector('.unit')?.value || 'pcs';

                        if (!select) return;

                        let item = {
                            item_type: itemType,
                            product_id: itemType === 'product' ? currentValue : '',
                            service_id: itemType === 'service' ? currentValue : '',
                            product_name: currentName,
                            price: currentPrice,
                            unit: currentUnit
                        };

                        if ($(select).hasClass('select2-hidden-accessible')) {
                            $(select).select2('destroy');
                        }

                        select.innerHTML = productServiceOptions(item);
                    });

                    initSelect2();
                });
            }

            function loadProductsAndServices(callback = null) {
                let c = $('#company_id').val();
                let u = $('#business_unit_id').val() || '0';
                let w = $('#work_point_id').val();

                if (!c || !w) {
                    products = [];
                    services = [];
                    if (callback) callback();
                    return;
                }

                fetch(`${productUrlBase}/${c}/${u}/${w}`)
                    .then(response => response.json())
                    .then(data => {
                        products = Array.isArray(data) ? data : [];

                        return fetch(`${serviceUrlBase}/${c}/${u}/${w}`);
                    })
                    .then(response => response.json())
                    .then(data => {
                        services = Array.isArray(data) ? data : [];

                        if (callback) callback();
                    })
                    .catch(() => {
                        if (callback) callback();
                    });
            }

            function loadPaymentAccounts(selectProformaBank = false) {
                let method = $('#payment_method').val() || 'bank';
                let company = $('#company_id').val();
                let workpoint = $('#work_point_id').val();

                let url =
                    `${paymentAccountUrlBase}/${method}?company_id=${encodeURIComponent(company || '')}&work_point_id=${encodeURIComponent(workpoint || '')}`;

                $('#payment_account_id')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        $('#payment_account_id')
                            .empty()
                            .append('<option value="">-- Select Account --</option>');

                        let hasProformaBank = false;

                        data.forEach(a => {
                            let selected = false;

                            if (
                                selectProformaBank &&
                                selectedProforma &&
                                selectedProforma.bank_id &&
                                method === 'bank' &&
                                String(a.id) === String(selectedProforma.bank_id)
                            ) {
                                selected = true;
                                hasProformaBank = true;
                            }

                            $('#payment_account_id').append(
                                $('<option>', {
                                    value: a.id,
                                    text: a.label,
                                    selected: selected
                                })
                            );
                        });

                        // If proforma bank is not in filtered list, still add it so bank payment can use it.
                        if (
                            selectProformaBank &&
                            selectedProforma &&
                            selectedProforma.bank_id &&
                            selectedProforma.bank_name &&
                            method === 'bank' &&
                            !hasProformaBank
                        ) {
                            $('#payment_account_id').append(
                                $('<option>', {
                                    value: selectedProforma.bank_id,
                                    text: (selectedProforma.bank_code || '') + ' - ' + selectedProforma
                                        .bank_name,
                                    selected: true
                                })
                            );
                        }

                        $('#payment_account_id').trigger('change.select2');
                    });
            }

            function loadProforma(encryptedId) {
                selectedProforma = null;

                if (!encryptedId) {
                    setReadonlyFromProforma(false);
                    $('#proforma_bank_box').hide();
                    clearRows();
                    addRow();
                    calculate();
                    return;
                }

                fetch(`${proformaDetailsUrlBase}/${encryptedId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            $('#proforma_id').val('').trigger('change.select2');
                            return;
                        }

                        selectedProforma = data.proforma;

                        setSelectValueWithText(
                            '#customer_id',
                            selectedProforma.customer_id,
                            makeLabel(selectedProforma.customer_code, selectedProforma.customer_name)
                        );

                        setSelectValueWithText(
                            '#company_id',
                            selectedProforma.company_id,
                            makeLabel(selectedProforma.company_code, selectedProforma.company_name)
                        );

                        setSelectValueWithText(
                            '#business_unit_id',
                            selectedProforma.business_unit_id,
                            makeLabel(selectedProforma.business_unit_code, selectedProforma
                                .business_unit_name)
                        );

                        setSelectValueWithText(
                            '#work_point_id',
                            selectedProforma.work_point_id,
                            makeLabel(selectedProforma.work_point_code, selectedProforma.work_point_name)
                        );

                        setSelectValueWithText(
                            '#invoice_type',
                            selectedProforma.invoice_type || 'local',
                            (selectedProforma.invoice_type || 'local') === 'export' ? 'Export' : 'Local'
                        );

                        // Proforma controls VAT. If it has VAT > 0 show yes, otherwise no.
                        setSelectValueWithText(
                            '#vat_option',
                            parseFloat(selectedProforma.vat || 0) > 0 ? 'yes' : 'no',
                            parseFloat(selectedProforma.vat || 0) > 0 ? 'VAT Inclusive' : 'No VAT'
                        );

                        $('#customer_info').text('');
                        $('#company_info').text('');
                        $('#unit_info').text('');
                        $('#workpoint_info').text('');

                        $('#bank_id').val(selectedProforma.bank_id || '');
                        $('#bank_name').val(selectedProforma.bank_name || '');
                        $('#proforma_bank_name').val(selectedProforma.bank_name || '-');
                        $('#account_number').val(selectedProforma.account_number || '');
                        $('#swift_code').val(selectedProforma.swift_code || '');
                        $('#branch').val(selectedProforma.branch || '');
                        $('#proforma_bank_box').show();

                        clearRows();
                        data.items.forEach(item => addRow(item));

                        setReadonlyFromProforma(true);

                        $('#payment_type').val('full').trigger('change.select2');
                        $('#payment_amount').val(parseFloat(selectedProforma.balance || 0).toFixed(2));

                        if (selectedProforma.bank_id) {
                            $('#payment_method').val('bank').trigger('change.select2');
                            loadPaymentAccounts(true);
                        } else {
                            loadPaymentAccounts(false);
                        }

                        calculate();
                    })
                    .catch(() => {
                        alert('Could not load proforma details.');
                    });
            }

            function calculate() {
                let grossTotal = 0;
                let lineSubtotal = 0;
                let vat = 0;
                let total = 0;

                document.querySelectorAll('#invoice_items tbody tr').forEach(row => {
                    let qty = parseFloat(row.querySelector('.qty')?.value) || 0;
                    let price = parseFloat(row.querySelector('.price')?.value) || 0;
                    let line = qty * price;

                    row.querySelector('.total').value = line.toFixed(2);
                    grossTotal += line;
                });

                if (selectedProforma) {
                    lineSubtotal = parseFloat(selectedProforma.subtotal || 0);
                    vat = parseFloat(selectedProforma.vat || 0);
                    total = parseFloat(selectedProforma.total || 0);
                } else {
                    let vatOption = $('#vat_option').val();
                    let invoiceType = $('#invoice_type').val();

                    if (vatOption === 'yes' && invoiceType !== 'export') {
                        total = grossTotal;
                        lineSubtotal = total / 1.18;
                        vat = total - lineSubtotal;
                    } else {
                        lineSubtotal = grossTotal;
                        vat = 0;
                        total = grossTotal;
                    }
                }

                let paymentType = $('#payment_type').val();
                let paid = 0;

                if (paymentType === 'full') {
                    paid = selectedProforma ?
                        parseFloat(selectedProforma.balance || 0) :
                        total;

                    $('#payment_amount').val(paid.toFixed(2));
                } else if (paymentType === 'credit') {
                    paid = 0;
                    $('#payment_amount').val('0.00');
                } else {
                    paid = parseFloat($('#payment_amount').val()) || 0;
                }

                $('#subtotal_text').text(money(lineSubtotal));
                $('#vat_text').text(money(vat));
                $('#total_text').text(money(total));
                $('#paid_text').text(money(paid));
            }

            $('#proforma_id').on('change', function() {
                loadProforma($(this).val());
            });

            $('#company_id').on('change', function() {
                if (!selectedProforma) {
                    loadCompanyUnits($(this).val());
                }
                loadPaymentAccounts(false);
            });

            $('#business_unit_id').on('change', function() {
                if (!selectedProforma) {
                    loadUnitWorkPoints($(this).val());
                }
                loadPaymentAccounts(false);
            });

            $('#work_point_id').on('change', function() {
                if (!selectedProforma) {
                    refreshManualItemOptions();
                }
                loadPaymentAccounts(false);
            });

            $('#payment_method').on('change', function() {
                loadPaymentAccounts(true);
            });

            $('#payment_type, #payment_amount, #invoice_type, #vat_option').on('change input', function() {
                calculate();
            });

            $('#addRow').on('click', function() {
                if (selectedProforma) {
                    alert('Proforma items are fixed. Clear the proforma to add manual items.');
                    return;
                }

                addRow();
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove')) {
                    e.target.closest('tr').remove();
                    reNumberRows();
                    calculate();
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('item_type')) {
                    let row = e.target.closest('tr');
                    let select = row.querySelector('.item_select');

                    row.querySelector('.product_name').value = '';
                    row.querySelector('.description_input').value = '';
                    row.querySelector('.price').value = 0;
                    row.querySelector('.unit').value = e.target.value === 'service' ? 'service' : 'pcs';
                    row.querySelector('.total').value = 0;

                    if ($(select).hasClass('select2-hidden-accessible')) {
                        $(select).select2('destroy');
                    }

                    select.name = e.target.value === 'service' ?
                        select.name.replace('[product_id]', '[service_id]') :
                        select.name.replace('[service_id]', '[product_id]');

                    select.innerHTML = productServiceOptions({
                        item_type: e.target.value
                    });

                    initSelect2();
                    calculate();
                }

                if (e.target.classList.contains('item_select')) {
                    let row = e.target.closest('tr');
                    let option = e.target.options[e.target.selectedIndex];

                    let name = option.getAttribute('data-name') || '';
                    let price = option.getAttribute('data-price') || 0;
                    let unit = option.getAttribute('data-unit') || 'pcs';

                    row.querySelector('.product_name').value = name;
                    row.querySelector('.description_input').value = name;
                    row.querySelector('.price').value = parseFloat(price).toFixed(2);
                    row.querySelector('.unit').value = unit;

                    calculate();
                }
            });

            document.addEventListener('input', function(e) {
                if (
                    e.target.classList.contains('qty') ||
                    e.target.classList.contains('price') ||
                    e.target.classList.contains('description_input')
                ) {
                    calculate();
                }
            });

            $('#invoiceForm').on('submit', function(e) {
                const paymentType = $('#payment_type').val();
                const amount = parseFloat($('#payment_amount').val()) || 0;
                const account = $('#payment_account_id').val();

                if (paymentType !== 'credit' && amount > 0 && !account) {
                    e.preventDefault();
                    alert('Please select Bank / Cash / Mobile Account before saving invoice payment.');
                    $('#payment_account_id').select2('open');
                    return false;
                }

                $('#saveInvoiceBtn')
                    .prop('disabled', true)
                    .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                return true;
            });

            function reNumberRows() {
                document.querySelectorAll('#invoice_items tbody tr').forEach((row, index) => {
                    row.querySelector('td:first-child').innerText = index + 1;
                });
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function escapeAttr(value) {
                return escapeHtml(value).replace(/`/g, '&#096;');
            }

            addRow();
            loadPaymentAccounts(false);
        });
    </script>
@endsection
