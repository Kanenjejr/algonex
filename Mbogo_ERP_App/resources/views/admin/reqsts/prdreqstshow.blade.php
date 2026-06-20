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
    <h3 class="mb-2 page-title">Product Request / Requisition</h3>
    <div class="float-right">
        <button class="btn btn-sm btn-secondary mr-1" onclick="window.history.back();">Back</button>
        <button onclick="printReceipt('printArea')" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> Print</button>
    </div>
</div>
<br><br>
<div id="printArea" class="wrapper wrapper-content animated fadeInRight" style="background:#fff; padding:20px;">
    <div style="max-width:900px; margin:0 auto; color:#000;">
        {{-- Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="display:flex; gap:12px; align-items:center;">
                {{-- optional logo if available --}}
                @if(optional($rq->company)->logo)
                <img src="{{ asset(optional($rq->company)->logo) }}" alt="logo" style="height:60px; width:auto; object-fit:contain;">
                @endif
                <div>
                    <div style="font-weight:700; font-size:18px;">{{ $companyName ?? '-' }}</div>
                    <div style="font-size:12px;">{{ $companyCode ? 'Company Code: '.$companyCode : '' }}</div>
                </div>
            </div>

            <div style="text-align:right;">
                <div style="font-weight:700; font-size:14px;">Request</div>
                <div style="font-size:13px;">Request No: <strong>{{ $rq->RequestNo }}</strong></div>
                <div style="font-size:13px;">Request Date: <strong>{{ $requestDate ?? ($rq->created_at ? $rq->created_at->format('Y-m-d') : '-') }}</strong></div>
            </div>
        </div>

        <hr style="border: none; border-top: 2px solid #eee; margin:8px 0 16px 0;">

        {{-- meta --}}
        <div style="display:flex; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div><strong>Work Point:</strong> {{ $workPointName ?? '-' }} {{ $workCode ? ' ('.$workCode.')' : '' }}</div>
            <div><strong>Requested By:</strong> {{ $requestedBy }}</div>
            <div><strong>Status:</strong> {{ $rq->Status }}</div>
        </div>

        {{-- Items table --}}
        <div style="margin-top:8px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;" border="1">
                <thead>
                    <tr style="background:#f4f4f4;">
                        <th style="padding:8px; text-align:center; width:6%;">#</th>
                        <th style="padding:8px; text-align:left;">Product</th>
                        <th style="padding:8px; text-align:right; width:14%;">Unit Price</th>
                        <th style="padding:8px; text-align:right; width:12%;">Quantity</th>
                        <th style="padding:8px; text-align:right; width:16%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($items) === 0)
                    <tr>
                        <td colspan="5" style="padding:12px; text-align:center;">No items found.</td>
                    </tr>
                    @endif

                    @foreach($items as $k => $it)
                    <tr>
                        <td style="padding:8px; text-align:center;">{{ $k + 1 }}</td>
                        <td style="padding:8px; text-align:left;">{{ $it['product_name'] }}</td>
                        <td style="padding:8px; text-align:right;">{{ number_format($it['unit_price'], 2) }}</td>
                        <td style="padding:8px; text-align:right;">{{ number_format($it['quantity'], 2) }}</td>
                        <td style="padding:8px; text-align:right;">{{ number_format($it['subtotal'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="padding:8px; text-align:right; font-weight:700;">Total</td>
                        <td style="padding:8px; text-align:right; font-weight:700;">{{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Notes --}}
        <div style="margin-top:16px; font-size:13px;">
            <strong>Notes:</strong>
            <div style="border:1px dashed #ccc; padding:8px; min-height:40px;">{{ $rq->remarks ?? '' }}</div>
        </div>

        {{-- Signatures --}}
        <div style="display:flex; gap:18px; margin-top:30px; justify-content:space-between; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px; text-align:center;">
                <div style="border-bottom:1px solid #000; height:2px; margin-bottom:6px;"></div>
                <div style="font-size:13px;">Requested By</div>
                <div style="height:6px;"></div>
                <div style="font-size:12px;">Name: <strong>{{ $requestedBy }}</strong></div>
                <div style="font-size:12px;">Date: ____________________</div>
            </div>

            <div style="flex:1; min-width:200px; text-align:center;">
                <div style="border-bottom:1px solid #000; height:2px; margin-bottom:6px;"></div>
                <div style="font-size:13px;">Approved By</div>
                <div style="height:6px;"></div>
                <div style="font-size:12px;">Name: ____________________</div>
                <div style="font-size:12px;">Date: ____________________</div>
            </div>

            <div style="flex:1; min-width:200px; text-align:center;">
                <div style="border-bottom:1px solid #000; height:2px; margin-bottom:6px;"></div>
                <div style="font-size:13px;">Store Keeper</div>
                <div style="height:6px;"></div>
                <div style="font-size:12px;">Name: ____________________</div>
                <div style="font-size:12px;">Date: ____________________</div>
            </div>
        </div>
        {{-- Footer small --}}
        <div style="margin-top:22px; font-size:11px; color:#666; text-align:center;">
            Printed: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div> {{-- /inner container --}}
</div> {{-- /printArea --}}
@endsection
