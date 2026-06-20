@extends('layouts.AdminMaster')
@section('content')
    <style>
        /* Keep Select2 dropdown clickable inside payroll modal */
        #payrollCreateModal .select2-container {
            width: 100% !important;
        }

        .select2-container--open,
        .select2-dropdown {
            z-index: 999999 !important;
        }

        .modal-open .select2-container--open {
            z-index: 999999 !important;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Payroll Information</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li><a href="{{ route('hr') }}">Human Resource</a></li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Staff/User Payroll</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong>
                        <?php use Carbon\Carbon;
                        $carbon = Carbon::now();
                        $carbon1 = Carbon::now()->toDateString();
                        echo $carbon->format('l');
                        echo ' , ';
                        echo $carbon1; ?>
                    </strong></li>
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
                            <tr>
                        </table>
                    </strong></li>
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
            if (curr_hour > 24) {
                curr_hour = curr_hour - 24;
            }
            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }
        timedMsg();
    </script>

    <div class="col-12">
        <h3 class="mb-2 page-title">Payrolls</h3>
        @can('Register-Payroll')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#payrollCreateModal">Prepare Payroll</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-info">
                        <h5>Payroll Batches</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Period</th>
                                        <th>Scope</th>
                                        <th>Status</th>
                                        <th>Gross</th>
                                        <th>Allowance</th>
                                        <th>Bonus</th>
                                        <th>Absence</th>
                                        <th>HESLB</th>
                                        <th>Loans</th>
                                        <th>PAYE</th>
                                        <th>NSSF Employee</th>
                                        <th>Net</th>
                                        <th>Employer Cost</th>
                                        <th>Total Payroll Cost</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payrolls as $k => $p)
                                        @php
                                            $nssfEmployeeTotal = $p->lines ? $p->lines->sum('nssf_employee') : 0;
                                            $employerCostTotal =
                                                ($p->employer_cost_total ?? 0) > 0
                                                    ? $p->employer_cost_total
                                                    : ($p->lines
                                                        ? $p->lines->sum('employer_cost')
                                                        : 0);
                                            $payrollCostTotal =
                                                ($p->payroll_cost_total ?? 0) > 0
                                                    ? $p->payroll_cost_total
                                                    : ($p->gross_total ?? 0) + $employerCostTotal;
                                        @endphp
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $p->period }}</td>
                                            <td>
                                                <strong>{{ $p->scope_type ?? 'All' }}</strong><br>
                                                <small>
                                                    @if (($p->scope_type ?? 'All') === 'Only-NCL')
                                                        Nile Complex Plaza Limited only
                                                    @elseif(($p->scope_type ?? 'All') === 'Exclude-NCL')
                                                        Excluding Nile Complex Plaza Limited
                                                    @else
                                                        All active staff including NCL
                                                    @endif
                                                </small>
                                            </td>
                                            <td>{{ $p->status }}</td>
                                            <td class="text-right">{{ number_format($p->gross_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($p->allowance_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($p->bonus_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($p->absence_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($p->heslb_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($p->loan_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($p->paye_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($nssfEmployeeTotal, 2) }}</td>
                                            <td class="text-right">{{ number_format($p->net_total ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($employerCostTotal, 2) }}</td>
                                            <td class="text-right font-weight-bold">
                                                {{ number_format($payrollCostTotal, 2) }}</td>
                                            <td>
                                                @can('Approve-Payroll')
                                                    @if ($p->status === 'Prepared')
                                                        <form action="{{ route('hr.payrolls.approve', encrypt($p->id)) }}"
                                                            method="POST" style="display:inline">@csrf<button
                                                                class="btn btn-sm btn-success">Approve</button></form>
                                                    @endif
                                                @endcan
                                                @can('Pay-Payroll')
                                                    @if ($p->status === 'Approved')
                                                        <form action="{{ route('hr.payrolls.pay', encrypt($p->id)) }}"
                                                            method="POST" style="display:inline">@csrf<button
                                                                class="btn btn-sm btn-primary">Pay</button></form>
                                                    @endif
                                                @endcan
                                                @can('View-Payrolls')
                                                    <a class="btn btn-sm btn-info"
                                                        href="{{ route('hr.payrolls.show', encrypt($p->id)) }}">View</a>
                                                @endcan
                                                @can('Delete-Payroll')
                                                    @if ($p->status === 'Prepared')
                                                        <button class="btn btn-sm btn-warning btn-rollback-payroll"
                                                            data-id="{{ encrypt($p->id) }}">Rollback</button>
                                                    @endif
                                                    @if (in_array($p->status, ['Prepared', 'Cancelled', 'Rolled Back']))
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-sm btn-danger btn-delete-payroll"
                                                            data-id="{{ encrypt($p->id) }}">Cancel</a>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="payrollCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="payrollCreateForm" action="{{ route('hr.payrolls.prepare') }}" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Prepare Payroll Batch</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Payroll Formula:</strong><br>
                            Gross = Basic + Allowance + Bonus + Approved Overtime - Absence Deduction.<br>
                            Net = Gross - PAYE - NSSF 10% - HESLB - Loans/Advances.<br><br>
                            <strong>NCL:</strong> Nile Complex Plaza Limited company code is <strong>NCL001</strong>. Select
                            whether to include it, exclude it, or generate it alone.
                        </div>

                        <div class="form-group">
                            <label>Period <span style="color:red">*</span></label>
                            <input type="month" name="period" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Payroll Scope <span style="color:red">*</span></label>
                            <select name="scope_type" class="form-control select2_demo_3" required>
                                <option value="All">All Staff - Include NCL</option>
                                <option value="Exclude-NCL">All Staff - Exclude NCL</option>
                                <option value="Only-NCL">NCL Only</option>
                            </select>
                            <small class="form-text text-muted">
                                Use this when Nile Complex Plaza Limited preparation/payment varies from other companies.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn mb-2 btn-secondary"
                            data-dismiss="modal">Close</button><button type="submit"
                            onclick="handleConfirmSubmit('payrollCreateForm')" class="btn mb-2 btn-primary">Prepare
                            Payroll</button></div>
                </div>
            </form>
        </div>
    </div>

    <form id="rollbackPayrollForm" method="POST" style="display:none">@csrf
        <input type="hidden" name="rollback_reason" id="rollback_reason">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function initPayrollModalSelect2() {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    var $modal = window.jQuery('#payrollCreateModal');
                    $modal.find('select.select2_demo_3').each(function() {
                        var $select = window.jQuery(this);
                        if ($select.hasClass('select2-hidden-accessible')) {
                            try {
                                $select.select2('destroy');
                            } catch (e) {}
                        }
                        $select.select2({
                            width: '100%',
                            dropdownParent: $modal
                        });
                    });
                }
            }

            if (window.jQuery) {
                window.jQuery('#payrollCreateModal').on('shown.bs.modal', function() {
                    initPayrollModalSelect2();
                });
            }

            document.querySelectorAll('.btn-delete-payroll').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                            title: 'Are you sure?',
                            text: "This will cancel the payroll batch.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes'
                        })
                        .then(function(res) {
                            if (res.isConfirmed) {
                                window.location.href =
                                    "{{ route('hr.payrolls.remove', ':id') }}".replace(':id',
                                        enc);
                            }
                        });
                });
            });

            document.querySelectorAll('.btn-rollback-payroll').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                            title: 'Rollback payroll?',
                            input: 'textarea',
                            inputLabel: 'Reason',
                            inputPlaceholder: 'Write rollback reason...',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Rollback'
                        })
                        .then(function(res) {
                            if (res.isConfirmed && res.value) {
                                var form = document.getElementById('rollbackPayrollForm');
                                document.getElementById('rollback_reason').value = res.value;
                                form.action = "{{ route('hr.payrolls.rollback', ':id') }}"
                                    .replace(':id', enc);
                                form.submit();
                            }
                        });
                });
            });
        });
    </script>
@endsection
