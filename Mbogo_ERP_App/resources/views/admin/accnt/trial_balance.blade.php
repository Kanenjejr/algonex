@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Trial Balance Report Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Trial Balance Report</strong></li>
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

    @php
        $reportCo = $reportCompany ?? ($holdingCompany ?? null);
        $fmt = function ($value) {
            return number_format((float) $value, 2);
        };
    @endphp

    <style>
        .report-filter-box {
            padding: 16px 18px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }

        .report-filter-box label {
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
            display: block;
        }

        .report-filter-box .form-control {
            height: 38px;
            width: 100%;
        }

        .report-filter-actions {
            padding-top: 24px;
            white-space: nowrap;
        }

        .tb-report-wrapper {
            background: #fff;
            color: #000;
            max-width: 1200px;
            margin: 0 auto;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .tb-header-img {
            width: 100%;
            max-height: 160px;
            object-fit: contain;
            display: block;
            margin: 0 auto 6px auto;
        }

        .tb-company-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0;
        }

        .tb-report-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0 8px 0;
        }

        .tb-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tb-table th,
        .tb-table td {
            border: 1px solid #000 !important;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .tb-table th {
            font-weight: bold;
        }

        .debit-amount {
            color: #000 !important;
        }

        .credit-amount,
        .credit-head {
            color: red !important;
        }

        .tb-total-row th,
        .tb-total-row td {
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 3px double #000 !important;
        }

        .text-right {
            text-align: right
        }

        .text-center {
            text-align: center
        }

        .no-print-only {
            display: table-cell;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            html,
            body {
                background: #fff !important;
                color: #000 !important;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 10px;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body * {
                visibility: hidden;
            }

            #trialBalanceBox,
            #trialBalanceBox * {
                visibility: visible;
            }

            #trialBalanceBox {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .no-print,
            .no-print-only {
                display: none !important;
            }

            .tb-report-wrapper {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                font-size: 10px !important;
            }

            .tb-header-img {
                max-height: 120px !important;
            }

            .tb-table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: auto;
            }

            .tb-table tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .tb-table th,
            .tb-table td {
                border: 1px solid #000 !important;
                padding: 3px 4px !important;
            }

            a {
                color: inherit !important;
                text-decoration: none !important;
            }
        }
    </style>

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

        function exportTableToExcel(tableID, filename = '') {
            var tableSelect = document.getElementById(tableID);
            if (!tableSelect) return alert('Table not found.');
            var html = '\ufeff' + tableSelect.outerHTML;
            filename = filename ? filename + '.xls' : 'trial_balance.xls';
            var blob = new Blob([html], {
                type: 'application/vnd.ms-excel;charset=utf-8;'
            });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function printReceipt(ele) {
            window.print();
        }
    </script>

    <div class="col-12">
        <h3>Trial Balance - {{ $year }}</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-success">
                        <div class="ibox-tools">
                            <a onclick="exportTableToExcel('trialBalanceTable','Trial-Balance-{{ $year }}')"
                                class="btn btn-primary text-white"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                            <a onclick="printReceipt('trialBalanceBox')" class="btn btn-primary text-white"><i
                                    class="fa fa-print"></i> Print</a>
                            <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            <a class="close-link"><i class="fa fa-times"></i></a>
                        </div>
                    </div>

                    <div class="ibox-content" style="padding:0;">
                        <form method="get" action="{{ route('trialbalance', ['year' => $year]) }}"
                            class="report-filter-box no-print">
                            <div class="row">
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ request('start_date', $start_date ?? '') }}">
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="{{ request('end_date', $end_date ?? '') }}">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <label>Company Site</label>
                                    <select name="company_id" class="form-control">
                                        <option value="">-- All Company Sites --</option>
                                        @foreach ($companies ?? ($companySites ?? collect()) as $company)
                                            <option value="{{ $company->id }}"
                                                @if ((string) ($selectedCompany ?? request('company_id')) === (string) $company->id) selected @endif>
                                                {{ $company->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <label>Company Unit / Business Unit</label>
                                    <select name="company_unit_id" class="form-control">
                                        <option value="">-- All Company Units --</option>
                                        @foreach ($companyUnits ?? collect() as $unit)
                                            <option value="{{ $unit->id }}"
                                                @if ((string) ($selectedCompanyUnit ?? request('company_unit_id')) === (string) $unit->id) selected @endif>{{ $unit->unit_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-12 mb-2 report-filter-actions">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                                        Search</button>
                                    <a href="{{ route('trialbalance', ['year' => $year]) }}"
                                        class="btn btn-default">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div style="padding:18px;">
                            <div id="trialBalanceBox" class="tb-report-wrapper">
                                <img src="{{ asset('img/header.png') }}"
                                    style="width:100%; max-height:100%; object-fit:contain;" alt="Company Header"
                                    class="payroll-header-img tb-header-img">
                                <div class="tb-company-title">
                                    {{ optional($reportCo)->company_name ?? 'GENERAL COMPANY / COMBINED COMPANY' }}
                                    @if (optional($reportCo)->TIN)
                                        TIN {{ optional($reportCo)->TIN }}
                                    @endif
                                    @if (optional($reportCo)->city)
                                        , {{ optional($reportCo)->city }}
                                    @endif
                                </div>
                                <div class="tb-report-title">TRIAL BALANCE FOR THE PERIOD
                                    {{ \Carbon\Carbon::parse($start_date)->format('d.m.Y') }} TO
                                    {{ \Carbon\Carbon::parse($end_date)->format('d.m.Y') }}</div>

                                <table id="trialBalanceTable" class="tb-table">
                                    <thead>
                                        <tr>
                                            <th style="width:5%">#</th>
                                            <th style="width:15%">Accounting Code</th>
                                            <th>Accounting Description</th>
                                            <th class="text-right debit-amount" style="width:18%">Debit Balance<br>TSHS</th>
                                            <th class="text-right credit-head" style="width:18%">Credit Balance<br>TSHS</th>
                                            <th class="no-print-only" style="width:10%">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data as $k => $r)
                                            @php
                                                $ledgerParams = [
                                                    'year' => $year,
                                                    'accountId' => $r->account_id ?: $r->accounting_code_6,
                                                    'start_date' => $start_date,
                                                    'end_date' => $end_date,
                                                ];
                                                if (!empty($selectedCompany ?? request('company_id'))) {
                                                    $ledgerParams['company_id'] =
                                                        $selectedCompany ?? request('company_id');
                                                }
                                                if (!empty($selectedCompanyUnit ?? request('company_unit_id'))) {
                                                    $ledgerParams['company_unit_id'] =
                                                        $selectedCompanyUnit ?? request('company_unit_id');
                                                }
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $k + 1 }}</td>
                                                <td>{{ $r->accounting_code_6 ?? (optional($r->account)->AccCode ?? 'N/A') }}
                                                </td>
                                                <td>{{ $r->accounting_name_6 ?? (optional($r->account)->AccDescription ?? '') }}
                                                </td>
                                                <td class="text-right debit-amount">
                                                    @if ((float) $r->debit > 0)
                                                        <a class="debit-amount"
                                                            href="{{ route('ledger.details', $ledgerParams) }}">{{ $fmt($r->debit) }}</a>
                                                    @else
                                                        0.00
                                                    @endif
                                                </td>
                                                <td class="text-right credit-amount">
                                                    @if ((float) $r->credit > 0)
                                                        <a class="credit-amount"
                                                            href="{{ route('ledger.details', $ledgerParams) }}">{{ $fmt($r->credit) }}</a>
                                                    @else
                                                        0.00
                                                    @endif
                                                </td>
                                                <td class="no-print-only text-center"><a class="btn btn-xs btn-info"
                                                        href="{{ route('ledger.details', $ledgerParams) }}">Details</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No transactions found for this
                                                    date/company/unit.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="tb-total-row">
                                            <th colspan="3" class="text-right">TOTAL</th>
                                            <th class="text-right debit-amount">{{ $fmt($totalDebit ?? 0) }}</th>
                                            <th class="text-right credit-amount">{{ $fmt($totalCredit ?? 0) }}</th>
                                            <th class="no-print-only"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div style="text-align:center; margin-top:10px; font-size:11px;">The Statement of
                                    Accounting Policies and the Accompanying notes form part of the financial Statement
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
