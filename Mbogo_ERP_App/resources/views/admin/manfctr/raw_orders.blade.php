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
                <strong>Raw Material Orders</strong>
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
  <h3 class="mb-2 page-title">Raw Material Orders</h3>
  @can('Register-Raw-Material-Order')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#rawOrderCreateModal">Add Order</button>
  @endcan
</div>
<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12">
    <div class="ibox">
      <div class="ibox-title bg-info"><h5>Raw Material Orders Table</h5></div>
      <div class="ibox-content">
        <div class="table-responsive">
          <table class="table table-striped table-bordered dataTables-example">
            <thead>
              <tr>
                <th>#</th><th>Date</th><th>Raw Material</th><th>No Bags</th><th>Bag Size</th><th>Ordered Tones</th>
                <th>Recv Tones</th><th>Unrecv Tones</th><th>Order Price</th><th>Customer</th><th>Phone</th><th>Location</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($orders as $k => $o)
              <tr>
                <td>{{ $k+1 }}</td>
                <td>{{ $o->ord_date }}</td>
                <td>{{ optional($o->raw)->material_name ?? '-' }}</td>
                <td>{{ $o->ord_no_bags }}</td>
                <td>{{ $o->ord_bag_size }}</td>
                <td>{{ $o->ord_tones }}</td>
                <td>{{ number_format($o->recv_tones ?? 0,2) }}</td>
                <td>{{ number_format($o->unrecv_tones ?? $o->ord_tones,2) }}</td>
                <td>{{ number_format($o->order_price ?? 0,2) }}</td>
                <td>{{ $o->customer_name }}</td>
                <td>{{ $o->phone_no }}</td>
                <td>{{ $o->location }}</td>
                <td>{{ $o->status }}</td>
                <td>
                  @can('Edit-Raw-Material-Order')
                    <button class="btn btn-sm btn-warning btn-edit-order"
                      data-id="{{ encrypt($o->id) }}"
                      data-ord_date="{{ $o->ord_date }}"
                      data-ord_no_bags="{{ $o->ord_no_bags }}"
                      data-ord_bag_size="{{ $o->ord_bag_size }}"
                      data-ord_tones="{{ $o->ord_tones }}"
                      data-recv_tones="{{ $o->recv_tones ?? 0 }}"
                      data-raw_id="{{ $o->raw_id }}"
                      data-order_price="{{ $o->order_price }}"
                      data-customer_name="{{ $o->customer_name }}"
                      data-phone_no="{{ $o->phone_no }}"
                      data-location="{{ $o->location }}"
                      data-work_point_id="{{ $o->work_point_id }}"
                      data-status="{{ $o->status }}"
                    >Edit</button>
                  @endcan
                  @can('Delete-Raw-Material-Order')
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-order" data-id="{{ encrypt($o->id) }}">Remove</a>
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
<div class="modal fade" id="rawOrderCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form id="rawOrderCreateForm" action="{{ route('manfctr.raworders.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Raw Material Order</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span class="text-danger">*</span></label>
              <select name="work_point_id" class="form-control select2_demo_3" required>
                <option value="">-- Select --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
          @else
            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
          @endif

          <div class="form-group"><label>Date</label><input type="date" name="ord_date" class="form-control" required></div>

          <div class="form-row">
            <div class="form-group col">
            <label>Raw Material</label>
            <select name="raw_id" class="form-control select2_demo_3" required>
              <option value="">-- Select raw material --</option>
              @foreach($raws as $r)<option value="{{ $r->id }}">{{ $r->material_name }}</option>@endforeach
            </select>
          </div>

          <div class="form-group col"><label>No Bags</label><input type="number" name="ord_no_bags" class="form-control" required></div>
        </div>
          <div class="form-row">
            <div class="form-group col"><label>Bag Size</label><input type="number" step="0.01" name="ord_bag_size" class="form-control" required></div>

          <div class="form-group col"><label>Ordered Tones</label><input type="number" step="0.01" name="ord_tones" class="form-control" required></div>
        </div>
          <div class="form-group"><label>Order Price</label><input type="number" step="0.01" name="order_price" class="form-control"></div>

          <div class="form-row">
          <div class="form-group col"><label>Customer Name</label><input type="text" name="customer_name" class="form-control" required></div>

          <div class="form-group col"><label>Phone</label><input type="text" name="phone_no" class="form-control"></div>
          </div>

          <div class="form-row">
          <div class="form-group col"><label>Location</label><input type="text" name="location" class="form-control"></div>

          <div class="form-group col"><label>Status</label><select name="status" class="form-control"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button onclick="handleConfirmSubmit('rawOrderCreateForm')" type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="rawOrderEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form id="rawOrderEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Raw Material Order</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_order_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span class="text-danger">*</span></label>
              <select id="edit_order_work_point_id" name="work_point_id" class="form-control select2_demo_3">
                <option value="">-- Select --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
          @endif

          <div class="form-group"><label>Date</label><input id="edit_ord_date" type="date" name="ord_date" class="form-control" required></div>

          <div class="form-row">
          <div class="form-group col"><label>Raw</label>
            <select id="edit_raw_id" name="raw_id" class="form-control select2_demo_3" required>
              <option value="">-- Select raw material --</option>
              @foreach($raws as $r)<option value="{{ $r->id }}">{{ $r->material_name }}</option>@endforeach
            </select>
          </div>

          <div class="form-group col"><label>No Bags</label><input id="edit_ord_no_bags" type="number" name="ord_no_bags" class="form-control" required></div>
        </div>

          <div class="form-row">
          <div class="form-group col"><label>Bag Size</label><input id="edit_ord_bag_size" type="number" step="0.01" name="ord_bag_size" class="form-control" required></div>

          <div class="form-group col"><label>Ordered Tones</label><input id="edit_ord_tones" type="number" step="0.01" name="ord_tones" class="form-control" required></div>
          </div>
          <div class="form-group"><label>Recv Tones</label><input id="edit_recv_tones" type="text" readonly class="form-control" value="0"></div>

          <div class="form-group"><label>Order Price</label><input id="edit_order_price" type="number" step="0.01" name="order_price" class="form-control"></div>

          <div class="form-row">
          <div class="form-group col"><label>Customer Name</label><input id="edit_customer_name" type="text" name="customer_name" class="form-control" required></div>

          <div class="form-group col"><label>Phone</label><input id="edit_phone_no" type="text" name="phone_no" class="form-control"></div>
          </div>
          <div class="form-row">
          <div class="form-group col"><label>Location</label><input id="edit_location" type="text" name="location" class="form-control"></div>
          <div class="form-group col"><label>Status</label><select id="edit_order_status" name="status" class="form-control"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button onclick="handleConfirmSubmit('rawOrderEditForm')" type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // Temporary storage to apply select values after modal is shown
  var tempRawOrderEditData = null;

  // Safe select2 init with explicit dropdownParent selector
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

  // Initialize all select2_demo_3 on page, using modal parents when inside modals
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#rawOrderCreateModal').length) {
      initSelect2WithParent($this, '#rawOrderCreateModal');
      return;
    }
    if ($this.closest('#rawOrderEditModal').length) {
      initSelect2WithParent($this, '#rawOrderEditModal');
      return;
    }
    initSelect2WithParent($this, null);
  });

  // When create modal is shown: re-init selects inside it with explicit dropdownParent and reset fields
  $(document).on('shown.bs.modal', '#rawOrderCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#rawOrderCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // When edit modal is shown: init selects inside it and then apply temporarily stored select values
  $(document).on('shown.bs.modal', '#rawOrderEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#rawOrderEditModal');
    });

    if (tempRawOrderEditData) {
      if (typeof tempRawOrderEditData.raw_id !== 'undefined' && tempRawOrderEditData.raw_id !== null) {
        $('#edit_raw_id').val(tempRawOrderEditData.raw_id).trigger('change');
      } else {
        $('#edit_raw_id').val('').trigger('change');
      }
      if (typeof tempRawOrderEditData.work_point_id !== 'undefined' && tempRawOrderEditData.work_point_id !== null) {
        $('#edit_order_work_point_id').val(tempRawOrderEditData.work_point_id).trigger('change');
      } else {
        $('#edit_order_work_point_id').val('').trigger('change');
      }
      // clear temp storage
      tempRawOrderEditData = null;
    }
  });

  // Edit button: populate non-select fields immediately, store select values and show modal
  document.querySelectorAll('.btn-edit-order').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = this.dataset.id;
      document.getElementById('edit_order_id').value = id || '';
      document.getElementById('edit_ord_date').value = this.dataset.ord_date || '';
      document.getElementById('edit_ord_no_bags').value = this.dataset.ord_no_bags || '';
      document.getElementById('edit_ord_bag_size').value = this.dataset.ord_bag_size || '';
      document.getElementById('edit_ord_tones').value = this.dataset.ord_tones || '';
      document.getElementById('edit_recv_tones').value = this.dataset.recv_tones || '0';
      document.getElementById('edit_order_price').value = this.dataset.order_price || '';
      document.getElementById('edit_customer_name').value = this.dataset.customer_name || '';
      document.getElementById('edit_phone_no').value = this.dataset.phone_no || '';
      document.getElementById('edit_location').value = this.dataset.location || '';
      document.getElementById('edit_order_status').value = this.dataset.status || 'Active';

      // store select values to apply after modal shown (ensures select2 is initialized first)
      tempRawOrderEditData = {
        raw_id: (typeof this.dataset.raw_id !== 'undefined') ? this.dataset.raw_id : null,
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      // set form action (keep your route unchanged)
      var form = document.getElementById('rawOrderEditForm');
      form.action = "{{ route('manfctr.raworders.update', ':id') }}".replace(':id', id);

      // show edit modal (shown.bs.modal handler will init select2 and apply stored values)
      $('#rawOrderEditModal').modal('show');
    });
  });

  // Delete buttons: preserve original behavior
  document.querySelectorAll('.btn-delete-order').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the order as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete'
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('manfctr.raworders.remove', ':id') }}".replace(':id', id);
        }
      });
    });
  });

  // Ensure create modal resets on show (redundant but safe)
  $('#rawOrderCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#rawOrderCreateModal');
    });
  });

});
</script>
@endsection

