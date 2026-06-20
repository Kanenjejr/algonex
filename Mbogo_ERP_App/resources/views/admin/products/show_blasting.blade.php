@extends('layouts.ManftrMaster')

@section('content')
@php
    $company = optional($record->company);
    $unit = optional($record->companyUnit);
    $wp = optional($record->workPoint);
    $creator = optional($record->creator);
    $updater = optional($record->updater);
@endphp

<div class="wrapper wrapper-content no-print">
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8">
            <h2 class="dashboard-title">Drilling & Blasting Record</h2>
            <ol class="breadcrumb" style="font-size:16px;color:#000">
                <li><a href="{{ route('production.drilling-blasting.index') }}">Drilling & Blasting</a></li>
                <span style="font-size:22px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>{{ $record->record_no }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-4 text-center" style="padding-top:25px;">
            <button onclick="window.print();" class="btn btn-primary">
                <i class="fa fa-print"></i> Print
            </button>
            <a href="{{ route('production.drilling-blasting.index') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<style>

.db-print-wrap{
    background:#f4f6fb;
    padding:20px 0;
    display:flex;
    justify-content:center;
}

.db-print{
    width:190mm;
    margin:0 auto;
    background:#fff;
    padding:5mm;
    box-sizing:border-box;
    box-shadow:0 0 8px rgba(0,0,0,.12);
}

.top-header{
    width:100%;
    text-align:center;
    margin-bottom:8px;
}

.top-header img{
    width:100%;
    display:block;
    margin:0 auto;
    height:auto;
}

.title{
    text-align:center;
    font-size:18px;
    font-weight:700;
    margin:10px 0;
}

.proforma-table{
    width:100%;
    border-collapse:collapse;
}

.proforma-table th,
.proforma-table td{
    border:1px solid #000;
    padding:4px;
    vertical-align:top;
}

.no-border td,
.no-border th{
    border:none !important;
}

.header-bar{
    background:#0b1a78;
    color:#fff;
    padding:5px;
    font-weight:700;
}

.box{
    border:1px solid #000;
    border-top:none;
    padding:6px;
}

.proforma-footer{
    text-align:center;
    margin-top:10px;
    padding-top:5px;
    border-top:1px solid #ddd;
    font-size:9px;
}

@media print{

    @page{
        size:A4 portrait;
        margin:10mm;
    }

    html,
    body{
        margin:0 !important;
        padding:0 !important;
        background:#fff !important;
    }

    body *{
        visibility:hidden;
    }

    #printArea,
    #printArea *{
        visibility:visible;
    }

    .no-print{
        display:none !important;
    }

    #printArea{
        position:absolute;
        left:50%;
        top:0;
        transform:translateX(-50%);
        width:190mm;
        margin:0 auto;
    }

    .db-print{
        width:190mm !important;
        margin:0 auto !important;
        padding:0 !important;
        box-shadow:none !important;
    }

    .top-header{
        width:100% !important;
        text-align:center !important;
    }

    .top-header img{
        width:100% !important;
        display:block !important;
        margin:0 auto !important;
        height:auto !important;
    }

    .proforma-table{
        width:100% !important;
    }

    .proforma-footer{
        width:100% !important;
        text-align:center !important;
        font-size:9px !important;
    }
}

</style>

<div class="db-print-wrap">
    <div class="db-print" id="printArea">
        <div class="top-header">
            <img src="{{ asset('img/header.png') }}" alt="Header">
        </div>

        <div class="title">DRILLING AND BLASTING SUMMARY</div>

        <table class="proforma-table no-border" style="margin-bottom:8px;">
            <tr>
                <td width="62%">
                    <div class="header-bar">SITE / CUSTOMER DETAILS</div>
                    <div class="box">
                        <strong>{{ $record->customer_name }}</strong><br>
                        Project / Site: {{ $record->project_site ?? '-' }}<br>
                        Blasting Period: {{ optional($record->period_from)->format('Y-m-d') ?? '-' }} to {{ optional($record->period_to)->format('Y-m-d') ?? '-' }}<br>
                        Authorized Blaster: {{ $record->authorized_blaster ?? '-' }}
                    </div>
                </td>
                <td width="38%">
                    <table class="proforma-table">
                        <tr><td><strong>RECORD NO</strong></td><td>{{ $record->record_no }}</td></tr>
                        <tr><td><strong>DATE</strong></td><td>{{ optional($record->record_date)->format('Y-m-d') ?? '-' }}</td></tr>
                        <tr><td><strong>COMPANY</strong></td><td>{{ $company->company_name ?? '-' }}</td></tr>
                        <tr><td><strong>BUSINESS UNIT</strong></td><td>{{ $unit->unit_name ?? '-' }}</td></tr>
                        <tr><td><strong>LOCATION</strong></td><td>{{ $unit->location ?? '-' }}</td></tr>
                        <tr><td><strong>WORK POINT</strong></td><td>{{ $wp->work_name ?? '-' }}</td></tr>
                        <tr><td><strong>STATUS</strong></td><td>{{ $record->status }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="proforma-table" style="margin-bottom:8px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Blasts</th>
                    <th>Holes Charged</th>
                    <th>Explosive Type</th>
                    <th>Qty (kg)</th>
                    <th>Detonators (pcs)</th>
                    <th>Cord (m)</th>
                    <th>Booster (pcs)</th>
                    <th>Rock Blasted</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td class="text-center">{{ number_format($record->blasts_conducted) }}</td>
                    <td class="text-center">{{ number_format($record->total_holes_charged) }}</td>
                    <td>{{ $record->explosive_type ?? '-' }}</td>
                    <td class="text-right">{{ number_format($record->explosive_qty, 2) }}</td>
                    <td class="text-right">{{ number_format($record->detonators_qty, 2) }}</td>
                    <td class="text-right">{{ number_format($record->detonating_cord_m, 2) }}</td>
                    <td class="text-right">{{ number_format($record->booster_qty, 2) }}</td>
                    <td class="text-right">{{ number_format($record->total_rock_blasted, 2) }} {{ $record->rock_unit }}</td>
                </tr>
            </tbody>
        </table>

        <table class="proforma-table no-border">
            <tr>
                <td width="50%">
                    <table class="proforma-table">
                        <tr class="header-bar"><th colspan="2">DETAILS</th></tr>
                        <tr><td>Explosive Qty Used/Sold</td><td class="text-right">{{ number_format($record->explosive_qty, 2) }} kg</td></tr>
                        <tr><td>Detonators Used/Sold</td><td class="text-right">{{ number_format($record->detonators_qty, 2) }} pcs</td></tr>
                        <tr><td>Detonating Cord Used/Sold</td><td class="text-right">{{ number_format($record->detonating_cord_m, 2) }} m</td></tr>
                        <tr><td>Booster Quantity</td><td class="text-right">{{ number_format($record->booster_qty, 2) }} pcs</td></tr>
                        <tr><td>Total Rock Blasted</td><td class="text-right">{{ number_format($record->total_rock_blasted, 2) }} {{ $record->rock_unit }}</td></tr>
                    </table>
                </td>
                <td width="50%">
                    <table class="proforma-table">
                        <tr class="header-bar"><th colspan="2">REMARKS / AUDIT</th></tr>
                        <tr><td>Remarks</td><td>{{ $record->remarks ?? '-' }}</td></tr>
                        <tr><td>Created By</td><td>{{ $creator->name ?? '-' }}</td></tr>
                        <tr><td>Last Updated By</td><td>{{ $updater->name ?? '-' }}</td></tr>
                        <tr><td>Created At</td><td>{{ optional($record->created_at)->format('Y-m-d H:i') ?? '-' }}</td></tr>
                        <tr><td>Updated At</td><td>{{ optional($record->updated_at)->format('Y-m-d H:i') ?? '-' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="proforma-footer">
            <div class="footer-title">Thank you for your business!</div>
            Should you have any enquiries concerning this services, please contact Managing Director<br>
            GROUND FLOOR, NILE PLAZA BUILDING, Shinyanga Road Opposite Nyashishi Min Bus Stand<br>
            Tel: {{ $company->phone_No ?? '+255756263287' }} | Email: info@mbogomining.co.tz | Web: www.mbogomining.co.tz
        </div>
    </div>
</div>
@endsection