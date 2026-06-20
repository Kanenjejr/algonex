<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'item_type',
        'item_id',
        'item_name',
        'description',
        'unit',
        'qty',
        'received_qty',
        'balance_qty',
        'unit_price',
        'sub_total',
        'vat_amount',
        'total_price',
        'account_code',
        'account_name',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'item_id');
    }
}