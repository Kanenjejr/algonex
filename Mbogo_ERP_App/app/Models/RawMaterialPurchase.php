<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialPurchase extends Model
{
    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'vendor_id',
        'raw_material_id',
        'purchase_date',
        'qty',
        'unit_price',
        'total_price',
        'invoice_no',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function material()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
}