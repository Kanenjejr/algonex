<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table='departments';
    protected $fillable=[
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'depName',
        'depCode',
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
    public function sections(){
        return $this->hasMany(Section::class, 'dept_id');
    }
}