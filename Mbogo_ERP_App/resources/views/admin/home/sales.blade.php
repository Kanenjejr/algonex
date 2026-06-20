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
@can('SalesMarketing-Modules')
<div class="col-12 mb-3">
    <h3 class="page-title">Sales & Marketing Dashboard</h3>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="ibox">
            <div class="ibox-title bg-info"><h5>Total Customers</h5></div>
            <div class="ibox-content"><strong style="font-size:30px">{{ $customersCount ?? 0 }}</strong></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ibox">
            <div class="ibox-title bg-success"><h5>Total Orders</h5></div>
            <div class="ibox-content"><strong style="font-size:30px">{{ $totalOrders ?? 0 }}</strong></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ibox">
            <div class="ibox-title bg-warning"><h5>Total Sales (All Time)</h5></div>
            <div class="ibox-content"><strong style="font-size:24px">{{ number_format($totalSalesAmount ?? 0,2) }}</strong></div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-4">
        <div class="ibox">
            <div class="ibox-title bg-success"><h5>Sales This Month</h5></div>
            <div class="ibox-content"><strong style="font-size:26px">{{ number_format($salesThisMonth ?? 0,2) }}</strong></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ibox">
            <div class="ibox-title bg-info"><h5>Sales This Year</h5></div>
            <div class="ibox-content"><strong style="font-size:26px">{{ number_format($salesThisYear ?? 0,2) }}</strong></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ibox">
            <div class="ibox-title bg-warning"><h5>Pending Activities</h5></div>
            <div class="ibox-content"><strong style="font-size:26px">{{ $activitiesPending ?? 0 }}</strong></div>
        </div>
    </div>
</div>

{{-- Recent Orders + Top Customers --}}
<div class="row mt-4">
    <div class="col-md-6">
        <div class="ibox">
            <div class="ibox-title bg-info"><h5>Recent Orders</h5></div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>#</th><th>Order No</th><th>Customer</th><th>Date</th><th>Total</th></tr></thead>
                        <tbody>
                        @if(isset($recentOrders) && $recentOrders->count())
                            @foreach($recentOrders as $k => $o)
                                <tr>
                                    <td>{{ $k+1 }}</td>
                                    <td>{{ $o->order_no ?? '-' }}</td>
                                    <td>{{ optional($o->customer)->customer_name ?? $o->customer_name ?? '-' }}</td>
                                    <td>{{ $o->order_date ?? '-' }}</td>
                                    <td>{{ number_format($o->total_amount ?? 0,2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center">No recent orders.</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="ibox">
            <div class="ibox-title bg-info"><h5>Top Customers (by sales)</h5></div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>#</th><th>Customer</th><th>Total</th></tr></thead>
                        <tbody>
                        @if(isset($topCustomers) && $topCustomers->count())
                            @foreach($topCustomers as $k => $t)
                                <tr>
                                    <td>{{ $k+1 }}</td>
                                    <td>{{ optional($t->customer)->customer_name ?? '-' }}</td>
                                    <td>{{ number_format($t->total,2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="3" class="text-center">No data.</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card p-3">
            <div class="d-flex justify-content-between"><h5 class="mb-3">Sales (last 6 months)</h5><small class="text-muted">Trend</small></div>
            <div style="height:260px"><canvas id="salesTrendChart"></canvas></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <div class="d-flex justify-content-between"><h5 class="mb-3">Sales by Product</h5><small class="text-muted">Top items</small></div>
            <div style="height:260px">
                <canvas id="salesProductChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($graphLabels ?? []);
    const salesData = @json($graphSalesData ?? []);
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{ label: 'Sales', data: salesData, fill: false, tension: 0.1 }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales:{ y: { beginAtZero: true } } }
    });

    // Sales by product
    const prodLabels = @json($salesByProduct->pluck('product_name')->map(fn($v)=> $v ?? 'Unnamed')->values() ?? []);
    const prodData = @json($salesByProduct->pluck('sold_qty')->map(fn($v)=> (float)$v)->values() ?? []);
    const ctx2 = document.getElementById('salesProductChart').getContext('2d');
    new Chart(ctx2, { type: 'bar', data: { labels: prodLabels, datasets:[{ label:'Qty', data: prodData }] }, options:{ responsive:true, maintainAspectRatio:false } });
</script>
@endcan
@endsection
