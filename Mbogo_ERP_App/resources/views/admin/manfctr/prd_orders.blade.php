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
                <strong>Product Orders</strong>
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
<div class="col-12"><h3 class="mb-2 page-title">Product Orders</h3>
  @can('Register-Product-Order')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#orderCreateModal">Add Order</button>
  @endcan
</div>
<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox">
    <div class="ibox-title bg-info"><h5>Product Orders Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered dataTables-example">
          <thead><tr><th>#</th><th>Date</th><th>Product</th><th>Ordered</th><th>Issued</th><th>Unissued</th><th>Unit</th><th>Customer</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($orders as $k => $o)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $o->ord_date }}</td>
              <td>{{ optional($o->product)->product_name ?? '-' }}</td>
              <td>{{ $o->ord_qnty }}</td>
              <td>{{ $o->iss_qnty }}</td>
              <td>{{ $o->uniss_qnty }}</td>
              <td>{{ $o->ord_unit }}</td>
              <td>{{ $o->customer_name }}</td>
              <td>{{ $o->status }}</td>
              <td>
                @can('Edit-Product-Order')
                  <button class="btn btn-sm btn-warning btn-edit-order"
                    data-id="{{ encrypt($o->id) }}"
                    data-ord_date="{{ $o->ord_date }}"
                    data-prd_id="{{ $o->prd_id }}"
                    data-ord_qnty="{{ $o->ord_qnty }}"
                    data-ord_unit="{{ $o->ord_unit }}"
                    data-customer_name="{{ $o->customer_name }}"
                    data-phone_no="{{ $o->phone_no }}"
                    data-location="{{ $o->location }}"
                    data-work_point_id="{{ $o->work_point_id }}"
                    data-status="{{ $o->status }}"
                  >Edit</button>
                @endcan
                @can('Delete-Product-Order')
                  <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-order" data-id="{{ encrypt($o->id) }}">Remove</a>
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
<div class="modal fade" id="orderCreateModal" tabindex="-1"><div class="modal-dialog"><form id="orderCreateForm" action="{{ route('manfctr.orders.store') }}" method="POST">@csrf
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Product Order</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point <span style="color:red">*</span></label>
          <select name="work_point_id" class="form-control select2_demo_3" required><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select>
        </div>
      @endif

     <div class="form-row">
      <div class="form-group col"><label>Date <span style="color:red">*</span></label><input type="date" name="ord_date" class="form-control" required></div>

      <div class="form-group col"><label>Product <span style="color:red">*</span></label>
        <select name="prd_id" class="form-control select2_demo_3" required><option value="">-- Select product --</option>@foreach($products as $prod)<option value="{{ $prod->id }}">{{ $prod->product_name }}</option>@endforeach</select>
      </div>
    </div>
     <div class="form-row">
      <div class="form-group col"><label>Ordered Quantity <span style="color:red">*</span></label><input type="number" step="0.01" name="ord_qnty" class="form-control" required></div>

      <div class="form-group col"><label>Unit <span style="color:red">*</span></label><select name="ord_unit" class="form-control select2_demo_3" required><option value="Kg">Kg</option>
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
      <div class="form-group"><label>Customer Name <span style="color:red">*</span></label><input type="text" name="customer_name" class="form-control" required></div>

      <div class="form-group"><label>Phone</label><input type="text" name="phone_no" class="form-control"></div>
      <div class="form-group"><label>Location</label><input type="text" name="location" class="form-control"></div>

      <div class="form-group"><label>Status</label><select name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('orderCreateForm')" type="submit" class="btn btn-primary">Submit</button></div>
  </div>
</form></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="orderEditModal" tabindex="-1"><div class="modal-dialog"><form id="orderEditForm" method="POST">@csrf @method('PUT')
  <div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Product Order</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <input id="edit_order_id" type="hidden" name="edit_id">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point <span style="color:red">*</span></label><select id="edit_order_work_point_id" name="work_point_id" class="form-control select2_demo_3"><option value="">-- Select --</option>@foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach</select></div>
      @endif
     <div class="form-row">
      <div class="form-group col"><label>Date <span style="color:red">*</span></label><input id="edit_ord_date" type="date" name="ord_date" class="form-control" required></div>
      <div class="form-group col"><label>Product <span style="color:red">*</span></label><select id="edit_prd_id" name="prd_id" class="form-control select2_demo_3"><option value="">-- Select product --</option>@foreach($products as $prod)<option value="{{ $prod->id }}">{{ $prod->product_name }}</option>@endforeach</select></div>
     </div>
     <div class="form-row">
      <div class="form-group col"><label>Ordered Quantity <span style="color:red">*</span></label><input id="edit_ord_qnty" type="number" step="0.01" name="ord_qnty" class="form-control" required></div>
      <div class="form-group col"><label>Unit <span style="color:red">*</span></label><select id="edit_ord_unit" name="ord_unit" class="form-control select2_demo_3"><option value="Kg">Kg</option>
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
      <div class="form-group"><label>Customer Name <span style="color:red">*</span></label><input id="edit_customer_name" type="text" name="customer_name" class="form-control" required></div>
      <div class="form-group"><label>Phone</label><input id="edit_phone_no" type="text" name="phone_no" class="form-control"></div>
      <div class="form-group"><label>Location</label><input id="edit_location" type="text" name="location" class="form-control"></div>
      <div class="form-group"><label>Status</label><select id="edit_order_status" name="status" class="form-control select2_demo_3"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('orderEditForm')" type="submit" class="btn btn-primary">Update</button></div>
  </div>
</form></div></div>
<script>
document.addEventListener('DOMContentLoaded', function(){

  // temporary storage for select values to apply after modal shown (so select2 is initialized first)
  var tempOrderEditData = null;

  // safe select2 init with explicit parent selector (destroys previous instance if any)
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

  // initialize all select2_demo_3 on page with explicit parent if inside a modal
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#orderCreateModal').length) {
      initSelect2WithParent($this, '#orderCreateModal');
      return;
    }
    if ($this.closest('#orderEditModal').length) {
      initSelect2WithParent($this, '#orderEditModal');
      return;
    }
    // fallback to body
    initSelect2WithParent($this, null);
  });

  // when create modal is shown: init selects inside with explicit parent and reset fields
  $(document).on('shown.bs.modal', '#orderCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#orderCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // when edit modal is shown: init selects inside with explicit parent then apply stored values
  $(document).on('shown.bs.modal', '#orderEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#orderEditModal');
    });

    if (tempOrderEditData) {
      if (typeof tempOrderEditData.prd_id !== 'undefined') {
        $('#edit_prd_id').val(tempOrderEditData.prd_id).trigger('change');
      }
      if (typeof tempOrderEditData.work_point_id !== 'undefined') {
        $('#edit_order_work_point_id').val(tempOrderEditData.work_point_id).trigger('change');
      }
      // clear
      tempOrderEditData = null;
    }
  });

  // Edit button: populate simple inputs, store select values, set form action and show modal
  document.querySelectorAll('.btn-edit-order').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      document.getElementById('edit_order_id').value = id;
      document.getElementById('edit_ord_date').value = this.dataset.ord_date || '';
      document.getElementById('edit_ord_qnty').value = this.dataset.ord_qnty || '';
      document.getElementById('edit_ord_unit').value = this.dataset.ord_unit || 'Kg';
      document.getElementById('edit_customer_name').value = this.dataset.customer_name || '';
      document.getElementById('edit_phone_no').value = this.dataset.phone_no || '';
      document.getElementById('edit_location').value = this.dataset.location || '';
      document.getElementById('edit_order_status').value = this.dataset.status || 'Active';

      // store select data to apply after modal is shown (ensures select2 initialized first)
      tempOrderEditData = {
        prd_id: (typeof this.dataset.prd_id !== 'undefined') ? this.dataset.prd_id : null,
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      // set form action (keeps your route strings)
      const form = document.getElementById('orderEditForm');
      form.action = "{{ route('manfctr.orders.update', ':id') }}".replace(':id', id);

      // show edit modal (select2 in shown.bs.modal will init and then stored values applied)
      $('#orderEditModal').modal('show');
    });
  });

  // Delete confirmation preserved
  document.querySelectorAll('.btn-delete-order').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      Swal.fire({
        title:'Are you sure?',
        text:'This will mark the order as Deleted.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Yes'
      }).then(res => {
        if (res.isConfirmed) {
          window.location.href = "{{ route('manfctr.orders.remove', ':id') }}".replace(':id', id);
        }
      });
    });
  });

  // Reset create modal on show (also ensured above with shown.bs.modal)
  $('#orderCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#orderCreateModal');
    });
  });

});
</script>
@endsection
