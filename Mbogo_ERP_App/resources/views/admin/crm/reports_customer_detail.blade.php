@extends('layouts.salesMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Customers, Supplies & Interactions Dashboard</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                 <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Customer / Supplier Details Report</strong>
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
    <h3 class="mb-2 page-title">Customer / Supplier Report - {{ $customer->customer_name }}</h3>
    <a href="{{ route('crm.reports.index') }}" class="btn btn-sm btn-secondary float-right">Back</a>
 <button onclick="printReceipt('form1')"class="btn btn-sm btn-primary float-right mr-2"><i class="fa fa-print"></i> Print Report</button>
</div>
    <div class="row mb-3">
        <div class="col-md-12">
            <form method="GET" action="{{ route('crm.reports.customer.detail', encrypt($customer->id)) }}" class="form-inline">
                <div class="form-group mr-2"><label class="mr-1">From</label><input type="date" class="form-control" name="from" value="{{ request('from', $from) }}"></div>
                <div class="form-group mr-2"><label class="mr-1">To</label><input type="date" class="form-control" name="to" value="{{ request('to', $to) }}"></div>
                <button class="btn btn-primary mr-2">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('crm.reports.customer.detail', encrypt($customer->id)) }}">Clear</a>
            </form>
        </div>
    </div>

<div id="form1" class="wrapper wrapper-content animated fadeInRight" id="reportContent">
  {{-- Customer Details --}}
<div class="row mb-3">
    <div class="col-md-12">
        <div class="ibox">
            <div class="ibox-title bg-primary"><h5>Customer / Supplier Info</h5></div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            <tr>
                                <th>Name</th>
                                <td>{{ $customer->customer_name }}</td>
                                <th>Phone</th>
                                <td>{{ $customer->phone_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{{ $customer->location ?? '-' }}</td>
                                <th>Category</th>
                                <td>{{ $customer->category }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td colspan="3">
                                    {{ $customer->address_line ?? '-' }}, {{ $customer->city ?? '-' }},
                                    {{ $customer->state ?? '-' }}, {{ $customer->postal_code ?? '-' }},
                                    {{ $customer->country ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td colspan="3">{{ $customer->status }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        {{-- Orders --}}
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title bg-info"><h5>Orders</h5></div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr><th>#</th><th>Order No</th><th>Date</th><th>Type</th><th>Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $k => $o)
                                <tr>
                                    <td>{{ $k+1 }}</td>
                                    <td>{{ $o->order_no }}</td>
                                    <td>{{ $o->order_date }}</td>
                                    <td>{{ ucfirst($o->type) }}</td>
                                    <td>{{ number_format($o->total_amount,2) }}</td>
                                    <td>{{ $o->status }}</td>
                                </tr>
                                @endforeach
                                @if($orders->isEmpty())
                                <tr><td colspan="6" class="text-center">No orders found</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title bg-warning"><h5>Transactions</h5></div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                    <th>Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalDebit = 0; $totalCredit = 0; @endphp
                                @foreach($txs as $k => $t)
                                <tr>
                                    <td>{{ $k+1 }}</td>
                                    <td>{{ $t->tx_date }}</td>
                                    <td>{{ $t->debit > 0 ? number_format($t->debit,2) : '-' }}</td>
                                    <td>{{ $t->credit > 0 ? number_format($t->credit,2) : '-' }}</td>
                                    <td>{{ number_format($t->balance_after ?? 0,2) }}</td>
                                    <td>{{ $t->reference ?? '-' }}</td>
                                </tr>
                                @php $totalDebit += $t->debit; $totalCredit += $t->credit; @endphp
                                @endforeach
                                @if($txs->isEmpty())
                                <tr><td colspan="6" class="text-center">No transactions found</td></tr>
                                @else
                                <tr class="font-weight-bold">
                                    <td colspan="2">Totals</td>
                                    <td>{{ number_format($totalDebit,2) }}</td>
                                    <td>{{ number_format($totalCredit,2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- /.row -->
</div>
@endsection
