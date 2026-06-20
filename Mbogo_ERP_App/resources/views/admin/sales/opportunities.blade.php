
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
                <strong>Opportunities</strong>
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
  <h3 class="mb-2 page-title">Opportunities</h3>
  @can('Register-Opportunities')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#oppCreateModal">Add Opportunity</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox ">
    <div class="ibox-title bg-primary"><h5>Opportunities Table</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example">
          <thead>
            <tr>
              <th>#</th><th>Name</th><th>Customer</th><th>Value</th><th>Close Expected</th><th>Stage</th><th>Assigned</th><th>Status</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($opps as $k => $o)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $o->opportunity_name }}</td>
              <td>{{ optional($o->customer)->customer_name ?? '-' }}</td>
              <td>{{ number_format($o->estimated_value,2) }}</td>
              <td>{{ $o->close_expected ?? '-' }}</td>
              <td>{{ $o->stage }}</td>
              <td>{{ optional($o->assignedTo)->name ?? '-' }}</td>
              <td>{{ $o->status }}</td>
              <td>
                @can('Edit-Opportunities')
                    <button class="btn btn-sm btn-warning btn-edit-opp"
                    data-id="{{ encrypt($o->id) }}"
                    data-opportunity-name="{{ $o->opportunity_name }}"
                    data-cstm-id="{{ $o->cstm_id }}"
                    data-estimated-value="{{ $o->estimated_value }}"
                    data-close-expected="{{ $o->close_expected }}"
                    data-stage="{{ $o->stage }}"
                    data-assigned-to="{{ $o->assigned_to }}"
                    data-work-point-id="{{ $o->work_point_id }}"
                    data-description="{{ $o->description }}"
                    data-status="{{ $o->status ?? 'Open' }}">Edit</button>
                @endcan
                @can('Delete-Opportunities')
                  <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-opp" data-id="{{ encrypt($o->id) }}">Remove</a>
                @endcan
                @can('Close-Opportunity')
                  <a href="{{ route('sales.opportunities.close', [encrypt($o->id),'won']) }}" class="btn btn-sm btn-success">Win</a>
                  <a href="{{ route('sales.opportunities.close', [encrypt($o->id),'lost']) }}" class="btn btn-sm btn-danger">Lose</a>
                @endcan
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
<div class="modal fade" id="oppCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="oppCreateForm" action="{{ route('sales.opportunities.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Opportunity</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
            <div class="form-group col-md-8">
              <label>Opportunity Name <span style="color:red">*</span></label>
              <input type="text" name="opportunity_name" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
              <label>Stage</label>
              <select name="stage" class="form-control select2_demo_3">
                <option value="Prospecting">Prospecting</option>
                <option value="Qualification">Qualification</option>
                <option value="Proposal">Proposal</option>
                <option value="Negotiation">Negotiation</option>
                <option value="On Hold">On Hold</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Customer (optional)</label>
              <select name="cstm_id" class="form-control select2_demo_3">
                <option value="">-- Select customer --</option>
                @foreach($customers as $cust) <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option> @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Assign To</label>
              <select name="assigned_to" class="form-control select2_demo_3">
                <option value="">-- Select user --</option>
                @foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Estimated Value</label>
              <input type="number" step="0.01" name="estimated_value" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>Close Expected</label>
              <input type="date" name="close_expected" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('oppCreateForm')" class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="oppEditModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="oppEditForm" method="POST">
      @csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Opportunity</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <input type="hidden" id="edit_opp_id" name="edit_id">

          @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
            <div class="form-group">
              <label>Work Point <span style="color:red">*</span></label>
              <select id="edit_opp_work_point_id" name="work_point_id" class="form-control select2_demo_3">
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
            <div class="form-group col-md-8">
              <label>Opportunity Name</label>
              <input id="edit_opp_name" type="text" name="opportunity_name" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
              <label>Stage</label>
              <select id="edit_opp_stage" name="stage" class="form-control select2_demo_3">
                <option value="Prospecting">Prospecting</option>
                <option value="Qualification">Qualification</option>
                <option value="Proposal">Proposal</option>
                <option value="Negotiation">Negotiation</option>
                <option value="On Hold">On Hold</option>
                <option value="Closed Won">Closed Won</option>
                <option value="Closed Lost">Closed Lost</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Customer (optional)</label>
              <select id="edit_opp_cstm" name="cstm_id" class="form-control select2_demo_3">
                <option value="">-- Select customer --</option>
                @foreach($customers as $cust) <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option> @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Assign To</label>
              <select id="edit_opp_assigned" name="assigned_to" class="form-control select2_demo_3">
                <option value="">-- Select user --</option>
                @foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Estimated Value</label>
              <input id="edit_opp_value" type="number" step="0.01" name="estimated_value" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>Close Expected</label>
              <input id="edit_opp_close" type="date" name="close_expected" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea id="edit_opp_description" name="description" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_opp_status" name="status" class="form-control select2_demo_3">
              <option value="Open">Open</option>
              <option value="Won">Won</option>
              <option value="Lost">Lost</option>
              <option value="Deleted">Deleted</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('oppEditForm')" class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var tempOppEdit = null;

  function initSelect2WithParent($el, parentSelector) {
    if (!$el || !$el.length) return;
    if ($el.data('select2')) { try { $el.select2('destroy'); } catch(e){} }
    var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
    $el.select2({ width:'100%', theme:'bootstrap4', dropdownParent: $parent });
  }

  $('.select2_demo_3').each(function(){
    var $this = $(this);
    if ($this.closest('#oppCreateModal').length) { initSelect2WithParent($this, '#oppCreateModal'); return; }
    if ($this.closest('#oppEditModal').length) { initSelect2WithParent($this, '#oppEditModal'); return; }
    initSelect2WithParent($this, null);
  });

  $(document).on('shown.bs.modal', '#oppCreateModal', function () {
    var form = $(this).find('form')[0]; if (form) form.reset();
    $(this).find('.select2_demo_3').each(function(){ initSelect2WithParent($(this), '#oppCreateModal'); $(this).val(null).trigger('change'); });
  });

  $(document).on('shown.bs.modal', '#oppEditModal', function () {
    $(this).find('.select2_demo_3').each(function(){ initSelect2WithParent($(this), '#oppEditModal'); });
    if (tempOppEdit) {
      $('#edit_opp_work_point_id').val(tempOppEdit.work_point_id || '').trigger('change');
      $('#edit_opp_cstm').val(tempOppEdit.cstm_id || '').trigger('change');
      $('#edit_opp_assigned').val(tempOppEdit.assigned_to || '').trigger('change');
      tempOppEdit = null;
    }
  });

  document.querySelectorAll('.btn-edit-opp').forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault && e.preventDefault();
      var enc = this.dataset.id;
      document.getElementById('edit_opp_id').value = enc || '';
      document.getElementById('edit_opp_name').value = this.dataset.opportunity_name || '';
      document.getElementById('edit_opp_value').value = this.dataset.estimated_value || '';
      document.getElementById('edit_opp_close').value = this.dataset.close_expected || '';
      document.getElementById('edit_opp_stage').value = this.dataset.stage || 'Prospecting';
      document.getElementById('edit_opp_description').value = this.dataset.description || '';
      document.getElementById('edit_opp_status').value = this.dataset.status || 'Open';
      tempOppEdit = {
        work_point_id: this.dataset.work_point_id || null,
        cstm_id: this.dataset.cstm_id || null,
        assigned_to: this.dataset.assigned_to || null
      };
      var form = document.getElementById('oppEditForm');
      form.action = "{{ route('sales.opportunities.update', ':id') }}".replace(':id', enc);
      $('#oppEditModal').modal('show');
    });
  });

  document.querySelectorAll('.btn-delete-opp').forEach(function(btn){
    btn.addEventListener('click', function () {
      var enc = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the opportunity as Deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
      }).then(function(res){
        if (res.isConfirmed) window.location.href = "{{ route('sales.opportunities.remove', ':id') }}".replace(':id', enc);
      });
    });
  });

});
</script>
@endsection
