<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;

    protected $table = 'raw_materials';

    protected $fillable = [
        'user_id',
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'material_name',
        'material_code',
        'unit_name',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requests()
    {
        return $this->hasMany(RawMaterialRequest::class, 'raw_material_id');
    }

    public function issues()
    {
        return $this->hasMany(RawMaterialIssue::class, 'raw_material_id');
    }

    public function receipts()
    {
        return $this->hasMany(ManufacturingReceipt::class, 'raw_material_id');
    }

    public function manufacturingStocks()
    {
        return $this->hasMany(ManufacturingMaterialStock::class, 'raw_material_id');
    }

    public function manufacturingMovements()
    {
        return $this->hasMany(ManufacturingStockMovement::class, 'raw_material_id');
    }

    public function consumptions()
    {
        return $this->hasMany(ManufacturingMaterialConsumption::class, 'raw_material_id');
    }

    public function storeStocks()
    {
        return $this->hasMany(RawMaterialStock::class, 'raw_material_id');
    }
}