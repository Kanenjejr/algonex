@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-9">
    <h2>Microfinancing Transactions Information</h2>
    <ol class="breadcrumb" style="font-size:17px;color:#000">
      <li><a href="{{ route('microfinancing') }}">Microfinancing</a></li>
      <span style="font-size:25px" class="fa fa-angle-double-right "></span>
      <li class="breadcrumb-item active"><strong>Detailed BN Report</strong></li>
    </ol>
  </div><div class="col-lg-2">
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
      <h3>Report for: <strong>{{ $bn->type }} - {{ $bn->name }}</strong></h3>
      <p>From: <strong>{{ $from }}</strong> To: <strong>{{ $to }}</strong></p>
    </div>
  </div>
 <div class="row mb-3">
    <div class="col-md-12">
  <form method="GET" action="{{ route('micro.reports.bn.detail') }}" class="mb-3">
    <input type="hidden" name="bank_network_id" value="{{ $bn->id }}">
    <div class="form-row align-items-end">
      <div class="form-group col-md-2"><label>From</label><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
      <div class="form-group col-md-2"><label>To</label><input type="date" name="to" value="{{ $to }}" class="form-control"></div>
      @if($workPoints->count())
      <div class="form-group col-md-3">
        <label>Work Point</label>
        <select name="work_point_id" class="form-control select2_modal">
          <option value="">-- All --</option>
          @foreach($workPoints as $wp)
            <option value="{{ $wp->id }}" {{ (isset($workPointFilter) && $workPointFilter == $wp->id) ? 'selected' : '' }}>{{ $wp->work_name }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="form-group col-md-2"><button class="btn btn-primary">Filter</button> <a href="{{ route('micro.reports.bn.detail', ['bank_network_id'=>$bn->id]) }}" class="btn btn-secondary">Reset</a></div>
    </div>
  </form>
</div>
</div>
<div id="form1" class="wrapper wrapper-content animated fadeInRight" id="reportContent">
    <div class="ibox-content">
    <div class="table-responsive">
  @php
    $currencies = array_unique(array_merge(array_keys($opening ?? []), array_keys($periodTotals ?? []), array_keys($closingBalances ?? [])));
    if (empty($currencies)) $currencies = ['TZS'];
  @endphp
  @foreach($currencies as $currency)
    <div class="ibox mb-3">
      <div class="ibox-title bg-info"><h5>{{ $currency }} — Transactions for: <strong>{{ $bn->type }} - {{ $bn->name }}</strong></h5></div>
      <div class="ibox-content">
        <div class="mb-2">
          <strong>Opening Balance:</strong> {{ number_format($opening[$currency] ?? 0, 2) }}
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Work Point</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Tx Rate</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance After</th>
                <th>Ref / Meta</th>
              </tr>
            </thead>
            <tbody>
              @php $i = 0; $sumDebit = 0; $sumCredit = 0; @endphp
              @foreach($txs_with_balance->where('currency',$currency) as $t)
                @php
                  $i++;
                  $debit = $t->computed->debit ?? 0;
                  $credit = $t->computed->credit ?? 0;
                  $sumDebit += $debit;
                  $sumCredit += $credit;
                @endphp
                <tr>
                  <td>{{ $i }}</td>
                  <td>{{ optional($t->created_at)->format('Y-m-d H:i') }}</td>
                  <td>{{ optional($t->workpoint)->work_name ?? '-' }}</td>
                  <td>{{ $t->tx_group }}</td>
                  <td>{{ number_format($t->amount/($t->fx_rate ?? 1),2) }}</td>
                  <td>{{ number_format($t->fx_rate,2) }}</td>
                  <td class="text-right">{{ $debit>0 ? number_format($debit,2) : '-' }}</td>
                  <td class="text-right">{{ $credit>0 ? number_format($credit,2) : '-' }}</td>
                  <td class="text-right">{{ number_format($t->computed->balance_after ?? 0,2) }}</td>
                  <td>{{ $t->meta ? (is_array($t->meta) ? json_encode($t->meta) : $t->meta) : ($t->id) }}</td>
                </tr>
              @endforeach
              @if($i === 0)
                <tr><td colspan="8" class="text-center">No transactions for this currency in selected period.</td></tr>
              @else
                <tr class="font-weight-bold">
                  <td colspan="6">Totals</td>
                  <td class="text-right">{{ number_format($sumDebit,2) }}</td>
                  <td class="text-right">{{ number_format($sumCredit,2) }}</td>
                  <td colspan="2" class="text-right">Closing: {{ number_format($closingBalances[$currency] ?? 0,2) }}</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endforeach
  <div class="ibox">
    <div class="ibox-title bg-primary"><h5>Breakdown by Work Point ({{ $from }} — {{ $to }}) for: <strong>{{ $bn->type }} - {{ $bn->name }}</strong></h5></div>
    <div class="ibox-content">
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Work Point</th>
              <th>Currency</th>
              <th>TX Count</th>
              <th>Credits</th>
              <th>Debits</th>
              <th>Net (Credits - Debits)</th>
            </tr>
          </thead>
          <tbody>
            @php $k=0; @endphp
            @forelse($perWorkpoint as $wpId => $currMap)
              @php $wpModel = \App\Models\WorkPoint::find($wpId); @endphp
              @foreach($currMap as $cur => $vals)
                @php $k++; @endphp
                <tr>
                  <td>{{ $k }}</td>
                  <td>{{ optional($wpModel)->work_name ?? 'All' }}</td>
                  <td>{{ $cur }}</td>
                  <td>{{ number_format($vals['tx_count'],0) }}</td>
                  <td class="text-right">{{ number_format($vals['credits'],2) }}</td>
                  <td class="text-right">{{ number_format($vals['debits'],2) }}</td>
                  <td class="text-right">{{ number_format($vals['net'],2) }}</td>
                </tr>
              @endforeach
            @empty
              <tr><td colspan="7" class="text-center">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (jQuery && jQuery().select2) {
    $('.select2_modal').select2({ width:'100%', theme:'bootstrap4' });
  }
});
</script>
@endsection
