<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingMaterialStock extends Model
{
    use HasFactory;

    protected $table = 'manufacturing_material_stocks';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'raw_material_id',
        'qty_in',
        'qty_out',
        'balance',
        'status',
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
}