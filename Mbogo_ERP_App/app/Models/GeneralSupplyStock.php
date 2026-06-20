<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSupplyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'dept_id',
        'section_id',
        'stock_scope',
        'item_id',
        'item_description_id',
        'expiry_date',
        'qty_in',
        'qty_out',
        'damaged_qty',
        'balance',
        'purchase_price',
        'status',
    ];

    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function unit(){ return $this->belongsTo(Company_unit::class, 'comp_unit_id'); }
    public function workpoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function department(){ return $this->belongsTo(Department::class, 'dept_id'); }
    public function section(){ return $this->belongsTo(Section::class, 'section_id'); }
    public function item(){ return $this->belongsTo(GeneralSupplyItem::class, 'item_id'); }
    public function description(){ return $this->belongsTo(GeneralSupplyItemDescription::class, 'item_description_id'); }
}