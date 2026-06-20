@extends('layouts.salesMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Sales & Marketing Module</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Activities</strong>
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
  <h3 class="mb-2 page-title">Activities</h3>
  @can('Register-Activities')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#activityCreateModal">Add Activity</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox ">
    <div class="ibox-title bg-primary"><h5>Activities Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example">
          <thead><tr><th>#</th><th>Type</th><th>Subject</th><th>Opportunity</th><th>Customer</th><th>Due At</th><th>Assigned</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($activities as $k => $a)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $a->type }}</td>
              <td>{{ $a->subject }}</td>
              <td> Opp: {{ optional($a->opportunity)->opportunity_name }} </td>
              <td>{{ optional($a->customer)->customer_name ?? '-' }}</td>
              <td>{{ $a->due_at ?? '-' }}</td>
              <td>{{ optional($a->assignedTo)->name ?? '-' }}</td>
              <td>{{ $a->status }}</td>
              <td>
                @can('Edit-Activities')
                  <button class="btn btn-sm btn-warning btn-edit-activity"
                    data-id="{{ encrypt($a->id) }}"
                    data-type="{{ $a->type }}"
                    data-subject="{{ $a->subject }}"
                    data-body="{{ $a->body }}"
                    data-due_at="{{ $a->due_at }}"
                    data-opportunity_id="{{ $a->opportunity_id }}"
                    data-cstm_id="{{ $a->cstm_id }}"
                    data-assigned_to="{{ $a->assigned_to }}"
                    data-work_point_id="{{ $a->work_point_id }}"
                    data-status="{{ $a->status ?? 'Pending' }}"
                  >Edit</button>
                @endcan
                @can('Delete-Activities')
                  <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-activity" data-id="{{ encrypt($a->id) }}">Remove</a>
                @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div></div></div></div>

{{-- Create Modal --}}
<div class="modal fade" id="activityCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="activityCreateForm" action="{{ route('sales.activities.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Activity</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group"><label>Work Point <span style="color:red">*</span></label>
              <select name="work_point_id" class="form-control select2_demo_3" required><option value="">-- Select work point --</option>@foreach($workPoints as $wp) <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select>
            </div>
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
          @else
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
          @endif

          <div class="form-row">
            <div class="form-group col-md-4"><label>Type</label><select name="type" class="form-control select2_demo_3"><option value="call">Call</option><option value="email">Email</option><option value="meeting">Meeting</option><option value="task">Task</option><option value="note">Note</option></select></div>
            <div class="form-group col-md-8"><label>Subject</label><input type="text" name="subject" class="form-control"></div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6"><label>Opportunity (optional)</label><select name="opportunity_id" class="form-control select2_demo_3"><option value="">-- Select opp --</option>@foreach($opps as $op) <option value="{{ $op->id }}">{{ $op->opportunity_name }}</option> @endforeach</select></div>
            <div class="form-group col-md-6"><label>Customer (optional)</label><select name="cstm_id" class="form-control select2_demo_3"><option value="">-- Select customer --</option>@foreach($customers as $cust) <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option> @endforeach</select></div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6"><label>Due At</label><input type="datetime-local" name="due_at" class="form-control"></div>
            <div class="form-group col-md-6"><label>Assign To</label><select name="assigned_to" class="form-control select2_demo_3"><option value="">-- Select user --</option>@foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach</select></div>
          </div>

          <div class="form-group"><label>Body / Notes</label><textarea name="body" class="form-control"></textarea></div>

          <div class="form-group"><label>Status</label><select name="status" class="form-control select2_demo_3"><option value="Pending">Pending</option><option value="Done">Done</option><option value="Cancelled">Cancelled</option></select></div>

        </div>
        <div class="modal-footer"><button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('activityCreateForm')" class="btn mb-2 btn-primary">Submit</button></div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="activityEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="activityEditForm" method="POST">@csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Activity</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_activity_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group"><label>Work Point <span style="color:red">*</span></label><select id="edit_activity_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select work point --</option>@foreach($workPoints as $wp) <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select></div>
          @endif

          <div class="form-row">
            <div class="form-group col-md-4"><label>Type</label><select id="edit_activity_type" name="type" class="form-control select2_demo_3"><option value="call">Call</option><option value="email">Email</option><option value="meeting">Meeting</option><option value="task">Task</option><option value="note">Note</option></select></div>
            <div class="form-group col-md-8"><label>Subject</label><input id="edit_activity_subject" type="text" name="subject" class="form-control"></div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6"><label>Opportunity</label><select id="edit_activity_opp" name="opportunity_id" class="form-control select2_demo_3"><option value="">-- Select opp --</option>@foreach($opps as $op) <option value="{{ $op->id }}">{{ $op->opportunity_name }}</option> @endforeach</select></div>
            <div class="form-group col-md-6"><label>Customer</label><select id="edit_activity_cstm" name="cstm_id" class="form-control select2_demo_3"><option value="">-- Select customer --</option>@foreach($customers as $cust) <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option> @endforeach</select></div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6"><label>Due At</label><input id="edit_activity_due" type="datetime-local" name="due_at" class="form-control"></div>
            <div class="form-group col-md-6"><label>Assign To</label><select id="edit_activity_assigned" name="assigned_to" class="form-control select2_demo_3"><option value="">-- Select user --</option>@foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
          </div>

          <div class="form-group"><label>Body / Notes</label><textarea id="edit_activity_body" name="body" class="form-control"></textarea></div>

          <div class="form-group"><label>Status</label><select id="edit_activity_status" name="status" class="form-control select2_demo_3"><option value="Pending">Pending</option><option value="Done">Done</option><option value="Cancelled">Cancelled</option><option value="Deleted">Deleted</option></select></div>

        </div>
        <div class="modal-footer"><button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('activityEditForm')" class="btn mb-2 btn-primary">Update</button></div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var tempActivityEdit = null;
  function initSelect2WithParent($el, parent) { if (!$el||!$el.length) return; if ($el.data('select2')){ try{$el.select2('destroy')}catch(e){} } $el.select2({width:'100%',theme:'bootstrap4',dropdownParent: (parent && $(parent).length)?$(parent):$(document.body)}); }

  $('.select2_demo_3').each(function(){ var $this=$(this); if($this.closest('#activityCreateModal').length){ initSelect2WithParent($this,'#activityCreateModal'); return;} if($this.closest('#activityEditModal').length){ initSelect2WithParent($this,'#activityEditModal'); return;} initSelect2WithParent($this,null); });

  $(document).on('shown.bs.modal','#activityCreateModal', function(){ var f=$(this).find('form')[0]; if(f) f.reset(); $(this).find('.select2_demo_3').each(function(){ initSelect2WithParent($(this),'#activityCreateModal'); $(this).val(null).trigger('change'); }); });

  $(document).on('shown.bs.modal','#activityEditModal', function(){ $(this).find('.select2_demo_3').each(function(){ initSelect2WithParent($(this),'#activityEditModal'); }); if(tempActivityEdit){ $('#edit_activity_work_point_id').val(tempActivityEdit.work_point_id||'').trigger('change'); $('#edit_activity_opp').val(tempActivityEdit.opportunity_id||'').trigger('change'); $('#edit_activity_cstm').val(tempActivityEdit.cstm_id||'').trigger('change'); $('#edit_activity_assigned').val(tempActivityEdit.assigned_to||'').trigger('change'); tempActivityEdit=null; } });

  document.querySelectorAll('.btn-edit-activity').forEach(function(btn){ btn.addEventListener('click', function(){ var enc = this.dataset.id; document.getElementById('edit_activity_id').value = enc || ''; document.getElementById('edit_activity_type').value = this.dataset.type || 'call'; document.getElementById('edit_activity_subject').value = this.dataset.subject || ''; document.getElementById('edit_activity_body').value = this.dataset.body || ''; document.getElementById('edit_activity_due').value = this.dataset.due_at || ''; document.getElementById('edit_activity_status').value = this.dataset.status || 'Pending'; tempActivityEdit = { work_point_id: this.dataset.work_point_id||null, opportunity_id: this.dataset.opportunity_id||null, cstm_id: this.dataset.cstm_id||null, assigned_to: this.dataset.assigned_to||null }; var form = document.getElementById('activityEditForm'); form.action = "{{ route('sales.activities.update', ':id') }}".replace(':id', enc); $('#activityEditModal').modal('show'); }); });
  document.querySelectorAll('.btn-delete-activity').forEach(function(btn){ btn.addEventListener('click', function(){ var enc=this.dataset.id; Swal.fire({title:'Are you sure?', text:"This will mark the activity as Deleted.", icon:'warning', showCancelButton:true, confirmButtonText:'Yes, delete it!'}).then(function(res){ if(res.isConfirmed) window.location.href = "{{ route('sales.activities.remove', ':id') }}".replace(':id', enc); }); }); });

});
</script>
@endsection
