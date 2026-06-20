<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_no',
        'qty',
        'unit_cost',
        'manufacture_date',
        'expiry_date',
        'source',
        'company_id',
        'work_point_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}