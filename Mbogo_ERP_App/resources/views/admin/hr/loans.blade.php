@extends('layouts.AdminMaster')
@section('content')
    <style>
        #loanCreateModal .select2-container,
        #loanEditModal .select2-container {
            width: 100% !important;
        }

        .select2-container--open,
        .select2-dropdown {
            z-index: 999999 !important;
        }

        .loan-location-info {
            margin-top: 6px;
            padding: 8px 10px;
            border: 1px solid #d9edf7;
            background: #f4fbff;
            color: #31708f;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Loans Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('hr') }}">Human Resource</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Staff/User Loans</strong>
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

    <div class="col-12">
        <h3 class="mb-2 page-title">Loans & Advances</h3>
        @can('Register-Loan')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#loanCreateModal">Add Loan/Advance</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>Loans Table</h5>
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
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Installments</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($loans as $k => $l)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ optional($l->user)->name ?? '-' }}</td>
                                            <td>
                                                {{ optional($l->company)->company_name ?? (optional(optional($l->workpoint)->company)->company_name ?? '-') }}
                                                @if (optional($l->company)->company_code || optional(optional($l->workpoint)->company)->company_code)
                                                    <br><small>{{ optional($l->company)->company_code ?? optional(optional($l->workpoint)->company)->company_code }}</small>
                                                @endif
                                            </td>
                                            <td>{{ optional($l->workpoint)->work_name ?? '-' }}</td>
                                            <td>{{ $l->type }}</td>
                                            <td>{{ number_format($l->amount, 2) }}</td>
                                            <td>{{ number_format($l->balance, 2) }}</td>
                                            <td>{{ $l->installments }}</td>
                                            <td>{{ $l->status }}</td>
                                            <td>
                                                @can('Edit-Loan')
                                                    <button class="btn btn-sm btn-warning btn-edit-loan"
                                                        data-id="{{ encrypt($l->id) }}" data-user_id="{{ $l->user_id }}"
                                                        data-type="{{ $l->type }}" data-amount="{{ $l->amount }}"
                                                        data-installments="{{ $l->installments }}"
                                                        data-status="{{ $l->status ?? 'Active' }}"
                                                        data-work_point_id="{{ $l->work_point_id }}">Edit</button>
                                                @endcan
                                                @can('Delete-Loan')
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-loan"
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

    <!-- Create Modal -->
    <div class="modal fade" id="loanCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="loanCreateForm" action="{{ route('hr.loans.store') }}" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Loan/Advance</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Work Point</label>
                            <select id="loan_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">-- Select work point --</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $wpCompanyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $wpCompanyCode = optional($wp->company)->company_code ?? '';
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company="{{ $wpCompanyName }}"
                                        data-company-code="{{ $wpCompanyCode }}" data-workpoint="{{ $wp->work_name }}">
                                        {{ $wp->work_name }} -
                                        {{ $wpCompanyName }}{{ $wpCompanyCode ? ' (' . $wpCompanyCode . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="loan_work_point_details" class="loan-location-info" style="display:none;"></div>
                        </div>

                        <div class="form-group">
                            <label>Staff</label>
                            <select name="user_id" class="form-control select2_demo_3" required>
                                <option value="">-- Select staff --</option>
                                @foreach ($staffUsers as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} - {{ optional($s->company)->company_name ?? 'NO COMPANY' }} /
                                        {{ optional($s->workpoint)->work_name ?? 'NO WORK POINT' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Type</label><select name="type"
                                class="form-control select2_demo_3">
                                <option>Advance</option>
                                <option>Loan</option>
                            </select></div>
                        <div class="form-group"><label>Amount</label><input type="number" step="0.01" name="amount"
                                class="form-control" required></div>
                        <div class="form-group"><label>Installments</label><input type="number" name="installments"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('loanCreateForm')" class="btn btn-primary">Submit</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="loanEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="loanEditForm" method="POST">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Loan</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_loan_id" name="edit_id">
                        <div class="form-group">
                            <label>Work Point</label>
                            <select id="edit_loan_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">--select--</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $wpCompanyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $wpCompanyCode = optional($wp->company)->company_code ?? '';
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company="{{ $wpCompanyName }}"
                                        data-company-code="{{ $wpCompanyCode }}" data-workpoint="{{ $wp->work_name }}">
                                        {{ $wp->work_name }} -
                                        {{ $wpCompanyName }}{{ $wpCompanyCode ? ' (' . $wpCompanyCode . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="edit_loan_work_point_details" class="loan-location-info" style="display:none;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Staff</label>
                            <select id="edit_loan_user_id" name="user_id" class="form-control select2_demo_3" required>
                                <option value="">--select staff--</option>
                                @foreach ($staffUsers as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} - {{ optional($s->company)->company_name ?? 'NO COMPANY' }} /
                                        {{ optional($s->workpoint)->work_name ?? 'NO WORK POINT' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Type</label><select id="edit_loan_type" name="type"
                                class="form-control select2_demo_3">
                                <option>Advance</option>
                                <option>Loan</option>
                            </select></div>
                        <div class="form-group"><label>Amount</label><input id="edit_loan_amount" name="amount"
                                type="number" step="0.01" class="form-control"></div>
                        <div class="form-group"><label>Installments</label><input id="edit_loan_installments"
                                name="installments" type="number" class="form-control"></div>
                        <div class="form-group"><label>Status</label><select id="edit_loan_status" name="status"
                                class="form-control select2_demo_3">
                                <option>Active</option>
                                <option>Paid</option>
                                <option>Defaulted</option>
                                <option>Deleted</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('loanEditForm')" class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tempLoanEditData = null;

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

            function showWorkPointDetails(selectId, targetId) {
                var select = document.getElementById(selectId);
                var target = document.getElementById(targetId);
                if (!select || !target) return;

                var selected = select.options[select.selectedIndex];
                if (!selected || !selected.value) {
                    target.style.display = 'none';
                    target.innerHTML = '';
                    return;
                }

                var company = selected.getAttribute('data-company') || '-';
                var companyCode = selected.getAttribute('data-company-code') || '';
                var workpoint = selected.getAttribute('data-workpoint') || '-';

                target.innerHTML = 'Company Site: <strong>' + company + (companyCode ? ' (' + companyCode + ')' :
                    '') + '</strong><br>Selected Work Point: <strong>' + workpoint + '</strong>';
                target.style.display = 'block';
            }

            $('.select2_demo_3').each(function() {
                var $this = $(this);
                if ($this.closest('#loanCreateModal').length) {
                    initSelect2WithParent($this, '#loanCreateModal');
                    return;
                }
                if ($this.closest('#loanEditModal').length) {
                    initSelect2WithParent($this, '#loanEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $(document).on('shown.bs.modal', '#loanCreateModal', function() {
                var $m = $(this);
                if ($m.find('form')[0]) $m.find('form')[0].reset();
                $m.find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#loanCreateModal');
                    $(this).val(null).trigger('change');
                });
                showWorkPointDetails('loan_work_point_id', 'loan_work_point_details');
            });

            $(document).on('change', '#loan_work_point_id', function() {
                showWorkPointDetails('loan_work_point_id', 'loan_work_point_details');
            });

            $(document).on('change', '#edit_loan_work_point_id', function() {
                showWorkPointDetails('edit_loan_work_point_id', 'edit_loan_work_point_details');
            });

            $(document).on('shown.bs.modal', '#loanEditModal', function() {
                var $m = $(this);
                $m.find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#loanEditModal');
                });
                if (tempLoanEditData) {
                    if (typeof tempLoanEditData.work_point_id !== 'undefined') $('#edit_loan_work_point_id')
                        .val(tempLoanEditData.work_point_id).trigger('change');
                    if (typeof tempLoanEditData.user_id !== 'undefined') $('#edit_loan_user_id').val(
                        tempLoanEditData.user_id).trigger('change');
                    tempLoanEditData = null;
                }
                showWorkPointDetails('edit_loan_work_point_id', 'edit_loan_work_point_details');
            });

            document.querySelectorAll('.btn-edit-loan').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    document.getElementById('edit_loan_id').value = enc || '';
                    document.getElementById('edit_loan_type').value = this.dataset.type || 'Loan';
                    document.getElementById('edit_loan_amount').value = this.dataset.amount || '';
                    document.getElementById('edit_loan_installments').value = this.dataset
                        .installments || '';
                    document.getElementById('edit_loan_status').value = this.dataset.status ||
                        'Active';
                    tempLoanEditData = {
                        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ?
                            this.dataset.work_point_id : null,
                        user_id: (typeof this.dataset.user_id !== 'undefined') ? this.dataset
                            .user_id : null
                    };
                    var form = document.getElementById('loanEditForm');
                    form.action = "{{ route('hr.loans.update', ':id') }}".replace(':id', enc);
                    $('#loanEditModal').modal('show');
                });
            });

            document.querySelectorAll('.btn-delete-loan').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will mark loan as Deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then(function(res) {
                        if (res.isConfirmed) window.location.href =
                            "{{ route('hr.loans.remove', ':id') }}".replace(':id', enc);
                    });
                });
            });
        });
    </script>
@endsection
