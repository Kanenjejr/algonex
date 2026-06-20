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
                <strong>Campaigns</strong>
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
  <h3 class="mb-2 page-title">Marketing Campaigns</h3>
  @can('Register-Campaigns')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#campaignCreateModal">Add Campaign</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-primary"><h5>Campaigns Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Campaign</th>
                  <th>Objective</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Budget</th>
                  <th>Actual</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($campaigns as $k => $c)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ $c->name }}</td>
                  <td>{{ Str::limit($c->objective, 50) ?? '-' }}</td>
                  <td>{{ $c->start_date ?? '-' }}</td>
                  <td>{{ $c->end_date ?? '-' }}</td>
                  <td>{{ number_format($c->budget,2) }}</td>
                  <td>{{ number_format($c->actual_cost,2) }}</td>
                  <td>{{ $c->status }}</td>
                  <td>
                    @can('Edit-Campaigns')
                      <button class="btn btn-sm btn-warning btn-edit-campaign"
                        data-id="{{ encrypt($c->id) }}"
                        data-name="{{ $c->name }}"
                        data-objective="{{ $c->objective }}"
                        data-start_date="{{ $c->start_date }}"
                        data-end_date="{{ $c->end_date }}"
                        data-budget="{{ $c->budget }}"
                        data-actual_cost="{{ $c->actual_cost }}"
                        data-work_point_id="{{ $c->work_point_id }}"
                        data-status="{{ $c->status ?? 'Planned' }}"
                      >Edit</button>
                    @endcan
                    @can('Delete-Campaigns')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-campaign" data-id="{{ encrypt($c->id) }}">Remove</a>
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
<div class="modal fade" id="campaignCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="campaignCreateForm" action="{{ route('sales.campaigns.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Campaign</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
            <label>Campaign Name <span style="color:red">*</span></label>
            <input type="text" name="name" class="form-control" required>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Start Date</label>
              <input type="date" name="start_date" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>End Date</label>
              <input type="date" name="end_date" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Objective</label>
            <textarea name="objective" class="form-control"></textarea>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Budget</label>
              <input type="number" step="0.01" name="budget" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>Actual Cost</label>
              <input type="number" step="0.01" name="actual_cost" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control select2_demo_3">
              <option value="Planned" selected>Planned</option>
              <option value="Running">Running</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('campaignCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="campaignEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="campaignEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Campaign</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_campaign_id" name="edit_id">

          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_campaign_work_point_id" name="work_point_id" class="form-control select2_demo_3">
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
            <label>Campaign Name <span style="color:red">*</span></label>
            <input id="edit_campaign_name" type="text" name="name" class="form-control" required>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Start Date</label>
              <input id="edit_campaign_start_date" type="date" name="start_date" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>End Date</label>
              <input id="edit_campaign_end_date" type="date" name="end_date" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Objective</label>
            <textarea id="edit_campaign_objective" name="objective" class="form-control"></textarea>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Budget</label>
              <input id="edit_campaign_budget" type="number" step="0.01" name="budget" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>Actual Cost</label>
              <input id="edit_campaign_actual_cost" type="number" step="0.01" name="actual_cost" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_campaign_status" name="status" class="form-control select2_demo_3">
              <option value="Planned">Planned</option>
              <option value="Running">Running</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('campaignEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  var tempCampaignEditData = null;

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
    if ($this.closest('#campaignCreateModal').length) {
      initSelect2WithParent($this, '#campaignCreateModal');
      return;
    }
    if ($this.closest('#campaignEditModal').length) {
      initSelect2WithParent($this, '#campaignEditModal');
      return;
    }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#campaignCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#campaignCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  $(document).on('shown.bs.modal', '#campaignEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#campaignEditModal');
    });

    if (tempCampaignEditData) {
      $('#edit_campaign_work_point_id').val(tempCampaignEditData.work_point_id || '').trigger('change');
      tempCampaignEditData = null;
    }
  });

  document.querySelectorAll('.btn-edit-campaign').forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault && e.preventDefault();
      var encId = this.dataset.id;

      document.getElementById('edit_campaign_id').value = encId || '';
      document.getElementById('edit_campaign_name').value = this.dataset.name || '';
      document.getElementById('edit_campaign_objective').value = this.dataset.objective || '';
      document.getElementById('edit_campaign_start_date').value = this.dataset.start_date || '';
      document.getElementById('edit_campaign_end_date').value = this.dataset.end_date || '';
      document.getElementById('edit_campaign_budget').value = this.dataset.budget || '';
      document.getElementById('edit_campaign_actual_cost').value = this.dataset.actual_cost || '';
      document.getElementById('edit_campaign_status').value = this.dataset.status || 'Planned';

      tempCampaignEditData = {
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null
      };

      var form = document.getElementById('campaignEditForm');
      form.action = "{{ route('sales.campaigns.update', ':id') }}".replace(':id', encId);

      $('#campaignEditModal').modal('show');
    });
  });

  document.querySelectorAll('.btn-delete-campaign').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the campaign as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('sales.campaigns.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  $('#campaignCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#campaignCreateModal');
    });
  });

});
</script>
@endsection
