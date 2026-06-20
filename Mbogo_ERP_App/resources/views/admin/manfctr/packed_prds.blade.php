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
                <strong>Packed Product</strong>
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
  <h3 class="mb-2 page-title">Packed Products</h3>
  @can('Register-Packed-Product')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#packCreateModal">Add Pack</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox">
    <div class="ibox-title bg-info"><h5>Packed Products Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered dataTables-example">
          <thead>
            <tr><th>#</th><th>Date</th><th>Product</th><th>Quantity</th><th>Unit</th><th>Work Point</th><th>Packed By</th><th>Status</th><th>Action</th></tr>
          </thead>
          <tbody>
            @foreach($packs as $k => $p)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $p->pck_date }}</td>
              <td>{{ optional($p->product)->product_name ?? '-' }}</td>
              <td>{{ $p->pck_qnty }}</td>
              <td>{{ $p->pck_unit }}</td>
              <td>{{ optional($p->workpoint)->work_name ?? '-' }}</td>
              <td>{{ optional($p->user)->name ?? '-' }}</td>
              <td>{{ $p->status }}</td>
              <td>
                @can('Edit-Packed-Product')
                  <button class="btn btn-sm btn-warning btn-edit-pack"
                    data-id="{{ encrypt($p->id) }}"
                    data-pck_date="{{ $p->pck_date }}"
                    data-prd_id="{{ $p->prd_id }}"
                    data-pck_qnty="{{ $p->pck_qnty }}"
                    data-pck_unit="{{ $p->pck_unit }}"
                    data-work_point_id="{{ $p->work_point_id }}"
                    data-status="{{ $p->status }}"
                  >Edit</button>
                @endcan
                @can('Delete-Packed-Product')
                  <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-pack" data-id="{{ encrypt($p->id) }}">Remove</a>
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

{{-- Create Modal --}}
<div class="modal fade" id="packCreateModal" tabindex="-1"><div class="modal-dialog"><form id="packCreateForm" action="{{ route('manfctr.packed.store') }}" method="POST">@csrf
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Packed Product</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group">
          <label>Work Point <span style="color:red">*</span></label>
          <select name="work_point_id" class="form-control select2_demo_3" required>
            <option value="">-- Select --</option>
            @foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach
          </select>
        </div>
      @endif

      <div class="form-group"><label>Date <span style="color:red">*</span></label><input type="date" name="pck_date" class="form-control" required></div>

      <div class="form-group"><label>Product <span style="color:red">*</span></label>
        <select name="prd_id" class="form-control select2_demo_3" required>
          <option value="">-- Select product --</option>
          @foreach($products as $prod)<option value="{{ $prod->id }}">{{ $prod->product_name }}</option>@endforeach
        </select>
      </div>

      <div class="form-group"><label>Quantity <span style="color:red">*</span></label><input type="number" step="0.01" name="pck_qnty" class="form-control" required></div>

      <div class="form-group"><label>Unit <span style="color:red">*</span></label>
        <select name="pck_unit" class="form-control select2_demo_3" required>
          <option value="Kg">Kg</option>
        <option value="g">g</option>
        <option value="Ton">Ton</option>
        <option value="Pc">Pc</option>
        <option value="Box">Box</option>
        <option value="Carton">Carton</option>
        <option value="Pack">Pack</option>
        <option value="Bottle">Bottle</option>
        <option value="Can">Can</option>
        <option value="Bag">Bag</option>
        <option value="Sack">Sack</option>
        <option value="L">L</option>
        <option value="ml">ml</option>
        <option value="m">m</option>
        <option value="cm">cm</option>
        </select>
      </div>

      <div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('packCreateForm')" type="submit" class="btn btn-primary">Submit</button></div>
  </div>
</form></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="packEditModal" tabindex="-1"><div class="modal-dialog"><form id="packEditForm" method="POST">@csrf @method('PUT')
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Packed Product</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="edit_pack_id" name="edit_id">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point <span style="color:red">*</span></label>
          <select id="edit_pack_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select>
        </div>
      @endif
      <div class="form-group"><label>Date <span style="color:red">*</span></label><input id="edit_pck_date" type="date" name="pck_date" class="form-control" required></div>
      <div class="form-group"><label>Product <span style="color:red">*</span></label><select id="edit_prd_id" name="prd_id" class="form-control select2_demo_3"><option value="">-- Select product --</option>@foreach($products as $prod)<option value="{{ $prod->id }}">{{ $prod->product_name }}</option>@endforeach</select></div>
      <div class="form-group"><label>Quantity <span style="color:red">*</span></label><input id="edit_pck_qnty" type="number" step="0.01" name="pck_qnty" class="form-control" required></div>
      <div class="form-group"><label>Unit <span style="color:red">*</span></label><select id="edit_pck_unit" name="pck_unit" class="form-control select2_demo_3"><option value="Kg">Kg</option>
    <option value="g">g</option>
    <option value="Ton">Ton</option>
    <option value="Pc">Pc</option>
    <option value="Box">Box</option>
    <option value="Carton">Carton</option>
    <option value="Pack">Pack</option>
    <option value="Bottle">Bottle</option>
    <option value="Can">Can</option>
    <option value="Bag">Bag</option>
    <option value="Sack">Sack</option>
    <option value="L">L</option>
    <option value="ml">ml</option>
    <option value="m">m</option>
    <option value="cm">cm</option></select></div>
      <div class="form-group"><label>Status</label><select id="edit_pack_status" name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('packEditForm')" type="submit" class="btn btn-primary">Update</button></div>
  </div>
</form></div></div>

<script>
document.addEventListener('DOMContentLoaded', function(){

  // temp storage for data to apply to selects once modal is shown (ensures select2 set after init)
  var tempPackEditData = null;

  // Helper to safely init a select2 element with an explicit parent selector (destroys previous instance first)
  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) {
      try { $el.select2('destroy'); } catch(e) { /* ignore */ }
    }
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({
      width: '100%',
      theme: 'bootstrap4',
      dropdownParent: $parent
    });
  }

  // Initialize select2s that are not inside modals (or initialize with modal parent explicitly if inside)
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#packCreateModal').length) {
      initSelect2WithParent($this, '#packCreateModal');
      return;
    }
    if ($this.closest('#packEditModal').length) {
      initSelect2WithParent($this, '#packEditModal');
      return;
    }
    // fallback
    initSelect2WithParent($this, null);
  });

  // When create modal is shown: re-init selects inside it with explicit dropdownParent and reset fields
  $(document).on('shown.bs.modal', '#packCreateModal', function () {
    var $modal = $(this);
    // reset form
    var form = $modal.find('form')[0];
    if (form) form.reset();
    // init select2 for selects inside this modal with explicit parent and clear values
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#packCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // When edit modal is shown: init select2 with dropdownParent set to the modal,
  // then apply previously stored tempPackEditData to select2s (so they reflect values)
  $(document).on('shown.bs.modal', '#packEditModal', function () {
    var $modal = $(this);
    // init selects inside edit modal
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#packEditModal');
    });

    // apply stored values (if any)
    if (tempPackEditData) {
      if (typeof tempPackEditData.prd_id !== 'undefined') {
        $('#edit_prd_id').val(tempPackEditData.prd_id).trigger('change');
      }
      if (typeof tempPackEditData.work_point_id !== 'undefined') {
        $('#edit_pack_work_point_id').val(tempPackEditData.work_point_id).trigger('change');
      }
      // clear temp storage
      tempPackEditData = null;
    }
  });

  // Edit button click: populate simple inputs, store select values, set form action, show modal
  document.querySelectorAll('.btn-edit-pack').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = this.dataset.id;
      document.getElementById('edit_pack_id').value = id || '';
      document.getElementById('edit_pck_date').value = this.dataset.pck_date || '';
      document.getElementById('edit_pck_qnty').value = this.dataset.pck_qnty || '';
      document.getElementById('edit_pck_unit').value = this.dataset.pck_unit || 'Kg';
      document.getElementById('edit_pack_status').value = this.dataset.status || 'Active';

      // store select values to apply after modal shown
      tempPackEditData = {
        prd_id: (typeof this.dataset.prd_id !== 'undefined') ? this.dataset.prd_id : null,
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      // set form action (keeps your route string as-is)
      var form = document.getElementById('packEditForm');
      form.action = "{{ route('manfctr.packed.update', ':id') }}".replace(':id', id);

      // finally show the modal (select2 will be initialized in shown.bs.modal and then values applied)
      $('#packEditModal').modal('show');
    });
  });

  // Delete buttons preserved behavior
  document.querySelectorAll('.btn-delete-pack').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will mark the pack record as Deleted and adjust stock.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then(function(res){
        if (res.isConfirmed) {
          window.location.href = "{{ route('manfctr.packed.remove', ':id') }}".replace(':id', id);
        }
      });
    });
  });

  // Reset create modal fields on show (ensure select2 values cleared and re-init with modal as parent)
  $('#packCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#packCreateModal');
    });
  });

});
</script>
@endsection
