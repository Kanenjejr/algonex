@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>IT Maintenance Management</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('company.dashboard') }}">Dashboard</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>IT Maintenance</strong>
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

    <script>
        function timedMsg() {
            setInterval(change_time, 1000);
        }

        function change_time() {
            var d = new Date();
            document.getElementById('Hour').innerHTML = d.getHours() + ':';
            document.getElementById('Minut').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second').innerHTML = d.getSeconds();
        }
        timedMsg();
    </script>

    @php
        $issueOptions = [];
        foreach ($issues as $issue) {
            $issueOptions[] = [
                'id' => $issue->issue_id,
                'name' => $issue->issue_id . ' - ' . $issue->device_name . ' - ' . ucfirst($issue->issue_type),
            ];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">IT Maintenance Details</h3>

        @can('Register-IT-Maintenance')
            <button style="position:absolute; top:4.5%; right:1.7%;" class="btn mb-2 btn-primary" type="button"
                onclick="openMaintenanceCreateModal()">
                Add Maintenance
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>IT Maintenance Table</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Issue Ref</th>
                                        <th>Asset ID</th>
                                        <th>Maintenance Type</th>
                                        <th>Description</th>
                                        <th>Technician</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Cost</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($maintenances as $k => $m)
                                        @php
                                            $maintDate = $m->maintenance_date
                                                ? \Carbon\Carbon::parse($m->maintenance_date)
                                                : null;

                                            $statusClass =
                                                $m->status == 'completed'
                                                    ? 'success'
                                                    : ($m->status == 'in_progress'
                                                        ? 'warning'
                                                        : 'secondary');

                                            $typeClass = $m->maintenance_type == 'preventive' ? 'primary' : 'info';

                                            $payload = [
                                                'id' => encrypt($m->maintenance_id),
                                                'issue_id' => $m->issue_id,
                                                'asset_id' => $m->asset_id,
                                                'maintenance_type' => $m->maintenance_type,
                                                'description' => $m->description,
                                                'technician_name' => $m->technician_name,
                                                'maintenance_date' => $maintDate
                                                    ? $maintDate->format('Y-m-d\TH:i')
                                                    : '',
                                                'status' => $m->status,
                                                'cost' => $m->cost,
                                                'remarks' => $m->remarks,
                                            ];
                                        @endphp

                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $m->issue->issue_id ?? '-' }}</td>
                                            <td>{{ $m->asset_id ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $typeClass }}">
                                                    {{ strtoupper($m->maintenance_type) }}
                                                </span>
                                            </td>
                                            <td>{{ $m->description }}</td>
                                            <td>{{ $m->technician_name }}</td>
                                            <td>{{ $maintDate ? $maintDate->format('d-M-Y H:i') : '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $statusClass }}">
                                                    {{ strtoupper($m->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $m->cost !== null ? number_format($m->cost, 2) : '-' }}</td>
                                            <td>{{ $m->remarks ?? '-' }}</td>
                                            <td style="white-space:nowrap;">
                                                @can('Edit-IT-Maintenance')
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openMaintenanceEdit(@json($payload))'>
                                                        Edit
                                                    </button>
                                                @endcan

                                                @can('Delete-IT-Maintenance')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteMaintenance(@json($payload))'>
                                                        Remove
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No maintenance records found</td>
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
    <div class="modal fade" id="maintenanceCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="maintenanceCreateForm" action="{{ route('ict.maintenance.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add IT Maintenance</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Issue Reference</label>
                            <select name="issue_id" class="form-control select2_modal">
                                <option value="">-- Select Issue --</option>
                                @foreach ($issues as $issue)
                                    <option value="{{ $issue->issue_id }}">
                                        {{ $issue->issue_id }} - {{ $issue->device_name }} -
                                        {{ ucfirst($issue->issue_type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Asset ID</label>
                            <input type="number" name="asset_id" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Maintenance Type *</label>
                            <select name="maintenance_type" class="form-control" required>
                                <option value="preventive">Preventive</option>
                                <option value="corrective" selected>Corrective</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Technician Name *</label>
                            <input type="text" name="technician_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Maintenance Date *</label>
                            <input type="datetime-local" name="maintenance_date" class="form-control"
                                value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Status *</label>
                                    <select name="status" class="form-control" required>
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Cost</label>
                                    <input type="number" step="0.01" name="cost" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" onclick="handleConfirmSubmit('maintenanceCreateForm')"
                            class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="maintenanceEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="maintenanceEditForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit IT Maintenance</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="maintenanceEditBody">
                        <div class="text-center">Loading...</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" onclick="handleConfirmSubmit('maintenanceEditForm')"
                            class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            var issueOptions = @json($issueOptions);

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
                if ($this.closest('#maintenanceCreateModal').length) {
                    initSelect2WithParent($this, '#maintenanceCreateModal');
                    return;
                }
                if ($this.closest('#maintenanceEditModal').length) {
                    initSelect2WithParent($this, '#maintenanceEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#maintenanceCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#maintenanceCreateModal');
            });

            $('#maintenanceEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#maintenanceEditModal');
            });

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            window.openMaintenanceCreateModal = function() {
                $('#maintenanceCreateModal').modal('show');
            };

            window.openMaintenanceEdit = function(row) {
                var html = '';

                html += '<div class="form-group">';
                html += '<label>Issue Reference</label>';
                html += '<select name="issue_id" class="form-control select2_modal">';
                html += '<option value="">-- Select Issue --</option>';

                for (var i = 0; i < issueOptions.length; i++) {
                    html += '<option value="' + issueOptions[i].id + '"' + (String(row.issue_id) === String(
                            issueOptions[i].id) ? ' selected' : '') + '>' + escapeHtml(issueOptions[i].name) +
                        '</option>';
                }

                html += '</select>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Asset ID</label>';
                html += '<input type="number" name="asset_id" class="form-control" value="' + escapeHtml(row
                    .asset_id || '') + '">';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Maintenance Type *</label>';
                html += '<select name="maintenance_type" class="form-control" required>';
                html += '<option value="preventive"' + (row.maintenance_type === 'preventive' ? ' selected' :
                    '') + '>Preventive</option>';
                html += '<option value="corrective"' + (row.maintenance_type === 'corrective' ? ' selected' :
                    '') + '>Corrective</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Description *</label>';
                html += '<textarea name="description" class="form-control" rows="4" required>' + escapeHtml(row
                    .description || '') + '</textarea>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Technician Name *</label>';
                html += '<input type="text" name="technician_name" class="form-control" value="' + escapeHtml(
                    row.technician_name || '') + '" required>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Maintenance Date *</label>';
                html += '<input type="datetime-local" name="maintenance_date" class="form-control" value="' +
                    escapeHtml(row.maintenance_date || '') + '" required>';
                html += '</div>';

                html += '<div class="row">';
                html += '<div class="col">';
                html += '<div class="form-group">';
                html += '<label>Status *</label>';
                html += '<select name="status" class="form-control" required>';
                html += '<option value="pending"' + (row.status === 'pending' ? ' selected' : '') +
                    '>Pending</option>';
                html += '<option value="in_progress"' + (row.status === 'in_progress' ? ' selected' : '') +
                    '>In Progress</option>';
                html += '<option value="completed"' + (row.status === 'completed' ? ' selected' : '') +
                    '>Completed</option>';
                html += '</select>';
                html += '</div>';
                html += '</div>';

                html += '<div class="col">';
                html += '<div class="form-group">';
                html += '<label>Cost</label>';
                html += '<input type="number" step="0.01" name="cost" class="form-control" value="' +
                    escapeHtml(row.cost || '') + '">';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Remarks</label>';
                html += '<textarea name="remarks" class="form-control" rows="3">' + escapeHtml(row.remarks ||
                    '') + '</textarea>';
                html += '</div>';

                $('#maintenanceEditBody').html(html);
                $('#maintenanceEditForm').attr('action', "{{ route('ict.maintenance.update', ':id') }}"
                    .replace(':id', row.id));
                $('#maintenanceEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#maintenanceEditModal');
                }, 200);
            };

            window.deleteMaintenance = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This maintenance record will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        var actionUrl = "{{ route('ict.maintenance.destroy', ':id') }}".replace(':id',
                            row.id);
                        var form = `
                            <form method="POST" action="${actionUrl}" id="deleteMaintenanceForm">
                                @csrf
                                @method('DELETE')
                            </form>
                        `;
                        $('body').append(form);
                        $('#deleteMaintenanceForm').submit();
                    }
                });
            };
        });
    </script>
@endsection
