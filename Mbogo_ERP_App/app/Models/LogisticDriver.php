<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticDriver extends Model
{
    use HasFactory;

    protected $table = 'logistic_drivers';

    protected $fillable = [
        'driver_code','first_name','last_name','phone','license_no','allowance_rate',
        'company_id','comp_unit_id','work_point_id','status','remarks','created_by','updated_by'
    ];

    protected $casts = ['allowance_rate' => 'decimal:2'];

    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function compUnit(){ return $this->belongsTo(Company_unit::class, 'comp_unit_id'); }
    public function workPoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function updater(){ return $this->belongsTo(User::class, 'updated_by'); }
}
