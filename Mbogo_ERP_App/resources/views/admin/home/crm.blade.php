@extends('layouts.CrmMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Customers, Supplies & Interactions Dashboard</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('crm') }}">Customers, Supplies & Interactions</a>
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
@can('CRM-Modules')
<div class="col-12">
    <h3 class="mb-2 page-title">Customer & Supplier Dashboard</h3>
</div>
<div class="row">
    {{-- Customers Summary --}}
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Total Customers</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($customers->count(),0) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Customer Debit</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($totalDebitCustomer,2) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Customer Credit</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($totalCreditCustomer,2) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Customer Balance</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($totalCreditCustomer - $totalDebitCustomer,2) }}</strong>
            </div>
        </div>
    </div>

    {{-- Suppliers Summary --}}
    <div class="col-lg-3 mt-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Total Suppliers</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($suppliers->count(),0) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mt-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Supplier Debit</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($totalDebitSupplier,2) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mt-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Supplier Credit</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($totalCreditSupplier,2) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mt-3">
        <div class="ibox">
            <div class="ibox-title"><h5>Supplier Balance</h5></div>
            <div class="ibox-content">
                <strong style="font-size: 30px">{{ number_format($totalCreditSupplier - $totalDebitSupplier,2) }}</strong>
            </div>
        </div>
    </div>
</div>
{{-- Chart --}}
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Customers vs Suppliers</h5>
                <div>
                    <label class="mr-2">Chart type:</label>
                    <select id="chartTypeSelect" class="form-control d-inline-block" style="width:140px">
                        <option value="bar" selected>Bar</option>
                        <option value="line">Line</option>
                        <option value="pie">Pie</option>
                    </select>
                </div>
            </div>
            <canvas id="customerSupplierChart" style="height:500px;"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartLabels = ['Customers','Suppliers'];
    const chartData   = [{{ $customers->count() }}, {{ $suppliers->count() }}];
    let chartInstance = null;

    function createChart(type='bar') {
        const ctx = document.getElementById('customerSupplierChart').getContext('2d');
        if(chartInstance) chartInstance.destroy();

        const datasets = [{
            label: 'Count',
            data: chartData,
            backgroundColor: ['#1ab394','#f8ac59'],
            borderColor: ['#1ab394','#f8ac59'],
            fill: type==='line',
            tension: 0.3
        }];

        const config = {
            type: type,
            data: { labels: chartLabels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: type==='pie'?true:true },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label||'';
                                const value = context.formattedValue??context.raw;
                                return label + ': ' + value;
                            }
                        }
                    }
                },
                scales: type==='pie'?{}:{
                    x: { ticks:{ autoSkip:true, maxRotation:45, minRotation:0 } },
                    y: { beginAtZero:true, ticks:{ precision:0 } }
                },
                layout: { padding:8 }
            }
        };

        chartInstance = new Chart(ctx, config);
    }

    createChart('bar');
    document.getElementById('chartTypeSelect').addEventListener('change', function(e){
        createChart(e.target.value);
    });
</script>
@endcan
@endsection
