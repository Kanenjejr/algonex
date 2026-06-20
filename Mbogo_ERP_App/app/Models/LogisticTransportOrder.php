<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticTransportOrder extends Model
{
    use HasFactory;

    protected $table = 'logistic_transport_orders';

    protected $fillable = [
        'order_no','order_date','company_id','comp_unit_id','work_point_id','customer_id',
        'customer_name','cargo_description','origin','destination','vehicle_source',
        'company_vehicle_id','hired_vehicle_name','hired_vehicle_plate','hired_vehicle_cost',
        'driver_id','escort_name','escort_allowance','driver_allowance','expected_fuel_liters',
        'fuel_rate','revenue_amount','status','remarks','created_by','updated_by'
    ];

    protected $casts = [
        'order_date' => 'date',
        'hired_vehicle_cost' => 'decimal:2',
        'escort_allowance' => 'decimal:2',
        'driver_allowance' => 'decimal:2',
        'expected_fuel_liters' => 'decimal:2',
        'fuel_rate' => 'decimal:2',
        'revenue_amount' => 'decimal:2',
    ];

    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function compUnit(){ return $this->belongsTo(Company_unit::class, 'comp_unit_id'); }
    public function workPoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function customer(){ return $this->belongsTo(Customer::class, 'customer_id'); }
    public function vehicle(){ return $this->belongsTo(LogisticFleetVehicle::class, 'company_vehicle_id'); }
    public function driver(){ return $this->belongsTo(LogisticDriver::class, 'driver_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function updater(){ return $this->belongsTo(User::class, 'updated_by'); }
    public function costing(){ return $this->hasOne(LogisticTransportCost::class, 'transport_order_id'); }
}
