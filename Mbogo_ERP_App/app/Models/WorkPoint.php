<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPoint extends Model
{
    use HasFactory;
     protected $table='work_points';
    protected $fillable = [
        'work_code',
        'work_name',
        'location',
        'phone_No',
        'company_id',
        'comp_unit_id',
        'district',
        'city',
        'user_id',
        'status',
    ];
     public function company(){
        return $this->belongsTo(CompanySite::class,'company_id');
    }
    public function comp_unit(){
        return $this->belongsTo(Company_unit::class,'comp_unit_id');
    }
}