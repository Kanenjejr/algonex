@extends('layouts.AdminMaster')
@section('content')
    @php
        $pageTitle = 'Cash Flow Report Information';
        $breadcrumbTitle = 'Cash Flow Report';
        $formAction = route('cashflow', ['year' => $year ?? request()->route('year')]);
        $reportTitle = 'CASH FLOW STATEMENT';
        $exportName = str_replace(' ', '-', $breadcrumbTitle) . '-' . ($year ?? date('Y'));
    @endphp
    @php
        $year = (int) ($year ?? (request()->route('year') ?? date('Y')));
        $start_date = $start_date ?? request('start_date', $year . '-01-01');
        $end_date = $end_date ?? request('end_date', $year . '-12-31');
        $prev_start_date = $prev_start_date ?? \Carbon\Carbon::parse($start_date)->copy()->subYear()->toDateString();
        $prev_end_date = $prev_end_date ?? \Carbon\Carbon::parse($end_date)->copy()->subYear()->toDateString();
        $selectedCompanyId =
            $selectedCompany ?? ($selectedCompanySite ?? request('company_id', request('company_site_id')));
        $selectedCompanyUnitId = $selectedCompanyUnit ?? request('company_unit_id');
        $companyList = $companies ?? ($companySites ?? collect());
        $unitList = $companyUnits ?? collect();
        $reportCo = $reportCompany ?? ($holdingCompany ?? null);
        $current = $current ?? [];
        $previous = $previous ?? [];
        $notes = $notes ?? [];
        $shareholders = $shareholders ?? [];

        $fmt = function ($v) {
            $v = round((float) ($v ?? 0), 2);
            if (abs($v) < 0.005) {
                return '0.00';
            }
            return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
        };

        $get = function ($source, $key) {
            if (is_array($source)) {
                return $source[$key] ?? 0;
            }
            if (is_object($source)) {
                return $source->{$key} ?? 0;
            }
            return 0;
        };

        $cur = function ($key) use ($current, $get) {
            return $get($current ?? [], $key);
        };

        $prev = function ($key) use ($previous, $get) {
            return $get($previous ?? [], $key);
        };

        $noteRows = function ($noteNo) use ($notes) {
            $n = (string) $noteNo;
            if (isset($notes[$n])) {
                return collect($notes[$n]);
            }
            if (isset($notes[(int) $noteNo])) {
                return collect($notes[(int) $noteNo]);
            }
            return collect();
        };

        $rowGet = function ($row, $key, $default = null) {
            if (is_array($row)) {
                return $row[$key] ?? $default;
            }
            if (is_object($row)) {
                return $row->{$key} ?? $default;
            }
            return $default;
        };

        $logoPath = function ($path) {
            if (empty($path)) {
                return null;
            }
            return str_starts_with((string) $path, 'http') ? $path : asset($path);
        };

        $signatureUrl = $logoPath(
            optional($reportCo)->signature ?? (optional($holdingCompany ?? null)->signature ?? null),
        );
        $stampUrl = $logoPath(optional($reportCo)->stamp ?? (optional($holdingCompany ?? null)->stamp ?? null));
    @endphp

    <style>
        .fs-wrap {
            max-width: 1120px;
            margin: 0 auto;
            background: #fff;
            color: #000;
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.25;
        }

        .fs-page {
            width: 100%;
            background: #fff;
            margin: 0 auto 18px auto;
            padding: 0 0 10px 0;
            page-break-after: always;
            break-after: page;
        }

        .fs-page:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .header-img {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
            display: block;
        }

        .fs-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0;
        }

        .fs-subtitle {
            text-align: center;
            font-weight: bold;
            margin: 2px 0;
        }

        .fs-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .fs-table th,
        .fs-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: top;
        }

        .fs-table th {
            font-weight: bold;
        }

        .no-border {
            border: 0 !important;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .section-row td {
            font-weight: bold;
            text-transform: uppercase;
            background: #f3f3f3;
        }

        .sub-total td {
            font-weight: bold;
            border-bottom: 2px solid #000 !important;
        }

        .grand-total td {
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 3px double #000 !important;
        }

        .note-link {
            color: #0069d9;
            font-weight: bold;
            text-decoration: underline;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .sign-table td {
            border: 0 !important;
            padding: 4px;
            vertical-align: bottom;
        }

        .sign-img {
            max-height: 70px;
            max-width: 260px;
            object-fit: contain;
        }

        .stamp-img {
            max-height: 95px;
            max-width: 230px;
            object-fit: contain;
        }

        .policy-text {
            text-align: center;
            font-size: 11px;
            margin-top: 8px;
        }

        .filter-box {
            margin: 0 0 15px 0;
            padding: 12px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .note-title {
            font-weight: bold;
            text-transform: uppercase;
            background: #efefef;
        }

        .nowrap {
            white-space: nowrap;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            html,
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .fs-wrap {
                max-width: none !important;
                width: 100% !important;
                font-size: 10px !important;
                line-height: 1.15 !important;
            }

            .fs-page {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            .fs-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }

            .fs-table {
                width: 100% !important;
                border-collapse: collapse !important;
                table-layout: fixed !important;
            }

            .fs-table th,
            .fs-table td {
                border: 1px solid #000 !important;
                padding: 2px 3px !important;
                color: #000 !important;
            }

            tr,
            td,
            th {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .header-img {
                max-height: 120px !important;
            }

            .sign-img {
                max-height: 55px !important;
            }

            .stamp-img {
                max-height: 75px !important;
            }

            a {
                color: #000 !important;
                text-decoration: none !important;
            }
        }
    </style>

    <script>
        function printReceipt(ele) {
            window.print();
        }

        function exportTableToExcel(containerID, filename = 'report') {
            var container = document.getElementById(containerID);
            if (!container) {
                alert('Nothing to export');
                return;
            }
            var html = '<html><head><meta charset="UTF-8"></head><body>' + container.innerHTML + '</body></html>';
            var blob = new Blob(['\ufeff', html], {
                type: 'application/vnd.ms-excel'
            });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename + '.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>


    <div class="row wrapper border-bottom white-bg page-heading no-print">
        <div class="col-lg-9">
            <h2>{{ $pageTitle }}</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li><span style="font-size:25px"
                    class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>{{ $breadcrumbTitle }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-3 text-right">
            <h2>{{ \Carbon\Carbon::now()->format('l') }}, {{ \Carbon\Carbon::now()->toDateString() }}</h2>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-success no-print">
                        <div class="ibox-tools">
                            <a onclick="exportTableToExcel('printArea', '{{ $exportName }}')"
                                class="btn btn-primary text-white"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                            <a onclick="printReceipt('printArea')" class="btn btn-primary text-white"><i
                                    class="fa fa-print"></i> Print</a>
                            <a class="collapse-link"><i class="fa fa-chevron-up"></i></a><a class="close-link"><i
                                    class="fa fa-times"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="filter-box no-print">
                            <form method="get" action="{{ $formAction }}">
                                <div class="row">
                                    <div class="col-md-2"><label><strong>Start Date</strong></label><input type="date"
                                            name="start_date" class="form-control" value="{{ $start_date }}"></div>
                                    <div class="col-md-2"><label><strong>End Date</strong></label><input type="date"
                                            name="end_date" class="form-control" value="{{ $end_date }}"></div>
                                    <div class="col-md-3"><label><strong>Company Site</strong></label><select
                                            name="company_id" class="form-control select2_demo_2">
                                            <option value="">-- All Company Sites --</option>
                                            @foreach ($companyList as $site)
                                                <option value="{{ $site->id }}"
                                                    {{ (string) $selectedCompanyId === (string) $site->id ? 'selected' : '' }}>
                                                    {{ $site->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3"><label><strong>Company Unit</strong></label><select
                                            name="company_unit_id" class="form-control select2_demo_2">
                                            <option value="">-- All Company Units --</option>
                                            @foreach ($unitList as $unit)
                                                <option value="{{ $unit->id }}"
                                                    {{ (string) $selectedCompanyUnitId === (string) $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->unit_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2" style="padding-top:24px"><button class="btn btn-primary"><i
                                                class="fa fa-search"></i> Search</button><a href="{{ $formAction }}"
                                            class="btn btn-default">Reset</a></div>
                                </div>
                            </form>
                        </div>

                        <div id="printArea" class="fs-wrap">
                            <div class="fs-page">
                                <div class="fs-header">
                                    <img src="{{ asset('img/header.png') }}"
                                        style="width:100%; max-height:100%; object-fit:contain;" alt="Company Header"
                                        class="payroll-header-img header-img">
                                    <div class="fs-subtitle">
                                        {{ strtoupper(optional($reportCo)->company_name ?? 'COMPANY') }} @if (optional($reportCo)->TIN)
                                            TIN {{ optional($reportCo)->TIN }}
                                            @endif @if (optional($reportCo)->city)
                                                , {{ strtoupper(optional($reportCo)->city) }}
                                            @endif
                                    </div>
                                </div>

                                <div class="fs-title">{{ $reportTitle }} FOR THE PERIOD ENDED
                                    {{ \Carbon\Carbon::parse($end_date)->format('d F Y') }}</div>
                                <table class="fs-table">
                                    <colgroup>
                                        <col style="width:64%">
                                        <col style="width:18%">
                                        <col style="width:18%">
                                    </colgroup>
                                    <tr>
                                        <th></th>
                                        <th class="text-right">
                                            {{ \Carbon\Carbon::parse($end_date)->format('d.m.Y') }}<br>TSHS</th>
                                        <th class="text-right">
                                            {{ \Carbon\Carbon::parse($prev_end_date)->format('d.m.Y') }}<br>TSHS</th>
                                    </tr>
                                    <tr class="section-row">
                                        <td colspan="3">1.00 Cash flow from operating activities</td>
                                    </tr>
                                    <tr>
                                        <td>Profit / (Loss) before tax</td>
                                        <td class="text-right">{{ $fmt($cur('profit_before_tax')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('profit_before_tax')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Add: Depreciation</td>
                                        <td class="text-right">{{ $fmt($cur('depreciation')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('depreciation')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Add: Interest Expenses</td>
                                        <td class="text-right">{{ $fmt($cur('finance_cost')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('finance_cost')) }}</td>
                                    </tr>
                                    <tr class="sub-total">
                                        <td>Operating profit before working capital changes</td>
                                        <td class="text-right">{{ $fmt($cur('operating_before_wc')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('operating_before_wc')) }}</td>
                                    </tr>
                                    <tr class="section-row">
                                        <td colspan="3">2.00 Working Capital Changes</td>
                                    </tr>
                                    <tr>
                                        <td>(Increase) / Decrease in Inventories</td>
                                        <td class="text-right">{{ $fmt($cur('change_inventories')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_inventories')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>(Increase) / Decrease in Accounts Receivables</td>
                                        <td class="text-right">{{ $fmt($cur('change_receivables')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_receivables')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Increase / (Decrease) in Creditors</td>
                                        <td class="text-right">{{ $fmt($cur('change_creditors')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_creditors')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Increase / (Decrease) in Provisions</td>
                                        <td class="text-right">{{ $fmt($cur('change_provisions')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_provisions')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>(Increase) / Decrease in Other Current Assets</td>
                                        <td class="text-right">{{ $fmt($cur('change_other_current_assets')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_other_current_assets')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Increase / (Decrease) in Other Current Liabilities</td>
                                        <td class="text-right">{{ $fmt($cur('change_other_current_liabilities')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_other_current_liabilities')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Increase / (Decrease) in Short Term Loans</td>
                                        <td class="text-right">{{ $fmt($cur('change_short_term_loans')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_short_term_loans')) }}</td>
                                    </tr>
                                    <tr class="sub-total">
                                        <td>Changes in working capital</td>
                                        <td class="text-right">{{ $fmt($cur('changes_working_capital')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('changes_working_capital')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Cash flow from Operations</td>
                                        <td class="text-right">{{ $fmt($cur('cash_from_operations')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('cash_from_operations')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Taxation</td>
                                        <td class="text-right">{{ $fmt(-abs($cur('income_tax'))) }}</td>
                                        <td class="text-right">{{ $fmt(-abs($prev('income_tax'))) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Interest Paid</td>
                                        <td class="text-right">{{ $fmt(-abs($cur('finance_cost'))) }}</td>
                                        <td class="text-right">{{ $fmt(-abs($prev('finance_cost'))) }}</td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Net Cash flow used for operating activities</td>
                                        <td class="text-right">{{ $fmt($cur('net_cash_operating')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('net_cash_operating')) }}</td>
                                    </tr>
                                    <tr class="section-row">
                                        <td colspan="3">3.00 Cash flow from investing activities</td>
                                    </tr>
                                    <tr>
                                        <td>Purchases of Property, Plant & Equipment</td>
                                        <td class="text-right">{{ $fmt(-abs($cur('ppe_purchases'))) }}</td>
                                        <td class="text-right">{{ $fmt(-abs($prev('ppe_purchases'))) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Increase / (Decrease) in Financial Investment</td>
                                        <td class="text-right">{{ $fmt($cur('financial_investment_change')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('financial_investment_change')) }}</td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Net Cash flow used investing activities</td>
                                        <td class="text-right">{{ $fmt($cur('net_cash_investing')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('net_cash_investing')) }}</td>
                                    </tr>
                                    <tr class="section-row">
                                        <td colspan="3">4.00 Cash flow from financing activities</td>
                                    </tr>
                                    <tr>
                                        <td>Issued and fully paid shares / Capital Introduced</td>
                                        <td class="text-right">{{ $fmt($cur('capital_introduced')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('capital_introduced')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Increase / (Decrease) in Long Term Loans</td>
                                        <td class="text-right">{{ $fmt($cur('change_long_term_loans')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_long_term_loans')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Increase / (Decrease) Due to Related Parties</td>
                                        <td class="text-right">{{ $fmt($cur('change_related_parties')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('change_related_parties')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Dividend Paid</td>
                                        <td class="text-right">{{ $fmt(-abs($cur('dividend_paid'))) }}</td>
                                        <td class="text-right">{{ $fmt(-abs($prev('dividend_paid'))) }}</td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Net Cash flow from financing activities</td>
                                        <td class="text-right">{{ $fmt($cur('net_cash_financing')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('net_cash_financing')) }}</td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Changes in cash and cash equivalents for the period</td>
                                        <td class="text-right">{{ $fmt($cur('cash_change')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('cash_change')) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Cash and cash equivalents at start</td>
                                        <td class="text-right">{{ $fmt($cur('cash_start')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('cash_start')) }}</td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Cash and cash equivalents at close</td>
                                        <td class="text-right">{{ $fmt($cur('cash_close')) }}</td>
                                        <td class="text-right">{{ $fmt($prev('cash_close')) }}</td>
                                    </tr>
                                </table>
                                <div class="bold" style="margin-top:10px;">Certified True and Correct</div>
                                <table class="sign-table">
                                    <tr>
                                        <td style="width:42%;height:90px;">
                                            @if ($signatureUrl)
                                                <img src="{{ $signatureUrl }}" class="sign-img"><br>
                                            @endif
                                            ......................................<br>Managing Director
                                        </td>
                                        <td style="width:18%;">{{ \Carbon\Carbon::now()->format('d.m.Y') }}<br>Date</td>
                                        <td style="width:40%;text-align:right;">
                                            @if ($stampUrl)
                                                <img src="{{ $stampUrl }}" class="stamp-img">
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <div class="policy-text">The Statement of Accounting Policies and the Accompanying notes
                                    form part of the financial Statement</div>
                            </div>
                            @php
                                $noteDefinitions = $noteDefinitions ?? [
                                    '2' => 'Sales',
                                    '3' => 'Cost of Sales',
                                    '4' => 'Administrative & Establishment Expenses',
                                    '5' => 'Selling & Distribution Expenses',
                                    '6' => 'Finance Cost',
                                    '7' => 'Property, Plant & Equipment',
                                    '8' => 'Inventories, Prepayments, Loans and Advances',
                                    '9' => 'Accounts Receivables',
                                    '10' => 'Cash and Cash Equivalents',
                                    '11' => 'Trade Creditors, Liabilities and Accruals',
                                    '12' => 'Term Loan / Bank Facilities',
                                    '13' => 'Employees Cost',
                                    '14' => 'Professional Fees',
                                    '15' => 'Income Tax Expenses',
                                    '16' => 'Capital Commitments',
                                    '17' => 'Contingent Liabilities',
                                    '18' => 'Comparative Information',
                                    '19' => 'Events After Reporting Date',
                                    '20' => 'Financial Assets and Liabilities',
                                    '21' => 'Financial Instruments',
                                ];
                            @endphp
                            <div class="fs-page" id="notes-section">
                                <div class="fs-header">
                                    <img src="{{ asset('img/header.png') }}"
                                        style="width:100%; max-height:100%; object-fit:contain;" alt="Company Header"
                                        class="payroll-header-img header-img">
                                    <div class="fs-subtitle">
                                        {{ strtoupper(optional($reportCo)->company_name ?? 'COMPANY') }} @if (optional($reportCo)->TIN)
                                            TIN {{ optional($reportCo)->TIN }}
                                            @endif @if (optional($reportCo)->city)
                                                , {{ strtoupper(optional($reportCo)->city) }}
                                            @endif
                                    </div>
                                </div>

                                <div class="fs-title">NOTES TO THE FINANCIAL STATEMENTS FOR THE YEAR ENDED
                                    {{ \Carbon\Carbon::parse($end_date)->format('d F Y') }}</div>
                                <table class="fs-table">
                                    <colgroup>
                                        <col style="width:12%">
                                        <col style="width:48%">
                                        <col style="width:20%">
                                        <col style="width:20%">
                                    </colgroup>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th class="text-right">
                                            {{ \Carbon\Carbon::parse($end_date)->format('d.m.Y') }}<br>TSHS</th>
                                        <th class="text-right">
                                            {{ \Carbon\Carbon::parse($prev_end_date)->format('d.m.Y') }}<br>TSHS</th>
                                    </tr>
                                    @foreach ($noteDefinitions as $noteNo => $noteTitle)
                                        <tr id="note{{ $noteNo }}" class="note-title">
                                            <td>Note {{ $noteNo }}</td>
                                            <td colspan="3">{{ $noteTitle }}</td>
                                        </tr>
                                        @php $rows = $noteRows($noteNo); @endphp
                                        @forelse($rows as $r)
                                            <tr>
                                                <td>{{ $rowGet($r, 'code', $rowGet($r, 'accounting_code_6', '')) }}</td>
                                                <td>{{ $rowGet($r, 'description', $rowGet($r, 'accounting_name_6', '')) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ $fmt($rowGet($r, 'current', $rowGet($r, 'amount', $rowGet($r, 'debit', 0)))) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ $fmt($rowGet($r, 'previous', $rowGet($r, 'prev_amount', $rowGet($r, 'prev_debit', 0)))) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td></td>
                                                <td>Available balance</td>
                                                <td class="text-right">0.00</td>
                                                <td class="text-right">0.00</td>
                                            </tr>
                                        @endforelse
                                        <tr class="sub-total">
                                            <td></td>
                                            <td>Total {{ $noteTitle }}</td>
                                            <td class="text-right">{{ $fmt($cur('note_' . $noteNo . '_total')) }}</td>
                                            <td class="text-right">{{ $fmt($prev('note_' . $noteNo . '_total')) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
