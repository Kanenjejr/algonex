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
                <strong>Quotes</strong>
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
  <h3 class="mb-2 page-title">Quotes</h3>
  @can('Register-Quotes')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#quoteCreateModal">Add Quote</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox ">
    <div class="ibox-title bg-primary"><h5>Quotes Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example">
          <thead><tr><th>#</th><th>Quote No</th><th>Date</th><th>Customer</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @foreach($quotes as $k => $q)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $q->quote_number ?? '-' }}</td>
              <td>{{ $q->quote_date ?? '-' }}</td>
              <td>{{ optional($q->customer)->customer_name ?? '-' }}</td>
              <td>{{ number_format($q->total,2) }}</td>
              <td>{{ $q->status }}</td>
              <td>
                @can('Edit-Quotes')
                  <button class="btn btn-sm btn-warning btn-edit-quote"
                    data-id="{{ encrypt($q->id) }}"
                    data-quote_number="{{ $q->quote_number }}"
                    data-quote_date="{{ $q->quote_date }}"
                    data-expiry_date="{{ $q->expiry_date }}"
                    data-cstm_id="{{ $q->cstm_id }}"
                    data-sub_total="{{ $q->sub_total }}"
                    data-tax="{{ $q->tax }}"
                    data-discount="{{ $q->discount }}"
                    data-total="{{ $q->total }}"
                    data-work_point_id="{{ $q->work_point_id }}"
                    data-status="{{ $q->status ?? 'Draft' }}"
                  >Edit</button>
                @endcan
                @can('Delete-Quotes')
                  <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-quote" data-id="{{ encrypt($q->id) }}">Remove</a>
                @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div></div></div></div>

{{-- Create Modal --}}
<div class="modal fade" id="quoteCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form id="Create" action="{{ route('sales.quotes.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Quote</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select name="work_point_id" class="form-control select2_demo_3" required>
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp) <option value="{{ $wp->id }}">{{ $wp->work_name }}</option> @endforeach
              </select>
            </div>
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
          @else
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
          @endif

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Quote Number</label>
              <input type="text" name="quote_number" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label>Quote Date</label>
              <input type="date" name="quote_date" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label>Expiry Date</label>
              <input type="date" name="expiry_date" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Customer</label>
            <select name="cstm_id" class="form-control select2_demo_3">
              <option value="">-- Select customer --</option>
              @foreach($customers as $cust) <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option> @endforeach
            </select>
          </div>

          {{-- Items: simple dynamic rows (name, qty, unit, unit_price) --}}
          <div id="quote_items_wrapper">
            <label>Items</label>
            <table class="table table-sm" id="quote_items_table">
              <thead><tr><th>Product/Name</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Total</th><th></th></tr></thead>
              <tbody></tbody>
            </table>
            <button type="button" id="add_quote_item" class="btn btn-sm btn-secondary">Add Item</button>
          </div>

          <div class="form-row mt-3">
            <div class="form-group col-md-3"><label>Sub Total</label><input type="number" step="0.01" name="sub_total" class="form-control"></div>
            <div class="form-group col-md-3"><label>Tax</label><input type="number" step="0.01" name="tax" class="form-control"></div>
            <div class="form-group col-md-3"><label>Discount</label><input type="number" step="0.01" name="discount" class="form-control"></div>
            <div class="form-group col-md-3"><label>Total</label><input type="number" step="0.01" name="total" class="form-control"></div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control select2_demo_3">
              <option value="Draft" selected>Draft</option>
              <option value="Sent">Sent</option>
              <option value="Accepted">Accepted</option>
              <option value="Rejected">Rejected</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('Create')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal (basic: items editing skipped for brevity — can be extended) --}}
<div class="modal fade" id="quoteEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form id="quoteEditForm" method="POST">
      @csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Quote</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_quote_id" name="edit_id">
          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_quote_work_point_id" name="work_point_id" class="form-control select2_demo_3">
                <option value="">-- Select work point --</option>
                @foreach($workPoints as $wp) <option value="{{ $wp->id }}">{{ $wp->work_name }}</option> @endforeach
              </select>
            </div>
          @endif

          <div class="form-row">
            <div class="form-group col-md-4"><label>Quote Number</label><input id="edit_quote_number" type="text" name="quote_number" class="form-control"></div>
            <div class="form-group col-md-4"><label>Quote Date</label><input id="edit_quote_date" type="date" name="quote_date" class="form-control"></div>
            <div class="form-group col-md-4"><label>Expiry Date</label><input id="edit_quote_expiry" type="date" name="expiry_date" class="form-control"></div>
          </div>

          <div class="form-group"><label>Customer</label>
            <select id="edit_quote_cstm" name="cstm_id" class="form-control select2_demo_3">
              <option value="">-- Select customer --</option>
              @foreach($customers as $cust) <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option> @endforeach
            </select>
          </div>

          <div class="form-row mt-3">
            <div class="form-group col-md-3"><label>Sub Total</label><input id="edit_quote_sub" type="number" step="0.01" name="sub_total" class="form-control"></div>
            <div class="form-group col-md-3"><label>Tax</label><input id="edit_quote_tax" type="number" step="0.01" name="tax" class="form-control"></div>
            <div class="form-group col-md-3"><label>Discount</label><input id="edit_quote_discount" type="number" step="0.01" name="discount" class="form-control"></div>
            <div class="form-group col-md-3"><label>Total</label><input id="edit_quote_total" type="number" step="0.01" name="total" class="form-control"></div>
          </div>

          <div class="form-group"><label>Status</label>
            <select id="edit_quote_status" name="status" class="form-control select2_demo_3">
              <option value="Draft">Draft</option>
              <option value="Sent">Sent</option>
              <option value="Accepted">Accepted</option>
              <option value="Rejected">Rejected</option>
              <option value="Expired">Expired</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer"><button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button><button type="submit" onclick="handleConfirmSubmit('quoteEditForm')" class="btn mb-2 btn-primary">Update</button></div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Helper to init a single select2 element with a given parent
  function initSelect2El($el, $parent) {
    if (!$el || !$el.length) return;
    // destroy if already initialized (safe)
    if ($el.data('select2')) {
      try { $el.select2('destroy'); } catch(e) { /* ignore */ }
    }
    // dropdownParent must be a jQuery object pointing to modal .modal-content for correct stacking
    var parent = ($parent && $parent.length) ? $parent : $(document.body);
    $el.select2({ width: '100%', theme: 'bootstrap4', dropdownParent: parent });
  }

  // Initialize select2 for selects that are NOT INSIDE MODALS (regular page selects)
  $('.select2_demo_3').each(function () {
    var $this = $(this);
    // if this element is inside a modal, skip here — will initialize on modal show
    if ($this.closest('#quoteCreateModal').length || $this.closest('#quoteEditModal').length) {
      return; // skip, init later on modal show
    }
    initSelect2El($this, $(document.body));
  });

  // Initialize selects inside create modal when modal is shown
  $('#quoteCreateModal').on('shown.bs.modal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function () {
      initSelect2El($(this), $modal.find('.modal-content'));
    });
  });

  // Destroy selects inside create modal when hidden (prevents duplicate init)
  $('#quoteCreateModal').on('hidden.bs.modal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function () {
      var $el = $(this);
      if ($el.data('select2')) {
        try { $el.select2('destroy'); } catch(e) {}
      }
    });
  });

  // Initialize selects inside edit modal when modal is shown
  $('#quoteEditModal').on('shown.bs.modal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function () {
      initSelect2El($(this), $modal.find('.modal-content'));
    });
  });

  // Destroy selects inside edit modal when hidden (prevents duplicate init)
  $('#quoteEditModal').on('hidden.bs.modal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function () {
      var $el = $(this);
      if ($el.data('select2')) {
        try { $el.select2('destroy'); } catch(e) {}
      }
    });
  });

  // Dynamic items: add row
  function addQuoteItemRow(product='', qty='', unit='', unit_price='') {
    var total = (parseFloat(qty||0) * parseFloat(unit_price||0)).toFixed(2);
    var row = `<tr>
      <td><input type="text" name="item_product_name[]" class="form-control" value="${product}"></td>
      <td><input type="number" step="0.0001" name="item_quantity[]" class="form-control item-qty" value="${qty}"></td>
      <td><input type="text" name="item_unit[]" class="form-control" value="${unit}"></td>
      <td><input type="number" step="0.01" name="item_unit_price[]" class="form-control item-price" value="${unit_price}"></td>
      <td class="item-total">${total}</td>
      <td><button type="button" class="btn btn-sm btn-danger remove-quote-item">X</button></td>
    </tr>`;
    $('#quote_items_table tbody').append(row);
  }

  $('#add_quote_item').on('click', function(){ addQuoteItemRow('','1','','0'); });

  $(document).on('click', '.remove-quote-item', function(){ $(this).closest('tr').remove(); });

  $(document).on('input', '.item-qty, .item-price', function(){
    var $tr = $(this).closest('tr');
    var qty = parseFloat($tr.find('.item-qty').val()||0);
    var price = parseFloat($tr.find('.item-price').val()||0);
    $tr.find('.item-total').text((qty*price).toFixed(2));
  });

  // Edit button
  document.querySelectorAll('.btn-edit-quote').forEach(function(btn){
    btn.addEventListener('click', function(){
      var enc = this.dataset.id;
      document.getElementById('edit_quote_id').value = enc || '';
      document.getElementById('edit_quote_number').value = this.dataset.quote_number || '';
      document.getElementById('edit_quote_date').value = this.dataset.quote_date || '';
      document.getElementById('edit_quote_expiry').value = this.dataset.expiry_date || '';
      document.getElementById('edit_quote_sub').value = this.dataset.sub_total || '';
      document.getElementById('edit_quote_tax').value = this.dataset.tax || '';
      document.getElementById('edit_quote_discount').value = this.dataset.discount || '';
      document.getElementById('edit_quote_total').value = this.dataset.total || '';
      document.getElementById('edit_quote_status').value = this.dataset.status || 'Draft';
      // set selected work point/customer if present
      if (this.dataset.work_point_id) {
        var $wp = $('#edit_quote_work_point_id');
        if ($wp.length) {
          $wp.val(this.dataset.work_point_id).trigger('change');
        }
      }
      if (this.dataset.cstm_id) {
        var $c = $('#edit_quote_cstm');
        if ($c.length) {
          $c.val(this.dataset.cstm_id).trigger('change');
        }
      }
      var form = document.getElementById('quoteEditForm');
      form.action = "{{ route('sales.quotes.update', ':id') }}".replace(':id', enc);
      $('#quoteEditModal').modal('show');
    });
  });

  // Delete quote
  document.querySelectorAll('.btn-delete-quote').forEach(function(btn){
    btn.addEventListener('click', function () {
      var enc = this.dataset.id;
      Swal.fire({ title: 'Are you sure?', text: "This will mark the quote as Deleted.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!' })
      .then(function(res){ if (res.isConfirmed) window.location.href = "{{ route('sales.quotes.remove', ':id') }}".replace(':id', enc); });
    });
  });
});
</script>
@endsection
