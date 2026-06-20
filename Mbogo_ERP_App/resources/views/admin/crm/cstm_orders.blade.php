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
                <strong>Customer / Supplier Orders</strong>
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
  <h3 class="mb-2 page-title">Customer / Supplier Orders</h3>
  @can('Register-Customer-Orders')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#orderCreateModal">Add Order</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox">
    <div class="ibox-title bg-info"><h5>Orders Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered dataTables-example">
          <thead><tr><th>#</th><th>Order No</th><th>Date</th><th>Customer</th><th>Type</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($orders as $k => $o)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $o->order_no }}</td>
              <td>{{ $o->order_date }}</td>
              <td>{{ optional($o->customer)->customer_name ?? '-' }}</td>
              <td>{{ ucfirst($o->type) }}</td>
              <td>{{ number_format($o->total_amount,2) }}</td>
              <td>{{ $o->status }}</td>
              <td>
                @can('Edit-Customer-Orders')
                  <button class="btn btn-sm btn-warning btn-edit-order"
                    data-id="{{ encrypt($o->id) }}"
                    data-order_no="{{ $o->order_no }}"
                    data-order_date="{{ $o->order_date }}"
                    data-cstm_id="{{ $o->cstm_id }}"
                    data-type="{{ $o->type }}"
                    data-total_amount="{{ $o->total_amount }}"
                    data-status="{{ $o->status }}"
                    data-work_point_id="{{ $o->work_point_id }}"
                  >Edit</button>
                @endcan
                @can('Delete-Customer-Orders')
                  <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-order" data-id="{{ encrypt($o->id) }}">Remove</a>
                @endcan
                <a href="{{ route('crm.items.index', encrypt($o->id)) }}" class="btn btn-sm btn-info">Items</a>
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
<div class="modal fade" id="orderCreateModal" tabindex="-1"><div class="modal-dialog"><form id="orderCreateForm" action="{{ route('crm.orders.store') }}" method="POST">@csrf
  <div class="modal-content modal-xl">
    <div class="modal-header"><h5 class="modal-title">Add Order</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point</label>
          <select name="work_point_id" class="form-control select2_order" required>
            <option value="">-- Select --</option>
            @foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach
          </select>
        </div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-6"><label>Customer / Supplier</label>
          <select name="cstm_id" class="form-control select2_order" required>
            <option value="">-- Select --</option>
            @foreach($cstmSplies as $c)<option value="{{ $c->id }}">{{ $c->customer_name }} ({{ $c->category }})</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-6"><label>Order Date</label><input type="date" name="order_date" class="form-control"></div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4"><label>Type</label>
          <select name="type" class="form-control select2_order" required><option value="sale">Sale</option><option value="purchase">Purchase</option></select>
        </div>
        <div class="form-group col-md-4"><label>Currency</label><input type="text" name="currency" class="form-control" value="USD"></div>
        <div class="form-group col-md-4"><label>Total Amount</label><input type="number" step="0.01" name="total_amount" class="form-control" value="0"></div>
      </div>

      <div class="form-group"><label>Status</label><select name="status" class="form-control select2_order"><option value="Pending">Pending</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>

    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('orderCreateForm')" type="submit" class="btn btn-primary">Submit</button></div>
  </div>
</form></div></div>
{{-- Edit Modal --}}
<div class="modal fade" id="orderEditModal" tabindex="-1"><div class="modal-dialog"><form id="orderEditForm" method="POST">@csrf @method('PUT')
  <div class="modal-content modal-xl">
    <div class="modal-header"><h5 class="modal-title">Edit Order</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <input id="edit_order_id" type="hidden" name="edit_id">
      @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
        <div class="form-group"><label>Work Point</label>
          <select id="edit_order_work_point_id" name="work_point_id" class="form-control select2_order">
            <option value="">-- Select --</option>
            @foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach
          </select>
        </div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-6"><label>Customer / Supplier</label>
          <select id="edit_cstm_id" name="cstm_id" class="form-control select2_order"><option value="">-- Select --</option>@foreach($cstmSplies as $c)<option value="{{ $c->id }}">{{ $c->customer_name }}</option>@endforeach</select>
        </div>
        <div class="form-group col-md-6"><label>Order Date</label><input id="edit_order_date" type="date" name="order_date" class="form-control"></div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4"><label>Type</label><select id="edit_type" name="type" class="form-control select2_order"><option value="sale">Sale</option><option value="purchase">Purchase</option></select></div>
        <div class="form-group col-md-4"><label>Currency</label><input id="edit_currency" type="text" name="currency" class="form-control"></div>
        <div class="form-group col-md-4"><label>Total Amount</label><input id="edit_total_amount" type="number" step="0.01" name="total_amount" class="form-control"></div>
      </div>

      <div class="form-group"><label>Status</label><select id="edit_order_status" name="status" class="form-control select2_order"><option value="Pending">Pending</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>

    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('orderEditForm')" type="submit" class="btn btn-primary">Update</button></div>
  </div>
</form></div></div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var tempOrderEdit = null;
  function initSelect2WithParent($el, parent) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) try{ $el.select2('destroy'); }catch(e){}
    var $parent = parent && $(parent).length ? $(parent) : $(document.body);
    $el.select2({ width:'100%', theme:'bootstrap4', dropdownParent: $parent });
  }

  $('.select2_order').each(function(){
    var $this = $(this);
    if ($this.closest('#orderCreateModal').length) { initSelect2WithParent($this,'#orderCreateModal'); return; }
    if ($this.closest('#orderEditModal').length) { initSelect2WithParent($this,'#orderEditModal'); return; }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#orderCreateModal', function(){
    var $m = $(this);
    $m.find('.select2_order').each(function(){ initSelect2WithParent($(this),'#orderCreateModal'); $(this).val(null).trigger('change'); });
  });

  $(document).on('shown.bs.modal', '#orderEditModal', function(){
    var $m = $(this);
    $m.find('.select2_order').each(function(){ initSelect2WithParent($(this),'#orderEditModal'); });
    if (tempOrderEdit) {
      $('#edit_order_work_point_id').val(tempOrderEdit.work_point_id || '').trigger('change');
      $('#edit_cstm_id').val(tempOrderEdit.cstm_id || '').trigger('change');
      tempOrderEdit = null;
    }
  });

  document.querySelectorAll('.btn-edit-order').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      document.getElementById('edit_order_id').value = enc;
      document.getElementById('edit_order_date').value = this.dataset.order_date || '';
      document.getElementById('edit_total_amount').value = this.dataset.total_amount || 0;
      document.getElementById('edit_currency').value = this.dataset.currency || 'USD';
      document.getElementById('edit_type').value = this.dataset.type || 'sale';
      document.getElementById('edit_order_status').value = this.dataset.status || 'Pending';

      tempOrderEdit = { work_point_id: this.dataset.work_point_id || null, cstm_id: this.dataset.cstm_id || null };

      var form = document.getElementById('orderEditForm');
      form.action = "{{ route('crm.orders.update', ':id') }}".replace(':id', enc);

      $('#orderEditModal').modal('show');
    });
  });

  document.querySelectorAll('.btn-delete-order').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      Swal.fire({ title:'Are you sure?', text:'This will cancel the order.', icon:'warning', showCancelButton:true, confirmButtonText:'Yes'}).then(res=>{ if(res.isConfirmed) window.location.href = "{{ route('crm.orders.remove', ':id') }}".replace(':id', enc); });
    });
  });
});
</script>
@endsection
