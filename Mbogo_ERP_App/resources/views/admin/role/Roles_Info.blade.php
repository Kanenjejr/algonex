@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Staff Title/Role Information</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('business-admin') }}">Business Administration</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Title/Role Registration</strong>
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
 function timedMsg()
  {
    var t=setInterval("change_time();",1000);
  }
 function change_time()
 {
   var d = new Date();
   var curr_hour = d.getHours();
   var curr_min = d.getMinutes();
   var curr_sec = d.getSeconds();
   if(curr_hour > 24)
     curr_hour = curr_hour - 24;
   document.getElementById('Hour').innerHTML =curr_hour+':';
    document.getElementById('Minut').innerHTML=curr_min+':';
    document.getElementById('Second').innerHTML=curr_sec;
 }
timedMsg();
</script>
        <div class="col-12">
              <h3 class="mb-2 page-title">Staff Title/Role Information</h3>
              @can('Register-Title/Role-Details')
              <button style="position: absolute; top: 4.5%; right: 1.7%;;" type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#varyModal" data-whatever="@mdo">Add Title</button>
              @endcan
          </div>
            <div class="col-6">
              <div class="modal fade" id="varyModal" tabindex="-1" role="dialog" aria-labelledby="varyModalLabel" aria-hidden="true">
                <div class="modal-dialog mw-50 w-50" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="varyModalLabel"> Title/Role Registration</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="reg" action="{{route('regrole')}}" method="POST" >
                        @csrf
                        <div class="form-group">
                          <label for="slug" class="col-form-label">Title/Role:<span style="color: red">*</span></label>
                          <input type="text" class="form-control" name="slug" id="slug" placeholder="Eg. MD, Director, Manager" required>
                        <span style="color: red">
                            @error('slug')
                                {{ $message }}
                            @enderror
                        </span>
                        </div>
                    <div class="modal-footer">
                      <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                      <button onclick="handleConfirmSubmit('reg')"  type="submit" value="upload" class="btn mb-2 btn-primary left">Submit</button>
                    </div>
                </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                      <h5>Title/Role Details Table.</h5>
                       <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                          <tr>
                            <th>#</th>
                            <th>Title/Role</th>
                            @can('Delete-Title/Role-Details')
                            <th>Action</th>
                            @endcan
                          </tr>
                        </thead>
                        <tbody>
                            @foreach ($role as $key=> $role)
                          <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $role->name }}</td>
                            @can('Delete-Title/Role-Details')
                            <td>
                              <a class="fa fa-trash"  onclick="return confirm('Are you sure you want to delete this item?');" style="color: red" href="{{ route('remvrole',encrypt([$role->id]))}}"> Remove</a>
                            </td>
                            @endcan
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                       </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
@endsection

