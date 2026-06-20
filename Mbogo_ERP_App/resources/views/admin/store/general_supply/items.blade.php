@extends('layouts.AdminMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-title bg-primary">
                    <h5>General Supply Items</h5>
                </div>

                <div class="ibox-content">

                    {{-- CREATE BUTTON --}}
                    <div class="mb-3">
                        <button class="btn btn-success" data-toggle="modal" data-target="#createItemModal">
                            <i class="fa fa-plus"></i> Add Item
                        </button>
                    </div>

                    {{-- TABLE --}}
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Type</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($items ?? [] as $k => $item)
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->unit }}</td>

                                <td>
                                    @if($item->status)
                                        <span class="label label-primary">Active</span>
                                    @else
                                        <span class="label label-default">Inactive</span>
                                    @endif
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
                                <td colspan="6" class="text-center">No Items Found</td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>
            </div>

        </div>
    </div>

</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="createItemModal">
    <div class="modal-dialog">
        <form method="POST" action="">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h4>Add Item</h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Item Name</label>
                        <input type="text" name="name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="raw">Raw</option>
                            <option value="finished">Finished</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unit</label>
                        <input type="text" name="unit" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Save</button>
                </div>

            </div>

        </form>
    </div>
</div>

@endsection