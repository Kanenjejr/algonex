<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrdReqstItem extends Model
{
    use HasFactory;
    protected $table = 'prd_reqst_items';
    protected $fillable = [
        'prd_reqst_header_id',
        'Product_id',
        'PrdPrice_id',
        'unit_price',
        'quantity',
        'subtotal',
    ];
    public function header()
    {
        return $this->belongsTo(PrdReqstHeader::class, 'prd_reqst_header_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'Product_id');
    }
    public function prdprice()
    {
        return $this->belongsTo(PrdPrice::class, 'PrdPrice_id');
    }
}
