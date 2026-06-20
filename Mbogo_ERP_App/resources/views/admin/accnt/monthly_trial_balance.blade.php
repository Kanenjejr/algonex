@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Monthly Trial Balance For The Year Ended At
                {{ \Carbon\Carbon::createFromDate($year, 12, 31)->format('d-M-Y') }}</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Monthly Trial Balance</strong></li>
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

        .mtb-report-wrapper {
            background: #fff;
            color: #000;
            max-width: 1600px;
            margin: 0 auto;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .mtb-header-img {
            width: 100%;
            max-height: 160px;
            object-fit: contain;
            display: block;
            margin: 0 auto 6px auto;
        }

        .mtb-company-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0;
        }

        .mtb-report-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0 8px 0;
        }

        .mtb-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1400px;
        }

        .mtb-table th,
        .mtb-table td {
            border: 1px solid #000 !important;
            padding: 3px 4px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .mtb-table th {
            font-weight: bold;
        }

        .debit-amount {
            color: #000 !important;
        }

        .credit-amount,
        .credit-head {
            color: red !important;
        }

        .mtb-total-row th,
        .mtb-total-row td {
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

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
            }

            html,
            body {
                background: #fff !important;
                color: #000 !important;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 8px;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body * {
                visibility: hidden;
            }

            #monthlyTrialBalanceBox,
            #monthlyTrialBalanceBox * {
                visibility: visible;
            }

            #monthlyTrialBalanceBox {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .no-print {
                display: none !important;
            }

            .mtb-report-wrapper {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                font-size: 7.5px !important;
            }

            .mtb-header-img {
                max-height: 110px !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .mtb-table {
                min-width: 0 !important;
                width: 100% !important;
                table-layout: fixed;
                border-collapse: collapse !important;
            }

            .mtb-table th,
            .mtb-table td {
                border: 1px solid #000 !important;
                padding: 2px !important;
                word-break: break-word;
                white-space: normal;
            }

            .mtb-table tr {
                page-break-inside: avoid;
                break-inside: avoid;
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
            var t = document.getElementById(tableID);
            if (!t) return alert('Table not found.');
            var html = '\ufeff' + t.outerHTML;
            var blob = new Blob([html], {
                type: 'application/vnd.ms-excel;charset=utf-8;'
            });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = (filename ? filename : 'monthly_trial_balance') + '.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function printReceipt(ele) {
            window.print();
        }
    </script>

    <div class="col-12">
        <h3>Monthly Trial Balance - {{ $year }}</h3>
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-success">
                        <div class="ibox-tools">
                            <a onclick="exportTableToExcel('monthlyTrialBalanceTable','Monthly-Trial-Balance-{{ $year }}')"
                                class="btn btn-primary text-white"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                            <a onclick="printReceipt('monthlyTrialBalanceBox')" class="btn btn-primary text-white"><i
                                    class="fa fa-print"></i> Print</a>
                            <a class="collapse-link"><i class="fa fa-chevron-up"></i></a><a class="close-link"><i
                                    class="fa fa-times"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content" style="padding:0;">
                        <form method="get" action="{{ route('mnttrialbalance', ['year' => $year]) }}"
                            class="report-filter-box no-print">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 mb-2"><label>Company Site</label><select name="company_id"
                                        class="form-control">
                                        <option value="">-- All Company Sites --</option>
                                        @foreach ($companies ?? ($companySites ?? collect()) as $company)
                                            <option value="{{ $company->id }}"
                                                @if ((string) ($selectedCompany ?? request('company_id')) === (string) $company->id) selected @endif>
                                                {{ $company->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2"><label>Company Unit / Business Unit</label><select
                                        name="company_unit_id" class="form-control">
                                        <option value="">-- All Company Units --</option>
                                        @foreach ($companyUnits ?? collect() as $unit)
                                            <option value="{{ $unit->id }}"
                                                @if ((string) ($selectedCompanyUnit ?? request('company_unit_id')) === (string) $unit->id) selected @endif>{{ $unit->unit_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-2"><label>Account</label><select name="account"
                                        class="form-control">
                                        <option value="">-- All accounts --</option>
                                        @foreach ($accounts ?? collect() as $acct)
                                            <option value="{{ $acct->id }}"
                                                @if ((string) ($selectedAccount ?? request('account')) === (string) $acct->id) selected @endif>{{ $acct->AccCode }} -
                                                {{ $acct->AccDescription }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-12 mb-2 report-filter-actions"><button type="submit"
                                        class="btn btn-primary"><i class="fa fa-search"></i> Search</button><a
                                        href="{{ route('mnttrialbalance', ['year' => $year]) }}"
                                        class="btn btn-default">Reset</a></div>
                            </div>
                        </form>

                        <div style="padding:18px;">
                            <div id="monthlyTrialBalanceBox" class="mtb-report-wrapper">
                                <img src="{{ asset('img/header.png') }}"
                                    style="width:100%; max-height:100%; object-fit:contain;" alt="Company Header"
                                    class="payroll-header-img mtb-header-img">
                                <div class="mtb-company-title">
                                    {{ optional($reportCo)->company_name ?? 'GENERAL COMPANY / COMBINED COMPANY' }}
                                    @if (optional($reportCo)->TIN)
                                        TIN {{ optional($reportCo)->TIN }}
                                        @endif @if (optional($reportCo)->city)
                                            , {{ optional($reportCo)->city }}
                                        @endif
                                </div>
                                <div class="mtb-report-title">MONTHLY TRIAL BALANCE FOR THE YEAR ENDED
                                    {{ \Carbon\Carbon::createFromDate($year, 12, 31)->format('d.m.Y') }}</div>
                                <div class="table-responsive" style="overflow:auto">
                                    <table id="monthlyTrialBalanceTable" class="mtb-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width:35px;vertical-align:middle">#</th>
                                                <th rowspan="2" style="width:90px;vertical-align:middle">AC Code</th>
                                                <th rowspan="2" style="min-width:220px;vertical-align:middle">AC
                                                    Description</th>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <th colspan="2" class="text-center">
                                                        {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('M') . '-' . $year }}
                                                    </th>
                                                @endfor
                                            </tr>
                                            <tr>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <th class="text-right debit-amount">Debit</th>
                                                    <th class="text-right credit-head">Credit</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($matrix as $idx => $row)
                                                <tr>
                                                    <td class="text-center">{{ $idx + 1 }}</td>
                                                    <td>{{ optional($row->account)->AccCode ?? 'N/A' }}</td>
                                                    <td>{{ optional($row->account)->AccDescription ?? '' }}</td>
                                                    @for ($m = 1; $m <= 12; $m++)
                                                        @php
                                                            $vals = $row->months[$m];
                                                            $debit = $vals['debit'];
                                                            $credit = $vals['credit'];
                                                            $monthStart = \Carbon\Carbon::createFromDate($year, $m, 1)
                                                                ->startOfMonth()
                                                                ->toDateString();
                                                            $monthEnd = \Carbon\Carbon::createFromDate($year, $m, 1)
                                                                ->endOfMonth()
                                                                ->toDateString();
                                                            $qs = http_build_query(
                                                                array_merge(request()->except([]), [
                                                                    'account' => optional($row->account)->id,
                                                                    'start_date' => $monthStart,
                                                                    'end_date' => $monthEnd,
                                                                ]),
                                                            );
                                                        @endphp
                                                        <td class="text-right debit-amount">
                                                            @if ($debit > 0)
                                                                <a class="debit-amount"
                                                                    href="{{ route('ledger', ['year' => $year]) }}?{{ $qs }}">{{ $fmt($debit) }}</a>
                                                            @else
                                                                0.00
                                                            @endif
                                                        </td>
                                                        <td class="text-right credit-amount">
                                                            @if ($credit > 0)
                                                                <a class="credit-amount"
                                                                    href="{{ route('ledger', ['year' => $year]) }}?{{ $qs }}">{{ $fmt($credit) }}</a>
                                                            @else
                                                                0.00
                                                            @endif
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ 3 + 12 * 2 }}" class="text-center">No accounts /
                                                        data for the selected filters.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="mtb-total-row">
                                                <th colspan="3" class="text-right">TOTALS</th>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    @php $ft=$footerTotals[$m]; @endphp
                                                    <th class="text-right debit-amount">{{ $fmt($ft['debit']) }}</th>
                                                    <th class="text-right credit-amount">{{ $fmt($ft['credit']) }}</th>
                                                @endfor
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div style="text-align:center;margin-top:10px;font-size:11px;">The Statement of Accounting
                                    Policies and the Accompanying notes form part of the financial Statement</div>
                            </div>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary mt-2 no-print">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
