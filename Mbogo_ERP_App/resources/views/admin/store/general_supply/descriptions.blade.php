@extends('layouts.AdminMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-title bg-primary">
                    <h5>General Supply Descriptions</h5>
                </div>

                <div class="ibox-content">

                    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addModal">
                        <i class="fa fa-plus"></i> Add Description
                    </button>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($descriptions as $k => $d)
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $d->name ?? '' }}</td>

                                <td>
                                    <span class="label label-primary">Active</span>
                                </td>

                                <td>
                                    <button class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button class="btn btn-xs btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No Data Found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>

</div>
<!-- ADD DESCRIPTION MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('gs.descriptions.store') }}">
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Add Description</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Description Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>

            </div>

        </form>
    </div>
</div>
@endsection