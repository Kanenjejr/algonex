@extends('layouts.salesMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="ibox">
        <div class="ibox-title bg-info text-white">
            <h5>Edit Invoice</h5>
        </div>

        <div class="ibox-content">

            <form method="POST" action="{{ route('sales.invoice.update', $invoice->id) }}">
                @csrf
                @method('PUT')

                <div class="row">

                    {{-- INVOICE NO --}}
                    <div class="col-md-3">
                        <label>Invoice No</label>
                        <input type="text" class="form-control" value="{{ $invoice->invoice_no }}" readonly>
                    </div>

                    {{-- CUSTOMER --}}
                    <div class="col-md-3">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" 
                                    {{ $invoice->customer_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PAYMENT TYPE --}}
                    <div class="col-md-3">
                        <label>Payment Type</label>
                        <select name="payment_type" class="form-control">
                            <option value="full" {{ $invoice->payment_type == 'full' ? 'selected' : '' }}>Full</option>
                            <option value="partial" {{ $invoice->payment_type == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="credit" {{ $invoice->payment_type == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>

                </div>

                <hr>

                {{-- ITEMS --}}
                <table class="table table-bordered" id="items_table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($invoice->items as $index => $item)
                        <tr>
                            <td>
                                <input type="text" name="items[{{ $index }}][description]" 
                                    class="form-control" value="{{ $item->description }}">
                            </td>

                            <td>
                                <input type="number" step="1" 
                                    name="items[{{ $index }}][qty]" 
                                    class="form-control qty" value="{{ $item->qty }}">
                            </td>

                            <td>
                                <input type="number" step="0.01" 
                                    name="items[{{ $index }}][price]" 
                                    class="form-control price" value="{{ $item->price }}">
                            </td>

                            <td>
                                <input type="text" class="form-control total" readonly 
                                    value="{{ number_format($item->total,2) }}">
                            </td>

                            <td>
                                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>

                <button type="button" class="btn btn-primary" id="addRow">+ Add Item</button>

                <hr>

                {{-- TOTALS --}}
                <div class="row text-center">

                    <div class="col-md-3">
                        <h5>Sub Total</h5>
                        <h4 id="subtotal">0.00</h4>
                    </div>

                    <div class="col-md-3">
                        <h5>VAT</h5>
                        <h4 id="vat">0.00</h4>
                    </div>

                    <div class="col-md-3">
                        <h5>Total</h5>
                        <h4 id="grand_total">0.00</h4>
                    </div>

                </div>

                {{-- HIDDEN --}}
                <input type="hidden" name="sub_total" id="sub_total_input">
                <input type="hidden" name="vat" id="vat_input">
                <input type="hidden" name="total" id="total_input">

                <br>

                <button class="btn btn-success">Update Invoice</button>

            </form>

        </div>
    </div>

</div>

<script>

document.addEventListener('input', function(){

    let rows = document.querySelectorAll('#items_table tbody tr');

    let subtotal = 0;

    rows.forEach(row => {

        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;

        let total = qty * price;

        row.querySelector('.total').value = total.toFixed(2);

        subtotal += total;

    });

    let vat = subtotal * 0.18;
    let grand = subtotal + vat;

    document.getElementById('subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('vat').innerText = vat.toFixed(2);
    document.getElementById('grand_total').innerText = grand.toFixed(2);

    document.getElementById('sub_total_input').value = subtotal;
    document.getElementById('vat_input').value = vat;
    document.getElementById('total_input').value = grand;

});

// ADD ROW
document.getElementById('addRow').addEventListener('click', function(){

    let tbody = document.querySelector('#items_table tbody');
    let index = tbody.rows.length;

    let row = `
        <tr>
            <td><input type="text" name="items[${index}][description]" class="form-control"></td>
            <td><input type="number" name="items[${index}][qty]" class="form-control qty"></td>
            <td><input type="number" name="items[${index}][price]" class="form-control price"></td>
            <td><input type="text" class="form-control total" readonly></td>
            <td><button type="button" class="btn btn-danger removeRow">X</button></td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', row);

});

// REMOVE ROW
document.addEventListener('click', function(e){
    if(e.target.classList.contains('removeRow')){
        e.target.closest('tr').remove();
    }
});

</script>

@endsection