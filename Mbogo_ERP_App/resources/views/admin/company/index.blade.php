@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Company Sites Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('business-admin') }}">Business Administration</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Company Sites Registration</strong>
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
  <h3 class="mb-2 page-title">Company Sites Information</h3>
  @can('Register-Company-Site')
    <button style="position: absolute; top: 4.5%; right: 1.7%;;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#varyModal" data-whatever="@mdo">Add Company</button>
  @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title bg-success">
          <h5>Company Sites Details Table.</h5>
           <div class="ibox-tools">
                <a class="collapse-link">
                    <i class="fa fa-chevron-up"></i>
                </a>
                <a class="close-link">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </div>
        <div class="ibox-content">
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover dataTables-example">
            <thead>
              <tr>
                <th>#</th>
                <th>Company Code</th>
                <th>Company Name</th>
                <th>City</th>
                <th>District</th>
                <th>Type</th>
                <th>TIN</th>
                <th>Phone</th>
                <th>Logo</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($companies as $key => $c)
                <tr>
                  <td>{{ $key + 1 }}</td>
                  <td>{{ $c->company_code }}</td>
                  <td>{{ $c->company_name }}</td>
                  <td>{{ $c->city }}</td>
                  <td>{{ $c->district }}</td>
                  <td>{{ $c->Type }}</td>
                  <td>{{ $c->TIN }}</td>
                  <td>{{ $c->phone_No }}</td>
                  <td><img  style="height: 2cm; width:2cm;" alt="image" src="assets/{{$c->logo}}"/></td>
                  <td>{{ $c->status }}</td>
                  <td>
                    @can('Edit-Company-Site')
                      <button
                        class="btn btn-sm btn-warning btn-edit-company"
                        data-toggle="modal"
                        data-target="#companyEditModal"
                        data-id="{{ encrypt($c->id) }}"
                        data-company_code="{{ $c->company_code }}"
                        data-company_name="{{ $c->company_name }}"
                        data-type="{{ $c->Type }}"
                        data-phone_No="{{ $c->phone_No }}"
                        data-status="{{ $c->status }}"
                        data-TIN="{{ $c->TIN }}"
                        data-city="{{ $c->city }}"
                        data-district="{{ $c->district }}"
                      >Edit</button>
                    @endcan
                    @can('Delete-Company-Site')
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-company" data-id="{{ encrypt($c->id) }}">Remove</a>
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

<!-- Create Modal -->
<div class="modal fade" id="varyModal" tabindex="-1" role="dialog" aria-labelledby="varyModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="reg" action="{{ route('company.store') }}" method="POST"enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Company Site</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group col"><label>Company Code <span style="color: red">*</span></label><input type="text" name="company_code" class="form-control" required></div>
                <div class="form-group col"><label>Company Logo</label><input type="file" name="logo" class="form-control"></div>
            </div>
            <div class="form-row">
                <div class="form-group col"><label>Company Name <span style="color: red">*</span></label><input type="text" name="company_name" class="form-control" required></div>
                <div class="form-group col"><label>Type <span style="color: red">*</span></label><input type="text" name="Type" class="form-control" required></div>
            </div>
            <div class="form-row">
                <div class="form-group col"><label>TIN<span style="color: red">*</span></label><input type="text" name="TIN" class="form-control" required></div>
                <div class="form-group col"><label>Phone No<span style="color: red">*</span></label><input type="text" name="phone_No" class="form-control" required></div>
            </div>
            <div class="form-row">
                <div class="form-group col"><label>City</label><input type="text" name="city" class="form-control"></div>
                <div class="form-group col"><label>District</label><input type="text" name="district" class="form-control"></div>
            </div>
          <div class="form-group"><label>Status</label>
            <select name="status" class="form-control select2_demo_3">
              <option value="Active" selected>Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('reg')"  class="btn mb-2 btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal (populated via JS) -->
<div class="modal fade" id="companyEditModal" tabindex="-1" role="dialog" aria-labelledby="companyEditModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="companyEditForm" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Company Site</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="company_edit_id">

          <!-- New: Company Code (mirrors Add modal) -->
          <div class="form-row">
              <div class="form-group col"><label>Company Code <span style="color: red">*</span></label>
                <input id="company_company_code" type="text" name="company_code" class="form-control" required>
              </div>
              <div class="form-group col"><label>Company Logo</label><input type="file" name="logo" class="form-control"></div>
          </div>
          <div class="form-row">
            <div class="form-group col"><label>Company Name <span style="color: red">*</span></label>
              <input id="company_company_name" type="text" name="company_name" class="form-control" required>
            </div>
            <div class="form-group col"><label>Type <span style="color: red">*</span></label>
              <input id="company_type" type="text" name="Type" class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <!-- New: TIN field (mirrors Add modal) -->
            <div class="form-group col"><label>TIN<span style="color: red">*</span></label>
              <input id="company_TIN" type="text" name="TIN" class="form-control" required>
            </div>

            <div class="form-group col"><label>Phone No <span style="color: red">*</span></label>
              <input id="company_phone" type="text" name="phone_No" class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <!-- New: City & District (mirrors Add modal) -->
            <div class="form-group col"><label>City</label>
              <input id="company_city" type="text" name="city" class="form-control">
            </div>
            <div class="form-group col"><label>District</label>
              <input id="company_district" type="text" name="district" class="form-control">
            </div>
          </div>
          <div class="form-group"><label>Status</label>
            <select id="company_status" name="status" class="form-control select2_demo_3">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" onclick="handleConfirmSubmit('companyEditForm')"  class="btn mb-2 btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Helper to find the actual button element (in case an inner element was clicked)
  function findDeleteButton(el) {
    return el.closest ? el.closest('.btn-delete-company') : (el.classList && el.classList.contains('btn-delete-company') ? el : null);
  }
  // Central delegated click handler (works for dynamic content too)
  document.addEventListener('click', function (event) {
    const btn = findDeleteButton(event.target);
    if (!btn) return; // ignore other clicks
    event.preventDefault();
    try {
      const rawEncId = btn.getAttribute('data-id');
      console.log('[Delete] clicked, rawEncId:', rawEncId);

      if (!rawEncId) {
        console.error('[Delete] No data-id found on button.');
        alert('Internal error: missing identifier.');
        return;
      }
      const encodedId = encodeURIComponent(rawEncId);
      const removeUrl = "{{ url('/admin/company-sites/remove') }}/" + encodedId;
      console.log('[Delete] encoded id:', encodedId, 'removeUrl:', removeUrl);
      // confirmation flow: prefer Swal if available
      const confirmAndSubmit = function () {
        // Try to submit via a form (GET) to avoid issues with special chars in URL navigation
        try {
          const form = document.createElement('form');
          form.method = 'GET';
          form.action = removeUrl;
          form.style.display = 'none';
          document.body.appendChild(form);

          // small timeout so console logs flush before navigation
          setTimeout(() => {
            console.log('[Delete] Submitting hidden GET form to:', form.action);
            form.submit();
          }, 80);
        } catch (err) {
          console.error('[Delete] form submit failed, falling back to window.location:', err);
          try {
            window.location.assign(removeUrl);
          } catch (err2) {
            console.error('[Delete] window.location failed too:', err2);
            alert('Unable to request delete. See console for details.');
          }
        }
      };
      // Use Swal if present and is a function
      if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
        Swal.fire({
          title: 'Are you sure?',
          text: "This will remove the company site.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, remove it!',
          cancelButtonText: 'Cancel',
        }).then(function (result) {
          if (result && result.isConfirmed) {
            confirmAndSubmit();
          } else {
            console.log('[Delete] cancelled by user (Swal).');
          }
        }).catch(function(err){
          console.error('[Delete] Swal promise error:', err);
          // fallback to native confirm
          if (confirm('Are you sure you want to remove the company site?')) {
            confirmAndSubmit();
          }
        });
      } else {
        // fallback to native confirm
        console.warn('[Delete] Swal not found, using window.confirm fallback.');
        if (confirm('Are you sure you want to remove the company site?')) {
          confirmAndSubmit();
        } else {
          console.log('[Delete] cancelled by user (confirm).');
        }
      }
    } catch (ex) {
      console.error('[Delete] Unexpected error:', ex);
      alert('Unexpected error. Check browser console.');
    }
  }, false);

  // --- EDIT handler: populate added fields as well and set form action robustly ---
  document.querySelectorAll('.btn-edit-company').forEach(btn => {
    btn.addEventListener('click', function () {
      try {
        // attributes from the button
        const encId = btn.getAttribute('data-id') || '';
        const companyCode = btn.getAttribute('data-company_code') || '';
        const cname = btn.getAttribute('data-company_name') || '';
        const type = btn.getAttribute('data-type') || '';
        const phone = btn.getAttribute('data-phone_No') || '';
        const status = btn.getAttribute('data-status') || 'Active';
        const tin = btn.getAttribute('data-TIN') || '';
        const city = btn.getAttribute('data-city') || '';
        const district = btn.getAttribute('data-district') || '';

        // Set inputs in modal
        document.getElementById('company_edit_id').value = encId;
        document.getElementById('company_company_code').value = companyCode;
        document.getElementById('company_company_name').value = cname;
        document.getElementById('company_type').value = type;
        document.getElementById('company_phone').value = phone;
        document.getElementById('company_status').value = status;
        document.getElementById('company_TIN').value = tin;
        document.getElementById('company_city').value = city;
        document.getElementById('company_district').value = district;

        // Set form action (same pattern as before)
        const encodedId = encodeURIComponent(encId);
        const baseUrl = "{{ url('/admin/company-sites') }}";
        const form = document.getElementById('companyEditForm');
        form.action = baseUrl + '/' + encodedId;
        console.log('[Edit] form action set to:', form.action);
      } catch (err) {
        console.error('[Edit] error populating modal:', err);
      }
    });
  });
});
</script>
@endsection
