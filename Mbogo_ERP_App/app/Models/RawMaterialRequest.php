<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialRequest extends Model
{
    use HasFactory;

    protected $table = 'raw_material_requests';

    protected $fillable = [
        'request_no',
        'request_date',
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'raw_material_id',
        'requested_qty',
        'issued_qty',
        'remaining_qty',
        'unit_name',
        'no_of_bags',
        'bag_size',
        'remarks',
        'status',
        'requested_by',
        'updated_by',
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
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function issues()
    {
        return $this->hasMany(RawMaterialIssue::class, 'manufacturing_request_id');
    }
    public function receipts()
    {
        return $this->hasMany(ManufacturingReceipt::class, 'raw_material_request_id');
    }
}





