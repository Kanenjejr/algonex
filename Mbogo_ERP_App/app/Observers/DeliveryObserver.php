<?php

namespace App\Observers;

use App\Models\Delivery;
use App\Models\StockLedger;

class DeliveryObserver
{
    public function created(Delivery $delivery)
    {
        foreach ($delivery->items as $item) {

            $last = StockLedger::where('product_id', $item->product_id)
                ->orderByDesc('id') // 🔥 BADALA YA latest()
                ->first();

            $previous_balance = $last->balance ?? 0;

            $new_balance = $previous_balance - $item->issued_qty;

            StockLedger::create([
                'product_id' => $item->product_id,
                'qty_in' => 0,
                'qty_out' => $item->issued_qty,
                'balance' => $new_balance,
                'reference_type' => 'delivery',
                'reference_id' => $delivery->id,
            ]);
        }
    }

    public function updated(Delivery $delivery)
    {
        StockLedger::where('reference_type', 'delivery')
            ->where('reference_id', $delivery->id)
            ->delete();

        foreach ($delivery->items as $item) {

            $last = StockLedger::where('product_id', $item->product_id)
                ->orderByDesc('id') // 🔥 HAPA PIA
                ->first();

            $previous_balance = $last->balance ?? 0;

            $new_balance = $previous_balance - $item->issued_qty;

            StockLedger::create([
                'product_id' => $item->product_id,
                'qty_in' => 0,
                'qty_out' => $item->issued_qty,
                'balance' => $new_balance,
                'reference_type' => 'delivery',
                'reference_id' => $delivery->id,
            ]);
        }
    }

    public function deleted(Delivery $delivery)
    {
        StockLedger::where('reference_type', 'delivery')
            ->where('reference_id', $delivery->id)
            ->delete();
    }
}