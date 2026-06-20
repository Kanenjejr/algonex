@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Leaves Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('hr') }}">Human Resource</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Staff/User Leaves</strong>
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
  <h3 class="mb-2 page-title">Leaves</h3>
  @can('Register-Leave')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#leaveCreateModal">Request Leave</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox ">
    <div class="ibox-title bg-info"><h5>Leave Requests</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example">
          <thead><tr><th>#</th><th>Staff</th><th>Type</th><th>Start</th><th>End</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($leaves as $k => $l)
            <tr>
              <td>{{ $k + 1 }}</td>
              <td>{{ optional($l->user)->name ?? '-' }}</td>
              <td>{{ $l->leave_type }}</td>
              <td>{{ $l->start_date }}</td>
              <td>{{ $l->end_date }}</td>
              <td>{{ $l->status }}</td>
              <td>
                {{-- Approve (only when Pending) --}}
                @can('Approve-Leave')
                    @if($l->status === 'Pending')
                    <form action="{{ route('hr.leaves.approve', encrypt($l->id)) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="btn btn-sm btn-success">Approve</button>
                    </form>
                    @endif
                @endcan

                {{-- Print (only when Approved) --}}
                @can('View-Leaves')
                    @if($l->status === 'Approved')
                    <a href="{{ route('hr.leaves.print', encrypt($l->id)) }}" target="_blank" class="btn btn-sm btn-primary">Print</a>
                    @endif
                @endcan

                {{-- Edit (allowed for Pending and Approved, not allowed for Cancelled) --}}
                @can('Edit-Leave')
                    @if(!in_array($l->status, ['Cancelled']))
                    <button class="btn btn-sm btn-warning btn-edit-leave"
                        data-id="{{ encrypt($l->id) }}"
                        data-user_id="{{ $l->user_id }}"
                        data-leave_type="{{ $l->leave_type }}"
                        data-start_date="{{ $l->start_date }}"
                        data-end_date="{{ $l->end_date }}"
                        data-reason="{{ $l->reason }}"
                        data-status="{{ $l->status ?? 'Pending' }}"
                        data-work_point_id="{{ $l->work_point_id }}"
                    >Edit</button>
                    @else
                    <button class="btn btn-sm btn-warning" disabled title="Cannot edit a cancelled leave">Edit</button>
                    @endif
                @endcan

                {{-- Delete / Cancel --}}
                @can('Delete-Leave')
                    @if(!in_array($l->status, ['Cancelled']))
                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-leave" data-id="{{ encrypt($l->id) }}">Cancel</a>
                    @else
                    <button class="btn btn-sm btn-danger" disabled title="Already cancelled">Cancel</button>
                    @endif
                @endcan

                {{-- status badge --}}
                @if($l->status === 'Approved')
                    <span class="badge badge-info ml-1">Approved</span>
                @elseif($l->status === 'Pending')
                    <span class="badge badge-warning ml-1">Pending</span>
                @elseif($l->status === 'Rejected')
                    <span class="badge badge-danger ml-1">Rejected</span>
                @elseif($l->status === 'Cancelled')
                    <span class="badge badge-secondary ml-1">Cancelled</span>
                @endif
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
<div class="modal fade" id="leaveCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="leaveCreateForm" action="{{ route('hr.leaves.store') }}" method="POST">@csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Create Leave Request</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group"><label>Work Point</label>
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

          <div class="form-group"><label>Staff</label>
            <select name="user_id" class="form-control select2_demo_3" required>
              <option value="">-- Select staff --</option>
              @foreach($staffUsers as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
            </select>
          </div>

          <div class="form-group"><label>Leave Type</label>
            <select name="leave_type" class="form-control select2_demo_3"><option>Annual</option><option>Maternity</option><option>Paternity</option><option>Sick</option><option>Compassionate</option></select>
          </div>

          <div class="form-group"><label>Start Date</label><input type="date" name="start_date" class="form-control" required></div>
          <div class="form-group"><label>End Date</label><input type="date" name="end_date" class="form-control" required></div>
          <div class="form-group"><label>Reason</label><textarea name="reason" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('leaveCreateForm')" class="btn btn-primary">Submit</button></div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="leaveEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="leaveEditForm" method="POST">
        @csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Leave</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_leave_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group"><label>Work Point</label>
              <select id="edit_leave_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">--select--</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select>
            </div>
          @endif

          <div class="form-group"><label>Staff</label>
            <select id="edit_leave_user_id" name="user_id" class="form-control select2_demo_3"><option value="">--select staff--</option>@foreach($staffUsers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
          </div>

          <div class="form-group"><label>Type</label><select id="edit_leave_type" name="leave_type" class="form-control select2_demo_3"><option>Annual</option><option>Maternity</option><option>Paternity</option><option>Sick</option><option>Compassionate</option></select></div>
          <div class="form-group"><label>Start</label><input id="edit_leave_start" type="date" name="start_date" class="form-control"></div>
          <div class="form-group"><label>End</label><input id="edit_leave_end" type="date" name="end_date" class="form-control"></div>
          <div class="form-group"><label>Reason</label><textarea id="edit_leave_reason" name="reason" class="form-control"></textarea></div>
          <div class="form-group"><label>Status</label><select id="edit_leave_status" name="status" class="form-control select2_demo_3"><option>Pending</option><option>Approved</option><option>Rejected</option><option>Cancelled</option></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('leaveEditForm')" class="btn btn-primary">Update</button></div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var tempLeaveEditData = null;
  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) try { $el.select2('destroy'); } catch(e){}
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({width:'100%', theme:'bootstrap4', dropdownParent:$parent});
  }
  $('.select2_demo_3').each(function(){
    var $this=$(this);
    if ($this.closest('#leaveCreateModal').length) { initSelect2WithParent($this,'#leaveCreateModal'); return; }
    if ($this.closest('#leaveEditModal').length) { initSelect2WithParent($this,'#leaveEditModal'); return; }
    initSelect2WithParent($this,null);
  });
  $(document).on('shown.bs.modal','#leaveCreateModal', function(){ var $m=$(this); if($m.find('form')[0]) $m.find('form')[0].reset(); $m.find('.select2_demo_3').each(function(){ initSelect2WithParent($(this),'#leaveCreateModal'); $(this).val(null).trigger('change'); }); });
  $(document).on('shown.bs.modal','#leaveEditModal', function(){ var $m=$(this); $m.find('.select2_demo_3').each(function(){ initSelect2WithParent($(this),'#leaveEditModal'); }); if(tempLeaveEditData){ if(typeof tempLeaveEditData.work_point_id!=='undefined') $('#edit_leave_work_point_id').val(tempLeaveEditData.work_point_id).trigger('change'); if(typeof tempLeaveEditData.user_id!=='undefined') $('#edit_leave_user_id').val(tempLeaveEditData.user_id).trigger('change'); tempLeaveEditData=null; } });
  document.querySelectorAll('.btn-edit-leave').forEach(function(btn){ btn.addEventListener('click', function(){ var enc=this.dataset.id; document.getElementById('edit_leave_id').value=enc||''; document.getElementById('edit_leave_type').value=this.dataset.leave_type||'Annual'; document.getElementById('edit_leave_start').value=this.dataset.start_date||''; document.getElementById('edit_leave_end').value=this.dataset.end_date||''; document.getElementById('edit_leave_reason').value=this.dataset.reason||''; document.getElementById('edit_leave_status').value=this.dataset.status||'Pending'; tempLeaveEditData={ work_point_id:(typeof this.dataset.work_point_id!=='undefined')?this.dataset.work_point_id:null, user_id:(typeof this.dataset.user_id!=='undefined')?this.dataset.user_id:null }; var form=document.getElementById('leaveEditForm'); form.action="{{ route('hr.leaves.update', ':id') }}".replace(':id', enc); $('#leaveEditModal').modal('show'); }); });
  document.querySelectorAll('.btn-delete-leave').forEach(function(btn){ btn.addEventListener('click', function(){ var enc=this.dataset.id; Swal.fire({title:'Are you sure?', text:"This will cancel the leave.", icon:'warning', showCancelButton:true, confirmButtonText:'Yes'}).then(function(res){ if(res.isConfirmed) window.location.href="{{ route('hr.leaves.remove', ':id') }}".replace(':id', enc); }); }); });

});
</script>
@endsection
