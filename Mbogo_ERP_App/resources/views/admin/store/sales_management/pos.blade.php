@extends('layouts.salesMaster')

@section('content')

<div class="row">

{{-- ================= PRODUCTS ================= --}}
<div class="col-md-7">

    {{--  STOCK ALERT (IMEONGEZWA TU) --}}
    @php $user = auth()->user(); @endphp
@if(in_array($user->role ?? 'admin', ['admin','accountant','manager']))
    @if(isset($alerts) && count($alerts))
    <div class="alert alert-danger">
        ⚠ Low Stock:
        @foreach($alerts as $a)
            {{ $a->product_name ?? 'Item' }} ({{ $a->current_stock }}) |
        @endforeach
    </div>
    @endif
@endif

    <div class="ibox">
        <div class="ibox-title d-flex justify-content-between">
            <h5>Products</h5>

            {{-- SEARCH --}}
            <input type="text" id="search" class="form-control" placeholder="Search or scan product...">
            <div id="suggestions" class="list-group"></div>
        </div>

        <div class="ibox-content" style="max-height:500px; overflow-y:auto;">

            <table class="table table-bordered table-hover">

         <thead style="background:#1c84c6;">

        <tr>

            <th style="
                color:indigo !important;
                font-weight:700;
            ">
                Product
            </th>

            <th style="
                color:indigo !important;
                font-weight:700;
            ">
                Stock
            </th>

            <th style="
                color:indigo !important;
                font-weight:700;
            ">
                Price
            </th>

            <th style="
                color:indigo !important;
                font-weight:700;
            ">
                Action
            </th>

        </tr>

    </thead>

    <tbody id="product-table">

        @foreach($products as $p)

        <tr class="product-row"
            data-name="{{ strtolower($p->product_name) }}">

            {{-- PRODUCT --}}
            <td style="
                font-weight:600;
                color:#1f2937;
            ">

                <div style="
                    display:flex;
                    align-items:center;
                    gap:10px;
                ">

                    <img src="{{ asset('img/product.png') }}"
                         style="
                            width:45px;
                            height:45px;
                            object-fit:contain;
                            border-radius:8px;
                            border:1px solid #ddd;
                            padding:3px;
                         ">

                    <div>

                        <div style="
                            font-weight:700;
                        ">

                            {{ $p->product_name }}

                        </div>

                    </div>

                </div>

            </td>

            {{-- STOCK --}}
            <td>

                @if($p->current_stock <= 5)

                    <span class="label label-danger"
                          style="
                            font-size:12px;
                            padding:6px 10px;
                          ">

                        {{ $p->current_stock }}

                    </span>

                @else

                    <span class="label label-primary"
                          style="
                            font-size:12px;
                            padding:6px 10px;
                          ">

                        {{ $p->current_stock }}

                    </span>

                @endif

            </td>

            {{-- PRICE --}}
            <td style="
                color:#1c84c6;
                font-weight:700;
                font-size:15px;
            ">

                {{ number_format($p->price,2) }}

            </td>

            {{-- BUTTON --}}
            <td>

                <button onclick="add({{ $p->id }})"
                        class="btn btn-success btn-sm"
                        style="
                            border-radius:8px;
                            font-weight:600;
                        ">

                    <i class="fa fa-cart-plus"></i>

                    Add

                </button>

            </td>

        </tr>

        @endforeach

        </tbody>

      </table>
        </div>
    </div>

</div>


{{-- ================= CART ================= --}}
<div class="col-md-5">

    <div class="ibox">
        <div class="ibox-title">
            <h5>Cart</h5>
        </div>

        <div class="ibox-content">

            {{-- SALE DATE --}}
            <div class="form-group">
                <label>Sale Date</label>
                <input type="datetime-local" id="sale_date" class="form-control"
                    value="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

            {{-- CURRENCY --}}
           <div class="row">
                <div class="col-md-6">
                    <label>Currency</label>
                    <select class="form-control" id="currency">
                        <option value="TZS">Tanzanian Shilling (TZS)</option>
                        <option value="USD">US Dollar (USD)</option>
                        <option value="EUR">Euro (EUR)</option>
                        <option value="GBP">British Pound (GBP)</option>
                        <option value="KES">Kenyan Shilling (KES)</option>
                        <option value="UGX">Ugandan Shilling (UGX)</option>
                        <option value="AED">UAE Dirham (AED)</option>
                        <option value="CNY">Chinese Yuan (CNY)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Exchange Rate</label>
                    <input type="number" id="exchange_rate" class="form-control" value="1" readonly>
                </div>
            </div>

            <hr>

            {{-- CUSTOMER --}}
            <div class="form-group">
                <label>Customer</label><br>

                <button class="btn btn-outline-primary btn-sm">Walk-in Customer</button>
                <button class="btn btn-outline-secondary btn-sm">Select Customer</button>

                <div class="alert alert-info mt-2 p-1">
                    Selected: Walk-in Customer
                </div>
            </div>

            {{-- CART ITEMS --}}
            <div id="cart" class="text-center text-muted">
                No items in cart
            </div>

            <hr>

            {{-- TOTALS --}}
            <div class="d-flex justify-content-between">
                <span>Subtotal:</span>
                <b id="sub">0.00 TZS</b>
            </div>

            <div class="d-flex justify-content-between">
                <span>VAT:</span>
                <b id="vat">0.00 TZS</b>
            </div>

            {{--  DISCOUNT (IMEONGEZWA) --}}
            <div class="d-flex justify-content-between">
                <span>Discount:</span>
                <input type="number" id="discount" value="0" class="form-control w-50">
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <strong>Total:</strong>
                <strong id="total">0.00 TZS</strong>
            </div>

            {{--  CHANGE (IMEONGEZWA) --}}
            <div class="d-flex justify-content-between">
                <span>Change:</span>
                <b id="change">0.00</b>
            </div>

            <hr>

            {{-- PAYMENT --}}
            <div class="form-group">
                <label>Paid From</label>
               <select class="form-control" id="paid_from">
                    <option value="CRDB">CRDB BANK</option>
                    <option value="NMB">NMB BANK</option>
                    <option value="NBC">NBC BANK</option>
                    <option value="MOBILE">Mobile phone</option>
                    <option value="GLOBAL_PAY">Global Pay Card</option>
               </select>
            </div>

            <div class="form-group">
                <label>Amount Paid</label>
                <input type="number" id="paid" class="form-control" value="0">
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea class="form-control"></textarea>
            </div>

            <button onclick="checkout()" class="btn btn-success btn-block">
                ✔ Complete Sale
            </button>

            <button onclick="clearCart()" class="btn btn-secondary btn-block mt-2">
                Clear Cart
            </button>

        </div>
    </div>

</div>

</div>

{{-- ================= SCRIPT ================= --}}
<script>

// SEARCH (unchanged)
document.getElementById('search').addEventListener('keyup', function () {

    let value = this.value.toLowerCase().trim();

    let rows = document.querySelectorAll('#product-table .product-row');

    rows.forEach(function(row){

        let productName =
            row.getAttribute('data-name') || '';

        productName =
            productName.toLowerCase();

        if(productName.includes(value)){

            row.style.display = '';

        }else{

            row.style.display = 'none';

        }

    });

});
// LOAD CART (UPDATED TU kuongeza discount + change)
function load(){
fetch('/cart').then(r=>r.json()).then(d=>{
let html='';

if(d.items.length === 0){
    html = "No items in cart";
}else{
    html = '<table class="table table-sm">';
    d.items.forEach(i=>{
        html+=`<tr>
            <td>${i.name}</td>
            <td>${i.qty}</td>
            <td>${(i.price*i.qty).toFixed(2)}</td>
            <td><button onclick="remove(${i.id})">x</button></td>
        </tr>`;
    });
    html += '</table>';
}

let discount = parseFloat(document.getElementById('discount').value || 0);
let total = d.total - discount;

document.getElementById('cart').innerHTML = html;
document.getElementById('sub').innerText = d.sub_total + ' TZS';
document.getElementById('vat').innerText = d.vat + ' TZS';
document.getElementById('total').innerText = total + ' TZS';

// CHANGE
let paid = parseFloat(document.getElementById('paid').value || 0);
document.getElementById('change').innerText = (paid - total).toFixed(2);

});
}

// ADD / REMOVE / CLEAR (unchanged)
function add(id){
fetch('/cart/add',{
method:'POST',
headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
body:JSON.stringify({product_id:id})
}).then(load);
}

function remove(id){
fetch('/cart/remove',{
method:'POST',
headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
body:JSON.stringify({product_id:id})
}).then(load);
}

function clearCart(){
fetch('/cart/clear').then(load);
}

// CHECKOUT (imeongezewa discount tu)
function checkout(){
let paid = document.getElementById('paid').value;
let discount = document.getElementById('discount').value;
let currency = document.getElementById('currency').value;
let exchange_rate = document.getElementById('exchange_rate').value;
let sale_date = document.getElementById('sale_date').value;
let paid_from = document.getElementById('paid_from').value;

fetch('/pos/checkout',{
method:'POST',
headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
body: JSON.stringify({
    paid: paid,
    discount: discount,
    paid_from: paid_from,
    currency: currency,
    exchange_rate: exchange_rate,
    sale_date: sale_date
})
})
.then(r=>r.json())
.then(res=>{
if(res.success){
window.open(res.receipt,'_blank');
load();
}else{
alert(res.error);
}
});
}

// AUTO UPDATE
document.getElementById('paid').addEventListener('keyup', load);
document.getElementById('discount').addEventListener('keyup', load);

document.getElementById('currency').addEventListener('change', function(){
document.getElementById('exchange_rate').value = 'Loading...';
let currency = this.value;

if(currency === 'TZS'){
document.getElementById('exchange_rate').value = 1;
return;
}

fetch('https://open.er-api.com/v6/latest/TZS')
.then(res => res.json())
.then(data => {
let rate = data.rates[currency];
if(rate){
document.getElementById('exchange_rate').value = (1 / rate).toFixed(4);
}
});
});

load();

document.getElementById('search').addEventListener('keyup', function(){

let q = this.value;

if(q.length < 2){
document.getElementById('suggestions').innerHTML = '';
return;
}

fetch('/pos/search?query=' + q)
.then(res => res.json())
.then(data => {

let html = '';
data.forEach(p=>{
html += `
<a href="#" class="list-group-item list-group-item-action"
onclick="selectProduct(${p.id})">
${p.product_name} - ${p.price} TZS (Stock: ${p.stock})
</a>`;
});
document.getElementById('suggestions').innerHTML = html;});
});
function selectProduct(id){

add(id); 
document.getElementById('suggestions').innerHTML = '';
document.getElementById('search').value = '';

}
</script>
@endsection