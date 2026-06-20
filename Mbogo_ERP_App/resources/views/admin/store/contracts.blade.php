@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>General Supply Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Contracts</strong></li>
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
                                <td id="Hour3" style="color:green;font-size:large;"></td>
                                <td id="Minut3" style="color:green;font-size:large;"></td>
                                <td id="Second3" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg3() {
            setInterval(change_time3, 1000);
        }

        function change_time3() {
            var d = new Date();
            document.getElementById('Hour3').innerHTML = d.getHours() + ':';
            document.getElementById('Minut3').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second3').innerHTML = d.getSeconds();
        }
        timedMsg3();
    </script>

    @php
        $vendorOptions = [];
        foreach ($vendors as $v) {
            $vendorOptions[] = [
                'id' => $v->id,
                'name' => $v->vendor_name,
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Contracts</h3>
        @can('Register-Contracts')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openContractCreateModal()">
                Add Contract
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Contracts Table</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Contract No</th>
                                <th>Title</th>
                                <th>Vendor</th>
                                <th>Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contracts as $k => $row)
                                @php
                                    $payload = [
                                        'id' => encrypt($row->id),
                                        'vendor_id' => $row->vendor_id,
                                        'contract_no' => $row->contract_no,
                                        'contract_title' => $row->contract_title,
                                        'start_date' => $row->start_date,
                                        'end_date' => $row->end_date,
                                        'contract_amount' => $row->contract_amount,
                                        'remarks' => $row->remarks,
                                        'status' => $row->status,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->contract_no ?? '-' }}</td>
                                    <td>{{ $row->contract_title }}</td>
                                    <td>{{ optional($row->vendor)->vendor_name ?? '-' }}</td>
                                    <td>{{ number_format((float) $row->contract_amount, 2) }}</td>
                                    <td>{{ $row->start_date ?? '-' }}</td>
                                    <td>{{ $row->end_date ?? '-' }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td style="white-space: nowrap;">
                                        @can('Edit-Contracts')
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='openContractEdit(@json($payload))'>
                                                Edit
                                            </button>
                                        @endcan

                                        @can('Delete-Contracts')
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick='deleteContract(@json($payload))'>
                                                Remove
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="contractCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="contractCreateForm" action="{{ route('sales.contracts.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add Contract</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-control select2_modal" required>
                                    <option value="">-- Select vendor --</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->vendor_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Contract No</label>
                                <input type="text" name="contract_no" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="contract_title" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="contract_amount"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select2_modal" required>
                                    <option value="">-- Select --</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="contractEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="contractEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Contract</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="contractEditBody">
                        <div class="text-center">Loading...</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            var vendorOptions = @json($vendorOptions);

            function initSelect2WithParent($el, parentSelector) {
                if (!$el || !$el.length) return;

                if ($el.data('select2')) {
                    try {
                        $el.select2('destroy');
                    } catch (e) {}
                }

                var $parent = (parentSelector && $(parentSelector).length) ? $(parentSelector) : $(document.body);

                $el.select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownParent: $parent
                });
            }

            function initAllModalSelects(modalSelector) {
                $(modalSelector).find('.select2_modal').each(function() {
                    initSelect2WithParent($(this), modalSelector);
                });
            }

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            $('.select2_modal').each(function() {
                var $this = $(this);
                if ($this.closest('#contractCreateModal').length) {
                    initSelect2WithParent($this, '#contractCreateModal');
                    return;
                }
                if ($this.closest('#contractEditModal').length) {
                    initSelect2WithParent($this, '#contractEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#contractCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#contractCreateModal');
            });

            $('#contractEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#contractEditModal');
            });

            window.openContractCreateModal = function() {
                var form = document.getElementById('contractCreateForm');
                if (form) form.reset();

                $('#contractCreateModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#contractCreateModal');
                    $('#contractCreateModal').find('select[name="vendor_id"]').val('').trigger(
                    'change');
                    $('#contractCreateModal').find('select[name="status"]').val('').trigger('change');
                }, 150);
            };

            window.openContractEdit = function(row) {
                var html = '';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Vendor <span class="text-danger">*</span></label>';
                html += '<select name="vendor_id" class="form-control select2_modal" required>';
                html += '<option value="">-- Select vendor --</option>';

                for (var i = 0; i < vendorOptions.length; i++) {
                    html += '<option value="' + vendorOptions[i].id + '"' +
                        (String(row.vendor_id) === String(vendorOptions[i].id) ? ' selected' : '') +
                        '>' + escapeHtml(vendorOptions[i].name) + '</option>';
                }

                html += '</select>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Contract No</label>';
                html += '<input type="text" name="contract_no" class="form-control" value="' + escapeHtml(row
                    .contract_no || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Title <span class="text-danger">*</span></label>';
                html += '<input type="text" name="contract_title" class="form-control" value="' + escapeHtml(row
                    .contract_title || '') + '" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Start Date</label>';
                html += '<input type="date" name="start_date" class="form-control" value="' + escapeHtml(row
                    .start_date || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>End Date</label>';
                html += '<input type="date" name="end_date" class="form-control" value="' + escapeHtml(row
                    .end_date || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Amount <span class="text-danger">*</span></label>';
                html +=
                    '<input type="number" step="0.01" min="0" name="contract_amount" class="form-control" value="' +
                    escapeHtml(row.contract_amount || '') + '" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-6">';
                html += '<label>Remarks</label>';
                html += '<textarea name="remarks" class="form-control">' + escapeHtml(row.remarks || '') +
                    '</textarea>';
                html += '</div>';

                html += '<div class="col-md-6">';
                html += '<label>Status <span class="text-danger">*</span></label>';
                html += '<select name="status" class="form-control select2_modal" required>';
                html += '<option value="">-- Select --</option>';
                html += '<option value="Active"' + (row.status === 'Active' ? ' selected' : '') +
                    '>Active</option>';
                html += '<option value="Inactive"' + (row.status === 'Inactive' ? ' selected' : '') +
                    '>Inactive</option>';
                html += '<option value="Closed"' + (row.status === 'Closed' ? ' selected' : '') +
                    '>Closed</option>';
                html += '</select>';
                html += '</div>';
                html += '</div>';

                $('#contractEditBody').html(html);
                $('#contractEditForm').attr('action',
                    "{{ url('/admin/sales/contracts') }}/" + encodeURIComponent(row.id)
                );
                $('#contractEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#contractEditModal');
                }, 200);
            };

            window.deleteContract = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This contract will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href =
                            "{{ url('/admin/sales/contracts/remove') }}/" + encodeURIComponent(row.id);
                    }
                });
            };
        });
    </script>
@endsection
