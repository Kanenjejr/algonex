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
                <strong>Issued Product</strong>
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
<div class="col-12"><h3 class="mb-2 page-title">Issued Products</h3>
  @can('Register-Issued-Product')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#issCreateModal">Add Issue</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox">
    <div class="ibox-title bg-warning"><h5>Issued Products Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered dataTables-example">
          <thead><tr><th>#</th><th>Date</th><th>Product</th><th>Qty</th><th>Unit</th><th>Order</th><th>Received By</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($issues as $k => $i)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $i->issue_date }}</td>
              <td>{{ optional($i->product)->product_name ?? '-' }}</td>
              <td>{{ $i->issue_qnty }}</td>
              <td>{{ $i->iss_unit }}</td>
              <td>{{ optional($i->order)->customer_name ?? '-' }}</td>
              <td>{{ $i->received_by }}</td>
              <td>{{ $i->status }}</td>
              <td>
                @can('Edit-Issued-Product')
                  <button class="btn btn-sm btn-warning btn-edit-iss" data-id="{{ encrypt($i->id) }}"
                    data-issue_date="{{ $i->issue_date }}"
                    data-prd_id="{{ $i->prd_id }}"
                    data-issue_qnty="{{ $i->issue_qnty }}"
                    data-iss_unit="{{ $i->iss_unit }}"
                    data-order_id="{{ $i->order_id }}"
                    data-received_by="{{ $i->received_by }}"
                    data-work_point_id="{{ $i->work_point_id }}"
                    data-status="{{ $i->status }}"
                  >Edit</button>
                @endcan
                @can('Delete-Issued-Product')
                  <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-iss" data-id="{{ encrypt($i->id) }}">Remove</a>
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
<div class="modal fade" id="issCreateModal" tabindex="-1"><div class="modal-dialog"><form id="issCreateForm" action="{{ route('manfctr.iss.store') }}" method="POST">@csrf
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Issued Products</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point <span style="color:red">*</span></label><select name="work_point_id" class="form-control select2_demo_3" required><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select></div>
      @endif

     <div class="form-row">
      <div class="form-group col"><label>Date <span style="color:red">*</span></label><input type="date" name="issue_date" class="form-control" required></div>

      <div class="form-group col"><label>Product <span style="color:red">*</span></label>
        <select name="prd_id" class="form-control select2_demo_3" required>
          <option value="">-- Select product --</option>
          @foreach($products as $prod)<option value="{{ $prod->id }}">{{ $prod->product_name }}</option>@endforeach
        </select>
      </div>
    </div>
     <div class="form-row">
      <div class="form-group col"><label>Quantity <span style="color:red">*</span></label><input type="number" step="0.01" name="issue_qnty" class="form-control" required></div>

      <div class="form-group col"><label>Unit <span style="color:red">*</span></label><select name="iss_unit" class="form-control" required><option value="Kg">Kg</option>
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
</select></div>
    </div>
      <div class="form-group"><label>Order (optional)</label><select name="order_id" class="form-control select2_demo_3"><option value="">-- Select order --</option>@foreach($orders as $o)<option value="{{ $o->id }}">#{{ $o->id }} - {{ $o->customer_name }} (Left: {{ $o->uniss_qnty }})</option>@endforeach</select></div>

      <div class="form-group"><label>Received By</label><input type="text" name="received_by" class="form-control"></div>

      <div class="form-group"><label>Status</label><select name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('issCreateForm')" type="submit" class="btn btn-primary">Submit</button></div>
  </div>
</form></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="issEditModal" tabindex="-1"><div class="modal-dialog"><form id="issEditForm" method="POST">@csrf @method('PUT')
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Issued Products</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <input id="edit_iss_id" type="hidden" name="edit_id">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point <span style="color:red">*</span></label><select id="edit_iss_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select></div>
      @endif

     <div class="form-row">
      <div class="form-group col"><label>Date <span style="color:red">*</span></label><input id="edit_issue_date" type="date" name="issue_date" class="form-control" required></div>
      <div class="form-group col"><label>Product <span style="color:red">*</span></label><select id="edit_prd_id" name="prd_id" class="form-control select2_demo_3"><option value="">-- Select product --</option>@foreach($products as $prod)<option value="{{ $prod->id }}">{{ $prod->product_name }}</option>@endforeach</select></div>
     </div>

     <div class="form-row">
      <div class="form-group col"><label>Quantity <span style="color:red">*</span></label><input id="edit_issue_qnty" type="number" step="0.01" name="issue_qnty" class="form-control" required></div>
      <div class="form-group col"><label>Unit <span style="color:red">*</span></label><select id="edit_iss_unit" name="iss_unit" class="form-control"><option value="Kg">Kg</option>
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
      </div>
      <div class="form-group"><label>Order</label><select id="edit_order_id" name="order_id" class="form-control select2_demo_3"><option value="">-- Select order --</option>@foreach($orders as $o)<option value="{{ $o->id }}">#{{ $o->id }} - {{ $o->customer_name }} (Left: {{ $o->uniss_qnty }})</option>@endforeach</select></div>
      <div class="form-group"><label>Received By</label><input id="edit_received_by" type="text" name="received_by" class="form-control"></div>
      <div class="form-group"><label>Status</label><select id="edit_iss_status" name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('issEditForm')" type="submit" class="btn btn-primary">Update</button></div>
  </div>
</form></div></div>

<script>
document.addEventListener('DOMContentLoaded', function(){

  // safe init helper: destroy previous select2 if exists, then init with explicit dropdownParent
  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    // destroy old instance if it exists
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

  // Initialize all select2_demo_3 on page, but give explicit parent per modal if inside one
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#issCreateModal').length) {
      initSelect2WithParent($this, '#issCreateModal'); // create modal explicit parent
      return;
    }
    if ($this.closest('#issEditModal').length) {
      initSelect2WithParent($this, '#issEditModal'); // edit modal explicit parent
      return;
    }
    // fallback
    initSelect2WithParent($this, null);
  });

  // Re-init selects inside create modal when shown to ensure dropdownParent is correct
  $(document).on('shown.bs.modal', '#issCreateModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#issCreateModal'); // explicit dropdownParent: $('#issCreateModal')
    });
  });

  // Re-init selects inside edit modal when shown to ensure dropdownParent is correct
  $(document).on('shown.bs.modal', '#issEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#issEditModal'); // explicit dropdownParent: $('#issEditModal')
    });
  });

  // Edit button: populate values properly, set action and show modal
  document.querySelectorAll('.btn-edit-iss').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      // simple inputs
      document.getElementById('edit_iss_id').value = id;
      document.getElementById('edit_issue_date').value = this.dataset.issue_date || '';
      document.getElementById('edit_issue_qnty').value = this.dataset.issue_qnty || '';
      document.getElementById('edit_iss_unit').value = this.dataset.iss_unit || 'Kg';
      document.getElementById('edit_received_by').value = this.dataset.received_by || '';
      document.getElementById('edit_iss_status').value = this.dataset.status || 'Active';

      // selects that are select2: set via jQuery and trigger change so select2 reflects
      if (typeof this.dataset.prd_id !== 'undefined') {
        $('#edit_prd_id').val(this.dataset.prd_id).trigger('change');
      } else {
        $('#edit_prd_id').val('').trigger('change');
      }

      if (typeof this.dataset.order_id !== 'undefined') {
        $('#edit_order_id').val(this.dataset.order_id).trigger('change');
      } else {
        $('#edit_order_id').val('').trigger('change');
      }

      if (typeof this.dataset.work_point_id !== 'undefined') {
        $('#edit_iss_work_point_id').val(this.dataset.work_point_id).trigger('change');
      } else {
        $('#edit_iss_work_point_id').val('').trigger('change');
      }

      // set form action (keeps your route unchanged)
      const form = document.getElementById('issEditForm');
      form.action = "{{ route('manfctr.iss.update', ':id') }}".replace(':id', id);

      // show edit modal
      $('#issEditModal').modal('show');
    });
  });

  // Delete button: existing behavior preserved
  document.querySelectorAll('.btn-delete-iss').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      Swal.fire({
        title:'Are you sure?',
        text:'This will mark the issue record as Deleted and adjust stock/orders.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Yes'
      }).then(res => { if(res.isConfirmed) window.location.href = "{{ route('manfctr.iss.remove', ':id') }}".replace(':id', id); });
    });
  });

  // Reset create modal fields on show (ensure select2 values cleared and re-init with modal as parent)
  $('#issCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#issCreateModal');
    });
  });

});
</script>
@endsection

