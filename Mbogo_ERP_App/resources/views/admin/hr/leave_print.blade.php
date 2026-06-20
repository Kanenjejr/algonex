@extends('layouts.AdminMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Leaves Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('hr') }}">Human Resource</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Staff/User Leaves</strong>
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
  <h3 class="mb-2 page-title">Staff Leave Approval</h3>

    <div class="col-4 text-right no-print">
        <button onclick="printReceipt('form1')"class="btn btn-sm btn-primary float-right mr-2"><i class="fa fa-print"></i> Print Report</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
    </div>
</div>
<div id='form1' class="wrapper wrapper-content animated fadeInRight">
  <div class="row mb-3">
    <div class="col-12">
      <h3>Leave Approval Slip</h3>
      <p><strong>Company:</strong> {{ optional($leave->company)->company_name ?? '-' }}</p>
      <p><strong>Work Point:</strong> {{ optional($leave->workpoint)->work_name ?? '-' }}</p>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Leave Details</h5>
      <table class="table table-borderless">
        <tr><th>Staff</th><td>{{ optional($leave->user)->name ?? '-' }}</td></tr>
        <tr><th>Leave Type</th><td>{{ $leave->leave_type }}</td></tr>
        <tr><th>Start Date</th><td>{{ $leave->start_date }}</td></tr>
        <tr><th>End Date</th><td>{{ $leave->end_date }}</td></tr>
        <tr><th>Number of days</th><td>
          @php
            $start = \Carbon\Carbon::parse($leave->start_date);
            $end = \Carbon\Carbon::parse($leave->end_date);
            $days = $end->diffInDays($start) + 1;
          @endphp
          {{ $days }}
        </td></tr>
        <tr><th>Reason</th><td style="white-space:pre-line">{{ $leave->reason ?? '-' }}</td></tr>
        <tr><th>Requested by</th><td>{{ optional($leave->user)->name ?? '-' }}</td></tr>
        <tr><th>Approved by</th><td>{{ optional($leave->approver)->name ?? '-' }}</td></tr>
        <tr><th>Approved at</th><td>{{ $leave->approved_at ? $leave->approved_at->format('Y-m-d H:i') : '-' }}</td></tr>
      </table>
    </div>
  </div>

  <div class="mt-4">
    <p>This leave has been approved and can be presented to the staff as proof of leave approval.</p>
  </div>
</div>
@endsection

