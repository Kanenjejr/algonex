@extends('layouts.salesMaster')

@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>General Supply Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li><a href="{{ route('sales.po.index') }}">Purchase Orders</a></li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active"><strong>Uploaded Documents</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title bg-info">
                <h5>Uploaded Documents - {{ $order->po_no }}</h5>
            </div>

            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-4">
                        <strong>PO No:</strong> {{ $order->po_no }}
                    </div>

                    <div class="col-md-4">
                        <strong>Vendor:</strong> {{ optional($order->vendor)->vendor_name }}
                    </div>

                    <div class="col-md-4">
                        <strong>Status:</strong> {{ $order->status }}
                    </div>
                </div>

                <hr>

                @php
                    $documents = [
                        [
                            'label' => 'Supplier Proforma',
                            'type' => 'proforma',
                            'path' => $order->supplier_proforma_attachment,
                        ],
                        [
                            'label' => 'Supplier Invoice',
                            'type' => 'invoice',
                            'path' => $order->supplier_invoice_attachment,
                        ],
                        [
                            'label' => 'Delivery Note',
                            'type' => 'delivery_note',
                            'path' => $order->delivery_note_attachment,
                        ],
                        [
                            'label' => 'Payment / Cheque / Petty Cash Document',
                            'type' => 'payment',
                            'path' => $order->payment_attachment,
                        ],
                    ];

                    $encryptedId = encrypt($order->id);
                @endphp

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Document Type</th>
                                <th>File Path</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($documents as $i => $doc)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $doc['label'] }}</td>
                                    <td>{{ $doc['path'] ?? '-' }}</td>
                                    <td>
                                        @if ($doc['path'])
                                            <a href="{{ route('sales.po.document.file', [$encryptedId, $doc['type'], 'open']) }}"
                                                target="_blank" class="btn btn-sm btn-primary">
                                                Open
                                            </a>

                                            <a href="{{ route('sales.po.document.file', [$encryptedId, $doc['type'], 'download']) }}"
                                                class="btn btn-sm btn-success">
                                                Download
                                            </a>
                                        @else
                                            <span class="text-muted">No document uploaded</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('sales.po.index') }}" class="btn btn-default">
                    Back
                </a>
            </div>
        </div>
    </div>
@endsection
