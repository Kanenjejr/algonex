<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [

        // RELATION
        'pos_sale_id',

        // PRODUCT
        'product_id',

        // DETAILS
        'qty',
        'price',
        'total',

        // ACCOUNT LINK 🔥 (VERY IMPORTANT)
        'account_code',
        'account_name',

        // COMPANY CONTROL
        'company_id',
        'work_point_id'
    ];


    // 🔥 POS SALE
    public function posSale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    // 🔥 PRODUCT
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 🔥 COMPANY
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    // 🔥 LOCATION / WORK POINT
    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
}