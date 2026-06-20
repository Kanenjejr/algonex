@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Staff/Users Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('hr') }}">Human Resource</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Staff/Users Registration</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
      <h2>Current Date</h2>
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">
          <strong>
            <?php use Carbon\Carbon;
              $carbon=Carbon::now();
              $carbon1=Carbon::now()->toDateString();
              echo $carbon->format('l'); echo" , ";echo $carbon1;
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
 function timedMsg()
  {
    var t=setInterval("change_time();",1000);
  }
 function change_time()
 {
   var d = new Date();
   var curr_hour = d.getHours();
   var curr_min = d.getMinutes();
   var curr_sec = d.getSeconds();
   if(curr_hour > 24)
     curr_hour = curr_hour - 24;
   document.getElementById('Hour').innerHTML =curr_hour+':';
    document.getElementById('Minut').innerHTML=curr_min+':';
    document.getElementById('Second').innerHTML=curr_sec;
 }
timedMsg();
</script>
  <div class="col-12">
    <h3 class="mb-2 page-title">Staff Information</h3>
    @can('Register-Staff')
      <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#varyModal">Add Staff</button>
    @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-success">
          <h5>Staff Table</h5>
        </div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Username</th>
                  <th>Name</th>
                  <th>Company</th>
                  <th>Company Unit</th>
                  <th>Work Point</th>
                  <th>Role</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Gross Salary</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($users as $key => $u)
                  <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ optional($u->company)->company_name ?? '-' }}</td>
                    <td>{{ optional($u->comp_unit)->unit_name ?? '-' }}</td>
                    <td>{{ optional($u->workpoint)->work_name ?? '-' }}</td>
                    <td>
                      @php
                        $roleObj = \App\Models\Role::where('slug', $u->role)->first();
                      @endphp
                      {{ $roleObj->name ?? $u->role }}
                    </td>
                    <td>{{ $u->phone_No }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ number_format($u->gross_salary ?? 0, 2) }}</td>
                    <td>{{ $u->status }}</td>
                    <td>
                      @can('Edit-Staff')
                        <button
                          class="btn btn-sm btn-warning btn-edit-staff"
                          data-toggle="modal"
                          data-target="#staffEditModal"
                          data-id="{{ encrypt($u->id) }}"
                          data-username="{{ $u->username }}"
                          data-name="{{ $u->name }}"
                          data-email="{{ $u->email }}"
                          data-phone_no="{{ $u->phone_No }}"
                          data-gender="{{ $u->gender }}"
                          data-company_id="{{ $u->company_id }}"
                          data-comp_unit_id="{{ $u->comp_unit_id }}"
                          data-work_point_id="{{ $u->work_point_id }}"
                          data-role="{{ $u->role }}"
                          data-status="{{ $u->status }}"
                          data-gross_salary="{{ number_format($u->gross_salary ?? 0, 2, '.', '') }}"
                          data-accname="{{ $u->accName ?? '' }}"
                          data-accno="{{ $u->accNo ?? '' }}"
                          data-nssfno="{{ $u->nssfNo ?? '' }}"
                          data-wcfno="{{ $u->wcfNo ?? '' }}"
                        >Edit</button>
                      @endcan

                      @can('Edit-Staff')
                        @if($u->status !== 'Active')
                          <a href="{{ route('staff.activate', encrypt($u->id)) }}" class="btn btn-sm btn-success">Activate</a>
                        @else
                          <a href="{{ route('staff.deactivate', encrypt($u->id)) }}" class="btn btn-sm btn-secondary">Deactivate</a>
                        @endif
                      @endcan

                      @can('Edit-Staff')
                        <a href="{{ route('staff.reset.password', encrypt($u->id)) }}" class="btn btn-sm btn-info btn-reset-pass">Reset Password</a>
                      @endcan

                      @can('Delete-Staff')
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-staff" data-id="{{ encrypt($u->id) }}">Remove</a>
                      @endcan
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div> <!-- /.table-responsive -->
        </div> <!-- /.ibox-content -->
      </div> <!-- /.ibox -->
    </div> <!-- /.col-lg-12 -->
  </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="varyModal" tabindex="-1" role="dialog" aria-labelledby="varyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="reg" action="{{ route('staff.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Staff</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <div class="form-group"><label>Username <span style="color: red">*</span></label><input type="text" name="username" class="form-control" required></div>
          <div class="form-row">
            <div class="form-group col"><label>Full Name<span style="color: red">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group col"><label>Email</label><input type="email" name="email" class="form-control"></div>
          </div>
          <div class="form-row">
            <div class="form-group col"><label>Phone</label><input type="text" name="phone_No" class="form-control"></div>

            <div class="form-group col">
              <label>Gender</label>
              <select name="gender" class="form-control select2_demo_3">
                <option value="">-- none --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Company</label>
              <select name="company_id" id="create_company_id" class="form-control select2_demo_3">
                <option value="">-- none --</option>
                @foreach($companies as $c)
                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col">
              <label>Work Point</label>
              <select name="work_point_id" id="create_work_point_id" class="form-control select2_demo_3">
                <option value="">-- none --</option>
                @foreach($workPoints as $w)
                <option value="{{ $w->id }}">{{ $w->work_name }}</option>
                @endforeach
              </select>
            </div>

          <div class="form-group col">
            <label>Company Unit</label>
            <select name="comp_unit_id" class="form-control select2_demo_3">
              <option value="">-- Select unit (optional) --</option>
              @foreach($unities as $u)
                <option value="{{ $u->id }}">{{ $u->unit_name }}</option>
              @endforeach
            </select>
          </div>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Role</label>
              <select name="role" class="form-control select2_demo_3" required>
                <option value="">-- Select role --</option>
                @foreach($roles as $r)
                <option value="{{ $r->slug }}">{{ $r->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col">
              <label>Status</label>
              <select name="status" class="form-control select2_demo_3">
                <option value="Active" selected>Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>

          <hr/>
          <h6>Payroll / Bank Details</h6>
          <div class="form-row">
            <div class="form-group col">
              <label>Gross Salary</label>
              <input type="number" step="0.01" name="gross_salary" class="form-control" value="0.00" />
            </div>
            <div class="form-group col">
              <label>Account Name</label>
              <input type="text" name="accName" class="form-control" value="N/A" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Account No</label>
              <input type="text" name="accNo" class="form-control" value="N/A" />
            </div>
            <div class="form-group col">
              <label>NSSF No</label>
              <input type="text" name="nssfNo" class="form-control" value="N/A" />
            </div>
          </div>

          <div class="form-group">
            <label>WCF No</label>
            <input type="text" name="wcfNo" class="form-control" value="N/A" />
          </div>

          <div class="alert alert-info">
            Default password will be set to <strong>123456</strong> (you can change this in controller).
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('reg')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- Edit Modal -->
<div class="modal fade" id="staffEditModal" tabindex="-1" role="dialog" aria-labelledby="staffEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="up" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Staff</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_staff_id" name="edit_id" />
          <div class="form-group"><label>Username <span style="color: red">*</span></label><input id="edit_username" type="text" name="username" class="form-control" required></div>
          <div class="form-row">
          <div class="form-group col"><label>Full Name <span style="color: red">*</span></label><input id="edit_name" type="text" name="name" class="form-control" required></div>
          <div class="form-group col"><label>Email</label><input id="edit_email" type="email" name="email" class="form-control"></div>
          </div>
          <div class="form-row">
            <div class="form-group col"><label>Phone</label><input id="edit_phone" type="text" name="phone_No" class="form-control"></div>
            <div class="form-group col">
              <label>Gender</label>
              <select id="edit_gender" name="gender" class="form-control edit">
                <option value="">-- none --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Company</label>
              <select id="edit_company_id" name="company_id" class="form-control edit">
                <option value="">-- none --</option>
                @foreach($companies as $c)
                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col">
              <label>Work Point</label>
              <select id="edit_work_point_id" name="work_point_id" class="form-control edit">
                <option value="">-- none --</option>
                @foreach($workPoints as $w)
                <option value="{{ $w->id }}">{{ $w->work_name }}</option>
                @endforeach
              </select>
            </div>

          <div class="form-group">
            <label>Company Unit</label>
            <select id="edit_comp_unit_id"  name="comp_unit_id" class="form-control edit">
              <option value="">-- Select unit (optional) --</option>
              @foreach($unities as $u)
                <option value="{{ $u->id }}">{{ $u->unit_name }}</option>
              @endforeach
            </select>
          </div>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Role</label>
              <select id="edit_role" name="role" class="form-control edit">
                <option value="">-- Select role --</option>
                @foreach($roles as $r)
                <option value="{{ $r->slug }}">{{ $r->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col">
              <label>Status</label>
              <select id="edit_status" name="status" class="form-control edit">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>

          <hr/>
          <h6>Payroll / Bank Details</h6>
          <div class="form-row">
            <div class="form-group col">
              <label>Gross Salary</label>
              <input id="edit_gross_salary" name="gross_salary" type="number" step="0.01" class="form-control" />
            </div>
            <div class="form-group col">
              <label>Account Name</label>
              <input id="edit_accName" name="accName" type="text" class="form-control" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Account No</label>
              <input id="edit_accNo" name="accNo" type="text" class="form-control" />
            </div>
            <div class="form-group col">
              <label>NSSF No</label>
              <input id="edit_nssfNo" name="nssfNo" type="text" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label>WCF No</label>
            <input id="edit_wcfNo" name="wcfNo" type="text" class="form-control" />
          </div>

          <div class="alert alert-warning">
            Changing password is not done here. Use Reset Password action to set default password.
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('up')" type="submit" class="btn mb-2 btn-primary">Update</button></div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // Fill edit modal when edit button clicked
  document.querySelectorAll('.btn-edit-staff').forEach(btn => {
    btn.addEventListener('click', function () {
      const encId = this.dataset.id;
      document.getElementById('edit_staff_id').value = encId;
      document.getElementById('edit_username').value = this.dataset.username || '';
      document.getElementById('edit_name').value = this.dataset.name || '';
      document.getElementById('edit_email').value = this.dataset.email || '';
      document.getElementById('edit_phone').value = this.dataset.phone_no || '';
      document.getElementById('edit_gender').value = this.dataset.gender || '';
      document.getElementById('edit_company_id').value = this.dataset.company_id || '';
      document.getElementById('edit_comp_unit_id').value = this.dataset.comp_unit_id || '';
      document.getElementById('edit_work_point_id').value = this.dataset.work_point_id || '';
      document.getElementById('edit_role').value = this.dataset.role || '';
      document.getElementById('edit_status').value = this.dataset.status || 'Active';
      document.getElementById('edit_gross_salary').value = this.dataset.gross_salary || 0;
      document.getElementById('edit_accName').value = this.dataset.accname || '';
      document.getElementById('edit_accNo').value = this.dataset.accno || '';
      document.getElementById('edit_nssfNo').value = this.dataset.nssfno || '';
      document.getElementById('edit_wcfNo').value = this.dataset.wcfno || '';

      const form = document.getElementById('up');
      form.action = "{{ route('staff.update', ':id') }}".replace(':id', encId);
    });
  });

  // DELETE staff (soft remove)
  document.querySelectorAll('.btn-delete-staff').forEach(btn => {
    btn.addEventListener('click', function () {
      const encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will remove the staff.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'Cancel',
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "{{ url('/admin/staff/remove') }}/" + encId;
        }
      });
    });
  });

  // Reset password confirmation
  document.querySelectorAll('.btn-reset-pass').forEach(a => {
    a.addEventListener('click', function (ev) {
      ev.preventDefault();
      const href = this.getAttribute('href');
      Swal.fire({
        title: 'Reset Password?',
        text: "This will reset the password to default for the user.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, reset it',
      }).then((res) => {
        if (res.isConfirmed) {
          window.location.href = href;
        }
      });
    });
  });

  // init Select2 for create modal and edit modal selects (use same pattern as other pages)
  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) try { $el.select2('destroy'); } catch (e) {}
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({ width: '100%', theme: 'bootstrap4', dropdownParent: $parent });
  }

  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#varyModal').length) { initSelect2WithParent($this, '#varyModal'); return; }
    initSelect2WithParent($this, null);
  });

  $('.edit').each(function(){ initSelect2WithParent($(this), '#staffEditModal'); });

});
</script>
@endsection
