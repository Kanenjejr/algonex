@php
    use Illuminate\Support\Facades\Crypt;
@endphp

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

                    <li>
                        <a href="{{ route('customers.index') }}">Customer</a>
                    </li>

                    <span style="font-size:22px" class="fa fa-angle-double-right"></span>

                    <li class="breadcrumb-item active">
                        <strong>Edit Customer</strong>
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

        {{-- ================= EDIT CUSTOMER ================= --}}
        <div class="ibox mt-3">
            <div class="ibox-title bg-primary">
                <h5>Edit Customer</h5>
            </div>

            <div class="ibox-content">

                <form action="{{ route('sales.customers.update', ['encryptedId' => $encryptedId]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- ================= ROW 1: CUSTOMER BASIC INFO ================= --}}

                        <div class="col-md-4">
                            <label>Customer *</label>
                            <select id="customer" name="customer_id" class="form-control select2_demo_2" required>
                                <option value="">Search Customer...</option>

                                @foreach ($customerAccounts as $c)
                                    <option value="{{ $c->id }}" data-code="{{ $c->SubCode }}"
                                        data-name="{{ $c->SubDescription }}"
                                        {{ old('customer_id', $customer->account_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->SubCode }} - {{ $c->SubDescription }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Customer Code</label>
                            <input type="text" id="customer_code" name="customer_code"
                                value="{{ old('customer_code', $customer->customer_code) }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Customer Name</label>
                            <input type="text" id="customer_name" name="customer_name"
                                value="{{ old('customer_name', $customer->customer_name) }}" class="form-control">
                        </div>

                        {{-- ================= ROW 2: CONTACT INFO ================= --}}

                        <div class="col-md-4">
                            <label>Phone</label>
                            <input type="tel" name="phone" id="phone"
                                value="{{ old('phone', $customer->phone) }}" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>TIN Number</label>
                            <input type="text" name="tin_number" id="tin_number"
                                value="{{ old('tin_number', $customer->tin_number) }}" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control select2_demo_2">
                                <option value="Active"
                                    {{ old('status', $customer->status) == 'Active' ? 'selected' : '' }}>
                                    Active
                                </option>

                                <option value="Inactive"
                                    {{ old('status', $customer->status) == 'Inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        {{-- ================= ROW 3: COUNTRY / ACCOUNT ================= --}}

                        <div class="col-md-4">
                            <label>Country</label>
                            <select name="country" id="country" class="form-control select2_demo_2">
                                <option value="">Search country...</option>

                                <option value="TZ" {{ old('country', $customer->country) == 'TZ' ? 'selected' : '' }}>
                                    Tanzania</option>
                                <option value="KE" {{ old('country', $customer->country) == 'KE' ? 'selected' : '' }}>
                                    Kenya</option>
                                <option value="UG" {{ old('country', $customer->country) == 'UG' ? 'selected' : '' }}>
                                    Uganda</option>
                                <option value="RW" {{ old('country', $customer->country) == 'RW' ? 'selected' : '' }}>
                                    Rwanda</option>
                                <option value="BI" {{ old('country', $customer->country) == 'BI' ? 'selected' : '' }}>
                                    Burundi</option>
                                <option value="CD" {{ old('country', $customer->country) == 'CD' ? 'selected' : '' }}>
                                    DR Congo</option>
                                <option value="ZM" {{ old('country', $customer->country) == 'ZM' ? 'selected' : '' }}>
                                    Zambia</option>
                                <option value="MW" {{ old('country', $customer->country) == 'MW' ? 'selected' : '' }}>
                                    Malawi</option>
                                <option value="ZA" {{ old('country', $customer->country) == 'ZA' ? 'selected' : '' }}>
                                    South Africa</option>
                                <option value="NG" {{ old('country', $customer->country) == 'NG' ? 'selected' : '' }}>
                                    Nigeria</option>
                                <option value="GH" {{ old('country', $customer->country) == 'GH' ? 'selected' : '' }}>
                                    Ghana</option>
                                <option value="ET" {{ old('country', $customer->country) == 'ET' ? 'selected' : '' }}>
                                    Ethiopia</option>
                                <option value="SD" {{ old('country', $customer->country) == 'SD' ? 'selected' : '' }}>
                                    Sudan</option>
                                <option value="EG" {{ old('country', $customer->country) == 'EG' ? 'selected' : '' }}>
                                    Egypt</option>
                                <option value="DZ" {{ old('country', $customer->country) == 'DZ' ? 'selected' : '' }}>
                                    Algeria</option>
                                <option value="MA" {{ old('country', $customer->country) == 'MA' ? 'selected' : '' }}>
                                    Morocco</option>
                                <option value="TN" {{ old('country', $customer->country) == 'TN' ? 'selected' : '' }}>
                                    Tunisia</option>
                                <option value="SO" {{ old('country', $customer->country) == 'SO' ? 'selected' : '' }}>
                                    Somalia</option>
                                <option value="ZW" {{ old('country', $customer->country) == 'ZW' ? 'selected' : '' }}>
                                    Zimbabwe</option>
                                <option value="BW" {{ old('country', $customer->country) == 'BW' ? 'selected' : '' }}>
                                    Botswana</option>
                                <option value="NA" {{ old('country', $customer->country) == 'NA' ? 'selected' : '' }}>
                                    Namibia</option>
                                <option value="LS" {{ old('country', $customer->country) == 'LS' ? 'selected' : '' }}>
                                    Lesotho</option>
                                <option value="SZ" {{ old('country', $customer->country) == 'SZ' ? 'selected' : '' }}>
                                    Eswatini</option>
                                <option value="GM" {{ old('country', $customer->country) == 'GM' ? 'selected' : '' }}>
                                    Gambia</option>
                                <option value="SN" {{ old('country', $customer->country) == 'SN' ? 'selected' : '' }}>
                                    Senegal</option>
                                <option value="KM" {{ old('country', $customer->country) == 'KM' ? 'selected' : '' }}>
                                    Comoros</option>
                                <option value="ML" {{ old('country', $customer->country) == 'ML' ? 'selected' : '' }}>
                                    Mali</option>
                                <option value="NE" {{ old('country', $customer->country) == 'NE' ? 'selected' : '' }}>
                                    Niger</option>
                                <option value="TD" {{ old('country', $customer->country) == 'TD' ? 'selected' : '' }}>
                                    Chad</option>
                                <option value="CM" {{ old('country', $customer->country) == 'CM' ? 'selected' : '' }}>
                                    Cameroon</option>
                                <option value="GA" {{ old('country', $customer->country) == 'GA' ? 'selected' : '' }}>
                                    Gabon</option>
                                <option value="CI" {{ old('country', $customer->country) == 'CI' ? 'selected' : '' }}>
                                    Ivory Coast</option>
                                <option value="DJ" {{ old('country', $customer->country) == 'DJ' ? 'selected' : '' }}>
                                    Djibouti</option>
                                <option value="ER" {{ old('country', $customer->country) == 'ER' ? 'selected' : '' }}>
                                    Eritrea</option>
                                <option value="LY" {{ old('country', $customer->country) == 'LY' ? 'selected' : '' }}>
                                    Libya</option>
                                <option value="CF" {{ old('country', $customer->country) == 'CF' ? 'selected' : '' }}>
                                    Central African Republic</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Destination Country</label>
                            <select name="destination" id="destination" class="form-control select2_demo_2">
                                <option value="">Search country...</option>

                                <option value="TZ"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'TZ' ? 'selected' : '' }}>
                                    Tanzania</option>
                                <option value="KE"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'KE' ? 'selected' : '' }}>
                                    Kenya</option>
                                <option value="UG"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'UG' ? 'selected' : '' }}>
                                    Uganda</option>
                                <option value="RW"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'RW' ? 'selected' : '' }}>
                                    Rwanda</option>
                                <option value="BI"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'BI' ? 'selected' : '' }}>
                                    Burundi</option>
                                <option value="CD"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'CD' ? 'selected' : '' }}>
                                    DR Congo</option>
                                <option value="ZM"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'ZM' ? 'selected' : '' }}>
                                    Zambia</option>
                                <option value="MW"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'MW' ? 'selected' : '' }}>
                                    Malawi</option>
                                <option value="ZA"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'ZA' ? 'selected' : '' }}>
                                    South Africa</option>
                                <option value="NG"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'NG' ? 'selected' : '' }}>
                                    Nigeria</option>
                                <option value="GH"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'GH' ? 'selected' : '' }}>
                                    Ghana</option>
                                <option value="ET"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'ET' ? 'selected' : '' }}>
                                    Ethiopia</option>
                                <option value="SO"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'SO' ? 'selected' : '' }}>
                                    Somalia</option>
                                <option value="SD"
                                    {{ old('destination', $customer->destination ?? $customer->country) == 'SD' ? 'selected' : '' }}>
                                    Sudan</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Account Category</label>
                            <select name="account_id" id="account_category" class="form-control select2_demo_2">
                                <option value="">-- Select Category --</option>

                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}" data-code="{{ $acc->SubCode }}"
                                        data-name="{{ $acc->SubDescription }}"
                                        {{ old('account_id', $customer->account_id) == $acc->id ? 'selected' : '' }}>
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
                                        data-name="{{ $c->company_name }}"
                                        {{ old('company_id', $customer->company_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->company_code }} - {{ $c->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Business *</label>
                            <select name="comp_unit_id" id="business" class="form-control select2_demo_2" required>
                                <option value="">Select Business</option>

                                @foreach ($businessUnits as $unit)
                                    <option value="{{ $unit->id }}" data-code="{{ $unit->unit_code }}"
                                        data-name="{{ $unit->unit_name }}"
                                        {{ old('comp_unit_id', $customer->comp_unit_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->unit_code }} - {{ $unit->unit_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Work Point *</label>
                            <select name="work_point_id" id="work_point" class="form-control select2_demo_2" required>
                                <option value="">-- Select Location --</option>

                                @foreach ($workPoints as $wp)
                                    <option value="{{ $wp->id }}" data-code="{{ $wp->work_code }}"
                                        data-name="{{ $wp->work_name }}"
                                        {{ old('work_point_id', $customer->work_point_id) == $wp->id ? 'selected' : '' }}>
                                        {{ $wp->work_code }} - {{ $wp->work_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ================= ROW 5: DETAILS ================= --}}

                        <div class="col-md-6">
                            <label>Address</label>
                            <textarea name="address" class="form-control">{{ old('address', $customer->address) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label>Description</label>
                            <textarea name="description" id="description" class="form-control">{{ old('description', $customer->description) }}</textarea>
                        </div>

                    </div>

                    <input type="hidden" name="account_code" id="account_code">
                    <input type="hidden" name="account_name" id="account_name">

                    <button type="submit" class="btn btn-success mt-3">
                        <i class="fa fa-save"></i> Update Customer
                    </button>

                    <a href="{{ route('customers.index') }}" class="btn btn-default mt-3">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </form>
            </div>
        </div>

    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const phoneInput = document.getElementById('phone');
            let iti = null;

            const businessUnitsUrl = "{{ route('sales.ajax.business.units', ':companyId') }}";
            const workPointsUrl = "{{ route('sales.ajax.work.points', ':unitId') }}";

            const selectedBusiness = "{{ old('comp_unit_id', $customer->comp_unit_id) }}";
            const selectedWorkPoint = "{{ old('work_point_id', $customer->work_point_id) }}";

            if (phoneInput && window.intlTelInput) {
                iti = window.intlTelInput(phoneInput, {
                    initialCountry: "{{ strtolower($customer->country ?? 'tz') }}",
                    separateDialCode: true,
                    nationalMode: false,
                    preferredCountries: ["tz", "ke", "ug", "rw"],
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
                });
            }

            $('#customer').on('change', function() {
                let selected = $(this).find(':selected');

                $('#customer_code').val(selected.data('code') || '');
                $('#customer_name').val(selected.data('name') || '');

                if (selected.data('tin')) {
                    $('#tin_number').val(selected.data('tin'));
                }

                if (selected.data('country')) {
                    $('#country').val(selected.data('country')).trigger('change');
                    $('#destination').val(selected.data('country')).trigger('change');
                }
            });

            $('#account_category').on('change', function() {
                let selected = $(this).find(':selected');

                $('#account_code').val(selected.data('code') || '');
                $('#account_name').val(selected.data('name') || '');
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
                            let isSelected = String(b.id) === String(selectedBusiness);

                            $('#business').append(
                                $('<option>', {
                                    value: b.id,
                                    text: `${b.unit_code} - ${b.unit_name}`,
                                    selected: isSelected
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
                            let isSelected = String(w.id) === String(selectedWorkPoint);

                            $('#work_point').append(
                                $('<option>', {
                                    value: w.id,
                                    text: `${w.work_code} - ${w.work_name}`,
                                    selected: isSelected
                                })
                                .attr('data-code', w.work_code)
                                .attr('data-name', w.work_name)
                            );
                        });
                    });
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
