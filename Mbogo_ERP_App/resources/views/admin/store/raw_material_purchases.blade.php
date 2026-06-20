@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>General Supply Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Raw Material Purchases</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php
                        use Carbon\Carbon;
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
                                <td id="Hour4" style="color:green;font-size:large;"></td>
                                <td id="Minut4" style="color:green;font-size:large;"></td>
                                <td id="Second4" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg4() {
            setInterval(change_time4, 1000);
        }

        function change_time4() {
            var d = new Date();
            document.getElementById('Hour4').innerHTML = d.getHours() + ':';
            document.getElementById('Minut4').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second4').innerHTML = d.getSeconds();
        }
        timedMsg4();
    </script>

    @php
        $vendorOptions = [];
        foreach ($vendors as $v) {
            $vendorOptions[] = [
                'id' => $v->id,
                'name' => $v->vendor_name,
            ];
        }

        $materialOptions = [];
        foreach ($materials as $m) {
            $materialOptions[] = [
                'id' => $m->id,
                'name' => $m->material_name,
            ];
        }

        $workPointOptions = [];
        foreach ($workPoints as $w) {
            $workPointOptions[] = [
                'id' => $w->id,
                'name' => trim(
                    ($w->work_code ?? '') .
                        (isset($w->work_code) && $w->work_code ? ' - ' : '') .
                        ($w->work_name ?? ''),
                ),
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Raw Material Purchases</h3>
        @can('Register-Raw-Materials-Purchase')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openRmPurchaseCreateModal()">
                Add Purchase
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Purchases Table</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Work Point</th>
                                <th>Vendor</th>
                                <th>Material</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Invoice No</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $k => $row)
                                @php
                                    $payload = [
                                        'id' => encrypt($row->id),
                                        'vendor_id' => $row->vendor_id,
                                        'raw_material_id' => $row->raw_material_id,
                                        'work_point_id' => $row->work_point_id,
                                        'purchase_date' => $row->purchase_date,
                                        'qty' => $row->qty,
                                        'unit_price' => $row->unit_price,
                                        'invoice_no' => $row->invoice_no,
                                        'remarks' => $row->remarks,
                                        'status' => $row->status,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->purchase_date }}</td>
                                    <td>{{ optional($row->workpoint)->work_code }}{{ optional($row->workpoint)->work_code ? ' - ' : '' }}{{ optional($row->workpoint)->work_name }}
                                    </td>
                                    <td>{{ optional($row->vendor)->vendor_name ?? '-' }}</td>
                                    <td>{{ optional($row->material)->material_name ?? '-' }}</td>
                                    <td>{{ number_format((float) $row->qty, 2) }}</td>
                                    <td>{{ number_format((float) $row->unit_price, 2) }}</td>
                                    <td>{{ number_format((float) $row->total_price, 2) }}</td>
                                    <td>{{ $row->invoice_no ?? '-' }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td style="white-space: nowrap;">
                                        @can('Edit-Raw-Materials-Purchase')
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='openRmPurchaseEdit(@json($payload))'>
                                                Edit
                                            </button>
                                        @endcan
                                        @can('Delete-Raw-Materials-Purchase')
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick='deleteRmPurchase(@json($payload))'>
                                                Remove
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="rmPurchaseCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="rmPurchaseCreateForm" action="{{ route('sales.rm.purchase.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add Raw Material Purchase</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control select2_modal">
                                    <option value="">-- Select vendor --</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->vendor_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Work Point</label>
                                <select name="work_point_id" class="form-control select2_modal">
                                    <option value="">-- Select work point --</option>
                                    @foreach ($workPoints as $w)
                                        <option value="{{ $w->id }}">
                                            {{ $w->work_code }}{{ $w->work_code ? ' - ' : '' }}{{ $w->work_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Material <span class="text-danger">*</span></label>
                                <select name="raw_material_id" class="form-control select2_modal" required>
                                    <option value="">-- Select material --</option>
                                    @foreach ($materials as $m)
                                        <option value="{{ $m->id }}">{{ $m->material_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Qty <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="qty" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label>Unit Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="unit_price" class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select2_modal" required>
                                    <option value="">-- Select --</option>
                                    <option value="Purchased">Purchased</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="rmPurchaseEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="rmPurchaseEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Purchase</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="rmPurchaseEditBody">
                        <div class="text-center">Loading...</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var vendorOptions = @json($vendorOptions);
            var materialOptions = @json($materialOptions);
            var workPointOptions = @json($workPointOptions);

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

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            $('.select2_modal').each(function() {
                var $this = $(this);
                if ($this.closest('#rmPurchaseCreateModal').length) {
                    initSelect2WithParent($this, '#rmPurchaseCreateModal');
                    return;
                }
                if ($this.closest('#rmPurchaseEditModal').length) {
                    initSelect2WithParent($this, '#rmPurchaseEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#rmPurchaseCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#rmPurchaseCreateModal');
            });

            $('#rmPurchaseEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#rmPurchaseEditModal');
            });

            window.openRmPurchaseCreateModal = function() {
                var form = document.getElementById('rmPurchaseCreateForm');
                if (form) form.reset();

                $('#rmPurchaseCreateModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#rmPurchaseCreateModal');
                    $('#rmPurchaseCreateModal').find('select[name="vendor_id"]').val('').trigger(
                        'change');
                    $('#rmPurchaseCreateModal').find('select[name="work_point_id"]').val('').trigger(
                        'change');
                    $('#rmPurchaseCreateModal').find('select[name="raw_material_id"]').val('').trigger(
                        'change');
                    $('#rmPurchaseCreateModal').find('select[name="status"]').val('').trigger('change');
                }, 150);
            };

            window.openRmPurchaseEdit = function(row) {
                var html = '';

                html += '<div class="row mt-2">';

                html += '<div class="col-md-4">';
                html += '<label>Vendor</label>';
                html += '<select name="vendor_id" class="form-control select2_modal">';
                html += '<option value="">-- Select vendor --</option>';
                for (var i = 0; i < vendorOptions.length; i++) {
                    html += '<option value="' + vendorOptions[i].id + '"' +
                        (String(row.vendor_id) === String(vendorOptions[i].id) ? ' selected' : '') +
                        '>' + escapeHtml(vendorOptions[i].name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Work Point</label>';
                html += '<select name="work_point_id" class="form-control select2_modal">';
                html += '<option value="">-- Select work point --</option>';
                for (var j = 0; j < workPointOptions.length; j++) {
                    html += '<option value="' + workPointOptions[j].id + '"' +
                        (String(row.work_point_id) === String(workPointOptions[j].id) ? ' selected' : '') +
                        '>' + escapeHtml(workPointOptions[j].name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Material <span class="text-danger">*</span></label>';
                html += '<select name="raw_material_id" class="form-control select2_modal" required>';
                html += '<option value="">-- Select material --</option>';
                for (var k = 0; k < materialOptions.length; k++) {
                    html += '<option value="' + materialOptions[k].id + '"' +
                        (String(row.raw_material_id) === String(materialOptions[k].id) ? ' selected' : '') +
                        '>' + escapeHtml(materialOptions[k].name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '</div>';

                html += '<div class="row mt-2">';

                html += '<div class="col-md-4">';
                html += '<label>Date <span class="text-danger">*</span></label>';
                html += '<input type="date" name="purchase_date" class="form-control" value="' + escapeHtml(row
                    .purchase_date || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Qty <span class="text-danger">*</span></label>';
                html += '<input type="number" step="0.01" min="0.01" name="qty" class="form-control" value="' +
                    escapeHtml(row.qty || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Unit Price <span class="text-danger">*</span></label>';
                html +=
                    '<input type="number" step="0.01" min="0" name="unit_price" class="form-control" value="' +
                    escapeHtml(row.unit_price || '') + '" required>';
                html += '</div>';

                html += '</div>';

                html += '<div class="row mt-2">';

                html += '<div class="col-md-4">';
                html += '<label>Invoice No</label>';
                html += '<input type="text" name="invoice_no" class="form-control" value="' + escapeHtml(row
                    .invoice_no || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Status <span class="text-danger">*</span></label>';
                html += '<select name="status" class="form-control select2_modal" required>';
                html += '<option value="">-- Select --</option>';
                html += '<option value="Purchased"' + (row.status === 'Purchased' ? ' selected' : '') +
                    '>Purchased</option>';
                html += '<option value="Pending"' + (row.status === 'Pending' ? ' selected' : '') +
                    '>Pending</option>';
                html += '<option value="Cancelled"' + (row.status === 'Cancelled' ? ' selected' : '') +
                    '>Cancelled</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Remarks</label>';
                html += '<textarea name="remarks" class="form-control">' + escapeHtml(row.remarks || '') +
                    '</textarea>';
                html += '</div>';

                html += '</div>';

                $('#rmPurchaseEditBody').html(html);
                $('#rmPurchaseEditForm').attr('action',
                    "{{ url('/admin/sales/raw-material-purchases') }}/" + encodeURIComponent(row.id)
                );
                $('#rmPurchaseEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#rmPurchaseEditModal');
                }, 200);
            };

            window.deleteRmPurchase = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This raw material purchase will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href =
                            "{{ url('/admin/sales/raw-material-purchases/remove') }}/" +
                            encodeURIComponent(row.id);
                    }
                });
            };
        });
    </script>
@endsection
