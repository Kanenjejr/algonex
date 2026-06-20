
@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Staff Next of Kins Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('hr') }}">Human Resource</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Staff Next of Kins</strong>
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
  <h3 class="mb-2 page-title">Staff Next of Kins</h3>
  @can('Register-NextOfKin')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#nokCreateModal">Add Next of Kin</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox ">
    <div class="ibox-title bg-info"><h5>Next of Kin Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example">
          <thead>
            <tr><th>#</th><th>Staff</th><th>Name</th><th>Relationship</th><th>Phone</th><th>Status</th><th>Action</th></tr>
          </thead>
          <tbody>
            @foreach($nextOfKins as $k => $n)
            <tr>
              <td>{{ $k + 1 }}</td>
              <td>{{ optional($n->user)->name ?? '-' }}</td>
              <td>{{ $n->name }}</td>
              <td>{{ $n->relationship }}</td>
              <td>{{ $n->phone }}</td>
              <td>{{ $n->status }}</td>
              <td>
                @can('Edit-NextOfKin')
                  <button class="btn btn-sm btn-warning btn-edit-nok"
                    data-id="{{ encrypt($n->id) }}"
                    data-user_id="{{ $n->user_id }}"
                    data-name="{{ $n->name }}"
                    data-relationship="{{ $n->relationship }}"
                    data-phone="{{ $n->phone }}"
                    data-address="{{ $n->address }}"
                    data-work_point_id="{{ $n->work_point_id }}"
                    data-status="{{ $n->status ?? 'Active' }}"
                  >Edit</button>
                @endcan
                @can('Delete-NextOfKin')
                  <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-nok" data-id="{{ encrypt($n->id) }}">Remove</a>
                @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div> <!-- /.table-responsive -->
    </div>
  </div></div></div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="nokCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="nokCreateForm" action="{{ route('hr.nextofkins.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Next of Kin</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
            <label>Staff <span style="color:red">*</span></label>
            <select name="user_id" class="form-control select2_demo_3" required>
              <option value="">-- Select staff --</option>
              @foreach($staffUsers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group"><label>Name <span style="color:red">*</span></label><input type="text" name="name" class="form-control" required></div>
          <div class="form-group"><label>Relationship</label><input type="text" name="relationship" class="form-control"></div>
          <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
          <div class="form-group"><label>Address</label><textarea name="address" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('nokCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="nokEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="nokEditForm" method="POST">
      @csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Next of Kin</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_nok_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select id="edit_nok_work_point_id" name="work_point_id" class="form-control select2_demo_3">
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
          @endif

          <div class="form-group">
            <label>Staff</label>
            <select id="edit_nok_user_id" name="user_id" class="form-control select2_demo_3">
              <option value="">-- Select staff --</option>
              @foreach($staffUsers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group"><label>Name</label><input id="edit_nok_name" name="name" class="form-control"></div>
          <div class="form-group"><label>Relationship</label><input id="edit_nok_relationship" name="relationship" class="form-control"></div>
          <div class="form-group"><label>Phone</label><input id="edit_nok_phone" name="phone" class="form-control"></div>
          <div class="form-group"><label>Address</label><textarea id="edit_nok_address" name="address" class="form-control"></textarea></div>
          <div class="form-group"><label>Status</label><select id="edit_nok_status" name="status" class="form-control select2_demo_3"><option>Active</option><option>Inactive</option><option>Deleted</option></select></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('nokEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // Temporary storage for selects to apply after modal shown
  var tempNokEditData = null;

  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) {
      try { $el.select2('destroy'); } catch (e) { /* ignore */ }
    }
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({ width: '100%', theme: 'bootstrap4', dropdownParent: $parent });
  }

  // initialize selects (handles selects on page and in modals)
  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#nokCreateModal').length) { initSelect2WithParent($this, '#nokCreateModal'); return; }
    if ($this.closest('#nokEditModal').length) { initSelect2WithParent($this, '#nokEditModal'); return; }
    initSelect2WithParent($this, null);
  });

  // reset create modal on show
  $(document).on('shown.bs.modal', '#nokCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#nokCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  // when edit modal shown apply selects
  $(document).on('shown.bs.modal', '#nokEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#nokEditModal');
    });
    if (tempNokEditData) {
      if (typeof tempNokEditData.work_point_id !== 'undefined') $('#edit_nok_work_point_id').val(tempNokEditData.work_point_id).trigger('change');
      if (typeof tempNokEditData.user_id !== 'undefined') $('#edit_nok_user_id').val(tempNokEditData.user_id).trigger('change');
      tempNokEditData = null;
    }
  });

  // Edit button click
  document.querySelectorAll('.btn-edit-nok').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      document.getElementById('edit_nok_id').value = encId || '';
      document.getElementById('edit_nok_name').value = this.dataset.name || '';
      document.getElementById('edit_nok_relationship').value = this.dataset.relationship || '';
      document.getElementById('edit_nok_phone').value = this.dataset.phone || '';
      document.getElementById('edit_nok_address').value = this.dataset.address || '';
      document.getElementById('edit_nok_status').value = this.dataset.status || 'Active';

      tempNokEditData = {
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null,
        user_id: (typeof this.dataset.user_id !== 'undefined') ? this.dataset.user_id : null
      };

      var form = document.getElementById('nokEditForm');
      form.action = "{{ route('hr.nextofkins.update', ':id') }}".replace(':id', encId);
      $('#nokEditModal').modal('show');
    });
  });

  // Delete
  document.querySelectorAll('.btn-delete-nok').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the record as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('hr.nextofkins.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  // init datatable

});
</script>
@endsection
