@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-9">
    <h2>Microfinancing Transactions Information</h2>
    <ol class="breadcrumb" style="font-size:17px;color:#000">
      <li><a href="{{ route('microfinancing') }}">Microfinancing</a></li>
      <span style="font-size:25px" class="fa fa-angle-double-right "></span>
      <li class="breadcrumb-item active"><strong>BN Summary Report</strong></li>
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
 <button onclick="printReceipt('form1')"class="btn btn-sm btn-primary float-right mr-2"><i class="fa fa-print"></i> Print Report</button>
</div>
  <div class="row mb-3">
    <div class="col-md-12">
    <div class="col-md-8">
      <h3>Report for {{ $bn->type }} - {{ $bn->name }}</h3>
      <p>From: <strong>{{ $from }}</strong> To: <strong>{{ $to }}</strong></p>
    </div>
    <div class="col-md-4 no-print">
      <form method="GET" action="{{ route('micro.reports.bn') }}" class="form-inline justify-content-end">
        <input type="hidden" name="bank_network_id" value="{{ encrypt($bn->id) }}">
        <div class="form-group mr-1">
          <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
        </div>
        <div class="form-group mr-1">
          <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
        </div>
        @if(isset($workPoints) && $workPoints->count())
          <div class="form-group mr-1">
            <select name="work_point_id" class="form-control form-control-sm select2_modal" style="min-width:140px">
              <option value="">-- All WorkPoints --</option>
              @foreach($workPoints as $wp)
                <option value="{{ $wp->id }}" {{ request('work_point_id') == $wp->id ? 'selected' : '' }}>{{ $wp->work_name }}</option>
              @endforeach
            </select>
          </div>
        @endif
        <button class="btn btn-sm btn-secondary">Apply</button>
      </form>
    </div>
  </div>
</div>
</div>
<div id="form1" class="wrapper wrapper-content animated fadeInRight" id="reportContent">
    <div class="ibox-content">
    <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th>Group</th>
          <th>Count</th>
          <th>Total</th>
          <th class="no-print">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($byGroup as $g)
          <tr>
            <td>{{ $g->tx_group }}</td>
            <td>{{ $g->tx_count }}</td>
            <td>{{ number_format($g->total_amount,2) }}</td>
            <td class="no-print">
              {{-- View detailed BN report for this bank/network --}}
              <a class="btn btn-sm btn-info"
                 href="{{ route('micro.reports.bn.detail', [
                    'bank_network_id' => encrypt($bn->id),
                    'from' => $from,
                    'to' => $to,
                    'work_point_id' => request('work_point_id') ?? ''
                 ]) }}">
                View Detailed
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mt-3">
    <h5>Totals</h5>
    <p>Transactions: <strong>{{ optional($total)->tx_count ?? 0 }}</strong> | Amount: <strong>{{ number_format(optional($total)->total_amount ?? 0,2) }}</strong></p>
  </div>
</div>
</div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (jQuery && jQuery().select2) {
    $('.select2_modal').select2({ width:'100%', theme:'bootstrap4' });
  }
});
</script>
@endsection
