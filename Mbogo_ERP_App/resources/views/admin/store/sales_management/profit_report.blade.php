@extends('layouts.salesMaster')

@section('content')

<div class="container">
<form method="GET" class="row mb-4">

    <div class="col-md-3">
        <label>From Date</label>
        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label>To Date</label>
        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label>Company</label>
        <select name="company_id" class="form-control">
            <option value="">All</option>
            @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ request('company_id')==$c->id?'selected':'' }}>
                {{ $c->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label>Location</label>
        <select name="work_point_id" class="form-control">
            <option value="">All</option>
            @foreach($workPoints as $w)
            <option value="{{ $w->id }}" {{ request('work_point_id')==$w->id?'selected':'' }}>
                {{ $w->work_name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-12 mt-3">
        <button class="btn btn-primary">Filter</button>
    </div>

</form>
    {{--  ACTIONS --}}
    <div class="mb-3">
        <a href="{{ route('sales.profit.export') }}" class="btn btn-success">
            ⬇ Download Report
        </a>

        <button onclick="window.print()" class="btn btn-primary">
            🖨 Print Report
        </button>
    </div>

    <h2> ERP Profit & Sales Dashboard</h2>

    {{--  SUMMARY --}}
    <div class="row mb-4">

        <div class="col-md-6">
            <h4>Profit Per Day</h4>
            <ul>
                @foreach($daily as $day => $value)
                    <li>{{ $day }} : {{ number_format($value,2) }}</li>
                @endforeach
            </ul>
        </div>

        <div class="col-md-6">
            <h4>Profit Per Month</h4>
            <ul>
                @foreach($monthly as $month => $value)
                    <li>{{ $month }} : {{ number_format($value,2) }}</li>
                @endforeach
            </ul>
        </div>

    </div>

    {{-- SALES VS PAYMENTS --}}
    <div class="ibox mb-4">
        <div class="ibox-title bg-warning"><h5>Sales vs Payments</h5></div>
        <div class="ibox-content text-center">
            <h4>Total Sales: {{ number_format($salesVsPayments['sales'] ?? 0,2) }}</h4>
            <h4>Payments: {{ number_format($salesVsPayments['payments'] ?? 0,2) }}</h4>
            <h4 style="color:red">
                Balance: {{ number_format(($salesVsPayments['sales'] ?? 0) - ($salesVsPayments['payments'] ?? 0),2) }}
            </h4>
        </div>
    </div>

    {{-- SALES BY COMPANY --}}
    <div class="ibox mb-4">
        <div class="ibox-title bg-info"><h5>Sales by Company</h5></div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesByCompany ?? [] as $s)
                    <tr>
                        <td>{{ $s->company->name ?? '-' }}</td>
                        <td>{{ number_format($s->total_sales,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- SALES BY LOCATION --}}
    <div class="ibox mb-4">
        <div class="ibox-title bg-primary"><h5>Sales by Location</h5></div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Company</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesByLocation ?? [] as $s)
                    <tr>
                        <td>{{ $s->workPoint->work_name ?? '-' }}</td>
                        <td>{{ $s->company->name ?? '-' }}</td>
                        <td>{{ number_format($s->total_sales,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- CAMPAIGN REPORT --}}
    <div class="ibox mb-4">
        <div class="ibox-title bg-success"><h5>Campaign ROI Report</h5></div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Revenue</th>
                        <th>Discount</th>
                        <th>Profit</th>
                        <th>ROI (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaignReport ?? [] as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ number_format($c->revenue_generated,2) }}</td>
                        <td>{{ number_format($c->discount_given,2) }}</td>
                        <td>{{ number_format($c->profit,2) }}</td>
                        <td>
                            {{ $c->budget > 0 ? number_format(($c->profit/$c->budget)*100,2) : 0 }} %
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{--  PRODUCT PERFORMANCE --}}
    <div class="ibox mb-4">
        <div class="ibox-title bg-danger"><h5>Product Performance</h5></div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty Sold</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productPerformance ?? [] as $p)
                    <tr>
                        <td>{{ $p->product->product_name ?? '-' }}</td>
                        <td>{{ $p->total_qty }}</td>
                        <td>{{ number_format($p->total_sales,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{--  ORIGINAL TOP PRODUCTS --}}
    <div class="mb-4">
        <h4> Top Selling Products</h4>
        <ul>
            @foreach($topProducts as $name => $qty)
                <li>{{ $name }} - {{ $qty }} sold</li>
            @endforeach
        </ul>
    </div>

    {{--  CHART --}}
    <div class="mb-5">
        <canvas id="profitChart"></canvas>
    </div>

    {{--  FILTER --}}
    <form method="GET" class="mb-4">
        <label>Select Company:</label>
        <select name="company_id" onchange="this.form.submit()" class="form-control w-25">
            <option value="">All</option>
            @foreach(\App\Models\Company::all() as $company)
                <option value="{{ $company->id }}"
                    {{ request('company_id') == $company->id ? 'selected' : '' }}>
                    {{ $company->name }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- DETAILED TABLE --}}
    <h4>Detailed Profit</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Revenue</th>
                <th>COGS</th>
                <th>Profit</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['invoice_no'] }}</td>
                <td>{{ $row['product'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ number_format($row['revenue'],2) }}</td>
                <td>{{ number_format($row['cogs'],2) }}</td>
                <td style="color: {{ $row['profit'] >= 0 ? 'green' : 'red' }}">
                    {{ number_format($row['profit'],2) }}
                </td>
                <td>{{ $row['date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{--  CHARTS SECTION --}}
<div class="row">

    <div class="col-md-6">
        <div class="ibox">
            <div class="ibox-title bg-info"><h5>Sales by Company</h5></div>
            <div class="ibox-content">
                <canvas id="companyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="ibox">
            <div class="ibox-title bg-primary"><h5>Sales by Location</h5></div>
            <div class="ibox-content">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-12 mt-3">
        <div class="ibox">
            <div class="ibox-title bg-success"><h5>Campaign ROI</h5></div>
            <div class="ibox-content">
                <canvas id="campaignChart"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="alert alert-info">
    <strong>System Status:</strong>

    Active Campaigns:
    {{ \App\Models\Campaign::where('status','active')->count() }}

    | Total Sales:
    {{ number_format(\App\Models\Sale::sum('total_amount'),2) }}
</div>
{{--  CHART --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    //  SALES BY COMPANY
const companyLabels = {!! json_encode($salesByCompany->pluck('company.name')) !!};
const companyData = {!! json_encode($salesByCompany->pluck('total_sales')) !!};

new Chart(document.getElementById('companyChart'), {
    type: 'bar',
    data: {
        labels: companyLabels,
        datasets: [{
            label: 'Sales',
            data: companyData
        }]
    }
});


//  SALES BY LOCATION
const locationLabels = {!! json_encode($salesByLocation->pluck('workPoint.work_name')) !!};
const locationData = {!! json_encode($salesByLocation->pluck('total_sales')) !!};

new Chart(document.getElementById('locationChart'), {
    type: 'bar',
    data: {
        labels: locationLabels,
        datasets: [{
            label: 'Sales',
            data: locationData
        }]
    }
});
setInterval(() => {

    fetch("{{ route('sales.dashboard') }}")
    .then(res => res.text())
    .then(html => {
        document.querySelector('.wrapper').innerHTML = html;
    });

}, 10000);

//  CAMPAIGN ROI
const campaignLabels = {!! json_encode($campaignReport->pluck('name')) !!};
const campaignData = {!! json_encode($campaignReport->map(function($c){
    return $c->budget > 0 ? (($c->revenue_generated - $c->discount_given)/$c->budget)*100 : 0;
})) !!};

new Chart(document.getElementById('campaignChart'), {
    type: 'line',
    data: {
        labels: campaignLabels,
        datasets: [{
            label: 'ROI (%)',
            data: campaignData
        }]
    }
});
</script>
@endsection