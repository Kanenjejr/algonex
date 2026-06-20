@extends('layouts.AdminMaster')
@section('content')
    <style>
        #adjCreateModal .select2-container,
        #adjEditModal .select2-container {
            width: 100% !important;
        }

        .select2-container--open,
        .select2-dropdown {
            z-index: 999999 !important;
        }

        .modal-open .select2-container--open {
            z-index: 999999 !important;
        }

        .workpoint-company-box {
            margin-top: 8px;
            padding: 8px 10px;
            border-radius: 6px;
            background: #f3f6fb;
            border: 1px solid #d8e1ef;
            font-size: 12px;
            color: #1f2937;
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Allowances & Bonuses</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li><a href="{{ route('hr') }}">Human Resource</a></li><span
                    style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Staff Allowances & Bonuses</strong></li>
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

    @php
        $workPoints = collect($workPoints ?? []);
        $staffUsers = collect($staffUsers ?? []);
        $adjustments = collect($adjustments ?? []);
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Allowances & Bonuses</h3>
        @can('Register-Payroll')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#adjCreateModal">Add Allowance/Bonus</button>
        @endcan
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>Allowances & Bonuses Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Period</th>
                                        <th>Company Site</th>
                                        <th>Work Point</th>
                                        <th>Staff</th>
                                        <th>Type</th>
                                        <th>Calculation</th>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($adjustments as $k => $a)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $a->period }}</td>
                                            <td>
                                                {{ optional($a->company)->company_name ?? (optional(optional($a->workpoint)->company)->company_name ?? '-') }}
                                                @if (optional($a->company)->company_code || optional(optional($a->workpoint)->company)->company_code)
                                                    <br><small>{{ optional($a->company)->company_code ?? optional(optional($a->workpoint)->company)->company_code }}</small>
                                                @endif
                                            </td>
                                            <td>{{ optional($a->workpoint)->work_name ?? '-' }}</td>
                                            <td>{{ optional($a->user)->name ?? '-' }}</td>
                                            <td>{{ $a->type }}</td>
                                            <td>{{ $a->calc_type }}</td>
                                            <td>{{ $a->rate ? $a->rate . '%' : '-' }}</td>
                                            <td class="text-right">{{ number_format($a->amount, 2) }}</td>
                                            <td>{{ $a->status }}</td>
                                            <td>
                                                @can('Edit-Payroll')
                                                    <button class="btn btn-sm btn-warning btn-edit-adj"
                                                        data-id="{{ encrypt($a->id) }}" data-period="{{ $a->period }}"
                                                        data-user_id="{{ $a->user_id }}"
                                                        data-work_point_id="{{ $a->work_point_id }}"
                                                        data-type="{{ $a->type }}" data-calc_type="{{ $a->calc_type }}"
                                                        data-rate="{{ $a->rate }}" data-amount="{{ $a->amount }}"
                                                        data-status="{{ $a->status }}"
                                                        data-note="{{ $a->note }}">Edit</button>
                                                @endcan
                                                @can('Delete-Payroll')
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-adj"
                                                        data-id="{{ encrypt($a->id) }}">Remove</a>
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

    <div class="modal fade" id="adjCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="adjCreateForm" action="{{ route('hr.salary-adjustments.store') }}" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Allowance/Bonus</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Work Point</label>
                            <select id="create_adj_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">-- Select Work Point --</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $companyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $companyCode = optional($wp->company)->company_code ?? '';
                                        $label = trim(
                                            ($wp->work_name ?? '-') .
                                                ' - ' .
                                                $companyName .
                                                ($companyCode ? ' (' . $companyCode . ')' : ''),
                                        );
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company-name="{{ $companyName }}"
                                        data-company-code="{{ $companyCode }}" data-work-name="{{ $wp->work_name }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="create_adj_workpoint_details" class="workpoint-company-box" style="display:none;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Staff</label>
                            <select name="user_id" class="form-control select2_demo_3" required>
                                <option value="">-- Select staff --</option>
                                @foreach ($staffUsers as $s)
                                    @php
                                        $staffCompany = optional($s->company)->company_name ?? 'NO COMPANY';
                                        $staffWorkPoint = optional($s->workpoint)->work_name ?? 'NO WORK POINT';
                                    @endphp
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} - {{ $staffCompany }} / {{ $staffWorkPoint }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group"><label>Period</label><input type="month" name="period"
                                class="form-control" required></div>
                        <div class="form-group"><label>Type</label><select name="type"
                                class="form-control select2_demo_3">
                                <option>Allowance</option>
                                <option>Bonus</option>
                            </select></div>
                        <div class="form-group"><label>Calculation Type</label><select name="calc_type"
                                class="form-control select2_demo_3">
                                <option>Fixed</option>
                                <option>Percent</option>
                            </select></div>
                        <div class="form-group"><label>Rate (%) for Percent</label><input type="number" step="0.01"
                                name="rate" class="form-control" placeholder="Example 2 or 5"></div>
                        <div class="form-group"><label>Amount for Fixed</label><input type="number" step="0.01"
                                name="amount" class="form-control" placeholder="Example 100000"></div>
                        <div class="form-group"><label>Note</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('adjCreateForm')" class="btn btn-primary">Submit</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="adjEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="adjEditForm" method="POST">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Allowance/Bonus</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Work Point</label>
                            <select id="edit_adj_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">-- Select Work Point --</option>
                                @foreach ($workPoints as $wp)
                                    @php
                                        $companyName = optional($wp->company)->company_name ?? 'NO COMPANY SITE';
                                        $companyCode = optional($wp->company)->company_code ?? '';
                                        $label = trim(
                                            ($wp->work_name ?? '-') .
                                                ' - ' .
                                                $companyName .
                                                ($companyCode ? ' (' . $companyCode . ')' : ''),
                                        );
                                    @endphp
                                    <option value="{{ $wp->id }}" data-company-name="{{ $companyName }}"
                                        data-company-code="{{ $companyCode }}" data-work-name="{{ $wp->work_name }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="edit_adj_workpoint_details" class="workpoint-company-box" style="display:none;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Staff</label>
                            <select id="edit_adj_user_id" name="user_id" class="form-control select2_demo_3" required>
                                <option value="">-- Select staff --</option>
                                @foreach ($staffUsers as $s)
                                    @php
                                        $staffCompany = optional($s->company)->company_name ?? 'NO COMPANY';
                                        $staffWorkPoint = optional($s->workpoint)->work_name ?? 'NO WORK POINT';
                                    @endphp
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} - {{ $staffCompany }} / {{ $staffWorkPoint }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group"><label>Period</label><input id="edit_adj_period" type="month"
                                name="period" class="form-control" required></div>
                        <div class="form-group"><label>Type</label><select id="edit_adj_type" name="type"
                                class="form-control select2_demo_3">
                                <option>Allowance</option>
                                <option>Bonus</option>
                            </select></div>
                        <div class="form-group"><label>Calculation Type</label><select id="edit_adj_calc_type"
                                name="calc_type" class="form-control select2_demo_3">
                                <option>Fixed</option>
                                <option>Percent</option>
                            </select></div>
                        <div class="form-group"><label>Rate (%)</label><input id="edit_adj_rate" type="number"
                                step="0.01" name="rate" class="form-control"></div>
                        <div class="form-group"><label>Amount</label><input id="edit_adj_amount" type="number"
                                step="0.01" name="amount" class="form-control"></div>
                        <div class="form-group"><label>Status</label><select id="edit_adj_status" name="status"
                                class="form-control select2_demo_3">
                                <option>Active</option>
                                <option>Inactive</option>
                                <option>Deleted</option>
                            </select></div>
                        <div class="form-group"><label>Note</label>
                            <textarea id="edit_adj_note" name="note" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('adjEditForm')" class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tempAdjEditData = null;

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

                var opt = select.options[select.selectedIndex];
                if (!opt || !select.value) {
                    target.style.display = 'none';
                    target.innerHTML = '';
                    return;
                }

                var companyName = opt.getAttribute('data-company-name') || '-';
                var companyCode = opt.getAttribute('data-company-code') || '';
                var workName = opt.getAttribute('data-work-name') || '-';

                target.style.display = 'block';
                target.innerHTML = '<strong>Company Site:</strong> ' + companyName + (companyCode ? ' (' +
                        companyCode + ')' : '') +
                    '<br><strong>Selected Work Point:</strong> ' + workName;
            }

            if (window.jQuery) {
                $('.select2_demo_3').each(function() {
                    var $this = $(this);
                    if ($this.closest('#adjCreateModal').length) {
                        initSelect2WithParent($this, '#adjCreateModal');
                        return;
                    }
                    if ($this.closest('#adjEditModal').length) {
                        initSelect2WithParent($this, '#adjEditModal');
                        return;
                    }
                    initSelect2WithParent($this, null);
                });

                $(document).on('shown.bs.modal', '#adjCreateModal', function() {
                    var $m = $(this);
                    if ($m.find('form')[0]) $m.find('form')[0].reset();
                    $m.find('.select2_demo_3').each(function() {
                        initSelect2WithParent($(this), '#adjCreateModal');
                        $(this).val(null).trigger('change');
                    });
                    showWorkPointDetails('create_adj_work_point_id', 'create_adj_workpoint_details');
                });

                $(document).on('shown.bs.modal', '#adjEditModal', function() {
                    var $m = $(this);
                    $m.find('.select2_demo_3').each(function() {
                        initSelect2WithParent($(this), '#adjEditModal');
                    });

                    if (tempAdjEditData) {
                        $('#edit_adj_work_point_id').val(tempAdjEditData.work_point_id || '').trigger(
                            'change');
                        $('#edit_adj_user_id').val(tempAdjEditData.user_id || '').trigger('change');
                        $('#edit_adj_type').val(tempAdjEditData.type || 'Allowance').trigger('change');
                        $('#edit_adj_calc_type').val(tempAdjEditData.calc_type || 'Fixed').trigger(
                        'change');
                        $('#edit_adj_status').val(tempAdjEditData.status || 'Active').trigger('change');
                        tempAdjEditData = null;
                    }

                    showWorkPointDetails('edit_adj_work_point_id', 'edit_adj_workpoint_details');
                });

                $(document).on('change', '#create_adj_work_point_id', function() {
                    showWorkPointDetails('create_adj_work_point_id', 'create_adj_workpoint_details');
                });

                $(document).on('change', '#edit_adj_work_point_id', function() {
                    showWorkPointDetails('edit_adj_work_point_id', 'edit_adj_workpoint_details');
                });
            }

            document.querySelectorAll('.btn-edit-adj').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.dataset.id;
                    document.getElementById('adjEditForm').action =
                        "{{ route('hr.salary-adjustments.update', ':id') }}".replace(':id', id);
                    document.getElementById('edit_adj_period').value = this.dataset.period || '';
                    document.getElementById('edit_adj_rate').value = this.dataset.rate || '';
                    document.getElementById('edit_adj_amount').value = this.dataset.amount || 0;
                    document.getElementById('edit_adj_note').value = this.dataset.note || '';

                    tempAdjEditData = {
                        work_point_id: this.dataset.work_point_id || '',
                        user_id: this.dataset.user_id || '',
                        type: this.dataset.type || 'Allowance',
                        calc_type: this.dataset.calc_type || 'Fixed',
                        status: this.dataset.status || 'Active'
                    };

                    $('#adjEditModal').modal('show');
                });
            });

            document.querySelectorAll('.btn-delete-adj').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                            title: 'Are you sure?',
                            text: "This will remove this adjustment.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes'
                        })
                        .then(function(res) {
                            if (res.isConfirmed) {
                                window.location.href =
                                    "{{ route('hr.salary-adjustments.remove', ':id') }}"
                                    .replace(':id', enc);
                            }
                        });
                });
            });
        });
    </script>
@endsection
