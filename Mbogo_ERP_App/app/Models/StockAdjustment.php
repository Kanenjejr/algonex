<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'product_id',
        'company_id',
        'work_point_id',
        'qty',
        'type',
        'reason',
        'created_by',
        'updated_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}