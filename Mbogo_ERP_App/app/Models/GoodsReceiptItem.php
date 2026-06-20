<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'product_id',
        'ordered_qty',
        'received_qty',
        'unit_price',
        'total_price'
    ];

    public function receipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }
}