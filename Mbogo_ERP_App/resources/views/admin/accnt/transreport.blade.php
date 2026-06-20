@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Financial Reports Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Financial Reports</strong></li>
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
    </script>

    <div class="col-12">
        <h3 class="mb-2 page-title">Financial Reports</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-success">
                        <div class="ibox-tools">
                            <button id="toggleReports" class="btn btn-primary btn-sm">Previous Year Report</button>
                            <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            <a class="close-link"><i class="fa fa-times"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="alert alert-info" style="margin-bottom:15px;text-align:left;">
                            <strong>Full / Combined Financial Statements</strong> pulls Statement of Financial Position,
                            Comprehensive Income, Changes in Equity, Cash Flow and Notes in one printable/exportable page.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    @php
                                        $startYear = 2023;
                                        $currentYear = now()->year;
                                        $reports = [
                                            'Full / Combined Financial Statements' => [
                                                'route' => 'financialstatements',
                                                'permission' => 'View-Accounting-Reports',
                                                'icon' => 'fa fa-files-o',
                                                'blue' => true,
                                            ],
                                            'Ledger Details' => [
                                                'route' => 'ledger',
                                                'permission' => 'View-Ledger-Details-Report',
                                                'icon' => 'fa fa-book',
                                                'blue' => true,
                                            ],
                                            'Trial Balance' => [
                                                'route' => 'trialbalance',
                                                'permission' => 'View-Trial-Balance-Report',
                                                'icon' => 'fa fa-balance-scale',
                                                'blue' => true,
                                            ],
                                            'Monthly Trial Balance' => [
                                                'route' => 'mnttrialbalance',
                                                'permission' => 'View-Monthly-Trial-Balance-Report',
                                                'icon' => 'fa fa-calendar',
                                                'blue' => true,
                                            ],
                                            'Profit and Loss / Comprehensive Income' => [
                                                'route' => 'profitloss',
                                                'permission' => 'View-Profit-&-Loss-Report',
                                                'icon' => 'fa fa-line-chart',
                                                'blue' => true,
                                            ],
                                            'Balance Sheet / Financial Position' => [
                                                'route' => 'balancesheet',
                                                'permission' => 'View-Balance-Sheet-Report',
                                                'icon' => 'fa fa-table',
                                                'blue' => true,
                                            ],
                                            'Change in Equity' => [
                                                'route' => 'changeinequity',
                                                'permission' => 'View-Change-in-Equity-Report',
                                                'icon' => 'fa fa-exchange',
                                                'blue' => true,
                                            ],
                                            'Cash Flow' => [
                                                'route' => 'cashflow',
                                                'permission' => 'View-Cash-Flow-Report',
                                                'icon' => 'fa fa-money',
                                                'blue' => true,
                                            ],
                                            'Asset Report / PPE Report' => [
                                                'route' => 'assets.report',
                                                'permission' => 'View-Asset-Report',
                                                'icon' => 'fa fa-building',
                                                'blue' => true,
                                                'asset' => true,
                                            ],
                                        ];
                                    @endphp
                                    <tr>
                                        <th style="min-width:320px;text-align:left;">Report Name / Duration</th>
                                        @for ($year = $startYear; $year <= $currentYear; $year++)
                                            <th class="year-header" data-year="{{ $year }}"
                                                style="text-align:left;">{{ $year }} Report</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reports as $label => $report)
                                        @can($report['permission'])
                                            <tr>
                                                <td style="text-align:left;"><i class="{{ $report['icon'] }}"></i>
                                                    {{ $label }}</td>
                                                @for ($year = $startYear; $year <= $currentYear; $year++)
                                                    <td class="year-data" data-year="{{ $year }}"
                                                        style="text-align:left;">
                                                        @php
                                                            $params = ['year' => $year];
                                                            if (!empty($report['asset'])) {
                                                                $url = route($report['route'], [
                                                                    'start_date' => $year . '-01-01',
                                                                    'end_date' => $year . '-12-31',
                                                                ]);
                                                            } else {
                                                                $url =
                                                                    route($report['route'], $params) .
                                                                    '?start_date=' .
                                                                    $year .
                                                                    '-01-01&end_date=' .
                                                                    $year .
                                                                    '-12-31';
                                                            }
                                                        @endphp
                                                        <a href="{{ $url }}" style="color:#007bff;font-weight:600;">
                                                            {{ $label }} {{ $year }}
                                                        </a>
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endcan
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentYear = new Date().getFullYear();
            let previousYear = currentYear - 1;
            let reportType = localStorage.getItem('reportType') || 'current';

            function updateReportView(type) {
                document.querySelectorAll('.year-header,.year-data').forEach(function(el) {
                    let year = parseInt(el.dataset.year);
                    if (type === 'current') el.style.display = (year < previousYear) ? 'none' : '';
                    else el.style.display = (year == currentYear) ? 'none' : '';
                });
                document.getElementById('toggleReports').textContent = type === 'current' ? 'Previous Year Report' :
                    'Current Year Reports';
            }
            updateReportView(reportType);
            document.getElementById('toggleReports').addEventListener('click', function() {
                reportType = reportType === 'current' ? 'previous' : 'current';
                localStorage.setItem('reportType', reportType);
                updateReportView(reportType);
            });
        });
    </script>
@endsection
