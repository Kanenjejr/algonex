<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;

    protected $table = 'product_stocks';

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }
}