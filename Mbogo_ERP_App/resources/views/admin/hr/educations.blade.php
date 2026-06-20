@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Educations Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('hr') }}">Human Resource</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Staff/User Educations</strong>
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
  <h3 class="mb-2 page-title">Staff Educations</h3>
  @can('Register-Education')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#eduCreateModal">Add Education</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox ">
    <div class="ibox-title bg-info"><h5>Education Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example">
          <thead><tr><th>#</th><th>Staff</th><th>Level</th><th>Institution</th><th>Field</th><th>Year</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($educations as $k => $e)
            <tr>
              <td>{{ $k + 1 }}</td>
              <td>{{ optional($e->user)->name ?? '-' }}</td>
              <td>{{ $e->level }}</td>
              <td>{{ $e->institution }}</td>
              <td>{{ $e->field_of_study }}</td>
              <td>{{ $e->year_completed }}</td>
              <td>{{ $e->status }}</td>
              <td>
                @can('Edit-Education')
                  <button class="btn btn-sm btn-warning btn-edit-edu"
                    data-id="{{ encrypt($e->id) }}"
                    data-user_id="{{ $e->user_id }}"
                    data-level="{{ $e->level }}"
                    data-institution="{{ $e->institution }}"
                    data-field="{{ $e->field_of_study }}"
                    data-year="{{ $e->year_completed }}"
                    data-status="{{ $e->status ?? 'Active' }}"
                    data-work_point_id="{{ $e->work_point_id }}"
                  >Edit</button>
                @endcan
                @can('Delete-Education')
                  <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-edu" data-id="{{ encrypt($e->id) }}">Remove</a>
                @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div></div></div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="eduCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="eduCreateForm" action="{{ route('hr.educations.store') }}" method="POST">@csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Education</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select name="work_point_id" class="form-control select2_demo_3" required>
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp) <option value="{{ $wp->id }}">{{ $wp->work_name }}</option> @endforeach
              </select>
            </div>
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
          @else
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
          @endif

          <div class="form-group">
            <label>Staff</label>
            <select name="user_id" class="form-control select2_demo_3" required>
              <option value="">-- Select staff --</option>
              @foreach($staffUsers as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
            </select>
          </div>

          <div class="form-group"><label>Level</label><input name="level" class="form-control" required></div>
          <div class="form-group"><label>Institution</label><input name="institution" class="form-control"></div>
          <div class="form-group"><label>Field of Study</label><input name="field_of_study" class="form-control"></div>
          <div class="form-group"><label>Year Completed</label><input name="year_completed" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('eduCreateForm')" class="btn btn-primary">Submit</button></div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="eduEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="eduEditForm" method="POST">@csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Education</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_edu_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group"><label>Work Point</label>
              <select id="edit_edu_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">--select--</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select>
            </div>
          @endif

          <div class="form-group"><label>Staff</label>
            <select id="edit_edu_user_id" name="user_id" class="form-control select2_demo_3"><option value="">--select staff--</option>@foreach($staffUsers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
          </div>

          <div class="form-group"><label>Level</label><input id="edit_edu_level" name="level" class="form-control"></div>
          <div class="form-group"><label>Institution</label><input id="edit_edu_institution" name="institution" class="form-control"></div>
          <div class="form-group"><label>Field</label><input id="edit_edu_field" name="field_of_study" class="form-control"></div>
          <div class="form-group"><label>Year</label><input id="edit_edu_year" name="year_completed" class="form-control"></div>
          <div class="form-group"><label>Status</label><select id="edit_edu_status" name="status" class="form-control select2_demo_3"><option>Active</option><option>Inactive</option><option>Deleted</option></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('eduEditForm')" class="btn btn-primary">Update</button></div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var tempEduEditData = null;
  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) { try { $el.select2('destroy'); } catch(e){} }
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({ width: '100%', theme: 'bootstrap4', dropdownParent: $parent });
  }
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#eduCreateModal').length) { initSelect2WithParent($this, '#eduCreateModal'); return; }
    if ($this.closest('#eduEditModal').length) { initSelect2WithParent($this, '#eduEditModal'); return; }
    initSelect2WithParent($this, null);
  });
  $(document).on('shown.bs.modal', '#eduCreateModal', function () {
    var $modal = $(this); var f = $modal.find('form')[0]; if (f) f.reset();
    $modal.find('.select2_demo_3').each(function(){ initSelect2WithParent($(this), '#eduCreateModal'); $(this).val(null).trigger('change'); });
  });
  $(document).on('shown.bs.modal', '#eduEditModal', function () {
    var $modal = $(this); $modal.find('.select2_demo_3').each(function(){ initSelect2WithParent($(this), '#eduEditModal'); });
    if (tempEduEditData) {
      if (typeof tempEduEditData.work_point_id !== 'undefined') $('#edit_edu_work_point_id').val(tempEduEditData.work_point_id).trigger('change');
      if (typeof tempEduEditData.user_id !== 'undefined') $('#edit_edu_user_id').val(tempEduEditData.user_id).trigger('change');
      tempEduEditData = null;
    }
  });
  document.querySelectorAll('.btn-edit-edu').forEach(function(btn){
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      document.getElementById('edit_edu_id').value = enc || '';
      document.getElementById('edit_edu_level').value = this.dataset.level || '';
      document.getElementById('edit_edu_institution').value = this.dataset.institution || '';
      document.getElementById('edit_edu_field').value = this.dataset.field || '';
      document.getElementById('edit_edu_year').value = this.dataset.year || '';
      document.getElementById('edit_edu_status').value = this.dataset.status || 'Active';
      tempEduEditData = { work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null, user_id: (typeof this.dataset.user_id !== 'undefined') ? this.dataset.user_id : null };
      var form = document.getElementById('eduEditForm'); form.action = "{{ route('hr.educations.update', ':id') }}".replace(':id', enc);
      $('#eduEditModal').modal('show');
    });
  });
  document.querySelectorAll('.btn-delete-edu').forEach(function(btn){
    btn.addEventListener('click', function(){ var enc = this.dataset.id; Swal.fire({title:'Are you sure?',text:"This will mark the record as Deleted.",icon:'warning',showCancelButton:true,confirmButtonText:'Yes, delete it!'}).then(function(res){ if(res.isConfirmed) window.location.href = "{{ route('hr.educations.remove', ':id') }}".replace(':id', enc); }); });
  });

});
</script>
@endsection
