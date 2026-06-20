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
                    <strong>Requested Items</strong>
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

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox"
            style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">
            <div class="ibox-title"
                style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                    <h5 style="margin:0; font-weight:800; color:#fff;">Requested Items Pending for Issue</h5>
                </div>
            </div>

            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request No</th>
                                <th>Date</th>
                                <th>Work Point</th>
                                <th>Department</th>
                                <th>Section</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Requested</th>
                                <th>Issued</th>
                                <th>Remaining</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $k => $row)
                                @php
                                    $remaining = (float) $row->requested_qty - (float) $row->issued_qty;
                                    $payload = [
                                        'id' => $row->id,
                                        'request_no' => $row->request_no,
                                        'item' => optional($row->item)->item_name,
                                        'desc' =>
                                            optional($row->description)->description_name .
                                            ' (' .
                                            optional($row->description)->unit_name .
                                            ')',
                                        'remaining' => $remaining,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $row->request_no }}</td>
                                    <td>{{ $row->request_date }}</td>
                                    <td>{{ optional($row->workpoint)->work_code }} -
                                        {{ optional($row->workpoint)->work_name }}</td>
                                    <td>{{ optional($row->department)->depName ?? '-' }}</td>
                                    <td>{{ optional($row->section)->secName ?? '-' }}</td>
                                    <td>{{ optional($row->item)->item_name }}</td>
                                    <td>{{ optional($row->description)->description_name }}
                                        ({{ optional($row->description)->unit_name }})
                                    </td>
                                    <td>{{ number_format($row->requested_qty, 2) }}</td>
                                    <td>{{ number_format($row->issued_qty, 2) }}</td>
                                    <td>{{ number_format($remaining, 2) }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td style="white-space:nowrap;">
                                        @can('Issue-Requested-Items')
                                            @if ($remaining > 0)
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick='openIssueModal(@json($payload))'>
                                                    Issue
                                                </button>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted">No pending requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ISSUE MODAL --}}
    <div class="modal fade" id="issueModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="issueForm" action="{{ route('sales.gs.issue.store') }}" method="POST">
                @csrf
                <input type="hidden" name="request_id" id="issue_request_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Issue Requested Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="issueModalBody">
                        <div class="text-center">Loading...</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn mb-2 btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function escapeHtml(value) {
                value = (value === null || value === undefined) ? '' : String(value);
                return value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            window.openIssueModal = function(row) {
                var html = '';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Request No</label>';
                html += '<input type="text" id="issue_request_no" class="form-control" value="' + escapeHtml(row
                    .request_no || '') + '" readonly>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Item</label>';
                html += '<input type="text" id="issue_item" class="form-control" value="' + escapeHtml(row
                    .item || '') + '" readonly>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label>Description</label>';
                html += '<input type="text" id="issue_desc" class="form-control" value="' + escapeHtml(row
                    .desc || '') + '" readonly>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-4">';
                html += '<label>Remaining Qty</label>';
                html += '<input type="text" id="issue_remaining" class="form-control" value="' + escapeHtml(row
                    .remaining || '') + '" readonly>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Issue Date</label>';
                html += '<input type="date" name="issue_date" id="issue_date" class="form-control" required>';
                html += '</div>';

                html += '<div class="col-md-4">';
                html += '<label><span style="color:red">*</span> Issued Qty</label>';
                html +=
                    '<input type="number" step="0.01" min="0.01" name="issued_qty" id="issue_qty" class="form-control" required>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-12">';
                html += '<label>Remarks</label>';
                html += '<textarea name="remarks" id="issue_remarks" class="form-control"></textarea>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row mt-2">';
                html += '<div class="col-md-12">';
                html +=
                    '<button type="button" class="btn btn-info" onclick="fillIssueAllRemaining()">Issue All Remaining</button>';
                html += '</div>';
                html += '</div>';

                $('#issue_request_id').val(row.id);
                $('#issueModalBody').html(html);
                $('#issueModal').modal('show');
            };

            window.fillIssueAllRemaining = function() {
                $('#issue_qty').val($('#issue_remaining').val());
            };
        });
    </script>
@endsection
