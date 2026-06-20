@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Assets Transaction Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('accounting') }}">Accounting And Finance</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Assets Transaction Registration</strong>
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
  <h3 class="mb-2 page-title">Asset Transactions</h3>
  @can('Register-Asset-Transactions')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#txCreateModal">Add Transaction</button>
  @endcan
</div>
<div class="wrapper wrapper-content animated fadeInRight mt-5">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-success"><h5>Asset Transactions</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Category</th>
                  <th>Serial / Ref</th>
                  <th>Description</th>
                  <th>Cost</th>
                  <th>Work Point</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($assets as $k => $t)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ optional($t->transaction_date ?? $t->purchase_date)->format('Y-m-d') }}</td>
                  <td>{{ ucfirst($t->transaction_type ?? 'acquisition') }}</td>
                  <td>{{ optional($t->category)->name ?? '-' }}</td>
                  <td>{{ $t->asset_tag ?? '-' }}</td>
                  <td>{{ $t->description ?? '-' }}</td>
                  <td>{{ number_format($t->purchase_cost ?? 0, 2) }}</td>
                  <td>{{ optional($t->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $t->status ?? 'Active' }}</td>
                  <td>
                    @can('Edit-Asset-Transactions')
                      <button
                        class="btn btn-sm btn-warning btn-edit-tx"
                        data-toggle="modal"
                        data-target="#txEditModal"
                        data-id="{{ $t->id }}"
                        data-asset_name="{{ $t->asset_name }}"
                        data-asset_tag="{{ $t->asset_tag }}"
                        data-asset_category_id="{{ $t->asset_category_id }}"
                        data-purchase_date="{{ optional($t->purchase_date)->format('Y-m-d') }}"
                        data-purchase_cost="{{ $t->purchase_cost }}"
                        data-useful_life_years="{{ $t->useful_life_years }}"
                        data-description="{{ $t->description }}"
                        data-company_id="{{ $t->company_id }}"
                        data-work_point_id="{{ $t->work_point_id }}"
                        data-status="{{ $t->status ?? 'Active' }}"
                      >Edit</button>
                    @endcan

                    @can('Dispose-Asset')
                      <button class="btn btn-sm btn-danger btn-open-dispose" data-id="{{ $t->id }}" data-asset_tag="{{ $t->asset_tag }}" data-purchase_cost="{{ $t->purchase_cost }}" data-toggle="modal" data-target="#disposeModal">Dispose</button>
                    @endcan

                    @can('Revalue-Asset')
                      <button class="btn btn-sm btn-info btn-open-revalue" data-id="{{ $t->id }}" data-asset_tag="{{ $t->asset_tag }}" data-purchase_cost="{{ $t->purchase_cost }}" data-toggle="modal" data-target="#revalueModal">Revalue</button>
                    @endcan

                    @can('Delete-Asset-Transactions')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-tx" data-id="{{ $t->id }}">Remove</a>
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

<!-- Create Asset Modal -->
<div class="modal fade" id="txCreateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="txCreateForm" action="{{ route('assets.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Record Asset (Acquisition)</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">

          @if($isSuper ?? in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span class="text-danger">*</span></label>
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
            <label>Asset Name <span class="text-danger">*</span></label>
            <input type="text" name="asset_name" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Asset Tag / Serial</label>
            <input type="text" name="asset_tag" class="form-control">
          </div>

          <div class="form-group">
            <label>Asset Category</label>
            <select name="asset_category_id" class="form-control select2_demo_3">
              <option value="">-- Select Category --</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ number_format($cat->depreciation_rate ?? 0,2) }}%)</option>
              @endforeach
            </select>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Purchase Date <span class="text-danger">*</span></label>
              <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group col">
              <label>Purchase Cost (TZS) <span class="text-danger">*</span></label>
              <input type="number" name="purchase_cost" step="0.01" min="0" class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label>Useful Life (years)</label>
            <input type="number" name="useful_life_years" step="1" min="0" class="form-control">
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control select2_demo_3">
              <option value="Active" selected>Active</option>
              <option value="Disposed">Disposed</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('txCreateForm')"class="btn mb-2 btn-primary">Record Asset</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Asset Modal -->
<div class="modal fade" id="txEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="txEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Asset</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_asset_id" name="edit_id">

          @if($isSuper ?? in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span class="text-danger">*</span></label>
              <select id="edit_work_point_id" name="work_point_id" class="form-control select2_demo_3">
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
            <label>Asset Name</label>
            <input id="edit_asset_name" type="text" name="asset_name" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Asset Tag / Serial</label>
            <input id="edit_asset_tag" type="text" name="asset_tag" class="form-control">
          </div>

          <div class="form-group">
            <label>Asset Category</label>
            <select id="edit_asset_category_id" name="asset_category_id" class="form-control select2_demo_3">
              <option value="">-- Select Category --</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ number_format($cat->depreciation_rate ?? 0,2) }}%)</option>
              @endforeach
            </select>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Purchase Date</label>
              <input id="edit_purchase_date" type="date" name="purchase_date" class="form-control" required>
            </div>
            <div class="form-group col">
              <label>Purchase Cost (TZS)</label>
              <input id="edit_purchase_cost" type="number" name="purchase_cost" step="0.01" min="0" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Useful Life (years)</label>
            <input id="edit_useful_life_years" type="number" name="useful_life_years" step="1" min="0" class="form-control">
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea id="edit_description" name="description" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_status" name="status" class="form-control select2_demo_3">
              <option value="Active">Active</option>
              <option value="Disposed">Disposed</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('txEditForm')" class="btn mb-2 btn-primary">Update Asset</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Dispose Modal -->
<div class="modal fade" id="disposeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="disposeForm" method="POST" >
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Dispose Asset</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="dispose_asset_id" name="asset_id">

          <div class="form-group">
            <label>Asset Tag / Reference</label>
            <input type="text" id="dispose_asset_tag" class="form-control" readonly>
          </div>

          <div class="form-group">
            <label>Disposal Date <span class="text-danger">*</span></label>
            <input type="date" id="dispose_transaction_date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>

          <div class="form-group">
            <label>Disposed / Salvage Value (TZS) <span class="text-danger">*</span></label>
            <input type="number" id="dispose_value" name="disposal_value" step="0.01" min="0" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Reason / Note</label>
            <textarea id="dispose_description" name="description" class="form-control"></textarea>
          </div>

          <small class="form-text text-muted">On successful disposal, the controller should mark the asset transaction as Disposed and record disposal value for reporting.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('disposeForm')" class="btn mb-2 btn-danger">Confirm Disposal</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Revalue Modal -->
<div class="modal fade" id="revalueModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="revalueForm" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Revalue / Add Value to Asset</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="revalue_asset_id" name="asset_id">

          <div class="form-group">
            <label>Asset Tag / Reference</label>
            <input type="text" id="revalue_asset_tag" class="form-control" readonly>
          </div>

          <div class="form-group">
            <label>Revaluation Date <span class="text-danger">*</span></label>
            <input type="date" id="revalue_transaction_date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>

          <div class="form-group">
            <label>Revalue Amount (TZS) <span class="text-danger">*</span></label>
            <input type="number" id="revalue_amount" name="revalue_amount" step="0.01" min="0" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Reason / Note</label>
            <textarea id="revalue_description" name="description" class="form-control"></textarea>
          </div>

          <small class="form-text text-muted">This will create a revaluation / addition entry — controller should record the added value and update book value accordingly.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('revalueForm')" class="btn mb-2 btn-info">Apply Revaluation</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

  // fill edit modal for assets (pull previous values)
  document.querySelectorAll('.btn-edit-tx').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      document.getElementById('edit_asset_id').value = id;

      document.getElementById('edit_asset_name').value = this.dataset.asset_name || '';
      document.getElementById('edit_asset_tag').value = this.dataset.asset_tag || '';
      document.getElementById('edit_purchase_date').value = this.dataset.purchase_date || '';
      document.getElementById('edit_purchase_cost').value = this.dataset.purchase_cost || '';
      document.getElementById('edit_useful_life_years').value = this.dataset.useful_life_years || '';
      document.getElementById('edit_description').value = this.dataset.description || '';
      document.getElementById('edit_status').value = this.dataset.status || 'Active';

      // select2 fields: category & work_point
      if (this.dataset.asset_category_id) {
        $('#edit_asset_category_id').val(this.dataset.asset_category_id).trigger('change');
      } else {
        $('#edit_asset_category_id').val('').trigger('change');
      }

      const wpSelect = document.getElementById('edit_work_point_id');
      if (wpSelect) {
        wpSelect.value = this.dataset.work_point_id || '';
        $('#edit_work_point_id').trigger('change');
      }

      // set form action to PUT route with numeric id
      const form = document.getElementById('txEditForm');
      form.action = "{{ route('assets.update', ':id') }}".replace(':id', id);
    });
  });

  // delete (soft)
  document.querySelectorAll('.btn-delete-tx').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the asset as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it!',
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "{{ route('assets.remove', ':id') }}".replace(':id', id);
        }
      });
    });
  });

  // Dispose: open modal and set form action & fields
  document.querySelectorAll('.btn-open-dispose').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const tag = this.dataset.asset_tag || '';
      const cost = this.dataset.purchase_cost || '';

      document.getElementById('dispose_asset_id').value = id;
      document.getElementById('dispose_asset_tag').value = tag;
      document.getElementById('dispose_value').value = cost;

      const form = document.getElementById('disposeForm');
      form.action = "{{ route('assets.dispose', ':id') }}".replace(':id', id);
    });
  });

  // Revalue: open modal and set form action & fields
  document.querySelectorAll('.btn-open-revalue').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const tag = this.dataset.asset_tag || '';

      document.getElementById('revalue_asset_id').value = id;
      document.getElementById('revalue_asset_tag').value = tag;
      document.getElementById('revalue_amount').value = '';

      const form = document.getElementById('revalueForm');
      form.action = "{{ route('assets.revalue', ':id') }}".replace(':id', id);
    });
  });
  // initialize select2 if used (with modal parent)
  $(document).ready(function() {
    $('.select2_demo_3').select2({ width: '100%', theme: 'bootstrap4' });
    $('#txCreateModal .select2_demo_3').select2({width: '100%', theme: 'bootstrap4', dropdownParent: $('#txCreateModal') });
    $('#txEditModal .select2_demo_3').select2({width: '100%', theme: 'bootstrap4',dropdownParent: $('#txEditModal') });
    $('#disposeModal .select2_demo_3').select2({width: '100%', theme: 'bootstrap4', dropdownParent: $('#disposeModal') });
    $('#revalueModal .select2_demo_3').select2({width: '100%', theme: 'bootstrap4', dropdownParent: $('#revalueModal') });
  });
});
</script>
@endsection
