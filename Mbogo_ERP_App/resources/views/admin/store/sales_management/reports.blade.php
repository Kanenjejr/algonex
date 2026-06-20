@extends('layouts.salesMaster')

@section('content')

<div class="wrapper wrapper-content animated fadeInRight">

    <!-- Page Heading -->
    <div class="row wrapper border-bottom white-bg page-heading mb-4">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="font-bold text-primary">CRM Reports Dashboard</h2>
                <p class="text-muted">Overview of customers, sales, debts, and leads.</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">

        <!-- Total Customers -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="ibox shadow-sm border-0">
                <div class="ibox-content text-center bg-primary text-white rounded p-4">
                    <div class="mb-3">
                        <i class="fa fa-users fa-3x"></i>
                    </div>
                    <h5 class="font-bold">Total Customers</h5>
                    <h2 class="font-bold">{{ number_format($customers) }}</h2>
                </div>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="ibox shadow-sm border-0">
                <div class="ibox-content text-center bg-success text-white rounded p-4">
                    <div class="mb-3">
                        <i class="fa fa-line-chart fa-3x"></i>
                    </div>
                    <h5 class="font-bold">Total Sales</h5>
                    <h2 class="font-bold">{{ number_format($sales, 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Pending Debts -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="ibox shadow-sm border-0">
                <div class="ibox-content text-center bg-warning text-white rounded p-4">
                    <div class="mb-3">
                        <i class="fa fa-money fa-3x"></i>
                    </div>
                    <h5 class="font-bold">Pending Debts</h5>
                    <h2 class="font-bold">{{ number_format($debts, 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Active Leads -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="ibox shadow-sm border-0">
                <div class="ibox-content text-center bg-danger text-white rounded p-4">
                    <div class="mb-3">
                        <i class="fa fa-bullhorn fa-3x"></i>
                    </div>
                    <h5 class="font-bold">Active Leads</h5>
                    <h2 class="font-bold">{{ number_format($leads) }}</h2>
                </div>
            </div>
        </div>

    </div>

    <!-- Reports Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox shadow-sm">
                <div class="ibox-title bg-white border-bottom">
                    <h5 class="font-bold text-primary">CRM Summary Report</h5>
                </div>

                <div class="ibox-content">

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Report Type</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Total Customers</td>
                                    <td>{{ number_format($customers) }}</td>
                                    <td>
                                        <span class="badge badge-primary">
                                            Active
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>2</td>
                                    <td>Total Sales</td>
                                    <td>{{ number_format($sales, 2) }}</td>
                                    <td>
                                        <span class="badge badge-success">
                                            Completed
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>3</td>
                                    <td>Pending Debts</td>
                                    <td>{{ number_format($debts, 2) }}</td>
                                    <td>
                                        <span class="badge badge-warning">
                                            Pending
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>4</td>
                                    <td>Active Leads</td>
                                    <td>{{ number_format($leads) }}</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            Ongoing
                                        </span>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection