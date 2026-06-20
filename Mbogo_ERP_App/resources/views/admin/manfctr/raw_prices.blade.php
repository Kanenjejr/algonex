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
                <strong>Raw Material Price Detials</strong>
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
  <h3 class="mb-2 page-title">Raw Material Price Details</h3>
  @can('Register-Raw-Material-Price')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#rawPriceCreateModal">Add Raw Price</button>
  @endcan
</div>
<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-warning"><h5>Raw Material Price Details Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Raw Material</th>
                  <th>Price</th>
                  <th>Company</th>
                  <th>Work Point</th>
                  <th>Entered By</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($rawPrices as $k => $rp)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ optional($rp->rawMaterial)->material_name ?? '-' }}</td>
                  <td>{{ $rp->RawPrice }}</td>
                  <td>{{ optional($rp->company)->company_name ?? '-' }}</td>
                  <td>{{ optional($rp->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ optional($rp->user)->name ?? '-' }}</td>
                  <td>{{ $rp->Status }}</td>
                  <td>
                    @can('Edit-Raw-Material-Price')
                      <!-- removed auto data-toggle from button so JS can populate then show modal -->
                      <button class="btn btn-sm btn-warning btn-edit-rawprice"
                        data-id="{{ encrypt($rp->id) }}"
                        data-Raw_id="{{ $rp->Raw_id }}"
                        data-RawPrice="{{ $rp->RawPrice }}"
                        data-work_point_id="{{ $rp->work_point_id }}"
                        data-status="{{ $rp->Status }}"
                      >Edit</button>
                    @endcan
                    @can('Delete-Raw-Material-Price')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-rawprice" data-id="{{ encrypt($rp->id) }}">Remove</a>
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
{{-- Create Modal --}}
<div class="modal fade" id="rawPriceCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="rawPriceCreateForm" action="{{ route('manfctr.rawprices.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Raw Material Price Details</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
            <label>Raw Material <span style="color:red">*</span></label>
            <select name="Raw_id" class="form-control select2_demo_3" required>
              <option value="">-- Select raw material --</option>
              @foreach($rawMaterials as $r)
                <option value="{{ $r->id }}">{{ $r->material_name }}</option>
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
          <button type="submit" onclick="handleConfirmSubmit('rawPriceCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="rawPriceEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="rawPriceEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Raw Material Price Details</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_rawprice_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_rawprice_work_point_id" name="work_point_id" class="form-control select2_demo_3">
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
            <label>Raw Material</label>
            <select id="edit_Raw_id" name="Raw_id" class="form-control select2_demo_3">
              <option value="">-- Select raw material --</option>
              @foreach($rawMaterials as $r)
                <option value="{{ $r->id }}">{{ $r->material_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Price</label>
            <input id="edit_RawPrice_field" type="number" step="0.01" name="RawPrice" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_rawprice_Status" name="Status" class="form-control select2_demo_3">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('rawPriceEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // temp store to apply select values after modal is shown
  var tempRawPriceEditData = null;

  // safe init: destroy previous select2 if present, then init with explicit parent
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

  // initialize existing selects on the page, giving explicit modal parents when inside modals
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#rawPriceCreateModal').length) {
      initSelect2WithParent($this, '#rawPriceCreateModal');
      return;
    }
    if ($this.closest('#rawPriceEditModal').length) {
      initSelect2WithParent($this, '#rawPriceEditModal');
      return;
    }
    initSelect2WithParent($this, null);
  });

  // when create modal is shown: reset and init selects with modal as parent
  $(document).on('shown.bs.modal', '#rawPriceCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#rawPriceCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // when edit modal is shown: init selects and then apply stored select values
  $(document).on('shown.bs.modal', '#rawPriceEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#rawPriceEditModal');
    });

    if (tempRawPriceEditData) {
      if (typeof tempRawPriceEditData.Raw_id !== 'undefined' && tempRawPriceEditData.Raw_id !== null) {
        $('#edit_Raw_id').val(tempRawPriceEditData.Raw_id).trigger('change');
      } else {
        $('#edit_Raw_id').val('').trigger('change');
      }
      if (typeof tempRawPriceEditData.work_point_id !== 'undefined' && tempRawPriceEditData.work_point_id !== null) {
        $('#edit_rawprice_work_point_id').val(tempRawPriceEditData.work_point_id).trigger('change');
      } else {
        $('#edit_rawprice_work_point_id').val('').trigger('change');
      }
      tempRawPriceEditData = null;
    }
  });

  // Edit button: populate simple inputs, stash select values, set form action and show modal
  document.querySelectorAll('.btn-edit-rawprice').forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault && e.preventDefault();
      var encId = this.dataset.id;
      document.getElementById('edit_rawprice_id').value = encId || '';
      // Accept different dataset casing (Raw_id / raw_id / RawPrice / rawprice)
      var rawId = this.dataset.raw_id || this.dataset.Raw_id || null;
      var rawPrice = this.dataset.rawprice || this.dataset.RawPrice || '';
      document.getElementById('edit_RawPrice_field').value = (rawPrice !== null) ? rawPrice : '';

      document.getElementById('edit_rawprice_Status').value = this.dataset.status || 'Active';

      // stash select values to apply after modal is shown (so select2 exists)
      tempRawPriceEditData = {
        Raw_id: rawId,
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      // set form action (keep your route unchanged)
      var form = document.getElementById('rawPriceEditForm');
      form.action = "{{ route('manfctr.rawprices.update', ':id') }}".replace(':id', encId);

      // show modal; shown.bs.modal handler will init select2 and apply stashed selects
      $('#rawPriceEditModal').modal('show');
    });
  });

  // delete buttons: preserve behavior
  document.querySelectorAll('.btn-delete-rawprice').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the raw price as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('manfctr.rawprices.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  // ensure create modal resets on show (extra safety)
  $('#rawPriceCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#rawPriceCreateModal');
    });
  });

});
</script>
@endsection

