<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $table='sections';
    protected $fillable=[
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'dept_id',
        'secName',
        'secCode',
        'Status',
    ];
    public function company(){
        return $this->belongsTo(CompanySite::class,'company_id');
    }
    public function comp_unit(){
        return $this->belongsTo(Company_unit::class,'comp_unit_id');
    }
    public function workpoint(){
        return $this->belongsTo(WorkPoint::class,'work_point_id');
    }
    public function dept(){
        return $this->belongsTo(Department::class,'dept_id');
    }
}