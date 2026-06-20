@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>General Supply Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Received Items</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong><?php use Carbon\Carbon;
                $carbon = Carbon::now();
                $carbon1 = Carbon::now()->toDateString();
                echo $carbon->format('l');
                echo ' , ';
                echo $carbon1; ?></strong></li>
            </ol>
        </div>
        <div class="col-lg-1">
            <h2>Time</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong>
                        <table>
                            <tr>
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong></li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg() {
            setInterval("change_time();", 1000);
        }

        function change_time() {
            var d = new Date();
            document.getElementById('Hour').innerHTML = d.getHours() + ':';
            document.getElementById('Minut').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second').innerHTML = d.getSeconds();
        }
        timedMsg();
    </script>

    @php
        $workPointOptions = [];
        foreach ($workPoints as $wp) {
            $workPointOptions[] = [
                'id' => $wp->id,
                'name' => trim(($wp->work_code ?? '') . ' - ' . $wp->work_name, ' -'),
            ];
        }

        $sectionOptions = [];
        foreach ($sections as $sec) {
            $sectionOptions[] = [
                'id' => $sec->id,
                'name' => trim(($sec->secCode ?? '') . ' - ' . $sec->secName, ' -'),
            ];
        }

        $itemOptions = [];
        foreach ($items as $item) {
            $itemOptions[] = [
                'id' => $item->id,
                'name' => $item->item_name,
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Received Items</h3>
        @can('Register-Received-Item-Details')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openReceiveCreateModal()">
                Receive Item
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Received Items Table</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Expiry</th>
                                <th>Work Point</th>
                                <th>Department</th>
                                <th>Section</th>
                                <th>Scope</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Received Qty</th>
                                <th>Damaged Qty</th>
                                <th>Good Qty</th>
                                <th>Purchase Price</th>
                                <th>Total Amount</th>
                                <th>Supplier</th>
                                <th>Invoice No</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receivings as $k => $row)
                                @php
                                    $payload = [
                                        'id' => encrypt($row->id),
                                        'work_point_id' => $row->work_point_id,
                                        'section_id' => $row->section_id,
                                        'stock_scope' => $row->stock_scope,
                                        'item_id' => $row->item_id,
                                        'item_description_id' => $row->item_description_id,
                                        'receive_date' => $row->receive_date,
                                        'expiry_date' => $row->expiry_date,
                                        'received_qty' => $row->received_qty,
                                        'damaged_qty' => $row->damaged_qty,
                                        'purchase_price' => $row->purchase_price,
                                        'supplier_name' => $row->supplier_name,
                                        'invoice_no' => $row->invoice_no,
                                        'reference_no' => $row->reference_no,
                                        'remarks' => $row->remarks,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->receive_date }}</td>
                                    <td>{{ $row->expiry_date ?? '-' }}</td>
                                    <td>{{ optional($row->workpoint)->work_code }} -
                                        {{ optional($row->workpoint)->work_name }}</td>
                                    <td>{{ optional($row->department)->depName ?? '-' }}</td>
                                    <td>{{ optional($row->section)->secName ?? '-' }}</td>
                                    <td>{{ $row->stock_scope }}</td>
                                    <td>{{ optional($row->item)->item_name }}</td>
                                    <td>{{ optional($row->description)->description_name }}
                                        ({{ optional($row->description)->unit_name }})
                                    </td>
                                    <td>{{ number_format($row->received_qty, 2) }}</td>
                                    <td>{{ number_format($row->damaged_qty, 2) }}</td>
                                    <td>{{ number_format($row->good_qty, 2) }}</td>
                                    <td>{{ number_format($row->purchase_price, 2) }}</td>
                                    <td>{{ number_format($row->total_amount, 2) }}</td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ $row->invoice_no }}</td>
                                    <td>
                                        @can('Edit-Received-Item-Details')
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='openReceiveEdit(@json($payload))'>
                                                Edit
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center text-muted">No received items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE --}}
    <div class="modal fade" id="receiveCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="receiveCreateForm" action="{{ route('sales.gs.received.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Receive Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="form-group col-md-4">
                                <label><span style="color:red">*</span> Work Point</label>
                                <select name="work_point_id" class="form-control select2_modal" required>
                                    <option value="">-- Select Work Point --</option>
                                    @foreach ($workPoints as $wp)
                                        <option value="{{ $wp->id }}">{{ $wp->work_code }} - {{ $wp->work_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Section</label>
                                <select name="section_id" class="form-control select2_modal">
                                    <option value="">-- Select Section --</option>
                                    @foreach ($sections as $sec)
                                        <option value="{{ $sec->id }}">{{ $sec->secCode ?? '' }} -
                                            {{ $sec->secName }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Department will be picked automatically in backend from selected
                                    section.</small>
                            </div>

                            <div class="form-group col-md-4">
                                <label><span style="color:red">*</span> Stock Scope</label>
                                <select name="stock_scope" class="form-control select2_modal" required>
                                    <option value="">-- Select scope --</option>
                                    <option value="Shared">Shared</option>
                                    <option value="Dedicated">Dedicated</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label><span style="color:red">*</span> Item</label>
                                <select name="item_id" id="rec_item_id" class="form-control select2_modal" required>
                                    <option value="">-- Select Item --</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label><span style="color:red">*</span> Description</label>
                                <select name="item_description_id" id="rec_description_id"
                                    class="form-control select2_modal" required>
                                    <option value="">-- Select Description --</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label><span style="color:red">*</span> Receive Date</label>
                                <input type="date" name="receive_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Received Qty</label>
                                <input type="number" step="0.01" min="0.01" name="received_qty"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Damaged Qty</label>
                                <input type="number" step="0.01" min="0" name="damaged_qty"
                                    class="form-control" required value="0">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Purchase Price</label>
                                <input type="number" step="0.01" min="0" name="purchase_price"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Supplier Name</label>
                                <input type="text" name="supplier_name" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label>Reference No</label>
                                <input type="text" name="reference_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT --}}
    <div class="modal fade" id="receiveEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="receiveEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Received Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="receiveEditBody">
                        <div class="text-center">Loading...</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var workPointOptions = @json($workPointOptions);
            var sectionOptions = @json($sectionOptions);
            var itemOptions = @json($itemOptions);

            function initSelect2WithParent($el, parentSelector) {
                if (!$el || !$el.length) return;
                if ($el.data('select2')) {
                    try {
                        $el.select2('destroy');
                    } catch (e) {}
                }
                var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
                $el.select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownParent: $parent
                });
            }

            function initAllModalSelects(modalSelector) {
                $(modalSelector).find('.select2_modal').each(function() {
                    initSelect2WithParent($(this), modalSelector);
                });
            }

            $('.select2_modal').each(function() {
                var $this = $(this);
                if ($this.closest('#receiveCreateModal').length) {
                    initSelect2WithParent($this, '#receiveCreateModal');
                    return;
                }
                if ($this.closest('#receiveEditModal').length) {
                    initSelect2WithParent($this, '#receiveEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#receiveCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#receiveCreateModal');
            });

            $('#receiveEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#receiveEditModal');
            });

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            window.openReceiveCreateModal = function() {
                $('#receiveCreateModal').modal('show');
            };

            $('#rec_item_id').on('change', function() {
                let itemId = $(this).val();
                $('#rec_description_id').html('<option value="">Loading...</option>');

                if (!itemId) {
                    $('#rec_description_id').html('<option value="">-- Select Description --</option>')
                        .trigger('change');
                    return;
                }

                $.get("{{ url('/admin/reqsts/general-supply/ajax/descriptions') }}/" + itemId, function(
                    res) {
                    let html = '<option value="">-- Select Description --</option>';
                    res.forEach(function(row) {
                        html +=
                            `<option value="${row.id}">${row.description_name} (${row.unit_name})</option>`;
                    });
                    $('#rec_description_id').html(html).trigger('change');
                });
            });

            window.openReceiveEdit = function(row) {
                var html = '';

                html += '<div class="row mt-2">';

                html += '<div class="form-group col-md-4">';
                html += '<label><span style="color:red">*</span> Work Point</label>';
                html +=
                    '<select name="work_point_id" id="edit_rec_work_point_id" class="form-control select2_modal" required>';
                html += '<option value="">-- Select Work Point --</option>';
                for (var i = 0; i < workPointOptions.length; i++) {
                    html += '<option value="' + workPointOptions[i].id + '"' + (String(row.work_point_id) ===
                        String(workPointOptions[i].id) ? ' selected' : '') + '>' + escapeHtml(
                        workPointOptions[i].name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="form-group col-md-4">';
                html += '<label>Section</label>';
                html +=
                    '<select name="section_id" id="edit_rec_section_id" class="form-control select2_modal">';
                html += '<option value="">-- Select Section --</option>';
                for (var j = 0; j < sectionOptions.length; j++) {
                    html += '<option value="' + sectionOptions[j].id + '"' + (String(row.section_id) === String(
                        sectionOptions[j].id) ? ' selected' : '') + '>' + escapeHtml(sectionOptions[j]
                        .name) + '</option>';
                }
                html += '</select>';
                html +=
                    '<small class="text-muted">Department will be picked automatically in backend from selected section.</small>';
                html += '</div>';

                html += '<div class="form-group col-md-4">';
                html += '<label><span style="color:red">*</span> Stock Scope</label>';
                html +=
                    '<select name="stock_scope" id="edit_rec_stock_scope" class="form-control select2_modal" required>';
                html += '<option value="">-- Select scope --</option>';
                html += '<option value="Shared"' + (row.stock_scope === 'Shared' ? ' selected' : '') +
                    '>Shared</option>';
                html += '<option value="Dedicated"' + (row.stock_scope === 'Dedicated' ? ' selected' : '') +
                    '>Dedicated</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="form-group col-md-4">';
                html += '<label><span style="color:red">*</span> Item</label>';
                html +=
                    '<select name="item_id" id="edit_rec_item_id" class="form-control select2_modal" required>';
                html += '<option value="">-- Select Item --</option>';
                for (var k = 0; k < itemOptions.length; k++) {
                    html += '<option value="' + itemOptions[k].id + '"' + (String(row.item_id) === String(
                            itemOptions[k].id) ? ' selected' : '') + '>' + escapeHtml(itemOptions[k].name) +
                        '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="form-group col-md-4">';
                html += '<label><span style="color:red">*</span> Description</label>';
                html +=
                    '<select name="item_description_id" id="edit_rec_description_id" class="form-control select2_modal" required>';
                html += '<option value="">Loading...</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="form-group col-md-4">';
                html += '<label><span style="color:red">*</span> Receive Date</label>';
                html += '<input type="date" name="receive_date" class="form-control" value="' + escapeHtml(row
                    .receive_date || '') + '" required>';
                html += '</div>';

                html += '</div>';

                html += '<div class="row mt-2">';
                html +=
                    '<div class="col-md-4"><label>Expiry Date</label><input type="date" name="expiry_date" class="form-control" value="' +
                    escapeHtml(row.expiry_date || '') + '"></div>';
                html +=
                    '<div class="col-md-4"><label><span style="color:red">*</span> Received Qty</label><input type="number" step="0.01" min="0.01" name="received_qty" class="form-control" value="' +
                    escapeHtml(row.received_qty || '') + '" required></div>';
                html +=
                    '<div class="col-md-4"><label><span style="color:red">*</span> Damaged Qty</label><input type="number" step="0.01" min="0" name="damaged_qty" class="form-control" value="' +
                    escapeHtml(row.damaged_qty || 0) + '" required></div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html +=
                    '<div class="col-md-4"><label><span style="color:red">*</span> Purchase Price</label><input type="number" step="0.01" min="0" name="purchase_price" class="form-control" value="' +
                    escapeHtml(row.purchase_price || '') + '" required></div>';
                html +=
                    '<div class="col-md-4"><label>Supplier Name</label><input type="text" name="supplier_name" class="form-control" value="' +
                    escapeHtml(row.supplier_name || '') + '"></div>';
                html +=
                    '<div class="col-md-4"><label>Invoice No</label><input type="text" name="invoice_no" class="form-control" value="' +
                    escapeHtml(row.invoice_no || '') + '"></div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html +=
                    '<div class="col-md-6"><label>Reference No</label><input type="text" name="reference_no" class="form-control" value="' +
                    escapeHtml(row.reference_no || '') + '"></div>';
                html +=
                    '<div class="col-md-6"><label>Remarks</label><textarea name="remarks" class="form-control">' +
                    escapeHtml(row.remarks || '') + '</textarea></div>';
                html += '</div>';

                $('#receiveEditBody').html(html);
                $('#receiveEditForm').attr('action', "{{ url('/admin/store/general-supply/received') }}/" +
                    encodeURIComponent(row.id));
                $('#receiveEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#receiveEditModal');

                    $.get("{{ url('/admin/reqsts/general-supply/ajax/descriptions') }}/" + row.item_id,
                        function(res) {
                            let descHtml = '<option value="">-- Select Description --</option>';
                            res.forEach(function(dRow) {
                                let selected = String(dRow.id) === String(row
                                    .item_description_id) ? ' selected' : '';
                                descHtml +=
                                    `<option value="${dRow.id}" ${selected}>${dRow.description_name} (${dRow.unit_name})</option>`;
                            });
                            $('#edit_rec_description_id').html(descHtml).trigger('change');
                        });

                    $(document).off('change', '#edit_rec_item_id').on('change', '#edit_rec_item_id',
                        function() {
                            let itemId = $(this).val();
                            $('#edit_rec_description_id').html(
                                '<option value="">Loading...</option>');

                            if (!itemId) {
                                $('#edit_rec_description_id').html(
                                        '<option value="">-- Select Description --</option>')
                                    .trigger('change');
                                return;
                            }

                            $.get("{{ url('/admin/reqsts/general-supply/ajax/descriptions') }}/" +
                                itemId,
                                function(res) {
                                    let descHtml =
                                        '<option value="">-- Select Description --</option>';
                                    res.forEach(function(dRow) {
                                        descHtml +=
                                            `<option value="${dRow.id}">${dRow.description_name} (${dRow.unit_name})</option>`;
                                    });
                                    $('#edit_rec_description_id').html(descHtml).trigger(
                                        'change');
                                });
                        });
                }, 200);
            };
        });
    </script>
@endsection
