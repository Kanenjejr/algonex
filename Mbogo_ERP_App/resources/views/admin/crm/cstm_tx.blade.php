@extends('layouts.salesMaster')
@section('content')

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>Customers, Supplies & Interactions Dashboard</h2>
            <ol class="breadcrumb"style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('sales-marketing') }}">Sales & Marketing </a>
                </li>
                <span style="font-size:25px"class="fa fa-angle-double-right "></span>
                <li class="breadcrumb-item active">
                    <strong>Customer / Supplier Ledger</strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php use Carbon\Carbon;
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
                            <tr>
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
    <div class="col-12">
        <h3 class="mb-2 page-title">Customer / Supplier Ledger</h3>
        @can('Register-Customer-Ledger')
            <button style="position: absolute; top: 4.5%; right: 1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#txCreateModal">Add Transaction</button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-warning">
                        <h5>Ledger Table</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Reference</th>
                                        <th>Notes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($txs as $k => $t)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $t->tx_date }}</td>
                                            <td>{{ optional($t->customer)->customer_name ?? '-' }}</td>
                                            <td>{{ ucfirst($t->type) }}</td>
                                            <td>{{ number_format($t->amount, 2) }}</td>
                                            <td>{{ number_format($t->balance_after ?? 0, 2) }}</td>
                                            <td>{{ $t->reference ?? '-' }}</td>
                                            <td>{{ Str::limit($t->notes, 50) }}</td>
                                            <td>
                                                @can('Edit-Customer-Ledger')
                                                    <button class="btn btn-sm btn-warning btn-edit-tx"
                                                        data-id="{{ encrypt($t->id) }}" data-tx_date="{{ $t->tx_date }}"
                                                        data-cstm_id="{{ $t->cstm_id }}" data-type="{{ $t->type }}"
                                                        data-amount="{{ $t->amount }}" data-reference="{{ $t->reference }}"
                                                        data-notes="{{ $t->notes }}">Edit</button>
                                                @endcan
                                                @can('Delete-Customer-Ledger')
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-tx"
                                                        data-id="{{ encrypt($t->id) }}">Remove</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Tx --}}
    <div class="modal fade" id="txCreateModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="txCreateForm" action="{{ route('crm.tx.store') }}" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Transaction</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        @if (in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer']))
                            <div class="form-group"><label>Work Point</label>
                                <select name="work_point_id" class="form-control select2_tx">
                                    <option value="">-- Select --</option>
                                    @foreach ($workPoints as $wp)
                                        <option value="{{ $wp->id }}">{{ $wp->work_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6"><label>Customer / Supplier</label>
                                <select name="cstm_id" class="form-control select2_tx" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($cstmSplies as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6"><label>Date</label><input type="date" name="tx_date"
                                    class="form-control" value="{{ date('Y-m-d') }}"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4"><label>Type</label>
                                <select name="type" class="form-control select2_tx" required>
                                    <option value="debit">Debit</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4"><label>Amount</label><input type="number" step="0.01"
                                    name="amount" class="form-control" required></div>
                            <div class="form-group col-md-4"><label>Reference</label><input type="text" name="reference"
                                    class="form-control"></div>
                        </div>

                        <div class="form-group"><label>Notes</label>
                            <textarea name="notes" class="form-control"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('txCreateForm')"
                            type="submit" class="btn btn-primary">Record</button></div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Tx --}}
    <div class="modal fade" id="txEditModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="txEditForm" method="POST">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Transaction</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input id="edit_tx_id" type="hidden" name="edit_id">
                        <div class="form-row">
                            <div class="form-group col-md-6"><label>Customer / Supplier</label>
                                <select id="edit_tx_cstm_id" name="cstm_id" class="form-control select2_tx">
                                    <option value="">-- Select --</option>
                                    @foreach ($cstmSplies as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6"><label>Date</label><input id="edit_tx_date" type="date"
                                    name="tx_date" class="form-control"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4"><label>Type</label><select id="edit_tx_type" name="type"
                                    class="form-control select2_tx">
                                    <option value="debit">Debit</option>
                                    <option value="credit">Credit</option>
                                </select></div>
                            <div class="form-group col-md-4"><label>Amount</label><input id="edit_tx_amount"
                                    type="number" step="0.01" name="amount" class="form-control" required></div>
                            <div class="form-group col-md-4"><label>Reference</label><input id="edit_tx_reference"
                                    type="text" name="reference" class="form-control"></div>
                        </div>

                        <div class="form-group"><label>Notes</label>
                            <textarea id="edit_tx_notes" name="notes" class="form-control"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button><button onclick="handleConfirmSubmit('txEditForm')"
                            type="submit" class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tempTxEdit = null;

            function initSelect2WithParent($el, parent) {
                if (!$el || !$el.length) return;
                if ($el.data('select2')) try {
                    $el.select2('destroy');
                } catch (e) {}
                var $parent = parent && $(parent).length ? $(parent) : $(document.body);
                $el.select2({
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownParent: $parent
                });
            }

            $('.select2_tx').each(function() {
                var $this = $(this);
                if ($this.closest('#txCreateModal').length) {
                    initSelect2WithParent($this, '#txCreateModal');
                    return;
                }
                if ($this.closest('#txEditModal').length) {
                    initSelect2WithParent($this, '#txEditModal');
                    return;
                }
                initSelect2WithParent($this, null);
            });

            $(document).on('shown.bs.modal', '#txCreateModal', function() {
                $(this).find('.select2_tx').each(function() {
                    initSelect2WithParent($(this), '#txCreateModal');
                    $(this).val(null).trigger('change');
                });
            });
            $(document).on('shown.bs.modal', '#txEditModal', function() {
                $(this).find('.select2_tx').each(function() {
                    initSelect2WithParent($(this), '#txEditModal');
                });
                if (tempTxEdit) {
                    $('#edit_tx_cstm_id').val(tempTxEdit.cstm_id || '').trigger('change');
                    tempTxEdit = null;
                }
            });

            document.querySelectorAll('.btn-edit-tx').forEach(btn => {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    document.getElementById('edit_tx_id').value = enc;
                    document.getElementById('edit_tx_date').value = this.dataset.tx_date || '';
                    document.getElementById('edit_tx_amount').value = this.dataset.amount || 0;
                    document.getElementById('edit_tx_reference').value = this.dataset.reference ||
                        '';
                    document.getElementById('edit_tx_notes').value = this.dataset.notes || '';
                    document.getElementById('edit_tx_type').value = this.dataset.type || 'debit';

                    tempTxEdit = {
                        cstm_id: this.dataset.cstm_id || null
                    };

                    var form = document.getElementById('txEditForm');
                    form.action = "{{ route('crm.tx.update', ':id') }}".replace(':id', enc);

                    $('#txEditModal').modal('show');
                });
            });

            document.querySelectorAll('.btn-delete-tx').forEach(btn => {
                btn.addEventListener('click', function() {
                    var enc = this.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will delete the transaction and recalc balances.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then(res => {
                        if (res.isConfirmed) window.location.href =
                            "{{ route('crm.tx.remove', ':id') }}".replace(':id', enc);
                    });
                });
            });

        });
    </script>
@endsection
