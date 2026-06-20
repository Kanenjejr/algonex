<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingStockMovement extends Model
{
    use HasFactory;

    protected $table = 'manufacturing_stock_movements';

    protected $fillable = [
        'movement_date',
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'raw_material_id',
        'reference_type',
        'reference_id',
        'qty_in',
        'qty_out',
        'balance_after',
        'remarks',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function unit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}