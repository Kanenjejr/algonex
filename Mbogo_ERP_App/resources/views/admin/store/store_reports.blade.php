@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Store Management Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Store Reports</strong></li>
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
                        echo $carbon->format('l');
                        echo ' , ';
                        echo $carbon1;
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
                                <td id="Hour7" style="color:green;font-size:large;"></td>
                                <td id="Minut7" style="color:green;font-size:large;"></td>
                                <td id="Second7" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg7() {
            setInterval("change_time7();", 1000);
        }

        function change_time7() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            if (curr_hour > 24) curr_hour = curr_hour - 24;
            document.getElementById('Hour7').innerHTML = curr_hour + ':';
            document.getElementById('Minut7').innerHTML = curr_min + ':';
            document.getElementById('Second7').innerHTML = curr_sec;
        }
        timedMsg7();
    </script>

    <div class="wrapper wrapper-content animated fadeInRight" style="padding:15px;">
        <div class="ibox"
            style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">

            <div class="ibox-title bg-success"
                style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff; padding:14px 16px; border-bottom:4px solid #b08a2e;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div style="font-size:18px; font-weight:800;">Store Report</div>
                    <div class="ibox-tools" style="display:flex; gap:10px; align-items:center;">
                        <button type="button" onclick="printReceipt('printArea')" class="btn btn-primary text-white"
                            style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#1f4fa3;">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <div class="ibox-content">
                <form method="GET" action="{{ route('sales.store.reports.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" value="{{ $startDate ?? now()->toDateString() }}"
                                class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" value="{{ $endDate ?? now()->toDateString() }}"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Work Point / Location</label>
                            <select name="work_point_id" id="work_point_id" class="form-control select2_filter">
                                <option value="">-- All work points --</option>
                                @foreach ($workPoints as $wp)
                                    <option value="{{ $wp->id }}"
                                        {{ (string) ($workPointId ?? '') === (string) $wp->id ? 'selected' : '' }}>
                                        {{ $wp->work_code ?? '' }}{{ !empty($wp->work_code) ? ' - ' : '' }}{{ $wp->work_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2" style="padding-top:25px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Search
                            </button>
                            <a href="{{ route('sales.store.reports.index') }}" class="btn btn-default">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div id="printArea">
                    <div
                        style="max-width:1100px; margin:0 auto; color:#000; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:13px; line-height:1.55;">

                        <div
                            style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; padding-bottom:12px; border-bottom:2px solid rgba(23,58,122,.14);">
                            <div style="max-width:68%;">
                                <div style="font-size:22px; font-weight:800; color:#173a7a; line-height:1.2;">
                                    {{ optional(auth()->user()->company)->company_name ?? 'Company Name' }}
                                </div>
                                <div style="font-size:12px; color:#334155; line-height:1.7; margin-top:4px;">
                                    {{ optional(auth()->user()->company)->company_code ? 'Code: ' . optional(auth()->user()->company)->company_code : '' }}<br>
                                    {{ optional(auth()->user()->company)->district ?? '' }}
                                    {{ optional(auth()->user()->company)->city ?? '' }}<br>
                                    {{ optional(auth()->user()->company)->phone_No ?? '' }}
                                </div>
                            </div>

                            <div style="text-align:right; min-width:220px;">
                                @if (optional(auth()->user()->company)->logo)
                                    <div style="margin-bottom:8px;">
                                        <img src="{{ asset(optional(auth()->user()->company)->logo) }}"
                                            style="max-height:70px; max-width:150px;">
                                    </div>
                                @endif
                                <div style="font-size:18px; font-weight:800; color:#7b4a2d; margin-top:4px;">
                                    Store Report
                                </div>
                                <div>From: <strong>{{ $startDate ?? '-' }}</strong></div>
                                <div>To: <strong>{{ $endDate ?? '-' }}</strong></div>
                                <div>Work Point:
                                    <strong>
                                        @if (!empty($workPointId))
                                            @php
                                                $selectedWp = $workPoints->firstWhere('id', $workPointId);
                                            @endphp
                                            {{ optional($selectedWp)->work_code ?? '' }}{{ !empty(optional($selectedWp)->work_code) ? ' - ' : '' }}{{ optional($selectedWp)->work_name ?? 'Selected Work Point' }}
                                        @else
                                            All Work Points
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>

                        {{-- GENERAL SUPPLY SECTION --}}
                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                General Supply Received Report
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Date</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Description</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Qty</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Purchase Price</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Total</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($gsReceived as $k => $r)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $r->receive_date }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->item)->item_name ?? '-' }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->description)->description_name ?? '-' }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->received_qty, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->purchase_price ?? 0, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->total_amount ?? 0, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->workpoint)->work_code ?? '' }}{{ optional($r->workpoint)->work_code ? ' - ' : '' }}{{ optional($r->workpoint)->work_name ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No data found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="6"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Total Purchase Value
                                            </th>
                                            <th colspan="2"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($gsReceived)->sum('total_amount'), 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                General Supply Issued Report
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Date</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Description</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Qty</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($gsIssued as $k => $r)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $r->issue_date }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->item)->item_name ?? '-' }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->description)->description_name ?? '-' }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->issued_qty, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->workpoint)->work_code ?? '' }}{{ optional($r->workpoint)->work_code ? ' - ' : '' }}{{ optional($r->workpoint)->work_name ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No data found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="4"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Total Issued Qty
                                            </th>
                                            <th colspan="2"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($gsIssued)->sum('issued_qty'), 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                General Supply Daily Stock Movement
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Description</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Opening
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Received
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Issued
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Closing
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($gsMovement as $k => $r)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $r['work_point'] }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $r['item_name'] }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $r['description_name'] }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['opening'], 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['received'], 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['issued'], 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['closing'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No stock movement found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="4"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Totals
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($gsMovement)->sum('opening'), 2) }}
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($gsMovement)->sum('received'), 2) }}
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($gsMovement)->sum('issued'), 2) }}
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($gsMovement)->sum('closing'), 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- RAW MATERIAL SECTION --}}
                        <div
                            style="margin-top:18px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                Raw Material Purchased Report
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Date</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Material</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Qty</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Unit Price</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Total</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rmPurchases as $k => $r)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $r->purchase_date }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->material)->material_name ?? '-' }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->qty, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->unit_price, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->total_price, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->workpoint)->work_code ?? '' }}{{ optional($r->workpoint)->work_code ? ' - ' : '' }}{{ optional($r->workpoint)->work_name ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No data found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="5"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Total Purchase Value
                                            </th>
                                            <th colspan="2"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($rmPurchases)->sum('total_price'), 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                Raw Material Issued Report
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Date</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Material</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Qty</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Issue To</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rmIssues as $k => $r)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $r->issue_date }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->material)->material_name ?? '-' }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r->issued_qty, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $r->issue_to_type }} - {{ $r->issue_to_name }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($r->workpoint)->work_code ?? '' }}{{ optional($r->workpoint)->work_code ? ' - ' : '' }}{{ optional($r->workpoint)->work_name ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No data found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="3"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Total Issued Qty
                                            </th>
                                            <th colspan="3"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($rmIssues)->sum('issued_qty'), 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                Raw Material Daily Stock Movement
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Material</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Opening
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Purchased
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Issued
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Closing
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rmMovement as $k => $r)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $r['work_point'] }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $r['material_name'] }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['opening'], 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['purchased'], 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['issued'], 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($r['closing'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No stock movement found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="3"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Totals
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($rmMovement)->sum('opening'), 2) }}
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($rmMovement)->sum('purchased'), 2) }}
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($rmMovement)->sum('issued'), 2) }}
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format(collect($rmMovement)->sum('closing'), 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div style="margin-top:18px; text-align:center; font-size:11px; color:#666;">
                            Printed: {{ now()->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
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
                @page{
                    size:A4 landscape;
                    margin:12mm;
                }
                *{
                    box-sizing:border-box;
                    -webkit-print-color-adjust:exact;
                    print-color-adjust:exact;
                }
                html, body{
                    margin:0;
                    padding:0;
                    background:#fff;
                    color:#000;
                    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
                    font-size:12px;
                }
                img{
                    max-width:100%;
                    height:auto;
                }
                a{
                    color:#000;
                    text-decoration:none;
                }
                table{
                    width:100%;
                    border-collapse:collapse;
                }
            </style>`;

            doc.write('<html><head><title>Store Report</title>' + style + '</head><body>');
            doc.write(content.innerHTML);
            doc.write('</body></html>');
            doc.close();

            pri.focus();
            setTimeout(function() {
                pri.print();
            }, 400);
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function initSelect2Normal($el) {
                if (!$el || !$el.length) return;

                if ($el.data('select2')) {
                    try {
                        $el.select2('destroy');
                    } catch (e) {}
                }

                $el.select2({
                    width: '100%',
                    theme: 'bootstrap4'
                });
            }

            initSelect2Normal($('#work_point_id'));
        });
    </script>
@endsection
