<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MBOGO INFO APP+ — System</title>
    <link rel="shortcut icon" href="{{ asset('icon1.png') }}" />
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/iCheck/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/colorpicker/bootstrap-colorpicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/cropper/cropper.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/switchery/switchery.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/nouslider/jquery.nouislider.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/datapicker/datepicker3.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/ionRangeSlider/ion.rangeSlider.css') }}" rel="stylesheet">
   <link href="{{ asset('css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/clockpicker/clockpicker.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/select2/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/select2/select2-bootstrap4.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('select2/select2.css') }}">

    <link href="{{ asset('css/plugins/touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/dualListbox/bootstrap-duallistbox.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom-confirm.css') }}">
    <link rel="stylesheet" href="{{ asset('Handover.css') }}">
    <script src="{{ asset('js/custom-confirm.js') }}"></script>
    <script src="{{ asset('js/loader.js') }}"></script>
    <style>
        .kmodal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .kmodal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            height: 35%;
            text-align: center;
            border-radius: 15px;
            font-size: 15px;
            font-weight: bold;
        }

        .kmodal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 25%;
        }

        .kmodal-buttons button {
            width: 30%;
            border-radius: 15px;
        }

        body {
            overflow: hidden;
            /* This will completely hide the scrollbar */
        }


        /* This rule makes the modal background cover the full screen */
        .modal-fullscreen {
            width: 100vw;
            height: 100vh;
            max-width: none;
            margin: 0;
            padding: 0;
            top: 0;
            left: 0;
        }

        /* This rule makes the modal dialog fill the entire modal */
        .modal-fullscreen .modal-dialog {
            width: 100%;
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* This rule ensures the image itself fits within the full screen dialog */
        .modal-fullscreen .modal-body {
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-fullscreen .modal-body img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            /* This is crucial for maintaining aspect ratio */
        }
    </style>
    <style>
        @media print {
            .transaction-page {
                page-break-after: always;
            }

            .transaction-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    <script src="{{ asset('js/chart.min.js') }}"></script>
    {{-- {{ GoogleTranslate::trans('',app()->getLocale()) }} --}}
    @include('sweetalert::alert')
    <div id="customConfirmModal" class="kmodal">
        <div class="kmodal-content">
            <p>Before we proceed, do you want to confirm this action?</p>
            <div class="kmodal-buttons">
                <button id="confirmNo" class="btn btn-danger">No</button>
                <button id="confirmYes" class="btn btn-success">Yes</button>
            </div>
        </div>
    </div>
    <div id="wrapper">
        <nav id="sidebar" class="navbar-default navbar-static-side" role="navigation"
            style="position: fixed; left: 0; width: 250px; height: calc(100vh - 0px); overflow-y: auto; z-index: 100; transition: width 0.3s;">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element">
                            @if (Auth()->user()->image == '')
                            @else
                                <img style="height: 2.7cm; width:2.7cm; align-items: center" alt="image"
                                    class="rounded-circle" src="storage/{{ Auth()->user()->image }}" />
                            @endif
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="block m-t-xs font-bold">{{ Auth()->user()->name }}</span>
                                <span class="text-muted text-xs block">{{ Auth()->user()->role }} <b
                                        class="caret"></b></span>
                            </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('profile') }}">Profile</a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('change-password') }}">Change Password</a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
                            </ul>
                        </div>
                        <div class="logo-element">
                            APP+
                        </div>
                    </li>
                    @can('Microfinancing-Modules')
                        <li>
                            <a href="{{ route('microfinancing') }}">
                                <i class="fa fa-th-large"></i>
                                <span class="nav-label">Dashboard</span>
                            </a>
                        </li>
                    @endcan
                    @can('View-Microfinancing-Menu')
                        <li>
                            <a href="#"><i class="fa fa-usd"></i> <span class="nav-label">Microfinancing Menu</span>
                                <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                @can('View-BankNetwork')
                                    <li><a href="{{ route('micro.bank_networks.index') }}"><i class="fa fa-home"></i>Banks &
                                            Networks</a></li>
                                @endcan
                                @can('View-Microfinancing-Transaction')
                                    <li><a href="{{ route('micro.transactions.index') }}"><i
                                                class="fa fa-money"></i>Transaction Details</a></li>
                                @endcan
                                @can('View-Microfinancing-Reports')
                                    <li><a href="{{ route('micro.reports.daily') }}"><i class="fa fa-book"></i>Daily Report
                                            Details</a></li>
                                    <li><a href="{{ route('micro.reports.bn') }}"><i class="fa fa-book"></i>Bank/Network
                                            Report</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                    <li>
                        <a href="{{ route('change-password') }}"><i class="fa fa-key"></i> <span
                                class="nav-label">Change Password</span></a>
                    </li>
                    <li>
                        <a href="{{ route('logout') }}"><i class="fa fa-sign-out"></i> <span
                                class="nav-label">Logout</span></a>
                    </li>
                </ul>
            </div>
        </nav>
        <div id="page-wrapper" class="gray-bg" style="margin-left: 250px;">
            <div class="row border-bottom">
                <nav id="top-navbar" class="navbar navbar-static-top white-bg" role="navigation"
                    style="position: fixed; top: 0; left: 250px; width: calc(100vw - 250px); z-index: 101; height: 56px; box-sizing: border-box; background-color: #1c84c6;">
                    <div class="navbar-header">
                        <a id="minimize-btn" class="navbar-minimalize minimalize-styl-2 btn btn-primary"
                            href="#" style="margin-left: 10px;">
                            <i class="fa fa-bars"></i>
                        </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li>
                            <span class="m-r-sm welcome-message"
                                style="color:#000;font-size:25px;font-weight:bold">Microfinancing Module</span>
                        </li>
                        <li>
                            <select id="move-module" class="form-control" aria-label="Move to another module"
                                onchange="if (this.value) navigateTo(this.value)">
                                <option value="">Move To Another Module</option>
                                @can('Administration-Modules')
                                    @unless (request()->routeIs('business-admin'))
                                        <option value="{{ route('business-admin') }}">Business Administration</option>
                                    @endunless
                                @endcan
                                @can('HR-Modules')
                                    @unless (request()->routeIs('hr'))
                                        <option value="{{ route('hr') }}">Human Resources</option>
                                    @endunless
                                @endcan
                                @can('CRM-Modules')
                                    @unless (request()->routeIs('crm'))
                                        <option value="{{ route('crm') }}">CRM & Supplies</option>
                                    @endunless
                                @endcan
                                @can('Inventory-Manufacturing-Modules')
                                    @unless (request()->routeIs('manufacturing'))
                                        <option value="{{ route('manufacturing') }}">Inventory & Manufacturing</option>
                                    @endunless
                                @endcan
                                @can('SalesMarketing-Modules')
                                    @unless (request()->routeIs('sales management'))
                                        <option value="{{ route('sales.management.dashboard')}}">Sales & Marketing</option>
                                    @endunless
                                @endcan
                                @can('Accounting-Modules')
                                    @unless (request()->routeIs('accounting'))
                                        <option value="{{ route('accounting') }}">Accounting</option>
                                    @endunless
                                @endcan
                                @can('Auditing-Modules')
                                    @unless (request()->routeIs('auditing'))
                                        <option value="{{ route('auditing') }}">Auditing</option>
                                    @endunless
                                @endcan
                                @can('QualityAssurance-Modules')
                                    @unless (request()->routeIs('quality-assurance'))
                                        <option value="{{ route('quality-assurance') }}">Quality Assurance</option>
                                    @endunless
                                @endcan
                                @can('Microfinancing-Modules')
                                    @unless (request()->routeIs('microfinancing'))
                                        <option value="{{ route('microfinancing') }}">Microfinancing</option>
                                    @endunless
                                @endcan
                                @can('Consultancy-Modules')
                                    @unless (request()->routeIs('consultancy'))
                                        <option value="{{ route('consultancy') }}">Consultancy</option>
                                    @endunless
                                @endcan
                                @can('Requisition-Modules')
                                    @unless (request()->routeIs('requisition'))
                                        <option value="{{ route('requisition') }}">Requisition & Approvals</option>
                                    @endunless
                                @endcan
                                @can('Reporting-Modules')
                                    @unless (request()->routeIs('reporting'))
                                        <option value="{{ route('reporting') }}">Reporting</option>
                                    @endunless
                                @endcan
                            </select>
                        </li>
                        {{-- <li>
                            <span class="m-r-sm welcome-message" style="color:#fff;font-size:25px;font-weight:bold">Welcome to
                                Mbogo Info App+ System</span>
                        </li>
                        <li>
                            <a href="{{ route('logout') }}" style="font-size:20px;font-weight:bold">
                                <i class="fa fa-sign-out"></i> Logout
                            </a>
                        </li> --}}
                    </ul>
                </nav>
            </div>
            <div class="wrapper wrapper-content" style="margin-top:60px;height:calc(100vh - 60px);overflow-y:auto;">

                @yield('content')
            </div>

            <div class="footer bg-success">
                <div class="row col-md-12">
                    <div class="float-left col-md-6">
                        <strong>Copyright</strong> &copy; {{ date('Y') }} MBOGO INFO
                        <strong>APP+</strong> System
                    </div>
                    <div class="float-right col-md-6">
                        <span class="float-right">Developed and Maintained By Eng. Kivuyo</span>
                    </div>
                </div>
            </div>
            <!-- Mainly scripts  loader.js-->
            <script src="{{ asset('js/jquery-3.1.1.min.js') }}"></script>
            <script src="{{ asset('js/popper.min.js') }}"></script>
            <script src="{{ asset('js/bootstrap.js') }}"></script>
            <script src="{{ asset('js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
            <!-- Flot -->
            <script src="{{ asset('js/plugins/flot/jquery.flot.js') }}"></script>
            <script src="{{ asset('js/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
            <script src="{{ asset('js/plugins/flot/jquery.flot.spline.js') }}"></script>
            <script src="{{ asset('js/plugins/flot/jquery.flot.resize.js') }}"></script>
            <script src="{{ asset('js/plugins/flot/jquery.flot.pie.js') }}"></script>
            <script src="{{ asset('js/plugins/flot/jquery.flot.symbol.js') }}"></script>
            <script src="{{ asset('js/plugins/flot/jquery.flot.time.js') }}"></script>
            <!-- Peity -->
            <script src="{{ asset('js/plugins/peity/jquery.peity.min.js') }}"></script>
            <script src="{{ asset('js/demo/peity-demo.js') }}"></script>
            <!-- Custom and plugin javascript -->
            <script src="{{ asset('js/inspinia.js') }}"></script>
            <script src="{{ asset('js/plugins/pace/pace.min.js') }}"></script>
            <!-- jQuery UI -->
            <script src="{{ asset('js/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
            <!-- Jvectormap -->
            <script src="{{ asset('js/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
            <script src="{{ asset('js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
            <!-- EayPIE -->
            <script src="{{ asset('js/plugins/easypiechart/jquery.easypiechart.js') }}"></script>
            <!-- Sparkline -->
            <script src="{{ asset('js/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
            <script src="{{ asset('js/plugins/dataTables/datatables.min.js') }}"></script>
            <!-- Sparkline demo data  -->
            <script src="{{ asset('js/demo/sparkline-demo.js') }}"></script>
            <!-- JSKnob -->
            <script src="{{ asset('js/plugins/jsKnob/jquery.knob.js') }}"></script>
            <!-- Input Mask-->
            <script src="{{ asset('js/plugins/jqueryMask/jquery.mask.min.js') }}"></script>
            <!-- Data picker -->
            <script src="{{ asset('js/plugins/datapicker/bootstrap-datepicker.js') }}"></script>
            <!-- NouSlider -->
            <script src="{{ asset('js/plugins/nouslider/jquery.nouislider.min.js') }}"></script>
            <!-- Switchery -->
            <script src="{{ asset('js/plugins/switchery/switchery.js') }}"></script>
            <!-- IonRangeSlider -->
            <script src="{{ asset('js/plugins/ionRangeSlider/ion.rangeSlider.min.js') }}"></script>
            <!-- iCheck -->
            <script src="{{ asset('js/plugins/iCheck/icheck.min.js') }}"></script>
            <!-- MENU -->
            <script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
            <!-- Color picker -->
            <script src="{{ asset('js/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>
            <!-- Clock picker -->
            <script src="{{ asset('js/plugins/clockpicker/clockpicker.js') }}"></script>
            <!-- Image cropper -->
            <script src="{{ asset('js/plugins/cropper/cropper.min.js') }}"></script>
            <!-- Date range use moment.js same as full calendar plugin -->
            <script src="{{ asset('js/plugins/fullcalendar/moment.min.js') }}"></script>
            <!-- Date range picker -->
            <script src="{{ asset('js/plugins/daterangepicker/daterangepicker.js') }}"></script>
            <!-- Select2 -->
            <script src="{{ asset('js/plugins/select2/select2.full.min.js') }}"></script>
            {{-- <script src="select2/select2.min.js"></script> --}}
            <!-- TouchSpin -->
            <script src="{{ asset('js/plugins/touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
            <!-- Tags Input -->
            <script src="{{ asset('js/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
            <!-- Dual Listbox -->
            <script src="{{ asset('js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
            <script>
                function printReceipt(el) {
                    var restorepage = document.body.innerHTML;
                    var printContent = document.getElementById(el).innerHTML;
                    var footer = document.getElementById('total-footer');
                    if (footer) footer.style.display = 'none';
                    document.body.innerHTML = printContent;
                    if (footer) {
                        document.body.innerHTML += footer.outerHTML;
                        document.getElementById('total-footer').style.display = 'block';
                    }
                    window.print();
                    document.body.innerHTML = restorepage;
                    if (footer) footer.style.display = 'block';
                }
            </script>
            <style>
                .bottom-content {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    color: #0408ec;
                    text-align: center;
                    background-color: white;
                    /* Optional: Adjust background color */
                    padding: 10px;
                    /* Optional: Add padding for better visibility */
                    border-top: 1px solid #ddd;
                    /* Optional: Add a border at the top for separation */
                }
            </style>
            <script>
                function navigateTo(url) {
                    if (!url) return;
                    window.location.href = url;
                }

                $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn btn-white btn-sm';
                $(document).ready(function() {
                    if ($.fn.DataTable.isDataTable('.dataTables-example')) {
                        $('.dataTables-example').DataTable().clear().destroy();
                    }
                    var table = $('.dataTables-example').DataTable({
                        pageLength: 25,
                        autoWidth: false,
                        responsive: true,
                        paging: true,
                        sScrollX: true,
                        dom: '<"html5buttons"B>lTfgitp',
                        buttons: [{
                            extend: 'copy'
                        }, {
                            extend: 'csv'
                        }, {
                            extend: 'excel',
                            title: 'MBOGO INFO APP+ Excel'
                        }, {
                            extend: 'pdf',
                            title: 'MBOGO INFO APP+ Pdf',
                            orientation: 'landscape',
                            messageTop: 'This PDF Was For MBOGO INFO  Use Only.',
                        }, {
                            extend: 'print',
                            title: 'Meksoft Documents',
                            messageBottom: 'Meksoft Document',
                            customize: function(win) {
                                $(win.document.body).addClass('white-bg');
                                $(win.document.body).css('font-size', '10px');
                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                                $(win.document.body).find('#total-footer').hide();
                                var totalRows = $(win.document.body).find('table tbody tr').length;
                                var rowsPerPage = table.page.len(); // The number of rows per page
                                var totalPages = Math.ceil(totalRows / rowsPerPage);
                                $(win.document.body).find('table tbody tr').each(function(index) {
                                    if (index >= (totalRows - rowsPerPage)) {
                                        $(this).after($(win.document.body).find('#total-footer')
                                            .show());
                                    }
                                });
                            }
                        }],
                        order: [],
                    });
                    table.on('draw', function() {
                        var pageInfo = table.page.info();
                        if (pageInfo.page === pageInfo.pages - 1) {
                            $('#total-footer').show();
                        } else {
                            $('#total-footer').hide();
                        }
                    });
                    var pageInfo = table.page.info();
                    if (pageInfo.page !== pageInfo.pages - 1) {
                        $('#total-footer').hide();
                    }
                    $('.navbar-minimalize').on('click', function() {
                        setTimeout(function() {
                            table.columns.adjust().responsive.recalc();
                            table.draw();
                        }, 500);
                    });
                    $(window).on('resize', function() {
                        table.columns.adjust().responsive.recalc();
                    });
                });

                function exportTableToExcel(tableID, filename = '') {
                    var downloadLink;
                    var dataType = 'application/vnd.ms-excel';
                    var tableSelect = document.getElementById(tableID);
                    var tableHTML = '<html><head><style>' +
                        'table { border-collapse: collapse; width: 100%; }' +
                        'th, td { border: 1px solid black; padding: 8px; text-align: left; }' +
                        '</style></head><body>' +
                        tableSelect.outerHTML +
                        '</body></html>';
                    var dataURI = 'data:' + dataType + ', ' + encodeURIComponent(tableHTML);
                    filename = filename ? filename + '.xls' : 'excel_data.xls';
                    downloadLink = document.createElement("a");
                    document.body.appendChild(downloadLink);
                    if (navigator.msSaveOrOpenBlob) {
                        var blob = new Blob(['\ufeff', tableHTML], {
                            type: dataType
                        });
                        navigator.msSaveOrOpenBlob(blob, filename);
                    } else {
                        downloadLink.href = dataURI;
                        downloadLink.download = filename;
                        downloadLink.click();
                    }
                }
            </script>
            <script>
                $(document).ready(function() {
                    $(".select2_demo_1").select2({
                        theme: 'bootstrap4',
                    });
                    $(".select2_demo_2").select2({
                        theme: 'bootstrap4',
                    });
                    $(".touchspin1").TouchSpin({
                        buttondown_class: 'btn btn-white',
                        buttonup_class: 'btn btn-white'
                    });
                    $(".select2_demo_3").select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#varyModal')
                    });
                    $(".select2_demo_4").select2({
                        width: '100%',
                        theme: 'bootstrap4',
                        dropdownParent: $('#varyModal1')
                    });
                    $(".select2_demo_5").select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#varyModal2')
                    });
                    $(".select2_demo_6").select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#varyModal3')
                    });
                });
            </script>
            <script>
                $('input.number').keyup(function(event) {
                    if (event.which >= 37 && event.which <= 40) return;
                    $(this).val(function(index, value) {
                        return value
                            .replace(/\D/g, "")
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    });
                });
            </script>
            <script type="text/javascript">
                function autoRefreshContent() {
                    $.ajax({
                        url: window.location.href,
                        type: 'GET',
                        success: function(data) {
                            var newContent = $(data).find('#contentWrapper').html();
                            $('#contentWrapper').html(newContent);
                        },
                        error: function(xhr, status, error) {
                            console.error(status + ": " + error);
                        }
                    });
                }
                setInterval(autoRefreshContent, 150000);
                var blinkElements = document.querySelectorAll('.blink');
                setInterval(function() {
                    blinkElements.forEach(function(blink) {
                        blink.style.opacity = (blink.style.opacity == 0 ? 1 : 0);
                    });
                }, 500);

                function showCustomConfirm(callback) {
                    var modal = document.getElementById('customConfirmModal');
                    var confirmYes = document.getElementById('confirmYes');
                    var confirmNo = document.getElementById('confirmNo');
                    modal.style.display = 'block';
                    confirmYes.onclick = function() {
                        modal.style.display = 'none';
                        callback(true);
                    };
                    confirmNo.onclick = function() {
                        modal.style.display = 'none';
                        callback(false);
                    };
                }

                function handleConfirmSubmit(formId) {
                    event.preventDefault();
                    var formElement = document.getElementById(formId);
                    showCustomConfirm(function(confirmed) {
                        if (confirmed) {
                            formElement.submit();
                        }
                    });
                }
            </script>
            <script>
                $(document).ready(function() {
                    function ExtSlp(id) {
                        $('#Ext_no').val(id);
                    }
                    window.ExtSlp = ExtSlp;
                });
                $(document).ready(function() {
                    function EntSlp(id) {
                        $('#Ent_no').val(id);
                    }
                    window.EntSlp = EntSlp;
                });
            </script>
            <script>
                document.getElementById('minimize-btn').addEventListener('click', function() {
                    var sidebar = document.getElementById('sidebar');
                    var pageWrapper = document.getElementById('page-wrapper');
                    var topNavbar = document.getElementById('top-navbar');

                    if (sidebar.style.width === '250px' || sidebar.style.width === '') {
                        // Minimize the sidebar
                        sidebar.style.width = '70px';
                        pageWrapper.style.marginLeft = '70px';
                        topNavbar.style.left = '70px'; // Adjust the top navbar
                        topNavbar.style.width = 'calc(100vw - 70px)';
                    } else {
                        // Maximize the sidebar
                        sidebar.style.width = '250px';
                        pageWrapper.style.marginLeft = '250px';
                        topNavbar.style.left = '250px'; // Adjust the top navbar
                        topNavbar.style.width = 'calc(100vw - 250px)';
                    }
                });
            </script>
            <!-- JavaScript for filtering table rows -->
            <script>
                document.getElementById('tableSearch').addEventListener('input', function() {
                    let searchValue = this.value.toLowerCase();
                    let table = document.getElementById('form2');
                    let rows = table.getElementsByTagName('tr');
                    requestAnimationFrame(() => {
                        for (let i = 1; i < rows.length; i++) {
                            let row = rows[i];
                            let cells = row.getElementsByTagName('td');
                            let rowText = '';
                            for (let j = 0; j < cells.length; j++) {
                                rowText += cells[j].textContent.toLowerCase();
                                if (rowText.includes(searchValue)) break; // Stop checking once found
                            }
                            row.style.display = rowText.includes(searchValue) ? 'table-row' : 'none';
                        }
                        adjustRowspan(table);
                    });
                });

                function adjustRowspan(table) {
                    let rows = table.getElementsByTagName('tr');
                    for (let i = 1; i < rows.length; i++) {
                        let row = rows[i];
                        let cells = row.getElementsByTagName('td');
                        for (let j = 0; j < cells.length; j++) {
                            if (cells[j].rowSpan > 1) {
                                let rowspan = cells[j].rowSpan;
                                for (let k = 1; k < rowspan; k++) {
                                    if (rows[i + k]) {
                                        rows[i + k].style.display = row.style.display;
                                    }
                                }
                            }
                        }
                    }
                }
            </script>
</body>

</html>
