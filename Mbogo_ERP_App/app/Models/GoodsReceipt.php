<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'grn_no',
        'grn_date',
        'purchase_order_id',
        'company_id',
        'work_point_id',
        'status'
    ];

    // RELATIONSHIPS
    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}