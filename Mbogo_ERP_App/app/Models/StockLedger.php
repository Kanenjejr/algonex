<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedger extends Model
{
    use HasFactory;

    protected $table = 'stock_ledgers';

    protected $fillable = [
        'product_id',
        'type',
        'transaction_type',
        'qty_in',
        'qty_out',
        'balance',
        'unit_cost',
        'total_value',
        'total_cost',
        'account_code',
        'reference_type',
        'reference_id',
        'description',
        'company_id',
        'company_unit_id',
        'work_point_id',
        'date',
    ];

    protected $casts = [
        'qty_in'  => 'float',
        'qty_out' => 'float',
        'balance' => 'float',
        'unit_cost' => 'float',
        'total_value' => 'float',
        'total_cost' => 'float',
        'date'    => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}