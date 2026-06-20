@extends('layouts.salesMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="row mb-3">
        <div class="col-md-12">

            <a href="{{ route('stock.management.dashboard') }}"
               class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>

            <button onclick="window.print()"
                    class="btn btn-primary pull-right">
                <i class="fa fa-print"></i> Print
            </button>

        </div>
    </div>

    <div class="ibox">
        <div class="ibox-content">

            {{-- Header --}}
            <div class="physical-stock-header-img">
                <img src="{{ asset('img/header.png') }}" alt="Header">
            </div>

            {{-- COPY HII SEHEMU YOTE TOKA DASHBOARD --}}
            <div id="physicalStockPrintArea" class="physical-stock-print-area">

                @forelse($physicalStockGroups as $groupRows)

                    @php
                        $firstRow = $groupRows->first();

                        $groupCompanyTitle = trim(
                            ($firstRow['company_name'] ?? 'NO COMPANY SITE')
                            .' '.
                            ($firstRow['company_code']
                                ? '(' . $firstRow['company_code'] . ')'
                                : '')
                        );

                        $groupUnitTitle = trim(
                            ($firstRow['unit_name'] ?? 'NO COMPANY UNIT')
                            .' '.
                            ($firstRow['unit_code']
                                ? '(' . $firstRow['unit_code'] . ')'
                                : '')
                        );
                    @endphp

                    <div class="physical-stock-group">

                        <div class="physical-stock-title">
                            {{ strtoupper($groupCompanyTitle) }}
                            PHYSICAL STOCK AS AT
                            {{ $printAsAtDateFormatted }}
                        </div>

                        <div class="physical-stock-subtitle">
                            COMPANY UNIT:
                            {{ strtoupper($groupUnitTitle) }}
                        </div>

                        <table class="physical-stock-table">

                            <thead>
                                <tr>
                                    <th>S/NO</th>
                                    <th>PRODUCT</th>
                                    <th>DETAILS</th>
                                    <th>UM</th>
                                    <th>OPENING</th>
                                    <th>RECEIVED</th>
                                    <th>TOTAL</th>
                                    <th>ISSUED</th>
                                    <th>CLOSING</th>
                                    <th>PRICE</th>
                                    <th>VALUE</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($groupRows as $index => $row)

                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row['product'] }}</td>
                                    <td>{{ $row['details'] }}</td>
                                    <td>{{ $row['um'] }}</td>
                                    <td>{{ number_format($row['opening']) }}</td>
                                    <td>{{ number_format($row['received']) }}</td>
                                    <td>{{ number_format($row['total_stock']) }}</td>
                                    <td>{{ number_format($row['issued']) }}</td>
                                    <td>{{ number_format($row['closing']) }}</td>
                                    <td>{{ number_format($row['price']) }}</td>
                                    <td>{{ number_format($row['value'],2) }}</td>
                                </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @empty

                    <div class="alert alert-warning">
                        No Inventory Records Found
                    </div>

                @endforelse

            </div>

        </div>
    </div>

</div>

@endsection