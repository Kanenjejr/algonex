@extends('layouts.ReqstMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Requisition & Approvals Dashboard</h2>
        <ol class="breadcrumb" style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('requisition') }}">Requisition & Approvals</a>
            </li>
            <span style="font-size:25px" class="fa fa-angle-double-right"></span>
            <li class="breadcrumb-item active">
                <strong>Cashed-Out / Retirement View</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <h2>Current Date</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">
                <strong>
                    <?php
                        use Carbon\Carbon;
                        $carbon = Carbon::now();
                        $carbon1 = Carbon::now()->toDateString();
                        echo $carbon->format('l'); echo " , "; echo $carbon1;
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
                        </tr>
                    </table>
                </strong>
            </li>
        </ol>
    </div>
</div>

<script type="text/javascript">
    function timedMsg() {
        setInterval("change_time();", 1000);
    }
    function change_time() {
        var d = new Date();
        var curr_hour = d.getHours();
        var curr_min = d.getMinutes();
        var curr_sec = d.getSeconds();
        if (curr_hour > 24) curr_hour = curr_hour - 24;
        document.getElementById('Hour').innerHTML = curr_hour + ':';
        document.getElementById('Minut').innerHTML = curr_min + ':';
        document.getElementById('Second').innerHTML = curr_sec;
    }
    timedMsg();
</script>

@php
    $actualSpendings = (float)((float)($mr->approved_amount ?? $mr->total_amount ?? 0) - (float)($mr->returned_amount ?? 0));
@endphp

<div class="col-12 mb-3">
    <h3 class="mb-2 page-title">Cashed-Out / Retirement Request</h3>
    <div class="float-right">
        <button class="btn btn-sm btn-secondary" onclick="window.history.back();">Back</button>
        <button class="btn btn-sm btn-primary" onclick="printReceipt('printArea')">
            <i class="fa fa-print"></i> Print
        </button>
    </div>
</div>

<br><br>

<div id="printArea" style="background:#fff; padding:24px;">
    {{-- COMPANY HEADER --}}
<div style="width:100%; margin-bottom:20px; text-align:center;">
    <img src="{{ asset('img/header.png') }}"
         alt="Company Header"
         style="width:100%; max-height:170px; object-fit:contain;">
</div>
    <div style="max-width:1000px; margin:0 auto; border:1px solid #d9e2f2; border-top:6px solid #173A7A; padding:24px; box-shadow:0 8px 24px rgba(23,58,122,.08);">

        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:16px;">
            <div style="max-width:65%;">
                <div style="font-size:22px; font-weight:800; color:#173a7a;">Cashed-Out / Retirement Form</div>
                <div style="font-weight:700; font-size:18px;">{{ optional($mr->company)->company_name ?? '-' }}</div>
                <div style="font-size:12px;">{{ optional($mr->company)->district ?? '' }} {{ optional($mr->company)->city ?? '' }}</div>
                <div style="font-size:12px;">{{ optional($mr->company)->phone_No ?? '' }}</div>
            </div>

            <div style="text-align:right; min-width:180px;">
                @if(optional($mr->company)->logo)
                    <div style="margin-bottom:8px;">
                        <img src="{{ asset(optional($mr->company)->logo) }}" style="max-height:80px; max-width:170px;">
                    </div>
                @endif
                <div><strong>Request No:</strong> {{ $mr->RequestNo }}</div>
                <div><strong>Request Date:</strong> {{ $mr->RequestDate ? \Carbon\Carbon::parse($mr->RequestDate)->format('Y-m-d') : '-' }}</div>
            </div>
        </div>

        <hr style="border-top:1px solid #173A7A;">

        <div class="row">
            <div class="col-md-6">
                <p><strong>Reference No:</strong> {{ $mr->RequestNo }}</p>
                <p><strong>Request Date:</strong> {{ $mr->RequestDate ? \Carbon\Carbon::parse($mr->RequestDate)->format('Y-m-d') : '-' }}</p>
                <p><strong>Working Point:</strong> {{ optional($mr->workpoint)->work_code }} - {{ optional($mr->workpoint)->work_name }}</p>
                <p><strong>Requested By:</strong> {{ optional($mr->requester)->name ?? '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Details:</strong> {{ $mr->Description ?? '-' }}</p>
                <p><strong>Approved Amount to Cash Out:</strong> {{ number_format((float)($mr->approved_amount ?? $mr->total_amount ?? 0), 2) }}</p>
                <p><strong>Status:</strong> {{ $mr->Status }}</p>
                <p><strong>Returned Amount:</strong> {{ number_format((float)($mr->returned_amount ?? 0), 2) }}</p>
            </div>
        </div>

        <div style="margin-top:12px; border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
            <strong>Accounting / Department</strong>
            <div style="margin-top:6px;">
                <div><strong>Account:</strong> {{ optional($mr->account)->AccCode ?? '-' }} - {{ optional($mr->account)->AccDescription ?? '-' }}</div>
                <div><strong>Sub Account:</strong> {{ optional($mr->subAccount)->SubCode ?? '-' }} - {{ optional($mr->subAccount)->SubDescription ?? '-' }}</div>
                <div><strong>Department:</strong> {{ optional($mr->department)->depCode ?? '-' }} - {{ optional($mr->department)->depName ?? '-' }}</div>
                <div><strong>Section:</strong> {{ optional($mr->section)->secCode ?? '-' }} - {{ optional($mr->section)->secName ?? '-' }}</div>
            </div>
        </div>

        <div style="margin-top:12px; border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
            <strong>Actual Spendings</strong>
            <div style="margin-top:6px; font-size:18px; font-weight:800; color:#173a7a;">
                {{ number_format($actualSpendings, 2) }}
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:18px; flex-wrap:wrap;">
            <div style="flex:1; min-width:230px;">
                <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; min-height:120px; background:#fff;">
                    <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                    <div style="text-align:center; font-weight:700;">Requested By</div>
                    <div style="text-align:center;">Name: <strong>{{ optional($mr->requester)->name ?? '-' }}</strong></div>
                    <div style="text-align:center; font-size:12px;">When: {{ $mr->created_at ? \Carbon\Carbon::parse($mr->created_at)->format('Y-m-d H:i') : '-' }}</div>
                </div>
            </div>

            <div style="flex:1; min-width:230px;">
                <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; min-height:120px; background:#fff;">
                    <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                    <div style="text-align:center; font-weight:700;">Approved By</div>
                    <div style="text-align:center;">Name: <strong>{{ optional($mr->approver)->name ?? '-' }}</strong></div>
                    <div style="text-align:center; font-size:12px;">When: {{ $mr->approved_at ? \Carbon\Carbon::parse($mr->approved_at)->format('Y-m-d H:i') : '-' }}</div>
                    @if($mr->Status === 'Approved')
                        @if(optional($mr->company)->signature)
                            <div style="text-align:center; margin-top:6px;">
                                <img src="{{ asset(optional($mr->company)->signature) }}" style="max-height:70px; max-width:150px;">
                            </div>
                        @endif
                        @if(optional($mr->company)->stamp)
                            <div style="text-align:center; margin-top:6px;">
                                <img src="{{ asset(optional($mr->company)->stamp) }}" style="max-height:70px; max-width:150px;">
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div style="flex:1; min-width:230px;">
                <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; min-height:120px; background:#fff;">
                    <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                    <div style="text-align:center; font-weight:700;">Retired / Cashier</div>
                    <div style="text-align:center;">Cashier: <strong>{{ optional($mr->cashier)->name ?? '-' }}</strong></div>
                    <div style="text-align:center;">Retired By: <strong>{{ optional($mr->retreater)->name ?? '-' }}</strong></div>
                    <div style="text-align:center; font-size:12px;">Retired At: {{ $mr->retired_at ? \Carbon\Carbon::parse($mr->retired_at)->format('Y-m-d H:i') : '-' }}</div>
                </div>
            </div>
        </div>

        <div style="margin-top:12px; border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
            <strong>Returned Amount</strong>
            <div>{{ number_format((float)($mr->returned_amount ?? 0), 2) }}</div>
        </div>

        <div style="margin-top:12px; border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
            <strong>Retirement Comment</strong>
            <div>{{ $mr->retirement_comment ?? '-' }}</div>
        </div>

        <div style="margin-top:12px; border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fbff;">
            <strong>Retirement Document</strong>
            <div style="margin-top:6px;">
                @if($mr->retirement_docs)
                    <a href="{{ route('reports.money.cashout_retirement.document', encrypt($mr->id)) }}" target="_blank">View Document</a>
                @else
                    -
                @endif
            </div>
        </div>

        <hr style="border-top:1px solid #173A7A; margin-top:18px;">

        <div style="font-size:11px; color:#666; text-align:center;">
            Printed: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>
</div>

<script>
function printReceipt(ele) {
    var content = document.getElementById(ele);
    if (!content) return alert('Nothing to print');

    var pri = window.open('', '_blank', 'height=842,width=595');
    var doc = pri.document.open();

    var style = `<style>
        @page{margin:18mm}
        *{box-sizing:border-box;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            color:#000;
            margin:0;
            padding:0;
            font-size:12.5px
        }
        img{max-width:100%;height:auto}
        a{text-decoration:none;color:#000;}
    </style>`;

    doc.write('<html><head><title>Cashed-Out / Retirement View</title>' + style + '</head><body>');
    doc.write(content.innerHTML);
    doc.write('</body></html>');
    doc.close();

    pri.focus();
    setTimeout(function() { pri.print(); }, 400);
}
</script>

@endsection