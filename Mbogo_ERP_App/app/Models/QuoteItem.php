<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;
    protected $table = 'quote_items';
    protected $fillable = [
        'quote_id',
        'product_id',
        'product_name',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
    ];
    public function quote() { return $this->belongsTo(Quote::class, 'quote_id'); }
}
