@extends('layouts.salesMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>General Supply Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('company.dashboard') }}">Dashboard</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>Items Details</strong>
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
            setInterval("change_time();", 1000);
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

    <div class="col-12">
        <h3 class="mb-2 page-title">Items Details</h3>

        @can('Register-Item-Details')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openItemCreateModal()">
                Add Item
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox"
                    style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">
                    <div class="ibox-title"
                        style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <h5 style="margin:0; font-weight:800; color:#fff;">Items Table</h5>
                        </div>
                    </div>

                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Item Code</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $k => $item)
                                        @php
                                            $payload = [
                                                'id' => encrypt($item->id),
                                                'item_name' => $item->item_name,
                                                'item_code' => $item->item_code,
                                                'status' => $item->status,
                                            ];
                                        @endphp

                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $item->item_name }}</td>
                                            <td>{{ $item->item_code ?? '-' }}</td>
                                            <td>{{ $item->status }}</td>
                                            <td style="white-space:nowrap;">
                                                @can('Edit-Item-Details')
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openItemEdit(@json($payload))'>
                                                        Edit
                                                    </button>
                                                @endcan

                                                @can('Delete-Item-Details')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteItem(@json($payload))'>
                                                        Remove
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No items found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="itemCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="itemCreateForm" action="{{ route('sales.gs.items.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Item Code</label>
                            <input type="text" name="item_code" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control select2_modal" required>
                                <option value="">-- Select status --</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="itemEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="itemEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="itemEditBody">
                        <div class="text-center">Loading...</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Update</button>
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

            $('.select2_modal').each(function() {
                var $this = $(this);
                if ($this.closest('#itemCreateModal').length) {
                    initSelect2WithParent($this, '#itemCreateModal');
                    return;
                }
                if ($this.closest('#itemEditModal').length) {
                    initSelect2WithParent($this, '#itemEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#itemCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#itemCreateModal');
            });

            $('#itemEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#itemEditModal');
            });

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            window.openItemCreateModal = function() {
                $('#itemCreateModal').modal('show');
            };

            window.openItemEdit = function(row) {
                var html = '';
                html += '<div class="form-group">';
                html += '<label>Item Name <span class="text-danger">*</span></label>';
                html += '<input type="text" name="item_name" class="form-control" value="' + escapeHtml(row
                    .item_name || '') + '" required>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Item Code</label>';
                html += '<input type="text" name="item_code" class="form-control" value="' + escapeHtml(row
                    .item_code || '') + '">';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Status <span class="text-danger">*</span></label>';
                html += '<select name="status" class="form-control select2_modal" required>';
                html += '<option value="">-- Select status --</option>';
                html += '<option value="Active"' + (row.status === 'Active' ? ' selected' : '') +
                    '>Active</option>';
                html += '<option value="Inactive"' + (row.status === 'Inactive' ? ' selected' : '') +
                    '>Inactive</option>';
                html += '</select>';
                html += '</div>';

                $('#itemEditBody').html(html);
                $('#itemEditForm').attr('action', "{{ url('/admin/store/general-supply/items') }}/" +
                    encodeURIComponent(row.id));
                $('#itemEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#itemEditModal');
                }, 200);
            };

            window.deleteItem = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This item will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href =
                            "{{ url('/admin/store/general-supply/items/remove') }}/" +
                            encodeURIComponent(row.id);
                    }
                });
            };
        });
    </script>
@endsection
