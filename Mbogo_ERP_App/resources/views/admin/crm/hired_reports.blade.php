@extends('layouts.salesMaster')
@section('content')

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Customers, Supplies & Interactions Dashboard</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Hired Equipment Report</strong>
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
    <!-- (keep all markup exactly as you already had above) -->
    <div class="col-12">
        <h3 class="mb-2 page-title">Hired Equipment — Reports (Paid vs Unpaid)</h3>
        @can('Export-Hired-Equipment-Reports')
            <a id="exportCsvBtn" class="btn mb-2 btn-success"
                href="{{ route('hired.workings.reports.export', request()->all()) }}">Export CSV/Excel</a>
            <button onclick="printReceipt('form1')"class="btn btn-sm btn-primary float-right mr-2"><i class="fa fa-print"></i>
                Print Report</button>
        @endcan
    </div>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card p-2">
                <h6>Paid Hours</h6>
                <h3>{{ number_format($paidHours, 2) }}</h3>
                <small>{{ $paidCount }} records • Amount: {{ number_format($paidAmount, 2) }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-2">
                <h6>Unpaid (Pending) Hours</h6>
                <h3>{{ number_format($unpaidHours, 2) }}</h3>
                <small>{{ $unpaidCount }} records • Amount: {{ number_format($unpaidAmount, 2) }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-2">
                <h6>Total Hours</h6>
                <h3>{{ number_format($totals['total_hours'], 2) }}</h3>
                <small>Total Amount: {{ number_format($totals['total_amount'], 2) }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-2">
                <h6>Filters</h6>
                <small>Use date range / equipment / status</small>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form method="GET" action="{{ route('hired.workings.reports') }}" class="form-inline">
            <div class="form-row w-100">
                <div class="form-group col-md-2">
                    <label>Start</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $start ?? '' }}">
                </div>
                <div class="form-group col-md-2">
                    <label>End</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $end ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label>Equipment</label>
                    <select name="equipment_id" class="form-control select2_modal">
                        <option value="">-- All equipment --</option>
                        @foreach ($equipments as $e)
                            <option value="{{ $e->id }}" @if ($equipmentId == $e->id) selected @endif>
                                {{ $e->EqpmntNo }} ({{ $e->PaymentPerDay }}/Day)</option>
                        @endforeach
                    </select>
                </div>

                @if ($workPoints && $workPoints->count())
                    <div class="form-group col-md-2">
                        <label>Work Point</label>
                        <select name="work_point_id" class="form-control select2_modal">
                            <option value="">-- All --</option>
                            @foreach ($workPoints as $wp)
                                <option value="{{ $wp->id }}" @if ($workPointId == $wp->id) selected @endif>
                                    {{ $wp->work_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-group col-md-2">
                    <label>Payment</label>
                    <select name="payment_status" class="form-control select2_modal">
                        <option value="">All</option>
                        <option value="Paid" @if ($paymentStatus == 'Paid') selected @endif>Paid</option>
                        <option value="Pending" @if ($paymentStatus == 'Pending') selected @endif>Pending</option>
                    </select>
                </div>

                <div class="form-group col-md-1 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>

            </div>
        </form>
    </div>

    <div id="form1" class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>Hired Equipment Workings Details Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Owner Name</th>
                                        <th>Equipment</th>
                                        <th>Operator Name</th>
                                        <th>Rate Per Day</th>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Hours</th>
                                        <th>Minutes</th>
                                        <th>Total Hours</th>
                                        <th>Total Price</th>
                                        <th>Payment Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $k => $w)
                                        @php
                                            $totalHours = (float) $w->WorkingHours + (float) $w->Minutes / 60.0;
                                        @endphp
                                        <tr>
                                            <td>{{ $items->firstItem() + $k }}</td>
                                            <td>{{ optional($w->equipment->cstm)->customer_name ?? '-' }}</td>
                                            <td>{{ optional($w->equipment)->EqpmntNo ?? '-' }}</td>
                                            <td>{{ optional($w->equipment)->OperatorName ?? '-' }}</td>
                                            <td>{{ number_format($w->equipment->PaymentPerDay, 2) }}</td>
                                            <td>{{ optional($w->WorkingDate)->format('Y-m-d') ?? '-' }}</td>
                                            <td>{{ $w->TimeIn ?? '-' }}</td>
                                            <td>{{ $w->TimeOut ?? '-' }}</td>
                                            <td>{{ number_format($w->WorkingHours, 2) }}</td>
                                            <td>{{ number_format($w->Minutes, 2) }}</td>
                                            <td>{{ number_format($totalHours, 2) }}</td>
                                            <td>{{ number_format($w->TotalPrice, 2) }}</td>
                                            <td>
                                                @if ($w->PaymentStatus == 'Paid')
                                                    <span class="badge badge-success">Paid</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @can('Change-Hired-Equipment-PaymentStatus')
                                                    @if ($w->PaymentStatus !== 'Paid')
                                                        <button class="btn btn-sm btn-success btn-mark-paid"
                                                            data-id="{{ encrypt($w->id) }}">Mark Paid</button>
                                                    @else
                                                        <span class="badge badge-success">Paid</span>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-3">
                                {{ $items->links() }}
                            </div>

                        </div> <!-- /.table-responsive -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- REPLACED SCRIPT: robust delegation + encodeURIComponent + form submit fallback -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // initialize select2 normal selects (not inside modal)
            $('.select2_modal').each(function() {
                try {
                    $(this).select2({
                        width: '100%',
                        theme: 'bootstrap4'
                    });
                } catch (e) {}
            });

            // Delegated click handler — works across pagination & redraws
            document.addEventListener('click', function(event) {
                var el = event.target;
                // DELETE: find closest .btn-delete-working if present
                var deleteBtn = el.closest ? el.closest('.btn-delete-working') : null;
                if (deleteBtn) {
                    event.preventDefault();
                    var raw = deleteBtn.getAttribute('data-id');
                    if (!raw) {
                        alert('Missing identifier');
                        return;
                    }
                    var encoded = encodeURIComponent(raw);
                    var removeUrlBase = "{{ url('/admin/hired-equipment/workings/remove') }}/";
                    var finalUrl = removeUrlBase + encoded;

                    var confirmDelete = function() {
                        // use hidden GET form to avoid browser URL encoding pitfalls and preserve behavior
                        try {
                            var form = document.createElement('form');
                            form.method = 'GET';
                            form.action = finalUrl;
                            form.style.display = 'none';
                            document.body.appendChild(form);
                            setTimeout(function() {
                                form.submit();
                            }, 60);
                        } catch (err) {
                            console.error('Delete fallback error:', err);
                            window.location.href = finalUrl;
                        }
                    };

                    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will mark the record as Deleted.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes'
                        }).then(function(res) {
                            if (res && res.isConfirmed) confirmDelete();
                        }).catch(function(err) {
                            console.error('Swal error on delete:', err);
                            if (confirm('This will mark the record as Deleted. Continue?'))
                                confirmDelete();
                        });
                    } else {
                        if (confirm('This will mark the record as Deleted. Continue?')) confirmDelete();
                    }
                    return; // exit handler
                }

                // MARK AS PAID: find closest .btn-mark-paid
                var markBtn = el.closest ? el.closest('.btn-mark-paid') : null;
                if (markBtn) {
                    event.preventDefault();
                    var rawId = markBtn.getAttribute('data-id');
                    if (!rawId) {
                        alert('Missing identifier');
                        return;
                    }

                    var doMark = function() {
                        try {
                            var encodedId = encodeURIComponent(rawId);
                            // build route using direct url to avoid route() string replacement issues
                            var baseUrl = "{{ url('/hired-equipment/workings/mark-paid') }}/" +
                                encodedId;
                            var qs = window.location.search || '';
                            var full = baseUrl + qs;
                            // submit hidden GET form to preserve query string and avoid encoding issues
                            var f = document.createElement('form');
                            f.method = 'GET';
                            f.action = full;
                            f.style.display = 'none';
                            document.body.appendChild(f);
                            setTimeout(function() {
                                f.submit();
                            }, 60);
                        } catch (err) {
                            console.error('Mark Paid fallback error:', err);
                            try {
                                window.location.href =
                                    "{{ url('/hired-equipment/workings/mark-paid') }}/" +
                                    encodeURIComponent(rawId) + (window.location.search || '');
                            } catch (err2) {
                                alert('Unable to perform request. See console.');
                                console.error(err2);
                            }
                        }
                    };

                    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                        Swal.fire({
                            title: 'Mark as Paid?',
                            text: "This will mark the working record as paid and update the report totals.",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, mark as Paid'
                        }).then(function(res) {
                            if (res && res.isConfirmed) doMark();
                        }).catch(function(err) {
                            console.error('Swal error on mark-paid:', err);
                            if (confirm('Mark this working as paid?')) doMark();
                        });
                    } else {
                        if (confirm('Mark this working as paid?')) doMark();
                    }
                    return; // exit handler
                }
                // no handled target — do nothing
            }, false);
        });
    </script>
@endsection
