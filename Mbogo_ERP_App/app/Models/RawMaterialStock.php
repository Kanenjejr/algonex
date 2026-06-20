<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialStock extends Model
{
    use HasFactory;

    protected $table = 'raw_material_stocks';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'raw_material_id',
        'qty_in',
        'qty_out',
        'balance',
        'unit_price',
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

    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function material()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}