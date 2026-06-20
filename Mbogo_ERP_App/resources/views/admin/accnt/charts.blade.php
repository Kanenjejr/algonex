@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Charts Of Accounting Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('accounting') }}">Accounting And Finance</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Charts Of Accounting Registration</strong>
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
        <h3 class="mb-2 page-title">Accounting Charts</h3>
        @can('Register-Accounting-Code')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary"
                data-toggle="modal" data-target="#accCreateModal">Add Account</button>
        @endcan
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                        <h5>Account Chart Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>AccCode</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Company</th>
                                        <th>Work Point</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($charts as $k => $c)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $c->AccCode }}</td>
                                            <td>{{ $c->AccDescription }}</td>
                                            <td>{{ $c->AccType }}</td>
                                            <td>{{ optional($c->company)->company_name ?? '-' }}</td>
                                            <td>{{ optional($c->workpoint)->work_name ?? '-' }}</td>
                                            <td>{{ $c->Status }}</td>
                                            <td>
                                                @can('Edit-Accounting-Code')
                                                    <button class="btn btn-sm btn-warning btn-edit-acc" data-toggle="modal"
                                                        data-target="#accEditModal" data-id="{{ encrypt($c->id) }}"
                                                        data-acccode="{{ $c->AccCode }}" data-desc="{{ $c->AccDescription }}"
                                                        data-acctype="{{ $c->AccType }}"
                                                        data-work_point_id="{{ $c->work_point_id }}"
                                                        data-status="{{ $c->Status }}">Edit</button>
                                                @endcan

                                                @can('Delete-Accounting-Code')
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-acc"
                                                        data-id="{{ encrypt($c->id) }}">Remove</a>
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

    <div class="modal fade" id="accCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="accCreateForm" action="{{ route('accntcharts.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Root Account Chart</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Location / Work Point <span style="color:red">*</span></label>
                            <select name="work_point_id" class="form-control select2_demo_3" required>
                                <option value="">-- Select location --</option>
                                @foreach ($workPoints as $wp)
                                    <option value="{{ $wp->id }}">{{ $wp->work_code }} - {{ $wp->work_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Root Code (2 Digit) <span style="color:red">*</span></label>
                            <input type="text" name="AccCode" class="form-control" maxlength="2" required>
                        </div>

                        <div class="form-group">
                            <label>Root Name <span style="color:red">*</span></label>
                            <input type="text" name="AccDescription" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Type <span style="color:red">*</span></label>
                            <select name="AccType" class="form-control select2_demo_3" required>
                                <option value="EQUITY">EQUITY</option>
                                <option value="CAPITAL">CAPITAL</option>
                                <option value="INVENTORY">INVENTORY</option>
                                <option value="ADJUSTMENTS">ADJUSTMENTS</option>
                                <option value="FINANCIAL">FINANCIAL</option>
                                <option value="EXPENSES">EXPENSES</option>
                                <option value="REVENUE">REVENUE</option>
                                <option value="OTHER">OTHER</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="Status" class="form-control select2_demo_3">
                                <option value="Active" selected>Active</option>
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

    <div class="modal fade" id="accEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="accEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Root Account Chart</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Location / Work Point <span style="color:red">*</span></label>
                            <select id="edit_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                required>
                                <option value="">-- Select location --</option>
                                @foreach ($workPoints as $wp)
                                    <option value="{{ $wp->id }}">{{ $wp->work_code }} - {{ $wp->work_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Root Code (2 Digit)</label>
                            <input id="edit_AccCode" type="text" name="AccCode" class="form-control" maxlength="2"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Root Name</label>
                            <input id="edit_AccDescription" type="text" name="AccDescription" class="form-control"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Type</label>
                            <select id="edit_AccType" name="AccType" class="form-control select2_demo_3" required>
                                <option value="EQUITY">EQUITY</option>
                                <option value="CAPITAL">CAPITAL</option>
                                <option value="INVENTORY">INVENTORY</option>
                                <option value="ADJUSTMENTS">ADJUSTMENTS</option>
                                <option value="FINANCIAL">FINANCIAL</option>
                                <option value="EXPENSES">EXPENSES</option>
                                <option value="REVENUE">REVENUE</option>
                                <option value="OTHER">OTHER</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select id="edit_Status" name="Status" class="form-control select2_demo_3" required>
                                <option value="Active">Active</option>
                                <option value="Deleted">Deleted</option>
                            </select>
                        </div>

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
            $('.select2_demo_3').select2({
                width: '100%',
                theme: 'bootstrap4'
            });

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
            $('#accCreateModal').on('shown.bs.modal', function() {
                $(this).find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#accCreateModal');
                });
            });
            $('#accEditModal').on('shown.bs.modal', function() {
                $(this).find('.select2_demo_3').each(function() {
                    initSelect2WithParent($(this), '#accEditModal');
                });
            });
            document.querySelectorAll('.btn-edit-acc').forEach(btn => {
                btn.addEventListener('click', function() {
                    const encId = this.getAttribute('data-id');
                    document.getElementById('edit_AccCode').value = this.getAttribute(
                        'data-acccode') || '';
                    document.getElementById('edit_AccDescription').value = this.getAttribute(
                        'data-desc') || '';
                    $('#edit_AccType').val(this.getAttribute('data-acctype') || '').trigger(
                        'change');
                    $('#edit_Status').val(this.getAttribute('data-status') || 'Active').trigger(
                        'change');
                    $('#edit_work_point_id').val(this.getAttribute('data-work_point_id') || '')
                        .trigger('change');
                    const form = document.getElementById('accEditForm');
                    form.action = "{{ route('accntcharts.update', ':id') }}".replace(':id', encId);
                });
            });
            document.querySelectorAll('.btn-delete-acc').forEach(btn => {
                btn.addEventListener('click', function() {
                    const encId = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will mark the account as Deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href =
                                "{{ route('accntcharts.remove', ':id') }}".replace(':id',
                                    encId);
                        }
                    });
                });
            });
            window.handleConfirmSubmit = function(formId) {
                const form = document.getElementById(formId);
                const required = form.querySelectorAll('[required]');
                for (let i = 0; i < required.length; i++) {
                    if (!required[i].value || required[i].value.toString().trim() === '') {
                        Swal.fire('Missing', 'Please fill all required fields before submitting.', 'warning');
                        return;
                    }
                }
                Swal.fire({
                    title: 'Confirm',
                    text: "Proceed with this action?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                }).then((res) => {
                    if (res.isConfirmed) form.submit();
                });
            };

        });
    </script>
@endsection
