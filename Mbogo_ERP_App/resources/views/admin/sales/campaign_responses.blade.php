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
                <strong>Campaigns Responses</strong>
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
  <h3 class="mb-2 page-title">Campaign Responses</h3>
  @can('Register-CampaignResponses')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#responseCreateModal">Add Response</button>
  @endcan
</div>
<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-primary"><h5>Campaign Responses Table</h5></div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Campaign</th>
                  <th>Customer</th>
                  <th>Contact</th>
                  <th>Response</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Notes</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($responses as $k => $r)
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>{{ optional($r->campaign)->name ?? '-' }}</td>
                  <td>{{ optional($r->customer)->customer_name ?? '-' }}</td>
                  <td>{{ optional($r->contact)->first_name ? (optional($r->contact)->first_name . ' ' . optional($r->contact)->last_name) : '-' }}</td>
                  <td>{{ $r->response_type ?? '-' }}</td>
                  <td>{{ $r->response_date ?? '-' }}</td>
                  <td>{{ $r->status }}</td>
                  <td>{{ Str::limit($r->notes, 80) }}</td>
                  <td>
                    @can('Edit-CampaignResponses')
                      <button class="btn btn-sm btn-warning btn-edit-response"
                        data-id="{{ encrypt($r->id) }}"
                        data-marketing_campaign_id="{{ $r->marketing_campaign_id }}"
                        data-cstm_id="{{ $r->cstm_id }}"
                        data-contact_id="{{ $r->contact_id }}"
                        data-response_type="{{ $r->response_type }}"
                        data-response_date="{{ $r->response_date }}"
                        data-notes="{{ $r->notes }}"
                        data-work_point_id="{{ $r->work_point_id }}"
                        data-status="{{ $r->status ?? 'New' }}"
                      >Edit</button>
                    @endcan
                    @can('Delete-CampaignResponses')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-response" data-id="{{ encrypt($r->id) }}">Remove</a>
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
<div class="modal fade" id="responseCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="responseCreateForm" action="{{ route('sales.campaignresponses.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Campaign Response</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
            <div class="form-group col-md-6">
              <label>Campaign</label>
              <select name="marketing_campaign_id" class="form-control select2_demo_3">
                <option value="">-- Select campaign --</option>
                @foreach($campaigns as $c)
                  <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Response Date</label>
              <input type="date" name="response_date" class="form-control">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Customer (optional)</label>
              <select name="cstm_id" class="form-control select2_demo_3">
                <option value="">-- Select customer --</option>
                @foreach($customers as $cust)
                  <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Contact (optional)</label>
              <select name="contact_id" class="form-control select2_demo_3">
                <option value="">-- Select contact --</option>
                @foreach($contacts as $ct)
                  <option value="{{ $ct->id }}">{{ $ct->first_name }} {{ $ct->last_name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Response Type</label>
            <input type="text" name="response_type" class="form-control" placeholder="Interested, Not Interested, Bounce...">
          </div>

          <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control select2_demo_3">
              <option value="New" selected>New</option>
              <option value="Processed">Processed</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('responseCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="responseEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="responseEditForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Campaign Response</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_response_id" name="edit_id">

          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_response_work_point_id" name="work_point_id" class="form-control select2_demo_3">
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
            <div class="form-group col-md-6">
              <label>Campaign</label>
              <select id="edit_response_campaign" name="marketing_campaign_id" class="form-control select2_demo_3">
                <option value="">-- Select campaign --</option>
                @foreach($campaigns as $c)
                  <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Response Date</label>
              <input id="edit_response_date" type="date" name="response_date" class="form-control">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Customer (optional)</label>
              <select id="edit_response_cstm_id" name="cstm_id" class="form-control select2_demo_3">
                <option value="">-- Select customer --</option>
                @foreach($customers as $cust)
                  <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Contact (optional)</label>
              <select id="edit_response_contact_id" name="contact_id" class="form-control select2_demo_3">
                <option value="">-- Select contact --</option>
                @foreach($contacts as $ct)
                  <option value="{{ $ct->id }}">{{ $ct->first_name }} {{ $ct->last_name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Response Type</label>
            <input id="edit_response_type" type="text" name="response_type" class="form-control">
          </div>

          <div class="form-group">
            <label>Notes</label>
            <textarea id="edit_response_notes" name="notes" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_response_status" name="status" class="form-control select2_demo_3">
              <option value="New">New</option>
              <option value="Processed">Processed</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('responseEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  var tempResponseEditData = null;

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
    if ($this.closest('#responseCreateModal').length) {
      initSelect2WithParent($this, '#responseCreateModal');
      return;
    }
    if ($this.closest('#responseEditModal').length) {
      initSelect2WithParent($this, '#responseEditModal');
      return;
    }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#responseCreateModal', function () {
    var $modal = $(this);
    var form = $modal.find('form')[0];
    if (form) form.reset();
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#responseCreateModal');
      $(this).val(null).trigger('change');
    });
  });

  $(document).on('shown.bs.modal', '#responseEditModal', function () {
    var $modal = $(this);
    $modal.find('.select2_demo_3').each(function(){
      initSelect2WithParent($(this), '#responseEditModal');
    });

    if (tempResponseEditData) {
      $('#edit_response_work_point_id').val(tempResponseEditData.work_point_id || '').trigger('change');
      $('#edit_response_campaign').val(tempResponseEditData.marketing_campaign_id || '').trigger('change');
      $('#edit_response_cstm_id').val(tempResponseEditData.cstm_id || '').trigger('change');
      $('#edit_response_contact_id').val(tempResponseEditData.contact_id || '').trigger('change');
      tempResponseEditData = null;
    }
  });

  document.querySelectorAll('.btn-edit-response').forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault && e.preventDefault();
      var encId = this.dataset.id;

      document.getElementById('edit_response_id').value = encId || '';
      document.getElementById('edit_response_date').value = this.dataset.response_date || '';
      document.getElementById('edit_response_type').value = this.dataset.response_type || '';
      document.getElementById('edit_response_notes').value = this.dataset.notes || '';
      document.getElementById('edit_response_status').value = this.dataset.status || 'New';

      tempResponseEditData = {
        work_point_id: (typeof this.dataset.work_point_id !== 'undefined') ? this.dataset.work_point_id : null,
        marketing_campaign_id: (typeof this.dataset.marketing_campaign_id !== 'undefined') ? this.dataset.marketing_campaign_id : null,
        cstm_id: (typeof this.dataset.cstm_id !== 'undefined') ? this.dataset.cstm_id : null,
        contact_id: (typeof this.dataset.contact_id !== 'undefined') ? this.dataset.contact_id : null
      };

      var form = document.getElementById('responseEditForm');
      form.action = "{{ route('sales.campaignresponses.update', ':id') }}".replace(':id', encId);

      $('#responseEditModal').modal('show');
    });
  });

  document.querySelectorAll('.btn-delete-response').forEach(function(btn){
    btn.addEventListener('click', function () {
      var encId = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the response as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(function(result){
        if (result.isConfirmed) {
          window.location.href = "{{ route('sales.campaignresponses.remove', ':id') }}".replace(':id', encId);
        }
      });
    });
  });

  $('#responseCreateModal').on('show.bs.modal', function(){
    var $m = $(this);
    var form = $m.find('form')[0];
    if (form) form.reset();
    $m.find('.select2_demo_3').each(function(){
      $(this).val(null).trigger('change');
      initSelect2WithParent($(this), '#responseCreateModal');
    });
  });

});
</script>
@endsection
