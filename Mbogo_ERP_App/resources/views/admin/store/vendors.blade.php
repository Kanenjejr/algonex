@extends('layouts.salesMaster')

@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>General Supply Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Vendors</strong></li>
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
                                <td id="Hour1" style="color:green;font-size:large;"></td>
                                <td id="Minut1" style="color:green;font-size:large;"></td>
                                <td id="Second1" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg1() {
            setInterval(change_time1, 1000);
        }

        function change_time1() {
            var d = new Date();
            document.getElementById('Hour1').innerHTML = d.getHours() + ':';
            document.getElementById('Minut1').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second1').innerHTML = d.getSeconds();
        }

        timedMsg1();
    </script>

    <div class="col-12">
        <h3 class="mb-2 page-title">Vendors</h3>

        @can('Register-Vendors')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openVendorCreateModal()">
                Add Vendor
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Vendors Table</h5>
            </div>

            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>TIN</th>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($vendors as $k => $row)
                                @php
                                    $payload = [
                                        'id' => encrypt($row->id),
                                        'vendor_name' => $row->vendor_name,
                                        'vendor_code' => $row->vendor_code,
                                        'phone_no' => $row->phone_no,
                                        'email' => $row->email,
                                        'address' => $row->address,
                                        'tin_no' => $row->tin_no,
                                        'account_code' => $row->account_code,
                                        'account_name' => $row->account_name,
                                        'status' => $row->status,
                                    ];
                                @endphp

                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->vendor_name }}</td>
                                    <td>{{ $row->vendor_code ?? '-' }}</td>
                                    <td>{{ $row->phone_no ?? '-' }}</td>
                                    <td>{{ $row->email ?? '-' }}</td>
                                    <td>{{ $row->address ?? '-' }}</td>
                                    <td>{{ $row->tin_no ?? '-' }}</td>
                                    <td>{{ $row->account_code ?? '-' }}</td>
                                    <td>{{ $row->account_name ?? '-' }}</td>
                                    <td>{{ $row->status }}</td>

                                    <td style="white-space: nowrap;">
                                        @can('Edit-Vendors')
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='openVendorEdit(@json($payload))'>
                                                Edit
                                            </button>
                                        @endcan

                                        @can('Delete-Vendors')
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick='deleteVendor(@json($payload))'>
                                                Remove
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="vendorCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="vendorCreateForm" action="{{ route('sales.vendors.store') }}" method="POST">
                @csrf

                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add Vendor</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="vendor_name" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label>Code</label>
                                <input type="text" name="vendor_code" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Phone</label>
                                <input type="text" name="phone_no" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>TIN</label>
                                <input type="text" name="tin_no" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Account Code</label>
                                <input type="text" name="account_code" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Account Name</label>
                                <input type="text" name="account_name" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select2_modal" required>
                                    <option value="">-- Select --</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
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
    <div class="modal fade" id="vendorEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="vendorEditForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Vendor</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="vendorEditBody">
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

                if ($this.closest('#vendorCreateModal').length) {
                    initSelect2WithParent($this, '#vendorCreateModal');
                    return;
                }

                if ($this.closest('#vendorEditModal').length) {
                    initSelect2WithParent($this, '#vendorEditModal');
                    return;
                }

                initSelect2WithParent($this, null);
            });

            $('#vendorCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#vendorCreateModal');
            });

            $('#vendorEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#vendorEditModal');
            });

            window.openVendorCreateModal = function() {
                var form = document.getElementById('vendorCreateForm');

                if (form) {
                    form.reset();
                }

                $('#vendorCreateModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#vendorCreateModal');
                    $('#vendorCreateModal').find('select[name="status"]').val('').trigger('change');
                }, 150);
            };

            window.openVendorEdit = function(row) {
                var html = '';

                html += '<div class="row mt-2">';

                html += '<div class="col-md-4">';
                html += '<label>Name <span class="text-danger">*</span></label>';
                html += '<input type="text" name="vendor_name" class="form-control" value="' + escapeHtml(row
                    .vendor_name || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Code</label>';
                html += '<input type="text" name="vendor_code" class="form-control" value="' + escapeHtml(row
                    .vendor_code || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Phone</label>';
                html += '<input type="text" name="phone_no" class="form-control" value="' + escapeHtml(row
                    .phone_no || '') + '">';
                html += '</div>';

                html += '</div>';

                html += '<div class="row mt-2">';

                html += '<div class="col-md-4">';
                html += '<label>Email</label>';
                html += '<input type="email" name="email" class="form-control" value="' + escapeHtml(row
                    .email || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Address</label>';
                html += '<input type="text" name="address" class="form-control" value="' + escapeHtml(row
                    .address || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>TIN</label>';
                html += '<input type="text" name="tin_no" class="form-control" value="' + escapeHtml(row
                    .tin_no || '') + '">';
                html += '</div>';

                html += '</div>';

                html += '<div class="row mt-2">';

                html += '<div class="col-md-4">';
                html += '<label>Account Code</label>';
                html += '<input type="text" name="account_code" class="form-control" value="' + escapeHtml(row
                    .account_code || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Account Name</label>';
                html += '<input type="text" name="account_name" class="form-control" value="' + escapeHtml(row
                    .account_name || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Status <span class="text-danger">*</span></label>';
                html += '<select name="status" class="form-control select2_modal" required>';
                html += '<option value="">-- Select --</option>';
                html += '<option value="Active"' + (row.status === 'Active' ? ' selected' : '') +
                    '>Active</option>';
                html += '<option value="Inactive"' + (row.status === 'Inactive' ? ' selected' : '') +
                    '>Inactive</option>';
                html += '</select>';
                html += '</div>';

                html += '</div>';

                $('#vendorEditBody').html(html);

                $('#vendorEditForm').attr(
                    'action',
                    "{{ url('/admin/sales/vendors') }}/" + encodeURIComponent(row.id)
                );

                $('#vendorEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#vendorEditModal');
                }, 200);
            };

            window.deleteVendor = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This vendor will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href =
                            "{{ url('/admin/sales/vendors/remove') }}/" + encodeURIComponent(row.id);
                    }
                });
            };
        });
    </script>
@endsection
