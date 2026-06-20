@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Loan Categories</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Loan Categories</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong><?php use Carbon\Carbon;
                $carbon = Carbon::now();
                $carbon1 = Carbon::now()->toDateString();
                echo $carbon->format('l');
                echo ' , ';
                echo $carbon1; ?></strong></li>
            </ol>
        </div>
        <div class="col-lg-1">
            <h2>Time</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong>
                        <table>
                            <tr>
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong></li>
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

    <div class="wrapper wrapper-content">
        <div class="row">
            @can('Register-Loan-Categories')
                <div class="col-md-4">
                    <div class="ibox"
                        style="border:1px solid #d9e2f2;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(23,58,122,.08);">
                        <div class="ibox-title"
                            style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%);color:#fff;">
                            <h5 style="color:#fff;">Add Category</h5>
                        </div>
                        <div class="ibox-content">
                            <form action="{{ route('micro.loan_categories.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Category Name</label>
                                    <input type="text" name="category_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="4"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="col-md-8">
                <div class="ibox"
                    style="border:1px solid #d9e2f2;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(23,58,122,.08);">
                    <div class="ibox-title"
                        style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%);color:#fff;">
                        <h5 style="color:#fff;">Categories Table</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-striped table-bordered dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $row->category_name }}</td>
                                        <td>{{ $row->description }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            @can('Edit-Loan-Categories')
                                                <form action="{{ route('micro.loan_categories.update', encrypt($row->id)) }}"
                                                    method="POST" style="display:inline-block;width:100%;">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="category_name"
                                                        value="{{ $row->category_name }}">
                                                    <input type="hidden" name="description" value="{{ $row->description }}">
                                                    <input type="hidden" name="status" value="Active">
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick="$('#editRow{{ $row->id }}').toggle();">Edit</button>
                                                </form>
                                            @endcan
                                            @can('Delete-Loan-Categories')
                                                <a href="{{ route('micro.loan_categories.remove', encrypt($row->id)) }}"
                                                    class="btn btn-sm btn-danger">Remove</a>
                                            @endcan
                                        </td>
                                    </tr>
                                    <tr id="editRow{{ $row->id }}" style="display:none;">
                                        <td colspan="5">
                                            <form action="{{ route('micro.loan_categories.update', encrypt($row->id)) }}"
                                                method="POST">
                                                @csrf @method('PUT')
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label>Category Name</label>
                                                        <input type="text" name="category_name"
                                                            value="{{ $row->category_name }}" class="form-control"
                                                            required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Description</label>
                                                        <input type="text" name="description"
                                                            value="{{ $row->description }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="Active"
                                                                {{ $row->status == 'Active' ? 'selected' : '' }}>Active
                                                            </option>
                                                            <option value="Deleted">Deleted</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2" style="padding-top:24px;">
                                                        <button class="btn btn-primary btn-block">Update</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
