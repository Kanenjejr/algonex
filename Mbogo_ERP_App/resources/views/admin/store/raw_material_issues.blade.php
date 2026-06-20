@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Store Management Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Raw Material Issues</strong></li>
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
                                <td id="Hour6" style="color:green;font-size:large;"></td>
                                <td id="Minut6" style="color:green;font-size:large;"></td>
                                <td id="Second6" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg6() {
            setInterval("change_time6();", 1000);
        }

        function change_time6() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            if (curr_hour > 24) curr_hour = curr_hour - 24;
            document.getElementById('Hour6').innerHTML = curr_hour + ':';
            document.getElementById('Minut6').innerHTML = curr_min + ':';
            document.getElementById('Second6').innerHTML = curr_sec;
        }
        timedMsg6();
    </script>

    @php
        $requestOptions = [];
        foreach ($requests as $req) {
            $requestOptions[] = [
                'id' => $req->id,
                'request_no' => $req->request_no,
                'raw_material_id' => $req->raw_material_id,
                'raw_material_name' => optional($req->rawMaterial)->material_name,
                'requested_qty' => (float) $req->requested_qty,
                'issued_qty' => (float) $req->issued_qty,
                'remaining_qty' => (float) $req->remaining_qty,
                'unit_name' => $req->unit_name,
                'no_of_bags' => $req->no_of_bags,
                'bag_size' => $req->bag_size,
                'issue_to_work_point_id' => $req->work_point_id,
                'issue_to_name' => optional($req->workPoint)->work_name,
                'issue_to_type' => 'Production',
                'remarks' => $req->remarks,
            ];
        }

        $workPointOptions = [];
        foreach ($workPoints as $w) {
            $workPointOptions[] = [
                'id' => $w->id,
                'name' => trim(($w->work_code ? $w->work_code . ' - ' : '') . $w->work_name),
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Raw Material Issues</h3>
        @can('Register-Raw-Material-Issues')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openCreateModal()">
                Issue Raw Material
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        {{-- REQUESTS WAITING FOR ISSUE --}}
        <div class="ibox">
            <div class="ibox-title bg-warning">
                <h5>Manufacturing Requests Waiting For Issue</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request No</th>
                                <th>Date</th>
                                <th>Raw Material</th>
                                <th>Requested Qty</th>
                                <th>Issued Qty</th>
                                <th>Remaining Qty</th>
                                <th>Unit</th>
                                <th>Work Point</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $k => $req)
                                @php
                                    $requestPayload = [
                                        'id' => $req->id,
                                        'request_no' => $req->request_no,
                                        'raw_material_id' => $req->raw_material_id,
                                        'raw_material_name' => optional($req->rawMaterial)->material_name,
                                        'requested_qty' => $req->requested_qty,
                                        'issued_qty' => $req->issued_qty,
                                        'remaining_qty' => $req->remaining_qty,
                                        'unit_name' => $req->unit_name,
                                        'no_of_bags' => $req->no_of_bags,
                                        'bag_size' => $req->bag_size,
                                        'issue_to_work_point_id' => $req->work_point_id,
                                        'issue_to_name' => optional($req->workPoint)->work_name,
                                        'issue_to_type' => 'Production',
                                        'remarks' => $req->remarks,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $req->request_no }}</td>
                                    <td>{{ $req->request_date }}</td>
                                    <td>{{ optional($req->rawMaterial)->material_name ?? '-' }}</td>
                                    <td>{{ number_format((float) $req->requested_qty, 2) }}</td>
                                    <td>{{ number_format((float) $req->issued_qty, 2) }}</td>
                                    <td>{{ number_format((float) $req->remaining_qty, 2) }}</td>
                                    <td>{{ $req->unit_name ?? '-' }}</td>
                                    <td>
                                        {{ optional($req->workPoint)->work_code ?? '' }}
                                        {{ optional($req->workPoint)->work_code ? ' - ' : '' }}
                                        {{ optional($req->workPoint)->work_name ?? '-' }}
                                    </td>
                                    <td>{{ $req->status }}</td>
                                    <td>
                                        @can('Register-Raw-Material-Issues')
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick='openIssueFromRequest(@json($requestPayload))'>
                                                Issue
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No pending manufacturing requests</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- EXISTING ISSUES --}}
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Raw Material Issues Table</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Unit</th>
                                <th>Store Work Point</th>
                                <th>Material</th>
                                <th>Request No</th>
                                <th>Issue To Type</th>
                                <th>Issue To Name</th>
                                <th>Issued Qty</th>
                                <th>Remarks</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $k => $row)
                                @php
                                    $payload = [
                                        'id' => encrypt($row->id),
                                        'issue_to_type' => $row->issue_to_type,
                                        'issue_to_name' => $row->issue_to_name,
                                        'issue_date' => $row->issue_date,
                                        'issued_qty' => $row->issued_qty,
                                        'remarks' => $row->remarks,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->issue_date }}</td>
                                    <td>{{ optional($row->company)->company_name ?? '-' }}</td>
                                    <td>{{ optional($row->unit)->unit_name ?? '-' }}</td>
                                    <td>
                                        {{ optional($row->workpoint)->work_code ?? '' }}
                                        {{ optional($row->workpoint)->work_code ? ' - ' : '' }}
                                        {{ optional($row->workpoint)->work_name ?? '-' }}
                                    </td>
                                    <td>{{ optional($row->material)->material_name ?? '-' }}</td>
                                    <td>{{ optional($row->manufacturingRequest)->request_no ?? '-' }}</td>
                                    <td>{{ $row->issue_to_type }}</td>
                                    <td>{{ $row->issue_to_name ?? '-' }}</td>
                                    <td>{{ number_format($row->issued_qty, 2) }}</td>
                                    <td>{{ $row->remarks ?? '-' }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td>
                                        @can('Edit-Raw-Material-Issues')
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='openEditModal(@json($payload))'>Edit</button>
                                        @endcan
                                        @can('Delete-Raw-Material-Issues')
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick='removeRow(@json($payload))'>Remove</button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('sales.rm.issue.store') }}" method="POST" id="createForm">
                @csrf
                <input type="hidden" name="manufacturing_request_id" id="create_manufacturing_request_id">
                <input type="hidden" name="issue_to_work_point_id" id="create_issue_to_work_point_id">
                <input type="hidden" name="raw_material_id" id="create_raw_material_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Issue Raw Material</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Request No:</strong> <span id="create_request_no_text">-</span><br>
                            <strong>Material:</strong> <span id="create_material_text">-</span><br>
                            <strong>Remaining Qty:</strong> <span id="create_remaining_qty_text">0.00</span>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Store Work Point</label>
                                <select name="work_point_id" id="create_work_point_id" class="form-control select2_modal">
                                    <option value="">-- Select work point --</option>
                                    @foreach ($workPoints as $w)
                                        <option value="{{ $w->id }}">
                                            {{ $w->work_code }}{{ $w->work_code ? ' - ' : '' }}{{ $w->work_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Issued Qty <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="issued_qty" id="create_issued_qty"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Issue To Type <span class="text-danger">*</span></label>
                                <select name="issue_to_type" id="create_issue_to_type" class="form-control select2_modal"
                                    required>
                                    <option value="">-- Select --</option>
                                    <option value="Production">Production</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Issue To Name</label>
                                <input type="text" name="issue_to_name" id="create_issue_to_name"
                                    class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Remarks</label>
                                <textarea name="remarks" id="create_remarks" class="form-control"></textarea>
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
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Raw Material Issue</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Issue To Type <span class="text-danger">*</span></label>
                                <select name="issue_to_type" id="edit_issue_to_type" class="form-control select2_modal"
                                    required>
                                    <option value="">-- Select --</option>
                                    <option value="Production">Production</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Issue To Name</label>
                                <input type="text" name="issue_to_name" id="edit_issue_to_name" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" id="edit_issue_date" class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Issued Qty <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="issued_qty"
                                    id="edit_issued_qty" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label>Remarks</label>
                                <textarea name="remarks" id="edit_remarks" class="form-control"></textarea>
                            </div>
                        </div>
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
        function initSelect2WithParent($element, parentSelector = null) {
            if (!$element || !$element.length) return;

            if ($element.hasClass("select2-hidden-accessible")) {
                try {
                    $element.select2('destroy');
                } catch (e) {}
            }

            $element.select2({
                width: '100%',
                theme: 'bootstrap4',
                dropdownParent: parentSelector ? $(parentSelector) : $(document.body)
            });
        }

        function initAllModalSelects(modalSelector) {
            $(modalSelector).find('.select2_modal').each(function() {
                initSelect2WithParent($(this), modalSelector);
            });
        }

        $(document).ready(function() {
            $('.select2_modal').each(function() {
                var $this = $(this);
                if ($this.closest('#createModal').length) {
                    initSelect2WithParent($this, '#createModal');
                    return;
                }
                if ($this.closest('#editModal').length) {
                    initSelect2WithParent($this, '#editModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#createModal').on('shown.bs.modal', function() {
                initAllModalSelects('#createModal');
            });

            $('#editModal').on('shown.bs.modal', function() {
                initAllModalSelects('#editModal');
            });
        });

        function openCreateModal() {
            $('#createForm')[0].reset();
            $('#create_manufacturing_request_id').val('');
            $('#create_issue_to_work_point_id').val('');
            $('#create_raw_material_id').val('');
            $('#create_request_no_text').text('-');
            $('#create_material_text').text('-');
            $('#create_remaining_qty_text').text('0.00');
            $('#create_issue_to_type').val('').trigger('change');
            $('#create_work_point_id').val('').trigger('change');
            $('#createModal').modal('show');
        }

        function openIssueFromRequest(req) {
            $('#createForm')[0].reset();

            $('#create_manufacturing_request_id').val(req.id);
            $('#create_issue_to_work_point_id').val(req.issue_to_work_point_id);
            $('#create_raw_material_id').val(req.raw_material_id);

            $('#create_request_no_text').text(req.request_no || '-');
            $('#create_material_text').text(req.raw_material_name || '-');
            $('#create_remaining_qty_text').text(req.remaining_qty || '0.00');

            $('#create_issue_to_type').val(req.issue_to_type || 'Production').trigger('change');
            $('#create_issue_to_name').val(req.issue_to_name || '');
            $('#create_remarks').val(req.remarks || '');
            $('#create_issued_qty').attr('max', req.remaining_qty || '');

            $('#createModal').modal('show');

            setTimeout(function() {
                initAllModalSelects('#createModal');
            }, 150);
        }

        function openEditModal(row) {
            $('#edit_issue_to_type').val(row.issue_to_type).trigger('change');
            $('#edit_issue_to_name').val(row.issue_to_name);
            $('#edit_issue_date').val(row.issue_date);
            $('#edit_issued_qty').val(row.issued_qty);
            $('#edit_remarks').val(row.remarks);
            $('#editForm').attr('action', "{{ url('/admin/sales/raw-material-issues') }}/" + row.id);
            $('#editModal').modal('show');

            setTimeout(function() {
                initAllModalSelects('#editModal');
            }, 150);
        }

        function removeRow(row) {
            Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true
            }).then(function(res) {
                if (res.isConfirmed) {
                    window.location.href = "{{ url('/admin/sales/raw-material-issues/remove') }}/" + row.id;
                }
            });
        }
    </script>
@endsection
