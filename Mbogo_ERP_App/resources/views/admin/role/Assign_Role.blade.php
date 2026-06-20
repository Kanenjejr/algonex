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
                <strong>Assign Permission</strong>
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
              <h3 class="mb-2 page-title">Staff Assign Permission</h3>
          </div>
          <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                      <h5>Staff Details Table.</h5>
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
                            <th>Full Name:</th>
                            <th>Stafff Title:</th>
                            <th>Phone:</th>
                            <th>Email:</th>
                            <th>Company</th>
                            <th>Work Point</th>
                            <th>Assign Permission:</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($role as $item)
                            <tr>
                                <td>{{$item->name}}</td>
                                <td>{{$item->role}}</td>
                                <td>{{$item->phone_No}}</td>
                                <td>{{$item->email}}</td>
                                <td>{{ optional($item->company)->company_name ?? '-' }}</td>
                                <td>{{ optional($item->workpoint)->work_name ?? '-' }}</td>
                                <td>
                                  <a class="fa fa-edit" style="color: rgb(0, 128, 90)"  href="{{route('attachrole',[encrypt($item->id)])}}" class="btn mb-2 btn-primary" > Assign</a><br>
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
        </div>
    @endsection

