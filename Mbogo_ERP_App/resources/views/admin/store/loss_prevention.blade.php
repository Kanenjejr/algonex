@extends('layouts.salesMaster')

@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Store Management Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Loss Prevention</strong></li>
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
                                <td id="Hour9" style="color:green;font-size:large;"></td>
                                <td id="Minut9" style="color:green;font-size:large;"></td>
                                <td id="Second9" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg9() {
            setInterval("change_time9();", 1000);
        }

        function change_time9() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();

            if (curr_hour > 24) {
                curr_hour = curr_hour - 24;
            }

            document.getElementById('Hour9').innerHTML = curr_hour + ':';
            document.getElementById('Minut9').innerHTML = curr_min + ':';
            document.getElementById('Second9').innerHTML = curr_sec;
        }

        timedMsg9();
    </script>

    @php
        $expiredItems = $expiredItems ?? collect();
        $damagedItems = $damagedItems ?? collect();
        $auditVariance = $auditVariance ?? collect();

        $totalExpiredLoss = collect($expiredItems)->sum(function ($r) {
            return (float) ($r->balance ?? 0) * (float) ($r->purchase_price ?? 0);
        });

        $totalDamagedLoss = collect($damagedItems)->sum(function ($r) {
            return (float) ($r->damaged_qty ?? 0) * (float) ($r->purchase_price ?? 0);
        });

        $totalVarianceQty = collect($auditVariance)->sum('variance_qty');

        $gainVarianceQty = collect($auditVariance)
            ->filter(function ($r) {
                return (float) $r->variance_qty > 0;
            })
            ->sum('variance_qty');

        $lossVarianceQty = collect($auditVariance)
            ->filter(function ($r) {
                return (float) $r->variance_qty < 0;
            })
            ->sum('variance_qty');

        $auditTypeLabel = function ($type) {
            if ($type === 'GeneralSupply') {
                return 'General Supply';
            }

            if ($type === 'RawMaterial') {
                return 'Raw Material';
            }

            if ($type === 'Product') {
                return 'Product';
            }

            return $type ?? '-';
        };

        $statusBadge = function ($status) {
            if ($status === 'Closed') {
                return 'background:#64748b;color:#fff;';
            }

            if ($status === 'Approved') {
                return 'background:#2563eb;color:#fff;';
            }

            return 'background:#16a34a;color:#fff;';
        };
    @endphp

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox"
            style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">

            <div class="ibox-title bg-success"
                style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff; padding:14px 16px; border-bottom:4px solid #b08a2e;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div style="font-size:18px; font-weight:800;">Loss Prevention Report</div>

                    <div class="ibox-tools" style="display:flex; gap:10px; align-items:center;">
                        <button type="button" onclick="printReceipt('printArea')" class="btn btn-primary text-white"
                            style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#1f4fa3;">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <div class="ibox-content">
                <div id="printArea">
                    <div
                        style="max-width:1200px; margin:0 auto; color:#000; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:13px; line-height:1.55;">

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
                                    Loss Prevention Report
                                </div>

                                <div>Date: <strong>{{ now()->toDateString() }}</strong></div>
                            </div>
                        </div>

                        {{-- SUMMARY CARDS --}}
                        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:14px;">
                            <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fafc;">
                                <div style="font-size:11px; color:#64748b; font-weight:800;">EXPIRED LOSS</div>
                                <div style="font-size:20px; font-weight:900; color:#dc2626;">
                                    {{ number_format($totalExpiredLoss, 2) }}
                                </div>
                            </div>

                            <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fafc;">
                                <div style="font-size:11px; color:#64748b; font-weight:800;">DAMAGED LOSS</div>
                                <div style="font-size:20px; font-weight:900; color:#dc2626;">
                                    {{ number_format($totalDamagedLoss, 2) }}
                                </div>
                            </div>

                            <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fafc;">
                                <div style="font-size:11px; color:#64748b; font-weight:800;">AUDIT GAIN QTY</div>
                                <div style="font-size:20px; font-weight:900; color:#16a34a;">
                                    {{ number_format($gainVarianceQty, 2) }}
                                </div>
                            </div>

                            <div style="border:1px solid #d9e2f2; border-radius:12px; padding:12px; background:#f8fafc;">
                                <div style="font-size:11px; color:#64748b; font-weight:800;">AUDIT LOSS QTY</div>
                                <div style="font-size:20px; font-weight:900; color:#dc2626;">
                                    {{ number_format($lossVarianceQty, 2) }}
                                </div>
                            </div>
                        </div>

                        {{-- EXPIRED ITEMS --}}
                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">

                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                Expired General Supply Items
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Description</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Section</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Expiry Date</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Balance
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Unit Cost
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Estimated
                                                Loss</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($expiredItems as $k => $row)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($row->item)->item_name ?? '-' }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($row->description)->description_name ?? '-' }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($row->section)->secName ?? 'ALL' }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $row->expiry_date }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->balance ?? 0), 2) }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->purchase_price ?? 0), 2) }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->balance ?? 0) * (float) ($row->purchase_price ?? 0), 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No expired items found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="7"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Total Expired Loss
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format($totalExpiredLoss, 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- DAMAGED ITEMS --}}
                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">

                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                Damaged Items
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Date</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Description</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Damaged
                                                Qty</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Purchase
                                                Price</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Estimated
                                                Loss</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($damagedItems as $k => $row)
                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $row->receive_date }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($row->item)->item_name ?? '-' }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($row->description)->description_name ?? '-' }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->damaged_qty ?? 0), 2) }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->purchase_price ?? 0), 2) }}
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->damaged_qty ?? 0) * (float) ($row->purchase_price ?? 0), 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No damaged items found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="6"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Total Damaged Loss
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format($totalDamagedLoss, 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- AUDIT VARIANCES --}}
                        <div
                            style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">

                            <div
                                style="font-weight:800; color:#173a7a; margin-bottom:10px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                Audit Variances
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Audit Date</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Status</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Company</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item Type</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item Code</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Item Name</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Unit / Size</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">System
                                                Qty</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Physical
                                                Qty</th>
                                            <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Variance
                                            </th>
                                            <th style="border:1px solid #d9e2f2; padding:10px;">Remarks</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($auditVariance as $k => $row)
                                            @php
                                                $audit = $row->audit;
                                                $wp = optional($audit)->workpoint;
                                                $company = optional($wp)->company;
                                                $variance = (float) ($row->variance_qty ?? 0);
                                            @endphp

                                            <tr>
                                                <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional(optional($row->audit)->audit_date)->format('Y-m-d') ?? '-' }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    <span
                                                        style="{{ $statusBadge(optional($audit)->status) }} padding:5px 8px; border-radius:6px; font-size:11px; font-weight:800;">
                                                        {{ optional($audit)->status ?? '-' }}
                                                    </span>
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    @if ($wp)
                                                        <strong>{{ $wp->work_code ?? 'No Code' }}</strong> -
                                                        {{ $wp->work_name }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ optional($company)->company_name ?? '-' }}
                                                    {{ optional($company)->company_code ? '(' . optional($company)->company_code . ')' : '' }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $auditTypeLabel($row->item_type) }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $row->item_code ?? '-' }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $row->item_name ?? '-' }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $row->unit_name ?? '-' }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->system_qty ?? 0), 2) }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format((float) ($row->physical_qty ?? 0), 2) }}
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    @if ($variance > 0)
                                                        <span
                                                            style="background:#16a34a;color:#fff;padding:5px 8px;border-radius:6px;font-weight:800;">
                                                            {{ number_format($variance, 2) }}
                                                        </span>
                                                    @elseif($variance < 0)
                                                        <span
                                                            style="background:#dc2626;color:#fff;padding:5px 8px;border-radius:6px;font-weight:800;">
                                                            {{ number_format($variance, 2) }}
                                                        </span>
                                                    @else
                                                        <span
                                                            style="background:#2563eb;color:#fff;padding:5px 8px;border-radius:6px;font-weight:800;">
                                                            {{ number_format($variance, 2) }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td style="border:1px solid #d9e2f2; padding:10px;">
                                                    {{ $row->remarks ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="13"
                                                    style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                    No variance records found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                    <tfoot>
                                        <tr style="background:#f8fafc;">
                                            <th colspan="11"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                Total Variance Qty
                                            </th>
                                            <th colspan="2"
                                                style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                {{ number_format($totalVarianceQty, 2) }}
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

            if (!content) {
                return alert('Nothing to print');
            }

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

            doc.write('<html><head><title>Loss Prevention Report</title>' + style + '</head><body>');
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
