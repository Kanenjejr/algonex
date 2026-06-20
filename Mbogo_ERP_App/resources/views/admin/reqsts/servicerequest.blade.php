@extends('layouts.ReqstMaster')
@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Requisition & Approvals Dashboard</h2>
        <ol class="breadcrumb" style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('requisition') }}">Requisition & Approvals</a>
            </li>
            <span style="font-size:25px" class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Service Request</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <h2>Current Date</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">
                <strong>
                    <?php use Carbon\Carbon;
              $carbon=Carbon::now();
              $carbon1=Carbon::now()->toDateString();
              echo $carbon->format('l'); echo" , ";echo $carbon1;
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
    <h3 class="mb-2 page-title">Service Requests</h3>

    @can('Register-ServiceRequest')
    <button style="position: absolute; top: 4.5%; right: 1.7%;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#serviceCreateModal">Create Service Request</button>
    @endcan
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title bg-success">
                    <h5>Service Requests Table</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Request No</th>
                                    <th>Request Date</th>
                                    <th>Work Point</th>
                                    <th>Service Type</th>
                                    <th>Estimated Cost</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($services as $k => $s)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $s->RequestNo }}</td>
                                    <td>{{ optional($s->RequestDate)->format('Y-m-d') ?? '-' }}</td>
                                    <td>{{ optional($s->workpoint)->work_name ?? '-' }}</td>
                                    <td>{{ $s->ServiceType }}</td>
                                    <td>{{ number_format($s->estimated_cost, 2) }}</td>
                                    <td>{{ $s->Status }}</td>
                                    <td>
                                        @can('View-ServiceRequest')
                                        <a href="{{ route('servicerequest.show', encrypt($s->id)) }}" class="btn btn-sm btn-info">View</a>
                                        @endcan

                                        @can('Edit-ServiceRequest')
                                        @if($s->Status === 'Pending')
                                        <button class="btn btn-sm btn-warning btn-edit-service" data-id="{{ encrypt($s->id) }}">Edit</button>
                                        @endif
                                        @endcan

                                        @can('Delete-ServiceRequest')
                                        @if($s->Status === 'Pending')
                                        <a href="{{ route('servicerequest.remove', encrypt($s->id)) }}" class="btn btn-sm btn-danger">Remove</a>
                                        @endif
                                        @endcan

                                        @can('Approve-ServiceRequest')
                                        @if($s->Status === 'Pending')
                                        <form style="display:inline-block" method="POST" action="{{ route('servicerequest.approve', encrypt($s->id)) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this service request?')">Approve</button>
                                        </form>
                                        <button class="btn btn-sm btn-secondary btn-reject-service" data-id="{{ encrypt($s->id) }}">Reject</button>
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

<div class="modal fade" id="serviceCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="serviceCreateForm" action="{{ route('servicerequest.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Service Request</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    @if(in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']))
                    <div class="form-group">
                        <label>Work Point</label>
                        <select id="service_create_work_point_id" name="work_point_id" class="form-control select2_demo_3" required>
                            <option value="">-- Select work point --</option>
                            @foreach($workPoints as $wp)
                            <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="work_point_id" value="{{ auth()->user()->work_point_id }}">
                    @endif

                    <div class="form-group">
                        <label>Request Date</label>
                        <input type="date" name="RequestDate" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label>Service Type</label>
                        <input type="text" name="ServiceType" class="form-control" placeholder="Maintenance, Allowance, etc." required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="Description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Estimated Cost</label>
                        <input type="number" step="0.01" name="estimated_cost" class="form-control" value="0">
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn mb-2 btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="serviceEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="serviceEditForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Service Request</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="serviceEditBody">
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
@php
    $canEditWorkPoint = in_array(optional(auth()->user())->role, ['Admin','CEO','Admin-Developer']);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {

    const WORKPOINTS = @json($workPoints->map(fn($w)=>[
        'id'=>$w->id,
        'name'=>$w->work_name
    ]));
    const CAN_EDIT_WORKPOINT = @json($canEditWorkPoint);

    function initSelect2(el, parent) {
        if (!$(el).length) return;
        if ($(el).data('select2')) {
            try { $(el).select2('destroy'); } catch(e){}
        }
        $(el).select2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: parent ? $(parent) : $(document.body)
        });
    }

    // Init normal select2
    $('.select2_demo_3').each(function(){
        initSelect2(this);
    });

    // Create modal
    $('#serviceCreateModal').on('shown.bs.modal', function(){
        $(this).find('.select2_demo_3').each(function(){
            initSelect2(this, '#serviceCreateModal');
        });
    });

    // ================= EDIT =================
    document.querySelectorAll('.btn-edit-service').forEach(btn=>{
        btn.addEventListener('click', function () {

            const encId = this.dataset.id;
            $('#serviceEditBody').html('<div class="text-center">Loading...</div>');
            $('#serviceEditModal').modal('show');

            $.get("{{ url('/admin/servicerequest/edit') }}/"+encId)
            .done(resp=>{
                const s = resp.service;
                let html = '';

                if (CAN_EDIT_WORKPOINT) {
                    html += '<div class="form-group">';
                    html += '<label>Work Point</label>';
                    html += '<select name="work_point_id" id="edit_service_work_point_id" class="form-control select2_demo_3">';
                    html += '<option value="">-- Select --</option>';

                    WORKPOINTS.forEach(w=>{
                        html += `<option value="${w.id}" ${s.work_point_id==w.id?'selected':''}>${w.name}</option>`;
                    });

                    html += '</select></div>';
                }

                html += `
                    <div class="form-group">
                        <label>Request Date</label>
                        <input type="date" name="RequestDate" class="form-control" value="${s.RequestDate ? s.RequestDate.substring(0,10) : ''}">
                    </div>
                    <div class="form-group">
                        <label>Service Type</label>
                        <input type="text" name="ServiceType" class="form-control" value="${s.ServiceType||''}">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="Description" class="form-control">${s.Description||''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Estimated Cost</label>
                        <input type="number" step="0.01" name="estimated_cost" class="form-control" value="${s.estimated_cost||0}">
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control">${s.remarks||''}</textarea>
                    </div>
                `;

                $('#serviceEditBody').html(html);
                $('#serviceEditForm').attr('action',
                    "{{ route('servicerequest.update', ':id') }}".replace(':id', encId)
                );

                $('#serviceEditModal .select2_demo_3').each(function(){
                    initSelect2(this, '#serviceEditModal');
                });
            })
            .fail(()=>{
                $('#serviceEditBody').html('<div class="text-danger">Failed to load.</div>');
            });

        });
    });

    // ================= DELETE =================
    document.querySelectorAll('.btn-delete-service').forEach(btn=>{
        btn.addEventListener('click', function(){
            const id = this.dataset.id;
            Swal.fire({
                title:'Are you sure?',
                text:'This will mark request as Deleted',
                icon:'warning',
                showCancelButton:true
            }).then(r=>{
                if(r.isConfirmed){
                    window.location.href="{{ url('/admin/servicerequest/remove') }}/"+id;
                }
            });
        });
    });

    // ================= REJECT =================
    document.querySelectorAll('.btn-reject-service').forEach(btn=>{
        btn.addEventListener('click', function(){
            const id = this.dataset.id;
            Swal.fire({
                title:'Reject request',
                input:'textarea',
                showCancelButton:true
            }).then(r=>{
                if(r.isConfirmed){
                    const f=$(`<form method="POST" action="{{ url('/admin/servicerequest/reject') }}/${id}">@csrf</form>`);
                    f.append(`<input type="hidden" name="remarks" value="${$('<div>').text(r.value||'').html()}">`);
                    $('body').append(f).submit();
                }
            });
        });
    });

});
</script>

@endsection
