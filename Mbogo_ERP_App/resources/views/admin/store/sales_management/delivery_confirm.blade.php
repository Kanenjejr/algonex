@extends('layouts.salesMaster')

@section('content')

<div class="wrapper wrapper-content">

    <div class="ibox">
        <div class="ibox-title bg-info">
            <h5>Confirm Delivery (POD)</h5>
        </div>

        <div class="ibox-content">

            <form method="POST" action="{{ route('delivery.confirm',$delivery->id) }}">
                @csrf

                <div class="form-group">
                    <label>Receiver Name</label>
                    <input type="text" name="receiver_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Signature</label>
                    <canvas id="signature-pad" width="400" height="200"
                        style="border:1px solid #000;"></canvas>

                    <input type="hidden" name="signature" id="signature">
                </div>

                <button class="btn btn-success">Confirm Delivery</button>
                <button type="button" class="btn btn-danger" onclick="clearPad()">Clear</button>

            </form>

        </div>
    </div>

</div>

@endsection


@section('scripts')

<script>
let canvas = document.getElementById('signature-pad');
let ctx = canvas.getContext('2d');
let drawing = false;

canvas.addEventListener('mousedown', () => drawing = true);
canvas.addEventListener('mouseup', () => drawing = false);

canvas.addEventListener('mousemove', draw);

function draw(e){
    if(!drawing) return;

    ctx.lineWidth = 2;
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
}

function clearPad(){
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

document.querySelector('form').addEventListener('submit', function(){
    document.getElementById('signature').value = canvas.toDataURL();
});
</script>

@endsection