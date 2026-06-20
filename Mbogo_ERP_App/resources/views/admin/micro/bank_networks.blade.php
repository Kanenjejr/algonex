@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Banks & Networks Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('microfinancing') }}">Microfinancing</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Banks & Networks</strong>
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
  <h3 class="mb-2 page-title">Banks & Networks</h3>
  @can('Register-BankNetwork')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#bnCreateModal">Add Bank/Network</button>
  @endcan
</div>

<div class="wrapper wrapper-content">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-info"><h5>Banks & Networks List</h5></div>
        <div class="ibox-content">
          <table class="table table-striped table-bordered dataTables-example">
            <thead><tr><th>#</th><th>Type</th><th>Name</th><th>Work Point</th><th>Code</th><th>Account/Wallet</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              @foreach($items as $k => $b)
              <tr>
                <td>{{ $k+1 }}</td>
                <td>{{ $b->type }}</td>
                <td>{{ $b->name }}</td>
                <td>{{ optional($b->workpoint)->work_name ?? '-' }}</td>
                <td>{{ $b->code ?? '-' }}</td>
                <td>{{ $b->account_or_wallet ?? '-' }}</td>
                <td>{{ $b->status }}</td>
                <td>
                  @can('Edit-BankNetwork')
                    <button class="btn btn-sm btn-warning btn-edit-bn"
                      data-id="{{ encrypt($b->id) }}"
                      data-type="{{ $b->type }}"
                      data-name="{{ $b->name }}"
                      data-code="{{ $b->code }}"
                      data-account_or_wallet="{{ $b->account_or_wallet }}"
                      data-branch="{{ $b->branch }}"
                      data-work_point_id="{{ $b->work_point_id }}"
                      data-status="{{ $b->status ?? 'Active' }}"
                    >Edit</button>
                  @endcan
                  @can('Delete-BankNetwork')
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-bn" data-id="{{ encrypt($b->id) }}">Remove</a>
                  @endcan
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="bnCreateModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="bnCreateForm" action="{{ route('micro.bank_networks.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Bank/Network</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select name="work_point_id" class="form-control select2_modal">
                <option value="">-- Select --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
          @else
            <input type="hidden" name="work_point_id" value="{{ optional(auth()->user())->work_point_id }}">
          @endif

          <div class="form-row">
            <div class="form-group col"><label>Type</label>
              <select name="type" class="form-control select2_modal"><option value="Bank">Bank</option><option value="Network">Network</option></select>
            </div>
            <div class="form-group col"><label>Name</label><input type="text" name="name" class="form-control" required></div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Code</label><input type="text" name="code" class="form-control"></div>
            <div class="form-group col"><label>Account / Wallet</label><input type="text" name="account_or_wallet" class="form-control"></div>
          </div>

          <div class="form-group"><label>Branch</label><input type="text" name="branch" class="form-control"></div>
          <div class="form-group"><label>Status</label><select name="status" class="form-control select2_modal"><option value="Active" selected>Active</option><option value="Deleted">Deleted</option></select></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('bnCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="bnEditModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="bnEditForm" method="POST">@csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_bn_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select id="edit_bn_work_point_id" name="work_point_id" class="form-control select2_modal">
                <option value="">-- Select --</option>
                @foreach($workPoints as $wp)
                  <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                @endforeach
              </select>
            </div>
          @endif

          <div class="form-row">
            <div class="form-group col"><label>Type</label>
              <select id="edit_bn_type" name="type" class="form-control select2_modal"><option value="Bank">Bank</option><option value="Network">Network</option></select>
            </div>
            <div class="form-group col"><label>Name</label><input id="edit_bn_name" type="text" name="name" class="form-control" required></div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Code</label><input id="edit_bn_code" type="text" name="code" class="form-control"></div>
            <div class="form-group col"><label>Account / Wallet</label><input id="edit_bn_account" type="text" name="account_or_wallet" class="form-control"></div>
          </div>

          <div class="form-group"><label>Branch</label><input id="edit_bn_branch" type="text" name="branch" class="form-control"></div>
          <div class="form-group"><label>Status</label><select id="edit_bn_status" name="status" class="form-control select2_modal"><option value="Active">Active</option><option value="Deleted">Deleted</option></select></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('bnEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var tempBnEdit = null;

  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) {
      try { $el.select2('destroy'); } catch (e){ }
    }
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({ width:'100%', theme:'bootstrap4', dropdownParent: $parent });
  }

  $('.select2_modal').each(function(){
    var $this = $(this);
    if ($this.closest('#bnCreateModal').length) { initSelect2WithParent($this, '#bnCreateModal'); return; }
    if ($this.closest('#bnEditModal').length) { initSelect2WithParent($this, '#bnEditModal'); return; }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#bnCreateModal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_modal').each(function(){ initSelect2WithParent($(this),'#bnCreateModal'); $(this).val(null).trigger('change'); });
  });

  $(document).on('shown.bs.modal', '#bnEditModal', function(){
    var $m = $(this);
    $m.find('.select2_modal').each(function(){ initSelect2WithParent($(this),'#bnEditModal'); });
    if (tempBnEdit) {
      $('#edit_bn_work_point_id').val(tempBnEdit.work_point_id || '').trigger('change');
      tempBnEdit = null;
    }
  });

  document.querySelectorAll('.btn-edit-bn').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      document.getElementById('edit_bn_id').value = enc;
      document.getElementById('edit_bn_type').value = this.dataset.type || 'Bank';
      document.getElementById('edit_bn_name').value = this.dataset.name || '';
      document.getElementById('edit_bn_code').value = this.dataset.code || '';
      document.getElementById('edit_bn_account').value = this.dataset.account_or_wallet || '';
      document.getElementById('edit_bn_branch').value = this.dataset.branch || '';
      document.getElementById('edit_bn_status').value = this.dataset.status || 'Active';

      tempBnEdit = { work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null };

      var form = document.getElementById('bnEditForm');
      form.action = "{{ route('micro.bank_networks.update', ':id') }}".replace(':id', enc);
      $('#bnEditModal').modal('show');
    });
  });

  document.querySelectorAll('.btn-delete-bn').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the record as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then(function(res){
        if (res.isConfirmed) window.location.href = "{{ route('micro.bank_networks.remove', ':id') }}".replace(':id', enc);
      });
    });
  });
});
</script>
@endsection
