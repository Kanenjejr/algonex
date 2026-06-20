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
                <strong>Product Stock Movement</strong>
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
  <h3 class="mb-2 page-title">Product Stock Movement ({{ $start }} → {{ $end }})</h3>
  <button onclick="printReceipt('form1')"class="btn btn-sm btn-primary float-right mr-2"><i class="fa fa-print"></i> Print Report</button>
</div>

<div class="mb-3">
  <form method="GET" action="{{ route('manfctr.prdstockmovement.index') }}" class="form-inline">
    <div class="form-group mr-3">
      <label class="mr-1">Start Date</label>
      <input type="date" name="start_date" value="{{ $start }}" class="form-control">
    </div>
    <div class="form-group mr-3">
      <label class="mr-1">End Date</label>
      <input type="date" name="end_date" value="{{ $end }}" class="form-control">
    </div>
    <div class="form-group mr-3">
      <label class="mr-1">Product Name</label>
      <select name="prd_id" class="form-control select2_demo_2">
        <option value="">-- All --</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}" @if($filterProduct == $p->id) selected @endif>{{ $p->product_name }}</option>
        @endforeach
      </select>
    </div>

    @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
      <div class="form-group mr-3">
        <label class="mr-1">Work Point</label>
        <select name="work_point_id" class="form-control select2_demo_2">
          <option value="">-- All --</option>
          @foreach($workPoints as $wp)
            <option value="{{ $wp->id }}" @if($filterWorkPoint == $wp->id) selected @endif>{{ $wp->work_name }}</option>
          @endforeach
        </select>
      </div>
    @endif

    <button class="btn btn-primary">Filter</button>
  </form>
</div>

<div id='form1' class="wrapper wrapper-content animated fadeInRight">
  <div class="row"><div class="col-lg-12">
    <div class="ibox">
      <div class="ibox-title bg-info"><h5>Product Stock Movement</h5></div>
      <div class="ibox-content">
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Product Name</th>
                <th>Unit</th>
                <th>Opening</th>
                <th>Packed (In)</th>
                <th>Issued (Out)</th>
                <th>Closing</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rows as $k => $r)
                <tr>
                  <td>{{ $k+1 }}</td>
                  <td>{{ $r['date'] }}</td>
                  <td>{{ optional($r['product'])->product_name ?? '-' }}</td>
                  <td>{{ $r['unit'] ?? optional($r['product'])->product_size ?? '-' }}</td>
                  <td>{{ number_format($r['opening'], 4) }}</td>
                  <td>{{ number_format($r['packed'], 4) }}</td>
                  <td>{{ number_format($r['issued'], 4) }}</td>
                  <td>{{ number_format($r['closing'], 4) }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="font-weight-bold">
                <td colspan="4" style="text-align:right">Totals:</td>
                <td>{{ number_format($totals['opening'] ?? 0, 4) }}</td>
                <td>{{ number_format($totals['packed'] ?? 0, 4) }}</td>
                <td>{{ number_format($totals['issued'] ?? 0, 4) }}</td>
                <td>{{ number_format($totals['closing'] ?? 0, 4) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div></div>
</div>
@endsection
