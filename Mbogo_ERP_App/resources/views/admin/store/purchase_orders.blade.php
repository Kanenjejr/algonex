@extends('layouts.salesMaster')

@section('content')
    @php
        $companies = $companies ?? collect();
        $vendors = $vendors ?? collect();
        $items = $items ?? collect();
        $rawMaterials = $rawMaterials ?? collect();
        $orders = $orders ?? collect();

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
                    'account_code' => $v->account_code ?? '',
                    'account_name' => $v->account_name ?? '',
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

        /*
        |--------------------------------------------------------------------------
        | RECEIVE DATA
        |--------------------------------------------------------------------------
        | Use real id only for JS matching. Do not use encrypt($o->id) here because
        | Laravel encryption changes value every time. The form action still uses
        | encrypted id from the button.
        */
        $ordersForReceive = $orders
            ->map(function ($o) {
                return [
                    'id' => $o->id,
                    'items' => $o->items
                        ->map(function ($it) {
                            return [
                                'id' => $it->id,
                                'item_name' => $it->item_name,
                                'qty' => $it->qty,
                                'received_qty' => $it->received_qty,
                                'balance_qty' => $it->balance_qty,
                                'unit' => $it->unit,
                            ];
                        })
                        ->values(),
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

        #po_items_table .select2-container {
            width: 100% !important;
        }

        .table thead th {
            background: #0c2f6e !important;
            color: #fff !important;
            vertical-align: middle !important;
            white-space: nowrap;
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

        .po-total-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 6px;
        }

        .po-total-box label {
            font-weight: bold;
            color: #333;
        }

        .po-small-note {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #777;
        }
    </style>

    <div class="wrapper wrapper-content">

        {{-- ================= HEADER ================= --}}
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-8">
                <h2 class="dashboard-title">General Supply Dashboard</h2>
                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li>
                        <a href="{{ route('company.dashboard') }}">Dashboard</a>
                    </li>
                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                    <li class="breadcrumb-item active">
                        <strong>Purchase Orders</strong>
                    </li>
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

        {{-- ================= CREATE PURCHASE ORDER ================= --}}
        @can('Register-Purchase-Orders')
            <div class="ibox mt-3">
                <div class="ibox-title bg-primary">
                    <h5>
                        <i class="fa fa-plus"></i> Create Purchase Order
                    </h5>
                </div>

                <div class="ibox-content">
                    <form method="POST" action="{{ route('sales.po.store') }}" enctype="multipart/form-data" id="poForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-3">
                                <label>Company *</label>
                                <select name="company_id" id="company_id" class="form-control select2_demo_2" required>
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->company_code }} - {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Business Unit *</label>
                                <select name="business_unit_id" id="business_unit_id" class="form-control select2_demo_2"
                                    required>
                                    <option value="">Select Business Unit</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Work Point / Location *</label>
                                <select name="work_point_id" id="work_point_id" class="form-control select2_demo_2" required>
                                    <option value="">Select Work Point</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Vendor / Supplier *</label>
                                <select name="vendor_id" id="vendor_id" class="form-control select2_demo_2" required>
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}">
                                            {{ $v->vendor_code }} - {{ $v->vendor_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>PO Date *</label>
                                <input type="date" name="po_date" class="form-control" value="{{ now()->toDateString() }}"
                                    required>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" class="form-control">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Supplier PI No</label>
                                <input type="text" name="pi_no" class="form-control">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Purchase Type *</label>
                                <select name="purchase_type" id="purchase_type" class="form-control select2_demo_2" required>
                                    <option value="GeneralSupply">General Supply / Product</option>
                                    <option value="RawMaterial">Raw Material</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Currency *</label>
                                <select name="currency" id="currency" class="form-control select2_demo_2" required>
                                    <option value="TZS" selected>TZS</option>
                                    <option value="USD">USD</option>
                                    <option value="KES">KES</option>
                                    <option value="UGX">UGX</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Exchange Rate *</label>
                                <input type="number" name="exchange_rate" id="exchange_rate" class="form-control"
                                    value="1" step="0.0001" min="0.0001" required>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>VAT Rate (%)</label>
                                <input type="number" name="vat_rate" id="vat_rate" class="form-control" value="18"
                                    step="0.0001" min="0">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Discount</label>
                                <input type="number" name="discount" id="discount" class="form-control" value="0"
                                    step="0.0001" min="0">
                            </div>
                        </div>

                        <hr>

                        {{-- ================= SHIPPING ================= --}}
                        <div class="row">
                            <div class="col-md-6">
                                <label>Ship To</label>
                                <textarea name="ship_to" id="ship_to" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label>From Vendor</label>
                                <textarea name="vendor_from" id="vendor_from" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Shipping Method</label>
                                <input type="text" name="shipping_method" class="form-control" value="By Road">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Shipping Terms</label>
                                <input type="text" name="shipping_terms" class="form-control" value="N/A">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Delivery Point</label>
                                <input type="text" name="delivery_point" id="delivery_point" class="form-control">
                            </div>
                        </div>

                        <hr>

                        {{-- ================= ITEMS ================= --}}
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

                        <button type="button" id="addPoRow" class="btn btn-primary">
                            + Add Item
                        </button>

                        <hr>

                        {{-- ================= TOTALS ================= --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="po-total-box">
                                    <label>Subtotal</label>
                                    <input type="text" id="subtotal_view" class="form-control" readonly value="0.00">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="po-total-box">
                                    <label>VAT</label>
                                    <input type="text" id="vat_view" class="form-control" readonly value="0.00">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="po-total-box">
                                    <label>Discount</label>
                                    <input type="text" id="discount_view" class="form-control" readonly value="0.00">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="po-total-box">
                                    <label>Total</label>
                                    <input type="text" id="grand_total_view" class="form-control" readonly
                                        value="0.00">
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= ATTACHMENT DURING PO CREATION ================= --}}
                        <div class="row">
                            <div class="col-md-4">
                                <label>Supplier Proforma / Quotation</label>
                                <input type="file" name="supplier_proforma_attachment" class="form-control">
                                <small class="po-small-note">
                                    Optional. Supplier Invoice and Delivery Note will be uploaded during receiving.
                                </small>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Terms & Conditions</label>
                                <textarea name="terms_conditions" class="form-control" rows="4">1. Enter this order in accordance with the price, terms, delivery methods and specification listed.
2. The above price is valid for 7 days from date of issue.
3. All taxes and charges must be arranged as agreed.
4. Unit prices are subject to supplier quotation.
5. Payment after receiving PO and invoice.</textarea>
                            </div>

                            <div class="col-md-6">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="4"></textarea>
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save Purchase Order
                        </button>

                        <button type="reset" class="btn btn-default">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </form>
                </div>
            </div>
        @endcan

        {{-- ================= PURCHASE ORDER LIST ================= --}}
        @can('View-Purchase-Orders')
            <div class="ibox">
                <div class="ibox-title bg-info">
                    <h5>Purchase Orders Table</h5>
                </div>

                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>PO No</th>
                                    <th>Date</th>
                                    <th>Company</th>
                                    <th>Unit</th>
                                    <th>Location</th>
                                    <th>Vendor</th>
                                    <th>Type</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Payment</th>
                                    <th>Receive</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($orders as $k => $row)
                                    @php
                                        $encryptedId = encrypt($row->id);
                                    @endphp

                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $row->po_no }}</td>
                                        <td>{{ $row->po_date ? \Carbon\Carbon::parse($row->po_date)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td>{{ optional($row->company)->company_code }} -
                                            {{ optional($row->company)->company_name }}</td>
                                        <td>{{ optional($row->businessUnit)->unit_code }} -
                                            {{ optional($row->businessUnit)->unit_name }}</td>
                                        <td>{{ optional($row->workPoint)->work_code }} -
                                            {{ optional($row->workPoint)->work_name }}</td>
                                        <td>{{ optional($row->vendor)->vendor_name }}</td>
                                        <td>{{ $row->purchase_type }}</td>
                                        <td>{{ $row->currency }} {{ number_format(round((float) $row->total_amount, 0), 2) }}
                                        </td>
                                        <td>{{ $row->currency }} {{ number_format((float) $row->amount_paid, 4) }}</td>
                                        <td>{{ $row->currency }} {{ number_format((float) $row->balance, 4) }}</td>
                                        <td>{{ strtoupper($row->payment_status) }}</td>
                                        <td>{{ strtoupper($row->receive_status) }}</td>
                                        <td>{{ $row->status }}</td>

                                        <td style="white-space:nowrap;">
                                            <a href="{{ route('sales.po.show', $encryptedId) }}"
                                                class="btn btn-xs btn-primary">
                                                View
                                            </a>

                                            @if (in_array($row->status, ['Draft', 'Cancelled']))
                                                @can('Edit-Purchase-Orders')
                                                    <a href="{{ route('sales.po.edit', $encryptedId) }}"
                                                        class="btn btn-xs btn-warning">
                                                        Edit
                                                    </a>
                                                @endcan
                                            @endif

                                            <a href="{{ route('sales.po.documents', $encryptedId) }}"
                                                class="btn btn-xs btn-default">
                                                Docs
                                            </a>

                                            <a href="{{ route('sales.po.print', $encryptedId) }}" target="_blank"
                                                class="btn btn-xs btn-info">
                                                Print
                                            </a>

                                            @if ($row->status === 'Draft')
                                                @can('Edit-Purchase-Orders')
                                                    <a href="{{ route('sales.po.approve', $encryptedId) }}"
                                                        class="btn btn-xs btn-success"
                                                        onclick="return confirm('Approve and post this Purchase Order to Accounting?')">
                                                        Approve
                                                    </a>
                                                @endcan
                                            @endif

                                            @if ($row->status === 'Draft')
                                                @can('Edit-Purchase-Orders')
                                                    <a href="{{ route('sales.po.reject', $encryptedId) }}"
                                                        class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Reject this Purchase Order?')">
                                                        Reject
                                                    </a>
                                                @endcan
                                            @endif

                                            @if (in_array($row->status, ['Approved', 'Ordered', 'PartiallyReceived']))
                                                @can('Edit-Purchase-Orders')
                                                    <button class="btn btn-xs btn-primary" type="button"
                                                        onclick="openReceiveModal('{{ $encryptedId }}', '{{ $row->id }}')">
                                                        Receive
                                                    </button>
                                                @endcan
                                            @endif

                                            @if ($row->payment_status !== 'paid')
                                                @can('Edit-Purchase-Orders')
                                                    <button class="btn btn-xs btn-warning" type="button"
                                                        onclick="openPaymentModal('{{ $encryptedId }}', {{ $row->balance }})">
                                                        Pay
                                                    </button>
                                                @endcan
                                            @endif

                                            @if ($row->status === 'Draft')
                                                @can('Delete-Purchase-Orders')
                                                    <a href="{{ route('sales.po.remove', $encryptedId) }}"
                                                        class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Remove this Purchase Order?')">
                                                        Remove
                                                    </a>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    {{-- ================= RECEIVE MODAL ================= --}}
    <div class="modal fade" id="receiveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="receiveForm" enctype="multipart/form-data">
                @csrf

                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5>Receive Purchase Order Items</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <label>Received Date</label>
                        <input type="date" name="received_date" class="form-control mb-3"
                            value="{{ now()->toDateString() }}" required>

                        <label>Attach Supplier Invoice</label>
                        <input type="file" name="supplier_invoice_attachment" class="form-control mb-3">

                        <label>Attach Delivery Note</label>
                        <input type="file" name="delivery_note_attachment" class="form-control mb-3">

                        <div id="receiveItemsBody"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary" id="receiveSubmitBtn">
                            Save Received Qty
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= PAYMENT MODAL ================= --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="paymentForm" enctype="multipart/form-data">
                @csrf

                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5>Pay Purchase Order</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Amount Paid</label>
                                <input type="number" name="amount_paid" id="pay_amount" class="form-control"
                                    step="0.0001" min="0.0001" required>
                                <small id="pay_balance_note" class="text-muted"></small>
                            </div>

                            <div class="col-md-4">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="">-- Select --</option>
                                    <option value="cash">Cash</option>
                                    <option value="pettycash">Petty Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="mobile">Mobile</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control"
                                    value="{{ now()->toDateString() }}" required>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Cheque No</label>
                                <input type="text" name="cheque_no" class="form-control">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Payment Reference</label>
                                <input type="text" name="payment_reference" class="form-control">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Attach Cheque / Petty Cash Document</label>
                                <input type="file" name="payment_attachment" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-warning">
                            Save Payment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= JAVASCRIPT ================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.vendorOptions = @json($vendorOptions);
            window.companyOptions = @json($companyOptions);
            window.generalItems = @json($generalItems);
            window.rawMaterialItems = @json($rawMaterialItems);
            window.ordersForReceive = @json($ordersForReceive);

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
                buildShipToText();
            });

            $('#business_unit_id').on('change', function() {
                loadWorkPoints(this.value);
                buildShipToText();
            });

            $('#work_point_id').on('change', function() {
                buildShipToText();
            });

            $('#vendor_id').on('change', function() {
                buildVendorFromText();
                calculateTotals();
            });

            $('#purchase_type').on('change', function() {
                $('#poItemsBody').html('');
                addPoRow();
            });

            $('#vat_rate, #discount').on('input', function() {
                calculateTotals();
            });

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
                    })
                    .catch(function() {
                        $('#business_unit_id').html('<option value="">Failed to load units</option>').trigger(
                            'change');
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
                        buildShipToText();
                    })
                    .catch(function() {
                        $('#work_point_id').html('<option value="">Failed to load work points</option>')
                            .trigger('change');
                    });
            }

            function buildShipToText() {
                let companyId = $('#company_id').val();
                let company = companyOptions.find(x => String(x.id) === String(companyId));

                let unitText = $('#business_unit_id option:selected').text();
                let workOption = $('#work_point_id option:selected');

                let text = '';

                if (company) {
                    text += company.company_name + "\n";

                    if (company.district || company.city) {
                        text += (company.district || '') + ' ' + (company.city || '') + "\n";
                    }

                    if (company.phone) text += company.phone + "\n";
                    if (company.email) text += company.email + "\n";
                }

                if (
                    unitText &&
                    unitText !== 'Select Business Unit' &&
                    unitText !== 'Loading...' &&
                    unitText !== 'Failed to load units'
                ) {
                    text += unitText + "\n";
                }

                if (workOption.val()) {
                    text += (workOption.attr('data-work-name') || '') + "\n";
                    text += (workOption.attr('data-location') || '') + "\n";
                    text += (workOption.attr('data-district') || '') + ' ' + (workOption.attr('data-city') || '');

                    $('#delivery_point').val(workOption.attr('data-work-name') || '');
                }

                $('#ship_to').val(text.trim());
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

            function addPoRow() {
                let index = $('#poItemsBody tr').length;
                let purchaseType = $('#purchase_type').val() || 'GeneralSupply';
                let source = purchaseType === 'RawMaterial' ? rawMaterialItems : generalItems;

                let options = '<option value="">-- Select Item --</option>';

                source.forEach(function(item) {
                    options += '<option value="' + escapeAttr(item.id) + '" ' +
                        'data-name="' + escapeAttr(item.name) + '" ' +
                        'data-unit="' + escapeAttr(item.unit) + '" ' +
                        'data-price="' + escapeAttr(item.price) + '" ' +
                        'data-account-code="' + escapeAttr(item.account_code) + '" ' +
                        'data-account-name="' + escapeAttr(item.account_name) + '">' +
                        escapeAttr(item.name) +
                        '</option>';
                });

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <select name="items[${index}][item_id]" class="form-control item_select select2_demo_2" required>
                                ${options}
                            </select>

                            <input type="hidden" name="items[${index}][account_code]" class="account_code">
                            <input type="hidden" name="items[${index}][account_name]" class="account_name">

                            <input type="text" name="items[${index}][description]" class="form-control mt-1 description" placeholder="Enter description">
                        </td>
                        <td>
                            <input type="number" name="items[${index}][qty]" class="form-control qty" value="1" step="0.0001" min="0.0001" required>
                        </td>
                        <td>
                            <input type="text" name="items[${index}][unit]" class="form-control unit">
                        </td>
                        <td>
                            <input type="number" name="items[${index}][unit_price]" class="form-control price" value="0" step="0.0001" min="0" required>
                        </td>
                        <td>
                            <input type="number" class="form-control actual_price" value="0" readonly step="0.0001">
                        </td>
                        <td>
                            <input type="number" class="form-control row_vat" value="0" readonly step="0.0001">
                        </td>
                        <td>
                            <input type="number" class="form-control line_total" value="0" readonly>
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

            $(document).on('input', '.qty, .price', function() {
                calculateTotals();
            });

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

            /*
            |--------------------------------------------------------------------------
            | RECEIVE MODAL FIX ONLY
            |--------------------------------------------------------------------------
            */
            window.openReceiveModal = function(encryptedId, orderId) {
                let order = ordersForReceive.find(x => String(x.id) === String(orderId));
                let body = $('#receiveItemsBody');

                body.html('');

                $('#receiveSubmitBtn').prop('disabled', false).text('Save Received Qty');

                if (!order) {
                    alert('No items found for this purchase order. Please refresh the page and try again.');
                    return;
                }

                if (!order.items || order.items.length < 1) {
                    alert('This purchase order has no items to receive.');
                    return;
                }

                order.items.forEach(function(item) {
                    let balanceQty = parseFloat(item.balance_qty || 0);

                    body.append(`
                        <div class="row mb-2" style="border-bottom:1px solid #ddd;padding-bottom:8px;">
                            <div class="col-md-6">
                                <strong>${escapeAttr(item.item_name)}</strong><br>
                                Ordered: ${escapeAttr(item.qty)} ${escapeAttr(item.unit || '')}<br>
                                Already Received: ${escapeAttr(item.received_qty)} ${escapeAttr(item.unit || '')}<br>
                                Balance: ${escapeAttr(item.balance_qty)} ${escapeAttr(item.unit || '')}
                            </div>
                            <div class="col-md-4">
                                <label>Receive Now</label>
                                <input type="number"
                                    name="items[${escapeAttr(item.id)}][received_qty]"
                                    class="form-control receive_qty"
                                    value="0"
                                    step="0.01"
                                    min="0"
                                    max="${escapeAttr(item.balance_qty)}"
                                    ${balanceQty <= 0 ? 'readonly' : ''}>
                            </div>
                        </div>
                    `);
                });

                $('#receiveForm').attr(
                    'action',
                    "{{ url('/admin/sales/purchase-orders/receive') }}/" + encodeURIComponent(encryptedId)
                );

                $('#receiveModal').modal('show');
            };

            $('#receiveForm').on('submit', function(e) {
                let totalReceivedNow = 0;

                $('.receive_qty').each(function() {
                    totalReceivedNow += parseFloat($(this).val() || 0);
                });

                if (totalReceivedNow <= 0) {
                    e.preventDefault();
                    alert('Please enter at least one received quantity greater than zero.');
                    return false;
                }

                $('#receiveSubmitBtn').prop('disabled', true).text('Saving...');
            });

            window.openPaymentModal = function(encryptedId, balance) {
                $('#paymentForm').attr(
                    'action',
                    "{{ url('/admin/sales/purchase-orders/payment') }}/" + encodeURIComponent(encryptedId)
                );

                $('#pay_amount').val(balance);
                $('#pay_amount').attr('max', balance);
                $('#pay_balance_note').html('Current balance: ' + parseFloat(balance).toFixed(2));

                $('#paymentModal').modal('show');
            };

            addPoRow();
        });
    </script>
@endsection
