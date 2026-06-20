<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingReceipt extends Model
{
    use HasFactory;

    protected $table = 'manufacturing_receipts';

    protected $fillable = [
        'receipt_no',
        'receipt_date',
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'raw_material_request_id',
        'raw_material_issue_id',
        'raw_material_id',
        'received_qty',
        'unit_name',
        'no_of_bags',
        'bag_size',
        'received_by',
        'remarks',
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

    public function request()
    {
        return $this->belongsTo(RawMaterialRequest::class, 'raw_material_request_id');
    }

    public function issue()
    {
        return $this->belongsTo(RawMaterialIssue::class, 'raw_material_issue_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}