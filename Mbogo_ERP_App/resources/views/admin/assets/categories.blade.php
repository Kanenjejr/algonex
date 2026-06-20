@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Assets Categories Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('accounting') }}">Accounting And Finance</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Assets Categories Registration</strong>
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
  <h3 class="mb-2 page-title">Asset Categories</h3>
  @can('Register-Asset-Categories')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#catCreateModal">Add Category</button>
  @endcan
</div>
<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-success"><h5>Asset Category Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Category</th>
                  <th>Depreciation (%)</th>
                  <th>Useful Life (yrs)</th>
                  <th>Company</th>
                  <th>Work Point</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($categories as $k => $c)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ $c->name }}</td>
                  <td>{{ number_format($c->depreciation_rate ?? 0, 2) }}%</td>
                  <td>{{ $c->useful_life_years ?? '-' }}</td>
                  <td>{{ optional($c->company)->company_name ?? '-' }}</td>
                  <td>{{ optional($c->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $c->Status ?? 'Active' }}</td>
                  <td>
                    @can('Edit-Asset-Categories')
                      <button
                        class="btn btn-sm btn-warning btn-edit-cat"
                        data-toggle="modal"
                        data-target="#catEditModal"
                        data-id="{{ encrypt($c->id) }}"
                        data-name="{{ $c->name }}"
                        data-depr="{{ $c->depreciation_rate }}"
                        data-life="{{ $c->useful_life_years }}"
                        data-code="{{ $c->code ?? '' }}"
                        data-desc="{{ $c->description ?? '' }}"
                        data-company_id="{{ $c->company_id }}"
                        data-work_point_id="{{ $c->work_point_id }}"
                        data-status="{{ $c->Status ?? 'Active' }}"
                      >Edit</button>
                    @endcan

                    @can('Delete-Asset-Categories')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-cat" data-id="{{ encrypt($c->id) }}">Remove</a>
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

<!-- Create Modal -->
<div class="modal fade" id="catCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="catCreateForm" action="{{ route('assets.categories.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Asset Category</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">

          {{-- Work point select for admin-like roles; else hidden fields (controller will use auth user) --}}
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
            <label>Category Name <span style="color:red">*</span></label>
            <input type="text" name="name" class="form-control" required>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Depreciation Rate (%) <span style="color:red">*</span></label>
              <input type="number" name="depreciation_rate" step="0.01" min="0" max="100" class="form-control" required>
              <small class="form-text text-muted">Annual percentage (e.g. enter 25 for 25%).</small>
            </div>

            <div class="form-group col">
              <label>Useful Life (years)</label>
              <input type="number" name="useful_life_years" step="1" min="0" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="Status" class="form-control select2_demo_3">
              <option value="Active" selected>Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('catCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="catEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="catEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Asset Category</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_cat_id" name="edit_id">

          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
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
            <label>Category Name</label>
            <input id="edit_name" type="text" name="name" class="form-control" required>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label>Depreciation Rate (%)</label>
              <input id="edit_depr" type="number" name="depreciation_rate" step="0.01" min="0" max="100" class="form-control" required>
            </div>
            <div class="form-group col">
              <label>Useful Life (years)</label>
              <input id="edit_life" type="number" name="useful_life_years" step="1" min="0" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Code (optional)</label>
            <input id="edit_code" type="text" name="code" class="form-control">
          </div>

          <div class="form-group">
            <label>Description (optional)</label>
            <textarea id="edit_desc" name="description" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_Status" name="Status" class="form-control select2_demo_3">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('catEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // fill edit modal for categories (pull previous values including selects)
  document.querySelectorAll('.btn-edit-cat').forEach(btn => {
    btn.addEventListener('click', function () {
      const encId = this.dataset.id;
      document.getElementById('edit_cat_id').value = encId;
      document.getElementById('edit_name').value = this.dataset.name || '';
      document.getElementById('edit_depr').value = this.dataset.depr || '';
      document.getElementById('edit_life').value = this.dataset.life || '';
      document.getElementById('edit_code').value = this.dataset.code || '';
      document.getElementById('edit_desc').value = this.dataset.desc || '';
      document.getElementById('edit_Status').value = this.dataset.status || 'Active';

      // set work point select (if present) and trigger select2
      const wpSelect = document.getElementById('edit_work_point_id');
      if (wpSelect) {
        wpSelect.value = this.dataset.work_point_id || '';
        $('#edit_work_point_id').trigger('change');
      }
      // set form action to PUT route with encrypted id
      const form = document.getElementById('catEditForm');
      form.action = "{{ route('assets.categories.update', ':id') }}".replace(':id', encId);
      // ensure select2 visuals update for status
      $('#edit_Status').trigger('change');
    });
  });
  // delete (soft)
  document.querySelectorAll('.btn-delete-cat').forEach(btn => {
    btn.addEventListener('click', function () {
      const encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the asset category as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "{{ route('assets.categories.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  // initialize select2 if used (with modal parent)
  $(document).ready(function() {
    $('.select2_demo_3').select2({ width: '100%', theme: 'bootstrap4' });
    $('#catCreateModal .select2_demo_3').select2({ width: '100%', theme: 'bootstrap4', dropdownParent: $('#catCreateModal') });
    $('#catEditModal .select2_demo_3').select2({ width: '100%', theme: 'bootstrap4', dropdownParent: $('#catEditModal') });
  });

});
</script>
@endsection
