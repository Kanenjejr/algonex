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
                <strong>Product Detials</strong>
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
  <h3 class="mb-2 page-title">Product Detials</h3>
  @can('Register-Product')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#productCreateModal">Add Product</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-primary"><h5>Product Detials Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Size</th>
                  <th>Company</th>
                  <th>Work Point</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($products as $k => $p)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ $p->product_name }}</td>
                  <td>{{ $p->product_size ?? '-' }}</td>
                  <td>{{ optional($p->company)->company_name ?? '-' }}</td>
                  <td>{{ optional($p->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $p->status }}</td>
                  <td>
                    @can('Edit-Product')
                      <!-- removed data-toggle/data-target to ensure JS populates fields before showing modal -->
                      <button class="btn btn-sm btn-warning btn-edit-product"
                        data-id="{{ encrypt($p->id) }}"
                        data-product_name="{{ $p->product_name }}"
                        data-product_size="{{ $p->product_size }}"
                        data-work_point_id="{{ $p->work_point_id }}"
                        data-status="{{ $p->status ?? 'Active' }}"
                      >Edit</button>
                    @endcan
                    @can('Delete-Product')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-product" data-id="{{ encrypt($p->id) }}">Remove</a>
                    @endcan
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div> <!-- /.table-responsive -->
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="productCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="productCreateForm" action="{{ route('manfctr.products.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Product Detials</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select name="work_point_id" class="form-control select2_demo_3" required>
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
          @else
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
          @endif

          <div class="form-group">
            <label>Product Name <span style="color:red">*</span></label>
            <input type="text" name="product_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Product Size</label>
            <input type="text" name="product_size" class="form-control">
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control select2_demo_3"><option value="Active" selected>Active</option><option value="Inactive">Inactive</option></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('productCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="productEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="productEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Product Detials</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_product_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_product_work_point_id" name="work_point_id" class="form-control select2_demo_3">
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
          @else
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
          @endif

          <div class="form-group">
            <label>Product Name</label>
            <input id="edit_product_name" type="text" name="product_name" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Product Size</label>
            <input id="edit_product_size" type="text" name="product_size" class="form-control">
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_product_status" name="status" class="form-control select2_demo_3">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('productEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // temp storage so we can apply select values after modal shown (ensures select2 is initialized)
  var tempProductEditData = null;

  // safe select2 init with explicit dropdownParent (destroy previous instances)
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

  // Initialize existing select2 elements on the page with explicit parents where applicable
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#productCreateModal').length) {
      initSelect2WithParent($this, '#productCreateModal');
      return;
    }
    if ($this.closest('#productEditModal').length) {
      initSelect2WithParent($this, '#productEditModal');
      return;
    }
    // fallback to body
    initSelect2WithParent($this, null);
  });

  // When Create modal shown: reset form and re-init selects inside the modal with modal as parent
  $(document).on('shown.bs.modal', '#productCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#productCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // When Edit modal shown: init select2 with modal as parent then apply stored select values
  $(document).on('shown.bs.modal', '#productEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#productEditModal');
    });

    if (tempProductEditData) {
      if (typeof tempProductEditData.work_point_id !== 'undefined' && tempProductEditData.work_point_id !== null) {
        $('#edit_product_work_point_id').val(tempProductEditData.work_point_id).trigger('change');
      } else {
        $('#edit_product_work_point_id').val('').trigger('change');
      }
      // clear temp
      tempProductEditData = null;
    }
  });

  // Edit button: populate simple inputs, store select values, set action, then show modal
  document.querySelectorAll('.btn-edit-product').forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault && e.preventDefault(); // prevent default just in case
      var encId = this.dataset.id;
      document.getElementById('edit_product_id').value = encId || '';
      document.getElementById('edit_product_name').value = this.dataset.product_name || '';
      document.getElementById('edit_product_size').value = this.dataset.product_size || '';
      document.getElementById('edit_product_status').value = this.dataset.status || 'Active';

      // store select values to apply AFTER modal shown
      tempProductEditData = {
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      // set form action (keeps your route as-is)
      var form = document.getElementById('productEditForm');
      form.action = "{{ route('manfctr.products.update', ':id') }}".replace(':id', encId);

      // show edit modal (shown.bs.modal will init select2 and apply stored values)
      $('#productEditModal').modal('show');
    });
  });

  // Delete confirmation preserved
  document.querySelectorAll('.btn-delete-product').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the product as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('manfctr.products.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  // ensure create modal resets also on show (redundant but safe)
  $('#productCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#productCreateModal');
    });
  });

});
</script>
@endsection
