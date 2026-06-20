@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Sub Charts Of Accounting Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('accounting') }}">Accounting And Finance</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Sub Charts Of Accounting Registration</strong>
                </li>
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

    <div class="col-12">
        <h3 class="mb-2 page-title">Sub Chart of Accounts Code</h3>
        @can('Register-Sub-Accounting-Code')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openSubCreateModal()">
                Add Sub Code
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox"
                    style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">
                    <div class="ibox-title"
                        style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <h5 style="margin:0; font-weight:800; color:#fff;">Description Table</h5>
                        </div>
                    </div>

                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Root Account</th>
                                        <th>Sub Code</th>
                                        <th>Description</th>
                                        <th>Work Point</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subcharts as $k => $s)
                                        @php
                                            $payload = [
                                                'id' => encrypt($s->id),
                                                'accnt_chart_id' => $s->accnt_chart_id,
                                                'work_point_id' => $s->work_point_id,
                                                'SubCode' => $s->SubCode,
                                                'SubDescription' => $s->SubDescription,
                                                'Status' => $s->Status,
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ optional($s->masterChart)->AccCode ?? '-' }} -
                                                {{ optional($s->masterChart)->AccDescription ?? '' }}</td>
                                            <td>{{ $s->SubCode }}</td>
                                            <td>{{ $s->SubDescription ?? '-' }}</td>
                                            <td>{{ optional($s->workpoint)->work_code ?? '' }}{{ optional($s->workpoint)->work_code ? ' - ' : '' }}{{ optional($s->workpoint)->work_name ?? '-' }}
                                            </td>
                                            <td>{{ $s->Status }}</td>
                                            <td style="white-space:nowrap;">
                                                @can('Edit-Sub-Accounting-Code')
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openSubEdit(@json($payload))'>
                                                        Edit
                                                    </button>
                                                @endcan

                                                @can('Delete-Sub-Accounting-Code')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteSubchart(@json($payload))'>
                                                        Remove
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No sub charts found.</td>
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
    <div class="modal fade" id="subCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="subCreateForm" action="{{ route('accntsubcharts.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Sub Account Code</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Location / Work Point <span class="text-danger">*</span></label>
                                <select name="work_point_id" id="create_work_point_id" class="form-control select2_modal"
                                    required>
                                    <option value="">-- Select location --</option>
                                    @foreach ($workPoints as $wp)
                                        <option value="{{ $wp->id }}">
                                            {{ $wp->work_code }} - {{ $wp->work_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Root Account (2 Digit) <span class="text-danger">*</span></label>
                                <select name="accnt_chart_id" id="create_accnt_chart_id" class="form-control select2_modal"
                                    required>
                                    <option value="">-- Select root chart --</option>
                                    @foreach ($charts as $c)
                                        <option value="{{ $c->id }}" data-code="{{ $c->AccCode }}"
                                            data-name="{{ $c->AccDescription }}">
                                            {{ $c->AccCode }} - {{ $c->AccDescription }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h4>First Root (3 Digit)</h4>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Code <span class="text-danger">*</span></label>
                                <input type="text" name="first_root_code" id="first_root_code" class="form-control"
                                    maxlength="3" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_root_name" id="first_root_name" class="form-control"
                                    required>
                            </div>
                        </div>

                        <hr>
                        <h4>Accounting Code (6 Digit)</h4>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Code <span class="text-danger">*</span></label>
                                <input type="text" name="accounting_code" id="accounting_code" class="form-control"
                                    maxlength="6" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="accounting_name" id="accounting_name" class="form-control"
                                    required>
                            </div>
                        </div>

                        <hr>
                        <h4>Sub Accounting Code (8 Digit)</h4>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Code <span class="text-danger">*</span></label>
                                <input type="text" name="sub_accounting_code" id="sub_accounting_code"
                                    class="form-control" maxlength="8" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="sub_accounting_name" id="sub_accounting_name"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="Status" class="form-control select2_modal" required>
                                <option value="Active">Active</option>
                                <option value="Deleted">Deleted</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="subEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="subEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sub Account Code</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="subEditBody">
                        <div class="text-center">Loading...</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            var chartOptions = [
                @foreach ($charts as $c)
                    {
                        id: "{{ $c->id }}",
                        code: "{{ $c->AccCode }}",
                        name: @json($c->AccDescription)
                    },
                @endforeach
            ];

            var workPointOptions = [
                @foreach ($workPoints as $wp)
                    {
                        id: "{{ $wp->id }}",
                        code: @json($wp->work_code),
                        name: @json($wp->work_name)
                    },
                @endforeach
            ];

            var submittingForms = {};

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

            $('#subCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#subCreateModal');
            });

            $('#subEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#subEditModal');
            });

            function next3Digit(rootCode) {
                return rootCode ? rootCode + '1' : '';
            }

            function next6Digit(firstRootCode) {
                return firstRootCode ? firstRootCode + '01' : '';
            }

            function next8Digit(accountingCode) {
                return accountingCode ? accountingCode + '00' : '';
            }

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function fillNamesByRootName(rootName) {
                $('#first_root_name').val(rootName);
                $('#accounting_name').val(rootName);
                $('#sub_accounting_name').val(rootName);
            }

            function suggestCodesAndNames() {
                var rootSelect = document.getElementById('create_accnt_chart_id');
                if (!rootSelect) return;

                var selected = rootSelect.options[rootSelect.selectedIndex];
                var rootCode = selected ? (selected.getAttribute('data-code') || '') : '';
                var rootName = selected ? (selected.getAttribute('data-name') || '') : '';

                if (rootCode !== '') {
                    var firstRoot = next3Digit(rootCode);
                    var accounting = next6Digit(firstRoot);
                    var subAccounting = next8Digit(accounting);

                    $('#first_root_code').val(firstRoot);
                    $('#accounting_code').val(accounting);
                    $('#sub_accounting_code').val(subAccounting);

                    fillNamesByRootName(rootName);
                }
            }

            window.openSubCreateModal = function() {
                $('#subCreateModal').modal('show');
                setTimeout(function() {
                    initAllModalSelects('#subCreateModal');
                }, 150);
            };

            $(document).on('change', '#create_accnt_chart_id', function() {
                suggestCodesAndNames();
            });

            $(document).on('keyup change', '#first_root_code', function() {
                var v = this.value;
                if (v.length === 3) {
                    $('#accounting_code').val(v + '01');
                    $('#sub_accounting_code').val(v + '0100');

                    var rootSelect = document.getElementById('create_accnt_chart_id');
                    var selected = rootSelect.options[rootSelect.selectedIndex];
                    var rootName = selected ? (selected.getAttribute('data-name') || '') : '';
                    fillNamesByRootName(rootName);
                }
            });

            $(document).on('keyup change', '#accounting_code', function() {
                var v = this.value;
                if (v.length === 6) {
                    $('#sub_accounting_code').val(v + '00');

                    var rootSelect = document.getElementById('create_accnt_chart_id');
                    var selected = rootSelect.options[rootSelect.selectedIndex];
                    var rootName = selected ? (selected.getAttribute('data-name') || '') : '';
                    fillNamesByRootName(rootName);
                }
            });

            window.openSubEdit = function(row) {
                var html = '';

                html += '<div class="row">';
                html += '<div class="form-group col-md-6">';
                html += '<label>Location / Work Point <span class="text-danger">*</span></label>';
                html += '<select name="work_point_id" class="form-control select2_modal" required>';
                html += '<option value="">-- Select location --</option>';
                for (var i = 0; i < workPointOptions.length; i++) {
                    var wpLabel = (workPointOptions[i].code ? workPointOptions[i].code + " - " : "") +
                        workPointOptions[i].name;
                    html += '<option value="' + workPointOptions[i].id + '"' + (String(row.work_point_id) ===
                            String(workPointOptions[i].id) ? ' selected' : '') + '>' + escapeHtml(wpLabel) +
                        '</option>';
                }
                html += '</select>';
                html += '</div>';

                html += '<div class="form-group col-md-6">';
                html += '<label>Root Account <span class="text-danger">*</span></label>';
                html += '<select name="accnt_chart_id" class="form-control select2_modal" required>';
                html += '<option value="">-- Select root chart --</option>';
                for (var j = 0; j < chartOptions.length; j++) {
                    html += '<option value="' + chartOptions[j].id + '"' + (String(row.accnt_chart_id) ===
                        String(chartOptions[j].id) ? ' selected' : '') + '>' + escapeHtml(chartOptions[j]
                        .code + ' - ' + chartOptions[j].name) + '</option>';
                }
                html += '</select>';
                html += '</div>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Sub Code <span class="text-danger">*</span></label>';
                html += '<input type="text" name="SubCode" class="form-control" value="' + escapeHtml(row
                    .SubCode || '') + '" required>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Description <span class="text-danger">*</span></label>';
                html += '<input type="text" name="SubDescription" class="form-control" value="' + escapeHtml(row
                    .SubDescription || '') + '" required>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Status <span class="text-danger">*</span></label>';
                html += '<select name="Status" class="form-control select2_modal" required>';
                html += '<option value="Active"' + (row.Status === 'Active' ? ' selected' : '') +
                    '>Active</option>';
                html += '<option value="Deleted"' + (row.Status === 'Deleted' ? ' selected' : '') +
                    '>Deleted</option>';
                html += '</select>';
                html += '</div>';

                $('#subEditBody').html(html);
                $('#subEditForm').attr('action', "{{ url('/admin/accnt-subcharts') }}/" + encodeURIComponent(
                    row.id));
                $('#subEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#subEditModal');
                }, 150);
            };

            window.deleteSubchart = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This sub account will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href = "{{ url('/admin/accnt-subcharts/remove') }}/" +
                            encodeURIComponent(row.id);
                    }
                });
            };

            $('#subCreateForm').on('submit', function(e) {
                var form = this;
                if (submittingForms['subCreateForm']) {
                    return true;
                }

                e.preventDefault();

                var required = form.querySelectorAll('[required]');
                for (var i = 0; i < required.length; i++) {
                    var el = required[i];
                    if (!el.value || el.value.toString().trim() === '') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Missing', 'Please fill all required fields before submitting.',
                                'warning');
                        } else {
                            alert('Please fill all required fields before submitting.');
                        }
                        return false;
                    }
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Confirm',
                        text: 'Proceed with this action?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then(function(res) {
                        if (res.isConfirmed) {
                            submittingForms['subCreateForm'] = true;
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Proceed with this action?')) {
                        submittingForms['subCreateForm'] = true;
                        form.submit();
                    }
                }

                return false;
            });

            $('#subEditForm').on('submit', function(e) {
                var form = this;
                if (submittingForms['subEditForm']) {
                    return true;
                }

                e.preventDefault();

                var required = form.querySelectorAll('[required]');
                for (var i = 0; i < required.length; i++) {
                    var el = required[i];
                    if (!el.value || el.value.toString().trim() === '') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Missing', 'Please fill all required fields before submitting.',
                                'warning');
                        } else {
                            alert('Please fill all required fields before submitting.');
                        }
                        return false;
                    }
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Confirm',
                        text: 'Proceed with this action?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then(function(res) {
                        if (res.isConfirmed) {
                            submittingForms['subEditForm'] = true;
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Proceed with this action?')) {
                        submittingForms['subEditForm'] = true;
                        form.submit();
                    }
                }

                return false;
            });
        });
    </script>
@endsection
