<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MBOGO INFO APP+ — Purchase & Procurement</title>

    <link rel="shortcut icon" href="{{ asset('icon1.png') }}" />

    {{-- CSS --}}
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/select2/select2.min.css') }}" rel="stylesheet">

    {{-- JS --}}
    <script src="{{ asset('js/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>

    <style>

        body{
            background:#f3f6fb;
            overflow-x:hidden;
            font-family:'Segoe UI', sans-serif;
        }

        #wrapper{
            width:100%;
        }

        /* SIDEBAR */

        #sidebar{
            position:fixed;
            top:0;
            left:0;
            width:250px;
            height:100vh;
            background:#1d2b36;
            overflow-y:auto;
            z-index:1001;
            transition:all .3s ease;
        }

        #sidebar .nav-header{
            padding:25px 20px;
            background:#17222c;
        }

        #sidebar .profile-name{
            color:#fff;
            font-size:17px;
            font-weight:700;
            margin-top:10px;
        }

        #sidebar .profile-role{
            color:#b0bec5;
            font-size:12px;
        }

        #side-menu{
            padding:0;
            margin:0;
            list-style:none;
        }

        #side-menu li a{
            display:block;
            padding:14px 20px;
            color:#d6dde5;
            font-size:14px;
            transition:.3s;
        }

        #side-menu li a:hover{
            background:#173A7A;
            color:#fff;
            text-decoration:none;
        }

        #side-menu li.active > a{
            background:#173A7A;
            color:#fff;
        }

        #side-menu .nav-second-level{
            background:#243746;
        }

        #side-menu .nav-second-level li a{
            padding-left:50px;
            font-size:13px;
        }

        #side-menu .nav-third-level{
            background:#2d4354;
        }

        #side-menu .nav-third-level li a{
            padding-left:70px;
        }

        /* PAGE */

        #page-wrapper{
            margin-left:250px;
            width:calc(100% - 250px);
            min-height:100vh;
            transition:all .3s ease;
        }

        /* TOP NAVBAR */

        #top-navbar{
            position:fixed;
            top:0;
            left:250px;
            width:calc(100vw - 250px);
            z-index:1000;
            height:60px;
            background:#173A7A;
            transition:all .3s ease;
            border:none;
        }

        .navbar-header{
            float:left;
        }

        .navbar-minimalize{
            margin-top:10px;
            margin-left:15px;
        }

        .module-title{
            color:#fff;
            font-size:26px;
            font-weight:700;
            line-height:60px;
        }

        .content-wrapper{
            margin-top:70px;
            padding:25px;
        }

        /* FOOTER */

        .footer{
            background:#173A7A;
            color:#fff;
            padding:15px 25px;
        }

        /* CARDS */

        .summary-card{
            border-radius:15px;
            padding:20px;
            color:#fff;
            position:relative;
            overflow:hidden;
            box-shadow:0 5px 18px rgba(0,0,0,0.08);
            transition:.3s;
            margin-bottom:25px;
        }

        .summary-card:hover{
            transform:translateY(-3px);
        }

        .summary-card h2{
            font-size:30px;
            font-weight:700;
            margin:10px 0;
        }

        .summary-card .icon{
            position:absolute;
            right:20px;
            top:20px;
            font-size:45px;
            opacity:.25;
        }

        .bg-primary-gradient{
            background:linear-gradient(135deg,#1d4ed8,#2563eb);
        }

        .bg-success-gradient{
            background:linear-gradient(135deg,#059669,#10b981);
        }

        .bg-warning-gradient{
            background:linear-gradient(135deg,#d97706,#f59e0b);
        }

        .bg-danger-gradient{
            background:linear-gradient(135deg,#dc2626,#ef4444);
        }

        .ibox{
            margin-bottom:25px;
            border:none;
            box-shadow:0 2px 10px rgba(0,0,0,.05);
            border-radius:12px;
            overflow:hidden;
        }

        .ibox-title{
            background:#fff;
            padding:18px 20px;
            border-bottom:1px solid #eef2f7;
        }

        .ibox-title h5{
            margin:0;
            font-size:16px;
            font-weight:700;
            color:#173A7A;
        }

        .ibox-content{
            background:#fff;
            padding:20px;
        }

        .table > thead > tr > th{
            background:#f4f7fb;
            border:none;
            color:#173A7A;
            font-weight:700;
        }

        .table > tbody > tr > td{
            vertical-align:middle;
        }

        .badge-status{
            padding:7px 10px;
            border-radius:20px;
            font-size:11px;
            font-weight:700;
        }

        .badge-approved{
            background:#dcfce7;
            color:#166534;
        }

        .badge-pending{
            background:#fef3c7;
            color:#92400e;
        }

        .badge-rejected{
            background:#fee2e2;
            color:#991b1b;
        }

        .quick-link{
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:14px 18px;
            border-radius:12px;
            background:#f8fafc;
            margin-bottom:12px;
            transition:.3s;
        }

        .quick-link:hover{
            background:#173A7A;
            color:#fff;
            text-decoration:none;
        }

        .quick-link i{
            font-size:18px;
        }

        .quick-link span{
            font-weight:600;
        }

        ::-webkit-scrollbar{
            width:7px;
            height:7px;
        }

        ::-webkit-scrollbar-thumb{
            background:#173A7A;
            border-radius:20px;
        }

    </style>

</head>

<body>

<div id="wrapper">

    {{-- SIDEBAR --}}
    <nav id="sidebar">

        <div class="sidebar-collapse">

            <ul class="nav metismenu" id="side-menu">

                {{-- USER --}}
                <li class="nav-header">

                    <div class="profile-element text-center">

                        @if(Auth::user()->image)

                            <img
                                alt="image"
                                class="rounded-circle"
                                style="width:85px;height:85px;object-fit:cover;"
                                src="{{ asset('storage/' . Auth::user()->image) }}"
                            >

                        @endif

                        <div class="profile-name">
                            {{ Auth::user()->name }}
                        </div>

                        <div class="profile-role">
                            {{ Auth::user()->role }}
                        </div>

                    </div>

                </li>

                {{-- DASHBOARD --}}
                @can('Purchase-Management-Modules')

                <li class="active">

                    <a href="{{ route('purchase.dashboard') }}">

                        <i class="fa fa-dashboard"></i>

                        <span class="nav-label">
                            Dashboard
                        </span>

                    </a>

                </li>

                @endcan

                {{-- PURCHASE --}}
                @can('Purchase-Management-Modules')

                <li>

                    <a href="#">

                        <i class="fa fa-shopping-cart"></i>

                        <span class="nav-label">
                            Purchase & Procurement
                        </span>

                        <span class="fa arrow"></span>

                    </a>

                    <ul class="nav nav-second-level collapse">

                        @can('View-Purchase-Requisition')

                        <li>

                            <a href="{{ url('/admin/reqsts/general-supply/requisition') }}">

                                Purchase Requisitions

                            </a>

                        </li>

                        @endcan

                        @can('View-Purchase-Orders')

                        <li>

                            <a href="{{ route('sales.po.index') }}">

                                Purchase Orders

                            </a>

                        </li>

                        @endcan

                        @can('Receive-Goods')

                        <li>

                            <a href="{{ route('sales.gs.received.index') }}">

                                Goods Receipt Notes

                            </a>

                        </li>

                        @endcan

                        @can('View-Purchase-Invoices')

                        <li>

                            <a href="{{ route('purchasing.invoices') }}">

                                Purchase Invoices

                            </a>

                        </li>

                        @endcan

                        @can('View-Vendors')

                        <li>

                            <a href="{{ route('sales.vendors.index') }}">

                                Supplier Management

                            </a>

                        </li>

                        @endcan

                    </ul>

                </li>

                @endcan

                {{-- INVENTORY --}}
                @can('View-Stock-Management')

                <li>

                    <a href="#">

                        <i class="fa fa-database"></i>

                        <span class="nav-label">
                            Inventory & Warehouse
                        </span>

                        <span class="fa arrow"></span>

                    </a>

                    <ul class="nav nav-second-level collapse">

                        <li>

                            <a href="{{ route('stock.management.dashboard') }}">

                                Inventory Dashboard

                            </a>

                        </li>

                        <li>

                            <a href="{{ route('stock.management.module','ledger') }}">

                                Stock Ledger

                            </a>

                        </li>

                        <li>

                            <a href="{{ route('stock.management.module','movement') }}">

                                Stock Movement

                            </a>

                        </li>

                    </ul>

                </li>

                @endcan

                {{-- AUDIT --}}
                @can('View-Stock-Audits')

                <li>

                    <a href="#">

                        <i class="fa fa-shield"></i>

                        <span class="nav-label">
                            Audits & Compliance
                        </span>

                        <span class="fa arrow"></span>

                    </a>

                    <ul class="nav nav-second-level collapse">

                        <li>

                            <a href="{{ route('sales.stock.audit.index') }}">

                                Stock Audits

                            </a>

                        </li>

                        <li>

                            <a href="{{ route('sales.loss.index') }}">

                                Loss Prevention

                            </a>

                        </li>

                    </ul>

                </li>

                @endcan

                {{-- SETTINGS --}}
                <li>

                    <a href="{{ route('change-password') }}">

                        <i class="fa fa-key"></i>

                        <span class="nav-label">
                            Change Password
                        </span>

                    </a>

                </li>

                <li>

                    <a href="{{ route('logout') }}">

                        <i class="fa fa-sign-out"></i>

                        <span class="nav-label">
                            Logout
                        </span>

                    </a>

                </li>

            </ul>

        </div>

    </nav>

    {{-- PAGE --}}
    <div id="page-wrapper" class="gray-bg">

        {{-- TOP NAVBAR --}}
        <div class="row border-bottom">

            <nav id="top-navbar"
                 class="navbar navbar-static-top white-bg"
                 role="navigation">

                <div class="navbar-header">

                    <a id="minimize-btn"
                       class="navbar-minimalize minimalize-styl-2 btn btn-primary"
                       href="#">

                        <i class="fa fa-bars"></i>

                    </a>

                </div>

                <ul class="nav navbar-top-links navbar-right">

                    <li>

                        <span class="module-title">

                            Purchase & Procurement Module

                        </span>

                    </li>

                    <li style="padding-top:10px;padding-right:20px;">

                        <select
                            id="move-module"
                            class="form-control"
                            style="width:320px;"
                            onchange="navigateTo(this.value)"
                        >

                            <option value="">
                                Move To Another Module
                            </option>

                            @can('Administration-Modules')

                            <option value="{{ route('business-admin') }}">

                                Finance & Administration

                            </option>

                            @endcan

                            @can('Business-Development-Sales-Marketing-Modules')

                            <option value="{{ route('sales.management.dashboard') }}">

                                Business Development & Sales

                            </option>

                            @endcan

                            @can('Production-Inventory-Manufacturing-Modules')

                            <option value="{{ route('manufacturing') }}">

                                Production & Manufacturing

                            </option>

                            @endcan

                            @can('Requisition-Modules')

                            <option value="{{ route('requisition') }}">

                                Requisition & Approvals

                            </option>

                            @endcan

                            @can('Reporting-Modules')

                            <option value="{{ route('reporting') }}">

                                Reporting

                            </option>

                            @endcan

                        </select>

                    </li>

                </ul>

            </nav>

        </div>

        {{-- CONTENT --}}
        <div class="content-wrapper animated fadeInRight">

            @yield('content')

        </div>

        {{-- FOOTER --}}
        <div class="footer">

            <div class="row">

                <div class="col-md-6">

                    <strong>Copyright</strong>

                    © {{ date('Y') }}

                    MBOGO INFO APP+ System

                </div>

                <div class="col-md-6 text-right">

                    Developed & Maintained By Eng. Kivuyo

                </div>

            </div>

        </div>

    </div>

</div>

{{-- SCRIPTS --}}
<script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
<script src="{{ asset('js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('js/plugins/dataTables/datatables.min.js') }}"></script>
<script src="{{ asset('js/inspinia.js') }}"></script>

<script>

    $('#side-menu').metisMenu();

    function navigateTo(url)
    {

        if(url && url !== '')
        {

            window.location.href = url;

        }

    }

</script>

<script>

    document
        .getElementById('minimize-btn')
        .addEventListener('click', function(e)
    {

        e.preventDefault();

        let sidebar   = document.getElementById('sidebar');
        let wrapper   = document.getElementById('page-wrapper');
        let navbar    = document.getElementById('top-navbar');

        if(sidebar.style.width === '250px' || sidebar.style.width === '')
        {

            sidebar.style.width = '70px';

            wrapper.style.marginLeft = '70px';

            wrapper.style.width = 'calc(100% - 70px)';

            navbar.style.left = '70px';

            navbar.style.width = 'calc(100vw - 70px)';

        }
        else
        {

            sidebar.style.width = '250px';

            wrapper.style.marginLeft = '250px';

            wrapper.style.width = 'calc(100% - 250px)';

            navbar.style.left = '250px';

            navbar.style.width = 'calc(100vw - 250px)';

        }

    });

</script>

<script>

    $(document).ready(function(){

        $('.dataTables-example').DataTable({

            pageLength:25,
            responsive:true,
            autoWidth:false

        });

    });

</script>

</body>
</html>