@extends('layouts.ReqstMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Requisition & Approvals Dashboard</h2>
        <ol class="breadcrumb" style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('requisition') }}">Requisition & Approvals</a>
            </li>
            <span style="font-size:25px" class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Product Request</strong>
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
    function timedMsg() {
        var t = setInterval("change_time();", 1000);
    }

    function change_time() {
        var d = new Date();
        var curr_hour = d.getHours();
        var curr_min = d.getMinutes();
        var curr_sec = d.getSeconds();
        if (curr_hour > 24)
            curr_hour = curr_hour - 24;
        document.getElementById('Hour').innerHTML = curr_hour + ':';
        document.getElementById('Minut').innerHTML = curr_min + ':';
        document.getElementById('Second').innerHTML = curr_sec;
    }
    timedMsg();

</script>
<div class="col-12 mb-3">
    <h3 class="mb-2 page-title">Service Request - {{ $sr->RequestNo }}</h3>
    <div class="float-right">
        <button class="btn btn-sm btn-secondary" onclick="window.history.back();">Back</button>
        <button class="btn btn-sm btn-primary" onclick="printReceipt('printArea')"><i class="fa fa-print"></i> Print</button>
    </div>
</div>

<br><br>
<div id="printArea" style="background:#fff; padding:20px;">
    <div style="max-width:900px; margin:0 auto; color:#000;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-weight:700; font-size:18px;">{{ optional($sr->company)->company_name ?? '-' }}</div>
                <div style="font-size:12px;">{{ optional($sr->company)->company_code ? 'Code: '.optional($sr->company)->company_code : '' }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:700;">Service Request</div>
                <div>Request No: <strong>{{ $sr->RequestNo }}</strong></div>
                <div>Request Date: <strong>{{ optional($sr->RequestDate)->format('Y-m-d') ?? '-' }}</strong></div>
            </div>
        </div>

        <hr>

        <div><strong>Work Point:</strong> {{ optional($sr->workpoint)->work_name ?? '-' }}</div>
        <div style="margin-top:12px;"><strong>Service Type:</strong> {{ $sr->ServiceType }}</div>
        <div style="margin-top:8px;"><strong>Description:</strong>
            <div style="border:1px dashed #ccc; padding:8px; margin-top:6px;">{!! nl2br(e($sr->Description)) !!}</div>
        </div>
        <div style="margin-top:12px;"><strong>Estimated Cost:</strong> {{ number_format($sr->estimated_cost,2) }}</div>

        <div style="margin-top:20px; display:flex; gap:18px; justify-content:space-between;">
            <div style="flex:1; text-align:center;">
                <div style="border-bottom:1px solid #000; height:2px; margin-bottom:6px;"></div>
                <div>Requested By</div>
                <div>Name: <strong>{{ optional($sr->user)->name }}</strong></div>
            </div>
            <div style="flex:1; text-align:center;">
                <div style="border-bottom:1px solid #000; height:2px; margin-bottom:6px;"></div>
                <div>Approved By</div>
                <div>Name: ____________________</div>
            </div>
            <div style="flex:1; text-align:center;">
                <div style="border-bottom:1px solid #000; height:2px; margin-bottom:6px;"></div>
                <div>Maintenance / Store</div>
                <div>Name: ____________________</div>
            </div>
        </div>

        <div style="margin-top:18px; font-size:11px; color:#666; text-align:center;">Printed: {{ now()->format('Y-m-d H:i') }}</div>
    </div>
</div>

<script>
    function printReceipt(ele) {
        var content = document.getElementById(ele);
        if (!content) return alert('Nothing to print');
        var pri = window.open('', '_blank', 'height=842,width=595');
        var doc = pri.document.open();
        var style = `<style>
    @page { margin: 20mm; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color:#000; margin:0; padding:0; font-size:13px; }
    table{border-collapse:collapse;width:100%}
    table th, table td{border:1px solid #ddd;padding:8px}
  </style>`;
        doc.write('<html><head><title>Print</title>' + style + '</head><body>');
        doc.write(content.innerHTML);
        doc.write('</body></html>');
        doc.close();
        pri.focus();
        setTimeout(function() {
            pri.print();
        }, 400);
    }

</script>
@endsection
