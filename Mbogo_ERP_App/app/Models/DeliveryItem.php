<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'product_id',
        'item_name',
        'unit',
        'quantity',

        // Packing List / Customs Manifest item fields
        'packages_no_type',
        'gross_weight',

        'unit_price',
        'total',
        'issued_qty',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_qty' => 'decimal:4',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}