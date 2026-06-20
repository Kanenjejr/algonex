@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-9">
    <h2>Microfinancing Transactions Information</h2>
    <ol class="breadcrumb" style="font-size:17px;color:#000">
      <li><a href="{{ route('microfinancing') }}">Microfinancing</a></li>
      <span style="font-size:25px" class="fa fa-angle-double-right "></span>
      <li class="breadcrumb-item active"><strong>Daily Summary Report</strong></li>
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
  <h3>Daily Summary for {{ $date }}</h3>
  <form method="GET" action="{{ route('micro.reports.daily') }}" class="mb-3 no-print">
    <div class="form-row">
      <div class="form-group col"><input type="date" name="date" class="form-control" value="{{ $date }}"></div>
      @if($workPoints->count())
      <div class="form-group col">
        <select name="work_point_id" class="form-control select2_modal">
          <option value="">-- All WorkPoints --</option>
          @foreach($workPoints as $wp)
            <option value="{{ $wp->id }}" {{ request('work_point_id') == $wp->id ? 'selected' : '' }}>{{ $wp->work_name }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="form-group col"><button class="btn btn-primary">Filter</button> <a href="{{ route('micro.reports.daily') }}" class="btn btn-secondary">Reset</a></div>
    </div>
  </form>
</div>
</div>
<div id="form1" class="wrapper wrapper-content animated fadeInRight" id="reportContent">
    <div class="ibox-content">
    <div class="table-responsive">
  <h5>By Transaction Group</h5>
  <div class="table-responsive">
  <table class="table table-sm table-bordered">
    <thead><tr><th>Group</th><th>Count</th><th>Total</th></tr></thead>
    <tbody>
      @foreach($byGroup as $g)
        <tr><td>{{ $g->tx_group }}</td><td>{{ $g->tx_count }}</td><td>{{ number_format($g->total_amount,2) }}</td></tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <h5 class="mt-4">By Bank/Network</h5>
  <div class="table-responsive">
  <table class="table table-sm table-bordered">
    <thead><tr><th>BN</th><th>Count</th><th>Total</th><th class="no-print">Action</th></tr></thead>
    <tbody>
      @foreach($byBN as $b)
        @php $bnModel = optional($b->bankNetwork); @endphp
        <tr>
          <td>
            <strong>{{ $bnModel->type ?? '' }} - {{ $bnModel->name ?? 'N/A' }}</strong>
          </td>
          <td>{{ $b->tx_count }}</td>
          <td>{{ number_format($b->total_amount,2) }}</td>
          <td class="no-print">
            <a class="btn btn-sm btn-info" href="{{ route('micro.reports.bn.detail', ['bank_network_id' => encrypt($b->bank_network_id), 'from' => $date, 'to' => $date, 'work_point_id' => request('work_point_id')]) }}">
              View Details
            </a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <h5 class="mt-3">Totals</h5>
  <p>Transactions: {{ optional($totalAll)->tx_count ?? 0 }} | Amount: {{ number_format(optional($totalAll)->total_amount ?? 0,2) }}</p>
  <h5>Commissions</h5>
  <p>Total commission: {{ number_format(optional($commTotal)->total_commission ?? 0,2) }} ({{ optional($commTotal)->comm_count ?? 0 }} items)</p>
</div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (jQuery && jQuery().select2) $('.select2_modal').select2({ width:'100%', theme:'bootstrap4' });
});
</script>
@endsection
