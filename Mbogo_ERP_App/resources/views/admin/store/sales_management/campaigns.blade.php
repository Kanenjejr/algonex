@php
    use Illuminate\Support\Facades\Crypt;
@endphp

@extends('layouts.salesMaster')

@section('content')

    <style>
        .select2-container {
            width: 100% !important;
        }

        select.select2-hidden-accessible {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            position: absolute !important;
        }
    </style>

    <div class="wrapper wrapper-content">

        {{-- ================= HEADER ================= --}}
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-8">
                <h2 class="dashboard-title">Sales Management Module</h2>

                <ol class="breadcrumb" style="font-size:16px;color:#000">
                    <li>
                        <a href="{{ route('sales.dashboard') }}">Sales Management</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Campaigns</strong>
                    </li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Current Date</h4>

                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>
                            @php
                                $carbon = \Carbon\Carbon::now();
                                echo $carbon->format('l') . ' , ' . $carbon->toDateString();
                            @endphp
                        </strong>
                    </li>
                </ol>
            </div>

            <div class="col-lg-2">
                <h4>Time</h4>

                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <strong>
                            <table>
                                <tr>
                                    <td id="Hour" style="color:green;"></td>
                                    <td id="Minut" style="color:green;"></td>
                                    <td id="Second" style="color:red;"></td>
                                </tr>
                            </table>
                        </strong>
                    </li>
                </ol>
            </div>
        </div>

        <script>
            function timedMsg() {
                setInterval(change_time, 1000);
            }

            function change_time() {
                const d = new Date();

                document.getElementById('Hour').innerHTML =
                    String(d.getHours()).padStart(2, '0') + ':';

                document.getElementById('Minut').innerHTML =
                    String(d.getMinutes()).padStart(2, '0') + ':';

                document.getElementById('Second').innerHTML =
                    String(d.getSeconds()).padStart(2, '0');
            }

            timedMsg();
        </script>

        {{-- ================= SUMMARY CARDS ================= --}}
        <div class="row mt-3">

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-primary text-white text-center">
                        <h5>Total Campaigns</h5>
                        <h2>{{ $totalCampaigns ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-success text-white text-center">
                        <h5>Active Campaigns</h5>
                        <h2>{{ $activeCampaigns ?? 0 }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-warning text-white text-center">
                        <h5>Total Discount</h5>
                        <h2>{{ number_format($totalDiscount ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-content bg-info text-white text-center">
                        <h5>Revenue Generated</h5>
                        <h2>{{ number_format($campaignRevenue ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= CREATE CAMPAIGN ================= --}}
        @can('Create-Campaigns')
            <div class="ibox">
                <div class="ibox-title bg-primary">
                    <h5>Create Campaign</h5>
                </div>

                <div class="ibox-content">

                    <form method="POST" action="{{ route('campaigns.store') }}">
                        @csrf

                        <div class="row">

                            {{-- COMPANY --}}
                            <div class="col-md-4">
                                <label>Company *</label>

                                <select name="company_id" id="company" class="form-control select2_demo_2" required>

                                    <option value="">Select Company</option>

                                    @foreach ($companies as $c)
                                        <option value="{{ $c->id }}" data-code="{{ $c->company_code }}"
                                            data-name="{{ $c->company_name }}"
                                            {{ old('company_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->company_code }} - {{ $c->company_name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            {{-- BUSINESS --}}
                            <div class="col-md-4">
                                <label>Business *</label>

                                <select name="business_unit_id" id="business" class="form-control select2_demo_2" required>

                                    <option value="">Select Business</option>

                                </select>
                            </div>

                            {{-- WORKPOINT --}}
                            <div class="col-md-4">
                                <label>Location *</label>

                                <select name="work_point_id" id="work_point" class="form-control select2_demo_2" required>

                                    <option value="">Select Location</option>

                                </select>
                            </div>

                            {{-- NAME --}}
                            <div class="col-md-4">
                                <label>Name *</label>

                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                            </div>

                            {{-- TYPE --}}
                            <div class="col-md-4">
                                <label>Type *</label>

                                <select name="type" class="form-control select2_demo_2" required>

                                    <option value="">Select Type</option>

                                    <option value="discount" {{ old('type') == 'discount' ? 'selected' : '' }}>
                                        Discount
                                    </option>

                                    <option value="promotion" {{ old('type') == 'promotion' ? 'selected' : '' }}>
                                        Promotion
                                    </option>

                                    <option value="awareness" {{ old('type') == 'awareness' ? 'selected' : '' }}>
                                        Awareness
                                    </option>

                                    <option value="seasonal" {{ old('type') == 'seasonal' ? 'selected' : '' }}>
                                        Seasonal
                                    </option>

                                </select>
                            </div>

                            {{-- CUSTOMER TYPE --}}
                            <div class="col-md-4">
                                <label>Customer Type</label>

                                <select name="customer_type" class="form-control select2_demo_2">

                                    <option value="all" {{ old('customer_type', 'all') == 'all' ? 'selected' : '' }}>
                                        All Customers
                                    </option>

                                    <option value="new" {{ old('customer_type') == 'new' ? 'selected' : '' }}>
                                        New Customers
                                    </option>

                                    <option value="existing" {{ old('customer_type') == 'existing' ? 'selected' : '' }}>
                                        Existing Customers
                                    </option>

                                </select>
                            </div>

                            {{-- DISCOUNT --}}
                            <div class="col-md-4">
                                <label>Discount</label>

                                <input type="number" name="discount" value="{{ old('discount', 0) }}" class="form-control"
                                    step="0.01">
                            </div>

                            {{-- BUDGET --}}
                            <div class="col-md-4">
                                <label>Budget</label>

                                <input type="number" name="budget" value="{{ old('budget', 0) }}" class="form-control"
                                    step="0.01">
                            </div>

                            {{-- STATUS --}}
                            <div class="col-md-4">
                                <label>Status</label>

                                <select name="status" class="form-control select2_demo_2">

                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>

                                </select>
                            </div>

                            {{-- START --}}
                            <div class="col-md-4">
                                <label>Start Date *</label>

                                <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control"
                                    required>
                            </div>

                            {{-- END --}}
                            <div class="col-md-4">
                                <label>End Date *</label>

                                <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control"
                                    required>
                            </div>

                            {{-- DESCRIPTION --}}
                            <div class="col-md-12">
                                <label>Description</label>

                                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-success mt-3">
                            <i class="fa fa-save"></i>
                            Save Campaign
                        </button>

                        <button type="reset" class="btn btn-default mt-3">
                            <i class="fa fa-refresh"></i>
                            Reset
                        </button>

                    </form>

                </div>
            </div>
        @endcan

        {{-- ================= CAMPAIGN TABLE ================= --}}
        @can('View-Campaigns')
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Campaign List</h5>
                </div>

                <div class="ibox-content">

                    <table class="table table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Company Code</th>
                                <th>Company Name</th>
                                <th>Business Code</th>
                                <th>Business Name</th>
                                <th>Location Code</th>
                                <th>Location Name</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($campaigns as $c)
                                <tr>
                                    <td>{{ $c->name }}</td>

                                    <td>{{ $c->company_code ?? ($c->company->company_code ?? '-') }}</td>
                                    <td>{{ $c->company_name ?? ($c->company->company_name ?? '-') }}</td>

                                    <td>{{ $c->business_code ?? ($c->businessUnit->unit_code ?? '-') }}</td>
                                    <td>{{ $c->business_name ?? ($c->businessUnit->unit_name ?? '-') }}</td>

                                    <td>{{ $c->location_code ?? ($c->workPoint->work_code ?? '-') }}</td>
                                    <td>{{ $c->location_name ?? ($c->workPoint->work_name ?? '-') }}</td>

                                    <td>{{ ucfirst($c->type) }}</td>

                                    <td>{{ $c->description ?? '-' }}</td>

                                    <td>{{ number_format($c->discount, 2) }}</td>

                                    <td>
                                        @if (
                                            $c->status == 'active' &&
                                                now()->between(\Carbon\Carbon::parse($c->start_date), \Carbon\Carbon::parse($c->end_date)))
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Expired</span>
                                        @endif
                                    </td>

                                    <td>{{ $c->start_date }} - {{ $c->end_date }}</td>

                                    <td>
                                        <div class="btn-group">

                                            @can('Edit-Campaigns')
                                                <a href="{{ route('campaigns.edit', ['id' => Crypt::encryptString($c->id)]) }}"
                                                    class="btn btn-xs btn-primary">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('Delete-Campaigns')
                                                <form
                                                    action="{{ route('campaigns.delete', ['id' => Crypt::encryptString($c->id)]) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this campaign?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-danger">
                                        No Campaign Data Found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        @endcan

        {{-- ================= PERFORMANCE ================= --}}
        @can('View-Campaign-Reports')
            <div class="ibox">
                <div class="ibox-title bg-info text-white">
                    <h5>Campaign Performance (Sales & Financial Impact)</h5>
                </div>

                <div class="ibox-content">

                    <div class="row text-center">

                        <div class="col-md-3">
                            <div class="card shadow-sm p-3">
                                <h5>Revenue</h5>
                                <h3 class="text-success">
                                    {{ number_format($campaignRevenue ?? 0, 2) }}
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm p-3">
                                <h5>Discount Cost</h5>
                                <h3 class="text-danger">
                                    {{ number_format($totalDiscount ?? 0, 2) }}
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm p-3">
                                <h5>Profit</h5>
                                <h3 class="text-primary">
                                    {{ number_format($profit ?? ($campaignRevenue ?? 0) - ($totalDiscount ?? 0), 2) }}
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm p-3">
                                <h5>ROI (%)</h5>
                                <h3 class="text-warning">
                                    {{ number_format($roi ?? 0, 2) }} %
                                </h3>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <div class="row text-center mt-3">

                        <div class="col-md-4">
                            <h5>Total Budget</h5>
                            <h3 class="text-secondary">
                                {{ number_format($totalBudget ?? 0, 2) }}
                            </h3>
                        </div>

                        <div class="col-md-4">
                            <h5>Active Campaigns</h5>
                            <h3 class="text-info">
                                {{ $activeCampaigns ?? 0 }}
                            </h3>
                        </div>

                        <div class="col-md-4">
                            <h5>Performance Status</h5>

                            @php
                                $calcProfit = $profit ?? ($campaignRevenue ?? 0) - ($totalDiscount ?? 0);
                            @endphp

                            @if ($calcProfit > 0)
                                <span class="badge badge-success p-2">Profitable</span>
                            @elseif ($calcProfit < 0)
                                <span class="badge badge-danger p-2">Loss</span>
                            @else
                                <span class="badge badge-secondary p-2">Break Even</span>
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        @endcan

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const businessUnitsUrl = "{{ route('sales.ajax.business.units', ':companyId') }}";
            const workPointsUrl = "{{ route('sales.ajax.work.points', ':unitId') }}";

            // ================= COMPANY → BUSINESS UNITS =================
            $('#company').on('change', function() {

                let companyId = $(this).val();

                $('#business')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                $('#work_point')
                    .empty()
                    .append('<option value="">Select Location</option>')
                    .trigger('change.select2');

                if (!companyId) {

                    $('#business')
                        .empty()
                        .append('<option value="">Select Business</option>')
                        .trigger('change.select2');

                    return;
                }

                let url = businessUnitsUrl.replace(':companyId', companyId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {

                        $('#business')
                            .empty()
                            .append('<option value="">Select Business</option>');

                        if (data.length > 0) {

                            data.forEach(b => {

                                $('#business').append(
                                    $('<option>', {
                                        value: b.id,
                                        text: `${b.unit_code} - ${b.unit_name}`
                                    })
                                    .attr('data-code', b.unit_code)
                                    .attr('data-name', b.unit_name)
                                );

                            });

                        } else {

                            $('#business')
                                .empty()
                                .append('<option value="">No Business Unit Found</option>');
                        }

                        $('#business').trigger('change.select2');

                    })
                    .catch(error => {

                        console.error(error);

                        $('#business')
                            .empty()
                            .append('<option value="">Error loading business units</option>')
                            .trigger('change.select2');
                    });

            });

            // ================= BUSINESS UNIT → WORK POINTS =================
            $('#business').on('change', function() {

                let unitId = $(this).val();

                $('#work_point')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .trigger('change.select2');

                if (!unitId) {

                    $('#work_point')
                        .empty()
                        .append('<option value="">Select Location</option>')
                        .trigger('change.select2');

                    return;
                }

                let url = workPointsUrl.replace(':unitId', unitId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {

                        $('#work_point')
                            .empty()
                            .append('<option value="">Select Location</option>');

                        if (data.length > 0) {

                            data.forEach(w => {

                                $('#work_point').append(
                                    $('<option>', {
                                        value: w.id,
                                        text: `${w.work_code} - ${w.work_name}`
                                    })
                                    .attr('data-code', w.work_code)
                                    .attr('data-name', w.work_name)
                                );

                            });

                        } else {

                            $('#work_point')
                                .empty()
                                .append('<option value="">No Location Found</option>');
                        }

                        $('#work_point').trigger('change.select2');

                    })
                    .catch(error => {

                        console.error(error);

                        $('#work_point')
                            .empty()
                            .append('<option value="">Error loading locations</option>')
                            .trigger('change.select2');
                    });

            });

        });
    </script>

@endsection
