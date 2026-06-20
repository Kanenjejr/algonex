@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Ledger Summary Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Ledger Summary</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>{{ \Carbon\Carbon::now()->format('l') }} , {{ \Carbon\Carbon::now()->toDateString() }}</strong>
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
            setInterval(change_time, 1000);
        }

        function change_time() {
            var d = new Date(),
                h = d.getHours(),
                m = d.getMinutes(),
                s = d.getSeconds();
            if (h > 24) h -= 24;
            document.getElementById('Hour').innerHTML = h + ':';
            document.getElementById('Minut').innerHTML = m + ':';
            document.getElementById('Second').innerHTML = s;
        }
        timedMsg();

        function printReceipt(ele) {
            var content = document.getElementById(ele);
            if (!content) return alert('Nothing to print');

            var pri = window.open('', '_blank', 'height=842,width=595');
            var doc = pri.document.open();
            var style = `<style>
            @page{ size:A4 landscape; margin:12mm; }
            *{ box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            html,body{ margin:0; padding:0; background:#fff; color:#000; font-family:Arial,sans-serif; font-size:12px; }
            table{ width:100%; border-collapse:collapse; }
            th,td{ border:1px solid #000; padding:5px; }
            a{ color:#000; text-decoration:none; }
            .no-print{ display:none; }
        </style>`;
            doc.write('<html><head><title>Ledger Summary</title>' + style + '</head><body>');
            doc.write(content.innerHTML);
            doc.write('</body></html>');
            doc.close();
            pri.focus();
            setTimeout(function() {
                pri.print();
            }, 400);
        }

        function exportTableToExcel(tableID, filename = '') {
            var tableSelect = document.getElementById(tableID);
            if (!tableSelect) return alert('Table not found.');
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            filename = filename ? filename + '.xls' : 'ledger_summary.xls';
            var downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            downloadLink.href = 'data:application/vnd.ms-excel, ' + tableHTML;
            downloadLink.download = filename;
            downloadLink.click();
        }
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
                            <div style="font-size:18px; font-weight:800;">General Ledger Summary</div>
                            <div class="ibox-tools" style="display:flex; gap:10px; align-items:center;">
                                <a onclick="window.history.back();" class="btn btn-secondary text-white"
                                    style="background:#6b7280;">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                                <a onclick="exportTableToExcel('ledgerSummaryTable', 'Ledger-Summary-{{ $year }}')"
                                    class="btn btn-primary text-white">
                                    <i class="fa fa-file-excel-o"></i> Export Excel
                                </a>
                                <a onclick="printReceipt('form1')" class="btn btn-primary text-white">
                                    <i class="fa fa-print"></i> Print
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="ibox-content" style="padding:18px;">
                        <form method="get" action="{{ route('ledger', ['year' => $year]) }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-2">
                                    <label><strong>Start Date</strong></label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ request('start_date', $start_date ?? '') }}">
                                </div>
                                <div class="col-md-2">
                                    <label><strong>End Date</strong></label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="{{ request('end_date', $end_date ?? '') }}">
                                </div>
                                <div class="col-md-2">
                                    <label><strong>Period</strong></label>
                                    <select name="period" class="form-control">
                                        <option value="ANNUAL" {{ ($period ?? 'ANNUAL') === 'ANNUAL' ? 'selected' : '' }}>
                                            Annual</option>
                                        <option value="H1" {{ ($period ?? '') === 'H1' ? 'selected' : '' }}>H1 Jan - Jun
                                        </option>
                                        <option value="H2" {{ ($period ?? '') === 'H2' ? 'selected' : '' }}>H2 Jul - Dec
                                        </option>
                                        <option value="Q1" {{ ($period ?? '') === 'Q1' ? 'selected' : '' }}>Q1</option>
                                        <option value="Q2" {{ ($period ?? '') === 'Q2' ? 'selected' : '' }}>Q2</option>
                                        <option value="Q3" {{ ($period ?? '') === 'Q3' ? 'selected' : '' }}>Q3</option>
                                        <option value="Q4" {{ ($period ?? '') === 'Q4' ? 'selected' : '' }}>Q4</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label><strong>Company Site</strong></label>
                                    <select name="company_site_id" class="form-control select2_demo_2">
                                        <option value="">-- All Company Sites --</option>
                                        @foreach ($companySites ?? collect() as $site)
                                            <option value="{{ $site->id }}"
                                                {{ (string) request('company_site_id', $selectedCompanySite ?? '') === (string) $site->id ? 'selected' : '' }}>
                                                {{ $site->company_name ?? ($site->name ?? 'Company Site') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label><strong>Company Unit</strong></label>
                                    <select name="company_unit_id" class="form-control select2_demo_2">
                                        <option value="">-- All Company Units --</option>
                                        @foreach ($companyUnits ?? collect() as $unit)
                                            <option value="{{ $unit->id }}"
                                                {{ (string) request('company_unit_id', $selectedCompanyUnit ?? '') === (string) $unit->id ? 'selected' : '' }}>
                                                {{ $unit->unit_name ?? ($unit->comp_unit_name ?? ($unit->name ?? 'Company Unit')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row" style="margin-top:12px;">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                                        Search</button>
                                    <a href="{{ route('ledger', ['year' => $year]) }}" class="btn btn-default">Reset</a>
                                    <span style="float:right; padding-top:8px;">
                                        <strong>Period:</strong> {{ $start_date ?? '-' }} to {{ $end_date ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="form1" class="ibox-content" style="padding:20px; background:#fff;">
                        <div
                            style="max-width:1300px; margin:0 auto; color:#000; font-family:Arial,sans-serif; font-size:13px; line-height:1.5;">
                            <div style="text-align:center; margin-bottom:15px;">
                                <h3 style="font-weight:800; margin:0;">GENERAL LEDGER SUMMARY</h3>
                                <div>For the period {{ $start_date ?? '-' }} to {{ $end_date ?? '-' }}</div>
                                @if (!empty($selectedCompanySiteName))
                                    <div><strong>Company Site:</strong> {{ $selectedCompanySiteName }}</div>
                                @endif
                                @if (!empty($selectedCompanyUnitName))
                                    <div><strong>Company Unit:</strong> {{ $selectedCompanyUnitName }}</div>
                                @endif
                            </div>

                            <div
                                style="margin-bottom:16px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px;">
                                <div style="font-weight:800; color:#173a7a; margin-bottom:8px;">Consolidated General Ledger
                                </div>
                                <div class="table-responsive">
                                    <table id="ledgerSummaryTable" style="width:100%; border-collapse:collapse;">
                                        <thead>
                                            <tr style="background:#f8fafc;">
                                                <th style="border:1px solid #d9e2f2; padding:8px;">#</th>
                                                <th style="border:1px solid #d9e2f2; padding:8px;">Accounting Code</th>
                                                <th style="border:1px solid #d9e2f2; padding:8px;">Accounting Description
                                                </th>
                                                <th style="border:1px solid #d9e2f2; padding:8px; text-align:right;">Total
                                                    Debit</th>
                                                <th style="border:1px solid #d9e2f2; padding:8px; text-align:right;">Total
                                                    Credit</th>
                                                <th style="border:1px solid #d9e2f2; padding:8px; text-align:right;">Debit
                                                    Balance</th>
                                                <th style="border:1px solid #d9e2f2; padding:8px; text-align:right;">Credit
                                                    Balance</th>
                                                <th class="no-print" style="border:1px solid #d9e2f2; padding:8px;">
                                                    Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalDebitMovement = 0;
                                                $totalCreditMovement = 0;
                                                $totalDebitBalance = 0;
                                                $totalCreditBalance = 0;
                                            @endphp

                                            @forelse(($consolidatedSummary ?? collect()) as $k => $row)
                                                @php
                                                    $debitBal =
                                                        (float) ($row->debit ??
                                                            (($row->balance ?? 0) > 0 ? abs($row->balance) : 0));
                                                    $creditBal =
                                                        (float) ($row->credit ??
                                                            (($row->balance ?? 0) < 0 ? abs($row->balance) : 0));
                                                    $totalDebitMovement += (float) ($row->debit_total ?? 0);
                                                    $totalCreditMovement += (float) ($row->credit_total ?? 0);
                                                    $totalDebitBalance += $debitBal;
                                                    $totalCreditBalance += $creditBal;

                                                    $detailParams = [
                                                        'year' => $year,
                                                        'accountId' =>
                                                            $row->account_id ?: $row->accounting_code_6 ?? '',
                                                        'period' => $period ?? 'ANNUAL',
                                                        'start_date' => $start_date ?? null,
                                                        'end_date' => $end_date ?? null,
                                                        'company_site_id' => request(
                                                            'company_site_id',
                                                            $selectedCompanySite ?? null,
                                                        ),
                                                        'company_unit_id' => request(
                                                            'company_unit_id',
                                                            $selectedCompanyUnit ?? null,
                                                        ),
                                                    ];
                                                @endphp
                                                <tr>
                                                    <td style="border:1px solid #d9e2f2; padding:8px;">{{ $k + 1 }}
                                                    </td>
                                                    <td style="border:1px solid #d9e2f2; padding:8px;">
                                                        {{ $row->accounting_code_6 ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:8px;">
                                                        {{ $row->accounting_name_6 ?? '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                        {{ number_format($row->debit_total ?? 0, 2) }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                        {{ number_format($row->credit_total ?? 0, 2) }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                        {{ $debitBal > 0 ? number_format($debitBal, 2) : '-' }}</td>
                                                    <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                        {{ $creditBal > 0 ? number_format($creditBal, 2) : '-' }}</td>
                                                    <td class="no-print" style="border:1px solid #d9e2f2; padding:8px;">
                                                        <a href="{{ route('ledger.details', array_filter($detailParams)) }}"
                                                            class="btn btn-sm btn-info">Details</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8"
                                                        style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                        No approved transactions found.</td>
                                                </tr>
                                            @endforelse

                                            <tr style="font-weight:bold; background:#f8fafc;">
                                                <td colspan="3"
                                                    style="border:1px solid #d9e2f2; padding:8px; text-align:right;">TOTAL
                                                </td>
                                                <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                    {{ number_format($totalDebitMovement, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                    {{ number_format($totalCreditMovement, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                    {{ number_format($totalDebitBalance, 2) }}</td>
                                                <td style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                    {{ number_format($totalCreditBalance, 2) }}</td>
                                                <td class="no-print" style="border:1px solid #d9e2f2; padding:8px;"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @foreach ($companySummaries ?? collect() as $i => $company)
                                <div
                                    style="margin-bottom:16px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:#fbfcff;">
                                    <div style="font-weight:800; color:#173a7a; margin-bottom:8px;">
                                        General Ledger - {{ $company->company_name ?? 'Company Site' }}
                                    </div>
                                    <div class="table-responsive">
                                        <table style="width:100%; border-collapse:collapse;">
                                            <thead>
                                                <tr style="background:#f8fafc;">
                                                    <th style="border:1px solid #d9e2f2; padding:8px;">#</th>
                                                    <th style="border:1px solid #d9e2f2; padding:8px;">Accounting Code</th>
                                                    <th style="border:1px solid #d9e2f2; padding:8px;">Accounting
                                                        Description</th>
                                                    <th style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                        Debit Balance</th>
                                                    <th style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                        Credit Balance</th>
                                                    <th class="no-print" style="border:1px solid #d9e2f2; padding:8px;">
                                                        Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($company->rows ?? collect()) as $k => $row)
                                                    @php
                                                        $debitBal =
                                                            (float) ($row->debit ??
                                                                (($row->balance ?? 0) > 0 ? abs($row->balance) : 0));
                                                        $creditBal =
                                                            (float) ($row->credit ??
                                                                (($row->balance ?? 0) < 0 ? abs($row->balance) : 0));
                                                        $detailParams = [
                                                            'year' => $year,
                                                            'accountId' =>
                                                                $row->account_id ?: $row->accounting_code_6 ?? '',
                                                            'period' => $period ?? 'ANNUAL',
                                                            'start_date' => $start_date ?? null,
                                                            'end_date' => $end_date ?? null,
                                                            'company_site_id' =>
                                                                $company->company_id ?? request('company_site_id'),
                                                            'company_unit_id' => request(
                                                                'company_unit_id',
                                                                $selectedCompanyUnit ?? null,
                                                            ),
                                                        ];
                                                    @endphp
                                                    <tr>
                                                        <td style="border:1px solid #d9e2f2; padding:8px;">
                                                            {{ $k + 1 }}</td>
                                                        <td style="border:1px solid #d9e2f2; padding:8px;">
                                                            {{ $row->accounting_code_6 ?? '-' }}</td>
                                                        <td style="border:1px solid #d9e2f2; padding:8px;">
                                                            {{ $row->accounting_name_6 ?? '-' }}</td>
                                                        <td
                                                            style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                            {{ $debitBal > 0 ? number_format($debitBal, 2) : '-' }}</td>
                                                        <td
                                                            style="border:1px solid #d9e2f2; padding:8px; text-align:right;">
                                                            {{ $creditBal > 0 ? number_format($creditBal, 2) : '-' }}</td>
                                                        <td class="no-print"
                                                            style="border:1px solid #d9e2f2; padding:8px;">
                                                            <a href="{{ route('ledger.details', array_filter($detailParams)) }}"
                                                                class="btn btn-sm btn-info">Details</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6"
                                                            style="border:1px solid #d9e2f2; padding:12px; text-align:center;">
                                                            No approved transactions found for this company.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                            <div style="margin-top:18px; text-align:center; font-size:11px; color:#666;">
                                Printed: {{ now()->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
