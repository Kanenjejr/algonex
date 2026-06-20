<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaItem extends Model
{
    use HasFactory;

    protected $table = 'proforma_items';

    protected $fillable = [
        'proforma_id',
        'item_type',
        'product_id',
        'service_id',
        'item_name',
        'description',
        'qty',
        'unit',
        'price',
        'total',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
