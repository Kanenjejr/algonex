@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Customers, Supplies & Interactions Dashboard</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Customer / Supplier Order Items</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php use Carbon\Carbon;
                        $carbon = Carbon::now();
                        $carbon1 = Carbon::now()->toDateString();
                        echo $carbon->format('l');
                        echo ' , ';
                        echo $carbon1;
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
        <h3 class="mb-2 page-title">Order Items</h3>
        @can('Register-Customer-OrderItems')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#itemCreateModal">Add Item</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Order Items Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Product Details</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $k => $it)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ optional($it->order)->order_no ?? '-' }}</td>
                                            <td>{{ optional($it->customer)->customer_name ?? '-' }}</td>
                                            <td>{{ optional($it->product)->product_name ?? '-' }}</td>
                                            <td>{{ $it->product_name }}</td>
                                            <td>{{ $it->quantity }}</td>
                                            <td>{{ $it->unit }}</td>
                                            <td>{{ number_format($it->unit_price, 2) }}</td>
                                            <td>{{ number_format($it->total_price, 2) }}</td>
                                            <td>
                                                @can('Edit-Customer-OrderItems')
                                                    <button class="btn btn-sm btn-warning btn-edit-item"
                                                        data-id="{{ encrypt($it->id) }}" data-order_id="{{ $it->order_id }}"
                                                        data-cstm_id="{{ $it->cstm_id }}"
                                                        data-product_id="{{ $it->product_id }}"
                                                        data-product_name="{{ $it->product_name }}"
                                                        data-quantity="{{ $it->quantity }}" data-unit="{{ $it->unit }}"
                                                        data-unit_price="{{ $it->unit_price }}">Edit</button>
                                                @endcan
                                                @can('Delete-Customer-OrderItems')
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-item"
                                                        data-id="{{ encrypt($it->id) }}">Remove</a>
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

    {{-- Create Item Modal --}}
    <div class="modal fade" id="itemCreateModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="itemCreateForm" action="{{ route('crm.items.store') }}" method="POST">@csrf
                <div class="modal-content modal-xl">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Order Item</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Order</label>
                            <select name="order_id" class="form-control select2_item">
                                <option value="">-- Select Order --</option>
                                @foreach ($orders as $o)
                                    <option value="{{ $o->id }}">{{ $o->order_no }} -
                                        {{ optional($o->customer)->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6"><label>Product Name</label>
                                <select name="product_id" class="form-control select2_item">
                                    <option value="">-- Select Product --</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6"><label>Product Description</label><input type="text"
                                    name="product_name" class="form-control"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4"><label>Quantity</label><input type="number" step="0.01"
                                    name="quantity" class="form-control" required></div>
                            <div class="form-group col-md-4"><label>Unit</label><input type="text" name="unit"
                                    class="form-control"></div>
                            <div class="form-group col-md-4"><label>Unit Price</label><input type="number" step="0.01"
                                    name="unit_price" class="form-control"></div>
                        </div>

                        <div class="form-group"><label>Customer (optional)</label>
                            <select name="cstm_id" class="form-control select2_item">
                                <option value="">-- Select --</option>
                                @foreach ($cstmSplies as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('itemCreateForm')"
                            type="submit" class="btn btn-primary">Add Item</button></div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Item Modal --}}
    <div class="modal fade" id="itemEditModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="itemEditForm" method="POST">@csrf @method('PUT') <div class="modal-content modal-xl">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Item</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input id="edit_item_id" type="hidden" name="edit_id">
                        <div class="form-group"><label>Order</label>
                            <select id="edit_order_id" name="order_id" class="form-control select2_item">
                                <option value="">-- Select Order --</option>
                                @foreach ($orders as $o)
                                    <option value="{{ $o->id }}">{{ $o->order_no }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6"><label>Product Name</label>
                                <select id="edit_product_id" name="product_id" class="form-control select2_item">
                                    <option value="">-- Select Product --</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6"><label> Product Description<< /label><input
                                            id="edit_product_name" type="text" name="product_name"
                                            class="form-control"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4"><label>Quantity</label><input id="edit_quantity"
                                    type="number" step="0.01" name="quantity" class="form-control" required></div>
                            <div class="form-group col-md-4"><label>Unit</label><input id="edit_unit" type="text"
                                    name="unit" class="form-control"></div>
                            <div class="form-group col-md-4"><label>Unit Price</label><input id="edit_unit_price"
                                    type="number" step="0.01" name="unit_price" class="form-control"></div>
                        </div>

                        <div class="form-group"><label>Customer (optional)</label>
                            <select id="edit_cstm_id" name="cstm_id" class="form-control select2_item">
                                <option value="">-- Select --</option>
                                @foreach ($cstmSplies as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('itemEditForm')"
                            type="submit" class="btn btn-primary">Update Item</button></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tempItemEdit = null;

            function initSelect2WithParent($el, parent) {
                if (!$el || !$el.length) return;
                if ($el.data('select2')) try {
                    $el.select2('destroy');
                } catch (e) {}
                var $parent = parent && $(parent).length ? $(parent) : $(document.body);
                $el.select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownParent: $parent
                });
            }

            $('.select2_item').each(function() {
                var $this = $(this);
                if ($this.closest('#itemCreateModal').length) {
                    initSelect2WithParent($this, '#itemCreateModal');
                    return;
                }
                if ($this.closest('#itemEditModal').length) {
                    initSelect2WithParent($this, '#itemEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $(document).on('shown.bs.modal', '#itemCreateModal', function() {
                $(this).find('.select2_item').each(function() {
                    initSelect2WithParent($(this), '#itemCreateModal');
                    $(this).val(null).trigger('change');
                });
            });

            $(document).on('shown.bs.modal', '#itemEditModal', function() {
                $(this).find('.select2_item').each(function() {
                    initSelect2WithParent($(this), '#itemEditModal');
                });
                if (tempItemEdit) {
                    $('#edit_order_id').val(tempItemEdit.order_id || '').trigger('change');
                    $('#edit_product_id').val(tempItemEdit.product_id || '').trigger('change');
                    $('#edit_cstm_id').val(tempItemEdit.cstm_id || '').trigger('change');
                    tempItemEdit = null;
                }
            });

            document.querySelectorAll('.btn-edit-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    document.getElementById('edit_item_id').value = enc;
                    document.getElementById('edit_product_name').value = this.dataset
                        .product_name || '';
                    document.getElementById('edit_quantity').value = this.dataset.quantity || 0;
                    document.getElementById('edit_unit').value = this.dataset.unit || '';
                    document.getElementById('edit_unit_price').value = this.dataset.unit_price || 0;

                    tempItemEdit = {
                        order_id: this.dataset.order_id || null,
                        product_id: this.dataset.product_id || null,
                        cstm_id: this.dataset.cstm_id || null
                    };

                    var form = document.getElementById('itemEditForm');
                    form.action = "{{ route('crm.items.update', ':id') }}".replace(':id', enc);

                    $('#itemEditModal').modal('show');
                });
            });

            document.querySelectorAll('.btn-delete-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Remove this item?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then(res => {
                        if (res.isConfirmed) window.location.href =
                            "{{ route('crm.items.remove', ':id') }}".replace(':id', enc);
                    });
                });
            });

        });
    </script>
@endsection
