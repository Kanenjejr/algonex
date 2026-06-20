@extends('layouts.AdminMaster')
@section('content')

    <style>
        /* Fix Select2 inside Bootstrap modal: dropdown must appear above modal and stay clickable */
        #absCreateModal .select2-container,
        #absEditModal .select2-container {
            width: 100% !important;
        }

        .select2-container--open,
        .select2-dropdown {
            z-index: 999999 !important;
        }

        .modal-open .select2-container--open {
            z-index: 999999 !important;
        }

        .work-point-company-info {
            margin-top: 8px;
            padding: 8px 10px;
            border-radius: 5px;
            background: #eef9ff;
            border: 1px solid #bde5f3;
            color: #1c5d70;
            font-size: 13px;
            display: none;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Absences Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li><a href="{{ route('hr') }}">Human Resource</a></li><span
                    style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Staff/User Absences</strong></li>
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
        <h3 class="mb-2 page-title">Absences</h3>
        @can('Register-Absence')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#absCreateModal">Record Absence</button>
        @endcan
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>Absence Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Staff</th>
                                        <th>Month Days</th>
                                        <th>Absent Days</th>
                                        <th>Paid Days</th>
                                        <th>Daily Rate</th>
                                        <th>Deduction</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($absences as $k => $a)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($a->date)->toDateString() }}</td>
                                            <td>{{ optional($a->user)->name ?? '-' }}</td>
                                            <td>{{ $a->calendar_days ?? \Carbon\Carbon::parse($a->date)->daysInMonth }}</td>
                                            <td>{{ $a->days }}</td>
                                            <td>{{ $a->paid_days ?? '-' }}</td>
                                            <td class="text-right">{{ number_format($a->daily_rate ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($a->deduction_amount, 2) }}</td>
                                            <td>{{ $a->status }}</td>
                                            <td>
                                                @can('Approve-Absence')
                                                    @if ($a->status === 'Pending')
                                                        <form action="{{ route('hr.absences.approve', encrypt($a->id)) }}"
                                                            method="POST" style="display:inline">@csrf<button
                                                                class="btn btn-sm btn-success">Approve</button></form>
                                                    @endif
                                                @endcan
                                                @can('Edit-Absence')
                                                    @if (!in_array($a->status, ['Approved', 'Deleted']))
                                                        <button class="btn btn-sm btn-warning btn-edit-abs"
                                                            data-id="{{ encrypt($a->id) }}" data-user_id="{{ $a->user_id }}"
                                                            data-date="{{ $a->date }}" data-days="{{ $a->days }}"
                                                            data-reason="{{ $a->reason }}"
                                                            data-status="{{ $a->status ?? 'Pending' }}"
                                                            data-work_point_id="{{ $a->work_point_id }}">Edit</button>
                                                    @else
                                                        <button class="btn btn-sm btn-warning" disabled
                                                            title="Approved or deleted absence cannot be edited">Edit</button>
                                                    @endif
                                                @endcan
                                                @can('Delete-Absence')
                                                    @if (!in_array($a->status, ['Approved', 'Deleted']))
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-sm btn-danger btn-delete-abs"
                                                            data-id="{{ encrypt($a->id) }}">Remove</a>
                                                    @else
                                                        <button class="btn btn-sm btn-danger" disabled
                                                            title="Approved or deleted absence cannot be removed">Remove</button>
                                                    @endif
                                                @endcan
                                                @if ($a->status === 'Approved')
                                                    <span class="badge badge-info ml-1">Locked</span>
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

    <div class="modal fade" id="absCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="absCreateForm" action="{{ route('hr.absences.store') }}" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Absence</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">Deduction is automatic: Basic salary / days of selected month
                            (28/29/30/31) × absent days. Payroll will also show paid days.</div>
                        @if (in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer']))
                            <div class="form-group"><label>Work Point</label><select name="work_point_id"
                                    class="form-control select2_demo_3">
                                    <option value="">--select--</option>
                                    @foreach ($workPoints as $wp)
                                        @php
                                            $wpCompanyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                            $wpCompanyCode = optional($wp->company)->company_code ?? '';
                                            $wpCompanyLabel = trim(
                                                $wpCompanyName . ($wpCompanyCode ? ' (' . $wpCompanyCode . ')' : ''),
                                            );
                                        @endphp
                                        <option value="{{ $wp->id }}" data-company="{{ $wpCompanyLabel }}"
                                            data-workpoint="{{ $wp->work_name }}">
                                            {{ $wp->work_name }} - {{ $wpCompanyLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="create_work_point_company_info" class="work-point-company-info"></div>
                            </div>
                        @endif
                        <div class="form-group"><label>Staff</label><select name="user_id"
                                class="form-control select2_demo_3">
                                <option value="">--select staff--</option>
                                @foreach ($staffUsers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Date</label><input type="date" name="date" class="form-control"
                                required></div>
                        <div class="form-group"><label>Absent Days</label><input type="number" step="0.25"
                                name="days" class="form-control" value="1" required></div>
                        <div class="form-group"><label>Reason</label>
                            <textarea name="reason" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('absCreateForm')" class="btn btn-primary">Submit</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="absEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="absEditForm" method="POST">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Absence</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        @if (in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer']))
                            <div class="form-group"><label>Work Point</label><select id="edit_abs_work_point_id"
                                    name="work_point_id" class="form-control select2_demo_3">
                                    <option value="">--select--</option>
                                    @foreach ($workPoints as $wp)
                                        @php
                                            $wpCompanyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                            $wpCompanyCode = optional($wp->company)->company_code ?? '';
                                            $wpCompanyLabel = trim(
                                                $wpCompanyName . ($wpCompanyCode ? ' (' . $wpCompanyCode . ')' : ''),
                                            );
                                        @endphp
                                        <option value="{{ $wp->id }}" data-company="{{ $wpCompanyLabel }}"
                                            data-workpoint="{{ $wp->work_name }}">
                                            {{ $wp->work_name }} - {{ $wpCompanyLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="edit_work_point_company_info" class="work-point-company-info"></div>
                            </div>
                        @endif
                        <div class="form-group"><label>Staff</label><select id="edit_abs_user_id" name="user_id"
                                class="form-control select2_demo_3">
                                <option value="">--select staff--</option>
                                @foreach ($staffUsers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select></div>
                        <div class="form-group"><label>Date</label><input id="edit_abs_date" type="date"
                                name="date" class="form-control"></div>
                        <div class="form-group"><label>Absent Days</label><input id="edit_abs_days" type="number"
                                step="0.25" name="days" class="form-control"></div>
                        <div class="form-group"><label>Reason</label>
                            <textarea id="edit_abs_reason" name="reason" class="form-control"></textarea>
                        </div>
                        <div class="form-group"><label>Status</label><select id="edit_abs_status" name="status"
                                class="form-control select2_demo_3">
                                <option>Pending</option>
                                <option>Approved</option>
                                <option>Rejected</option>
                                <option>Deleted</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('absEditForm')" class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function initAbsenceModalSelect2(modalSelector) {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    var $modal = window.jQuery(modalSelector);

                    $modal.find('select.select2_demo_3').each(function() {
                        var $select = window.jQuery(this);

                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }

                        $select.select2({
                            width: '100%',
                            dropdownParent: $modal
                        });
                    });
                }
            }



            function showWorkPointCompany(selectSelector, infoSelector) {
                var select = document.querySelector(selectSelector);
                var info = document.querySelector(infoSelector);

                if (!select || !info) {
                    return;
                }

                var selected = select.options[select.selectedIndex];
                var company = selected ? (selected.getAttribute('data-company') || '') : '';
                var workpoint = selected ? (selected.getAttribute('data-workpoint') || '') : '';

                if (company) {
                    info.innerHTML = '<strong>Company Site:</strong> ' + company +
                        '<br><strong>Selected Work Point:</strong> ' + (workpoint || '-');
                    info.style.display = 'block';
                } else {
                    info.innerHTML = '';
                    info.style.display = 'none';
                }
            }

            function bindWorkPointCompanyDetails() {
                var createSelect = document.querySelector('#absCreateModal select[name="work_point_id"]');
                var editSelect = document.querySelector('#edit_abs_work_point_id');

                if (createSelect && !createSelect.dataset.companyInfoBound) {
                    createSelect.dataset.companyInfoBound = '1';
                    createSelect.addEventListener('change', function() {
                        showWorkPointCompany('#absCreateModal select[name="work_point_id"]',
                            '#create_work_point_company_info');
                    });
                }

                if (editSelect && !editSelect.dataset.companyInfoBound) {
                    editSelect.dataset.companyInfoBound = '1';
                    editSelect.addEventListener('change', function() {
                        showWorkPointCompany('#edit_abs_work_point_id', '#edit_work_point_company_info');
                    });
                }

                showWorkPointCompany('#absCreateModal select[name="work_point_id"]',
                    '#create_work_point_company_info');
                showWorkPointCompany('#edit_abs_work_point_id', '#edit_work_point_company_info');
            }


            if (window.jQuery) {
                window.jQuery('#absCreateModal').on('shown.bs.modal', function() {
                    initAbsenceModalSelect2('#absCreateModal');
                    bindWorkPointCompanyDetails();
                });

                window.jQuery('#absEditModal').on('shown.bs.modal', function() {
                    initAbsenceModalSelect2('#absEditModal');
                    bindWorkPointCompanyDetails();
                });
            }

            bindWorkPointCompanyDetails();

            document.querySelectorAll('.btn-edit-abs').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.dataset.id;
                    document.getElementById('absEditForm').action =
                        "{{ route('hr.absences.update', ':id') }}".replace(':id', id);
                    document.getElementById('edit_abs_user_id').value = this.dataset.user_id || '';
                    if (window.jQuery) window.jQuery('#edit_abs_user_id').trigger('change');
                    document.getElementById('edit_abs_date').value = this.dataset.date || '';
                    document.getElementById('edit_abs_days').value = this.dataset.days || 1;
                    document.getElementById('edit_abs_reason').value = this.dataset.reason || '';
                    document.getElementById('edit_abs_status').value = this.dataset.status ||
                        'Pending';
                    if (window.jQuery) window.jQuery('#edit_abs_status').trigger('change');
                    if (document.getElementById('edit_abs_work_point_id')) document.getElementById(
                        'edit_abs_work_point_id').value = this.dataset.work_point_id || '';
                    if (window.jQuery) window.jQuery('#edit_abs_work_point_id').trigger('change');
                    showWorkPointCompany('#edit_abs_work_point_id',
                        '#edit_work_point_company_info');
                    $('#absEditModal').modal('show');
                });
            });
            document.querySelectorAll('.btn-delete-abs').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will delete the absence record.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then(function(res) {
                        if (res.isConfirmed) {
                            window.location.href =
                                "{{ route('hr.absences.remove', ':id') }}".replace(':id',
                                    enc);
                        }
                    });
                });
            });
        });
    </script>
@endsection
