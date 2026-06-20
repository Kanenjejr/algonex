@extends('layouts.ReqstMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Requisition & Approvals Dashboard</h2>
        <ol class="breadcrumb" style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('requisition') }}">Requisition & Approvals</a>
            </li>
            <span style="font-size:25px" class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Product Request</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <h2>Current Date</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">
                <strong>
                    <?php use Carbon\Carbon;
              $carbon=Carbon::now();
              $carbon1=Carbon::now()->toDateString();
              echo $carbon->format('l'); echo" , ";echo $carbon1;
            ?>
                </strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-1">
        <h2>Time</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">
                <strong>
                    <table>
                        <tr>
                            <td id="Hour" style="color:green;font-size:large;"></td>
                            <td id="Minut" style="color:green;font-size:large;"></td>
                            <td id="Second" style="color:red;font-size:large;"></td>
                        <tr>
                    </table>
                </strong>
            </li>
        </ol>
    </div>
</div>
<script type="text/javascript">
    function timedMsg() {
        var t = setInterval("change_time();", 1000);
    }

    function change_time() {
        var d = new Date();
        var curr_hour = d.getHours();
        var curr_min = d.getMinutes();
        var curr_sec = d.getSeconds();
        if (curr_hour > 24)
            curr_hour = curr_hour - 24;
        document.getElementById('Hour').innerHTML = curr_hour + ':';
        document.getElementById('Minut').innerHTML = curr_min + ':';
        document.getElementById('Second').innerHTML = curr_sec;
    }
    timedMsg();

</script>

<div class="col-12">
    <h3 class="mb-2 page-title">Product Requests (Requisitions)</h3>

    @can('Register-PrdRequest')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#prdReqCreateModal">Create Request</button>
    @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title bg-success">
                    <h5>Requests Table</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Request No</th>
                                    <th>Request Date</th>
                                    <th>Work Point</th>
                                    <th>Requested By</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $k => $rq)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $rq->RequestNo }}</td>
                                    <td>{{ optional($rq->RequestDate)->format('Y-m-d') ?? '-' }}</td>
                                    <td>{{ optional($rq->workpoint)->work_name ?? '-' }}</td>
                                    <td>{{ optional($rq->user)->name ?? '-' }}</td>
                                    <td>{{ number_format($rq->total_amount, 2) }}</td>
                                    <td>{{ $rq->Status }}</td>
                                    <td>
                                        @can('View-PrdRequest')
                                        <a href="{{ route('prdrequest.show', encrypt($rq->id)) }}" class="btn btn-sm btn-info">View</a>
                                        @endcan

                                        @can('Edit-PrdRequest')
                                        @if($rq->Status === 'Pending')
                                        <button class="btn btn-sm btn-warning btn-edit-req" data-id="{{ encrypt($rq->id) }}">Edit</button>
                                        @endif
                                        @endcan

                                        @can('Delete-PrdRequest')
                                        @if($rq->Status === 'Pending')
                                        <a href="{{ route('prdrequest.remove', encrypt($rq->id)) }}" class="btn btn-sm btn-danger">Remove</a>
                                        @endif
                                        @endcan

                                        @can('Approve-PrdRequest')
                                        @if($rq->Status === 'Pending')
                                        <form style="display:inline-block" method="POST" action="{{ route('prdrequest.approve', encrypt($rq->id)) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this request?')">Approve</button>
                                        </form>
                                        <button class="btn btn-sm btn-secondary btn-reject-req" data-id="{{ encrypt($rq->id) }}">Reject</button>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> <!-- /.table-responsive -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="prdReqCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog mw-100 w-75" role="document">
        <form id="prdReqCreateForm" action="{{ route('prdrequest.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Product Request</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Work Point <span class="text-danger">*</span></label>
                            <select id="create_work_point_id" name="work_point_id" class="form-control select2_demo_3" required>
                                <option value="">-- Select work point --</option>
                                @foreach($workPoints as $wp)
                                <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Request Date <span class="text-danger">*</span></label>
                            <input type="date" name="RequestDate" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                    @else
                    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                    <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
                    <div class="form-group">
                        <label>Request Date <span class="text-danger">*</span></label>
                        <input type="date" name="RequestDate" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    @endif

                    <div class="form-group">
                        <label>Products</label>
                        <table class="table table-sm" id="pr-items-table">
                            <thead>
                                <tr>
                                    <th style="width:46%">Product</th>
                                    <th style="width:15%">Price</th>
                                    <th style="width:15%">Qty</th>
                                    <th style="width:15%">Subtotal</th>
                                    <th style="width:9%"><button type="button" class="btn btn-sm btn-primary" id="add-pr-row">+</button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="pr-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-control select2_demo_3 pr-product" required>
                                            <option value="">-- Select product --</option>
                                            @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->product_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control pr-unit-price" readonly></td>
                                    <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control pr-qty" value="1" required></td>
                                    <td><input type="number" step="0.01" name="items[0][subtotal]" class="form-control pr-subtotal" readonly></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-pr-row">x</button></td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="text-align:right; margin-top:10px;">
                            <strong>Total: </strong> <span id="pr-total">0.00</span>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn mb-2 btn-primary">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit modal similar to previous pattern (populated by AJAX) --}}
<div class="modal fade" id="prdReqEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog mw-100 w-75" role="document">
        <form id="prdReqEditForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product Request</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_prdreq_id" name="edit_id">
                    <div id="edit-modal-body-content">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn mb-2 btn-primary">Update Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ======== Prepare JS arrays from server-side collections ========
    const PRODUCTS = @json($products->map(function($p){ return ['id'=>$p->id,'name'=>$p->product_name]; }));
    const WORKPOINTS = @json($workPoints->map(function($w){ return ['id'=>$w->id,'name'=>$w->work_name]; }));

    // Template generator helpers
    function buildProductOptions(selectedId) {
        let html = '<option value="">-- Select product --</option>';
        for (let p of PRODUCTS) {
            html += '<option value="'+p.id+'"'+(selectedId && selectedId == p.id ? ' selected' : '')+'>'+escapeHtml(p.name)+'</option>';
        }
        return html;
    }
    function buildWorkPointOptions(selectedId) {
        let html = '<option value="">-- Select work point --</option>';
        for (let w of WORKPOINTS) {
            html += '<option value="'+w.id+'"'+(selectedId && selectedId == w.id ? ' selected' : '')+'>'+escapeHtml(w.name)+'</option>';
        }
        return html;
    }
    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        return String(text).replace(/[&<>"'`=\/]/g, function (s) {
            return ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
            })[s];
        });
    }

    // ======== Select2 helpers (safe init/destroy, dropdown parent) ========
    function initSelect2El($el, $parent) {
        if (!$el || !$el.length) return;
        if ($el.data('select2')) {
            try { $el.select2('destroy'); } catch(e) { /* ignore */ }
        }
        var parent = ($parent && $parent.length) ? $parent : $(document.body);
        $el.select2({ width: '100%', theme: 'bootstrap4', dropdownParent: parent });
    }

    // init page selects (outside modals)
    $('.select2_demo_3').each(function () {
        var $this = $(this);
        if ($this.closest('#prdReqCreateModal').length || $this.closest('#prdReqEditModal').length) {
            return; // skip modal selects - init on modal show
        }
        initSelect2El($this, $(document.body));
    });

    // ======== CREATE MODAL lifecycle ========
    $('#prdReqCreateModal').on('shown.bs.modal', function () {
        var $modal = $(this);
        $modal.find('.select2_demo_3').each(function () {
            initSelect2El($(this), $modal.find('.modal-content'));
        });
        // ensure first row product select is init
        $modal.find('#pr-items-table tbody tr.pr-row').each(function(){
            initSelect2El($(this).find('.pr-product'), $modal.find('.modal-content'));
        });
    });
    $('#prdReqCreateModal').on('hidden.bs.modal', function () {
        var $modal = $(this);
        $modal.find('.select2_demo_3').each(function () {
            if ($(this).data('select2')) {
                try { $(this).select2('destroy'); } catch(e){ }
            }
        });
    });

    // ======== CREATE: dynamic row creation using a clean template (no cloning of select2 data) ========
    let createRowIndex = 1;

    function createPrRow(index, productId = '', unit_price = '', qty = 1, subtotal = '') {
        var $tr = $(
            '<tr class="pr-row">' +
                '<td>' +
                    '<select name="items['+index+'][product_id]" class="form-control select2_demo_3 pr-product" required>' +
                        buildProductOptions(productId) +
                    '</select>' +
                '</td>' +
                '<td><input type="number" step="0.01" name="items['+index+'][unit_price]" class="form-control pr-unit-price" readonly value="'+(unit_price?Number(unit_price).toFixed(2):'')+'"></td>' +
                '<td><input type="number" step="0.01" name="items['+index+'][quantity]" class="form-control pr-qty" value="'+(qty||1)+'" required></td>' +
                '<td><input type="number" step="0.01" name="items['+index+'][subtotal]" class="form-control pr-subtotal" readonly value="'+(subtotal?Number(subtotal).toFixed(2):'')+'"></td>' +
                '<td><button type="button" class="btn btn-sm btn-danger remove-pr-row">x</button></td>' +
            '</tr>'
        );
        return $tr;
    }

    // Add row button
    $('#add-pr-row').on('click', function () {
        var $tbody = $('#pr-items-table tbody');
        var $row = createPrRow(createRowIndex, '', '', 1, '');
        $tbody.append($row);
        // init select2 on new row's select, parent is modal content
        initSelect2El($row.find('.select2_demo_3'), $('#prdReqCreateModal .modal-content'));
        createRowIndex++;
    });

    // Remove row handler (works for create modal)
    $(document).on('click', '.remove-pr-row', function () {
        var $tbody = $('#pr-items-table tbody');
        if ($tbody.find('tr').length <= 1) {
            // reset first row
            var $first = $tbody.find('tr:first');
            $first.find('select').val('').trigger('change');
            $first.find('.pr-unit-price, .pr-subtotal').val('');
            $first.find('.pr-qty').val(1);
        } else {
            $(this).closest('tr').remove();
        }
        recalcCreateTotal();
    });

    // ======== Create: recalc functions ========
    function recalcCreateRow($row) {
        var qty = parseFloat($row.find('.pr-qty').val() || 0);
        var price = parseFloat($row.find('.pr-unit-price').val() || 0);
        var subtotal = (qty * price) || 0;
        $row.find('.pr-subtotal').val(subtotal.toFixed(2));
        recalcCreateTotal();
    }
    function recalcCreateTotal() {
        var total = 0;
        $('#pr-items-table tbody tr').each(function () {
            total += parseFloat($(this).find('.pr-subtotal').val() || 0);
        });
        $('#pr-total').text(total.toFixed(2));
    }

    // product change inside create modal -> fetch price
    $(document).on('change', '#prdReqCreateModal .pr-product', function () {
        var $row = $(this).closest('tr');
        var productId = $(this).val();
        var work_point_id = $('#create_work_point_id').val() || '';
        if (!productId) {
            $row.find('.pr-unit-price').val('');
            $row.find('.pr-subtotal').val('');
            recalcCreateTotal();
            return;
        }
        var priceUrlTemplate = "{{ route('prdrequest.product.price', ':id') }}";
        var url = priceUrlTemplate.replace(':id', productId) + (work_point_id ? ('?work_point_id=' + work_point_id) : '');
        $.get(url)
            .done(function (resp) {
                var p = parseFloat(resp.price || 0);
                $row.find('.pr-unit-price').val(p.toFixed(2));
                recalcCreateRow($row);
            })
            .fail(function () {
                $row.find('.pr-unit-price').val('');
                $row.find('.pr-subtotal').val('');
                recalcCreateTotal();
                Swal.fire('Error', 'Price not found for selected product.', 'error');
            });
    });

    // qty input change inside create modal
    $(document).on('input', '#prdReqCreateModal .pr-qty', function () {
        recalcCreateRow($(this).closest('tr'));
    });

    // when work point changes in create modal, clear prices so user re-selects product price
    $(document).on('change', '#create_work_point_id', function () {
        $('#pr-items-table tbody tr').each(function () {
            $(this).find('.pr-unit-price').val('');
            $(this).find('.pr-subtotal').val('');
        });
        recalcCreateTotal();
    });

    // ======== EDIT modal: lifecycle & helpers ========
    $('#prdReqEditModal').on('shown.bs.modal', function () {
        var $modal = $(this);
        $modal.find('.select2_demo_3').each(function () {
            initSelect2El($(this), $modal.find('.modal-content'));
        });
    });
    $('#prdReqEditModal').on('hidden.bs.modal', function () {
        var $modal = $(this);
        $modal.find('.select2_demo_3').each(function () {
            if ($(this).data('select2')) {
                try { $(this).select2('destroy'); } catch(e) {}
            }
        });
        // remove delegated handlers to avoid duplicates (they are namespaced below)
        $(document).off('.editPrdHandlers');
    });

    // create an edit row (similar to create, but css classes differ)
    function createEditRow(idx, productId='', unit_price='', qty=1, subtotal='') {
        var $tr = $(
            '<tr class="edit-pr-row">' +
                '<td>' +
                    '<select name="items['+idx+'][product_id]" class="form-control select2_demo_3 edit-pr-product" required>' +
                        buildProductOptions(productId) +
                    '</select>' +
                '</td>' +
                '<td><input type="number" step="0.01" name="items['+idx+'][unit_price]" class="form-control edit-pr-unit-price" readonly value="'+(unit_price?Number(unit_price).toFixed(2):'')+'"></td>' +
                '<td><input type="number" step="0.01" name="items['+idx+'][quantity]" class="form-control edit-pr-qty" value="'+(qty||1)+'"></td>' +
                '<td><input type="number" step="0.01" name="items['+idx+'][subtotal]" class="form-control edit-pr-subtotal" readonly value="'+(subtotal?Number(subtotal).toFixed(2):'')+'"></td>' +
                '<td><button type="button" class="btn btn-sm btn-danger edit-remove-pr-row">x</button></td>' +
            '</tr>'
        );
        return $tr;
    }

    // When user clicks edit button - fetch header & items via AJAX and build the edit modal HTML
    document.querySelectorAll('.btn-edit-req').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var encId = this.dataset.id;
            var url = "{{ route('prdrequest.edit', ':id') }}".replace(':id', encId);

            $('#prdReqEditModal #edit-modal-body-content').html('<div class="text-center">Loading...</div>');
            $('#prdReqEditModal').modal('show');

            $.get(url).done(function (data) {
                var header = data.header || {};
                var items = data.items || [];

                // Build HTML for edit form body
                var html = '';

                // Work point - always show select (controller will enforce permissions)
                html += '<div class="form-row">';
                html += '<div class="form-group col-md-6"><label>Work Point</label>';
                html += '<select id="edit_work_point_id" name="work_point_id" class="form-control select2_demo_3">'+ buildWorkPointOptions(header.work_point_id) +'</select></div>';
                html += '<div class="form-group col-md-6"><label>Request Date</label>';
                html += '<input type="date" name="RequestDate" id="edit_RequestDate" class="form-control" value="'+(header.RequestDate ? header.RequestDate.substring(0,10) : '')+'"></div>';
                html += '</div>';

                html += '<table class="table table-sm" id="edit-pr-items-table"><thead><tr><th style="width:46%">Product</th><th style="width:15%">Price</th><th style="width:15%">Qty</th><thstyle="width:15%">Subtotal</thstyle=><th></th></tr></thead><tbody>';

                if (!items.length) {
                    html += createSingleEditRowHtml(0, '', '', 1, '');
                } else {
                    for (var i = 0; i < items.length; i++) {
                        var it = items[i];
                        html += createSingleEditRowHtml(i, it.Product_id || '', it.unit_price || '', it.quantity || 1, it.subtotal || '');
                    }
                }

                html += '</tbody></table>';
                html += '<div style="text-align:right; margin-top:10px;"><strong>Total: </strong> <span id="edit-pr-total">0.00</span></div>';

                $('#prdReqEditModal #edit-modal-body-content').html(html);

                // set edit form hidden id and action
                $('#edit_prdreq_id').val(encId);
                $('#prdReqEditForm').attr('action', "{{ route('prdrequest.update', ':id') }}".replace(':id', encId));

                // init select2 inside modal for all selects we just added
                $('#prdReqEditModal .select2_demo_3').each(function () {
                    initSelect2El($(this), $('#prdReqEditModal .modal-content'));
                });

                // After select2 init, set selected values (works because options already contain them)
                $('#prdReqEditModal').find('.edit-pr-product').each(function (idx) {
                    var val = items[idx] ? items[idx].Product_id : '';
                    if (val) $(this).val(val).trigger('change');
                });

                // compute total
                var t = 0;
                $('#prdReqEditModal .edit-pr-subtotal').each(function () {
                    t += parseFloat($(this).val() || 0);
                });
                $('#edit-pr-total').text(t.toFixed(2));

                // Now attach delegated handlers for edit modal (namespace them to remove later)
                $(document).on('change.editPrdHandlers', '#prdReqEditModal .edit-pr-product', function () {
                    var $row = $(this).closest('tr');
                    var productId = $(this).val();
                    var work_point_id = $('#edit_work_point_id').val() || '';
                    if (!productId) {
                        $row.find('.edit-pr-unit-price, .edit-pr-subtotal').val('');
                        recalcEditTotal();
                        return;
                    }
                    var priceUrlTemplate = "{{ route('prdrequest.product.price', ':id') }}";
                    var url = priceUrlTemplate.replace(':id', productId) + (work_point_id ? ('?work_point_id=' + work_point_id) : '');
                    $.get(url).done(function (resp) {
                        var p = parseFloat(resp.price || 0);
                        $row.find('.edit-pr-unit-price').val(p.toFixed(2));
                        var qty = parseFloat($row.find('.edit-pr-qty').val() || 0);
                        $row.find('.edit-pr-subtotal').val((qty * p).toFixed(2));
                        recalcEditTotal();
                    }).fail(function () {
                        Swal.fire('Error', 'Price not found for selected product.', 'error');
                    });
                });

                $(document).on('input.editPrdHandlers', '#prdReqEditModal .edit-pr-qty', function () {
                    var $row = $(this).closest('tr');
                    var qty = parseFloat($row.find('.edit-pr-qty').val() || 0);
                    var price = parseFloat($row.find('.edit-pr-unit-price').val() || 0);
                    $row.find('.edit-pr-subtotal').val((qty * price).toFixed(2));
                    recalcEditTotal();
                });

                $(document).on('click.editPrdHandlers', '#prdReqEditModal .edit-remove-pr-row', function () {
                    var $tbody = $('#edit-pr-items-table tbody');
                    if ($tbody.find('tr').length <= 1) {
                        var $r = $tbody.find('tr:first');
                        $r.find('select').val('').trigger('change');
                        $r.find('.edit-pr-unit-price, .edit-pr-subtotal').val('');
                        $r.find('.edit-pr-qty').val(1);
                    } else {
                        $(this).closest('tr').remove();
                    }
                    recalcEditTotal();
                });

                // add ability to append a new edit row via clicking last cell (not shown in original UI, but safe)
                if (!$('#prdReqEditModal #edit-pr-add-row').length) {
                    $('#prdReqEditModal #edit-pr-items-table').after('<div class="text-right mt-2"><button id="edit-pr-add-row" type="button" class="btn btn-sm btn-primary">+ Add row</button></div>');
                }
                $(document).off('click.editPrdHandlersAdd').on('click.editPrdHandlersAdd', '#edit-pr-add-row', function () {
                    var idx = $('#edit-pr-items-table tbody tr').length;
                    var $r = $(createSingleEditRowHtml(idx, '', '', 1, ''));
                    $('#edit-pr-items-table tbody').append($r);
                    initSelect2El($r.find('.select2_demo_3'), $('#prdReqEditModal .modal-content'));
                    recalcEditTotal();
                });

                // when edit work point changes, clear prices to force re-fetch
                $(document).off('change.editWorkPoint').on('change.editWorkPoint', '#edit_work_point_id', function(){
                    $('#prdReqEditModal .edit-pr-unit-price, #prdReqEditModal .edit-pr-subtotal').val('');
                    recalcEditTotal();
                });

            }).fail(function () {
                $('#prdReqEditModal #edit-modal-body-content').html('<div class="text-danger">Failed to load request.</div>');
            });
        });
    });

    // helper to create single edit-row HTML string (used while building edit modal body)
    function createSingleEditRowHtml(idx, productId, unit_price, qty, subtotal) {
        return '<tr class="edit-pr-row">' +
                '<td>' +
                    '<select name="items['+idx+'][product_id]" class="form-control select2_demo_3 edit-pr-product" required>' +
                        buildProductOptions(productId) +
                    '</select>' +
                '</td>' +
                '<td><input type="number" step="0.01" name="items['+idx+'][unit_price]" class="form-control edit-pr-unit-price" readonly value="'+(unit_price?Number(unit_price).toFixed(2):'')+'"></td>' +
                '<td><input type="number" step="0.01" name="items['+idx+'][quantity]" class="form-control edit-pr-qty" value="'+(qty||1)+'"></td>' +
                '<td><input type="number" step="0.01" name="items['+idx+'][subtotal]" class="form-control edit-pr-subtotal" readonly value="'+(subtotal?Number(subtotal).toFixed(2):'')+'"></td>' +
                '<td><button type="button" class="btn btn-sm btn-danger edit-remove-pr-row">x</button></td>' +
            '</tr>';
    }

    // recalc for edit modal
    function recalcEditTotal() {
        var total = 0;
        $('#prdReqEditModal .edit-pr-subtotal').each(function () {
            total += parseFloat($(this).val() || 0);
        });
        $('#edit-pr-total').text(total.toFixed(2));
    }

    // ======== Delete & Reject handlers (unchanged logic) ========
    document.querySelectorAll('.btn-delete-req').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var encId = this.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will mark the request as Deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('prdrequest.remove', ':id') }}".replace(':id', encId);
                }
            });
        });
    });

    document.querySelectorAll('.btn-reject-req').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var encId = this.dataset.id;
            Swal.fire({
                title: 'Reject request',
                input: 'textarea',
                inputPlaceholder: 'Optional remarks...',
                showCancelButton: true,
                confirmButtonText: 'Reject'
            }).then(function (result) {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="{{ route("prdrequest.reject", ":id") }}">@csrf</form>'.replace(':id', encId));
                    form.append('<input type="hidden" name="remarks" value="' + (result.value ? $('<div/>').text(result.value).html() : '') + '">');
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });

});
</script>
@endsection
