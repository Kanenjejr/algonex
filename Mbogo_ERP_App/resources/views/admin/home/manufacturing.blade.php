@extends('layouts.ManftrMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Inventory And Manufacturing Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('manufacturing') }}">Inventory And Manufacturing</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Dashboard</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php
                        use Carbon\Carbon;
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
                            </tr>
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
            if (curr_hour > 24) curr_hour = curr_hour - 24;
            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }
        timedMsg();
    </script>

    @can('Inventory-Manufacturing-Modules')
        <div class="col-12 mb-3">
            <h3 class="page-title">Manufacturing & Inventory Dashboard</h3>
        </div>

        {{-- TOP SUMMARY CARDS --}}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(28,45,94,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#0f4c81 0%, #1f6fb2 100%); color:#fff;">
                        <h5 style="color:#fff;">Total Raw Materials</h5>
                    </div>
                    <div class="ibox-content" style="background:#f8fbff;">
                        <strong
                            style="font-size:30px; color:#0f4c81;">{{ isset($rawMaterials) ? $rawMaterials->count() : 0 }}</strong>
                        <p class="text-muted small mt-2">Registered materials</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(40,80,45,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#0f8a5f 0%, #1dbf73 100%); color:#fff;">
                        <h5 style="color:#fff;">Requested Qty</h5>
                    </div>
                    <div class="ibox-content" style="background:#f4fffa;">
                        <strong
                            style="font-size:30px; color:#0f8a5f;">{{ isset($requestedRawTotal) ? number_format($requestedRawTotal, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Requested raw materials</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(159,92,27,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#d97706 0%, #f59e0b 100%); color:#fff;">
                        <h5 style="color:#fff;">Received Qty</h5>
                    </div>
                    <div class="ibox-content" style="background:#fffaf2;">
                        <strong
                            style="font-size:30px; color:#b45309;">{{ isset($receivedRawTotal) ? number_format($receivedRawTotal, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Manufacturing receipts</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(115,33,46,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#b42318 0%, #ef4444 100%); color:#fff;">
                        <h5 style="color:#fff;">Consumed Qty</h5>
                    </div>
                    <div class="ibox-content" style="background:#fff7f7;">
                        <strong
                            style="font-size:30px; color:#b42318;">{{ isset($consumedRawTotal) ? number_format($consumedRawTotal, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Materials consumed</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECOND ROW --}}
        <div class="row mt-3">
            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(79,70,229,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#4338ca 0%, #6366f1 100%); color:#fff;">
                        <h5 style="color:#fff;">Available Raw Stock</h5>
                    </div>
                    <div class="ibox-content" style="background:#f7f7ff;">
                        <strong
                            style="font-size:30px; color:#4338ca;">{{ isset($availableRaw) ? number_format($availableRaw, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Current stock balance</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(14,116,144,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#0e7490 0%, #06b6d4 100%); color:#fff;">
                        <h5 style="color:#fff;">Total Products</h5>
                    </div>
                    <div class="ibox-content" style="background:#f3fcff;">
                        <strong style="font-size:30px; color:#0e7490;">{{ isset($products) ? $products->count() : 0 }}</strong>
                        <p class="text-muted small mt-2">Registered products</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(22,101,52,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#166534 0%, #22c55e 100%); color:#fff;">
                        <h5 style="color:#fff;">Packed Production</h5>
                    </div>
                    <div class="ibox-content" style="background:#f4fff7;">
                        <strong
                            style="font-size:30px; color:#166534;">{{ isset($packedProductsTotal) ? number_format($packedProductsTotal, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Packed finished products</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 8px 20px rgba(127,29,29,.08);">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#991b1b 0%, #f87171 100%); color:#fff;">
                        <h5 style="color:#fff;">Available Packed</h5>
                    </div>
                    <div class="ibox-content" style="background:#fff7f7;">
                        <strong
                            style="font-size:30px; color:#991b1b;">{{ isset($availablePacked) ? number_format($availablePacked, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Packed - issued products</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- THIRD ROW SMALL SUMMARY --}}
        <div class="row mt-3">
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Issued Against Requests</h5>
                    </div>
                    <div class="ibox-content">
                        <strong
                            style="font-size:26px">{{ isset($issuedAgainstRequestTotal) ? number_format($issuedAgainstRequestTotal, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Issued qty from requests</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title bg-warning">
                        <h5>Remaining Request Qty</h5>
                    </div>
                    <div class="ibox-content">
                        <strong
                            style="font-size:26px">{{ isset($remainingRequestTotal) ? number_format($remainingRequestTotal, 2) : '0.00' }}</strong>
                        <p class="text-muted small mt-2">Still pending to be issued</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title bg-primary">
                        <h5>Manufacturing Stock In / Out</h5>
                    </div>
                    <div class="ibox-content">
                        <strong style="font-size:20px">IN:
                            {{ isset($stockQtyInTotal) ? number_format($stockQtyInTotal, 2) : '0.00' }}</strong>
                        <br>
                        <strong style="font-size:20px">OUT:
                            {{ isset($stockQtyOutTotal) ? number_format($stockQtyOutTotal, 2) : '0.00' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLES --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Raw Materials</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Material</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($rawMaterials) && $rawMaterials->count())
                                        @foreach ($rawMaterials as $k => $r)
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ $r->material_name }}</td>
                                                <td>{{ $r->material_code ?? '-' }}</td>
                                                <td>{{ $r->status }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No raw materials found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Products</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($products) && $products->count())
                                        @foreach ($products as $k => $p)
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ $p->product_name }}</td>
                                                <td>{{ $p->product_size ?? '-' }}</td>
                                                <td>{{ $p->status }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No products found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STOCK TABLE --}}
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="ibox">
                    <div class="ibox-title bg-primary">
                        <h5>Manufacturing Material Stock</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
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
                                    @if (isset($materialStocks) && $materialStocks->count())
                                        @foreach ($materialStocks as $k => $s)
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ optional($s->rawMaterial)->material_name ?? '-' }}</td>
                                                <td>{{ number_format((float) $s->qty_in, 2) }}</td>
                                                <td>{{ number_format((float) $s->qty_out, 2) }}</td>
                                                <td>{{ number_format((float) $s->balance, 2) }}</td>
                                                <td>{{ $s->status ?? 'Active' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No stock records found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden;">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#173a7a 0%, #244f96 100%); color:#fff;">
                        <h5 style="color:#fff;">Raw Material Flow</h5>
                    </div>
                    <div class="ibox-content">
                        <div style="height:260px">
                            <canvas id="rawChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden;">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#0e7490 0%, #06b6d4 100%); color:#fff;">
                        <h5 style="color:#fff;">Product Flow</h5>
                    </div>
                    <div class="ibox-content">
                        <div style="height:260px">
                            <canvas id="productChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECONDARY CHARTS --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden;">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#166534 0%, #22c55e 100%); color:#fff;">
                        <h5 style="color:#fff;">Raw Material Summary Bar</h5>
                    </div>
                    <div class="ibox-content">
                        <div style="height:260px">
                            <canvas id="rawBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="ibox" style="border-radius:14px; overflow:hidden;">
                    <div class="ibox-title" style="background:linear-gradient(135deg,#991b1b 0%, #ef4444 100%); color:#fff;">
                        <h5 style="color:#fff;">Products Summary Doughnut</h5>
                    </div>
                    <div class="ibox-content">
                        <div style="height:260px">
                            <canvas id="productDoughnutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @php
            $safeGraphLabels = isset($graphLabels)
                ? $graphLabels
                : ['Requested', 'Received', 'Consumed', 'Available Stock'];
            $safeGraphRawData = isset($graphRawData) ? $graphRawData : [0, 0, 0, 0];

            $safeGraphLabelsProducts = isset($graphLabelsProducts)
                ? $graphLabelsProducts
                : ['Packed Production', 'Issued Products', 'Available Packed'];
            $safeGraphProductData = isset($graphProductData) ? $graphProductData : [0, 0, 0];
        @endphp

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const rawLabels = {!! json_encode($safeGraphLabels) !!};
            const rawData = {!! json_encode($safeGraphRawData) !!};

            const productLabels = {!! json_encode($safeGraphLabelsProducts) !!};
            const productData = {!! json_encode($safeGraphProductData) !!};

            const rawCanvas = document.getElementById('rawChart');
            if (rawCanvas) {
                const rawCtx = rawCanvas.getContext('2d');
                new Chart(rawCtx, {
                    type: 'doughnut',
                    data: {
                        labels: rawLabels,
                        datasets: [{
                            data: rawData,
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                            borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutoutPercentage: 58,
                        legend: {
                            position: 'bottom'
                        }
                    }
                });
            }

            const productCanvas = document.getElementById('productChart');
            if (productCanvas) {
                const productCtx = productCanvas.getContext('2d');
                new Chart(productCtx, {
                    type: 'bar',
                    data: {
                        labels: productLabels,
                        datasets: [{
                            label: 'Qty',
                            data: productData,
                            backgroundColor: ['#06b6d4', '#ef4444', '#22c55e'],
                            borderRadius: 8,
                            maxBarThickness: 55
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }

            const rawBarCanvas = document.getElementById('rawBarChart');
            if (rawBarCanvas) {
                const rawBarCtx = rawBarCanvas.getContext('2d');
                new Chart(rawBarCtx, {
                    type: 'bar',
                    data: {
                        labels: rawLabels,
                        datasets: [{
                            label: 'Qty',
                            data: rawData,
                            backgroundColor: ['#1d4ed8', '#059669', '#d97706', '#7c3aed'],
                            borderRadius: 8,
                            maxBarThickness: 50
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }

            const productDoughnutCanvas = document.getElementById('productDoughnutChart');
            if (productDoughnutCanvas) {
                const productDoughnutCtx = productDoughnutCanvas.getContext('2d');
                new Chart(productDoughnutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: productLabels,
                        datasets: [{
                            data: productData,
                            backgroundColor: ['#14b8a6', '#f43f5e', '#84cc16'],
                            borderColor: ['#ffffff', '#ffffff', '#ffffff'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutoutPercentage: 55,
                        legend: {
                            position: 'bottom'
                        }
                    }
                });
            }
        </script>
    @endcan
@endsection
