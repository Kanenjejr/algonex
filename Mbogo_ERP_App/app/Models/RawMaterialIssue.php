<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialIssue extends Model
{
    use HasFactory;

    protected $table = 'raw_material_issues';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'raw_material_id',
        'manufacturing_request_id',
        'issue_to_work_point_id',
        'issue_to_type',
        'issue_to_name',
        'issue_date',
        'issued_qty',
        'remarks',
        'issued_by',
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

    public function manufacturingRequest()
    {
        return $this->belongsTo(RawMaterialRequest::class, 'manufacturing_request_id');
    }

    public function issueToWorkPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'issue_to_work_point_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receipts()
    {
        return $this->hasMany(ManufacturingReceipt::class, 'raw_material_issue_id');
    }
}