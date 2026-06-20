@extends('layouts.salesMaster')
@section('content')
    @php $enc = fn($id) => $id ? \Illuminate\Support\Facades\Crypt::encryptString((string)$id) : ''; @endphp
    <style>
        .invoice-box {
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px
        }

        .header-bar {
            background: #0b1a78;
            color: #fff;
            font-weight: bold;
            padding: 7px
        }

        .table th {
            background: #f3f3f4
        }

        .text-right {
            text-align: right
        }

        .status-paid {
            color: green;
            font-weight: bold
        }

        .status-partial {
            color: #f8ac59;
            font-weight: bold
        }

        .status-unpaid {
            color: red;
            font-weight: bold
        }
    </style>
    <div class="wrapper wrapper-content">
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-8">
                <h2>Tax Invoice</h2>
                <ol class="breadcrumb">
                    <li><a href="{{ route('sales.invoices.index') }}">Invoices</a></li>
                    <li class="active"><strong>{{ $invoice->invoice_no }}</strong></li>
                </ol>
            </div>
            <div class="col-lg-2">
                <h4>Current Date</h4><strong>{{ now()->format('l, Y-m-d') }}</strong>
            </div>
            <div class="col-lg-2">
                <h4>Time</h4><strong><span id="Hour" style="color:green"></span><span id="Minut"
                        style="color:green"></span><span id="Second" style="color:red"></span></strong>
            </div>
        </div>
        <div class="text-right mt-2"><a href="{{ route('sales.invoices.index') }}" class="btn btn-default">Back</a> <a
                href="{{ route('sales.invoice.print', $enc($invoice->id)) }}" target="_blank"
                class="btn btn-primary">Print</a></div>
        <div class="invoice-box mt-3"><img src="{{ asset('img/header.png') }}"
                style="width:100%;max-height:100%;object-fit:contain">
            <h2 class="text-center">TAX INVOICE</h2>
            <div class="row">
                <div class="col-md-7">
                    <div class="header-bar">CUSTOMER DETAILS</div>
                    <p><strong>{{ optional($invoice->customer)->customer_name }}</strong><br>{{ optional($invoice->customer)->address }}<br>Phone:
                        {{ optional($invoice->customer)->phone }}<br>
                        @if (optional($invoice->customer)->tin_number)
                            TIN: {{ optional($invoice->customer)->tin_number }}
                        @endif
                    </p>
                </div>
                <div class="col-md-5">
                    <table class="table table-bordered">
                        <tr>
                            <th>Date</th>
                            <td>{{ optional($invoice->invoice_date)->format('F d, Y') ?? $invoice->created_at->format('F d, Y') }}
                            </td>
                        </tr>
                        <tr>
                            <th>Invoice #</th>
                            <td>{{ $invoice->invoice_no }}</td>
                        </tr>
                        <tr>
                            <th>Proforma #</th>
                            <td>{{ optional($invoice->proforma)->proforma_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td class="status-{{ $invoice->payment_status }}">{{ strtoupper($invoice->payment_status) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->description ?? $item->product_name }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-right">{{ $invoice->currency }} {{ number_format($item->price, 2) }}</td>
                            <td class="text-right">{{ $invoice->currency }} {{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-7">
                    <div class="header-bar">BANK / PAYMENT DETAILS</div>
                    <table class="table table-bordered">
                        <tr>
                            <th>Bank</th>
                            <td>{{ $invoice->bank_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Account No</th>
                            <td>{{ $invoice->account_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>SWIFT</th>
                            <td>{{ $invoice->swift_code ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-5">
                    <table class="table table-bordered">
                        <tr>
                            <th>Subtotal</th>
                            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->sub_total, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th>VAT</th>
                            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td class="text-right"><strong>{{ $invoice->currency }}
                                    {{ number_format($invoice->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Approved Paid</th>
                            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->paid_amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th>Balance</th>
                            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->balance, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <hr>
            <h4>Payments</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Receipt</th>
                        <th>Method</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Attachment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->payments as $p)
                        <tr>
                            <td>{{ optional($p->payment_date)->format('Y-m-d') }}</td>
                            <td>{{ $p->receipt_no ?? $p->payment_no }}</td>
                            <td>{{ ucfirst($p->payment_method) }}</td>
                            <td>{{ optional($p->paymentAccount)->SubDescription }}</td>
                            <td class="text-right">{{ $p->currency }} {{ number_format($p->amount, 2) }}</td>
                            <td><span
                                    class="label label-{{ $p->status == 'approved' ? 'success' : 'warning' }}">{{ strtoupper($p->status) }}</span>
                            </td>
                            <td>
                                @if ($p->receipt_attachment)
                                    <a target="_blank" href="{{ asset($p->receipt_attachment) }}">View Receipt</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($p->status == 'pending')
                                    @can('Verify-Payments')
                                        <form method="POST" action="{{ route('sales.payments.verify', $enc($p->id)) }}"
                                            style="display:inline">@csrf<button class="btn btn-xs btn-success">Approve</button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($invoice->balance > 0)
                <hr>
                <h4>Record Additional Pending Payment</h4>
                <form method="POST" action="{{ route('sales.invoice.payment', $enc($invoice->id)) }}"
                    enctype="multipart/form-data">@csrf<div class="row">
                        <div class="col-md-2"><label>Amount</label><input type="number" step="0.01"
                                max="{{ $invoice->balance }}" name="payment_amount" class="form-control" required></div>
                        <div class="col-md-2"><label>Method</label><select name="payment_method" class="form-control">
                                <option value="bank">Bank</option>
                                <option value="cash">Cash</option>
                                <option value="mobile">Mobile</option>
                            </select></div>
                        <div class="col-md-3"><label>Account</label><select name="payment_account_id" class="form-control">
                                @foreach ($paymentAccounts as $a)
                                    <option value="{{ $enc($a->id) }}">{{ $a->SubCode }} - {{ $a->SubDescription }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><label>Receipt No</label><input type="text" name="receipt_no"
                                class="form-control"></div>
                        <div class="col-md-3"><label>Attachment</label><input type="file" name="receipt_attachment"
                                class="form-control"></div>
                        <div class="col-md-12 mt-2"><button class="btn btn-success">Save Pending Payment</button></div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <script>
        function timedMsg() {
            setInterval(change_time, 1000)
        }

        function change_time() {
            const d = new Date();
            Hour.innerHTML = String(d.getHours()).padStart(2, '0') + ':';
            Minut.innerHTML = String(d.getMinutes()).padStart(2, '0') + ':';
            Second.innerHTML = String(d.getSeconds()).padStart(2, '0')
        }
        timedMsg();
    </script>
@endsection
