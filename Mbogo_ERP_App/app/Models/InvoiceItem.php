<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id','item_type','product_id','service_id','product_name','description','qty','unit','price','total'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function product(){ return $this->belongsTo(Product::class, 'product_id'); }
    public function service(){ return $this->belongsTo(Service::class, 'service_id'); }
}
