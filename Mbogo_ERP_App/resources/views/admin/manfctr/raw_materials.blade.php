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
                    <strong>Raw Materials Details</strong>
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
            setInterval(change_time, 1000);
        }

        function change_time() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }
        timedMsg();
    </script>

    @php
        $workPointOptions = [];
        foreach ($workPoints as $wp) {
            $workPointOptions[] = [
                'id' => $wp->id,
                'name' => $wp->work_name,
            ];
        }

        $canSelectWorkPoint =
            auth()->user()->can('View-Raw-Material-All') ||
            auth()->user()->can('View-Raw-Material-Company') ||
            auth()->user()->can('View-Raw-Material-Unit') ||
            in_array(auth()->user()->role, [
                'Admin',
                'CEO',
                'Managing Director (MD)',
                'Admin-Developer',
                'Company Manager',
                'Unit Manager',
            ]);
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">Raw Materials Details</h3>
        @can('Register-Raw-Material')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openRawCreateModal()">
                Add Raw Material
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>Raw Materials Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Material</th>
                                        <th>Code</th>
                                        <th>Unit</th>
                                        <th>Company</th>
                                        <th>Work Point</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rawMaterials as $k => $r)
                                        @php
                                            $payload = [
                                                'id' => encrypt($r->id),
                                                'material_name' => $r->material_name,
                                                'material_code' => $r->material_code,
                                                'unit_name' => $r->unit_name,
                                                'work_point_id' => $r->work_point_id,
                                                'status' => $r->status,
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $r->material_name }}</td>
                                            <td>{{ $r->material_code ?? '-' }}</td>
                                            <td>{{ $r->unit_name ?? '-' }}</td>
                                            <td>{{ optional($r->company)->company_name ?? '-' }}</td>
                                            <td>{{ optional($r->workpoint)->work_name ?? '-' }}</td>
                                            <td>{{ $r->status }}</td>
                                            <td style="white-space: nowrap;">
                                                @can('Edit-Raw-Material')
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openRawEditModal(@json($payload))'>
                                                        Edit
                                                    </button>
                                                @endcan

                                                @can('Delete-Raw-Material')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteRaw(@json($payload))'>
                                                        Remove
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No data found</td>
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
    <div class="modal fade" id="rawCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="rawCreateForm" action="{{ route('manfctr.rawmaterials.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Raw Material</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        @if ($canSelectWorkPoint)
                            <div class="form-group">
                                <label>Work Point <span class="text-danger">*</span></label>
                                <select name="work_point_id" class="form-control select2_modal" required>
                                    <option value="">-- Select work point --</option>
                                    @foreach ($workPoints as $wp)
                                        <option value="{{ $wp->id }}">{{ $wp->work_code }} - {{ $wp->work_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
                        @endif

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Material Name <span class="text-danger">*</span></label>
                                <input type="text" name="material_name" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label>Material Code</label>
                                <input type="text" name="material_code" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Unit</label>
                                <input type="text" name="unit_name" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select2_modal" required>
                                    <option value="">-- Select status --</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
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
    <div class="modal fade" id="rawEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="rawEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Raw Material</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="rawEditBody">
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
            var workPointOptions = @json($workPointOptions);
            var canSelectWorkPoint = @json($canSelectWorkPoint);

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
                if ($this.closest('#rawCreateModal').length) {
                    initSelect2WithParent($this, '#rawCreateModal');
                    return;
                }
                if ($this.closest('#rawEditModal').length) {
                    initSelect2WithParent($this, '#rawEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#rawCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#rawCreateModal');
            });

            $('#rawEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#rawEditModal');
            });

            window.openRawCreateModal = function() {
                $('#rawCreateForm')[0].reset();
                $('#rawCreateModal').modal('show');
            };

            window.openRawEditModal = function(row) {
                var html = '';

                if (canSelectWorkPoint) {
                    html += '<div class="form-group">';
                    html += '<label>Work Point <span class="text-danger">*</span></label>';
                    html += '<select name="work_point_id" class="form-control select2_modal" required>';
                    html += '<option value="">-- Select work point --</option>';

                    for (var i = 0; i < workPointOptions.length; i++) {
                        html += '<option value="' + workPointOptions[i].id + '"' +
                            (String(row.work_point_id) === String(workPointOptions[i].id) ? ' selected' : '') +
                            '>' + escapeHtml(workPointOptions[i].name) + '</option>';
                    }

                    html += '</select>';
                    html += '</div>';
                } else {
                    html += '<input type="hidden" name="work_point_id" value="' + escapeHtml(row
                        .work_point_id || '') + '">';
                }

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Material Name <span class="text-danger">*</span></label>';
                html += '<input type="text" name="material_name" class="form-control" value="' + escapeHtml(row
                    .material_name || '') + '" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Material Code</label>';
                html += '<input type="text" name="material_code" class="form-control" value="' + escapeHtml(row
                    .material_code || '') + '">';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Unit</label>';
                html += '<input type="text" name="unit_name" class="form-control" value="' + escapeHtml(row
                    .unit_name || '') + '">';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Status <span class="text-danger">*</span></label>';
                html += '<select name="status" class="form-control select2_modal" required>';
                html += '<option value="">-- Select status --</option>';
                html += '<option value="Active"' + (row.status === 'Active' ? ' selected' : '') +
                    '>Active</option>';
                html += '<option value="Inactive"' + (row.status === 'Inactive' ? ' selected' : '') +
                    '>Inactive</option>';
                html += '<option value="Deleted"' + (row.status === 'Deleted' ? ' selected' : '') +
                    '>Deleted</option>';
                html += '</select>';
                html += '</div>';
                html += '</div>';

                $('#rawEditBody').html(html);
                $('#rawEditForm').attr('action',
                    "{{ route('manfctr.rawmaterials.update', ':id') }}".replace(':id', row.id)
                );
                $('#rawEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#rawEditModal');
                }, 200);
            };

            window.deleteRaw = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This raw material will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        window.location.href =
                            "{{ route('manfctr.rawmaterials.remove', ':id') }}".replace(':id', row.id);
                    }
                });
            };
        });
    </script>
@endsection
