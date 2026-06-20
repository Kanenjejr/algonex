@extends('layouts.MicroMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Microfinancing Dashboard</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('microfinancing') }}">Microfinancing</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Dashboard</strong>
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
{{-- Filters + title --}}
<div class="col-12 mt-3 mb-2">
  <h3 class="page-title">Microfinance Overview</h3>
</div>

<form method="GET" action="{{ route('microfinancing') }}">
  <div class="row mb-3">
    <div class="col-md-3">
      <input type="date" name="date" class="form-control" value="{{ $date }}">
    </div>
    @if(isset($workPoints) && $workPoints->count())
      <div class="col-md-3">
        <select name="work_point_id" class="form-control select2_modal">
          <option value="">-- All Work Points --</option>
          @foreach($workPoints as $wp)
            <option value="{{ $wp->id }}" {{ (isset($workPointId) && $workPointId == $wp->id) ? 'selected' : '' }}>{{ $wp->work_name }}</option>
          @endforeach
        </select>
      </div>
    @endif
    <div class="col-md-2">
      <button class="btn btn-primary">Filter</button>
      <a href="{{ route('microfinancing') }}" class="btn btn-secondary">Reset</a>
    </div>
  </div>
</form>
{{-- Summary cards --}}
<div class="row">
  <div class="col-lg-3">
    <div class="ibox">
      <div class="ibox-title"><h5>Total Bank/Networks</h5></div>
      <div class="ibox-content">
        <strong style="font-size:28px">{{ number_format($bankNetworks->count(),0) }}</strong>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="ibox">
      <div class="ibox-title"><h5>Total Transactions ({{ $date }})</h5></div>
      <div class="ibox-content">
        <strong style="font-size:28px">{{ number_format($totalCount ?? 0,0) }}</strong>
        <div>Amount: <strong>{{ number_format($totalAmount ?? 0,2) }}</strong></div>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="ibox">
      <div class="ibox-title"><h5>Total Commission ({{ $date }})</h5></div>
      <div class="ibox-content">
        <strong style="font-size:28px">{{ number_format(optional($commTotal)->total_commission ?? 0,2) }}</strong>
        <div>Count: <strong>{{ optional($commTotal)->comm_count ?? 0 }}</strong></div>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="ibox">
      <div class="ibox-title"><h5>Average per Tx</h5></div>
      <div class="ibox-content">
        <strong style="font-size:28px">
          {{ number_format(($totalCount>0 ? $totalAmount / $totalCount : 0), 2) }}
        </strong>
        <div>Currency mixed — check details below</div>
      </div>
    </div>
  </div>
</div>

{{-- Charts --}}
<div class="row mt-4">
  <div class="col-md-6">
    <div class="card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Transactions by Group ({{ $date }})</h5>
        <div>
          <label class="mr-2">Chart type:</label>
          <select id="groupChartType" class="form-control d-inline-block" style="width:120px">
            <option value="bar" selected>Bar</option>
            <option value="line">Line</option>
            <option value="pie">Pie</option>
          </select>
        </div>
      </div>
      <canvas id="txGroupChart" style="height:350px"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Top Bank/Network by Volume ({{ $date }})</h5>
        <div>
          <label class="mr-2">Chart type:</label>
          <select id="bnChartType" class="form-control d-inline-block" style="width:120px">
            <option value="bar" selected>Bar</option>
            <option value="line">Line</option>
            <option value="pie">Pie</option>
          </select>
        </div>
      </div>
      <canvas id="bnChart" style="height:350px"></canvas>
    </div>
  </div>
</div>

{{-- Bank/Network breakdown table --}}
<div class="row mt-4">
  <div class="col-md-12">
    <div class="ibox">
      <div class="ibox-title"><h5>Bank / Network Breakdown ({{ $date }})</h5></div>
      <div class="ibox-content">
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead><tr><th>#</th><th>Type</th><th>Name</th><th>Work Point</th><th>Tx Count</th><th>Total Amount</th><th>Actions</th></tr></thead>
            <tbody>
              @forelse($bnMap as $k => $bn)
                <tr>
                  <td>{{ $k+1 }}</td>
                  <td>{{ $bn['type'] ?? '-' }}</td>
                  <td>{{ $bn['name'] ?? '-' }}</td>
                  <td>
                    @php
                      $wp = \App\Models\WorkPoint::find(optional(\App\Models\BankNetwork::find($bn['id']))->work_point_id);
                    @endphp
                    {{ optional($wp)->work_name ?? '-' }}
                  </td>
                  <td>{{ number_format($bn['tx_count'],0) }}</td>
                  <td>{{ number_format($bn['total_amount'],2) }}</td>
                  <td>
                    <a href="{{ route('micro.reports.bn', ['bank_network_id' => $bn['id'], 'from'=>$date, 'to'=>$date ]) }}" class="btn btn-sm btn-primary">View Report</a>
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center">No transactions for selected date.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const groupLabels = {!! json_encode($groupLabels ?? ['Deposit','Withdraw','FX-Sell','FX-Buy']) !!};
  const groupData   = {!! json_encode($groupData ?? [0,0,0,0]) !!};
  const bnLabels = {!! json_encode($bnLabels ?? []) !!};
  const bnData   = {!! json_encode($bnData ?? []) !!};
  let groupChart = null;
  let bnChart = null;
  function createGroupChart(type='bar') {
    const ctx = document.getElementById('txGroupChart').getContext('2d');
    if (groupChart) groupChart.destroy();
    const datasets = [{ label: 'Amount', data: groupData, fill: type==='line' }];
    groupChart = new Chart(ctx, {
      type: type,
      data: { labels: groupLabels, datasets: datasets },
      options: {
        responsive:true,
        plugins:{ legend:{ display: type==='pie' }, tooltip:{ callbacks:{ label: ctx => (ctx.formattedValue || ctx.raw) } } },
        scales: type==='pie'?{}:{ y: { beginAtZero:true } }
      }
    });
  }
  function createBnChart(type='bar') {
    const ctx = document.getElementById('bnChart').getContext('2d');
    if (bnChart) bnChart.destroy();
    const datasets = [{ label: 'Amount', data: bnData, fill: type==='line' }];
    bnChart = new Chart(ctx, {
      type: type,
      data: { labels: bnLabels, datasets: datasets },
      options: {
        responsive:true,
        plugins:{ legend:{ display: type==='pie' }, tooltip:{ callbacks:{ label: ctx => (ctx.formattedValue || ctx.raw) } } },
        scales: type==='pie'?{}:{ y: { beginAtZero:true } }
      }
    });
  }
  // init default charts
  createGroupChart('bar');
  createBnChart('bar');
  document.getElementById('groupChartType').addEventListener('change', function(e){
    createGroupChart(e.target.value);
  });
  document.getElementById('bnChartType').addEventListener('change', function(e){
    createBnChart(e.target.value);
  });
</script>
{{-- init select2 for filter --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (jQuery && jQuery().select2) {
    $('.select2_modal').select2({ width:'100%', theme:'bootstrap4' });
  }
});
</script>
@endsection
