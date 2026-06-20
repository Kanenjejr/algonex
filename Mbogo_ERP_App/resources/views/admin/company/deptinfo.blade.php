@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Departments Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('business-admin') }}">Business Administration</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Departments Registration</strong>
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
        <h3 class="mb-2 page-title">Departments Details</h3>
        @can('Register-Department')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary"
                data-toggle="modal" data-target="#deptCreateModal">Add Department</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight mt-5">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                        <h5>Departments Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>location</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($departments as $k => $d)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $d->depCode }}</td>
                                            <td>{{ $d->depName }}</td>
                                            <td>{{ optional($d->company)->company_name ?? '-' }}</td>
                                            <td>{{ optional($d->workpoint)->work_name ?? '-' }}</td>
                                            <td>{{ $d->Status }}</td>
                                            <td>
                                                @can('Edit-Department')
                                                    <button class="btn btn-sm btn-warning btn-edit-dept" data-toggle="modal"
                                                        data-target="#deptEditModal" data-id="{{ encrypt($d->id) }}"
                                                        data-name="{{ $d->depName }}" data-depcode="{{ $d->depCode }}"
                                                        data-company_id="{{ $d->company_id }}"
                                                        data-comp_unit_id="{{ $d->comp_unit_id }}"
                                                        data-work_point_id="{{ $d->work_point_id }}"
                                                        data-status="{{ $d->Status }}">Edit</button>
                                                @endcan
                                                @can('Delete-Department')
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-dept"
                                                        data-id="{{ encrypt($d->id) }}">Remove</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- /.table-responsive -->
                    </div> <!-- /.ibox-content -->
                </div> <!-- /.ibox -->
            </div>
        </div>

        {{-- Create Modal --}}
        <div class="modal fade" id="deptCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="deptCreateForm" action="{{ route('departments.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Department</h5><button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">

                            {{-- @if (in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'])) --}}
                            <div class="form-group">
                                <label>Work Point <span style="color:red">*</span></label>
                                <select name="work_point_id" class="form-control select2_demo_3" required>
                                    <option value="">-- Select work point --</option>
                                    @foreach ($workPoints as $wp)
                                        <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                        @else
                            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
                        @endif --}}

                            <div class="form-group">
                                <label>Company Unit</label>
                                <select name="comp_unit_id" class="form-control select2_demo_3">
                                    <option value="">-- Select unit (optional) --</option>
                                    @foreach ($unities as $u)
                                        <option value="{{ $u->id }}">{{ $u->unit_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Department Code <span style="color:red">*</span></label>
                                <input type="text" name="depCode" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label> Department Name <span style="color:red">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="Status" class="form-control select2_demo_3">
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" onclick="handleConfirmSubmit('deptCreateForm')"
                                class="btn mb-2 btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="modal fade" id="deptEditModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="deptEditForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Department</h5><button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit_dept_id" name="edit_id">

                            {{-- @if (in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'])) --}}
                            <div class="form-group">
                                <label>Work Point <span style="color:red">*</span></label>
                                <select id="edit_work_point_id" name="work_point_id" class="form-control select2_demo_3"
                                    required>
                                    <option value="">-- Select work point --</option>
                                    @foreach ($workPoints as $wp)
                                        <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}"> --}}
                            {{-- @else
              <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
              <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
            @endif --}}

                            <div class="form-group">
                                <label>Company Unit</label>
                                <select id="edit_comp_unit_id" name="comp_unit_id" class="form-control select2_demo_3">
                                    <option value="">-- Select unit (optional) --</option>
                                    @foreach ($unities as $u)
                                        <option value="{{ $u->id }}">{{ $u->unit_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Department Code</label>
                                <input id="edit_depCode" type="text" name="depCode" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Department Name</label>
                                <input id="edit_name" type="text" name="name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select id="edit_Status" name="Status" class="form-control select2_demo_3">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Deleted">Deleted</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit"
                                onclick="handleConfirmSubmit('deptEditForm')"class="btn mb-2 btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            // small safe fallback — if you already have handleConfirmSubmit defined globally, this will not override it
            if (typeof handleConfirmSubmit !== 'function') {
                function handleConfirmSubmit(formId) {
                    // default: just submit form
                    var f = document.getElementById(formId);
                    if (f) f.submit();
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Edit button wiring
                document.querySelectorAll('.btn-edit-dept').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const encId = this.dataset.id;
                        document.getElementById('edit_dept_id').value = encId;
                        document.getElementById('edit_name').value = this.dataset.depName || '';
                        document.getElementById('edit_depCode').value = this.dataset.depcode || '';
                        document.getElementById('edit_Status').value = this.dataset.status || 'Active';

                        const wp = document.getElementById('edit_work_point_id');
                        if (wp) wp.value = this.dataset.work_point_id || '';

                        const cu = document.getElementById('edit_comp_unit_id');
                        if (cu) cu.value = this.dataset.comp_unit_id || '';

                        // set form action to put route with encrypted id
                        const form = document.getElementById('deptEditForm');
                        form.action = "{{ route('departments.update', ':id') }}".replace(':id', encId);
                    });
                });

                // Delete button wiring (SweetAlert confirmation)
                document.querySelectorAll('.btn-delete-dept').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const encId = this.dataset.id;
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will mark the department as Deleted.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it!',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href =
                                    "{{ route('departments.remove', ':id') }}".replace(':id',
                                        encId);
                            }
                        });
                    });
                });

                // initialize select2 (create/edit modal contexts)
                $(document).ready(function() {
                    // general selects that share the same class (create modal will inherit this)
                    $('.select2_demo_3').each(function() {
                        // `.select2_demo_3` are many; init with no dropdownParent so selects outside modals are fine
                        $(this).select2({
                            width: '100%',
                            theme: 'bootstrap4'
                        });
                    });

                    // specifically ensure Select2 dropdownParent is set for modals so dropdowns render correctly
                    $('#deptCreateModal .select2_demo_3').select2({
                        width: '100%',
                        theme: 'bootstrap4',
                        dropdownParent: $('#deptCreateModal')
                    });
                    $('#deptEditModal .select2_demo_3').select2({
                        width: '100%',
                        theme: 'bootstrap4',
                        dropdownParent: $('#deptEditModal')
                    });
                });
            });
        </script>
    @endsection
