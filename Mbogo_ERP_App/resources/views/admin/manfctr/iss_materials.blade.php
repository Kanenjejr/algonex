@extends('layouts.ManftrMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Inventory And Manufacturing Dashboard</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Issued Raw Material</strong>
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
  <h3 class="mb-2 page-title">Issued Raw Materials</h3>
  @can('Register-Issued-Raw-Material')
    <button class="btn mb-2 btn-primary" style="position:absolute; top:4.5%; right:1.7%" data-toggle="modal" data-target="#issCreateModal">Add Issue</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12">
    <div class="ibox">
      <div class="ibox-title bg-warning"><h5>Issued Raw Materials Table</h5></div>
      <div class="ibox-content">
        <div class="table-responsive">
          <table class="table table-striped table-bordered dataTables-example">
            <thead><tr><th>#</th><th>Date</th><th>Raw Material</th><th>No Bags</th><th>Bag Size</th><th>Tones</th><th>Entered By</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              @foreach($issues as $k => $i)
              <tr>
                <td>{{ $k+1 }}</td>
                <td>{{ $i->iss_date }}</td>
                <td>{{ optional($i->raw)->material_name ?? '-' }}</td>
                <td>{{ $i->iss_no_bags }}</td>
                <td>{{ $i->iss_bag_size }}</td>
                <td>{{ $i->iss_tones }}</td>
                <td>{{ optional($i->user)->name ?? '-' }}</td>
                <td>{{ $i->status }}</td>
                <td>
                  @can('Edit-Issued-Raw-Material')
                    <button class="btn btn-sm btn-warning btn-edit-iss" data-id="{{ encrypt($i->id) }}"
                      data-iss_date="{{ $i->iss_date }}" data-raw_id="{{ $i->raw_id }}" data-iss_no_bags="{{ $i->iss_no_bags }}"
                      data-iss_bag_size="{{ $i->iss_bag_size }}" data-iss_tones="{{ $i->iss_tones }}" data-work_point_id="{{ $i->work_point_id }}"
                      data-status="{{ $i->status }}">Edit</button>
                  @endcan
                  @can('Delete-Issued-Raw-Material')
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-iss" data-id="{{ encrypt($i->id) }}">Remove</a>
                  @endcan
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div></div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="issCreateModal" tabindex="-1"><div class="modal-dialog"><form id="issCreateForm" action="{{ route('manfctr.issmaterials.store') }}" method="POST">@csrf
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Issued Raw Materials </h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point</label><select name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select></div>
      @endif
      <div class="form-group"><label>Date</label><input type="date" name="iss_date" class="form-control" required></div>
      <div class="form-group"><label>Raw</label><select name="raw_id" class="form-control select2_demo_3" required><option value="">-- Select --</option>@foreach($raws as $r)<option value="{{ $r->id }}">{{ $r->material_name }}</option>@endforeach</select></div>
      <div class="form-group"><label>No Bags</label><input type="number" name="iss_no_bags" class="form-control" required></div>
      <div class="form-group"><label>Bag Size</label><input type="number" step="0.01" name="iss_bag_size" class="form-control" required></div>
      <div class="form-group"><label>Tones</label><input type="number" step="0.01" name="iss_tones" class="form-control" required></div>
      <div class="form-group"><label>Status</label><select name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('issCreateForm')" type="submit" class="btn btn-primary">Submit</button></div>
  </div></form></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="issEditModal" tabindex="-1"><div class="modal-dialog"><form id="issEditForm" method="POST">@csrf @method('PUT')
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Issued Raw Materials </h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <input id="edit_iss_id" type="hidden" name="edit_id">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point</label><select id="edit_iss_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select></div>
      @endif
      <div class="form-group"><label>Date</label><input id="edit_iss_date" type="date" name="iss_date" class="form-control" required></div>
      <div class="form-group"><label>Raw</label><select id="edit_iss_raw_id" name="raw_id" class="form-control select2_demo_3" required><option value="">-- Select --</option>@foreach($raws as $r)<option value="{{ $r->id }}">{{ $r->material_name }}</option>@endforeach</select></div>
      <div class="form-group"><label>No Bags</label><input id="edit_iss_no_bags" type="number" name="iss_no_bags" class="form-control" required></div>
      <div class="form-group"><label>Bag Size</label><input id="edit_iss_bag_size" type="number" step="0.01" name="iss_bag_size" class="form-control" required></div>
      <div class="form-group"><label>Tones</label><input id="edit_iss_tones" type="number" step="0.01" name="iss_tones" class="form-control" required></div>
      <div class="form-group"><label>Status</label><select id="edit_iss_status" name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('issEditForm')" type="submit" class="btn btn-primary">Update</button></div>
  </div></form></div></div>

<script>
document.addEventListener('DOMContentLoaded', function(){

  // ---------- helper to init Select2 safely ----------
  function initSelect2For($el, dropdownParentSelector) {
    if (!$el || !$el.length) return;
    // destroy any previous instance to avoid duplicates
    if ($el.data('select2')) {
      try { $el.select2('destroy'); } catch (e) { /* ignore */ }
    }
    var dp = dropdownParentSelector && $(dropdownParentSelector).length ? $(dropdownParentSelector) : $(document.body);
    $el.select2({
      width: '100%',
      theme: 'bootstrap4',
      dropdownParent: dp
    });
  }

  // Initialize selects that are not inside modals (fallback to body)
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    // If this select is inside Create modal -> use that modal as parent
    if ($this.closest('#issCreateModal').length) {
      initSelect2For($this, '#issCreateModal'); // explicit dropdownParent for Create modal
      return;
    }
    // If this select is inside Edit modal -> use that modal as parent
    if ($this.closest('#issEditModal').length) {
      initSelect2For($this, '#issEditModal'); // explicit dropdownParent for Edit modal
      return;
    }
    // Otherwise fallback to document.body
    initSelect2For($this, null);
  });

  // When any modal is shown, (re-)init the Select2s inside it with that modal as dropdownParent
  $(document).on('shown.bs.modal', '#issCreateModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2For($(this), '#issCreateModal'); // explicit dropdownParent: $('#issCreateModal')
    });
  });
  $(document).on('shown.bs.modal', '#issEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2For($(this), '#issEditModal'); // explicit dropdownParent: $('#issEditModal')
    });
  });

  // ---------- Edit button: populate fields, set form action, open modal ----------
  document.querySelectorAll('.btn-edit-iss').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      document.getElementById('edit_iss_id').value = id;
      document.getElementById('edit_iss_date').value = this.dataset.iss_date || '';
      document.getElementById('edit_iss_no_bags').value = this.dataset.iss_no_bags || '';
      document.getElementById('edit_iss_bag_size').value = this.dataset.iss_bag_size || '';
      document.getElementById('edit_iss_tones').value = this.dataset.iss_tones || '';
      document.getElementById('edit_iss_status').value = this.dataset.status || 'Active';

      // Set select2 selects via jQuery and trigger change so select2 shows correct value
      if (typeof this.dataset.raw_id !== 'undefined') {
        $('#edit_iss_raw_id').val(this.dataset.raw_id).trigger('change');
      } else {
        $('#edit_iss_raw_id').val('').trigger('change');
      }

      if (typeof this.dataset.work_point_id !== 'undefined') {
        $('#edit_iss_work_point_id').val(this.dataset.work_point_id).trigger('change');
      } else {
        $('#edit_iss_work_point_id').val('').trigger('change');
      }

      // set action (keeps your route usage unchanged)
      const form = document.getElementById('issEditForm');
      form.action = "{{ route('manfctr.issmaterials.update', ':id') }}".replace(':id', id);

      // show the edit modal
      $('#issEditModal').modal('show');
    });
  });

  // ---------- Delete button ----------
  document.querySelectorAll('.btn-delete-iss').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      Swal.fire({
        title:'Are you sure?',
        text:'This will mark the issue record as Deleted and adjust stock.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Yes'
      }).then(res => {
        if(res.isConfirmed) {
          window.location.href = "{{ route('manfctr.issmaterials.remove', ':id') }}".replace(':id', id);
        }
      });
    });
  });

  // ---------- Reset create modal fields on show (and ensure Select2 reset) ----------
  $('#issCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    // reset and re-init select2 inside create modal with explicit dropdownParent
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2For($(this), '#issCreateModal'); // explicit dropdownParent: $('#issCreateModal')
    });
  });

});
</script>
@endsection


