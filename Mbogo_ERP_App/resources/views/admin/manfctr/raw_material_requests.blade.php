@extends('layouts.ManftrMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Inventory And Manufacturing Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Request Raw Material</strong></li>
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
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg() {
            setInterval(change_time, 1000);
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
        $rawOptions = [];
        foreach ($raws as $raw) {
            $rawOptions[] = [
                'id' => $raw->id,
                'name' => trim(($raw->material_code ? $raw->material_code . ' - ' : '') . $raw->material_name),
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Raw Material Requests</h3>
        @can('Register-Manufacturing-Raw-Material-Request')
            <button class="btn mb-2 btn-primary" style="position:absolute; top:4.5%; right:1.7%" type="button"
                onclick="openRequestCreateModal()">
                Add Request
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Request Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Req No</th>
                                        <th>Date</th>
                                        <th>Raw Material</th>
                                        <th>Requested Qty</th>
                                        <th>Issued Qty</th>
                                        <th>Remaining Qty</th>
                                        <th>Unit</th>
                                        <th>No Bags</th>
                                        <th>Bag Size</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $k => $r)
                                        @php
                                            $payload = [
                                                'id' => encrypt($r->id),
                                                'request_date' => $r->request_date,
                                                'raw_material_id' => $r->raw_material_id,
                                                'requested_qty' => $r->requested_qty,
                                                'unit_name' => $r->unit_name,
                                                'no_of_bags' => $r->no_of_bags,
                                                'bag_size' => $r->bag_size,
                                                'remarks' => $r->remarks,
                                                'status' => $r->status,
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $r->request_no }}</td>
                                            <td>{{ $r->request_date }}</td>
                                            <td>{{ optional($r->rawMaterial)->material_name }}</td>
                                            <td>{{ number_format((float) $r->requested_qty, 2) }}</td>
                                            <td>{{ number_format((float) $r->issued_qty, 2) }}</td>
                                            <td>{{ number_format((float) $r->remaining_qty, 2) }}</td>
                                            <td>{{ $r->unit_name ?? '-' }}</td>
                                            <td>{{ $r->no_of_bags ?? '-' }}</td>
                                            <td>{{ $r->bag_size ?? '-' }}</td>
                                            <td>{{ $r->status }}</td>
                                            <td style="white-space: nowrap;">
                                                @if (in_array($r->status, ['Pending', 'Cancelled']))
                                                    @can('Edit-Manufacturing-Raw-Material-Request')
                                                        <button class="btn btn-sm btn-warning" type="button"
                                                            onclick='openRequestEditModal(@json($payload))'>
                                                            Edit
                                                        </button>
                                                    @endcan

                                                    @can('Delete-Manufacturing-Raw-Material-Request')
                                                        <button class="btn btn-sm btn-danger" type="button"
                                                            onclick='deleteRequest(@json($payload))'>
                                                            Remove
                                                        </button>
                                                    @endcan
                                                @else
                                                    <span class="badge badge-secondary">Locked</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="requestCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="requestCreateForm" action="{{ route('manfctr.requests.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add Request</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="request_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Raw Material <span class="text-danger">*</span></label>
                                <select name="raw_material_id" class="form-control select2_modal" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($raws as $raw)
                                        <option value="{{ $raw->id }}">
                                            {{ $raw->material_code }}{{ $raw->material_code ? ' - ' : '' }}{{ $raw->material_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Requested Qty <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="requested_qty" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Unit</label>
                                <input type="text" name="unit_name" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>No Bags</label>
                                <input type="number" name="no_of_bags" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Bag Size</label>
                                <input type="number" step="0.01" name="bag_size" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12">
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
    <div class="modal fade" id="requestEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="requestEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Request</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="requestEditBody">
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
            var rawOptions = @json($rawOptions);

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
                if ($this.closest('#requestCreateModal').length) {
                    initSelect2WithParent($this, '#requestCreateModal');
                    return;
                }
                if ($this.closest('#requestEditModal').length) {
                    initSelect2WithParent($this, '#requestEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#requestCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#requestCreateModal');
            });

            $('#requestEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#requestEditModal');
            });

            window.openRequestCreateModal = function() {
                $('#requestCreateForm')[0].reset();
                $('#requestCreateModal').modal('show');
            };

            window.openRequestEditModal = function(row) {
                var html = '';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Date <span class="text-danger">*</span></label>';
                html += '<input type="date" name="request_date" class="form-control" value="' + escapeHtml(row
                    .request_date || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Raw Material <span class="text-danger">*</span></label>';
                html += '<select name="raw_material_id" class="form-control select2_modal" required>';
                html += '<option value="">-- Select --</option>';
                for (var i = 0; i < rawOptions.length; i++) {
                    html += '<option value="' + rawOptions[i].id + '"' +
                        (String(row.raw_material_id) === String(rawOptions[i].id) ? ' selected' : '') +
                        '>' + escapeHtml(rawOptions[i].name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Requested Qty <span class="text-danger">*</span></label>';
                html += '<input type="number" step="0.01" name="requested_qty" class="form-control" value="' +
                    escapeHtml(row.requested_qty || '') + '" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Unit</label>';
                html += '<input type="text" name="unit_name" class="form-control" value="' + escapeHtml(row
                    .unit_name || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>No Bags</label>';
                html += '<input type="number" name="no_of_bags" class="form-control" value="' + escapeHtml(row
                    .no_of_bags || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Bag Size</label>';
                html += '<input type="number" step="0.01" name="bag_size" class="form-control" value="' +
                    escapeHtml(row.bag_size || '') + '">';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-12">';
                html += '<label>Remarks</label>';
                html += '<textarea name="remarks" class="form-control">' + escapeHtml(row.remarks || '') +
                    '</textarea>';
                html += '</div>';
                html += '</div>';

                $('#requestEditBody').html(html);
                $('#requestEditForm').attr('action', "{{ url('/admin/manfctr/raw-material-requests') }}/" +
                    encodeURIComponent(row.id));
                $('#requestEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#requestEditModal');
                }, 200);
            };

            window.deleteRequest = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This request will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href =
                            "{{ url('/admin/manfctr/raw-material-requests/remove') }}/" +
                            encodeURIComponent(row.id);
                    }
                });
            };
        });
    </script>
@endsection
