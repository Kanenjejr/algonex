@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Document Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('hr') }}">Human Resource</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Staff/User Document</strong>
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
  <h3 class="mb-2 page-title">Staff Documents</h3>
  @can('Register-Staff-Documents')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#docCreateModal">Upload Document</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox ">
    <div class="ibox-title bg-info"><h5>Documents Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example">
          <thead><tr><th>#</th><th>Staff</th><th>Title</th><th>File</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($docs as $k => $d)
            <tr>
              <td>{{ $k + 1 }}</td>
              <td>{{ optional($d->user)->name ?? '-' }}</td>
              <td>{{ $d->title }}</td>
              <td><a href="{{ asset($d->file_path) }}" target="_blank">{{ $d->file_name }}</a></td>
              {{-- <td>{{ $d->size }}</td> --}}
              <td>{{ $d->status }}</td>
              <td>
                @can('Edit-Staff-Documents')
                  <button class="btn btn-sm btn-warning btn-edit-doc"
                    data-id="{{ encrypt($d->id) }}"
                    data-user_id="{{ $d->user_id }}"
                    data-title="{{ $d->title }}"
                    data-work_point_id="{{ $d->work_point_id }}"
                    data-status="{{ $d->status ?? 'Active' }}"
                  >Edit</button>
                @endcan
                @can('Delete-Staff-Documents')
                  <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-doc" data-id="{{ encrypt($d->id) }}">Remove</a>
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
<div class="modal fade" id="docCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="docCreateForm" action="{{ route('hr.documents.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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

          <div class="form-group"><label>Title</label><input name="title" class="form-control"></div>
          <div class="form-group"><label>File</label><input type="file" name="file" class="form-control" required></div>
          <div class="form-group"><label>Status</label><select name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('docCreateForm')" class="btn btn-primary">Upload</button></div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="docEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="docEditForm" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Document</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_doc_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group"><label>Work Point</label>
              <select id="edit_doc_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">--select--</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select>
            </div>
          @endif
          <div class="form-group"><label>Staff</label>
            <select id="edit_doc_user_id" name="user_id" class="form-control select2_demo_3"><option value="">--select staff--</option>@foreach($staffUsers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
          </div>
          <div class="form-group"><label>Title</label><input id="edit_doc_title" name="title" class="form-control"></div>
          <div class="form-group"><label>Replace File (optional)</label><input type="file" name="file" class="form-control"></div>
          <div class="form-group"><label>Status</label><select id="edit_doc_status" name="status" class="form-control select2_demo_3"><option>Active</option><option>Inactive</option><option>Deleted</option></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('docEditForm')" class="btn btn-primary">Update</button></div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var tempDocEditData = null;
  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) try { $el.select2('destroy'); } catch(e){}
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({ width: '100%', theme: 'bootstrap4', dropdownParent: $parent });
  }
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#docCreateModal').length) { initSelect2WithParent($this, '#docCreateModal'); return; }
    if ($this.closest('#docEditModal').length) { initSelect2WithParent($this, '#docEditModal'); return; }
    initSelect2WithParent($this, null);
  });
  $(document).on('shown.bs.modal', '#docCreateModal', function () { var $m=$(this); if($m.find('form')[0]) $m.find('form')[0].reset(); $m.find('.select2_demo_3').each(function(){initSelect2WithParent($(this),'#docCreateModal'); $(this).val(null).trigger('change');}); });
  $(document).on('shown.bs.modal', '#docEditModal', function () { var $m=$(this); $m.find('.select2_demo_3').each(function(){initSelect2WithParent($(this),'#docEditModal');}); if(tempDocEditData){ if(typeof tempDocEditData.work_point_id!=='undefined') $('#edit_doc_work_point_id').val(tempDocEditData.work_point_id).trigger('change'); if(typeof tempDocEditData.user_id!=='undefined') $('#edit_doc_user_id').val(tempDocEditData.user_id).trigger('change'); tempDocEditData=null;} });
  document.querySelectorAll('.btn-edit-doc').forEach(function(btn){ btn.addEventListener('click', function(){ var enc=this.dataset.id; document.getElementById('edit_doc_id').value=enc||''; document.getElementById('edit_doc_title').value=this.dataset.title||''; document.getElementById('edit_doc_status').value=this.dataset.status||'Active'; tempDocEditData={ work_point_id:(typeof this.dataset.work_point_id!=='undefined')?this.dataset.work_point_id:null, user_id:(typeof this.dataset.user_id!=='undefined')?this.dataset.user_id:null }; var form=document.getElementById('docEditForm'); form.action="{{ route('hr.documents.update', ':id') }}".replace(':id', enc); $('#docEditModal').modal('show'); }); });
  document.querySelectorAll('.btn-delete-doc').forEach(function(btn){ btn.addEventListener('click', function(){ var enc=this.dataset.id; Swal.fire({ title:'Are you sure?', text:"This will mark the document as Deleted.", icon:'warning', showCancelButton:true, confirmButtonText:'Yes, delete it!' }).then(function(res){ if(res.isConfirmed) window.location.href="{{ route('hr.documents.remove', ':id') }}".replace(':id', enc); }); }); });

});
</script>
@endsection
