<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id',
        'company_id',
        'work_point_id',
        'type',
        'quantity',
        'unit',
        'account_code',
        'account_name',
        'reference_type',
        'reference_id',
        'transaction_date',
        'notes',
        'created_by',
    ];
}