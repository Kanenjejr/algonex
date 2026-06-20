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
                    <strong>Hired Equipment Workings</strong>
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
    <div class="col-12">
        <h3 class="mb-2 page-title">Hired Equipment — Workings</h3>
        @can('Register-Hired-Equipment-Working')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#workingCreateModal">Add Working</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>Hired Equipment Workings Details Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
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
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $k => $w)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ optional($w->equipment->cstm)->customer_name ?? '-' }}</td>
                                            <td>{{ optional($w->equipment)->EqpmntNo ?? '-' }}</td>
                                            <td>{{ optional($w->equipment)->OperatorName ?? '-' }}</td>
                                            <td>{{ number_format($w->equipment->PaymentPerDay, 2) }}</td>
                                            <td>{{ optional($w->WorkingDate)->format('Y-m-d') ?? '-' }}</td>
                                            <td>{{ $w->TimeIn ?? '-' }}</td>
                                            <td>{{ $w->TimeOut ?? '-' }}</td>
                                            <td>{{ $w->WorkingHours }}</td>
                                            <td>{{ $w->Minutes }}</td>
                                            <td>{{ number_format($w->TotalPrice, 2) }}</td>
                                            <td>
                                                @if ($w->PaymentStatus == 'Paid')
                                                    <span class="badge badge-success">Paid</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>{{ $w->Status }}</td>
                                            <td>
                                                @if ($w->PaymentStatus !== 'Paid')
                                                    @can('Edit-Hired-Equipment-Working')
                                                        <button class="btn btn-sm btn-warning btn-edit-working"
                                                            data-id="{{ encrypt($w->id) }}"
                                                            data-hired_equipment_id="{{ $w->hired_equipment_id }}"
                                                            data-workingdate="{{ $w->WorkingDate ? $w->WorkingDate->format('Y-m-d') : '' }}"
                                                            data-timein="{{ $w->TimeIn }}"
                                                            data-timeout="{{ $w->TimeOut }}"
                                                            data-workinghours="{{ $w->WorkingHours }}"
                                                            data-minutes="{{ $w->Minutes }}"
                                                            data-paymentstatus="{{ $w->PaymentStatus }}"
                                                            data-status="{{ $w->Status }}">Edit</button>
                                                    @endcan
                                                    @can('Delete-Hired-Equipment-Working')
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-sm btn-danger btn-delete-working"
                                                            data-id="{{ encrypt($w->id) }}">Remove</a>
                                                    @endcan
                                                @else
                                                    <span class="badge badge-success">Paid</span>
                                                @endif
                                                @can('Change-Hired-Equipment-PaymentStatus')
                                                    @if ($w->PaymentStatus !== 'Paid')
                                                        <button class="btn btn-sm btn-success btn-mark-paid"
                                                            data-id="{{ encrypt($w->id) }}">Mark Paid</button>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- /.table-responsive -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="workingCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="workingCreateForm" action="{{ route('hired.workings.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Working Record</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Equipment <span style="color:red">*</span></label>
                            <select name="hired_equipment_id" id="create_hired_equipment_id"
                                class="form-control select2_modal" required>
                                <option value="">-- Select equipment --</option>
                                @foreach ($equipments as $e)
                                    <option value="{{ $e->id }}"
                                        data-day-rate="{{ $e->PaymentPerDay ?? $e->PaymentPerHour * 8 }}">
                                        {{ $e->EqpmntNo }} Model/type {{ $e->Model }} ->
                                        ({{ number_format($e->PaymentPerDay ?? $e->PaymentPerHour * 8, 2) }}/day)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col"><label>Working Date</label><input type="date" name="WorkingDate"
                                    class="form-control" required></div>
                            <div class="form-group col"><label>Time In</label><input id="create_time_in" type="time"
                                    name="TimeIn" class="form-control"></div>
                            <div class="form-group col"><label>Time Out</label><input id="create_time_out" type="time"
                                    name="TimeOut" class="form-control"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col"><label>Working Hours</label><input id="create_working_hours"
                                    type="number" step="1" name="WorkingHours" class="form-control"></div>
                            <div class="form-group col"><label>Minutes</label><input id="create_minutes" type="number"
                                    step="1" name="Minutes" class="form-control" min="0" max="59">
                            </div>
                            <div class="form-group col"><label>Total Price</label><input id="create_total_price"
                                    type="text" name="TotalPrice" class="form-control" readonly></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col"><label>Payment Status</label>
                                <select name="PaymentStatus" class="form-control select2_modal">
                                    <option value="Pending" selected>Pending</option>
                                    <option value="Paid">Paid</option>
                                </select>
                            </div>
                            <div class="form-group col"><label>Status</label>
                                <select name="Status" class="form-control select2_modal">
                                    <option value="Active" selected>Active</option>
                                    <option value="Deleted">Deleted</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" onclick="handleConfirmSubmit('workingCreateForm')"
                            class="btn mb-2 btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="workingEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="workingEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Working Record</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_working_id" name="edit_id">

                        <div class="form-group">
                            <label>Equipment <span style="color:red">*</span></label>
                            <select id="edit_hired_equipment_id" name="hired_equipment_id"
                                class="form-control select2_modal" required>
                                <option value="">-- Select equipment --</option>
                                @foreach ($equipments as $e)
                                    <option value="{{ $e->id }}"
                                        data-day-rate="{{ $e->PaymentPerDay ?? $e->PaymentPerHour * 8 }}">
                                        {{ $e->EqpmntNo }} Model/type {{ $e->Model }} ->
                                        ({{ number_format($e->PaymentPerDay ?? $e->PaymentPerHour * 8, 2) }}/day)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col"><label>Working Date</label><input id="edit_working_date"
                                    type="date" name="WorkingDate" class="form-control" required></div>
                            <div class="form-group col"><label>Time In</label><input id="edit_time_in" type="time"
                                    name="TimeIn" class="form-control"></div>
                            <div class="form-group col"><label>Time Out</label><input id="edit_time_out" type="time"
                                    name="TimeOut" class="form-control"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col"><label>Working Hours</label><input id="edit_working_hours"
                                    type="number" step="1" name="WorkingHours" class="form-control"></div>
                            <div class="form-group col"><label>Minutes</label><input id="edit_minutes" type="number"
                                    step="1" name="Minutes" class="form-control" min="0" max="59">
                            </div>
                            <div class="form-group col"><label>Total Price</label><input id="edit_total_price"
                                    type="text" name="TotalPrice" class="form-control" readonly></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col"><label>Payment Status</label>
                                <select id="edit_payment_status" name="PaymentStatus" class="form-control select2_modal">
                                    <option value="Pending">Pending</option>
                                    <option value="Paid">Paid</option>
                                </select>
                            </div>
                            <div class="form-group col"><label>Status</label>
                                <select id="edit_status" name="Status" class="form-control select2_modal">
                                    <option value="Active">Active</option>
                                    <option value="Deleted">Deleted</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" onclick="handleConfirmSubmit('workingEditForm')"
                            class="btn mb-2 btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // helper: init select2 in modals
            function initSelect2WithParent($el, parentSelector) {
                if (!$el || !$el.length) return;
                if ($el.data('select2')) {
                    try {
                        $el.select2('destroy');
                    } catch (e) {
                        /* ignore */
                    }
                }
                var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);
                $el.select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownParent: $parent
                });
            }

            $('.select2_modal').each(function() {
                var $this = $(this);
                if ($this.closest('#workingCreateModal').length) {
                    initSelect2WithParent($this, '#workingCreateModal');
                    return;
                }
                if ($this.closest('#workingEditModal').length) {
                    initSelect2WithParent($this, '#workingEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            // --------- time & pricing helpers ----------
            function minutesSinceMidnight(hhmm) {
                if (!hhmm) return null;
                var parts = hhmm.split(':');
                if (parts.length < 2) return null;
                var h = parseInt(parts[0], 10);
                var m = parseInt(parts[1], 10);
                if (isNaN(h) || isNaN(m)) return null;
                return h * 60 + m;
            }

            function computeDurationMinutes(timeIn, timeOut) {
                var inMin = minutesSinceMidnight(timeIn);
                var outMin = minutesSinceMidnight(timeOut);
                if (inMin === null || outMin === null) return null;
                var diff = outMin - inMin;
                if (diff < 0) diff += 24 * 60;
                return diff;
            }

            function minutesToHoursMinutes(totalMin) {
                if (totalMin === null) return {
                    hours: 0,
                    minutes: 0,
                    totalHours: 0
                };
                var hours = Math.floor(totalMin / 60);
                var minutes = totalMin % 60;
                var totalHours = totalMin / 60;
                return {
                    hours: hours,
                    minutes: minutes,
                    totalHours: totalHours
                };
            }

            function calcTotalPriceUsingDayRate(dayRate, totalHours) {
                if (!dayRate) return (0).toFixed(2);
                var daysFraction = (totalHours || 0) / 8.0;
                var total = daysFraction * parseFloat(dayRate || 0);
                return total.toFixed(2);
            }

            function updateComputedFields(timeInSel, timeOutSel, hoursSel, minutesSel, totalPriceSel,
                equipmentSelect) {
                var timeIn = $(timeInSel).val();
                var timeOut = $(timeOutSel).val();

                var durationMin = computeDurationMinutes(timeIn, timeOut);

                if (durationMin !== null) {
                    var hm = minutesToHoursMinutes(durationMin);
                    $(hoursSel).val(hm.hours);
                    $(minutesSel).val(hm.minutes);
                    var dayRate = parseFloat($(equipmentSelect).find('option:selected').data('day-rate') || 0);
                    var total = calcTotalPriceUsingDayRate(dayRate, hm.totalHours);
                    $(totalPriceSel).val(total);
                } else {
                    var hrs = parseFloat($(hoursSel).val() || 0);
                    var mins = parseFloat($(minutesSel).val() || 0);
                    var dayRate = parseFloat($(equipmentSelect).find('option:selected').data('day-rate') || 0);
                    var total = calcTotalPriceUsingDayRate(dayRate, hrs + mins / 60);
                    $(totalPriceSel).val(total.toFixed(2));
                }
            }

            // create modal listeners
            $(document).on('change keyup', '#create_time_in, #create_time_out, #create_hired_equipment_id',
                function() {
                    updateComputedFields('#create_time_in', '#create_time_out', '#create_working_hours',
                        '#create_minutes', '#create_total_price', '#create_hired_equipment_id');
                });
            $(document).on('change keyup', '#create_working_hours, #create_minutes', function() {
                updateComputedFields('#create_time_in', '#create_time_out', '#create_working_hours',
                    '#create_minutes', '#create_total_price', '#create_hired_equipment_id');
            });

            // edit modal listeners
            $(document).on('change keyup', '#edit_time_in, #edit_time_out, #edit_hired_equipment_id', function() {
                updateComputedFields('#edit_time_in', '#edit_time_out', '#edit_working_hours',
                    '#edit_minutes', '#edit_total_price', '#edit_hired_equipment_id');
            });
            $(document).on('change keyup', '#edit_working_hours, #edit_minutes', function() {
                updateComputedFields('#edit_time_in', '#edit_time_out', '#edit_working_hours',
                    '#edit_minutes', '#edit_total_price', '#edit_hired_equipment_id');
            });

            // reset create modal on show
            $(document).on('shown.bs.modal', '#workingCreateModal', function() {
                var $m = $(this);
                var form = $m.find('form')[0];
                if (form) form.reset();
                $m.find('.select2_modal').each(function() {
                    initSelect2WithParent($(this), '#workingCreateModal');
                    $(this).val(null).trigger('change');
                });
                $('#create_total_price').val('');
            });

            // store edit temporary values when opening
            var tempWorkingEdit = null;

            // open edit modal, populate fields
            document.querySelectorAll('.btn-edit-working').forEach(btn => {
                btn.addEventListener('click', function() {
                    var enc = this.getAttribute('data-id');
                    var hid = this.getAttribute('data-hired_equipment_id') || '';
                    document.getElementById('edit_working_id').value = enc;
                    document.getElementById('edit_working_date').value = this.getAttribute(
                        'data-workingdate') || '';
                    document.getElementById('edit_time_in').value = this.getAttribute(
                        'data-timein') || '';
                    document.getElementById('edit_time_out').value = this.getAttribute(
                        'data-timeout') || '';
                    document.getElementById('edit_working_hours').value = this.getAttribute(
                        'data-workinghours') || 0;
                    document.getElementById('edit_minutes').value = this.getAttribute(
                        'data-minutes') || 0;
                    document.getElementById('edit_payment_status').value = this.getAttribute(
                        'data-paymentstatus') || 'Pending';
                    document.getElementById('edit_status').value = this.getAttribute(
                        'data-status') || 'Active';

                    tempWorkingEdit = {
                        hired_equipment_id: hid
                    };

                    var form = document.getElementById('workingEditForm');
                    // encode id for route
                    var encoded = encodeURIComponent(enc);
                    form.action = "{{ url('/hired-equipment/workings') }}/" + encoded;
                    $('#workingEditModal').modal('show');
                });
            });

            // when edit modal shown, apply select2 picks & compute totals
            $('#workingEditModal').on('shown.bs.modal', function() {
                if (tempWorkingEdit) {
                    $('#edit_hired_equipment_id').val(tempWorkingEdit.hired_equipment_id || '').trigger(
                        'change');
                    tempWorkingEdit = null;
                }
                updateComputedFields('#edit_time_in', '#edit_time_out', '#edit_working_hours',
                    '#edit_minutes', '#edit_total_price', '#edit_hired_equipment_id');
            });

            // delete confirmation (keeps original behaviour)
            document.querySelectorAll('.btn-delete-working').forEach(btn => {
                btn.addEventListener('click', function() {
                    var enc = this.getAttribute('data-id');
                    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will mark the record as Deleted.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes'
                        }).then(function(res) {
                            if (res.isConfirmed) {
                                var encoded = encodeURIComponent(enc);
                                window.location.href =
                                    "{{ url('/hired-equipment/workings/remove') }}/" +
                                    encoded;
                            }
                        });
                    } else {
                        if (confirm('This will mark the record as Deleted. Are you sure?')) {
                            var encoded = encodeURIComponent(enc);
                            window.location.href =
                                "{{ url('/hired-equipment/workings/remove') }}/" + encoded;
                        }
                    }
                });
            });

            // MARK AS PAID - robust delegated handler and encode id
            // Use delegation so it works with DataTables redraws
            document.addEventListener('click', function(e) {
                var target = e.target;
                // find closest element that is a mark-paid button (in case inner <span> clicked)
                var markBtn = target.closest ? target.closest('.btn-mark-paid') : null;
                if (!markBtn) return;

                e.preventDefault();

                var rawId = markBtn.getAttribute('data-id');
                console.log('[MarkPaid] clicked id:', rawId);

                if (!rawId) {
                    alert('Missing identifier for mark as paid action.');
                    return;
                }

                var doMark = function() {
                    try {
                        var encoded = encodeURIComponent(rawId);
                        // build route using your named route pattern (but we will use direct url to be safe)
                        var url = "{{ url('/hired-equipment/workings/mark-paid') }}/" + encoded;
                        // preserve query string
                        var qs = window.location.search || '';
                        console.log('[MarkPaid] navigating to:', url + qs);
                        // use form submit to avoid potential navigation encoding issues
                        var form = document.createElement('form');
                        form.method = 'GET';
                        form.action = url + qs;
                        form.style.display = 'none';
                        document.body.appendChild(form);
                        setTimeout(function() {
                            form.submit();
                        }, 50);
                    } catch (err) {
                        console.error('[MarkPaid] error building URL, fallback to window.location:',
                            err);
                        try {
                            window.location.assign(
                                "{{ url('/hired-equipment/workings/mark-paid') }}/" +
                                encodeURIComponent(rawId) + (window.location.search || ''));
                        } catch (err2) {
                            alert('Unable to perform request. See console for details.');
                            console.error(err2);
                        }
                    }
                };

                // confirm with Swal if present, else confirm()
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire({
                        title: 'Mark as Paid?',
                        text: "This will mark the working record as paid and update totals.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, mark as Paid',
                        cancelButtonText: 'Cancel'
                    }).then(function(res) {
                        if (res && res.isConfirmed) {
                            doMark();
                        } else {
                            console.log('[MarkPaid] cancelled by user.');
                        }
                    }).catch(function(err) {
                        console.error('[MarkPaid] Swal error:', err);
                        if (confirm('Mark this working as paid?')) doMark();
                    });
                } else {
                    if (confirm('Mark this working as paid?')) doMark();
                }
            }, false);

        });
    </script>
@endsection
