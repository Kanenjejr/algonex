@extends('layouts.ManftrMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Inventory And Manufacturing Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Raw Material Stock</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-primary">
                        <h5>Stock Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Raw Material</th>
                                        <th>Qty In</th>
                                        <th>Qty Out</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stocks as $k => $s)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ optional($s->rawMaterial)->material_name }}</td>
                                            <td>{{ number_format((float) $s->qty_in, 2) }}</td>
                                            <td>{{ number_format((float) $s->qty_out, 2) }}</td>
                                            <td>{{ number_format((float) $s->balance, 2) }}</td>
                                            <td>{{ $s->status }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No stock found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" style="text-align:right;">Totals</th>
                                        <th>{{ number_format((float) collect($stocks)->sum('qty_in'), 2) }}</th>
                                        <th>{{ number_format((float) collect($stocks)->sum('qty_out'), 2) }}</th>
                                        <th>{{ number_format((float) collect($stocks)->sum('balance'), 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="button" class="btn btn-primary" onclick="printReceipt('stockPrintArea')">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>

                <div id="stockPrintArea" style="display:none;">
                    <h3>Manufacturing Raw Material Stock</h3>
                    <table border="1" cellspacing="0" cellpadding="6" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Raw Material</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stocks as $k => $s)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ optional($s->rawMaterial)->material_name }}</td>
                                    <td>{{ number_format((float) $s->qty_in, 2) }}</td>
                                    <td>{{ number_format((float) $s->qty_out, 2) }}</td>
                                    <td>{{ number_format((float) $s->balance, 2) }}</td>
                                    <td>{{ $s->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        @page{ size:A4 portrait; margin:12mm; }
        html, body{ font-family:Arial,sans-serif; font-size:12px; color:#000; }
        table{ width:100%; border-collapse:collapse; }
        th, td{ border:1px solid #000; padding:6px; }
    </style>`;

            doc.write('<html><head><title>Manufacturing Stock</title>' + style + '</head><body>');
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
