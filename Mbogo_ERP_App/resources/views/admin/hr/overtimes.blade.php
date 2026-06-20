@extends('layouts.AdminMaster')
@section('content')
    <style>
        #otCreateModal .select2-container,
        #otEditModal .select2-container {
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
            margin-top: 6px;
            padding: 8px 10px;
            background: #f5f7fa;
            border: 1px solid #e7eaec;
            border-radius: 4px;
            font-size: 12px;
            color: #333;
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Staff/Users Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('hr') }}">Human Resource</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Staff/User Overtimes</strong>
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
        <h3 class="mb-2 page-title">Overtime</h3>
        @can('Register-Overtime')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#otCreateModal">Add Overtime</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>Overtime Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Company</th>
                                        <th>Work Point</th>
                                        <th>Staff</th>
                                        <th>Hours</th>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($overtimes as $k => $o)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $o->date }}</td>
                                            <td>{{ optional($o->company)->company_name ?? '-' }}</td>
                                            <td>{{ optional($o->workpoint)->work_name ?? '-' }}</td>
                                            <td>{{ optional($o->user)->name ?? '-' }}</td>
                                            <td>{{ $o->hours }}</td>
                                            <td>{{ number_format($o->rate_per_hour, 2) }}</td>
                                            <td>{{ number_format($o->amount, 2) }}</td>
                                            <td>{{ $o->status }}</td>
                                            <td>
                                                @can('Approve-Overtime')
                                                    @if ($o->status === 'Pending')
                                                        <form action="{{ route('hr.overtimes.approve', encrypt($o->id)) }}"
                                                            method="POST" style="display:inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                    @endif
                                                @endcan

                                                @can('Pay-Overtime')
                                                    @if ($o->status === 'Approved')
                                                        <form action="{{ route('hr.overtimes.pay', encrypt($o->id)) }}"
                                                            method="POST" style="display:inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-primary">Pay</button>
                                                        </form>
                                                    @endif
                                                @endcan

                                                @can('Edit-Overtime')
                                                    @if (!in_array($o->status, ['Paid', 'Deleted', 'Approved']))
                                                        <button class="btn btn-sm btn-warning btn-edit-ot"
                                                            data-id="{{ encrypt($o->id) }}" data-user_id="{{ $o->user_id }}"
                                                            data-date="{{ $o->date }}" data-hours="{{ $o->hours }}"
                                                            data-rate="{{ $o->rate_per_hour }}"
                                                            data-note="{{ $o->note }}"
                                                            data-status="{{ $o->status ?? 'Pending' }}"
                                                            data-work_point_id="{{ $o->work_point_id }}">Edit</button>
                                                    @else
                                                        <button class="btn btn-sm btn-warning" disabled
                                                            title="Cannot edit a Paid, Approved or Deleted record">Edit</button>
                                                    @endif
                                                @endcan

                                                @can('Delete-Overtime')
                                                    @if (!in_array($o->status, ['Paid', 'Deleted', 'Approved']))
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-sm btn-danger btn-delete-ot"
                                                            data-id="{{ encrypt($o->id) }}">Remove</a>
                                                    @else
                                                        <button class="btn btn-sm btn-danger" disabled
                                                            title="Cannot remove a Paid, Approved or Deleted record">Remove</button>
                                                    @endif
                                                @endcan

                                                @if ($o->status === 'Paid')
                                                    <span class="badge badge-success ml-1">Paid</span>
                                                @elseif($o->status === 'Approved')
                                                    <span class="badge badge-info ml-1">Locked</span>
                                                @elseif($o->status === 'Pending')
                                                    <span class="badge badge-warning ml-1">Pending</span>
                                                @endif
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

    <!-- Create Modal -->
    <div class="modal fade" id="otCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="otCreateForm" action="{{ route('hr.overtimes.store') }}" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Overtime</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Work Point</label>
                            <select id="create_ot_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">--select work point--</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $companyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $companyCode = optional($wp->company)->company_code ?? '';
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company-name="{{ $companyName }}"
                                        data-company-code="{{ $companyCode }}" data-work-name="{{ $wp->work_name }}">
                                        {{ $wp->work_name }} -
                                        {{ $companyName }}{{ $companyCode ? ' (' . $companyCode . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="create_ot_workpoint_company_info" class="workpoint-company-info" style="display:none;">
                            </div>
                        </div>
                        <div class="form-group"><label>Staff</label>
                            <select name="user_id" class="form-control select2_demo_3" required>
                                <option value="">--select staff--</option>
                                @foreach ($staffUsers as $s)
                                    @php
                                        $staffCompany = optional($s->company)->company_name ?? 'NO COMPANY SITE';
                                        $staffWorkPoint = optional($s->workpoint)->work_name ?? 'NO WORK POINT';
                                    @endphp
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} - {{ $staffCompany }} / {{ $staffWorkPoint }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Date</label><input type="date" name="date" class="form-control"
                                required></div>
                        <div class="form-group"><label>Hours</label><input type="number" step="0.01" name="hours"
                                class="form-control" required></div>
                        <div class="form-group"><label>Rate per hour</label><input type="text" name="rate_per_hour"
                                class="form-control"></div>
                        <div class="form-group"><label>Note</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('otCreateForm')" class="btn btn-primary">Submit</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="otEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="otEditForm" method="POST">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Overtime</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_ot_id" name="edit_id">
                        <div class="form-group"><label>Work Point</label>
                            <select id="edit_ot_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">--select--</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $companyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $companyCode = optional($wp->company)->company_code ?? '';
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company-name="{{ $companyName }}"
                                        data-company-code="{{ $companyCode }}" data-work-name="{{ $wp->work_name }}">
                                        {{ $wp->work_name }} -
                                        {{ $companyName }}{{ $companyCode ? ' (' . $companyCode . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="edit_ot_workpoint_company_info" class="workpoint-company-info"
                                style="display:none;"></div>
                        </div>
                        <div class="form-group"><label>Staff</label>
                            <select id="edit_ot_user_id" name="user_id" class="form-control select2_demo_3" required>
                                <option value="">--select staff--</option>
                                @foreach ($staffUsers as $s)
                                    @php
                                        $staffCompany = optional($s->company)->company_name ?? 'NO COMPANY SITE';
                                        $staffWorkPoint = optional($s->workpoint)->work_name ?? 'NO WORK POINT';
                                    @endphp
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} - {{ $staffCompany }} / {{ $staffWorkPoint }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Date</label><input id="edit_ot_date" type="date"
                                name="date" class="form-control"></div>
                        <div class="form-group"><label>Hours</label><input id="edit_ot_hours" type="number"
                                step="0.01" name="hours" class="form-control"></div>
                        <div class="form-group"><label>Rate</label><input id="edit_ot_rate" name="rate_per_hour"
                                class="form-control"></div>
                        <div class="form-group"><label>Note</label>
                            <textarea id="edit_ot_note" name="note" class="form-control"></textarea>
                        </div>
                        <div class="form-group"><label>Status</label><select id="edit_ot_status" name="status"
                                class="form-control select2_demo_3">
                                <option>Pending</option>
                                <option>Approved</option>
                                <option>Paid</option>
                                <option>Deleted</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('otEditForm')" class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tempOtEditData = null;

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

            function showWorkPointCompany(selectSelector, infoSelector) {
                var select = document.querySelector(selectSelector);
                var info = document.querySelector(infoSelector);
                if (!select || !info) return;

                var opt = select.options[select.selectedIndex];
                if (!opt || !opt.value) {
                    info.style.display = 'none';
                    info.innerHTML = '';
                    return;
                }

                var companyName = opt.getAttribute('data-company-name') || '-';
                var companyCode = opt.getAttribute('data-company-code') || '';
                var workName = opt.getAttribute('data-work-name') || opt.text;

                info.style.display = 'block';
                info.innerHTML = '<strong>Company Site:</strong> ' + companyName + (companyCode ? ' (' +
                    companyCode + ')' : '') + '<br><strong>Selected Work Point:</strong> ' + workName;
            }

            $('.select2_demo_3').each(function() {
                var $this = $(this);
                if ($this.closest('#otCreateModal').length) {
                    initSelect2WithParent($this, '#otCreateModal');
                    return;
                }
                if ($this.closest('#otEditModal').length) {
                    initSelect2WithParent($this, '#otEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $(document).on('shown.bs.modal', '#otCreateModal', function() {
                var $m = $(this);
                if ($m.find('form')[0]) $m.find('form')[0].reset();
                $m.find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#otCreateModal');
                    $(this).val(null).trigger('change');
                });
                showWorkPointCompany('#create_ot_work_point_id', '#create_ot_workpoint_company_info');
            });

            $(document).on('shown.bs.modal', '#otEditModal', function() {
                var $m = $(this);
                $m.find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#otEditModal');
                });
                if (tempOtEditData) {
                    if (typeof tempOtEditData.work_point_id !== 'undefined') $('#edit_ot_work_point_id')
                        .val(tempOtEditData.work_point_id).trigger('change');
                    if (typeof tempOtEditData.user_id !== 'undefined') $('#edit_ot_user_id').val(
                        tempOtEditData.user_id).trigger('change');
                    tempOtEditData = null;
                }
                showWorkPointCompany('#edit_ot_work_point_id', '#edit_ot_workpoint_company_info');
            });

            $(document).on('change', '#create_ot_work_point_id', function() {
                showWorkPointCompany('#create_ot_work_point_id', '#create_ot_workpoint_company_info');
            });

            $(document).on('change', '#edit_ot_work_point_id', function() {
                showWorkPointCompany('#edit_ot_work_point_id', '#edit_ot_workpoint_company_info');
            });

            document.querySelectorAll('.btn-edit-ot').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    document.getElementById('edit_ot_id').value = enc || '';
                    document.getElementById('edit_ot_date').value = this.dataset.date || '';
                    document.getElementById('edit_ot_hours').value = this.dataset.hours || '';
                    document.getElementById('edit_ot_rate').value = this.dataset.rate || '';
                    document.getElementById('edit_ot_note').value = this.dataset.note || '';
                    document.getElementById('edit_ot_status').value = this.dataset.status ||
                        'Pending';
                    tempOtEditData = {
                        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ?
                            this.dataset.work_point_id : null,
                        user_id: (typeof this.dataset.user_id !== 'undefined') ? this.dataset
                            .user_id : null
                    };
                    var form = document.getElementById('otEditForm');
                    form.action = "{{ route('hr.overtimes.update', ':id') }}".replace(':id', enc);
                    $('#otEditModal').modal('show');
                });
            });

            document.querySelectorAll('.btn-delete-ot').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will mark the overtime as Deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then(function(res) {
                        if (res.isConfirmed) window.location.href =
                            "{{ route('hr.overtimes.remove', ':id') }}".replace(':id',
                                enc);
                    });
                });
            });

        });
    </script>
@endsection
