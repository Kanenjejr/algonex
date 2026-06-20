@extends('layouts.salesMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Sales & Marketing Module</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Customer Contacts</strong>
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
  <h3 class="mb-2 page-title">Customer Contact Details</h3>
  @can('Register-Customer-Contacts')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#contactCreateModal">Add Contact</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-primary"><h5>Customer Contact Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Contact</th>
                  <th>Job title</th>
                  <th>Customer</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Work Point</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($contacts as $k => $c)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ $c->first_name }} {{ $c->last_name ?? '' }}</td>
                  <td>{{ $c->job_title ?? '-' }}</td>
                  <td>{{ optional($c->customer)->customer_name ?? '-' }}</td>
                  <td>{{ $c->phone ?? '-' }}</td>
                  <td>{{ $c->email ?? '-' }}</td>
                  <td>{{ optional($c->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $c->status }}</td>
                  <td>
                    @can('Edit-Customer-Contacts')
                      <button class="btn btn-sm btn-warning btn-edit-contact"
                        data-id="{{ encrypt($c->id) }}"
                        data-first_name="{{ $c->first_name }}"
                        data-last_name="{{ $c->last_name }}"
                        data-job_title="{{ $c->job_title }}"
                        data-phone="{{ $c->phone }}"
                        data-email="{{ $c->email }}"
                        data-address="{{ $c->address }}"
                        data-notes="{{ $c->notes }}"
                        data-cstm_id="{{ $c->cstm_id }}"
                        data-work_point_id="{{ $c->work_point_id }}"
                        data-status="{{ $c->status ?? 'Active' }}"
                      >Edit</button>
                    @endcan
                    @can('Delete-Customer-Contacts')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-contact" data-id="{{ encrypt($c->id) }}">Remove</a>
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
<div class="modal fade" id="contactCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form id="contactCreateForm" action="{{ route('sales.contacts.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Customer Contact</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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


          <div class="form-row">
            <div class="form-group col-md-4">
            <label>Customer (optional)</label>
            <select name="cstm_id" class="form-control select2_demo_3">
              <option value="">-- Select customer --</option>
              @foreach($customers as $cust)
                <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group col-md-4">
            <label>First Name <span style="color:red">*</span></label>
            <input type="text" name="first_name" class="form-control" required>
          </div>

          <div class="form-group col-md-4">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control">
          </div>
        </div>

          <div class="form-row">
            <div class="form-group col-md-4">
                <label>Job Title</label>
                <input type="text" name="job_title" class="form-control">
            </div>

            <div class="form-group col-md-4">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="form-group col-md-4">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
                <label>Address</label>
                <textarea name="address" class="form-control"></textarea>
            </div>

            <div class="form-group col-md-4">
                <label>Notes</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>

            <div class="form-group col-md-4">
                <label>Status</label>
                <select name="status" class="form-control select2_demo_3"><option value="Active" selected>Active</option><option value="Inactive">Inactive</option></select>
            </div>
        </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('contactCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="contactEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="contactEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Customer Contact</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_contact_id" name="edit_id">

          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_contact_work_point_id" name="work_point_id" class="form-control select2_demo_3">
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

          <div class="form-row">
            <div class="form-group col-md-4">
            <label>Customer (optional)</label>
            <select id="edit_contact_cstm_id" name="cstm_id" class="form-control select2_demo_3">
              <option value="">-- Select customer --</option>
              @foreach($customers as $cust)
                <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group col-md-4">
            <label>First Name</label>
            <input id="edit_contact_first_name" type="text" name="first_name" class="form-control" required>
          </div>

          <div class="form-group col-md-4">
            <label>Last Name</label>
            <input id="edit_contact_last_name" type="text" name="last_name" class="form-control">
          </div>
        </div>
          <div class="form-row">
            <div class="form-group col-md-4">
            <label>Job Title</label>
            <input id="edit_contact_job_title" type="text" name="job_title" class="form-control">
          </div>

          <div class="form-group col-md-4">
            <label>Phone</label>
            <input id="edit_contact_phone" type="text" name="phone" class="form-control">
          </div>

          <div class="form-group col-md-4">
            <label>Email</label>
            <input id="edit_contact_email" type="email" name="email" class="form-control">
          </div>
        </div>
          <div class="form-row">
            <div class="form-group col-md-4">
            <label>Address</label>
            <textarea id="edit_contact_address" name="address" class="form-control"></textarea>
          </div>

          <div class="form-group col-md-4">
            <label>Notes</label>
            <textarea id="edit_contact_notes" name="notes" class="form-control"></textarea>
          </div>

          <div class="form-group col-md-4">
            <label>Status</label>
            <select id="edit_contact_status" name="status" class="form-control select2_demo_3">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>
        </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('contactEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  var tempContactEditData = null;

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

  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#contactCreateModal').length) {
      initSelect2WithParent($this, '#contactCreateModal');
      return;
    }
    if ($this.closest('#contactEditModal').length) {
      initSelect2WithParent($this, '#contactEditModal');
      return;
    }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#contactCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#contactCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  $(document).on('shown.bs.modal', '#contactEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#contactEditModal');
    });

    if (tempContactEditData) {
      if (typeof tempContactEditData.work_point_id !== 'undefined' && tempContactEditData.work_point_id !== null) {
        $('#edit_contact_work_point_id').val(tempContactEditData.work_point_id).trigger('change');
      } else {
        $('#edit_contact_work_point_id').val('').trigger('change');
      }
      $('#edit_contact_cstm_id').val(tempContactEditData.cstm_id || '').trigger('change');

      tempContactEditData = null;
    }
  });

  document.querySelectorAll('.btn-edit-contact').forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault && e.preventDefault();
      var encId = this.dataset.id;
      document.getElementById('edit_contact_id').value = encId || '';
      document.getElementById('edit_contact_first_name').value = this.dataset.first_name || '';
      document.getElementById('edit_contact_last_name').value = this.dataset.last_name || '';
      document.getElementById('edit_contact_job_title').value = this.dataset.job_title || '';
      document.getElementById('edit_contact_phone').value = this.dataset.phone || '';
      document.getElementById('edit_contact_email').value = this.dataset.email || '';
      document.getElementById('edit_contact_address').value = this.dataset.address || '';
      document.getElementById('edit_contact_notes').value = this.dataset.notes || '';
      document.getElementById('edit_contact_status').value = this.dataset.status || 'Active';

      tempContactEditData = {
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null,
        cstm_id: (typeof this.dataset.cstm_id !== 'undefined') ? this.dataset.cstm_id : null
      };

      var form = document.getElementById('contactEditForm');
      form.action = "{{ route('sales.contacts.update', ':id') }}".replace(':id', encId);

      $('#contactEditModal').modal('show');
    });
  });

  document.querySelectorAll('.btn-delete-contact').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the contact as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('sales.contacts.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  $('#contactCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#contactCreateModal');
    });
  });

});
</script>
@endsection
