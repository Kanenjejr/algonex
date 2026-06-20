<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSupplyIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'dept_id',
        'section_id',
        'stock_scope',
        'item_id',
        'item_description_id',
        'issue_date',
        'issued_qty',
        'remarks',
        'issued_by',
        'status',
    ];

    public function request(){ return $this->belongsTo(GeneralSupplyRequest::class, 'request_id'); }
    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function unit(){ return $this->belongsTo(Company_unit::class, 'comp_unit_id'); }
    public function workpoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function department(){ return $this->belongsTo(Department::class, 'dept_id'); }
    public function section(){ return $this->belongsTo(Section::class, 'section_id'); }
    public function item(){ return $this->belongsTo(GeneralSupplyItem::class, 'item_id'); }
    public function description(){ return $this->belongsTo(GeneralSupplyItemDescription::class, 'item_description_id'); }
}