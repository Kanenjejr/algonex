@extends('layouts.salesMaster')

@section('content')
    <style>
        .iti {
            width: 100%;
        }

        .iti input {
            width: 100%;
        }

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

    @php
        use Illuminate\Support\Facades\Crypt;
    @endphp

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
                        <strong>Customer</strong>
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

        {{-- ================= CREATE CUSTOMER ================= --}}
        @can('Create-Customers')
            <div class="ibox mt-3">
                <div class="ibox-title bg-primary">
                    <h5>Create Customer</h5>
                </div>

                <div class="ibox-content">

                    <form action="{{ route('sales.customers.store') }}" method="POST">
                        @csrf

                        <div class="row">

                            {{-- ================= ROW 1: CUSTOMER BASIC INFO ================= --}}

                            <div class="col-md-4">
                                <label>Customer Code</label>
                                <input type="text"
                                       id="customer_code"
                                       name="customer_code"
                                       class="form-control"
                                       placeholder="Auto Generated"
                                       readonly>
                            </div>

                            <div class="col-md-4">
                                <label>Customer Name</label>
                                <input type="text"
                                       id="customer_name"
                                       name="customer_name"
                                       class="form-control"
                                       placeholder="Enter Customer Name">
                            </div>

                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="status" class="form-control select2_demo_2">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            {{-- ================= ROW 2: CONTACT INFO ================= --}}

                            <div class="col-md-4">
                                <label>Phone</label>
                                <input type="tel" name="phone" id="phone" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>TIN Number</label>
                                <input type="text" name="tin_number" id="tin_number" class="form-control">
                            </div>
                            {{-- ================= ROW 3: COUNTRY / ACCOUNT ================= --}}

                            <div class="col-md-4">
                                <label>Country</label>
                                <select name="country" id="country" class="form-control select2_demo_2">
                                    <option value="">Search country...</option>

                                    <option value="TZ">Tanzania</option>
                                    <option value="KE">Kenya</option>
                                    <option value="UG">Uganda</option>
                                    <option value="RW">Rwanda</option>
                                    <option value="BI">Burundi</option>
                                    <option value="CD">DR Congo</option>
                                    <option value="ZM">Zambia</option>
                                    <option value="MW">Malawi</option>
                                    <option value="ZA">South Africa</option>
                                    <option value="NG">Nigeria</option>
                                    <option value="GH">Ghana</option>
                                    <option value="ET">Ethiopia</option>
                                    <option value="SD">Sudan</option>
                                    <option value="EG">Egypt</option>
                                    <option value="DZ">Algeria</option>
                                    <option value="MA">Morocco</option>
                                    <option value="TN">Tunisia</option>
                                    <option value="SO">Somalia</option>
                                    <option value="ZW">Zimbabwe</option>
                                    <option value="BW">Botswana</option>
                                    <option value="NA">Namibia</option>
                                    <option value="LS">Lesotho</option>
                                    <option value="SZ">Eswatini</option>
                                    <option value="GM">Gambia</option>
                                    <option value="SN">Senegal</option>
                                    <option value="KM">Comoros</option>
                                    <option value="ML">Mali</option>
                                    <option value="NE">Niger</option>
                                    <option value="TD">Chad</option>
                                    <option value="CM">Cameroon</option>
                                    <option value="GA">Gabon</option>
                                    <option value="CI">Ivory Coast</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Destination Country</label>
                                <select name="destination" id="destination" class="form-control select2_demo_2" required>
                                    <option value="">Search country...</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Account Category</label>
                                <select name="account_id" id="account_category" class="form-control select2_demo_2" required>
                                    <option value="">-- Select Category --</option>

                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" data-code="{{ $acc->SubCode }}"
                                            data-name="{{ $acc->SubDescription }}">
                                            {{ $acc->SubCode }} - {{ $acc->SubDescription }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ================= ROW 4: COMPANY STRUCTURE ================= --}}

                            <div class="col-md-4">
                                <label>Company *</label>
                                <select name="company_id" id="company" class="form-control select2_demo_2" required>
                                    <option value="">Select Company</option>

                                    @foreach ($companies as $c)
                                        <option value="{{ $c->id }}" data-code="{{ $c->company_code }}"
                                            data-name="{{ $c->company_name }}">
                                            {{ $c->company_code }} - {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Business *</label>
                                <select name="comp_unit_id" id="business" class="form-control select2_demo_2" required>
                                    <option value="">Select Business</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Work Point *</label>
                                <select name="work_point_id" id="work_point" class="form-control select2_demo_2" required>
                                    <option value="">-- Select Location --</option>
                                </select>
                            </div>

                            {{-- ================= ROW 5: DETAILS ================= --}}

                            <div class="col-md-6">
                                <label>Address</label>
                                <textarea name="address" class="form-control"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label>Description</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                            </div>

                        </div>

                        <input type="hidden" name="account_code" id="account_code">
                        <input type="hidden" name="account_name" id="account_name">

                        <button type="submit" class="btn btn-success mt-3">
                            <i class="fa fa-save"></i> Save Customer
                        </button>
                    </form>
                </div>
            </div>

            <div class="mb-3 text-right">
                <a href="{{ route('sales.customers.export.excel') }}" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
            </div>
        @endcan

        {{-- ================= CUSTOMER LIST ================= --}}
        @can('View-Customers')
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Customer List</h5>
                </div>

                <div class="ibox-content">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer Code</th>
                                <th>Customer Name</th>
                                <th>Company Code</th>
                                <th>Company Name</th>
                                <th>Business Code</th>
                                <th>Business Name</th>
                                <th>Location Code</th>
                                <th>Location Name</th>
                                <th>Address</th>
                                <th>Description</th>
                                <th>TIN</th>
                                <th>Country</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($customers as $k => $c)
                                <tr>
                                    <td>{{ $k + 1 }}</td>

                                    <td>
                                        <span class="label label-info">
                                            {{ $c->customer_code ?? '-' }}
                                        </span>
                                    </td>

                                    <td style="font-weight:600;">
                                        {{ $c->customer_name }}
                                    </td>

                                    <td>{{ $c->company_code ?? '-' }}</td>
                                    <td>{{ $c->company_name ?? '-' }}</td>

                                    <td>{{ $c->business_code ?? '-' }}</td>
                                    <td>{{ $c->business_name ?? '-' }}</td>

                                    <td>{{ $c->location_code ?? '-' }}</td>
                                    <td>{{ $c->location_name ?? '-' }}</td>

                                    <td>{{ $c->address ?? '-' }}</td>
                                    <td>{{ $c->description ?? '-' }}</td>
                                    <td>{{ $c->tin_number ?? '-' }}</td>
                                    <td>{{ $c->country ?? '-' }}</td>
                                    <td>{{ $c->phone ?? '-' }}</td>

                                    <td>
                                        @if ($c->status === 'Active')
                                            <span class="label label-success">Active</span>
                                        @else
                                            <span class="label label-danger">Inactive</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="btn-group">

                                            @can('Edit-Customers')
                                                <a href="{{ route('sales.customers.edit', ['encryptedId' => Crypt::encryptString($c->id)]) }}"
                                                    class="btn btn-xs btn-primary">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('Delete-Customers')
                                                <form
                                                    action="{{ route('sales.customers.delete', ['encryptedId' => Crypt::encryptString($c->id)]) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-xs btn-warning">
                                                        <i class="fa fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center text-muted">
                                        No customers available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        @endcan

    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const phoneInput = document.getElementById('phone');
            let iti = null;

            const businessUnitsUrl = "{{ route('sales.ajax.business.units', ':companyId') }}";
            const workPointsUrl = "{{ route('sales.ajax.work.points', ':unitId') }}";

            if (phoneInput && window.intlTelInput) {
                iti = window.intlTelInput(phoneInput, {
                    initialCountry: "tz",
                    separateDialCode: true,
                    nationalMode: false,
                    preferredCountries: ["tz", "ke", "ug", "rw"],
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
                });
            }

            const countries = [
                "TZ", "KE", "UG", "RW", "BI", "CD", "ZM", "MW", "ZA", "NG", "GH", "ET", "SO", "SD",
                "EG", "DZ", "MA", "TN", "ZW", "BW", "NA", "LS", "SZ", "GM", "SN", "ML", "NE", "TD",
                "CM", "GA", "CI", "DJ", "ER", "LY", "CF", "KM"
            ];

            const countryNames = {
                TZ: "Tanzania",
                KE: "Kenya",
                UG: "Uganda",
                RW: "Rwanda",
                BI: "Burundi",
                CD: "DR Congo",
                ZM: "Zambia",
                MW: "Malawi",
                ZA: "South Africa",
                NG: "Nigeria",
                GH: "Ghana",
                ET: "Ethiopia",
                SO: "Somalia",
                SD: "Sudan",
                EG: "Egypt",
                DZ: "Algeria",
                MA: "Morocco",
                TN: "Tunisia",
                ZW: "Zimbabwe",
                BW: "Botswana",
                NA: "Namibia",
                LS: "Lesotho",
                SZ: "Eswatini",
                GM: "Gambia",
                SN: "Senegal",
                ML: "Mali",
                NE: "Niger",
                TD: "Chad",
                CM: "Cameroon",
                GA: "Gabon",
                CI: "Ivory Coast",
                DJ: "Djibouti",
                ER: "Eritrea",
                LY: "Libya",
                CF: "Central African Republic",
                KM: "Comoros"
            };

            countries.forEach(code => {
                let name = countryNames[code] || code;

                if ($('#destination option[value="' + code + '"]').length === 0) {
                    $('#destination').append(new Option(name, code));
                }
            });

            $('#company').on('change', function() {

                let companyId = $(this).val();

                $('#business')
                    .empty()
                    .append('<option value="">Loading...</option>');

                $('#work_point')
                    .empty()
                    .append('<option value="">-- Select Location --</option>');

                if (!companyId) {
                    $('#business')
                        .empty()
                        .append('<option value="">Select Business</option>');
                    return;
                }

                let url = businessUnitsUrl.replace(':companyId', companyId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        $('#business')
                            .empty()
                            .append('<option value="">Select Business</option>');

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

                        $('#business').trigger('change');
                    });
            });

            $('#business').on('change', function() {

                let unitId = $(this).val();

                $('#work_point')
                    .empty()
                    .append('<option value="">Loading...</option>');

                if (!unitId) {
                    $('#work_point')
                        .empty()
                        .append('<option value="">-- Select Location --</option>');
                    return;
                }

                let url = workPointsUrl.replace(':unitId', unitId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        $('#work_point')
                            .empty()
                            .append('<option value="">-- Select Location --</option>');

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
                    });
            });

            $('#account_category').on('change', function() {
                let selected = $(this).find(':selected');

                $('#account_code').val(selected.data('code') || '');
                $('#account_name').val(selected.data('name') || '');
            });


            $('#country').on('change', function() {
                let countryCode = $(this).val();

                if (countryCode && iti) {
                    iti.setCountry(countryCode.toLowerCase());
                }
            });

            $('form').on('submit', function() {
                if (iti && phoneInput) {

                    let fullPhone = iti.getNumber();

                    if (fullPhone) {
                        phoneInput.value = fullPhone;
                    }

                }
            });

        });
    </script>
@endsection
