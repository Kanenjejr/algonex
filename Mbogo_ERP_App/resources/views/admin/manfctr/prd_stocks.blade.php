@extends('layouts.ManftrMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Inventory And Manufacturing Dashboard</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Product Stock</strong>
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
<div class="col-12"><h3 class="mb-2 page-title">Product Stocks / Validity</h3></div>

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12"><div class="ibox">
    <div class="ibox-title bg-success"><h5>Product Stocks</h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-striped table-bordered dataTables-example">
          <thead>
            <tr><th>#</th><th>Product</th><th>Unit</th><th>Available Qty</th><th>Issued Qty</th><th>Work Point</th></tr>
          </thead>
          <tbody>
            @foreach($stocks as $k => $s)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ optional($s->product)->product_name ?? '-' }}</td>
              <td>{{ $s->stck_unit }}</td>
              <td>{{ number_format($s->avlb_qnty,2) }}</td>
              <td>{{ number_format($s->issd_qnty,2) }}</td>
              <td>{{ optional($s->workpoint)->work_name ?? '-' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div></div></div>
</div>
@endsection
