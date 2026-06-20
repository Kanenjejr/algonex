<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSale extends Model
{
    protected $fillable = [

        // 🔹 ORGANIZATION
        'company_id',
        'business_unit_id',
        'work_point_id',

        // 🔹 SALES VALUES
        'sub_total',
        'tax',
        'discount', // 🔥 NEW
        'total',

        // 🔹 COST & PROFIT
        'cost_total',
        'profit',

        // 🔹 PAYMENT
        'paid',
        'paid_from', // 🔥 NEW
        'balance',

        // 🔹 MULTI CURRENCY
        'currency', // 🔥 NEW
        'exchange_rate', // 🔥 NEW

        // 🔹 DATE
        'sale_date', // 🔥 NEW

    ];

    public function items()
    {
        return $this->hasMany(PosSaleItem::class, 'pos_sale_id');
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2) . ' ' . ($this->currency ?? 'TZS');
    }

    public function getFormattedProfitAttribute()
    {
        return number_format($this->profit, 2);
    }

    public function getStatusAttribute()
    {
        return $this->balance <= 0 ? 'PAID' : 'PENDING';
    }
}