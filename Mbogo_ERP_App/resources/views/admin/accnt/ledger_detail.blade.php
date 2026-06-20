@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Ledger Details Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('accounting') }}">Accounting And Finance</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Ledger Details</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php use Carbon\Carbon;
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

    <div class="wrapper wrapper-content animated fadeInRight" style="padding:15px;">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox"
                    style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">

                    <div class="ibox-title bg-success"
                        style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff; padding:14px 16px; border-bottom:4px solid #b08a2e;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <div style="font-size:18px; font-weight:800;">Ledger Details</div>
                            <div class="ibox-tools" style="display:flex; gap:10px; align-items:center;">
                                <a href="{{ route('ledger', ['year' => $year, 'period' => $period]) }}"
                                    class="btn btn-secondary text-white"
                                    style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#6b7280;">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                                <a onclick="printReceipt('form1')" class="btn btn-primary text-white"
                                    style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#1f4fa3;">
                                    <i class="fa fa-print"></i> Print
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="ibox-content" style="padding:18px;">
                        <form method="get"
                            action="{{ route('ledger.details', ['year' => $year, 'accountId' => $accountId]) }}"
                            class="mb-3">
                            @if (!empty($companyId))
                                <input type="hidden" name="company_id" value="{{ $companyId }}">
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <label><strong>Period</strong></label>
                                    <select name="period" class="form-control">
                                        <option value="ANNUAL" {{ $period === 'ANNUAL' ? 'selected' : '' }}>Annual</option>
                                        <option value="H1" {{ $period === 'H1' ? 'selected' : '' }}>Semi-Annual 1
                                            (January - June)</option>
                                        <option value="H2" {{ $period === 'H2' ? 'selected' : '' }}>Semi-Annual 2 (July
                                            - December)</option>
                                        <option value="Q1" {{ $period === 'Q1' ? 'selected' : '' }}>Quarter 1 (January -
                                            March)</option>
                                        <option value="Q2" {{ $period === 'Q2' ? 'selected' : '' }}>Quarter 2 (April -
                                            June)</option>
                                        <option value="Q3" {{ $period === 'Q3' ? 'selected' : '' }}>Quarter 3 (July -
                                            September)</option>
                                        <option value="Q4" {{ $period === 'Q4' ? 'selected' : '' }}>Quarter 4 (October
                                            -
                                            December)</option>
                                    </select>
                                </div>
                                <div class="col-md-4" style="padding-top:25px;">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                                <div class="col-md-4" style="padding-top:32px; text-align:right;">
                                    <strong>Period:</strong> {{ $start_date }} to {{ $end_date }}
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="form1" class="ibox-content" style="padding:20px; background:#fff;">
                        <div
                            style="max-width:1300px; margin:0 auto; color:#000; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:13px; line-height:1.55;">

                            <div
                                style="margin-bottom:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                <div style="font-weight:800; color:#173a7a;">Accounting Code: {{ $accountingCode ?? '-' }}
                                </div>
                                <div style="margin-top:4px;">Description: {{ $accountingName ?? '-' }}</div>
                                @if (!empty($companyName))
                                    <div style="margin-top:4px;">Company: {{ $companyName }}</div>
                                @endif
                            </div>

                            <div
                                style="margin-bottom:16px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                <div style="font-weight:800; color:#173a7a; margin-bottom:8px;">Transaction Details</div>

                                <div class="table-responsive">
                                    <table style="width:100%; border-collapse:collapse;">
                                        <thead>
                                            <tr style="background:#f8fafc;">
                                                <th style="border:1px solid #d9e2f2; padding:10px;">#</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Date</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Accounting Code</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Accounting Description
                                                </th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Sub Accounting Code</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Sub Accounting
                                                    Description</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Reference</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Memo</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Section</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Corresponding Entry</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Debit
                                                </th>
                                                <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">Credit
                                                </th>
                                                <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    Running Balance</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Company</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Work Point</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($txRows as $k => $r)
                                                @php
                                                    $running = (float) $r->running;
                                                    $displayBalance = number_format(abs($running), 2);
                                                    $balanceSide = $running >= 0 ? 'Dr' : 'Cr';

                                                    if (($r->approved ?? '') === 'approved') {
                                                        $statusLabel = 'approved';
                                                        $statusClass = 'badge badge-success';
                                                    } elseif (($r->verified ?? '') === 'verified') {
                                                        $statusLabel = 'verified';
                                                        $statusClass = 'badge badge-primary';
                                                    } else {
                                                        $statusLabel = 'pending';
                                                        $statusClass = 'badge badge-warning';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">{{ $k + 1 }}
                                                    </td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ optional($r->trans_date)->format('Y-m-d') }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->accounting_code_6 ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->accounting_name_6 ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->sub_accounting_code_8 ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->sub_accounting_name_8 ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->reference ?? '' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->memo ?? '' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->section_code ?? '-' }} - {{ $r->section_name ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        @if (!empty($r->corresponding_entries) && count($r->corresponding_entries))
                                                            @foreach ($r->corresponding_entries as $entry)
                                                                <div style="margin-bottom:4px;">
                                                                    <strong>{{ strtoupper($entry['type']) }}</strong> :
                                                                    {{ $entry['sub_accounting_code_8'] ?? '-' }}
                                                                    -
                                                                    {{ $entry['sub_accounting_name_8'] ?? '-' }}
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                        {{ $r->debit ? number_format($r->debit, 2) : '' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                        {{ $r->credit ? number_format($r->credit, 2) : '' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                        {{ $displayBalance }} {{ $balanceSide }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->company_name ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;">
                                                        {{ $r->workpoint_name ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:10px;"><span
                                                            class="{{ $statusClass }}">{{ ucfirst($statusLabel) }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="16"
                                                        style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                        No approved transactions found for this account in the selected
                                                        period.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr style="background:#f8fafc;">
                                                <th colspan="10"
                                                    style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    Totals</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($debitTotal, 2) }}</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    {{ number_format($creditTotal, 2) }}</th>
                                                <th style="border:1px solid #d9e2f2; padding:10px; text-align:right;">
                                                    <strong>{{ number_format(abs($finalBalance), 2) }}
                                                        {{ $finalSide === 'debit' ? 'Dr' : 'Cr' }}</strong>
                                                </th>
                                                <th colspan="3" style="border:1px solid #d9e2f2; padding:10px;"></th>
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
                    font-size:11px;
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

                doc.write('<html><head><title>Ledger Detail Print</title>' + style + '</head><body>');
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
