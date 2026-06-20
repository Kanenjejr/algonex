@extends('layouts.ReqstMaster')
@section('content')

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Requisition & Approvals Dashboard</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('requisition') }}">Requisition & Approvals</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Money Request</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php
                        use Carbon\Carbon;
                        $carbon = Carbon::now();
                        $carbon1 = Carbon::now()->toDateString();
                        echo $carbon->format('l');
                        echo ' , ';
                        echo $carbon1;
                        ?>
                    </strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-1">
            <h2>Time</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <table>
                            <tr>
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script type="text/javascript">
        function timedMsg() {
            var t = setInterval("change_time();", 1000);
        }

        function change_time() {
            var d = new Date();
            var curr_hour = d.getHours();
            var curr_min = d.getMinutes();
            var curr_sec = d.getSeconds();
            if (curr_hour > 24)
                curr_hour = curr_hour - 24;
            document.getElementById('Hour').innerHTML = curr_hour + ':';
            document.getElementById('Minut').innerHTML = curr_min + ':';
            document.getElementById('Second').innerHTML = curr_sec;
        }
        timedMsg();
    </script>

    @php
        $status = strtolower(trim($mr->Status ?? ''));
        $isApproved = !empty($mr->approved_at) || $status === 'approved';
        $isRejected = $status === 'rejected';
        $isDeclined = $status === 'declined';
    @endphp

    <div class="wrapper wrapper-content animated fadeInRight" style="padding:15px;">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox"
                    style="border:1px solid #d9e2f2; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(23,58,122,.08); background:#fff;">

                    <div class="ibox-title bg-success"
                        style="background:linear-gradient(135deg,#173a7a 0%,#214f9c 55%,#244f96 100%); color:#fff; padding:14px 16px; border-bottom:4px solid #b08a2e;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <div style="font-size:18px; font-weight:800;">Money Requisition Form</div>
                            <div class="ibox-tools" style="display:flex; gap:10px; align-items:center;">
                                <a onclick="window.history.back();" class="btn btn-secondary text-white"
                                    style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#6b7280;">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                                <a onclick="printReceipt('form1')" class="btn btn-primary text-white"
                                    style="border:none; border-radius:10px; padding:8px 14px; font-weight:600; box-shadow:0 4px 10px rgba(0,0,0,.12); background:#1f4fa3;">
                                    <i class="fa fa-print"></i> Print
                                </a>
                            </div>
                        </div>
                    </div>

                    <div id="form1" class="ibox-content" style="padding:20px; background:#fff;">

                            {{-- COMPANY HEADER --}}
                            <div style="width:100%; margin-bottom:20px; text-align:center;">

                              <img src="{{ asset('img/header.png') }}"
                                  alt="Company Header"
                                   style="width:100%;
                                       max-height:170px;
                                        object-fit:contain;">

                            </div>

                            <div
                                style="max-width:980px; margin:0 auto; color:#000; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:13px; line-height:1.55;">

                                <div
                                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; padding-bottom:12px; border-bottom:2px solid rgba(23,58,122,.14);">

                                    <div style="max-width:68%;">
                                        <div style="font-size:22px; font-weight:800; color:#173a7a; line-height:1.2;">
                                            {{ optional($mr->company)->company_name ?? '-' }}
                                        </div>

                                        <div style="font-size:12px; color:#334155; line-height:1.7; margin-top:4px;">
                                            {{ optional($mr->company)->company_code ? 'Code: ' . optional($mr->company)->company_code : '' }}<br>

                                            {{ optional($mr->company)->district ?? '' }}
                                            {{ optional($mr->company)->city ?? '' }}<br>

                                            {{ optional($mr->company)->phone_No ?? '' }}
                                        </div>
                                    </div>
            
                                   <div style="text-align:right; min-width:220px;">
                                    @if (optional($mr->company)->logo)
                                        <div style="margin-bottom:8px;">
                                            <img src="{{ asset(optional($mr->company)->logo) }}"
                                                style="max-height:70px; max-width:150px;">
                                        </div>
                                    @endif
                                    <div style="font-size:18px; font-weight:800; color:#7b4a2d; margin-top:4px;">Money
                                        Request</div>
                                    <div>Request No: <strong>{{ $mr->RequestNo }}</strong></div>
                                    <div>Request Date:
                                        <strong>{{ $mr->RequestDate ? \Carbon\Carbon::parse($mr->RequestDate)->format('Y-m-d') : '-' }}</strong>
                                    </div>
                                    <div style="margin-top:8px;">
                                        <span
                                            style="display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px;font-weight:700;
                                            background: {{ $isApproved ? '#eef4ff' : ($isRejected ? '#ffe4e6' : ($isDeclined ? '#ffe4e6' : '#fff4e5')) }};
                                            color: {{ $isApproved ? '#173a7a' : ($isRejected ? '#b42318' : ($isDeclined ? '#b42318' : '#9a6700')) }};
                                            border:1px solid {{ $isApproved ? '#d8e4fb' : ($isRejected ? '#fda4af' : ($isDeclined ? '#fda4af' : '#ffe1b8')) }};">
                                            {{ $isApproved ? 'APPROVED' : ($isRejected ? 'REJECTED' : ($isDeclined ? 'DECLINED' : 'PENDING APPROVAL')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; gap:14px; margin-top:14px; flex-wrap:wrap;">
                                <div
                                    style="flex:1; min-width:320px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                    <div
                                        style="font-weight:800; color:#173a7a; margin-bottom:8px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                        Request Information</div>
                                    <div><strong>Company Unit:</strong> {{ optional($mr->unit)->unit_name ?? '-' }}</div>
                                    <div style="margin-top:6px;"><strong>Work Site:</strong>
                                        {{ optional($mr->workpoint)->work_code ?? '-' }} -
                                        {{ optional($mr->workpoint)->work_name ?? '-' }}</div>
                                    <div style="margin-top:8px;"><strong>Requested By:</strong>
                                        {{ optional($mr->requester)->name ?? '-' }}</div>
                                    <div style="margin-top:8px;"><strong>Status:</strong> {{ $mr->Status }}</div>
                                </div>

                                <div
                                    style="flex:1; min-width:320px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                    <div
                                        style="font-weight:800; color:#173a7a; margin-bottom:8px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                        Payee Information</div>
                                    <div><strong>Payee:</strong> {{ $mr->PayeeName }}
                                        <small>({{ $mr->PayeeContact }})</small>
                                    </div>
                                    <div style="margin-top:12px;"><strong>Amount Requested:</strong>
                                        {{ number_format((float) $mr->total_amount, 2) }}</div>
                                    <div style="margin-top:12px;"><strong>Purpose:</strong> {{ $mr->remarks ?? '-' }}</div>
                                </div>
                            </div>

                            <div
                                style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                <div
                                    style="font-weight:800; color:#173a7a; margin-bottom:8px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                    Accounting & Organizational Details</div>
                                <div style="display:flex; gap:14px; flex-wrap:wrap;">
                                    <div style="flex:1; min-width:220px;">
                                        <div><strong>Accounting Code:</strong> {{ $mr->accounting_code_6 ?? '-' }}</div>
                                        <div><strong>Accounting Description:</strong>
                                            {{ $mr->accounting_name_6 ?? '-' }}</div>
                                    </div>
                                    <div style="flex:1; min-width:220px;">
                                        <div><strong>Sub Accounting Code:</strong>
                                            {{ $mr->sub_accounting_code_8 ?? '-' }}</div>
                                        <div><strong>Sub Accounting Description:</strong>
                                            {{ $mr->sub_accounting_name_8 ?? '-' }}</div>
                                    </div>
                                    <div style="flex:1; min-width:220px;">
                                        <div><strong>Root Account Code:</strong> {{ $mr->root_account_code ?? '-' }}</div>
                                        <div><strong>Root Account Description:</strong>
                                            {{ $mr->root_account_name ?? '-' }}</div>
                                    </div>
                                </div>

                                <div style="display:flex; gap:14px; flex-wrap:wrap; margin-top:12px;">
                                    <div style="flex:1; min-width:220px;">
                                        <div><strong>Department:</strong> {{ optional($mr->department)->depCode ?? '-' }} -
                                            {{ optional($mr->department)->depName ?? '-' }}</div>
                                        <div><strong>Section:</strong> {{ optional($mr->section)->secCode ?? '-' }} -
                                            {{ optional($mr->section)->secName ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div
                                style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                <div
                                    style="font-weight:800; color:#173a7a; margin-bottom:8px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                    Description</div>
                                <div style="color:#374151;">{{ $mr->Description ?? '-' }}</div>
                            </div>

                            <div
                                style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                <div
                                    style="font-weight:800; color:#173a7a; margin-bottom:8px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                    Remarks</div>
                                <div style="color:#374151;">{{ $mr->remarks ?? '-' }}</div>
                            </div>

                            <div
                                style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                <div
                                    style="font-weight:800; color:#173a7a; margin-bottom:8px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                    Workflow Comments</div>
                                <div style="color:#374151;">
                                    <div><strong>Verified Comment:</strong> {{ $mr->verified_comment ?? '-' }}</div>
                                    <div><strong>Approval Comment:</strong> {{ $mr->approval_comment ?? '-' }}</div>
                                    <div><strong>Cashier Comment:</strong> {{ $mr->cashier_comment ?? '-' }}</div>
                                    <div><strong>Retirement Comment:</strong> {{ $mr->retirement_comment ?? '-' }}</div>
                                    <div><strong>Rejection Comment:</strong> {{ $mr->rejection_comment ?? '-' }}</div>
                                </div>
                            </div>

                            <div
                                style="margin-top:14px; border:1px solid #d9e2f2; border-radius:12px; padding:12px 14px; background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);">
                                <div
                                    style="font-weight:800; color:#173a7a; margin-bottom:8px; padding-bottom:4px; border-bottom:1px dashed #b7c7e6;">
                                    Approval Details</div>
                                <div style="color:#374151;">
                                    <div><strong>Approved Amount:</strong>
                                        {{ $mr->approved_amount !== null ? number_format((float) $mr->approved_amount, 2) : '-' }}
                                    </div>
                                    <div><strong>Payment Mode:</strong> {{ $mr->Payment_mode ?? '-' }}</div>
                                    <div><strong>Payment Voucher No:</strong> {{ $mr->payment_vocher_no ?? '-' }}</div>
                                    <div><strong>Returned Amount:</strong>
                                        {{ number_format((float) ($mr->returned_amount ?? 0), 2) }}</div>
                                </div>
                            </div>

                            <div style="display:flex; gap:12px; margin-top:18px; flex-wrap:wrap;">
                                <div style="flex:1; min-width:230px;">
                                    <div
                                        style="border:1px solid #d9e2f2; border-left:5px solid #b08a2e; border-radius:12px; padding:12px 12px 14px; min-height:130px; background:#fff;">
                                        <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                                        <div style="text-align:center; font-weight:700;">Requested By</div>
                                        <div style="text-align:center;">Name:
                                            <strong>{{ optional($mr->requester)->name ?? '-' }}</strong>
                                        </div>
                                        <div style="text-align:center; font-size:12px;">When:
                                            {{ $mr->created_at ? \Carbon\Carbon::parse($mr->created_at)->format('Y-m-d H:i') : '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div style="flex:1; min-width:230px;">
                                    <div
                                        style="border:1px solid #d9e2f2; border-left:5px solid #b08a2e; border-radius:12px; padding:12px 12px 14px; min-height:130px; background:#fff;">
                                        <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                                        <div style="text-align:center; font-weight:700;">Verified By</div>
                                        <div style="text-align:center;">Name:
                                            <strong>{{ optional($mr->verifier)->name ?? '-' }}</strong>
                                        </div>
                                        <div style="text-align:center; font-size:12px;">When:
                                            {{ $mr->verified_at ? \Carbon\Carbon::parse($mr->verified_at)->format('Y-m-d H:i') : '-' }}
                                        </div>
                                        <div style="margin-top:8px; text-align:center;">
                                            @if (optional($mr->verifier)->st_sign)
                                                <img src="{{ asset(optional($mr->verifier)->st_sign) }}"
                                                    style="max-height:70px; max-width:150px;">
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div style="flex:1; min-width:230px;">
                                    <div
                                        style="border:1px solid #d9e2f2; border-left:5px solid #b08a2e; border-radius:12px; padding:12px 12px 14px; min-height:130px; background:#fff;">
                                        <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                                        <div style="text-align:center; font-weight:700;">Approved By</div>
                                        <div style="text-align:center;">Name:
                                            <strong>{{ $isApproved ? optional($mr->approver)->name ?? '-' : '-' }}</strong>
                                        </div>
                                        <div style="text-align:center; font-size:12px;">When:
                                            {{ $isApproved && $mr->approved_at ? \Carbon\Carbon::parse($mr->approved_at)->format('Y-m-d H:i') : '-' }}
                                        </div>

                                        <div style="margin-top:8px; text-align:center;">
                                            @if ($isApproved)
                                                @if (optional($mr->company)->signature)
                                                    <img src="{{ asset(optional($mr->company)->signature) }}"
                                                        style="max-height:70px; max-width:150px;">
                                                @endif
                                                @if (optional($mr->company)->stamp)
                                                    <div style="margin-top:6px;">
                                                        <img src="{{ asset(optional($mr->company)->stamp) }}"
                                                            style="max-height:70px; max-width:150px;">
                                                    </div>
                                                @endif
                                            @else
                                                <div style="font-size:12px; color:#9a6700; font-style:italic;">Signature
                                                    and stamp will appear after approval.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; gap:12px; margin-top:18px; flex-wrap:wrap;">
                                <div style="flex:1; min-width:230px;">
                                    <div
                                        style="border:1px solid #d9e2f2; border-left:5px solid #b08a2e; border-radius:12px; padding:12px 12px 14px; min-height:120px; background:#fff;">
                                        <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                                        <div style="text-align:center; font-weight:700;">Cashed Out By</div>
                                        <div style="text-align:center;">Name:
                                            <strong>{{ optional($mr->cashier)->name ?? '-' }}</strong>
                                        </div>
                                        <div style="text-align:center; font-size:12px;">When:
                                            {{ $mr->cashed_at ? \Carbon\Carbon::parse($mr->cashed_at)->format('Y-m-d H:i') : '-' }}
                                        </div>
                                        <div style="text-align:center; font-size:12px; margin-top:6px;">Voucher No:
                                            {{ $mr->payment_vocher_no ?? '-' }}</div>
                                    </div>
                                </div>

                                <div style="flex:1; min-width:230px;">
                                    <div
                                        style="border:1px solid #d9e2f2; border-left:5px solid #b08a2e; border-radius:12px; padding:12px 12px 14px; min-height:120px; background:#fff;">
                                        <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                                        <div style="text-align:center; font-weight:700;">Retired By</div>
                                        <div style="text-align:center;">Name:
                                            <strong>{{ optional($mr->retreater)->name ?? '-' }}</strong>
                                        </div>
                                        <div style="text-align:center; font-size:12px;">When:
                                            {{ $mr->retired_at ? \Carbon\Carbon::parse($mr->retired_at)->format('Y-m-d H:i') : '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div style="flex:1; min-width:230px;">
                                    <div
                                        style="border:1px solid #d9e2f2; border-left:5px solid #b08a2e; border-radius:12px; padding:12px 12px 14px; min-height:120px; background:#fff;">
                                        <div style="border-bottom:1px solid #111; height:2px; margin-bottom:6px;"></div>
                                        <div style="text-align:center; font-weight:700;">Retirement Docs</div>
                                        <div style="text-align:center; margin-top:6px;">
                                            @if ($mr->retirement_docs)
                                                <a href="{{ route('reports.money.cashout_retirement.document', encrypt($mr->id)) }}"
                                                    target="_blank">View Document</a>
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top:18px; text-align:center; font-size:11px; color:#666;">
                                Printed: {{ now()->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printReceipt(ele) {
            var content = document.getElementById(ele);
            if (!content) return alert('Nothing to print');

            var pri = window.open('', '_blank', 'height=842,width=595');
            var doc = pri.document.open();

            var style = `<style>
            @page{
                size:A4 portrait;
                margin:12mm;
            }
            *{
                box-sizing:border-box;
                -webkit-print-color-adjust:exact;
                print-color-adjust:exact;
            }
            html, body{
                margin:0;
                padding:0;
                background:#fff;
                color:#000;
                font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
                font-size:12.5px;
            }
            img{
                max-width:100%;
                height:auto;
            }
            a{
                color:#000;
                text-decoration:none;
            }
        </style>`;

            doc.write('<html><head><title>Print</title>' + style + '</head><body>');
            doc.write(content.innerHTML);
            doc.write('</body></html>');
            doc.close();

            pri.focus();
            setTimeout(function() {
                pri.print();
            }, 400);
        }
    </script>
@endsection
