<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CstmProduct extends Model
{
    use HasFactory;
    protected $table = 'cstm_products';
    protected $fillable = [
        'order_id','cstm_id','product_id','product_name','quantity','unit',
        'unit_price','total_price','user_id','company_id','work_point_id'
    ];
    public function order() { return $this->belongsTo(CstmOrder::class, 'order_id'); }
    public function customer() { return $this->belongsTo(CstmSply::class, 'cstm_id'); }
    public function product() {return $this->belongsTo(Product::class, 'product_id');}
}