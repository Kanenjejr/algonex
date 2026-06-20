@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Microfinancing Transactions Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('microfinancing') }}">Microfinancing</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Microfinancing Transactions</strong>
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
  <h3 class="mb-2 page-title">Microfinancing Transactions</h3>
  @can('Register-Microfinancing-Transaction')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#txCreateModal">Record Transaction</button>
  @endcan
</div>

<div class="wrapper wrapper-content">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-info"><h5>Microfinance Transactions</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered dataTables-example">
              <thead><tr><th>#</th><th>Date</th><th>WP</th><th>Type</th><th>BN</th><th>Currency</th><th>Amount</th><th>Tx Rate</th><th>Amount In TZS</th><th>Commission In TZS</th><th>Status</th><th>Action</th></tr></thead>
              <tbody>
                @foreach($items as $k => $t)
                <tr>
                  <td>{{ $k+1 }}</td>
                  <td>{{ $t->created_at }}</td>
                  <td>{{ optional($t->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $t->tx_group }}</td>
                  <td>{{ optional($t->bankNetwork)->type ? optional($t->bankNetwork)->type . ' - ' . optional($t->bankNetwork)->name : '-' }}</td>
                  <td>{{ $t->currency }}</td>
                  <td>{{ number_format($t->amount/($t->fx_rate ?? 1),2) }}</td>
                  <td>{{ number_format($t->fx_rate ?? 1,2) }}</td>
                  <td>{{ number_format($t->amount,2) }}</td>
                  <td>{{ number_format($t->commission,2) }}</td>
                  <td>{{ $t->status }}</td>
                  <td>
                    @can('Edit-Microfinancing-Transaction')
                      <button class="btn btn-sm btn-info btn-edit-tx" data-id="{{ encrypt($t->id) }}">Edit</button>
                    @endcan
                    @can('Delete-Microfinancing-Transaction')
                      <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-tx" data-id="{{ encrypt($t->id) }}">Cancel</a>
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
</div>

{{-- Create Modal --}}
<div class="modal fade" id="txCreateModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="txCreateForm" action="{{ route('micro.transactions.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Record Transaction</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select name="work_point_id" class="form-control select2_modal" required>
                <option value="">-- Select --</option>
                @foreach($workPoints as $wp)<option value="{{ $wp->id }}">{{ $wp->work_name }}</option>@endforeach
              </select>
            </div>
          @else
            <input type="hidden" name="work_point_id" value="{{ optional(auth()->user())->work_point_id }}">
          @endif

          <div class="form-row">
            <div class="form-group col">
              <label>Type</label>
              <select name="tx_group" class="form-control select2_modal" required>
                <option value="Deposit">Deposit (Kuweka)</option>
                <option value="Withdraw">Withdraw (Kutoa)</option>
                <option value="FX-Sell">FX Sell (Kuuza)</option>
                <option value="FX-Buy">FX Buy (Kununua)</option>
              </select>
            </div>

            <div class="form-group col">
              <label>Bank/Network</label>
              <select name="bank_network_id" class="form-control select2_modal">
                <option value="">-- Optional --</option>
                @foreach($bankNetworks as $bn)
                  <option value="{{ $bn->id }}">{{ $bn->type }} - {{ $bn->name }} @if($bn->work_point_id) ({{ optional($bn->workpoint)->work_name }}) @endif</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Currency</label><input type="text" name="currency" class="form-control" placeholder="e.g. TZS, USD"></div>
            <div class="form-group col"><label>Amount</label><input type="text" name="amount" class="form-control" required></div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>FX Rate (if FX)</label><input type="text" name="fx_rate" class="form-control"></div>
            <div class="form-group col"><label>Commission %</label><input type="text" name="commission_pct" class="form-control" placeholder="eg. 2.5"></div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Commission Fixed</label><input type="text" name="commission_fixed" class="form-control" placeholder="use fixed amount to override pct"></div>
            <div class="form-group col"><label>Meta (JSON) - optional</label><input type="text" name="meta" class="form-control" placeholder='{"client":"John"}'></div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('txCreateForm')" class="btn mb-2 btn-primary">Record</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="txEditModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="txEditForm" method="POST">@csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Transaction</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_tx_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point</label>
              <select id="edit_tx_work_point_id" name="work_point_id" class="form-control select2_modal">
                <option value="">-- Select --</option>
                @foreach($workPoints as $wp) <option value="{{ $wp->id }}">{{ $wp->work_name }}</option> @endforeach
              </select>
            </div>
          @endif

          <div class="form-row">
            <div class="form-group col"><label>Type</label>
              <select id="edit_tx_group" name="tx_group" class="form-control select2_modal" required>
                <option value="Deposit">Deposit</option>
                <option value="Withdraw">Withdraw</option>
                <option value="FX-Sell">FX Sell</option>
                <option value="FX-Buy">FX Buy</option>
              </select>
            </div>

            <div class="form-group col"><label>Bank/Network</label>
              <select id="edit_tx_bn" name="bank_network_id" class="form-control select2_modal">
                <option value="">-- Optional --</option>
                @foreach($bankNetworks as $bn)
                  <option value="{{ $bn->id }}">{{ $bn->type }} - {{ $bn->name }} @if($bn->work_point_id) ({{ optional($bn->workpoint)->work_name }}) @endif</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Currency</label><input id="edit_tx_currency" type="text" name="currency" class="form-control"></div>
            <div class="form-group col"><label>Amount</label><input id="edit_tx_amount" type="text" name="amount" class="form-control" required></div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>FX Rate</label><input id="edit_tx_fx" type="text" name="fx_rate" class="form-control"></div>
            <div class="form-group col"><label>Commission %</label><input id="edit_tx_comm_pct" type="text" name="commission_pct" class="form-control"></div>
          </div>

          <div class="form-row">
            <div class="form-group col"><label>Commission Fixed</label><input id="edit_tx_comm_fixed" type="text" name="commission_fixed" class="form-control"></div>
            <div class="form-group col"><label>Meta (JSON)</label><input id="edit_tx_meta" type="text" name="meta" class="form-control"></div>
          </div>

          <div class="form-group"><label>Status</label>
            <select id="edit_tx_status" name="status" class="form-control select2_modal"><option value="Completed">Completed</option><option value="Pending">Pending</option><option value="Cancelled">Cancelled</option></select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('txEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // init select2 helper (same as your pattern)
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
    if ($this.closest('#txCreateModal').length) { initSelect2WithParent($this, '#txCreateModal'); return; }
    if ($this.closest('#txEditModal').length) { initSelect2WithParent($this, '#txEditModal'); return; }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#txCreateModal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_modal').each(function(){ initSelect2WithParent($(this),'#txCreateModal'); $(this).val(null).trigger('change'); });
  });

  document.querySelectorAll('.btn-delete-tx').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      Swal.fire({
        title: 'Cancel transaction?',
        text: "This will mark the transaction as Cancelled.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then(function(res){
        if (res.isConfirmed) window.location.href = "{{ route('micro.transactions.remove', ':id') }}".replace(':id', enc);
      });
    });
  });

  // Edit: fetch tx json and open modal
  document.querySelectorAll('.btn-edit-tx').forEach(btn => {
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      fetch("{{ url('/admin/micro/transactions/show') }}/" + enc)
        .then(r => r.json())
        .then(data => {
          if (data.error) { Swal.fire('Error', data.error, 'error'); return; }
          // populate fields
          document.getElementById('edit_tx_id').value = enc;
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            $('#edit_tx_work_point_id').val(data.work_point_id).trigger('change');
          @endif
          document.getElementById('edit_tx_group').value = data.tx_group;
          $('#edit_tx_bn').val(data.bank_network_id).trigger('change');
          document.getElementById('edit_tx_currency').value = data.currency;
          document.getElementById('edit_tx_amount').value = data.amount/(data.fx_rate ?? 1);
          document.getElementById('edit_tx_fx').value = data.fx_rate ?? 1;
          document.getElementById('edit_tx_comm_pct').value = data.commission_pct ?? 1; // unknown — user may re-enter
          document.getElementById('edit_tx_comm_fixed').value = data.commission_fixed ?? '';
          document.getElementById('edit_tx_meta').value = data.meta ? JSON.stringify(data.meta) : '';
          document.getElementById('edit_tx_status').value = data.status;
          var form = document.getElementById('txEditForm');
          form.action = "{{ route('micro.transactions.update', ':id') }}".replace(':id', enc);
          $('#txEditModal').modal('show');
        }).catch(err => { Swal.fire('Error','Could not fetch transaction','error'); });
    });
  });

});
</script>
@endsection
