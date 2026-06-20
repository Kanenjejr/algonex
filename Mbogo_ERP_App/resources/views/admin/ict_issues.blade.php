@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>ICT Issues Management</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('company.dashboard') }}">Dashboard</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>ICT Issues</strong>
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
        $userOptions = [];
        foreach ($users as $user) {
            $userOptions[] = ['id' => $user->id, 'name' => $user->name];
        }
    @endphp

    <div class="col-12">
        <h3 class="mb-2 page-title">ICT Issues Details</h3>

        @can('Register-Software-Hardware-Issues')
            <button style="position:absolute; top:4.5%; right:1.7%;" type="button" class="btn mb-2 btn-primary"
                onclick="openIssueCreateModal()">
                Add Issue
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>ICT Issues Table</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Device Name</th>
                                        <th>Issue Type</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Priority</th>
                                        <th>Date Reported</th>
                                        <th>Assigned To</th>
                                        <th>Status</th>
                                        <th>Resolved Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($issues as $k => $i)
                                        @php
                                            $reportedAt = $i->date_reported
                                                ? \Carbon\Carbon::parse($i->date_reported)
                                                : null;
                                            $resolvedAt = $i->resolved_date
                                                ? \Carbon\Carbon::parse($i->resolved_date)
                                                : null;

                                            $priorityClass =
                                                $i->priority_level == 'critical'
                                                    ? 'danger'
                                                    : ($i->priority_level == 'high'
                                                        ? 'warning'
                                                        : ($i->priority_level == 'medium'
                                                            ? 'primary'
                                                            : 'secondary'));

                                            $statusClass =
                                                $i->issue_status == 'resolved'
                                                    ? 'success'
                                                    : ($i->issue_status == 'pending'
                                                        ? 'warning'
                                                        : 'danger');

                                            $payload = [
                                                'id' => encrypt($i->issue_id),
                                                'device_name' => $i->device_name,
                                                'issue_type' => $i->issue_type,
                                                'category' => $i->category,
                                                'problem_description' => $i->problem_description,
                                                'priority_level' => $i->priority_level,
                                                'date_reported' => $reportedAt ? $reportedAt->format('Y-m-d\TH:i') : '',
                                                'assigned_to' => $i->assigned_to,
                                                'issue_status' => $i->issue_status,
                                                'resolution_details' => $i->resolution_details,
                                                'resolved_date' => $resolvedAt ? $resolvedAt->format('Y-m-d\TH:i') : '',
                                            ];
                                        @endphp

                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $i->device_name }}</td>
                                            <td>{{ ucfirst($i->issue_type) }}</td>
                                            <td>{{ $i->category }}</td>
                                            <td>{{ $i->problem_description }}</td>
                                            <td>
                                                <span class="badge badge-{{ $priorityClass }}">
                                                    {{ strtoupper($i->priority_level) }}
                                                </span>
                                            </td>
                                            <td>{{ $reportedAt ? $reportedAt->format('d-M-Y H:i') : '-' }}</td>
                                            <td>{{ $i->assignedTo->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $statusClass }}">
                                                    {{ strtoupper($i->issue_status) }}
                                                </span>
                                            </td>
                                            <td>{{ $resolvedAt ? $resolvedAt->format('d-M-Y H:i') : '-' }}</td>
                                            <td style="white-space:nowrap;">
                                                @can('Edit-Software-Hardware-Issues')
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick='openIssueEdit(@json($payload))'>
                                                        Edit
                                                    </button>
                                                @endcan

                                                @can('Delete-Software-Hardware-Issues')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick='deleteIssue(@json($payload))'>
                                                        Remove
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No ICT issues found</td>
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
    <div class="modal fade" id="issueCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="issueCreateForm" action="{{ route('ict.issues.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Add ICT Issue</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Device Name *</label>
                                    <input type="text" name="device_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Issue Type *</label>
                                    <select name="issue_type" class="form-control" required>
                                        <option value="">-- Select --</option>
                                        <option value="software">Software</option>
                                        <option value="hardware">Hardware</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Category *</label>
                            <input type="text" name="category" class="form-control"
                                placeholder="OS, Network, Printer, RAM, HDD, Application" required>
                        </div>

                        <div class="form-group">
                            <label>Problem Description *</label>
                            <textarea name="problem_description" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Priority Level *</label>
                                    <select name="priority_level" class="form-control" required>
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <label>Date Reported *</label>
                                    <input type="datetime-local" name="date_reported" class="form-control"
                                        value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Assigned To</label>
                            <select name="assigned_to" class="form-control select2_modal">
                                <option value="">-- None --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="issue_status" class="form-control">
                                <option value="open">Open</option>
                                <option value="pending">Pending</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Resolution Details</label>
                            <textarea name="resolution_details" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Resolved Date</label>
                            <input type="datetime-local" name="resolved_date" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" onclick="handleConfirmSubmit('issueCreateForm')"
                            class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="issueEditModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="issueEditForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit ICT Issue</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="issueEditBody">
                        <div class="text-center">Loading...</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" onclick="handleConfirmSubmit('issueEditForm')"
                            class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            var userOptions = @json($userOptions);

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
                if ($this.closest('#issueCreateModal').length) {
                    initSelect2WithParent($this, '#issueCreateModal');
                    return;
                }
                if ($this.closest('#issueEditModal').length) {
                    initSelect2WithParent($this, '#issueEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $('#issueCreateModal').on('shown.bs.modal', function() {
                initAllModalSelects('#issueCreateModal');
            });

            $('#issueEditModal').on('shown.bs.modal', function() {
                initAllModalSelects('#issueEditModal');
            });

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            window.openIssueCreateModal = function() {
                $('#issueCreateModal').modal('show');
            };

            window.openIssueEdit = function(row) {
                var html = '';

                html += '<div class="row">';
                html += '<div class="col">';
                html += '<div class="form-group">';
                html += '<label>Device Name *</label>';
                html += '<input type="text" name="device_name" class="form-control" value="' + escapeHtml(row
                    .device_name || '') + '" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="col">';
                html += '<div class="form-group">';
                html += '<label>Issue Type *</label>';
                html += '<select name="issue_type" class="form-control" required>';
                html += '<option value="software"' + (row.issue_type === 'software' ? ' selected' : '') +
                    '>Software</option>';
                html += '<option value="hardware"' + (row.issue_type === 'hardware' ? ' selected' : '') +
                    '>Hardware</option>';
                html += '</select>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Category *</label>';
                html += '<input type="text" name="category" class="form-control" value="' + escapeHtml(row
                    .category || '') + '" required>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Problem Description *</label>';
                html += '<textarea name="problem_description" class="form-control" rows="4" required>' +
                    escapeHtml(row.problem_description || '') + '</textarea>';
                html += '</div>';

                html += '<div class="row">';
                html += '<div class="col">';
                html += '<div class="form-group">';
                html += '<label>Priority Level *</label>';
                html += '<select name="priority_level" class="form-control" required>';
                html += '<option value="low"' + (row.priority_level === 'low' ? ' selected' : '') +
                    '>Low</option>';
                html += '<option value="medium"' + (row.priority_level === 'medium' ? ' selected' : '') +
                    '>Medium</option>';
                html += '<option value="high"' + (row.priority_level === 'high' ? ' selected' : '') +
                    '>High</option>';
                html += '<option value="critical"' + (row.priority_level === 'critical' ? ' selected' : '') +
                    '>Critical</option>';
                html += '</select>';
                html += '</div>';
                html += '</div>';

                html += '<div class="col">';
                html += '<div class="form-group">';
                html += '<label>Date Reported *</label>';
                html += '<input type="datetime-local" name="date_reported" class="form-control" value="' +
                    escapeHtml(row.date_reported || '') + '" required>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Assigned To</label>';
                html += '<select name="assigned_to" class="form-control select2_modal">';
                html += '<option value="">-- None --</option>';

                for (var i = 0; i < userOptions.length; i++) {
                    html += '<option value="' + userOptions[i].id + '"' + (String(row.assigned_to) === String(
                            userOptions[i].id) ? ' selected' : '') + '>' + escapeHtml(userOptions[i].name) +
                        '</option>';
                }

                html += '</select>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Status</label>';
                html += '<select name="issue_status" class="form-control">';
                html += '<option value="open"' + (row.issue_status === 'open' ? ' selected' : '') +
                    '>Open</option>';
                html += '<option value="pending"' + (row.issue_status === 'pending' ? ' selected' : '') +
                    '>Pending</option>';
                html += '<option value="resolved"' + (row.issue_status === 'resolved' ? ' selected' : '') +
                    '>Resolved</option>';
                html += '</select>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Resolution Details</label>';
                html += '<textarea name="resolution_details" class="form-control" rows="3">' + escapeHtml(row
                    .resolution_details || '') + '</textarea>';
                html += '</div>';

                html += '<div class="form-group">';
                html += '<label>Resolved Date</label>';
                html += '<input type="datetime-local" name="resolved_date" class="form-control" value="' +
                    escapeHtml(row.resolved_date || '') + '">';
                html += '</div>';

                $('#issueEditBody').html(html);
                $('#issueEditForm').attr('action', "{{ route('ict.issues.update', ':id') }}".replace(':id', row
                    .id));
                $('#issueEditModal').modal('show');

                setTimeout(function() {
                    initAllModalSelects('#issueEditModal');
                }, 200);
            };

            window.deleteIssue = function(row) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This ICT issue will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) {
                        var actionUrl = "{{ route('ict.issues.destroy', ':id') }}".replace(':id', row
                            .id);
                        var form = `
                            <form method="POST" action="${actionUrl}" id="deleteIssueForm">
                                @csrf
                                @method('DELETE')
                            </form>
                        `;
                        $('body').append(form);
                        $('#deleteIssueForm').submit();
                    }
                });
            };
        });
    </script>
@endsection
