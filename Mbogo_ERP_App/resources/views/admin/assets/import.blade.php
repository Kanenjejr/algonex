@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Asset Excel Import Information</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('accounting') }}">Accounting And Finance</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active"><strong>Asset Excel Import</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><strong>{{ \Carbon\Carbon::now()->format('l') }} ,
                        {{ \Carbon\Carbon::now()->toDateString() }}</strong></li>
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
            var d = new Date(),
                h = d.getHours(),
                m = d.getMinutes(),
                s = d.getSeconds();
            if (h > 24) h -= 24;
            document.getElementById('Hour').innerHTML = h + ':';
            document.getElementById('Minut').innerHTML = m + ':';
            document.getElementById('Second').innerHTML = s;
        }
        timedMsg();
    </script>

    <div class="col-12">
        <h3>Import Asset Register From Excel / CSV</h3>
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-success">
                        <div class="ibox-tools">
                            <a href="{{ route('assets.report') }}" class="btn btn-secondary text-white"><i
                                    class="fa fa-arrow-left"></i> Back To Asset Report</a>
                            <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            <a class="close-link"><i class="fa fa-times"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="margin-bottom:0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('assets.import.excel') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><strong>Work Point</strong> <span style="color:red">*</span></label>
                                        <select name="work_point_id" class="form-control select2_demo_2" required>
                                            <option value="">-- Select Work Point --</option>
                                            @foreach ($workPoints as $wp)
                                                <option value="{{ $wp->id }}"
                                                    @if ((string) old('work_point_id', $selectedWorkPoint ?? '') === (string) $wp->id) selected @endif>{{ $wp->work_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><strong>Default Category</strong></label>
                                        <select name="default_category_id" class="form-control select2_demo_2">
                                            <option value="">-- Use Category Column / Skip If Missing --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}"
                                                    @if ((string) old('default_category_id') === (string) $cat->id) selected @endif>{{ $cat->name }}
                                                    ({{ number_format($cat->depreciation_rate ?? 0, 2) }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><strong>Excel / CSV File</strong> <span style="color:red">*</span></label>
                                        <input type="file" name="asset_file" class="form-control"
                                            accept=".xlsx,.xls,.csv,.txt" required>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                Accepted columns include: <strong>Description / Asset Name</strong>, Asset Tag, Category,
                                Purchase Date, Cost, Opening Cost, Additions, Depreciation Rate, Accumulated Depreciation.
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Import Asset
                                Register</button>
                            <a href="{{ route('assets.report') }}" class="btn btn-default">Cancel</a>
                        </form>

                        @if (!empty($previewRows))
                            <hr>
                            <h4>Last Import Preview</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Asset</th>
                                            <th>Tag</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th class="text-right">Cost</th>
                                            <th class="text-right">Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($previewRows as $k => $row)
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ $row['asset_name'] ?? '-' }}</td>
                                                <td>{{ $row['asset_tag'] ?? '-' }}</td>
                                                <td>{{ $row['category'] ?? '-' }}</td>
                                                <td>{{ $row['purchase_date'] ?? '-' }}</td>
                                                <td class="text-right">{{ number_format($row['purchase_cost'] ?? 0, 2) }}
                                                </td>
                                                <td class="text-right">{{ number_format($row['rate'] ?? 0, 2) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
