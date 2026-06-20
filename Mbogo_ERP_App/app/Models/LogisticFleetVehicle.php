<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticFleetVehicle extends Model
{
    use HasFactory;

    protected $table = 'logistic_fleet_vehicles';

    protected $fillable = [
        'vehicle_code','plate_number','vehicle_type','ownership','make','model',
        'manufacture_year','fuel_type','fuel_rate_per_liter','hire_rate_per_day',
        'capacity','company_id','comp_unit_id','work_point_id','status','remarks',
        'created_by','updated_by'
    ];

    protected $casts = [
        'manufacture_year' => 'integer',
        'fuel_rate_per_liter' => 'decimal:2',
        'hire_rate_per_day' => 'decimal:2',
        'capacity' => 'decimal:2',
    ];

    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function compUnit(){ return $this->belongsTo(Company_unit::class, 'comp_unit_id'); }
    public function workPoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function updater(){ return $this->belongsTo(User::class, 'updated_by'); }
}
