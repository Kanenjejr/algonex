@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Assets Report Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('accounting') }}">Accounting And Finance</a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Assets Report Registration</strong>
                </li>
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
    @php
        $displayTitle = 'ASSETS MANAGEMENT AS AT ' . Carbon::parse($end_date)->format('d F Y') . ' IN TZS';
    @endphp
    <div class="col-12">
        <h3 class="mb-2 page-title">Asset Report</h3>
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                        <h5>Filters</h5>
                    </div>
                    <div class="ibox-content">
                        <form action="{{ route('assets.report') }}" method="GET" class="form-inline">
                            @csrf
                            <div class="form-group mr-2">
                                <label class="mr-1">Start Date</label>
                                <input type="date" name="start_date" value="{{ request('start_date', $start_date) }}"
                                    class="form-control">
                            </div>
                            <div class="form-group mr-2">
                                <label class="mr-1">End Date</label>
                                <input type="date" name="end_date" value="{{ request('end_date', $end_date) }}"
                                    class="form-control">
                            </div>
                            <div class="form-group mr-2" style="min-width:250px">
                                <label class="mr-1">Work Point</label>
                                <select name="work_point_id" id="filter_work_point" class="form-control select2_demo_2"
                                    style="width:220px">
                                    <option value="">-- All Work Points --</option>
                                    @foreach ($workPoints as $wp)
                                        <option value="{{ $wp->id }}"
                                            @if ((string) request('work_point_id', $selectedWorkPoint) === (string) $wp->id) selected @endif>{{ $wp->work_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Search</button>
                                <a href="{{ route('assets.report') }}" class="btn btn-default ml-2">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Report Panel --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                        <div class="ibox-tools">
                            <a href="{{ route('assets.import.form') }}" class="btn btn-primary text-white">
                                <i class="fa fa-upload"></i> Import Assets Excel
                            </a>

                            <a onclick="exportTableToExcel('summaryTable', 'Assets-Summary-{{ $end_date }}')"
                                class="btn btn-primary text-white"><i class="fa fa-file-excel-o"></i> Export Summary to
                                Excel</a>
                            <a onclick="exportTableToExcel('detailsTable', 'Assets-Details-{{ $end_date }}')"
                                class="btn btn-primary text-white"><i class="fa fa-file-excel-o"></i> Export Details to
                                Excel</a>
                            <a onclick="printReceipt('reportBox')" class="btn btn-primary text-white"><i
                                    class="fa fa-print"></i> Print</a>
                        </div>
                    </div>

                    <div id="reportBox" class="ibox-content">
                        <div style="text-align: center;">
                            <img style="display: block; margin: 0 auto; max-height:90px"
                                src="{{ asset('ItracomHeader1.jpg') }}" alt="">
                            <h2 style="text-align:center;font-weight:bold">{{ $displayTitle }}</h2>
                            <p>Period: {{ \Carbon\Carbon::parse($start_date)->format('Y-m-d') }} to
                                {{ \Carbon\Carbon::parse($end_date)->format('Y-m-d') }}</p>
                            @if ($selectedWorkPoint)
                                <p>Work Point:
                                    {{ optional($workPoints->firstWhere('id', $selectedWorkPoint))->work_name ?? '-' }}</p>
                            @endif
                        </div>

                        {{-- SUMMARY TABLE (unchanged) --}}
                        <div class="table-responsive">
                            <table id="summaryTable" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Items / Properties</th>
                                        @foreach ($assetTypes as $type)
                                            <th style="text-align:right">{{ $type->name }}</th>
                                        @endforeach
                                        <th style="text-align:right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- ... same summary rows as before ... --}}
                                    <tr>
                                        <td>Depreciation Percentage</td>
                                        @foreach ($assetTypes as $type)
                                            <td style="text-align:right">
                                                {{ number_format($type->depreciation_rate ?? 0, 2) }} %</td>
                                        @endforeach
                                        <th style="text-align:center">-</th>
                                    </tr>

                                    <tr>
                                        <td colspan="{{ $assetTypes->count() + 2 }}">
                                            <h3 style="text-align:left">Historical Costs</h3>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Balance as at {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</td>
                                        @php $totalOpening = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $openingBalances[$type->id] ?? 0;
                                                $totalOpening += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalOpening, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Additions during period</td>
                                        @php $totalAdd = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $additions[$type->id] ?? 0;
                                                $totalAdd += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalAdd, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Revaluation Surplus during period</td>
                                        @php $totalReval = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $revaluationValues[$type->id] ?? 0;
                                                $totalReval += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalReval, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Disposal during period</td>
                                        @php $totalDisp = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $disposalValues[$type->id] ?? 0;
                                                $totalDisp += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalDisp, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Total as at {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</td>
                                        @php $totalEnd = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $totalAsAtEnd[$type->id] ?? 0;
                                                $totalEnd += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalEnd, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td colspan="{{ $assetTypes->count() + 2 }}">
                                            <h3 style="text-align:left">Depreciation</h3>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Balance as at {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</td>
                                        @php $totalPrevDep = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $prev =
                                                    ($cumulativeDepreciation[$type->id] ?? 0) -
                                                    ($depreciation[$type->id] ?? 0);
                                                $totalPrevDep += $prev;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($prev, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalPrevDep, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Depreciation during period</td>
                                        @php $totalDep = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $depreciation[$type->id] ?? 0;
                                                $totalDep += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalDep, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Depreciation of Disposal during period</td>
                                        @foreach ($assetTypes as $type)
                                            <td style="text-align:right">{{ number_format(0, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format(0, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Total as at {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</td>
                                        @php $totalCumDep = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $cumulativeDepreciation[$type->id] ?? 0;
                                                $totalCumDep += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalCumDep, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td colspan="{{ $assetTypes->count() + 2 }}">
                                            <h3 style="text-align:left">Net Book Value</h3>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Book value as at {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</td>
                                        @php $totalBook = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $bookValues[$type->id] ?? 0;
                                                $totalBook += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalBook, 2) }}</th>
                                    </tr>

                                    <tr>
                                        <td>Book value as at previous period end</td>
                                        @php $totalPrevBook = 0; @endphp
                                        @foreach ($assetTypes as $type)
                                            @php
                                                $v = $closingBalancesPreviousYear[$type->id] ?? 0;
                                                $totalPrevBook += $v;
                                            @endphp
                                            <td style="text-align:right">{{ number_format($v, 2) }}</td>
                                        @endforeach
                                        <th style="text-align:right">{{ number_format($totalPrevBook, 2) }}</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        {{-- DETAILS grouped by category --}}
                        <div class="table-responsive mt-3">
                            <h4>Details</h4>

                            <table id="detailsTable" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Description / Asset</th>
                                        <th>Purchase Date</th>

                                        {{-- COST / VALUATION --}}
                                        <th style="text-align:right">As at
                                            {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</th>
                                        <th style="text-align:right">Additions (During)</th>
                                        <th style="text-align:right">Revaluations (During)</th>
                                        <th style="text-align:right">Disposals (During)</th>
                                        <th style="text-align:right">As at
                                            {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</th>

                                        {{-- DEPRECIATION --}}
                                        <th style="text-align:right">Cum. Dep. as at
                                            {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</th>
                                        <th style="text-align:right">Charges (Period)</th>
                                        <th style="text-align:right">Rate %</th>

                                        {{-- BOOK VALUE --}}
                                        <th style="text-align:right">Book as at
                                            {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</th>
                                        <th style="text-align:right">Book as at
                                            {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // overall totals across categories
                                        $grand = [
                                            'opening' => 0,
                                            'additions' => 0,
                                            'revaluations' => 0,
                                            'disposals' => 0,
                                            'end' => 0,
                                            'cum_dep_start' => 0,
                                            'period_dep' => 0,
                                            'book_start' => 0,
                                            'book_end' => 0,
                                        ];
                                    @endphp

                                    @foreach ($detailsByCategory as $catId => $cat)
                                        {{-- Category header row --}}
                                        <tr style="background:#efefef;font-weight:bold">
                                            <td colspan="13">{{ strtoupper($cat['name']) }}</td>
                                        </tr>

                                        @foreach ($cat['rows'] as $r)
                                            <tr>
                                                {{-- show asset name if present, otherwise serial/ref --}}
                                                <td>{{ $r->asset_name ?: $r->serial_no }}</td>
                                                <td>{{ $r->purchase_date ?: '-' }}</td>

                                                <td style="text-align:right">{{ number_format($r->opening_cost, 2) }}</td>
                                                <td style="text-align:right">{{ number_format($r->additions, 2) }}</td>
                                                <td style="text-align:right">{{ number_format($r->revaluations, 2) }}</td>
                                                <td style="text-align:right">{{ number_format($r->disposals, 2) }}</td>
                                                <td style="text-align:right">{{ number_format($r->total_at_end, 2) }}</td>

                                                <td style="text-align:right">{{ number_format($r->cum_dep_start, 2) }}
                                                </td>
                                                <td style="text-align:right">
                                                    {{ number_format($r->depreciation_period, 2) }}</td>
                                                <td style="text-align:right">{{ number_format($r->rate, 2) }} %</td>

                                                <td style="text-align:right">{{ number_format($r->book_start, 2) }}</td>
                                                <td style="text-align:right">{{ number_format($r->book_end, 2) }}</td>
                                            </tr>

                                            @php
                                                $grand['opening'] += $r->opening_cost;
                                                $grand['additions'] += $r->additions;
                                                $grand['revaluations'] += $r->revaluations;
                                                $grand['disposals'] += $r->disposals;
                                                $grand['end'] += $r->total_at_end;
                                                $grand['cum_dep_start'] += $r->cum_dep_start;
                                                $grand['period_dep'] += $r->depreciation_period;
                                                $grand['book_start'] += $r->book_start;
                                                $grand['book_end'] += $r->book_end;
                                            @endphp
                                        @endforeach
                                        {{-- Category subtotal row --}}
                                        <tr style="font-weight:bold;background:#fafafa">
                                            <td style="text-align:left">TOTAL {{ strtoupper($cat['name']) }}</td>
                                            <td></td>
                                            <td style="text-align:right">{{ number_format($cat['totals']['opening'], 2) }}
                                            </td>
                                            <td style="text-align:right">
                                                {{ number_format($cat['totals']['additions'], 2) }}</td>
                                            <td style="text-align:right">
                                                {{ number_format($cat['totals']['revaluations'], 2) }}</td>
                                            <td style="text-align:right">
                                                {{ number_format($cat['totals']['disposals'], 2) }}</td>
                                            <td style="text-align:right">{{ number_format($cat['totals']['end'], 2) }}
                                            </td>
                                            <td style="text-align:right">
                                                {{ number_format($cat['totals']['cum_dep_start'], 2) }}</td>
                                            <td style="text-align:right">
                                                {{ number_format($cat['totals']['period_dep'], 2) }}</td>
                                            <td style="text-align:right">{{ number_format($cat['rate'] ?? 0, 2) }} %</td>
                                            <td style="text-align:right">
                                                {{ number_format($cat['totals']['book_start'], 2) }}</td>
                                            <td style="text-align:right">
                                                {{ number_format($cat['totals']['book_end'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                                <tfoot>
                                    <tr style="font-weight:bold">
                                        <td style="text-align:left">GRAND TOTAL</td>
                                        <td></td>
                                        <td style="text-align:right">{{ number_format($grand['opening'], 2) }}</td>
                                        <td style="text-align:right">{{ number_format($grand['additions'], 2) }}</td>
                                        <td style="text-align:right">{{ number_format($grand['revaluations'], 2) }}</td>
                                        <td style="text-align:right">{{ number_format($grand['disposals'], 2) }}</td>
                                        <td style="text-align:right">{{ number_format($grand['end'], 2) }}</td>
                                        <td style="text-align:right">{{ number_format($grand['cum_dep_start'], 2) }}</td>
                                        <td style="text-align:right">{{ number_format($grand['period_dep'], 2) }}</td>
                                        <td></td>
                                        <td style="text-align:right">{{ number_format($grand['book_start'], 2) }}</td>
                                        <td style="text-align:right">{{ number_format($grand['book_end'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div> <!-- /ibox-content -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportTableToExcel(tableId, filename = '') {
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableId);
            if (!tableSelect) {
                alert('Table not found: ' + tableId);
                return;
            }
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            filename = filename ? filename + '.xls' : 'export.xls';
            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            if (navigator.msSaveOrOpenBlob) {
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        }

        function printReceipt(divId) {
            var printContents = document.getElementById(divId).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
        $(document).ready(function() {
            $('.select2_demo_2').select2({
                width: '100%',
                theme: 'bootstrap4'
            });
        });
    </script>
@endsection
