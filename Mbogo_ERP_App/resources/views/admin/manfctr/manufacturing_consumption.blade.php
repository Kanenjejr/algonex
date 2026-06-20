@extends('layouts.ManftrMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Inventory And Manufacturing Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Raw Material Used</strong></li>
            </ol>
        </div>
    </div>

    @php
        $stockOptions = [];
        foreach ($stocks as $s) {
            $stockOptions[] = [
                'id' => $s->raw_material_id,
                'name' =>
                    trim(
                        (optional($s->rawMaterial)->material_code
                            ? optional($s->rawMaterial)->material_code . ' - '
                            : '') .
                            (optional($s->rawMaterial)->material_name ?? '-'),
                    ) .
                    ' | Balance: ' .
                    number_format((float) $s->balance, 2),
                'balance' => (float) $s->balance,
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Consumption</h3>
        @can('Register-Manufacturing-Consumption')
            <button class="btn mb-2 btn-primary" style="position:absolute; top:4.5%; right:1.7%" type="button"
                onclick="openConsumptionCreateModal()">
                Add Consumption
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-warning">
                        <h5>Consumption Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Raw Material</th>
                                        <th>Consumed Qty</th>
                                        <th>Unit</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($consumptions as $k => $c)
                                        @php
                                            $payload = [
                                                'id' => encrypt($c->id),
                                                'consumption_date' => $c->consumption_date,
                                                'consumed_qty' => $c->consumed_qty,
                                                'unit_name' => $c->unit_name,
                                                'remarks' => $c->remarks,
                                                'material_name' => optional($c->rawMaterial)->material_name,
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $c->consumption_date }}</td>
                                            <td>{{ optional($c->rawMaterial)->material_name }}</td>
                                            <td>{{ number_format((float) $c->consumed_qty, 2) }}</td>
                                            <td>{{ $c->unit_name ?? '-' }}</td>
                                            <td>{{ $c->remarks }}</td>
                                            <td>{{ $c->status }}</td>
                                            <td style="white-space: nowrap;">
                                                @can('Edit-Manufacturing-Consumption')
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openConsumptionEditModal(@json($payload))'>
                                                        Edit
                                                    </button>
                                                @endcan
                                                @can('Delete-Manufacturing-Consumption')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteConsumption(@json($payload))'>
                                                        Remove
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary" onclick="printReceipt('consumptionPrintArea')">
                                <i class="fa fa-print"></i> Print
                            </button>
                        </div>

                        <div id="consumptionPrintArea" style="display:none;">
                            <h3>Manufacturing Consumption</h3>
                            <table border="1" cellspacing="0" cellpadding="6" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Raw Material</th>
                                        <th>Consumed Qty</th>
                                        <th>Unit</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($consumptions as $k => $c)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $c->consumption_date }}</td>
                                            <td>{{ optional($c->rawMaterial)->material_name }}</td>
                                            <td>{{ number_format((float) $c->consumed_qty, 2) }}</td>
                                            <td>{{ $c->unit_name ?? '-' }}</td>
                                            <td>{{ $c->remarks }}</td>
                                            <td>{{ $c->status }}</td>
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

    {{-- CREATE --}}
    <div class="modal fade" id="consumptionCreateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="consumptionCreateForm" action="{{ route('manfctr.consumption.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add Consumption</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="consumption_date" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label>Raw Material <span class="text-danger">*</span></label>
                                <select name="raw_material_id" id="consumption_raw_id" class="form-control select2_modal"
                                    required>
                                    <option value="">-- Select --</option>
                                    @foreach ($stocks as $s)
                                        <option value="{{ $s->raw_material_id }}" data-balance="{{ $s->balance }}">
                                            {{ optional($s->rawMaterial)->material_code }}{{ optional($s->rawMaterial)->material_code ? ' - ' : '' }}{{ optional($s->rawMaterial)->material_name }}
                                            | Balance: {{ number_format((float) $s->balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="balanceHelp"></small>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Consumed Qty <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="consumed_qty" id="consumed_qty"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Unit</label>
                                <input type="text" name="unit_name" class="form-control">
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

    {{-- EDIT --}}
    <div class="modal fade" id="consumptionEditModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="consumptionEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Consumption</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="consumptionEditBody">
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
                initSelect2WithParent($(this), '#consumptionCreateModal');
            });

            $('#consumptionCreateModal').on('shown.bs.modal', function() {
                initSelect2WithParent($('#consumption_raw_id'), '#consumptionCreateModal');
            });

            window.openConsumptionCreateModal = function() {
                $('#consumptionCreateForm')[0].reset();
                $('#balanceHelp').text('');
                $('#consumptionCreateModal').modal('show');
            };

            $('#consumption_raw_id').on('change', function() {
                var selected = $(this).find(':selected');
                var balance = selected.data('balance');

                if (balance !== undefined) {
                    $('#balanceHelp').text('Available balance: ' + balance);
                    $('#consumed_qty').attr('max', balance);
                } else {
                    $('#balanceHelp').text('');
                    $('#consumed_qty').removeAttr('max');
                }
            });

            window.openConsumptionEditModal = function(row) {
                var html = '';

                html += '<div class="alert alert-info">';
                html += '<strong>Material:</strong> ' + (row.material_name || '-');
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Date <span class="text-danger">*</span></label>';
                html += '<input type="date" name="consumption_date" class="form-control" value="' + (row
                    .consumption_date || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Consumed Qty <span class="text-danger">*</span></label>';
                html +=
                    '<input type="number" step="0.01" min="0.01" name="consumed_qty" class="form-control" value="' +
                    (row.consumed_qty || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Unit</label>';
                html += '<input type="text" name="unit_name" class="form-control" value="' + (row.unit_name ||
                    '') + '">';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-12">';
                html += '<label>Remarks</label>';
                html += '<textarea name="remarks" class="form-control">' + (row.remarks || '') + '</textarea>';
                html += '</div>';
                html += '</div>';

                $('#consumptionEditBody').html(html);
                $('#consumptionEditForm').attr('action',
                    "{{ url('/admin/manfctr/raw-material-consumption') }}/" + encodeURIComponent(row.id));
                $('#consumptionEditModal').modal('show');
            };

            window.deleteConsumption = function(row) {
                Swal.fire({
                    title: 'Delete restricted',
                    text: 'Consumption delete is disabled for stock consistency.',
                    icon: 'warning'
                });
            };
        });

        function printReceipt(ele) {
            var content = document.getElementById(ele);
            if (!content) return alert('Nothing to print');

            var pri = window.open('', '_blank', 'height=842,width=595');
            var doc = pri.document.open();

            var style = `<style>
        @page{ size:A4 portrait; margin:12mm; }
        html, body{ font-family:Arial,sans-serif; font-size:12px; color:#000; }
        table{ width:100%; border-collapse:collapse; }
        th, td{ border:1px solid #000; padding:6px; }
    </style>`;

            doc.write('<html><head><title>Manufacturing Consumption</title>' + style + '</head><body>');
            doc.write(content.innerHTML);
            doc.write('</body></html>');
            doc.close();

            pri.focus();
            setTimeout(function() {
                pri.print();
            }, 400);
        }
    </script>
@endsection
