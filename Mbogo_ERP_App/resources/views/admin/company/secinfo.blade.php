@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Section Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('business-admin') }}">Business Administration</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Section Registration</strong>
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
        <h3 class="mb-2 page-title">Sections Details</h3>
        @can('Register-Section')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary"
                data-toggle="modal" data-target="#secCreateModal">Add Section</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                        <h5>Sections Table</h5>
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
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sections as $k => $s)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $s->secCode }}</td>
                                            <td>{{ $s->secName }}</td>
                                            <td>{{ optional($s->company)->company_name ?? '-' }}</td>
                                            <td>{{ optional($s->workpoint)->location_name ?? '-' }}</td>
                                            <td>{{ optional($s->dept)->depName ?? '-' }}</td>
                                            <td>{{ $s->Status }}</td>
                                            <td>
                                                @can('Edit-Section')
                                                    <button class="btn btn-sm btn-warning btn-edit-sec" data-toggle="modal"
                                                        data-target="#secEditModal" data-id="{{ encrypt($s->id) }}"
                                                        data-name="{{ $s->name }}" data-seccode="{{ $s->secCode }}"
                                                        data-company_id="{{ $s->company_id }}"
                                                        data-comp_unit_id="{{ $s->comp_unit_id }}"
                                                        data-work_point_id="{{ $s->work_point_id }}"
                                                        data-dept_id="{{ $s->dept_id }}"
                                                        data-status="{{ $s->Status }}">Edit</button>
                                                @endcan
                                                @can('Delete-Section')
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-sec"
                                                        data-id="{{ encrypt($s->id) }}">Remove</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Create Modal -->
                    <div class="modal fade" id="secCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form id="secCreateForm" action="{{ route('sections.store') }}" method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Add Section</h5><button type="button" class="close"
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
                                            <label>Department <span style="color:red">*</span></label>
                                            <select name="dept_id" class="form-control select2_demo_3" required>
                                                <option value="">-- Select department --</option>
                                                @foreach ($departments as $d)
                                                    <option value="{{ $d->id }}">{{ $d->depName }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Section Code</label>
                                            <input type="text" name="secCode" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label> Section Name <span style="color:red">*</span></label>
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
                                        <button type="button" class="btn mb-2 btn-secondary"
                                            data-dismiss="modal">Close</button>
                                        <button type="submit" onclick="handleConfirmSubmit('secCreateForm')"
                                            class="btn mb-2 btn-primary">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="secEditModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form id="secEditForm" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Section</h5><button type="button" class="close"
                                            data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" id="edit_sec_id" name="edit_id">

                                        {{-- @if (in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'])) --}}
                                        <div class="form-group">
                                            <label>Work Point <span style="color:red">*</span></label>
                                            <select id="edit_work_point_id" name="work_point_id"
                                                class="form-control select2_demo_3" required>
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
                                            <select id="edit_comp_unit_id" name="comp_unit_id"
                                                class="form-control select2_demo_3">
                                                <option value="">-- Select unit (optional) --</option>
                                                @foreach ($unities as $u)
                                                    <option value="{{ $u->id }}">{{ $u->unit_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Department</label>
                                            <select id="edit_dept_id" name="dept_id" class="form-control select2_demo_3"
                                                required>
                                                <option value="">-- Select department --</option>
                                                @foreach ($departments as $d)
                                                    <option value="{{ $d->id }}">{{ $d->depName }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Section Code</label>
                                            <input id="edit_secCode" type="text" name="secCode" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Section Name</label>
                                            <input id="edit_name" type="text" name="name" class="form-control"
                                                required>
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
                                        <button type="button" class="btn mb-2 btn-secondary"
                                            data-dismiss="modal">Close</button>
                                        <button type="submit"
                                            onclick="handleConfirmSubmit('secEditForm')"class="btn mb-2 btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            document.querySelectorAll('.btn-edit-sec').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const encId = this.dataset.id;
                                    document.getElementById('edit_sec_id').value = encId;
                                    document.getElementById('edit_name').value = this.dataset.secName || '';
                                    document.getElementById('edit_secCode').value = this.dataset.secCode || '';
                                    document.getElementById('edit_Status').value = this.dataset.status || 'Active';

                                    const wp = document.getElementById('edit_work_point_id');
                                    if (wp) wp.value = this.dataset.work_point_id || '';

                                    const cu = document.getElementById('edit_comp_unit_id');
                                    if (cu) cu.value = this.dataset.comp_unit_id || '';

                                    const dept = document.getElementById('edit_dept_id');
                                    if (dept) dept.value = this.dataset.dept_id || '';

                                    // set form action to put route with encrypted id
                                    const form = document.getElementById('secEditForm');
                                    form.action = "{{ route('sections.update', ':id') }}".replace(':id', encId);
                                });
                            });

                            document.querySelectorAll('.btn-delete-sec').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const encId = this.dataset.id;
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "This will mark the section as Deleted.",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete it!',
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = "{{ route('sections.remove', ':id') }}"
                                                .replace(':id', encId);
                                        }
                                    });
                                });
                            });

                            // initialize select2 if you're using it
                            $(document).ready(function() {
                                $('.select2_demo_3').select2({
                                    width: '100%',
                                    theme: 'bootstrap4',
                                    dropdownParent: $('#secCreateModal')
                                });
                                $('#edit_work_point_id').select2({
                                    width: '100%',
                                    theme: 'bootstrap4',
                                    dropdownParent: $('#secEditModal')
                                });
                                $('#edit_comp_unit_id').select2({
                                    width: '100%',
                                    theme: 'bootstrap4',
                                    dropdownParent: $('#secEditModal')
                                });
                                $('#edit_dept_id').select2({
                                    width: '100%',
                                    theme: 'bootstrap4',
                                    dropdownParent: $('#secEditModal')
                                });
                            });
                        });
                    </script>
                @endsection
