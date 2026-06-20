@extends('layouts.AdminMaster')
@section('content')
    <style>
        /* Fix Select2 inside Bootstrap modal */
        #heslbCreateModal .select2-container,
        #heslbEditModal .select2-container {
            width: 100% !important;
        }

        .select2-container--open,
        .select2-dropdown {
            z-index: 999999 !important;
        }

        .modal-open .select2-container--open {
            z-index: 999999 !important;
        }

        .workpoint-company-info {
            margin-top: 8px;
            padding: 8px 10px;
            border-radius: 5px;
            background: #f3f6fb;
            border: 1px solid #dce6f5;
            font-size: 12px;
            color: #333;
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>HESLB Loans</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li><a href="{{ route('hr') }}">Human Resource</a></li><span
                    style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Staff HESLB Loans</strong></li>
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
                            <tr>
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

    <div class="col-12">
        <h3 class="mb-2 page-title">HESLB Loans</h3>
        @can('Register-Loan')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#heslbCreateModal">Add HESLB Loan</button>
        @endcan
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>HESLB Loan Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Staff</th>
                                        <th>Company Site</th>
                                        <th>Work Point</th>
                                        <th>Original Amount</th>
                                        <th>Outstanding</th>
                                        <th>Monthly Rate</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($heslbLoans as $k => $l)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ optional($l->user)->name ?? '-' }}</td>
                                            <td>{{ optional($l->company)->company_name ?? '-' }}</td>
                                            <td>{{ optional($l->workpoint)->work_name ?? '-' }}</td>
                                            <td class="text-right">{{ number_format($l->original_amount, 2) }}</td>
                                            <td class="text-right">{{ number_format($l->outstanding_balance, 2) }}</td>
                                            <td>{{ $l->monthly_rate }}%</td>
                                            <td>{{ $l->status }}</td>
                                            <td>
                                                @can('Edit-Loan')
                                                    <button class="btn btn-sm btn-warning btn-edit-heslb"
                                                        data-id="{{ encrypt($l->id) }}" data-user_id="{{ $l->user_id }}"
                                                        data-original_amount="{{ $l->original_amount }}"
                                                        data-outstanding_balance="{{ $l->outstanding_balance }}"
                                                        data-monthly_rate="{{ $l->monthly_rate }}"
                                                        data-start_date="{{ optional($l->start_date)->format('Y-m-d') }}"
                                                        data-end_date="{{ optional($l->end_date)->format('Y-m-d') }}"
                                                        data-status="{{ $l->status }}" data-notes="{{ $l->notes }}"
                                                        data-work_point_id="{{ $l->work_point_id }}">Edit</button>
                                                @endcan
                                                @can('Delete-Loan')
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-heslb"
                                                        data-id="{{ encrypt($l->id) }}">Remove</a>
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

    <div class="modal fade" id="heslbCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="heslbCreateForm" action="{{ route('hr.heslb.store') }}" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add HESLB Loan</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Work Point</label>
                            <select id="create_heslb_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">-- Select Work Point --</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $companyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $companyCode = optional($wp->company)->company_code ?? '';
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company="{{ $companyName }}"
                                        data-company-code="{{ $companyCode }}" data-workpoint="{{ $wp->work_name }}">
                                        {{ $wp->work_name }} -
                                        {{ $companyName }}{{ $companyCode ? ' (' . $companyCode . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="create_heslb_workpoint_info" class="workpoint-company-info" style="display:none;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Staff</label>
                            <select id="create_heslb_user_id" name="user_id" class="form-control select2_demo_3" required>
                                <option value="">-- Select staff --</option>
                                @foreach ($staffUsers as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }}{{ optional($s->company)->company_name ? ' - ' . optional($s->company)->company_name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Original Loan Amount</label><input type="number" step="0.01"
                                name="original_amount" class="form-control" required></div>
                        <div class="form-group"><label>Outstanding Balance</label><input type="number" step="0.01"
                                name="outstanding_balance" class="form-control"
                                placeholder="Leave empty to use original amount"></div>
                        <div class="form-group"><label>Monthly Rate (%)</label><input type="number" step="0.01"
                                name="monthly_rate" class="form-control" value="15" required></div>
                        <div class="form-group"><label>Start Date</label><input type="date" name="start_date"
                                class="form-control"></div>
                        <div class="form-group"><label>Notes</label>
                            <textarea name="notes" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('heslbCreateForm')" class="btn btn-primary">Submit</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="heslbEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="heslbEditForm" method="POST">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit HESLB Loan</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Work Point</label>
                            <select id="edit_heslb_work_point_id" name="work_point_id"
                                class="form-control select2_demo_3" required>
                                <option value="">-- Select Work Point --</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $companyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $companyCode = optional($wp->company)->company_code ?? '';
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company="{{ $companyName }}"
                                        data-company-code="{{ $companyCode }}" data-workpoint="{{ $wp->work_name }}">
                                        {{ $wp->work_name }} -
                                        {{ $companyName }}{{ $companyCode ? ' (' . $companyCode . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="edit_heslb_workpoint_info" class="workpoint-company-info" style="display:none;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Staff</label>
                            <select id="edit_heslb_user_id" name="user_id" class="form-control select2_demo_3" required>
                                <option value="">-- Select staff --</option>
                                @foreach ($staffUsers as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }}{{ optional($s->company)->company_name ? ' - ' . optional($s->company)->company_name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group"><label>Original Amount</label><input id="edit_heslb_original_amount"
                                type="number" step="0.01" name="original_amount" class="form-control" required>
                        </div>
                        <div class="form-group"><label>Outstanding Balance</label><input
                                id="edit_heslb_outstanding_balance" type="number" step="0.01"
                                name="outstanding_balance" class="form-control" required></div>
                        <div class="form-group"><label>Monthly Rate (%)</label><input id="edit_heslb_monthly_rate"
                                type="number" step="0.01" name="monthly_rate" class="form-control" required></div>
                        <div class="form-group"><label>Start Date</label><input id="edit_heslb_start_date" type="date"
                                name="start_date" class="form-control"></div>
                        <div class="form-group"><label>End Date</label><input id="edit_heslb_end_date" type="date"
                                name="end_date" class="form-control"></div>
                        <div class="form-group"><label>Status</label><select id="edit_heslb_status" name="status"
                                class="form-control select2_demo_3">
                                <option>Active</option>
                                <option>Paid</option>
                                <option>Suspended</option>
                                <option>Deleted</option>
                            </select></div>
                        <div class="form-group"><label>Notes</label>
                            <textarea id="edit_heslb_notes" name="notes" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('heslbEditForm')" class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tempHeslbEditData = null;

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

            function showWorkPointInfo(selectEl, infoSelector) {
                var option = selectEl.options[selectEl.selectedIndex];
                var info = document.querySelector(infoSelector);
                if (!info) return;

                if (!option || !selectEl.value) {
                    info.style.display = 'none';
                    info.innerHTML = '';
                    return;
                }

                var company = option.getAttribute('data-company') || '-';
                var code = option.getAttribute('data-company-code') || '';
                var workpoint = option.getAttribute('data-workpoint') || option.text || '-';

                info.style.display = 'block';
                info.innerHTML = '<strong>Company Site:</strong> ' + company + (code ? ' (' + code + ')' : '') +
                    '<br><strong>Selected Work Point:</strong> ' + workpoint;
            }

            $('.select2_demo_3').each(function() {
                var $this = $(this);
                if ($this.closest('#heslbCreateModal').length) {
                    initSelect2WithParent($this, '#heslbCreateModal');
                    return;
                }
                if ($this.closest('#heslbEditModal').length) {
                    initSelect2WithParent($this, '#heslbEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $(document).on('shown.bs.modal', '#heslbCreateModal', function() {
                var $m = $(this);
                if ($m.find('form')[0]) $m.find('form')[0].reset();
                $m.find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#heslbCreateModal');
                    $(this).val(null).trigger('change');
                });
                showWorkPointInfo(document.getElementById('create_heslb_work_point_id'),
                    '#create_heslb_workpoint_info');
            });

            $(document).on('shown.bs.modal', '#heslbEditModal', function() {
                var $m = $(this);
                $m.find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#heslbEditModal');
                });

                if (tempHeslbEditData) {
                    $('#edit_heslb_work_point_id').val(tempHeslbEditData.work_point_id || '').trigger(
                        'change');
                    $('#edit_heslb_user_id').val(tempHeslbEditData.user_id || '').trigger('change');
                    $('#edit_heslb_status').val(tempHeslbEditData.status || 'Active').trigger('change');
                    tempHeslbEditData = null;
                }

                showWorkPointInfo(document.getElementById('edit_heslb_work_point_id'),
                    '#edit_heslb_workpoint_info');
            });

            $(document).on('change', '#create_heslb_work_point_id', function() {
                showWorkPointInfo(this, '#create_heslb_workpoint_info');
            });

            $(document).on('change', '#edit_heslb_work_point_id', function() {
                showWorkPointInfo(this, '#edit_heslb_workpoint_info');
            });

            document.querySelectorAll('.btn-edit-heslb').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.dataset.id;
                    document.getElementById('heslbEditForm').action =
                        "{{ route('hr.heslb.update', ':id') }}".replace(':id', id);

                    document.getElementById('edit_heslb_original_amount').value = this.dataset
                        .original_amount || 0;
                    document.getElementById('edit_heslb_outstanding_balance').value = this.dataset
                        .outstanding_balance || 0;
                    document.getElementById('edit_heslb_monthly_rate').value = this.dataset
                        .monthly_rate || 15;
                    document.getElementById('edit_heslb_start_date').value = this.dataset
                        .start_date || '';
                    document.getElementById('edit_heslb_end_date').value = this.dataset.end_date ||
                        '';
                    document.getElementById('edit_heslb_notes').value = this.dataset.notes || '';

                    tempHeslbEditData = {
                        work_point_id: this.dataset.work_point_id || '',
                        user_id: this.dataset.user_id || '',
                        status: this.dataset.status || 'Active'
                    };

                    $('#heslbEditModal').modal('show');
                });
            });

            document.querySelectorAll('.btn-delete-heslb').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                            title: 'Are you sure?',
                            text: "This will remove this HESLB record.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes'
                        })
                        .then(function(res) {
                            if (res.isConfirmed) {
                                window.location.href = "{{ route('hr.heslb.remove', ':id') }}"
                                    .replace(':id', enc);
                            }
                        });
                });
            });
        });
    </script>
@endsection
