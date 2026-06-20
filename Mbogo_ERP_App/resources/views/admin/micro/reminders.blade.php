@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Loan Reminders</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('micro.dashboard') }}">Microfinance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Reminders</strong></li>
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

    <style>
        .ibox-custom {
            border: 1px solid #d9e2f2;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(23, 58, 122, .08);
            background: #fff;
        }

        .ibox-title-custom {
            background: linear-gradient(135deg, #173a7a 0%, #214f9c 55%, #244f96 100%);
            color: #fff;
        }

        .ibox-title-custom h5 {
            color: #fff !important;
            font-weight: 800;
        }
    </style>

    <div class="col-12">
        <h3 class="mb-2 page-title">Loan Reminders</h3>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-4">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Send Reminder</h5>
                    </div>
                    <div class="ibox-content">
                        @can('Send-Loan-Reminders')
                            <form id="reminderForm" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Loan Application</label>
                                    <select id="application_selector" class="form-control select2_demo_2" required>
                                        <option value="">-- Select Application --</option>
                                        @foreach ($applications as $app)
                                            <option value="{{ encrypt($app->id) }}">
                                                {{ $app->application_no }} - {{ optional($app->applicant)->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Reminder Date</label>
                                    <input type="date" name="reminder_date" value="{{ date('Y-m-d') }}" class="form-control"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>

                                <button class="btn btn-primary">Send / Record Reminder</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="ibox ibox-custom">
                    <div class="ibox-title ibox-title-custom">
                        <h5>Reminder History</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-bordered dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Application</th>
                                    <th>Phone</th>
                                    <th>Reminder Date</th>
                                    <th>SMS Charge</th>
                                    <th>Delivery Status</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ optional($row->application)->application_no }}</td>
                                        <td>{{ $row->phone_no }}</td>
                                        <td>{{ $row->reminder_date }}</td>
                                        <td>{{ number_format($row->sms_charge ?? 0, 2) }}</td>
                                        <td>{{ $row->delivery_status }}</td>
                                        <td>{{ $row->message }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No reminders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            $('#application_selector').on('change', function() {
                var encryptedId = $(this).val();
                if (encryptedId) {
                    $('#reminderForm').attr('action', "{{ url('/admin/micro/reminders/send') }}/" +
                        encodeURIComponent(encryptedId));
                } else {
                    $('#reminderForm').attr('action', '#');
                }
            });
        });
    </script>
@endsection
