@extends('layouts.AdminMaster')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Permissions Assignment</h2>
        <ol class="breadcrumb"style="font-size:17px;color:#000">
            <li>
                <a href="{{ route('business-admin') }}">Business Administration</a>
            </li>
            <span style="font-size:25px"class="fa fa-angle-double-right "></span>
            <li class="breadcrumb-item active">
                <strong>Assign Permissions</strong>
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
<br>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox ">
            <div class="ibox-title bg-success">
                <h5>Staff New Permissions | <small>Attach Permissions Here</small></h5>
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
                <div class="row">
                    <div class="col-sm-12 b-r"><h3 class="m-t-none m-b"></h3>
                        <form id="reg"role="form" method="POST" action="{{route('storerole')}}">
                           @csrf
                            <input type="hidden" value="{{$user->id}}" name="User_id">
                            <div class="row">
                            <div class="form-group col-md-4">
                                <label>Staff First Name: <span style="color: red">*</span></label>
                                <input type="text" value="{{$user->name}}" name="FirstName" autocomplete="off"  class="form-control"readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Staff Title: <span style="color: red">*</span></label>
                                <input type="text" value="{{$user->role}}" name="role" autocomplete="off"  class="form-control"readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Staff Email: <span style="color: red">*</span></label>
                                <input type="text" value="{{$user->email}}" name="Email" autocomplete="off"  class="form-control"readonly>
                            </div>
                            </div>
                            <div class="row">
                            <div class="form-group col-md-12">
                                <label for="Role" class="col-form-label">Select Permissions:<span style="color: red">*</span></label>
                                <table class="table table-striped table-bordered table-hover dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Permission Slug</th>
                                            <th>Permission Name</th>
                                            <th>Condition</th>
                                            <th>Permission</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($perm as $item)
                                        <tr>
                                            <td>{{$item->id}}</td>
                                            <td>{{$item->slug}}</td>
                                            <td>{{$item->name}}</td>
                                            <td>
                                                @if ($item->users->contains($user->id))
                                                    <span class="label label-primary">Assigned</span>
                                                @else
                                                    <span class="label label-danger"> Not Assigned</span>
                                                @endif
                                            </td>
                                            <td><input type="checkbox" name="perm[]" value="{{$item->id}}"
                                                data-toggle="toggle" data-on="Yes" data-off="No"
                                                data-onstyle="success" data-offstyle="danger">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                              </div>
                            </div>
                           <div>
                                <button onclick="return confirm('Are You Sure You Want To Perform This Task ?');" name="remove"class="btn btn-sm btn-danger float-left m-t-n-xs" type="submit"><strong>Remove</strong></button>

                                <button onclick="return confirm('Are You Sure You Want To Perform This Task ?');" name="assign"class="btn btn-sm btn-primary float-right m-t-n-xs" type="submit"><strong>Assign</strong></button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
