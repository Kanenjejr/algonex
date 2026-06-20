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
<div class="col-12"><h3>Sales Reports</h3></div>
<div class="row mt-3">
  <div class="col-md-3">
    <div class="ibox"><div class="ibox-title bg-primary"><h5>Available Reports</h5></div>
      <div class="ibox-content">
        <ul class="list-unstyled">
          @can('View-Sales-Summary')<li><a href="{{ route('sales.reports.summary') }}">Sales Summary</a></li>@endcan
          @can('View-Sales-TopCustomers')<li><a href="{{ route('sales.reports.topcustomers') }}">Top Customers</a></li>@endcan
          @can('View-Sales-ByProduct')<li><a href="{{ route('sales.reports.byproduct') }}">Sales by Product</a></li>@endcan
          @can('View-Sales-Campaigns')<li><a href="{{ route('sales.reports.campaigns') }}">Campaign Performance</a></li>@endcan
          @can('View-Sales-Quotes')<li><a href="{{ route('sales.reports.quotes') }}">Quotes</a></li>@endcan
          @can('View-Sales-Activities')<li><a href="{{ route('sales.reports.activities') }}">Activities</a></li>@endcan
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-9">
    <div class="ibox"><div class="ibox-title bg-info"><h5>Report preview</h5></div>
      <div class="ibox-content">
        <p>Select a report on the left to view details.</p>
      </div>
    </div>
  </div>
</div>
@endsection
