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
                <strong>Received Raw Material</strong>
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
  <h3 class="mb-2 page-title">Received Raw Materials </h3>
  @can('Register-Received-Raw-Material')
    <button class="btn mb-2 btn-primary" style="position:absolute; top:4.5%; right:1.7%" data-toggle="modal" data-target="#recCreateModal">Add Received</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12">
    <div class="ibox">
      <div class="ibox-title bg-success"><h5>Received Raw Materials Table</h5></div>
      <div class="ibox-content">
        <div class="table-responsive">
          <table class="table table-striped table-bordered dataTables-example">
            <thead><tr><th>#</th><th>Date</th><th>Raw Material</th><th>No Bags</th><th>Bag Size</th><th>Tones</th><th>Order</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              @foreach($recs as $k => $r)
              <tr>
                <td>{{ $k+1 }}</td>
                <td>{{ $r->rcv_date }}</td>
                <td>{{ optional($r->raw)->material_name ?? '-' }}</td>
                <td>{{ $r->rcv_no_bags }}</td>
                <td>{{ $r->rcv_bag_size }}</td>
                <td>{{ $r->rcv_tones }}</td>
                <td>{{ optional($r->order)->customer_name ?? '-' }}</td>
                <td>{{ $r->status }}</td>
                <td>
                  @can('Edit-Received-Raw-Material')
                    <button class="btn btn-sm btn-warning btn-edit-rec" data-id="{{ encrypt($r->id) }}"
                      data-rcv_date="{{ $r->rcv_date }}" data-raw_id="{{ $r->raw_id }}"
                      data-rcv_no_bags="{{ $r->rcv_no_bags }}" data-rcv_bag_size="{{ $r->rcv_bag_size }}"
                      data-rcv_tones="{{ $r->rcv_tones }}" data-order_id="{{ $r->order_id }}"
                      data-work_point_id="{{ $r->work_point_id }}" data-status="{{ $r->status }}">Edit</button>
                  @endcan
                  @can('Delete-Received-Raw-Material')
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-rec" data-id="{{ encrypt($r->id) }}">Remove</a>
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
<div class="modal fade" id="recCreateModal" tabindex="-1"><div class="modal-dialog">
  <form id="recCreateForm" action="{{ route('manfctr.recmaterials.store') }}" method="POST">@csrf
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Received Raw Materials</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
          <div class="form-group"><label>Work Point</label>
            <select name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select>
          </div>
        @endif

        <div class="form-row">
            <div class="form-group col"><label>Date</label><input type="date" name="rcv_date" class="form-control" required></div>

            <div class="form-group col"><label>Raw</label><select name="raw_id" class="form-control select2_demo_3" required><option value="">-- Select --</option>@foreach($raws as $r)<option value="{{ $r->id }}">{{ $r->material_name }}</option>@endforeach</select></div>
        </div>
        <div class="form-group "><label>No Bags</label><input type="number" name="rcv_no_bags" class="form-control" required></div>

        <div class="form-group"><label>Bag Size</label><input type="number" step="0.01" name="rcv_bag_size" class="form-control" required></div>

        <div class="form-group"><label>Tones</label><input type="number" step="0.01" name="rcv_tones" class="form-control" required></div>

        <div class="form-group"><label>Order (optional)</label>
          <select name="order_id" class="form-control select2_demo_3">
            <option value="">-- Select order --</option>
            @foreach($orders as $o)
              <option value="{{ $o->id }}">#{{ $o->id }} - {{ $o->customer_name }} (Ordered: {{ $o->ord_tones }} t; Received: {{ number_format($o->recv_tones ?? 0,2) }} t; Left: {{ number_format($o->unrecv_tones ?? $o->ord_tones,2) }} t)</option>
            @endforeach
          </select>
        </div>

        <div class="form-group"><label>Status</label><select name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('recCreateForm')" type="submit" class="btn btn-primary">Submit</button></div>
    </div>
  </form>
</div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="recEditModal" tabindex="-1"><div class="modal-dialog"><form id="recEditForm" method="POST">@csrf @method('PUT')
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Received Raw Materials</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <input id="edit_rec_id" type="hidden" name="edit_id">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point</label><select id="edit_rec_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select></div>
      @endif

    <div class="form-row">
      <div class="form-group col"><label>Date</label><input id="edit_rcv_date" type="date" name="rcv_date" class="form-control" required></div>
      <div class="form-group col"><label>Raw</label><select id="edit_raw_id" name="raw_id" class="form-control select2_demo_3" required><option value="">-- Select --</option>@foreach($raws as $r)<option value="{{ $r->id }}">{{ $r->material_name }}</option>@endforeach</select></div>
    </div>
      <div class="form-group"><label>No Bags</label><input id="edit_rcv_no_bags" type="number" name="rcv_no_bags" class="form-control" required></div>
      <div class="form-group"><label>Bag Size</label><input id="edit_rcv_bag_size" type="number" step="0.01" name="rcv_bag_size" class="form-control" required></div>
      <div class="form-group"><label>Tones</label><input id="edit_rcv_tones" type="number" step="0.01" name="rcv_tones" class="form-control" required></div>
      <div class="form-group"><label>Order</label><select id="edit_order_id" name="order_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($orders as $o)<option value="{{ $o->id }}">#{{ $o->id }} - {{ $o->customer_name }} (Left: {{ number_format($o->unrecv_tones ?? $o->ord_tones,2) }} t)</option>@endforeach</select></div>
      <div class="form-group"><label>Status</label><select id="edit_rec_status" name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('recEditForm')" type="submit" class="btn btn-primary">Update</button></div>
  </div>
</form></div></div>

<script>
document.addEventListener('DOMContentLoaded', function(){

  // temporary storage for select values to apply after edit modal shown
  var tempRecEditData = null;

  // safe init: destroy previous select2 instance then (re)initialize with provided parent selector
  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) {
      try { $el.select2('destroy'); } catch (e) { /* ignore */ }
    }
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({
      width: '100%',
      theme: 'bootstrap4',
      dropdownParent: $parent
    });
  }

  // Initialize all select2_demo_3 on page and give explicit modal parents where appropriate
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#recCreateModal').length) {
      initSelect2WithParent($this, '#recCreateModal');
      return;
    }
    if ($this.closest('#recEditModal').length) {
      initSelect2WithParent($this, '#recEditModal');
      return;
    }
    // fallback to body
    initSelect2WithParent($this, null);
  });

  // When create modal is shown: reset fields and (re)init selects with explicit parent
  $(document).on('shown.bs.modal', '#recCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#recCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // When edit modal is shown: init selects with modal as parent and apply stored values
  $(document).on('shown.bs.modal', '#recEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#recEditModal');
    });

    if (tempRecEditData) {
      if (typeof tempRecEditData.raw_id !== 'undefined' && tempRecEditData.raw_id !== null) {
        $('#edit_raw_id').val(tempRecEditData.raw_id).trigger('change');
      } else {
        $('#edit_raw_id').val('').trigger('change');
      }
      if (typeof tempRecEditData.order_id !== 'undefined' && tempRecEditData.order_id !== null) {
        $('#edit_order_id').val(tempRecEditData.order_id).trigger('change');
      } else {
        $('#edit_order_id').val('').trigger('change');
      }
      if (typeof tempRecEditData.work_point_id !== 'undefined' && tempRecEditData.work_point_id !== null) {
        $('#edit_rec_work_point_id').val(tempRecEditData.work_point_id).trigger('change');
      } else {
        $('#edit_rec_work_point_id').val('').trigger('change');
      }
      tempRecEditData = null;
    }
  });

  // Edit button: populate simple inputs, store select values, set action and show modal
  document.querySelectorAll('.btn-edit-rec').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = this.dataset.id;
      document.getElementById('edit_rec_id').value = id || '';
      document.getElementById('edit_rcv_date').value = this.dataset.rcv_date || '';
      document.getElementById('edit_rcv_no_bags').value = this.dataset.rcv_no_bags || '';
      document.getElementById('edit_rcv_bag_size').value = this.dataset.rcv_bag_size || '';
      document.getElementById('edit_rcv_tones').value = this.dataset.rcv_tones || '';
      document.getElementById('edit_rec_status').value = this.dataset.status || 'Active';

      // store select values to be applied after modal shown
      tempRecEditData = {
        raw_id: (typeof this.dataset.raw_id !== 'undefined') ? this.dataset.raw_id : null,
        order_id: (typeof this.dataset.order_id !== 'undefined') ? this.dataset.order_id : null,
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      // set form action (keeps your route as-is)
      var form = document.getElementById('recEditForm');
      form.action = "{{ route('manfctr.recmaterials.update', ':id') }}".replace(':id', id);

      // show the edit modal (shown.bs.modal will init select2 and apply stored select values)
      $('#recEditModal').modal('show');
    });
  });

  // Delete button behavior preserved
  document.querySelectorAll('.btn-delete-rec').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = this.dataset.id;
      Swal.fire({
        title:'Are you sure?',
        text:'This will mark the record as Deleted and adjust stock.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Yes'
      }).then(function(res){
        if (res.isConfirmed) {
          window.location.href = "{{ route('manfctr.recmaterials.remove', ':id') }}".replace(':id', id);
        }
      });
    });
  });

  // Ensure create modal resets on show (redundant but safe)
  $('#recCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#recCreateModal');
    });
  });

});
</script>
@endsection
