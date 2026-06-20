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
                <strong>Product Price Detials</strong>
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
  <h3 class="mb-2 page-title">Product Price Details</h3>
  @can('Register-Product-Price')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#prdPriceCreateModal">Add Product Price</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-success"><h5>Product Price Details Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Company</th>
                  <th>Work Point</th>
                  <th>Entered By</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($prdPrices as $k => $pp)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ optional($pp->product)->product_name ?? '-' }}</td>
                  <td>{{ $pp->RawPrice }}</td>
                  <td>{{ optional($pp->company)->company_name ?? '-' }}</td>
                  <td>{{ optional($pp->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ optional($pp->user)->name ?? '-' }}</td>
                  <td>{{ $pp->Status }}</td>
                  <td>
                    @can('Edit-Product-Price')
                      <!-- remove auto data-toggle/data-target so JS can populate before showing -->
                      <button class="btn btn-sm btn-warning btn-edit-prdprice"
                        data-id="{{ encrypt($pp->id) }}"
                        data-Product_id="{{ $pp->Product_id }}"
                        data-RawPrice="{{ $pp->RawPrice }}"
                        data-work_point_id="{{ $pp->work_point_id }}"
                        data-status="{{ $pp->Status }}"
                      >Edit</button>
                    @endcan
                    @can('Delete-Product-Price')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-prdprice" data-id="{{ encrypt($pp->id) }}">Remove</a>
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
<div class="modal fade" id="prdPriceCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="prdPriceCreateForm" action="{{ route('manfctr.prdprices.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Product Price Details</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
            <label>Product <span style="color:red">*</span></label>
            <select name="Product_id" class="form-control select2_demo_3" required>
              <option value="">-- Select product --</option>
              @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->product_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Price <span style="color:red">*</span></label>
            <input type="number" step="0.01" name="RawPrice" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="Status" class="form-control select2_demo_3"><option value="Active" selected>Active</option><option value="Inactive">Inactive</option></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('prdPriceCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="prdPriceEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="prdPriceEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Product Price Details</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_prdprice_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_prdprice_work_point_id" name="work_point_id" class="form-control select2_demo_3">
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
            <label>Product</label>
            <select id="edit_Product_id" name="Product_id" class="form-control select2_demo_3">
              <option value="">-- Select product --</option>
              @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->product_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Price</label>
            <input id="edit_RawPrice" type="number" step="0.01" name="RawPrice" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_prdprice_Status" name="Status" class="form-control select2_demo_3">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('prdPriceEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // temp store to apply select values AFTER modal shown (so select2 exists)
  var tempPrdPriceEditData = null;

  // Safe Select2 init: destroy previous instance, init with explicit dropdownParent
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

  // initialize all select2_demo_3 on page; give modal parents when inside modals
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#prdPriceCreateModal').length) {
      initSelect2WithParent($this, '#prdPriceCreateModal');
      return;
    }
    if ($this.closest('#prdPriceEditModal').length) {
      initSelect2WithParent($this, '#prdPriceEditModal');
      return;
    }
    initSelect2WithParent($this, null);
  });

  // When Create modal shown: reset form and re-init selects with modal as parent
  $(document).on('shown.bs.modal', '#prdPriceCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#prdPriceCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // When Edit modal shown: init selects then apply stored values
  $(document).on('shown.bs.modal', '#prdPriceEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#prdPriceEditModal');
    });

    if (tempPrdPriceEditData) {
      if (typeof tempPrdPriceEditData.Product_id !== 'undefined' && tempPrdPriceEditData.Product_id !== null) {
        $('#edit_Product_id').val(tempPrdPriceEditData.Product_id).trigger('change');
      } else {
        $('#edit_Product_id').val('').trigger('change');
      }
      if (typeof tempPrdPriceEditData.work_point_id !== 'undefined' && tempPrdPriceEditData.work_point_id !== null) {
        $('#edit_prdprice_work_point_id').val(tempPrdPriceEditData.work_point_id).trigger('change');
      } else {
        $('#edit_prdprice_work_point_id').val('').trigger('change');
      }
      tempPrdPriceEditData = null;
    }
  });

  // Edit button: populate non-select fields, stash select values, set action, then show modal
  document.querySelectorAll('.btn-edit-prdprice').forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault && e.preventDefault();
      var encId = this.dataset.id;
      document.getElementById('edit_prdprice_id').value = encId || '';
      // accept different dataset casing
      var productId = this.dataset.product_id || this.dataset.Product_id || null;
      var rawPrice = this.dataset.rawprice || this.dataset.RawPrice || '';
      document.getElementById('edit_RawPrice').value = (rawPrice !== null) ? rawPrice : '';
      document.getElementById('edit_prdprice_Status').value = this.dataset.status || 'Active';

      // stash select values to apply after modal shown
      tempPrdPriceEditData = {
        Product_id: productId,
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      // set form action (keeps your route unchanged)
      var form = document.getElementById('prdPriceEditForm');
      form.action = "{{ route('manfctr.prdprices.update', ':id') }}".replace(':id', encId);

      // show modal; shown.bs.modal handler will init select2 and apply stored selects
      $('#prdPriceEditModal').modal('show');
    });
  });

  // Delete preserve behavior
  document.querySelectorAll('.btn-delete-prdprice').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the product price as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('manfctr.prdprices.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  // Extra: ensure create modal resets on show (safe)
  $('#prdPriceCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#prdPriceCreateModal');
    });
  });

});
</script>
@endsection
