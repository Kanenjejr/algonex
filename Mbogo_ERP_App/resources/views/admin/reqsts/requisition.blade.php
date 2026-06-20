@extends('layouts.ReqstMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Requisition & Approvals Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('requisition') }}">Requisition & Approvals</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>General Supply Requisition</strong></li>
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
            setInterval("change_time();", 1000);
        }

        function change_time() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            if (curr_hour > 24) curr_hour = curr_hour - 24;
            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }
        timedMsg();
    </script>

    @php
        $companyOptions = [];
        foreach ($companies as $company) {
            $companyOptions[] = [
                'id' => $company->id,
                'name' => trim(($company->company_code ?? '') . ' - ' . $company->company_name, ' -'),
            ];
        }

        $departmentOptions = [];
        foreach ($departments as $department) {
            $departmentOptions[] = [
                'id' => $department->id,
                'name' => trim(($department->depCode ?? '') . ' - ' . $department->depName, ' -'),
            ];
        }

        $itemOptions = [];
        foreach ($items as $item) {
            $itemOptions[] = [
                'id' => $item->id,
                'name' => $item->item_name,
            ];
        }

        $accountOptions = [];
        foreach ($accounts as $acc) {
            $accountOptions[] = [
                'id' => $acc->SubCode,
                'name' => trim($acc->SubCode . ' - ' . $acc->SubDescription, ' -'),
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">General Supply Requisition</h3>
        @can('Register-Requested-Items-Details')
            <button style="position:absolute; top:4.5%; right:1.7%;" class="btn mb-2 btn-primary" type="button"
                onclick="openRequestCreateModal()">
                New Request
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Requested Items Table</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request No</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Section</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Requested Qty</th>
                                <th>Issued Qty</th>
                                <th>Received Qty</th>
                                <th>Received Date</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Received Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $k => $row)
                                @php
                                    $payload = [
                                        'id' => encrypt($row->id),
                                        'company_id' => $row->company_id,
                                        'comp_unit_id' => $row->comp_unit_id,
                                        'work_point_id' => $row->work_point_id,
                                        'dept_id' => $row->dept_id,
                                        'section_id' => $row->section_id,
                                        'item_id' => $row->item_id,
                                        'item_description_id' => $row->item_description_id,
                                        'account_code' => $row->account_code ?? null,
                                        'request_date' => $row->request_date,
                                        'requested_qty' => $row->requested_qty,
                                        'reason' => $row->reason,
                                        'request_no' => $row->request_no,
                                        'issued_qty' => $row->issued_qty,
                                        'received_qty' => $row->received_qty,
                                        'received_date' => $row->received_date,
                                        'received_remarks' => $row->received_remarks,
                                        'status' => $row->status,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->request_no }}</td>
                                    <td>{{ $row->request_date }}</td>
                                    <td>{{ optional($row->workpoint)->work_code }} -
                                        {{ optional($row->workpoint)->work_name }}</td>
                                    <td>{{ optional($row->department)->depName ?? '-' }}</td>
                                    <td>{{ optional($row->section)->secName ?? '-' }}</td>
                                    <td>{{ optional($row->item)->item_name }}</td>
                                    <td>{{ optional($row->description)->description_name }}
                                        ({{ optional($row->description)->unit_name }})
                                    </td>
                                    <td>{{ number_format($row->requested_qty, 2) }}</td>
                                    <td>{{ number_format($row->issued_qty, 2) }}</td>
                                    <td>{{ number_format($row->received_qty, 2) }}</td>
                                    <td>{{ $row->received_date ?? '-' }}</td>
                                    <td>
                                        @if ($row->request_type == 'stock')
                                            <span class="badge badge-success">Stock</span>
                                        @else
                                            <span class="badge badge-warning">Purchase</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->status }}</td>
                                    <td>{{ $row->reason }}</td>
                                    <td>{{ $row->received_remarks ?? '-' }}</td>
                                    <td style="white-space:nowrap;">
                                        @if ($row->status !== 'Issued' && (float) $row->issued_qty <= 0)
                                            @can('Edit-Requested-Items-Details')
                                                <button type="button" class="btn btn-sm btn-warning"
                                                    onclick='openRequestEdit(@json($payload))'>Edit</button>
                                            @endcan
                                        @endif

                                        @if ($row->status !== 'Issued' && (float) $row->issued_qty <= 0)
                                            @can('Delete-Requested-Items-Details')
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick='deleteRequest(@json($payload))'>Remove</button>
                                            @endcan
                                        @endif

                                        @if ((float) $row->issued_qty > 0)
                                            @can('Confirm-Received-Requested-Items')
                                                <button type="button" class="btn btn-sm btn-success"
                                                    onclick='openConfirmReceipt(@json($payload))'>Confirm
                                                    Receipt</button>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center text-muted">No requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE --}}
    <div class="modal fade" id="requestCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="requestCreateForm" action="{{ route('req.gs.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>New General Supply Request</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Company</label>
                                <select name="company_id" id="req_company_id" class="form-control select2_modal" required>
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">
                                            {{ trim(($company->company_code ?? '') . ' - ' . $company->company_name, ' -') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Business Unit</label>
                                <select name="comp_unit_id" id="req_comp_unit_id" class="form-control select2_modal"
                                    required>
                                    <option value="">Select Business Unit</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Working Point</label>
                                <select name="work_point_id" id="req_work_point_id" class="form-control select2_modal"
                                    required>
                                    <option value="">Select Working Point</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Department</label>
                                <select name="dept_id" id="req_dept_id" class="form-control select2_modal">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">
                                            {{ trim(($department->depCode ?? '') . ' - ' . $department->depName, ' -') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Section</label>
                                <select name="section_id" id="req_section_id" class="form-control select2_modal">
                                    <option value="">Select Section</option>
                                </select>
                                <small class="text-muted">Department will still be picked automatically in backend from
                                    selected section.</small>
                            </div>

                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Request Date</label>
                                <input type="date" name="request_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Item</label>
                                <select name="item_id" id="req_item_id" class="form-control select2_modal" required>
                                    <option value="">Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label><span style="color:red">*</span> Description</label>
                                <select name="item_description_id" id="req_description_id"
                                    class="form-control select2_modal" required>
                                    <option value="">Select Description</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Total Available Stock</label>
                                <input type="text" id="req_available_stock" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label><span style="color:red">*</span> Expense Account</label>
                                <select name="account_code" id="req_account_code" class="form-control select2_modal"
                                    required>
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->SubCode }}">{{ $acc->SubCode }} -
                                            {{ $acc->SubDescription }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label><span style="color:red">*</span> Requested Qty</label>
                                <input type="number" step="0.01" min="0.01" name="requested_qty"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label>Reason</label>
                                <textarea name="reason" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT --}}
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
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- CONFIRM RECEIPT --}}
    <div class="modal fade" id="confirmReceiptModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="confirmReceiptForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Confirm Received Quantity</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="confirmReceiptBody">
                        <div class="text-center">Loading...</div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var companyOptions = @json($companyOptions);
            var departmentOptions = @json($departmentOptions);
            var itemOptions = @json($itemOptions);
            var accountOptions = @json($accountOptions);

            function initSelect2WithParent($element, parentSelector = null) {
                if (!$element || !$element.length) return;

                if ($element.hasClass("select2-hidden-accessible")) {
                    try {
                        $element.select2('destroy');
                    } catch (e) {}
                }

                var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);

                $element.select2({
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
                if ($this.closest('#requestCreateModal').length) {
                    initSelect2WithParent($this, '#requestCreateModal');
                    return;
                }
                if ($this.closest('#requestEditModal').length) {
                    initSelect2WithParent($this, '#requestEditModal');
                    return;
                }
                if ($this.closest('#confirmReceiptModal').length) {
                    initSelect2WithParent($this, '#confirmReceiptModal');
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

            $('#confirmReceiptModal').on('shown.bs.modal', function() {
                initAllModalSelects('#confirmReceiptModal');
            });

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function setSelectHtml(selector, placeholder, html) {
                $(selector).html('<option value="">' + placeholder + '</option>' + (html || '')).trigger('change');
            }

            function clearOrganizationAfter(level, prefix) {
                if (level <= 1) setSelectHtml('#' + prefix + '_comp_unit_id', 'Select Business Unit');
                if (level <= 2) setSelectHtml('#' + prefix + '_work_point_id', 'Select Working Point');

                /*
                 * Department is NOT cleared/loaded by Company, Business Unit, or Working Point.
                 * It is pulled once from $departments in the controller and printed directly in the view.
                 * Only Section depends on Department through AJAX.
                 */
                if (level <= 3) setSelectHtml('#' + prefix + '_section_id', 'Select Section');

                if (prefix === 'req') $('#req_available_stock').val('');
            }

            function loadUnits(companyId, prefix, selectedId = null, callback = null) {
                clearOrganizationAfter(1, prefix);
                if (!companyId) return;

                $('#' + prefix + '_comp_unit_id').html('<option value="">Loading...</option>').trigger('change');

                $.get("{{ url('/admin/reqsts/general-supply/ajax/company-units') }}/" + companyId, function(res) {
                    var html = '';
                    res.forEach(function(row) {
                        var text = $.trim((row.unit_code || '') + ' - ' + row.unit_name).replace(
                            /^ - | - $/g, '');
                        var selected = String(row.id) === String(selectedId) ? ' selected' : '';
                        html += '<option value="' + row.id + '"' + selected + '>' + escapeHtml(
                            text) + '</option>';
                    });
                    setSelectHtml('#' + prefix + '_comp_unit_id', 'Select Business Unit', html);
                    if (callback) callback();
                });
            }

            function loadWorkPoints(unitId, prefix, selectedId = null, callback = null) {
                clearOrganizationAfter(2, prefix);
                if (!unitId) return;

                $('#' + prefix + '_work_point_id').html('<option value="">Loading...</option>').trigger('change');

                $.get("{{ url('/admin/reqsts/general-supply/ajax/work-points') }}/" + unitId, function(res) {
                    var html = '';
                    res.forEach(function(row) {
                        var text = $.trim((row.work_code || '') + ' - ' + row.work_name).replace(
                            /^ - | - $/g, '');
                        var selected = String(row.id) === String(selectedId) ? ' selected' : '';
                        html += '<option value="' + row.id + '"' + selected + '>' + escapeHtml(
                            text) + '</option>';
                    });
                    setSelectHtml('#' + prefix + '_work_point_id', 'Select Working Point', html);
                    if (callback) callback();
                });
            }

            function loadSections(departmentId, prefix, selectedId = null, callback = null) {
                clearOrganizationAfter(4, prefix);
                if (!departmentId) return;

                $('#' + prefix + '_section_id').html('<option value="">Loading...</option>').trigger('change');

                $.get("{{ url('/admin/reqsts/general-supply/ajax/sections') }}/" + departmentId, function(res) {
                    var html = '';
                    res.forEach(function(row) {
                        var text = $.trim((row.secCode || '') + ' - ' + row.secName).replace(
                            /^ - | - $/g, '');
                        var selected = String(row.id) === String(selectedId) ? ' selected' : '';
                        html += '<option value="' + row.id + '"' + selected + '>' + escapeHtml(
                            text) + '</option>';
                    });
                    setSelectHtml('#' + prefix + '_section_id', 'Select Section', html);
                    if (callback) callback();
                });
            }

            function loadDescriptions(itemId, descriptionSelector, selectedId = null, callback = null) {
                $(descriptionSelector).html('<option value="">Loading...</option>').trigger('change');

                if (!itemId) {
                    $(descriptionSelector).html('<option value="">Select Description</option>').trigger('change');
                    return;
                }

                $.get("{{ url('/admin/reqsts/general-supply/ajax/descriptions') }}/" + itemId, function(res) {
                    var html = '<option value="">Select Description</option>';
                    res.forEach(function(row) {
                        var selected = String(row.id) === String(selectedId) ? ' selected' : '';
                        html += '<option value="' + row.id + '"' + selected + '>' + escapeHtml(row
                            .description_name + ' (' + row.unit_name + ')') + '</option>';
                    });
                    $(descriptionSelector).html(html).trigger('change');
                    if (callback) callback();
                });
            }

            window.openRequestCreateModal = function() {
                $('#requestCreateModal').modal('show');
            };

            $('#req_company_id').on('change', function() {
                loadUnits($(this).val(), 'req');
            });

            $('#req_comp_unit_id').on('change', function() {
                loadWorkPoints($(this).val(), 'req');
            });

            $('#req_work_point_id').on('change', function() {
                loadAvailableStock();
            });

            $('#req_dept_id').on('change', function() {
                loadSections($(this).val(), 'req');
            });

            $('#req_section_id').on('change', function() {
                loadAvailableStock();
            });

            $('#req_item_id').on('change', function() {
                loadDescriptions($(this).val(), '#req_description_id');
                loadAvailableStock();
            });

            $('#req_description_id').on('change', function() {
                loadAvailableStock();
            });

            function loadAvailableStock() {
                let work_point_id = $('#req_work_point_id').val();
                let section_id = $('#req_section_id').val();
                let item_id = $('#req_item_id').val();
                let item_description_id = $('#req_description_id').val();

                if (!work_point_id || !item_id || !item_description_id) {
                    $('#req_available_stock').val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('req.gs.ajax.available.stock') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        work_point_id: work_point_id,
                        section_id: section_id,
                        item_id: item_id,
                        item_description_id: item_description_id
                    },
                    success: function(res) {
                        $('#req_available_stock').val(res.total_available);
                    }
                });
            }

            window.openRequestEdit = function(row) {
                var html = '';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Company</label>';
                html +=
                    '<select name="company_id" id="edit_req_company_id" class="form-control select2_modal" required>';
                html += '<option value="">Select Company</option>';
                for (var c = 0; c < companyOptions.length; c++) {
                    html += '<option value="' + companyOptions[c].id + '"' + (String(row.company_id) === String(
                        companyOptions[c].id) ? ' selected' : '') + '>' + escapeHtml(companyOptions[c]
                        .name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Business Unit</label>';
                html +=
                    '<select name="comp_unit_id" id="edit_req_comp_unit_id" class="form-control select2_modal" required>';
                html += '<option value="">Select Business Unit</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Working Point</label>';
                html +=
                    '<select name="work_point_id" id="edit_req_work_point_id" class="form-control select2_modal" required>';
                html += '<option value="">Select Working Point</option>';
                html += '</select>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Department</label>';
                html += '<select name="dept_id" id="edit_req_dept_id" class="form-control select2_modal">';
                html += '<option value="">Select Department</option>';
                for (var d = 0; d < departmentOptions.length; d++) {
                    html += '<option value="' + departmentOptions[d].id + '"' + (String(row.dept_id) === String(
                        departmentOptions[d].id) ? ' selected' : '') + '>' + escapeHtml(departmentOptions[d]
                        .name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Section</label>';
                html +=
                    '<select name="section_id" id="edit_req_section_id" class="form-control select2_modal">';
                html += '<option value="">Select Section</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Request Date</label>';
                html += '<input type="date" name="request_date" class="form-control" value="' + escapeHtml(row
                    .request_date || '') + '" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Item</label>';
                html +=
                    '<select name="item_id" id="edit_req_item_id" class="form-control select2_modal" required>';
                html += '<option value="">Select Item</option>';
                for (var i = 0; i < itemOptions.length; i++) {
                    html += '<option value="' + itemOptions[i].id + '"' + (String(row.item_id) === String(
                            itemOptions[i].id) ? ' selected' : '') + '>' + escapeHtml(itemOptions[i].name) +
                        '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Description</label>';
                html +=
                    '<select name="item_description_id" id="edit_req_description_id" class="form-control select2_modal" required>';
                html += '<option value="">Loading...</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Requested Qty</label>';
                html +=
                    '<input type="number" step="0.01" min="0.01" name="requested_qty" class="form-control" value="' +
                    escapeHtml(row.requested_qty || '') + '" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-6">';
                html += '<label><span style="color:red">*</span> Expense Account</label>';
                html +=
                    '<select name="account_code" id="edit_req_account_code" class="form-control select2_modal" required>';
                html += '<option value="">Select Account</option>';
                for (var a = 0; a < accountOptions.length; a++) {
                    html += '<option value="' + escapeHtml(accountOptions[a].id) + '"' + (String(row
                            .account_code) === String(accountOptions[a].id) ? ' selected' : '') + '>' +
                        escapeHtml(accountOptions[a].name) + '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-6">';
                html += '<label>Reason</label>';
                html += '<textarea name="reason" class="form-control" rows="2">' + escapeHtml(row.reason ||
                    '') + '</textarea>';
                html += '</div>';
                html += '</div>';

                $('#requestEditBody').html(html);
                $('#requestEditForm').attr('action', "{{ url('/admin/reqsts/general-supply/requisition') }}/" +
                    encodeURIComponent(row.id));
                $('#requestEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#requestEditModal');

                    if (row.company_id) {
                        loadUnits(row.company_id, 'edit_req', row.comp_unit_id, function() {
                            if (row.comp_unit_id) {
                                loadWorkPoints(row.comp_unit_id, 'edit_req', row.work_point_id);
                            }
                        });
                    }

                    if (row.dept_id) {
                        loadSections(row.dept_id, 'edit_req', row.section_id);
                    }

                    loadDescriptions(row.item_id, '#edit_req_description_id', row.item_description_id);

                    $(document).off('change', '#edit_req_company_id').on('change',
                        '#edit_req_company_id',
                        function() {
                            loadUnits($(this).val(), 'edit_req');
                        });

                    $(document).off('change', '#edit_req_comp_unit_id').on('change',
                        '#edit_req_comp_unit_id',
                        function() {
                            loadWorkPoints($(this).val(), 'edit_req');
                        });

                    $(document).off('change', '#edit_req_work_point_id').on('change',
                        '#edit_req_work_point_id',
                        function() {
                            // Department is not dependent on Working Point.
                        });

                    $(document).off('change', '#edit_req_dept_id').on('change', '#edit_req_dept_id',
                        function() {
                            loadSections($(this).val(), 'edit_req');
                        });

                    $(document).off('change', '#edit_req_item_id').on('change', '#edit_req_item_id',
                        function() {
                            loadDescriptions($(this).val(), '#edit_req_description_id');
                        });
                }, 200);
            };

            window.deleteRequest = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href =
                            "{{ url('/admin/reqsts/general-supply/requisition/remove') }}/" +
                            encodeURIComponent(row.id);
                    }
                });
            };

            window.openConfirmReceipt = function(row) {
                var html = '';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Request No</label>';
                html += '<input type="text" class="form-control" value="' + escapeHtml(row.request_no || '') +
                    '" readonly>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Issued Qty</label>';
                html += '<input type="text" class="form-control" value="' + escapeHtml(row.issued_qty || '') +
                    '" readonly>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Received Qty</label>';
                html +=
                    '<input type="number" step="0.01" min="0.01" name="received_qty" class="form-control" value="' +
                    escapeHtml(row.received_qty || '') + '" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-6">';
                html += '<label><span style="color:red">*</span> Received Date</label>';
                html += '<input type="date" name="received_date" class="form-control" value="' + escapeHtml(row
                    .received_date || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-6">';
                html += '<label>Remarks</label>';
                html += '<textarea name="received_remarks" class="form-control">' + escapeHtml(row
                    .received_remarks || '') + '</textarea>';
                html += '</div>';
                html += '</div>';

                $('#confirmReceiptBody').html(html);
                $('#confirmReceiptForm').attr('action',
                    "{{ url('/admin/reqsts/general-supply/requisition/confirm-receipt') }}/" +
                    encodeURIComponent(row.id));
                $('#confirmReceiptModal').modal('show');
            };
        });
    </script>
@endsection
