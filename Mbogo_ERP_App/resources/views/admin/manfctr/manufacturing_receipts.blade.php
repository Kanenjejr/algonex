@extends('layouts.ManftrMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Inventory And Manufacturing Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Manufacturing Receipts</strong></li>
            </ol>
        </div>
    </div>

    @php
        $issueOpt = [];
        foreach ($issueOptions as $x) {
            $issueOpt[] = [
                'id' => $x['id'],
                'name' =>
                    $x['issue_no'] .
                    ' - ' .
                    $x['material_name'] .
                    ' - Remaining: ' .
                    number_format((float) $x['remaining_qty'], 2),
                'remaining_qty' => $x['remaining_qty'],
                'remarks' => $x['remarks'],
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Manufacturing Receipts</h3>
        @can('Register-Manufacturing-Receipts')
            <button class="btn mb-2 btn-primary" style="position:absolute; top:4.5%; right:1.7%" type="button"
                onclick="openReceiptCreateModal()">
                Receive From Store
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-success">
                        <h5>Receipt Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Receipt No</th>
                                        <th>Date</th>
                                        <th>Raw Material</th>
                                        <th>Received Qty</th>
                                        <th>Unit</th>
                                        <th>No Bags</th>
                                        <th>Bag Size</th>
                                        <th>Request No</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($receipts as $k => $r)
                                        @php
                                            $payload = [
                                                'id' => encrypt($r->id),
                                                'receipt_date' => $r->receipt_date,
                                                'received_qty' => $r->received_qty,
                                                'unit_name' => $r->unit_name,
                                                'no_of_bags' => $r->no_of_bags,
                                                'bag_size' => $r->bag_size,
                                                'remarks' => $r->remarks,
                                                'material_name' => optional($r->rawMaterial)->material_name,
                                                'request_no' => optional($r->request)->request_no,
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $r->receipt_no }}</td>
                                            <td>{{ $r->receipt_date }}</td>
                                            <td>{{ optional($r->rawMaterial)->material_name }}</td>
                                            <td>{{ number_format((float) $r->received_qty, 2) }}</td>
                                            <td>{{ $r->unit_name ?? '-' }}</td>
                                            <td>{{ $r->no_of_bags ?? '-' }}</td>
                                            <td>{{ $r->bag_size ?? '-' }}</td>
                                            <td>{{ optional($r->request)->request_no ?? '-' }}</td>
                                            <td>{{ $r->status }}</td>
                                            <td style="white-space: nowrap;">
                                                @can('Edit-Manufacturing-Receipts')
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openReceiptEditModal(@json($payload))'>
                                                        Edit
                                                    </button>
                                                @endcan
                                                @can('Delete-Manufacturing-Receipts')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteReceipt(@json($payload))'>
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

                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Pending Store Issues To Receive</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Issue Ref</th>
                                        <th>Material</th>
                                        <th>Remaining To Receive</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($issueOptions as $k => $x)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $x['issue_no'] }}</td>
                                            <td>{{ $x['material_name'] }}</td>
                                            <td>{{ number_format((float) $x['remaining_qty'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No pending issued materials to receive
                                            </td>
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

    {{-- CREATE RECEIPT --}}
    <div class="modal fade" id="receiptCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="receiptCreateForm" action="{{ route('manfctr.receipts.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Receive Issued Material</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Receipt Date <span class="text-danger">*</span></label>
                                <input type="date" name="receipt_date" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label>Store Issue <span class="text-danger">*</span></label>
                                <select name="raw_material_issue_id" id="receipt_issue_id"
                                    class="form-control select2_modal" required>
                                    <option value="">-- Select issued material --</option>
                                    @foreach ($issueOptions as $x)
                                        <option value="{{ $x['id'] }}" data-remaining_qty="{{ $x['remaining_qty'] }}"
                                            data-remarks="{{ $x['remarks'] }}">
                                            {{ $x['issue_no'] }} - {{ $x['material_name'] }} - Remaining:
                                            {{ number_format((float) $x['remaining_qty'], 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Only issues not fully received appear here.</small>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Received Qty <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="received_qty" id="received_qty"
                                    class="form-control" required>
                                <small class="text-muted" id="remainingHelp"></small>
                            </div>
                            <div class="col-md-4">
                                <label>Unit</label>
                                <input type="text" name="unit_name" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>No Bags</label>
                                <input type="number" name="no_of_bags" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Bag Size</label>
                                <input type="number" step="0.01" name="bag_size" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label>Remarks</label>
                                <textarea name="remarks" id="receipt_remarks" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Receive</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT RECEIPT --}}
    <div class="modal fade" id="receiptEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="receiptEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Receipt</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="receiptEditBody">
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

            $('.select2_modal').each(function() {
                initSelect2WithParent($(this), '#receiptCreateModal');
            });

            $('#receiptCreateModal').on('shown.bs.modal', function() {
                initSelect2WithParent($('#receipt_issue_id'), '#receiptCreateModal');
            });

            window.openReceiptCreateModal = function() {
                $('#receiptCreateForm')[0].reset();
                $('#remainingHelp').text('');
                $('#receiptCreateModal').modal('show');
            };

            $('#receipt_issue_id').on('change', function() {
                var selected = $(this).find(':selected');
                var remaining = selected.data('remaining_qty');
                var remarks = selected.data('remarks');

                if (remaining !== undefined) {
                    $('#remainingHelp').text('Remaining to receive: ' + remaining);
                    $('#received_qty').attr('max', remaining);
                } else {
                    $('#remainingHelp').text('');
                    $('#received_qty').removeAttr('max');
                }

                if (remarks) {
                    $('#receipt_remarks').val(remarks);
                }
            });

            window.openReceiptEditModal = function(row) {
                var html = '';

                html += '<div class="alert alert-info">';
                html += '<strong>Material:</strong> ' + (row.material_name || '-') + '<br>';
                html += '<strong>Request No:</strong> ' + (row.request_no || '-');
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Receipt Date <span class="text-danger">*</span></label>';
                html += '<input type="date" name="receipt_date" class="form-control" value="' + (row
                    .receipt_date || '') + '" required>';
                html += '</div>';
                html += '<div class="col-md-4">';
                html += '<label>Received Qty <span class="text-danger">*</span></label>';
                html +=
                    '<input type="number" step="0.01" min="0.01" name="received_qty" class="form-control" value="' +
                    (row.received_qty || '') + '" required>';
                html += '</div>';
                html += '<div class="col-md-4">';
                html += '<label>Unit</label>';
                html += '<input type="text" name="unit_name" class="form-control" value="' + (row.unit_name ||
                    '') + '">';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>No Bags</label>';
                html += '<input type="number" name="no_of_bags" class="form-control" value="' + (row
                    .no_of_bags || '') + '">';
                html += '</div>';
                html += '<div class="col-md-4">';
                html += '<label>Bag Size</label>';
                html += '<input type="number" step="0.01" name="bag_size" class="form-control" value="' + (row
                    .bag_size || '') + '">';
                html += '</div>';
                html += '<div class="col-md-4">';
                html += '<label>Remarks</label>';
                html += '<textarea name="remarks" class="form-control">' + (row.remarks || '') + '</textarea>';
                html += '</div>';
                html += '</div>';

                $('#receiptEditBody').html(html);
                $('#receiptEditForm').attr('action', "{{ url('/admin/manfctr/raw-material-receipts') }}/" +
                    encodeURIComponent(row.id));
                $('#receiptEditModal').modal('show');
            };

            window.deleteReceipt = function(row) {
                Swal.fire({
                    title: 'Delete restricted',
                    text: 'Receipt delete is disabled for stock consistency.',
                    icon: 'warning'
                });
            };
        });
    </script>
@endsection
