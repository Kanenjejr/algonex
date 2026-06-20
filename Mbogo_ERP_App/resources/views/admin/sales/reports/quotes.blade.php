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
                <strong>Reports</strong>
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

<div class="col-12"><h3>Quotes</h3></div>
<div class="ibox"><div class="ibox-title bg-primary"><h5>Quotes</h5></div>
  <div class="ibox-content">
    <table class="table table-sm table-bordered">
      <thead><tr><th>#</th><th>Quote No</th><th>Date</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
      @forelse($quotes as $k => $q)
        <tr><td>{{ $k+1 }}</td><td>{{ $q->quote_number ?? '-' }}</td><td>{{ $q->quote_date ?? '-' }}</td><td>{{ optional($q->customer)->customer_name ?? '-' }}</td><td>{{ number_format($q->total,2) }}</td><td>{{ $q->status }}</td></tr>
      @empty
        <tr><td colspan="6" class="text-center">No quotes</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
