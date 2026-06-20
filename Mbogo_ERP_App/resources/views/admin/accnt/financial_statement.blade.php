@extends('layouts.AdminMaster')
@section('content')
    @php
        $fmt = function ($v) {
            $v = round((float) ($v ?? 0), 2);
            return $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
        };
        $fmtNeg = function ($v) {
            $v = round((float) ($v ?? 0), 2);
            return $v == 0 ? '0.00' : '(' . number_format(abs($v), 2) . ')';
        };
        $fileUrl = function ($path) {
            if (empty($path)) {
                return null;
            }
            return str_starts_with($path, 'http') ? $path : asset($path);
        };
        $holding = $holdingCompany ?? \App\Models\CompanySite::find(1);
        $reportCo = $reportCompany ?? $holding;
        $signatureUrl = $fileUrl(optional($reportCo)->signature ?: optional($holding)->signature);
        $stampUrl = $fileUrl(optional($reportCo)->stamp ?: optional($holding)->stamp);
        $assetParams =
            $assetParams ??
            array_filter(
                [
                    'start_date' => $start_date ?? request('start_date'),
                    'end_date' => $end_date ?? request('end_date'),
                    'company_id' => $selectedCompany ?? request('company_id'),
                    'company_unit_id' => $selectedCompanyUnit ?? request('company_unit_id'),
                ],
                fn($v) => $v !== null && $v !== '',
            );
        $periodTitle = \Carbon\Carbon::parse($end_date)->format('d F Y');
        $currentCol = \Carbon\Carbon::parse($end_date)->format('d.m.Y');
        $previousCol = \Carbon\Carbon::parse($previous_end_date ?? \Carbon\Carbon::parse($end_date)->subYear())->format(
            'd.m.Y',
        );
        $companyTitle = strtoupper(optional($reportCo)->company_name ?? 'GENERAL COMPANY / COMBINED COMPANY');
        $tinLine = optional($reportCo)->TIN ? ' TIN ' . optional($reportCo)->TIN : '';
        $addressLine = trim(
            ($companyTitle ?: '') . $tinLine . ', ' . (optional($reportCo)->city ? optional($reportCo)->city : ''),
        );

        $amountByPrefixes = function ($rows, array $prefixes, string $side = 'debit') {
            $sum = 0.0;
            foreach (collect($rows ?? []) as $r) {
                $code = (string) ($r->accounting_code_6 ?? '');
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($code, (string) $prefix)) {
                        $sum += $side === 'credit' ? (float) ($r->credit ?? 0) : (float) ($r->debit ?? 0);
                        break;
                    }
                }
            }
            return round($sum, 2);
        };

        $comparativeRows = function (array $prefixes, string $side = 'debit') use ($currentRows, $previousRows) {
            $curr = collect($currentRows ?? [])
                ->filter(function ($r) use ($prefixes, $side) {
                    $code = (string) ($r->accounting_code_6 ?? '');
                    foreach ($prefixes as $prefix) {
                        if (str_starts_with($code, (string) $prefix)) {
                            return (float) ($side === 'credit' ? $r->credit ?? 0 : $r->debit ?? 0) != 0.0;
                        }
                    }
                    return false;
                })
                ->keyBy('accounting_code_6');
            $prev = collect($previousRows ?? [])
                ->filter(function ($r) use ($prefixes, $side) {
                    $code = (string) ($r->accounting_code_6 ?? '');
                    foreach ($prefixes as $prefix) {
                        if (str_starts_with($code, (string) $prefix)) {
                            return (float) ($side === 'credit' ? $r->credit ?? 0 : $r->debit ?? 0) != 0.0;
                        }
                    }
                    return false;
                })
                ->keyBy('accounting_code_6');
            return $curr
                ->keys()
                ->merge($prev->keys())
                ->unique()
                ->sort()
                ->map(function ($code) use ($curr, $prev, $side) {
                    $c = $curr->get($code);
                    $p = $prev->get($code);
                    return (object) [
                        'code' => $code,
                        'name' => optional($c)->accounting_name_6 ?: optional($p)->accounting_name_6 ?: $code,
                        'current' => (float) ($side === 'credit' ? optional($c)->credit : optional($c)->debit),
                        'previous' => (float) ($side === 'credit' ? optional($p)->credit : optional($p)->debit),
                    ];
                })
                ->values();
        };
    @endphp

    <div class="row wrapper border-bottom white-bg page-heading no-print">
        <div class="col-lg-9">
            <h2>Combined Financial Statements Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Combined Financial Statements</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong>{{ \Carbon\Carbon::now()->format('l') }} ,
                        {{ \Carbon\Carbon::now()->toDateString() }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-1">
            <h2>Time</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong>
                        <table>
                            <tr>
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong></li>
            </ol>
        </div>
    </div>

    <style id="financial-statement-print-style">
        .fs-report {
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.12;
            width: 100%;
        }

        .fs-sheet {
            max-width: 1120px;
            margin: 0 auto;
            background: #fff;
        }

        .fs-report table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 8px 0;
            table-layout: fixed;
            border: 1px solid #000;
        }

        .fs-report th,
        .fs-report td {
            border: 1px solid #000 !important;
            padding: 2px 4px;
            vertical-align: top;
            word-wrap: break-word;
            background: #fff;
        }

        .fs-report .no-border {
            border: 0 !important;
        }

        .fs-report .company-line {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .fs-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            background: #f1f1f1;
        }

        .group-row {
            font-weight: bold;
            background: #fafafa;
        }

        .sub-row {
            font-weight: bold;
        }

        .total-row th,
        .total-row td {
            font-weight: bold;
            border-bottom: 3px double #000 !important;
        }

        .subtotal-row th,
        .subtotal-row td {
            font-weight: bold;
            border-bottom: 1.5px solid #000 !important;
        }

        .text-right {
            text-align: right
        }

        .text-center {
            text-align: center
        }

        .text-left {
            text-align: left
        }

        .page-break {
            display: block;
            clear: both;
            page-break-before: always;
            break-before: page;
            margin: 0;
            height: 1px;
            line-height: 1px;
            font-size: 1px;
            overflow: hidden;
        }

        .signature-table td {
            height: 28px;
        }

        .sign-img {
            max-height: 44px;
            max-width: 160px
        }

        .stamp-img {
            max-height: 55px;
            max-width: 110px
        }

        .payroll-header-img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 95px;
            object-fit: contain;
            margin: 0 auto 4px auto;
        }

        .note-title {
            font-weight: bold;
            text-transform: uppercase;
            background: #efefef;
        }

        .note-link {
            color: #0069d9;
            font-weight: bold;
            text-decoration: none;
        }

        .fs-toolbar {
            margin-bottom: 12px;
        }

        .balance-alert {
            padding: 8px;
            border: 1px solid #d97706;
            background: #fff7ed;
            margin-top: 10px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-sizing: border-box !important;
            }

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
            }

            .no-print,
            .navbar,
            .footer,
            .page-heading,
            .sidebar,
            .sidebard-panel,
            .theme-config,
            .pace,
            .ibox-title {
                display: none !important;
            }

            .content,
            .wrapper,
            .wrapper-content,
            .ibox,
            .ibox-content,
            .row,
            .col-lg-12 {
                padding: 0 !important;
                margin: 0 !important;
                border: 0 !important;
                background: #fff !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .fs-report {
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 8.2pt !important;
                line-height: 1.12 !important;
                width: 198mm !important;
                max-width: 198mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
            }

            .fs-sheet {
                width: 198mm !important;
                max-width: 198mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .fs-report table {
                width: 100% !important;
                border-collapse: collapse !important;
                border-spacing: 0 !important;
                table-layout: fixed !important;
                margin: 0 0 3mm 0 !important;
                border: 1pt solid #000 !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                background: #fff !important;
            }

            .fs-report th,
            .fs-report td {
                border: 0.75pt solid #000 !important;
                padding: 0.75mm 1mm !important;
                vertical-align: top !important;
                background: #fff !important;
                color: #000 !important;
            }

            .fs-report tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .fs-report thead {
                display: table-header-group !important;
            }

            .fs-report tfoot {
                display: table-footer-group !important;
            }

            .fs-report .no-border {
                border: 0 !important;
            }

            .fs-title {
                font-size: 8.3pt !important;
                font-weight: bold !important;
                text-align: center !important;
                text-transform: uppercase !important;
            }

            .section-title,
            .note-title {
                font-weight: bold !important;
                background: #efefef !important;
            }

            .group-row,
            .sub-row {
                font-weight: bold !important;
            }

            .total-row th,
            .total-row td {
                font-weight: bold !important;
                border-bottom: 0.7mm double #000 !important;
            }

            .subtotal-row th,
            .subtotal-row td {
                font-weight: bold !important;
                border-bottom: 0.35mm solid #000 !important;
            }

            .page-break {
                display: block !important;
                clear: both !important;
                page-break-before: always !important;
                break-before: page !important;
                height: 1px !important;
                line-height: 1px !important;
                font-size: 1px !important;
                border: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }

            .payroll-header-img {
                width: 100% !important;
                max-height: 26mm !important;
                object-fit: contain !important;
                margin: 0 auto 1mm auto !important;
                display: block !important;
            }

            .signature-table td {
                height: 15mm !important;
            }

            .sign-img {
                max-height: 13mm !important;
                max-width: 45mm !important;
            }

            .stamp-img {
                max-height: 18mm !important;
                max-width: 38mm !important;
            }

            a {
                color: #000 !important;
                text-decoration: none !important;
            }
        }
    </style>

    <script>
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
            var c = document.getElementById(ele);
            if (!c) return alert('Nothing to print');

            var printCss = document.getElementById('financial-statement-print-style') ?
                document.getElementById('financial-statement-print-style').innerHTML :
                '';

            var html = '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
                '<base href="{{ url('/') }}/">' +
                '<title>Financial Statements</title>' +
                '<style>' + printCss + '</style>' +
                '</head><body><div id="financialStatementsBox" class="fs-report">' +
                c.innerHTML +
                '</div></body></html>';

            var w = window.open('', '_blank', 'width=900,height=1100,scrollbars=yes');
            w.document.open();
            w.document.write(html);
            w.document.close();
            w.focus();

            function doPrint() {
                w.focus();
                w.print();
            }
            var imgs = w.document.images,
                loaded = 0;
            if (!imgs.length) {
                setTimeout(doPrint, 500);
            } else {
                for (var i = 0; i < imgs.length; i++) {
                    if (imgs[i].complete) {
                        loaded++;
                    } else {
                        imgs[i].onload = imgs[i].onerror = function() {
                            loaded++;
                            if (loaded >= imgs.length) setTimeout(doPrint, 500);
                        };
                    }
                }
                if (loaded >= imgs.length) setTimeout(doPrint, 500);
            }
        }

        function exportFinancialStatements() {
            var box = document.getElementById('financialStatementsBox');
            if (!box) return alert('Report not found.');
            var html =
                '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>table{border-collapse:collapse;width:100%;}th,td{border:1px solid #000;padding:4px;font-family:Arial;font-size:10pt;} tr{page-break-inside:avoid;} .payroll-header-img{width:100%;max-height:95px;object-fit:contain;} .no-border{border:0!important}.text-right{text-align:right}.text-center{text-align:center}.section-title,.note-title{font-weight:bold;text-align:center;background:#eee}.total-row th,.total-row td{font-weight:bold;border-bottom:3px double #000!important}.subtotal-row th,.subtotal-row td{font-weight:bold;border-bottom:1px solid #000!important}</style></head><body>' +
                box.innerHTML + '</body></html>';
            var blob = new Blob(['\ufeff', html], {
                type: 'application/vnd.ms-excel'
            });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'Combined-Financial-Statements-{{ $year }}.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-success no-print">
                        <div class="ibox-tools">
                            <a onclick="exportFinancialStatements()" class="btn btn-primary text-white"><i
                                    class="fa fa-file-excel-o"></i> Export Excel</a>
                            <a onclick="printReceipt('financialStatementsBox')" class="btn btn-primary text-white"><i
                                    class="fa fa-print"></i> Print</a>
                            <a class="collapse-link"><i class="fa fa-chevron-up"></i></a><a class="close-link"><i
                                    class="fa fa-times"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content no-print">
                        <form method="get" action="{{ route('financialstatements', ['year' => $year]) }}">
                            <div class="row">
                                <div class="col-md-2"><label><strong>Start Date</strong></label><input type="date"
                                        name="start_date" class="form-control"
                                        value="{{ request('start_date', $start_date ?? '') }}"></div>
                                <div class="col-md-2"><label><strong>End Date</strong></label><input type="date"
                                        name="end_date" class="form-control"
                                        value="{{ request('end_date', $end_date ?? '') }}"></div>
                                <div class="col-md-3"><label><strong>Company Site</strong></label><select name="company_id"
                                        class="form-control select2_demo_2">
                                        <option value="">-- All Company Sites --</option>
                                        @foreach ($companies ?? ($companySites ?? collect()) as $site)
                                            <option value="{{ $site->id }}"
                                                {{ (string) ($selectedCompany ?? request('company_id')) === (string) $site->id ? 'selected' : '' }}>
                                                {{ $site->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label><strong>Company Unit</strong></label><select
                                        name="company_unit_id" class="form-control select2_demo_2">
                                        <option value="">-- All Company Units --</option>
                                        @foreach ($companyUnits ?? collect() as $unit)
                                            <option value="{{ $unit->id }}"
                                                {{ (string) ($selectedCompanyUnit ?? request('company_unit_id')) === (string) $unit->id ? 'selected' : '' }}>
                                                {{ $unit->unit_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2" style="padding-top:25px;"><button class="btn btn-primary"><i
                                            class="fa fa-search"></i> Search</button><a
                                        href="{{ route('financialstatements', ['year' => $year]) }}"
                                        class="btn btn-default">Reset</a></div>
                                <div class="col-md-12" style="margin-top:10px;"><a
                                        href="{{ route('assets.report', $assetParams) }}" class="btn btn-info"><i
                                            class="fa fa-link"></i> Open Note 7 Asset Report</a></div>
                            </div>
                        </form>
                        @if (abs($trialDifference ?? 0) > 0.01 || abs($previousTrialDifference ?? 0) > 0.01)
                            <div class="balance-alert"><strong>Warning:</strong> Debit and credit difference exists.
                                Current: {{ $fmt($trialDifference ?? 0) }}, Previous:
                                {{ $fmt($previousTrialDifference ?? 0) }}.</div>
                        @endif
                    </div>

                    <div id="financialStatementsBox" class="ibox-content fs-report">
                        <div class="fs-sheet">
                            {{-- STATEMENT OF FINANCIAL POSITION --}}
                            <table>
                                <colgroup>
                                    <col style="width:52%">
                                    <col style="width:14%">
                                    <col style="width:17%">
                                    <col style="width:17%">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="no-border text-center"><img
                                                src="{{ asset('img/header.png') }}"
                                                style="width:100%; max-height:100%; object-fit:contain;"
                                                alt="Company Header" class="payroll-header-img"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border company-line">{{ $addressLine }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border fs-title">AUDITED STATEMENT OF FINANCIAL
                                            POSITION AS AT {{ strtoupper($periodTitle) }}</td>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th class="text-right">{{ $currentCol }}<br>TSHS</th>
                                        <th class="text-right">{{ $previousCol }}<br>TSHS</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">ASSETS</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">NON CURRENT ASSETS</th>
                                    </tr>
                                    <tr>
                                        <td>Property, Plant &amp; Equipment</td>
                                        <td><a class="note-link" href="{{ route('assets.report', $assetParams) }}">Note
                                                7</a></td>
                                        <td class="text-right">{{ $fmt($ppeCurrent ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($ppePrevious ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Intangible Assets and Goodwill</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($intangibleAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($intangibleAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Biological Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($biologicalAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($biologicalAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Trade and Other receivables</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($nonCurrentReceivables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($nonCurrentReceivablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Investments</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($investments ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($investmentsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Deferred tax Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($deferredTaxAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($deferredTaxAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Other Non-Current Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($otherNonCurrentAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherNonCurrentAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Non Current Asset</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalNonCurrentAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalNonCurrentAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">CURRENT ASSETS</th>
                                    </tr>
                                    <tr>
                                        <td>Cash &amp; Cash Equivalents</td>
                                        <td><a class="note-link" href="#note10">Note 10</a></td>
                                        <td class="text-right">{{ $fmt($cash ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Accounts Receivables</td>
                                        <td><a class="note-link" href="#note9">Note 9</a></td>
                                        <td class="text-right">{{ $fmt($receivables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($receivablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Inventories</td>
                                        <td><a class="note-link" href="#note8">Note 8</a></td>
                                        <td class="text-right">{{ $fmt($inventories ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($inventoriesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Goods in Transit</td>
                                        <td><a class="note-link" href="#note8">Note 8</a></td>
                                        <td class="text-right">{{ $fmt($goodsInTransit ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($goodsInTransitPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Work in progress</td>
                                        <td><a class="note-link" href="#note8">Note 8</a></td>
                                        <td class="text-right">{{ $fmt($workInProgress ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($workInProgressPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Prepayment and Advances</td>
                                        <td><a class="note-link" href="#note8">Note 8</a></td>
                                        <td class="text-right">{{ $fmt($prepayments ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($prepaymentsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Balance Due from related parties</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($dueFromRelatedParties ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($dueFromRelatedPartiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tax Receivables</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($taxReceivables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($taxReceivablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Inter company Balances</td>
                                        <td><a class="note-link" href="#note8">Note 8</a></td>
                                        <td class="text-right">{{ $fmt($interCompanyBalances ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($interCompanyBalancesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Other Current Assets</td>
                                        <td><a class="note-link" href="#note8">Note 8</a></td>
                                        <td class="text-right">{{ $fmt($otherCurrentAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherCurrentAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Current Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalCurrentAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalCurrentAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td>Total Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">EQUITY &amp; LIABILITIES</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">Equity</th>
                                    </tr>
                                    <tr>
                                        <td>Share Capital</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($shareCapital ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($shareCapitalPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Share premium</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($sharePremium ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($sharePremiumPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Reserve</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($reserves ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($reservesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Retained Earnings</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($retainedEarnings ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($retainedEarningsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Advance towards share capital</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($advanceShareCapital ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($advanceShareCapitalPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Other equity item</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($otherEquity ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherEquityPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Equity</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalEquity ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalEquityPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">LIABILITIES</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">Long term Liabilities</th>
                                    </tr>
                                    <tr>
                                        <td>Long term loan</td>
                                        <td><a class="note-link" href="#note12">Note 12</a></td>
                                        <td class="text-right">{{ $fmt($longTermLoans ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($longTermLoansPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Loans and borrowings</td>
                                        <td></td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Debentures</td>
                                        <td></td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Balance due to related parties</td>
                                        <td></td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Deferred Income/revenue</td>
                                        <td></td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Deferred tax liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($deferredTaxLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($deferredTaxLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Provisions</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($provisions ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($provisionsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Other Non-Current liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($otherNonCurrentLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherNonCurrentLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Long Term Liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalLongTermLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalLongTermLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">Current Liabilities</th>
                                    </tr>
                                    <tr>
                                        <td>Bank Overdraft</td>
                                        <td><a class="note-link" href="#note12">Note 12</a></td>
                                        <td class="text-right">{{ $fmt($overdraft ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($overdraftPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Short term loan</td>
                                        <td><a class="note-link" href="#note12">Note 12</a></td>
                                        <td class="text-right">{{ $fmt($shortTermLoans ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($shortTermLoansPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Current tax liabilities</td>
                                        <td><a class="note-link" href="#note11">Note 11</a></td>
                                        <td class="text-right">{{ $fmt($currentTaxLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($currentTaxLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Trade and other payables</td>
                                        <td><a class="note-link" href="#note11">Note 11</a></td>
                                        <td class="text-right">{{ $fmt($payables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($payablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Balance due to related parties</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($dueToRelatedParties ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($dueToRelatedPartiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Deferred Income/revenue</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($deferredIncome ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($deferredIncomePrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Other Current liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($otherCurrentLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherCurrentLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Current Liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalCurrentLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalCurrentLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    @if (abs($unclassifiedBalance ?? 0) > 0.01 || abs($unclassifiedBalancePrev ?? 0) > 0.01)
                                        <tr>
                                            <td>Unclassified / mapping difference</td>
                                            <td></td>
                                            <td class="text-right">{{ $fmt($unclassifiedBalance ?? 0) }}</td>
                                            <td class="text-right">{{ $fmt($unclassifiedBalancePrev ?? 0) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="total-row">
                                        <td>Total Equity &amp; Liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalEquityLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalEquityLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @includeWhen(false, 'nothing')
                            <table class="signature-table">
                                <tbody>
                                    <tr>
                                        <td class="no-border" colspan="4"><strong>Certified True and Correct</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="no-border" style="width:35%;">
                                            @if ($signatureUrl)
                                                <img src="{{ $signatureUrl }}" class="sign-img"><br>
                                            @endif
                                            ……………………………..<br>{{ optional($reportCo)->user_id ? '' : 'Managing Director' }}
                                        </td>
                                        <td class="no-border" style="width:25%;">
                                            {{ \Carbon\Carbon::now()->format('d.m.Y') }}<br>Date</td>
                                        <td class="no-border" style="width:20%;">
                                            @if ($stampUrl)
                                                <img src="{{ $stampUrl }}" class="stamp-img">
                                            @endif
                                        </td>
                                        <td class="no-border"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border text-center">The Statement of Accounting
                                            Policies and the Accompanying notes form part of the financial Statement</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="page-break">&nbsp;</div>

                            {{-- COMPREHENSIVE INCOME --}}
                            <table>
                                <colgroup>
                                    <col style="width:52%">
                                    <col style="width:14%">
                                    <col style="width:17%">
                                    <col style="width:17%">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="no-border text-center"><img
                                                src="{{ asset('img/header.png') }}"
                                                style="width:100%; max-height:100%; object-fit:contain;"
                                                alt="Company Header" class="payroll-header-img"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border company-line">{{ $addressLine }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border fs-title">AUDITED STATEMENT OF COMPREHENSIVE
                                            INCOME FOR THE PERIOD ENDED {{ strtoupper($periodTitle) }}</td>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th class="text-right">{{ $currentCol }}<br>TSHS</th>
                                        <th class="text-right">{{ $previousCol }}<br>TSHS</th>
                                    </tr>
                                    <tr>
                                        <td>Revenue</td>
                                        <td><a class="note-link" href="#note2">Note 2</a></td>
                                        <td class="text-right">{{ $fmt($revenue ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($revenuePrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Less: Cost of Revenue</td>
                                        <td><a class="note-link" href="#note3">Note 3</a></td>
                                        <td class="text-right">{{ $fmtNeg($costOfRevenue ?? 0) }}</td>
                                        <td class="text-right">{{ $fmtNeg($costOfRevenuePrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Gross Profit</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($grossProfit ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($grossProfitPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">Other Income</th>
                                    </tr>
                                    <tr>
                                        <td>Interest Income / Other Income</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($otherIncome ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherIncomePrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Income</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalIncome ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalIncomePrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">Operating Expenses</th>
                                    </tr>
                                    <tr>
                                        <td>Administrative &amp; Establishment Expenses</td>
                                        <td><a class="note-link" href="#note4">Note 4</a></td>
                                        <td class="text-right">{{ $fmtNeg($adminExpenses ?? 0) }}</td>
                                        <td class="text-right">{{ $fmtNeg($adminExpensesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Selling &amp; Distribution Cost</td>
                                        <td><a class="note-link" href="#note5">Note 5</a></td>
                                        <td class="text-right">{{ $fmtNeg($sellingDistribution ?? 0) }}</td>
                                        <td class="text-right">{{ $fmtNeg($sellingDistributionPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Depreciation &amp; amortization</td>
                                        <td><a class="note-link" href="{{ route('assets.report', $assetParams) }}">Note
                                                7</a></td>
                                        <td class="text-right">{{ $fmtNeg($depreciation ?? 0) }}</td>
                                        <td class="text-right">{{ $fmtNeg($depreciationPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Profit Before interest and Tax</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($profitBeforeInterestTax ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($profitBeforeInterestTaxPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Finance Cost (Interest Paid)</td>
                                        <td><a class="note-link" href="#note6">Note 6</a></td>
                                        <td class="text-right">{{ $fmtNeg($financeCost ?? 0) }}</td>
                                        <td class="text-right">{{ $fmtNeg($financeCostPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Profit before Tax</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($profitBeforeTax ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($profitBeforeTaxPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Income Tax Expenses</td>
                                        <td><a class="note-link" href="#note15">Note 15</a></td>
                                        <td class="text-right">{{ $fmtNeg($taxExpense ?? 0) }}</td>
                                        <td class="text-right">{{ $fmtNeg($taxExpensePrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td>Profit / (Loss) For the period after Income Tax Expenses</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($profitAfterTax ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($profitAfterTaxPrev ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="signature-table">
                                <tbody>
                                    <tr>
                                        <td class="no-border" colspan="4"><strong>Certified True and Correct</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="no-border" style="width:35%;">
                                            @if ($signatureUrl)
                                                <img src="{{ $signatureUrl }}" class="sign-img"><br>
                                            @endif……………………………..<br>Managing Director
                                        </td>
                                        <td class="no-border" style="width:25%;">
                                            {{ \Carbon\Carbon::now()->format('d.m.Y') }}<br>Date</td>
                                        <td class="no-border" style="width:20%;">
                                            @if ($stampUrl)
                                                <img src="{{ $stampUrl }}" class="stamp-img">
                                            @endif
                                        </td>
                                        <td class="no-border"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border text-center">The Statement of Accounting
                                            Policies and the Accompanying notes form part of the financial Statement</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="page-break">&nbsp;</div>

                            {{-- CHANGES IN EQUITY --}}
                            <table>
                                <colgroup>
                                    <col style="width:43%">
                                    <col style="width:19%">
                                    <col style="width:19%">
                                    <col style="width:19%">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="no-border text-center"><img
                                                src="{{ asset('img/header.png') }}"
                                                style="width:100%; max-height:100%; object-fit:contain;"
                                                alt="Company Header" class="payroll-header-img"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border company-line">{{ $addressLine }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border fs-title">AUDITED STATEMENT OF CHANGES IN
                                            EQUITY AS AT {{ strtoupper($periodTitle) }}</td>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th class="text-right">Capital<br>TSHS</th>
                                        <th class="text-right">Retained Earnings / Accumulated Loss<br>TSHS</th>
                                        <th class="text-right">Total Equity<br>TSHS</th>
                                    </tr>
                                    <tr>
                                        <td>Balance at {{ $previousCol }}</td>
                                        <td class="text-right">
                                            {{ $fmt(($shareCapitalPrev ?? 0) + ($sharePremiumPrev ?? 0) + ($reservesPrev ?? 0) + ($advanceShareCapitalPrev ?? 0) + ($otherEquityPrev ?? 0)) }}
                                        </td>
                                        <td class="text-right">{{ $fmt($retainedEarningsPrev ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalEquityPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Paid up Share capital</td>
                                        <td class="text-right">{{ $fmt($shareCapital ?? 0) }}</td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">{{ $fmt($shareCapital ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Reserve</td>
                                        <td class="text-right">
                                            {{ $fmt(($reserves ?? 0) + ($sharePremium ?? 0) + ($advanceShareCapital ?? 0) + ($otherEquity ?? 0)) }}
                                        </td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">
                                            {{ $fmt(($reserves ?? 0) + ($sharePremium ?? 0) + ($advanceShareCapital ?? 0) + ($otherEquity ?? 0)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Profit / (Loss) for the period</td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">{{ $fmt($profitAfterTax ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($profitAfterTax ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Dividend paid</td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">0.00</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td>Balance at {{ $currentCol }}</td>
                                        <td class="text-right">
                                            {{ $fmt(($shareCapital ?? 0) + ($sharePremium ?? 0) + ($reserves ?? 0) + ($advanceShareCapital ?? 0) + ($otherEquity ?? 0)) }}
                                        </td>
                                        <td class="text-right">{{ $fmt($retainedEarnings ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalEquity ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="signature-table">
                                <tbody>
                                    <tr>
                                        <td class="no-border" colspan="4"><strong>Certified True and Correct</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="no-border" style="width:35%;">
                                            @if ($signatureUrl)
                                                <img src="{{ $signatureUrl }}" class="sign-img"><br>
                                            @endif……………………………..<br>Managing Director
                                        </td>
                                        <td class="no-border" style="width:25%;">
                                            {{ \Carbon\Carbon::now()->format('d.m.Y') }}<br>Date</td>
                                        <td class="no-border" style="width:20%;">
                                            @if ($stampUrl)
                                                <img src="{{ $stampUrl }}" class="stamp-img">
                                            @endif
                                        </td>
                                        <td class="no-border"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border text-center">The Statement of Accounting
                                            Policies and the Accompanying notes form part of the financial Statement</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="page-break">&nbsp;</div>

                            {{-- CASH FLOW --}}
                            <table>
                                <colgroup>
                                    <col style="width:10%">
                                    <col style="width:50%">
                                    <col style="width:20%">
                                    <col style="width:20%">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="no-border text-center"><img
                                                src="{{ asset('img/header.png') }}"
                                                style="width:100%; max-height:100%; object-fit:contain;"
                                                alt="Company Header" class="payroll-header-img"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border company-line">{{ $addressLine }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border fs-title">AUDITED CASH FLOW STATEMENT FOR THE
                                            PERIOD ENDED {{ strtoupper($periodTitle) }}</td>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th class="text-right">{{ $currentCol }}<br>TSHS</th>
                                        <th class="text-right">{{ $previousCol }}<br>TSHS</th>
                                    </tr>
                                    <tr>
                                        <th>1.00</th>
                                        <th colspan="3" class="text-left">Cash flow from operating activities</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Profit / (Loss) before tax</td>
                                        <td class="text-right">{{ $fmt($cashFlow['profit_before_tax'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['profit_before_tax_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Add: Depreciation</td>
                                        <td class="text-right">{{ $fmt($cashFlow['depreciation'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['depreciation_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Add: Interest Expenses</td>
                                        <td class="text-right">{{ $fmt($cashFlow['finance_cost'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['finance_cost_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Operating profit before working capital changes</td>
                                        <td class="text-right">
                                            {{ $fmt($cashFlow['operating_before_working_capital'] ?? 0) }}</td>
                                        <td class="text-right">
                                            {{ $fmt($cashFlow['operating_before_working_capital_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th>2.00</th>
                                        <th colspan="3" class="text-left">Working Capital Changes</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>(Increase) / Decrease in Inventories</td>
                                        <td class="text-right">{{ $fmt($cashFlow['inventory_change'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['inventory_change_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>(Increase) / Decrease in Accounts Receivables</td>
                                        <td class="text-right">{{ $fmt($cashFlow['receivable_change'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['receivable_change_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Increase / (Decrease) in Creditors</td>
                                        <td class="text-right">{{ $fmt($cashFlow['payable_change'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['payable_change_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>(Increase) / Decrease in other Current Assets</td>
                                        <td class="text-right">{{ $fmt($cashFlow['other_current_asset_change'] ?? 0) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $fmt($cashFlow['other_current_asset_change_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Increase / (Decrease) in other Current Liabilities</td>
                                        <td class="text-right">
                                            {{ $fmt($cashFlow['other_current_liability_change'] ?? 0) }}</td>
                                        <td class="text-right">
                                            {{ $fmt($cashFlow['other_current_liability_change_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Cash flow from Operations</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_from_operations'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_from_operations_prev'] ?? 0) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Taxation</td>
                                        <td class="text-right">{{ $fmt($cashFlow['taxation'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['taxation_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Interest Paid</td>
                                        <td class="text-right">{{ $fmt($cashFlow['interest_paid'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['interest_paid_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Net Cash flow used for operating activities</td>
                                        <td class="text-right">{{ $fmt($cashFlow['net_operating'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['net_operating_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th>3.00</th>
                                        <th colspan="3" class="text-left">Cash flow from investing activities</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Purchases of Property, Plant &amp; Equipment</td>
                                        <td class="text-right">{{ $fmt($cashFlow['ppe_purchase'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['ppe_purchase_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Net Cash flow used investing activities</td>
                                        <td class="text-right">{{ $fmt($cashFlow['net_investing'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['net_investing_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th>4.00</th>
                                        <th colspan="3" class="text-left">Cash flow from financing activities</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Increase / (Decrease) in Loans</td>
                                        <td class="text-right">{{ $fmt($cashFlow['loan_change'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['loan_change_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Net Cash flow from financing activities</td>
                                        <td class="text-right">{{ $fmt($cashFlow['net_financing'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['net_financing_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Changes in cash and cash equivalents for the period</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_change'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_change_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Cash and cash equivalents at start</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_opening'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_opening_prev'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Cash and cash equivalents at close</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_closing'] ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashFlow['cash_closing_prev'] ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="signature-table">
                                <tbody>
                                    <tr>
                                        <td class="no-border" colspan="4"><strong>Certified True and Correct</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="no-border" style="width:35%;">
                                            @if ($signatureUrl)
                                                <img src="{{ $signatureUrl }}" class="sign-img"><br>
                                            @endif……………………………..<br>Managing Director
                                        </td>
                                        <td class="no-border" style="width:25%;">
                                            {{ \Carbon\Carbon::now()->format('d.m.Y') }}<br>Date</td>
                                        <td class="no-border" style="width:20%;">
                                            @if ($stampUrl)
                                                <img src="{{ $stampUrl }}" class="stamp-img">
                                            @endif
                                        </td>
                                        <td class="no-border"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border text-center">The Statement of Accounting
                                            Policies and the Accompanying notes form part of the financial Statement</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="page-break">&nbsp;</div>

                            {{-- NOTES --}}
                            <table>
                                <colgroup>
                                    <col style="width:10%">
                                    <col style="width:48%">
                                    <col style="width:12%">
                                    <col style="width:15%">
                                    <col style="width:15%">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="no-border text-center"><img
                                                src="{{ asset('img/header.png') }}"
                                                style="width:100%; max-height:100%; object-fit:contain;"
                                                alt="Company Header" class="payroll-header-img"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="no-border company-line">{{ $addressLine }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="no-border fs-title">AUDITED NOTES TO THE FINANCIAL
                                            STATEMENTS FOR THE PERIOD ENDED {{ strtoupper($periodTitle) }}</td>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th class="text-right">{{ $currentCol }}<br>TSHS</th>
                                        <th class="text-right">{{ $previousCol }}<br>TSHS</th>
                                    </tr>
                                    @php $n2=$comparativeRows(['70','71','72','73','74','77','78'],'credit'); @endphp
                                    <tr id="note2">
                                        <td class="note-title">Note 2</td>
                                        <td colspan="4" class="note-title text-left">Sales / Revenue</td>
                                    </tr>
                                    @forelse($n2 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Business Income</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Revenue</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt(($revenue ?? 0) + ($otherIncome ?? 0)) }}</td>
                                        <td class="text-right">{{ $fmt(($revenuePrev ?? 0) + ($otherIncomePrev ?? 0)) }}
                                        </td>
                                    </tr>
                                    @php $n3=$comparativeRows(['60','61'],'debit'); @endphp
                                    <tr id="note3">
                                        <td class="note-title">Note 3</td>
                                        <td colspan="4" class="note-title text-left">Cost of Sales</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Opening Inventories</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($inventoriesPrev ?? 0) }}</td>
                                        <td class="text-right">0.00</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Sub total Opening Inventory</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($inventoriesPrev ?? 0) }}</td>
                                        <td class="text-right">0.00</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><strong>Add: Purchase of Materials / Cost of Services</strong></td>
                                        <td></td>
                                        <td class="text-right"></td>
                                        <td class="text-right"></td>
                                    </tr>
                                    @forelse($n3 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Purchases and direct costs</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td></td>
                                        <td>Less: Closing Inventories</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($inventories ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($inventoriesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Cost of Sales and Services</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($costOfRevenue ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($costOfRevenuePrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td></td>
                                        <td>Gross Profit</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($grossProfit ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($grossProfitPrev ?? 0) }}</td>
                                    </tr>
                                    @php $n4=$comparativeRows(['63','64','65'],'debit'); @endphp
                                    <tr id="note4">
                                        <td class="note-title">Note 4</td>
                                        <td colspan="4" class="note-title text-left">Administrative &amp; Establishment
                                            Expenses</td>
                                    </tr>
                                    @forelse($n4 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Administrative expenses</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Administrative &amp; Establishment Expenses</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($adminExpenses ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($adminExpensesPrev ?? 0) }}</td>
                                    </tr>
                                    @php $n5=$comparativeRows(['62'],'debit'); @endphp
                                    <tr id="note5">
                                        <td class="note-title">Note 5</td>
                                        <td colspan="4" class="note-title text-left">Selling &amp; Distribution
                                            Expenses</td>
                                    </tr>
                                    @forelse($n5 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Selling and distribution expenses</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Selling &amp; Distribution Expenses</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($sellingDistribution ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($sellingDistributionPrev ?? 0) }}</td>
                                    </tr>
                                    @php $n6=$comparativeRows(['66','67'],'debit'); @endphp
                                    <tr id="note6">
                                        <td class="note-title">Note 6</td>
                                        <td colspan="4" class="note-title text-left">Finance Cost</td>
                                    </tr>
                                    @forelse($n6 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Interest / finance cost</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Finance Cost</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($financeCost ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($financeCostPrev ?? 0) }}</td>
                                    </tr>
                                    <tr id="note7">
                                        <td class="note-title">Note 7</td>
                                        <td colspan="4" class="note-title text-left">Property, Plant &amp; Equipment
                                        </td>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th>Asset Category</th>
                                        <th class="text-right">Rate %</th>
                                        <th class="text-right">{{ $currentCol }}</th>
                                        <th class="text-right">{{ $previousCol }}</th>
                                    </tr>
                                    @forelse(($assetNoteRows ?? collect()) as $ar)
                                        <tr>
                                            <td></td>
                                            <td>{{ $ar->name }}</td>
                                            <td class="text-right">{{ number_format($ar->rate ?? 0, 2) }}</td>
                                            <td class="text-right">{{ $fmt($ar->closing ?? 0) }}</td>
                                            <td class="text-right">{{ $fmt($ar->previous ?? 0) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Property, Plant &amp; Equipment</td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">{{ $fmt($ppeCurrent ?? 0) }}</td>
                                            <td class="text-right">{{ $fmt($ppePrevious ?? 0) }}</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Property, Plant &amp; Equipment</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($ppeCurrent ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($ppePrevious ?? 0) }}</td>
                                    </tr>
                                    <tr id="note8">
                                        <td class="note-title">Note 8</td>
                                        <td colspan="4" class="note-title text-left">Inventories, Prepayments, Loans
                                            and Advances</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Total Inventories</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($inventories ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($inventoriesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>8.1</td>
                                        <td>Work in Progress</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($workInProgress ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($workInProgressPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>8.2</td>
                                        <td>Prepayment, loans and advances</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ $fmt(($prepayments ?? 0) + ($interCompanyBalances ?? 0)) }}</td>
                                        <td class="text-right">
                                            {{ $fmt(($prepaymentsPrev ?? 0) + ($interCompanyBalancesPrev ?? 0)) }}</td>
                                    </tr>
                                    <tr>
                                        <td>8.3</td>
                                        <td>Other Current Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($otherCurrentAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherCurrentAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Inventories, Prepayments, Loans and Advances</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ $fmt(($inventories ?? 0) + ($workInProgress ?? 0) + ($prepayments ?? 0) + ($interCompanyBalances ?? 0) + ($otherCurrentAssets ?? 0)) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $fmt(($inventoriesPrev ?? 0) + ($workInProgressPrev ?? 0) + ($prepaymentsPrev ?? 0) + ($interCompanyBalancesPrev ?? 0) + ($otherCurrentAssetsPrev ?? 0)) }}
                                        </td>
                                    </tr>
                                    @php $n9=$comparativeRows(['41'],'debit'); @endphp
                                    <tr id="note9">
                                        <td class="note-title">Note 9</td>
                                        <td colspan="4" class="note-title text-left">Accounts Receivables</td>
                                    </tr>
                                    @forelse($n9 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Trade debtors</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Accounts Receivable</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($receivables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($receivablesPrev ?? 0) }}</td>
                                    </tr>
                                    @php $n10=$comparativeRows(['52','53','54','55','56','57','58'],'debit'); @endphp
                                    <tr id="note10">
                                        <td class="note-title">Note 10</td>
                                        <td colspan="4" class="note-title text-left">Cash and Cash Equivalents</td>
                                    </tr>
                                    @forelse($n10 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Cash and Bank Balances</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Cash and Bank Balances</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($cash ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashPrev ?? 0) }}</td>
                                    </tr>
                                    @php $n11=$comparativeRows(['40','42','43','44','45','46','47','48'],'credit'); @endphp
                                    <tr id="note11">
                                        <td class="note-title">Note 11</td>
                                        <td colspan="4" class="note-title text-left">Trade Creditors, Liabilities and
                                            Accruals</td>
                                    </tr>
                                    @forelse($n11 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Trade creditors and accruals</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Creditors and Liabilities</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ $fmt(($payables ?? 0) + ($currentTaxLiabilities ?? 0) + ($otherCurrentLiabilities ?? 0) + ($dueToRelatedParties ?? 0) + ($deferredIncome ?? 0)) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $fmt(($payablesPrev ?? 0) + ($currentTaxLiabilitiesPrev ?? 0) + ($otherCurrentLiabilitiesPrev ?? 0) + ($dueToRelatedPartiesPrev ?? 0) + ($deferredIncomePrev ?? 0)) }}
                                        </td>
                                    </tr>
                                    @php $n12=$comparativeRows(['16','17','464','53','569'],'credit'); @endphp
                                    <tr id="note12">
                                        <td class="note-title">Note 12</td>
                                        <td colspan="4" class="note-title text-left">Term Loan / Bank Facilities</td>
                                    </tr>
                                    @forelse($n12 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Loans and Bank Facilities</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Loans and Borrowings</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ $fmt(($longTermLoans ?? 0) + ($shortTermLoans ?? 0) + ($overdraft ?? 0)) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $fmt(($longTermLoansPrev ?? 0) + ($shortTermLoansPrev ?? 0) + ($overdraftPrev ?? 0)) }}
                                        </td>
                                    </tr>
                                    @php $n13=$comparativeRows(['65'],'debit'); @endphp
                                    <tr id="note13">
                                        <td class="note-title">Note 13</td>
                                        <td colspan="4" class="note-title text-left">Employees Cost</td>
                                    </tr>
                                    @forelse($n13 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Employment Costs</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Employment Costs</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ $fmt($amountByPrefixes($currentRows ?? [], ['65'], 'debit')) }}</td>
                                        <td class="text-right">
                                            {{ $fmt($amountByPrefixes($previousRows ?? [], ['65'], 'debit')) }}</td>
                                    </tr>
                                    @php $n14=$comparativeRows(['633'],'debit'); @endphp
                                    <tr id="note14">
                                        <td class="note-title">Note 14</td>
                                        <td colspan="4" class="note-title text-left">Professional Fees</td>
                                    </tr>
                                    @forelse($n14 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Professional fees: Legal, Audit, Tax, Engineering, Surveyor, Valuer</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Professional Fees</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ $fmt($amountByPrefixes($currentRows ?? [], ['633'], 'debit')) }}</td>
                                        <td class="text-right">
                                            {{ $fmt($amountByPrefixes($previousRows ?? [], ['633'], 'debit')) }}</td>
                                    </tr>
                                    @php $n15=$comparativeRows(['69','431','432','433','434','435','437','697','699'],'debit'); @endphp
                                    <tr id="note15">
                                        <td class="note-title">Note 15</td>
                                        <td colspan="4" class="note-title text-left">Income Tax Expenses</td>
                                    </tr>
                                    @forelse($n15 as $r)
                                        <tr>
                                            <td></td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->code }}</td>
                                            <td class="text-right">{{ $fmt($r->current) }}</td>
                                            <td class="text-right">{{ $fmt($r->previous) }}</td>
                                    </tr>@empty<tr>
                                            <td></td>
                                            <td>Tax due / Income tax expense</td>
                                            <td></td>
                                            <td class="text-right">0.00</td>
                                            <td class="text-right">0.00</td>
                                        </tr>
                                    @endforelse
                                    <tr class="total-row">
                                        <td></td>
                                        <td>Total Tax Due / Expense</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($taxExpense ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($taxExpensePrev ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table>
                                <tbody>
                                    <tr>
                                        <td class="note-title">Note 16</td>
                                        <td class="note-title">CAPITAL COMMITMENTS</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>There were no future commitments not provided for in the financial statements as
                                            at {{ strtoupper($periodTitle) }}.</td>
                                    </tr>
                                    <tr>
                                        <td class="note-title">Note 17</td>
                                        <td class="note-title">CONTINGENT LIABILITIES</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>There was no contingent liability envisaged by the business as at
                                            {{ strtoupper($periodTitle) }}.</td>
                                    </tr>
                                    <tr>
                                        <td class="note-title">Note 18</td>
                                        <td class="note-title">COMPARATIVE INFORMATION</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Where necessary, comparative figures have been adjusted to conform with changes
                                            in presentation in the current period.</td>
                                    </tr>
                                    <tr>
                                        <td class="note-title">Note 19</td>
                                        <td class="note-title">EVENTS AFTER DATE OF STATEMENT OF FINANCIAL POSITION</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>No adjusting or significant non-adjusting events have occurred between the
                                            reporting date and the date of authorisation.</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table>
                                <colgroup>
                                    <col style="width:52%">
                                    <col style="width:14%">
                                    <col style="width:17%">
                                    <col style="width:17%">
                                </colgroup>
                                <tbody>
                                    <tr id="note20">
                                        <td colspan="4" class="note-title">Note 20 Financial Assets and Liabilities
                                            (IFRS 9)</td>
                                    </tr>
                                    <tr>
                                        <th>FINANCIAL ASSETS</th>
                                        <th></th>
                                        <th class="text-right">{{ $currentCol }}</th>
                                        <th class="text-right">{{ $previousCol }}</th>
                                    </tr>
                                    <tr>
                                        <td>Cash &amp; Cash Equivalents</td>
                                        <td>Note 10</td>
                                        <td class="text-right">{{ $fmt($cash ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Accounts Receivables</td>
                                        <td>Note 9</td>
                                        <td class="text-right">{{ $fmt($receivables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($receivablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Financial Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt(($cash ?? 0) + ($receivables ?? 0)) }}</td>
                                        <td class="text-right">{{ $fmt(($cashPrev ?? 0) + ($receivablesPrev ?? 0)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>FINANCIAL LIABILITIES</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <td>Accounts Payables</td>
                                        <td>Note 11</td>
                                        <td class="text-right">{{ $fmt($payables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($payablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Loan</td>
                                        <td>Note 12</td>
                                        <td class="text-right">
                                            {{ $fmt(($longTermLoans ?? 0) + ($shortTermLoans ?? 0) + ($overdraft ?? 0)) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $fmt(($longTermLoansPrev ?? 0) + ($shortTermLoansPrev ?? 0) + ($overdraftPrev ?? 0)) }}
                                        </td>
                                    </tr>
                                    <tr class="total-row">
                                        <td>Total Financial Liabilities</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ $fmt(($payables ?? 0) + ($longTermLoans ?? 0) + ($shortTermLoans ?? 0) + ($overdraft ?? 0)) }}
                                        </td>
                                        <td class="text-right">
                                            {{ $fmt(($payablesPrev ?? 0) + ($longTermLoansPrev ?? 0) + ($shortTermLoansPrev ?? 0) + ($overdraftPrev ?? 0)) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table>
                                <colgroup>
                                    <col style="width:52%">
                                    <col style="width:14%">
                                    <col style="width:17%">
                                    <col style="width:17%">
                                </colgroup>
                                <tbody>
                                    <tr id="note21">
                                        <td colspan="4" class="note-title">Note 21 Financial Instruments (IAS 32)</td>
                                    </tr>
                                    <tr>
                                        <th>Categories of Financial Instruments</th>
                                        <th></th>
                                        <th class="text-right">{{ $currentCol }}</th>
                                        <th class="text-right">{{ $previousCol }}</th>
                                    </tr>
                                    <tr>
                                        <td>Accounts Receivables</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($receivables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($receivablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Cash &amp; Cash Equivalents</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($cash ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($cashPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Property, Plant and Equipment</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($ppeCurrent ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($ppePrevious ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Inventories</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($inventories ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($inventoriesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="subtotal-row">
                                        <td>Total Assets</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalAssets ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalAssetsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Trade Creditors</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($payables ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($payablesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Accrued Charges</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($otherCurrentLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($otherCurrentLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Term Loan</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($shortTermLoans ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($shortTermLoansPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Bank Overdraft</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($overdraft ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($overdraftPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tax Account</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($currentTaxLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($currentTaxLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Capital</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($shareCapital ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($shareCapitalPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Retained Earnings / Accumulated Loss</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($retainedEarnings ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($retainedEarningsPrev ?? 0) }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td>Equity &amp; Liabilities</td>
                                        <td></td>
                                        <td class="text-right">{{ $fmt($totalEquityLiabilities ?? 0) }}</td>
                                        <td class="text-right">{{ $fmt($totalEquityLiabilitiesPrev ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">The company financial instruments consist of cash, receivables
                                            and payables. Unless otherwise noted, management considers that the carrying
                                            amounts approximate their fair values.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="signature-table">
                                <tbody>
                                    <tr>
                                        <td class="no-border" colspan="4"><strong>Certified True and Correct</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="no-border" style="width:35%;">
                                            @if ($signatureUrl)
                                                <img src="{{ $signatureUrl }}" class="sign-img"><br>
                                            @endif……………………………..<br>Managing Director
                                        </td>
                                        <td class="no-border" style="width:25%;">
                                            {{ \Carbon\Carbon::now()->format('d.m.Y') }}<br>Date</td>
                                        <td class="no-border" style="width:20%;">
                                            @if ($stampUrl)
                                                <img src="{{ $stampUrl }}" class="stamp-img">
                                            @endif
                                        </td>
                                        <td class="no-border"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="no-border text-center">The Statement of Accounting
                                            Policies and the Accompanying notes form part of the financial Statement</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
