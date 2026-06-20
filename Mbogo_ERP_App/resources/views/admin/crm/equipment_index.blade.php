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
                <strong>Hired Equipment</strong>
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
  <h3 class="mb-2 page-title">Hired Equipment</h3>
  @can('Register-Hired-Equipment')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#equipCreateModal">Add Equipment</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-info"><h5>Hired Equipment Details Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th><th>Eqpmnt No</th><th>Model/Type/Category</th><th>Operator</th><th>Per Day</th><th>Customer</th><th>Work Point</th><th>Status</th><th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $k => $e)
                <tr>
                  <td>{{ $k+1 }}</td>
                  <td>{{ $e->EqpmntNo }}</td>
                  <td>{{ $e->Model ?? '-' }}</td>
                  <td>{{ $e->OperatorName ?? '-' }}</td>
                  <td>{{ $e->PaymentPerDay }}</td>
                  <td>{{ optional($e->cstm)->customer_name ?? '-' }}</td>
                  <td>{{ optional($e->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $e->Status }}</td>
                  <td>
                    @can('Edit-Hired-Equipment')
                      <button class="btn btn-sm btn-warning btn-edit-equip"
                        data-id="{{ encrypt($e->id) }}"
                        data-model="{{ $e->Model }}"
                        data-eqpmntno="{{ $e->EqpmntNo }}"
                        data-operatorname="{{ $e->OperatorName }}"
                        data-paymentperhour="{{ $e->PaymentPerDay }}"
                        data-cstm_id="{{ $e->cstm_id }}"
                        data-work_point_id="{{ $e->work_point_id }}"
                        data-status="{{ $e->Status ?? 'Active' }}"
                      >Edit</button>
                    @endcan

                    @can('Delete-Hired-Equipment')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-equip" data-id="{{ encrypt($e->id) }}">Remove</a>
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
<div class="modal fade" id="equipCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="equipCreateForm" action="{{ route('hired.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Equipment</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
            <label>Equipment No <span style="color:red">*</span></label>
            <input type="text" name="EqpmntNo" class="form-control" required>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Model/Type/Category</label><input type="text" name="Model" class="form-control"></div>
            <div class="form-group col"><label>Operator</label><input type="text" name="OperatorName" class="form-control"></div>
            <div class="form-group col"><label>Payment/Day</label><input type="number" name="PaymentPerDay" class="form-control" min="0"></div>
          </div>

          <div class="form-group">
            <label>Customer (optional)</label>
            <select name="cstm_id" class="form-control select2_modal">
              <option value="">-- Select customer --</option>
              @foreach($customers as $c) <option value="{{ $c->id }}">{{ $c->customer_name }}</option> @endforeach
            </select>
          </div>

          <div class="form-group"><label>Status</label>
            <select name="Status" class="form-control select2_modal"><option value="Active" selected>Active</option><option value="Deleted">Deleted</option></select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('equipCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="equipEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="equipEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Equipment</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_equip_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select id="edit_equip_work_point_id" name="work_point_id" class="form-control select2_modal">
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp) <option value="{{ $wp->id }}">{{ $wp->work_name }}</option> @endforeach
              </select>
            </div>
          @endif

          <div class="form-group"><label>Equipment No</label><input id="edit_eqpmntno" type="text" name="EqpmntNo" class="form-control" required></div>
          <div class="form-row">
            <div class="form-group col"><label>Model/Type/Category</label><input id="edit_model" type="text" name="Model" class="form-control"></div>
            <div class="form-group col"><label>Operator</label><input id="edit_operatorname" type="text" name="OperatorName" class="form-control"></div>
            <div class="form-group col"><label>Payment/Day</label><input id="edit_paymentperhour" type="number" name="PaymentPerDay" class="form-control" min="0"></div>
          </div>

          <div class="form-group">
            <label>Customer (optional)</label>
            <select id="edit_cstm_id" name="cstm_id" class="form-control select2_modal">
              <option value="">-- Select customer --</option>
              @foreach($customers as $c) <option value="{{ $c->id }}">{{ $c->customer_name }}</option> @endforeach
            </select>
          </div>

          <div class="form-group"><label>Status</label>
            <select id="edit_equip_status" name="Status" class="form-control select2_modal"><option value="Active">Active</option><option value="Deleted">Deleted</option></select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('equipEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  var tempEquipEdit = null;

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
    if ($this.closest('#equipCreateModal').length) { initSelect2WithParent($this, '#equipCreateModal'); return; }
    if ($this.closest('#equipEditModal').length) { initSelect2WithParent($this, '#equipEditModal'); return; }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#equipCreateModal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_modal').each(function(){ initSelect2WithParent($(this),'#equipCreateModal'); $(this).val(null).trigger('change'); });
  });

  $(document).on('shown.bs.modal', '#equipEditModal', function(){
    var $m = $(this);
    $m.find('.select2_modal').each(function(){ initSelect2WithParent($(this),'#equipEditModal'); });
    if (tempEquipEdit) {
      $('#edit_equip_work_point_id').val(tempEquipEdit.work_point_id || '').trigger('change');
      $('#edit_cstm_id').val(tempEquipEdit.cstm_id || '').trigger('change');
      tempEquipEdit = null;
    }
  });

  // open edit
  document.querySelectorAll('.btn-edit-equip').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      document.getElementById('edit_equip_id').value = enc;
      document.getElementById('edit_model').value = this.dataset.model || '';
      document.getElementById('edit_eqpmntno').value = this.dataset.eqpmntno || '';
      document.getElementById('edit_operatorname').value = this.dataset.operatorname || '';
      document.getElementById('edit_paymentperhour').value = this.dataset.paymentperhour || 0;
      document.getElementById('edit_equip_status').value = this.dataset.status || 'Active';

      tempEquipEdit = {
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null,
        cstm_id: (typeof this.dataset.cstm_id !== 'undefined') ? this.dataset.cstm_id : null
      };

      var form = document.getElementById('equipEditForm');
      form.action = "{{ route('hired.update', ':id') }}".replace(':id', enc);

      $('#equipEditModal').modal('show');
    });
  });

  // delete
  document.querySelectorAll('.btn-delete-equip').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the equipment as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then(function(res){
        if (res.isConfirmed) window.location.href = "{{ route('hired.remove', ':id') }}".replace(':id', enc);
      });
    });
  });

});
</script>
@endsection
