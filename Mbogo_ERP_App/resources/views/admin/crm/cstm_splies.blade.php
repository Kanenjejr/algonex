@extends('layouts.salesMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Customers, Supplies & Interactions Dashboard</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                 <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Customers & Suppliers</strong>
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
  <h3 class="mb-2 page-title">Customers & Suppliers</h3>
  @can('Register-CustomerSupplier')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#cstmCreateModal">Add Customer/Supplier</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-info"><h5>Customers & Suppliers Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th><th>Name</th><th>Phone</th><th>Location</th><th>Category</th><th>Work Point</th><th>Status</th><th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $k => $c)
                <tr>
                  <td>{{ $k+1 }}</td>
                  <td>{{ $c->customer_name }}</td>
                  <td>{{ $c->phone_no ?? '-' }}</td>
                  <td>{{ $c->location ?? '-' }}</td>
                  <td>{{ $c->category }}</td>
                  <td>{{ optional($c->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $c->status }}</td>
                  <td>
                    @can('Edit-CustomerSupplier')
                      <button class="btn btn-sm btn-warning btn-edit-cstm"
                        data-id="{{ encrypt($c->id) }}"
                        data-customer_name="{{ $c->customer_name }}"
                        data-phone_no="{{ $c->phone_no }}"
                        data-location="{{ $c->location }}"
                        data-address_line="{{ $c->address_line }}"
                        data-city="{{ $c->city }}"
                        data-state="{{ $c->state }}"
                        data-postal_code="{{ $c->postal_code }}"
                        data-country="{{ $c->country }}"
                        data-category="{{ $c->category }}"
                        data-work_point_id="{{ $c->work_point_id }}"
                        data-status="{{ $c->status ?? 'Active' }}"
                      >Edit</button>
                    @endcan

                    @can('Delete-CustomerSupplier')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-cstm" data-id="{{ encrypt($c->id) }}">Remove</a>
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
<div class="modal fade" id="cstmCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="cstmCreateForm" action="{{ route('crm.cstm.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Customer / Supplier</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select name="work_point_id" class="form-control select2_modal" required>
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
          @else
            <input type="hidden" name="work_point_id" value="{{ optional(auth()->user())->work_point_id }}">
          @endif

          <div class="form-group">
            <label>Name <span style="color:red">*</span></label>
            <input type="text" name="customer_name" class="form-control" required>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Phone</label><input type="text" name="phone_no" class="form-control"></div>
            <div class="form-group col"><label>Location</label><input type="text" name="location" class="form-control"></div>
          </div>

          <div class="form-group"><label>Address Line</label><input type="text" name="address_line" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col"><label>City</label><input type="text" name="city" class="form-control"></div>
            <div class="form-group col"><label>State</label><input type="text" name="state" class="form-control"></div>
            <div class="form-group col"><label>Postal Code</label><input type="text" name="postal_code" class="form-control"></div>
          </div>
          <div class="form-group"><label>Country</label><input type="text" name="country" class="form-control"></div>

          <div class="form-group"><label>Category</label>
            <select name="category" class="form-control select2_modal">
              <option value="Customer">Customer</option>
              <option value="Supplier">Supplier</option>
            </select>
          </div>

          <div class="form-group"><label>Status</label>
            <select name="status" class="form-control select2_modal"><option value="Active" selected>Active</option><option value="Deleted">Deleted</option></select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('cstmCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="cstmEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="cstmEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Customer / Supplier</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_cstm_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select id="edit_cstm_work_point_id" name="work_point_id" class="form-control select2_modal">
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
          @endif

          <div class="form-group"><label>Name</label><input id="edit_customer_name" type="text" name="customer_name" class="form-control" required></div>
          <div class="form-row">
            <div class="form-group col"><label>Phone</label><input id="edit_phone_no" type="text" name="phone_no" class="form-control"></div>
            <div class="form-group col"><label>Location</label><input id="edit_location" type="text" name="location" class="form-control"></div>
          </div>

          <div class="form-group"><label>Address Line</label><input id="edit_address_line" type="text" name="address_line" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col"><label>City</label><input id="edit_city" type="text" name="city" class="form-control"></div>
            <div class="form-group col"><label>State</label><input id="edit_state" type="text" name="state" class="form-control"></div>
            <div class="form-group col"><label>Postal Code</label><input id="edit_postal_code" type="text" name="postal_code" class="form-control"></div>
          </div>
          <div class="form-group"><label>Country</label><input id="edit_country" type="text" name="country" class="form-control"></div>

          <div class="form-group"><label>Category</label>
            <select id="edit_category" name="category" class="form-control select2_modal">
              <option value="Customer">Customer</option>
              <option value="Supplier">Supplier</option>
            </select>
          </div>

          <div class="form-group"><label>Status</label>
            <select id="edit_cstm_status" name="status" class="form-control select2_modal">
              <option value="Active">Active</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('cstmEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

  var tempCstmEdit = null;

  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) {
      try { $el.select2('destroy'); } catch (e){ /* ignore */ }
    }
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({ width:'100%', theme:'bootstrap4', dropdownParent: $parent });
  }

  // init selects on page / modals
  $('.select2_modal').each(function(){
    var $this = $(this);
    if ($this.closest('#cstmCreateModal').length) { initSelect2WithParent($this, '#cstmCreateModal'); return; }
    if ($this.closest('#cstmEditModal').length) { initSelect2WithParent($this, '#cstmEditModal'); return; }
    initSelect2WithParent($this, null);
  });

  // create modal reset + init select2 on shown
  $(document).on('shown.bs.modal', '#cstmCreateModal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_modal').each(function(){ initSelect2WithParent($(this),'#cstmCreateModal'); $(this).val(null).trigger('change'); });
  });

  // when edit modal shown apply stored selects
  $(document).on('shown.bs.modal', '#cstmEditModal', function(){
    var $m = $(this);
    $m.find('.select2_modal').each(function(){ initSelect2WithParent($(this),'#cstmEditModal'); });
    if (tempCstmEdit) {
      $('#edit_cstm_work_point_id').val(tempCstmEdit.work_point_id || '').trigger('change');
      tempCstmEdit = null;
    }
  });

  // open edit
  document.querySelectorAll('.btn-edit-cstm').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      document.getElementById('edit_cstm_id').value = enc;
      document.getElementById('edit_customer_name').value = this.dataset.customer_name || '';
      document.getElementById('edit_phone_no').value = this.dataset.phone_no || '';
      document.getElementById('edit_location').value = this.dataset.location || '';
      document.getElementById('edit_address_line').value = this.dataset.address_line || '';
      document.getElementById('edit_city').value = this.dataset.city || '';
      document.getElementById('edit_state').value = this.dataset.state || '';
      document.getElementById('edit_postal_code').value = this.dataset.postal_code || '';
      document.getElementById('edit_country').value = this.dataset.country || '';
      document.getElementById('edit_category').value = this.dataset.category || 'Customer';
      document.getElementById('edit_cstm_status').value = this.dataset.status || 'Active';

      tempCstmEdit = { work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null };

      var form = document.getElementById('cstmEditForm');
      form.action = "{{ route('crm.cstm.update', ':id') }}".replace(':id', enc);

      $('#cstmEditModal').modal('show');
    });
  });

  // delete
  document.querySelectorAll('.btn-delete-cstm').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the record as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then(function(res){
        if (res.isConfirmed) window.location.href = "{{ route('crm.cstm.remove', ':id') }}".replace(':id', enc);
      });
    });
  });

});
</script>
@endsection
