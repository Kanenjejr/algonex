@extends('layouts.ManftrMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Inventory And Manufacturing Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Manufacturing Stock Movement</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Filter Movement</h5>
            </div>
            <div class="ibox-content">
                <form method="GET" action="{{ route('manfctr.stock.movement.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" value="{{ $start }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" value="{{ $end }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Raw Material</label>
                            <select name="raw_id" id="raw_id" class="form-control select2_filter">
                                <option value="">-- All --</option>
                                @foreach ($raws as $r)
                                    <option value="{{ $r->id }}" @if ($filterRaw == $r->id) selected @endif>
                                        {{ $r->material_code }}{{ $r->material_code ? ' - ' : '' }}{{ $r->material_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2" style="padding-top:25px;">
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Movement Table</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Raw Material</th>
                                <th>Reference Type</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Balance After</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $k => $r)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $r->movement_date }}</td>
                                    <td>{{ optional($r->rawMaterial)->material_name }}</td>
                                    <td>{{ $r->reference_type }}</td>
                                    <td>{{ number_format((float) $r->qty_in, 2) }}</td>
                                    <td>{{ number_format((float) $r->qty_out, 2) }}</td>
                                    <td>{{ number_format((float) $r->balance_after, 2) }}</td>
                                    <td>{{ $r->remarks }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="button" class="btn btn-primary" onclick="printReceipt('movementPrintArea')">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>

                <div id="movementPrintArea" style="display:none;">
                    <h3>Manufacturing Stock Movement</h3>
                    <p>From: {{ $start }} | To: {{ $end }}</p>
                    <table border="1" cellspacing="0" cellpadding="6" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Raw Material</th>
                                <th>Reference Type</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Balance After</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $k => $r)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $r->movement_date }}</td>
                                    <td>{{ optional($r->rawMaterial)->material_name }}</td>
                                    <td>{{ $r->reference_type }}</td>
                                    <td>{{ number_format((float) $r->qty_in, 2) }}</td>
                                    <td>{{ number_format((float) $r->qty_out, 2) }}</td>
                                    <td>{{ number_format((float) $r->balance_after, 2) }}</td>
                                    <td>{{ $r->remarks }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if ($('#raw_id').length) {
                $('#raw_id').select2({
                    width: '100%',
                    theme: 'bootstrap4'
                });
            }
        });

        function printReceipt(ele) {
            var content = document.getElementById(ele);
            if (!content) return alert('Nothing to print');

            var pri = window.open('', '_blank', 'height=842,width=595');
            var doc = pri.document.open();

            var style = `<style>
        @page{ size:A4 landscape; margin:12mm; }
        html, body{ font-family:Arial,sans-serif; font-size:12px; color:#000; }
        table{ width:100%; border-collapse:collapse; }
        th, td{ border:1px solid #000; padding:6px; }
    </style>`;

            doc.write('<html><head><title>Manufacturing Stock Movement</title>' + style + '</head><body>');
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
